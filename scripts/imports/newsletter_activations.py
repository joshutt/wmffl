#!/usr/bin/env python3
"""Generate activations INSERT SQL from historical season newsletters.

Unlike scripts/imports/newsletter_playerscores.py (which resolves playerids
through an *already-backfilled* activations table), this script builds
activations from scratch for seasons where none exist yet (activations
only goes back to 2001; nothing before that has been backfilled). There
is no existing per-team roster to constrain candidates against, so
playerids are resolved directly against `players`, disambiguated with
`nflrosters` (which NFL team a player was on, and when) and a reference
table of "unit" players (Team QB / Kicker / Offense fakes) built from
the *entire* history of real activations.

Why the unit-player reference works: unit players (e.g. "Saints, New
Orleans" for New Orleans's Team QB slot) turn out to have exactly one
stable playerid per (slot type, NFL mascot) for all of 2001-2025 -
verified by checking that no (pos, lastname) pair among OL/blank-pos
"team" rows was ever activated under more than one playerid. Newsletters
may print an old city name for a since-relocated team (e.g. "St. Louis
Offense" for the Rams); CITY_MASCOTS below maps the printed city to the
team's mascot, which is what's stable, not the city.

Column semantics (per football/base): activations rows are (season,
week, teamid, pos, playerid) - the 15-slot lineup a team fielded that
week. A short newsletter block (fewer than 15 printed lines) usually
means an illegal/incomplete lineup that was fined; this script does not
fabricate placeholder rows for missing slots, it just inserts what the
newsletter actually lists and flags the shortfall.

Handles both newsletter layouts also handled by newsletter_playerscores.py
(two-column, single-column playoff/championship), plus an older
two-column variant (seen in 1999) with an extra "owner name" line
between the team name and the "====" rule.

Usage:
  1. Export the reference data (see the newsletter-import-activations
     skill for the exact SQL):
       --players       playerid, pos, lastname, firstname  (whole table)
       --nflrosters    playerid, nflteamid, dateon, dateoff (whole table)
       --unit-ref      DISTINCT pos, lastname, firstname, playerid
                       from activations JOIN players, pos IN (QB,K,OL)
       --week-dates    season, week, MIN(kickoff) from nflgames
       --teamnames     teamid, name for the target season
  2. python3 newsletter_activations.py --season 1999 \
       --players players.tsv --nflrosters nflrosters.tsv \
       --unit-ref unit_ref.tsv --week-dates weekdates.tsv \
       --teamnames teamnames_1999.tsv \
       --out out.sql <file:week> [<file:week> ...]

Anomalies (unresolved names, ambiguous candidates, short lineups) are
printed to stderr and embedded as `-- !!` comments in the SQL; exit code
1 if anything is unresolved. Never run automatically - Josh applies the
SQL by hand after reviewing the flagged anomalies.
"""

import argparse
import datetime
import re
import sys
from collections import defaultdict

SPLIT_COL = 36  # column where the right-hand team block starts in two-column layouts

CITY_MASCOTS = {
    "arizona": {"cardinals"}, "atlanta": {"falcons"}, "baltimore": {"ravens"},
    "buffalo": {"bills"}, "carolina": {"panthers"}, "chicago": {"bears"},
    "cincinnati": {"bengals"}, "cleveland": {"browns"}, "dallas": {"cowboys"},
    "denver": {"broncos"}, "detroit": {"lions"}, "greenbay": {"packers"},
    "houston": {"oilers", "texans"}, "indianapolis": {"colts"},
    "jacksonville": {"jaguars"}, "kansascity": {"chiefs"},
    "miami": {"dolphins"}, "minnesota": {"vikings"},
    "newengland": {"patriots"}, "neworleans": {"saints"},
    "nygiants": {"giants"}, "nyjets": {"jets"}, "oakland": {"raiders"},
    "philadelphia": {"eagles"}, "pittsburgh": {"steelers"},
    "sandiego": {"chargers"}, "sanfran": {"49ers"},
    "sanfrancisco": {"49ers"}, "seattle": {"seahawks"},
    "stlouis": {"rams"}, "tampabay": {"buccaneers"},
    "tennessee": {"titans", "oilers"}, "washington": {"redskins", "commanders", "football team"},
    "sanfransico": {"49ers"},  # recurring newsletter typo for "San Francisco"
}

# newsletters print the common/AP abbreviation, which sometimes differs from
# the one stored in `nflrosters` - normalize before comparing.
ABBREV_ALIASES = {"JAX": "JAC"}

# Team QB and Team Kicker unit-player rows were seeded in fixed flmid bands,
# one row per NFL team - confirmed directly by Josh (not inferred), so this
# is authoritative even for teams/eras with zero real activation history to
# fall back on (e.g. pre-2001 seasons where the unit was never activated
# again after the league started tracking activations in 2001).
UNIT_FLMID_RANGES = {"QB": (651, 682), "K": (701, 732)}

# normalized nickname/given-name groups; any two names in the same group
# are treated as the same person for matching purposes.
NICKNAME_GROUPS = [
    {"michael", "micheal", "mike", "mick"}, {"robert", "rob", "bob", "bobby"},
    {"william", "will", "bill", "billy"}, {"james", "jim", "jimmy"},
    {"anthony", "tony"}, {"edward", "ed", "eddie"}, {"charles", "chuck", "charlie"},
    {"thomas", "tom", "tommy"}, {"richard", "rick", "dick", "ricky"},
    {"donald", "don", "donnie"}, {"kenneth", "ken", "kenny"},
    {"joseph", "joe", "joey"}, {"christopher", "chris"}, {"matthew", "matt"},
    {"daniel", "dan", "danny"}, {"timothy", "tim", "timmy"}, {"gerald", "jerry"},
    {"lawrence", "larry"}, {"raymond", "ray"}, {"ronald", "ron", "ronnie"},
    {"steven", "steve", "stephen"}, {"gregory", "greg"}, {"alexander", "alex"},
    {"nathaniel", "nate", "nathan"}, {"jonathan", "jon"}, {"samuel", "sam"},
    {"benjamin", "ben"}, {"frederick", "fred"}, {"albert", "al"},
    {"andrew", "andy", "drew"}, {"patrick", "pat"}, {"douglas", "doug"},
    {"harold", "harry"}, {"walter", "walt"}, {"russell", "russ"},
    {"vincent", "vince"}, {"nicholas", "nick"}, {"zachary", "zach"},
    {"jeffrey", "jeff"}, {"antwan", "antoine", "antwaan"},
]
NICKNAME_OF = {}
for _grp in NICKNAME_GROUPS:
    for _n in _grp:
        NICKNAME_OF[_n] = _grp


def norm(s):
    return re.sub(r"[^a-z0-9]", "", (s or "").lower())


def names_match(a, b):
    if a == b:
        return True
    return b in NICKNAME_OF.get(a, ()) or a in NICKNAME_OF.get(b, ())


class Entry:
    def __init__(self, team, pos, name, abbrev, pts):
        self.team, self.pos, self.name, self.abbrev, self.pts = team, pos, name, abbrev, pts
        self.playerid = None

    def __repr__(self):
        return f"{self.team} {self.pos} {self.name} ({self.abbrev}) = {self.pts}"


def parse_pts(text):
    m = re.search(r"(-?\d+)\s*$", text)
    return int(m.group(1)) if m else None


def parse_player_line(line):
    m = re.match(r"^(HC|QB|RB|WR|TE|DL|LB|DB):|^(K) :|^(Off) ", line)
    if not m:
        return None
    pos = next(g for g in m.groups() if g)
    return "OL" if pos == "Off" else pos


def split_name_abbrev_pts(text):
    """From 'POS: Name...  ABBREV  pts' (two-column) return (name, abbrev, pts)."""
    pts = parse_pts(text)
    name = text[4:24].strip()
    rest = text[24:]
    if pts is not None:
        rest = re.sub(r"-?\d+\s*$", "", rest)
    m = re.match(r"\s*([A-Za-z.\' ]+?)\s*$", rest)
    abbrev = m.group(1).strip() if m and m.group(1).strip() else None
    return name, abbrev, pts


def is_rule(s):
    return len(s) >= 8 and set(s) == {"="}


def parse_two_column(lines, teams_known):
    """Two-column layout. Tolerates an optional owner-name line between the
    team name and the '====' rule (seen in 1999)."""
    out = []
    cur = {}
    for i, raw in enumerate(lines):
        halves = {"L": raw[:SPLIT_COL].rstrip(), "R": raw[SPLIT_COL:].rstrip()}
        for side, text in halves.items():
            if not text.strip():
                continue
            stripped = text.strip()

            def half(j):
                if j < 0 or j >= len(lines):
                    return ""
                return (lines[j][:SPLIT_COL] if side == "L" else lines[j][SPLIT_COL:]).strip()

            if stripped in teams_known and (is_rule(half(i + 1)) or is_rule(half(i + 2))):
                cur[side] = stripped
                continue
            if is_rule(stripped):
                continue
            team = cur.get(side)
            if team is None:
                continue
            pos = parse_player_line(text)
            if pos:
                name, abbrev, pts = split_name_abbrev_pts(text)
                if pts is None:
                    out.append(("BAD", team, pos, text))
                else:
                    out.append(("PLAYER", team, pos, name, abbrev, pts))
            elif "Offensive Points" in text or "Defensive Points" in text:
                kind = "off" if "Offensive" in text else "def"
                out.append(("SUBTOTAL", team, kind, parse_pts(text)))
    return out


def parse_single_column(lines, teams_known):
    """Playoff/championship layout: one team per block, no NFL abbrev column."""
    out = []
    team = None
    for i, raw in enumerate(lines):
        text = raw.rstrip()
        stripped = text.strip()
        if not stripped:
            continue
        if is_rule(stripped) and i >= 2 and lines[i - 2].strip() in teams_known:
            team = lines[i - 2].strip()
            continue
        if text[:1].isspace():
            if team and ("Offensive Points" in text or "Defensive Points" in text):
                kind = "off" if "Offensive" in text else "def"
                out.append(("SUBTOTAL", team, kind, parse_pts(text)))
            continue
        pos = parse_player_line(text)
        if pos and team:
            # pts sits right after the name; some seasons (1999) then
            # continue the line with inline stat detail instead of
            # putting it on the next indented line (2001-style) - only
            # anchor on end-of-string when nothing else matches.
            m = re.match(r"\s*(.+?)\s+(-?\d+)(?:\s|$)", text[4:])
            if m:
                out.append(("PLAYER", team, pos, m.group(1), None, int(m.group(2))))
            else:
                out.append(("BAD", team, pos, text))
    return out


# 1993-era box scores print no position label and no NFL abbreviation at
# all - the slot is implied purely by row order within a team's 15-line
# block. Order confirmed against each newsletter's own "LEADING POINT
# SCORERS BY POSITION" section, which lists positions in this exact
# sequence: HC is LAST (unlike 2001+, where it's first) and DB comes
# before DL/LB (also reversed from 2001+).
TAB_SLOT_ORDER = ["QB", "RB", "RB", "WR", "WR", "TE", "K", "OL",
                  "DB", "DB", "DL", "DL", "LB", "LB", "HC"]


def parse_tabbed_dual(lines, teams_known):
    """1993-style layout: '<pts>\\t<name>\\t<name>\\t<pts>' pairs, two teams
    per block, position implied by row index (TAB_SLOT_ORDER), no
    per-line position label and no NFL abbreviation column."""
    teams_lower = {t.lower(): t for t in teams_known}
    out = []
    pair = None
    idx = 0
    for raw in lines:
        stripped = raw.strip()
        if not stripped:
            continue
        parts = [p.strip() for p in re.split(r"\t+", stripped) if p.strip()]
        if len(parts) == 2 and parts[0].lower() in teams_lower and parts[1].lower() in teams_lower:
            pair = (teams_lower[parts[0].lower()], teams_lower[parts[1].lower()])
            idx = 0
            continue
        if pair is None or len(parts) != 4:
            continue
        if parts[1].strip().lower() == "final score":
            try:
                out.append(("SUBTOTAL", pair[0], "total", int(parts[0])))
                out.append(("SUBTOTAL", pair[1], "total", int(parts[3])))
            except ValueError:
                pass
            continue
        try:
            lp, rp = int(parts[0]), int(parts[3])
        except ValueError:
            continue
        if idx >= len(TAB_SLOT_ORDER):
            out.append(("BAD", pair[0], "?", raw))
            continue
        pos = TAB_SLOT_ORDER[idx]
        out.append(("PLAYER", pair[0], pos, parts[1], None, lp))
        out.append(("PLAYER", pair[1], pos, parts[2], None, rp))
        idx += 1
    return out


def is_tabbed_dual(lines, teams_known):
    teams_lower = {t.lower() for t in teams_known}
    for l in lines:
        parts = [p.strip() for p in re.split(r"\t+", l.strip()) if p.strip()]
        if len(parts) == 2 and parts[0].lower() in teams_lower and parts[1].lower() in teams_lower:
            return True
    return False


def extract_breakdown(path):
    lines = open(path, encoding="latin-1").read().splitlines()
    for i, l in enumerate(lines):
        if "Scoring Breakdown" in l:
            return lines[i + 1:]
    return lines


def load_tsv(path, cols):
    rows = []
    with open(path, encoding="utf-8") as f:
        header = f.readline()
        for line in f:
            parts = line.rstrip("\n").split("\t")
            rows.append(dict(zip(cols, parts)))
    return rows


def load_players(path):
    by_pos = defaultdict(list)
    by_lastname = defaultdict(list)
    for r in load_tsv(path, ("playerid", "flmid", "pos", "lastname", "firstname")):
        r["playerid"] = int(r["playerid"])
        r["flmid"] = int(r["flmid"]) if r.get("flmid") not in (None, "", "NULL") else None
        by_pos[r["pos"]].append(r)
        by_lastname[norm(r["lastname"])].append(r)
    return by_pos, by_lastname


def load_nflrosters(path):
    by_pid = defaultdict(list)
    for r in load_tsv(path, ("playerid", "nflteamid", "dateon", "dateoff")):
        pid = int(r["playerid"])
        dateon = r["dateon"] or "0001-01-01"
        dateoff = r["dateoff"] or "9999-12-31"
        by_pid[pid].append((r["nflteamid"], dateon, dateoff))
    return by_pid


def load_unit_ref(path):
    ref = defaultdict(set)
    for r in load_tsv(path, ("pos", "lastname", "firstname", "playerid")):
        ref[(r["pos"], norm(r["lastname"]))].add(int(r["playerid"]))
    return ref


def load_week_dates(path):
    dates = {}
    for r in load_tsv(path, ("season", "week", "mindate")):
        if r["mindate"] and r["mindate"] != "NULL":
            dates[(int(r["season"]), int(r["week"]))] = r["mindate"][:10]
    return dates


def load_teamnames(path):
    names = {}
    for r in load_tsv(path, ("teamid", "name")):
        names[r["name"]] = int(r["teamid"])
    return names


def approx_date(season, week, week_dates):
    if (season, week) in week_dates:
        return week_dates[(season, week)]
    # fall back to a plain NFL-calendar heuristic (~7 days/week from
    # the first Sunday in September); good enough for year-scale
    # nflrosters date ranges, not for anything week-precise.
    start = datetime.date(season, 9, 5)
    return str(start + datetime.timedelta(days=7 * (week - 1)))


def unit_city(name, pos):
    """Return the city/location text if `name` looks like a unit-player
    entry for this slot, else None. Handles 'X Team Qb'/'X Kicker'/
    'Off X Offense' phrasing (1999+) and the bare 'X QB'/'X K'/'X OL'
    phrasing (1993, where every QB/K/OL slot is always a unit)."""
    m = re.search(r"^(.*?)\s*(Kicker|Offense|Team Qb|Team QB)\s*$", name, flags=re.I)
    if m:
        return m.group(1)
    if pos in ("QB", "K", "OL"):
        m2 = re.match(rf"^(.*\S)\s+{pos}$", name)
        if m2:
            return m2.group(1)
    return None


def candidate_mascots(city_phrase):
    """Resolve a unit-player's printed location to candidate NFL mascots.
    Usually the phrase is just a city ('Carolina', 'St. Louis') - looked
    up in CITY_MASCOTS. 1993-era newsletters sometimes spell the mascot
    out directly instead ('New York Giants', 'L.A. Rams'); treat the last
    word as a literal mascot guess too."""
    cands = set(CITY_MASCOTS.get(norm(city_phrase), ()))
    words = city_phrase.split()
    if len(words) >= 2:
        cands |= set(CITY_MASCOTS.get(norm(" ".join(words[:-1])), ()))
        cands.add(norm(words[-1]))
    return cands


def resolve_unit(pos, city_phrase, unit_ref, by_lastname):
    mascots = candidate_mascots(city_phrase)
    if not mascots:
        return None, f"unknown city/unit name '{city_phrase}'"
    candidates = set()
    for m in mascots:
        candidates |= unit_ref.get((pos, norm(m)), set())
    if len(candidates) == 1:
        return next(iter(candidates)), None
    if len(candidates) > 1:
        return None, f"ambiguous unit ids {sorted(candidates)} for {pos} '{city_phrase}'"
    flmid_range = UNIT_FLMID_RANGES.get(pos)
    if flmid_range:
        lo, hi = flmid_range
        fl_matches = {p["playerid"] for m in mascots for p in by_lastname.get(norm(m), [])
                      if p["flmid"] is not None and lo <= p["flmid"] <= hi}
        if len(fl_matches) == 1:
            return next(iter(fl_matches)), (f"never activated post-2001; resolved via confirmed "
                                             f"{pos} unit-player flmid band {lo}-{hi}")
    # Kicker units were seeded id=OL_id-1 with zero exceptions across every
    # team where both are independently confirmed by real activations
    # history - use that when the Kicker slot itself was never activated
    # (common: teams switched to real individual kickers early).
    if pos == "K":
        ol_ids = set()
        for m in mascots:
            ol_ids |= unit_ref.get(("OL", norm(m)), set())
        if len(ol_ids) == 1:
            guess = next(iter(ol_ids)) - 1
            valid_ids = {p["playerid"] for m in mascots for p in by_lastname.get(norm(m), [])}
            if guess in valid_ids:
                return guess, ("inferred as OL_id-1 (Kicker was never itself activated post-2001; "
                               "verify - this pattern holds for every OL/K pair confirmed so far)")
    # never seen in real activations under this slot (common: kicker/QB
    # units got replaced by real individual players before any post-2001
    # activations used the fake unit row) - surface every playerid that
    # was ever created under this mascot so Josh can pick with --map.
    hint_ids = sorted({p["playerid"] for m in mascots for p in by_lastname.get(norm(m), [])})
    hint = f"; never-activated candidates for mascot: {hint_ids}" if hint_ids else "; no matching player row at all"
    return None, f"no reference activation ever found for {pos} unit '{city_phrase}'{hint}"


def nfl_team_at(pid, abbrev, date, nflrosters):
    abbrev = ABBREV_ALIASES.get(abbrev, abbrev)
    for teamid, dateon, dateoff in nflrosters.get(pid, ()):
        if teamid == abbrev and dateon <= date <= dateoff:
            return True
    return False


def resolve_real(pos, name, abbrev, week_date, players_by_pos, by_lastname, nflrosters, overrides, key):
    if key in overrides:
        return overrides[key], "manual override"
    if "," in name:
        last, _, first = name.partition(",")
    else:
        # 1993-era newsletters print 'First Last' (no comma) instead of
        # 'Last,First' - first token is the given name, everything after
        # is the surname (correctly keeps multi-word surnames like
        # 'Del Rio' or "O'Neal" together).
        toks = name.split(None, 1)
        first, last = (toks[0], toks[1]) if len(toks) == 2 else (toks[0], "")
    last, first = norm(last), norm(first)
    cands = [p for p in players_by_pos.get(pos, []) if norm(p["lastname"]) == last]
    exact = [p for p in cands if norm(p["firstname"]) == first]
    nickmatch = [] if exact else [p for p in cands if names_match(norm(p["firstname"]), first)]
    pos_note = None
    if not exact and not nickmatch:
        # the newsletter's slot heading (e.g. DL) doesn't have to match the
        # player's default pos in `players` (e.g. LB) - DL/LB and RB/WR-flex
        # crossover was common in older lineups. This also covers the case
        # where `cands` is non-empty but none of them is the right first
        # name (the real player is filed under a different default pos) -
        # not just the case where `cands` was empty outright. Retry across
        # all positions.
        wide = [p for p in by_lastname.get(last, [])]
        if wide:
            pos_note = (f"slot '{pos}' differs from this player's default pos "
                       f"'{wide[0]['pos']}' (flex/positional-overlap, common in older lineups)")
        exact = [p for p in wide if norm(p["firstname"]) == first]
        nickmatch = [] if exact else [p for p in wide if names_match(norm(p["firstname"]), first)]
        cands = wide
    if not cands:
        return None, f"no player named '{name}' anywhere in players table"
    pool, note = (exact, pos_note) if exact else (
        (nickmatch, "matched via nickname" + (f"; {pos_note}" if pos_note else ""))
        if nickmatch else
        (cands, "matched on last name only" + (f"; {pos_note}" if pos_note else "")))
    if len(pool) == 1:
        return pool[0]["playerid"], note
    if abbrev and week_date:
        narrowed = [p for p in pool if nfl_team_at(p["playerid"], abbrev, week_date, nflrosters)]
        if len(narrowed) == 1:
            return narrowed[0]["playerid"], (note or "disambiguated via nflrosters")
        if len(narrowed) > 1:
            return None, (f"still ambiguous after nflrosters filter for '{name}' ({abbrev} "
                          f"on {week_date}): {[p['playerid'] for p in narrowed]}")
    return None, f"ambiguous ({len(pool)} candidates) for '{name}' ({pos}), no abbrev/date to narrow"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--season", type=int, required=True)
    ap.add_argument("--players", required=True)
    ap.add_argument("--nflrosters", required=True)
    ap.add_argument("--unit-ref", required=True)
    ap.add_argument("--week-dates", required=True)
    ap.add_argument("--teamnames", required=True)
    ap.add_argument("--out", required=True)
    ap.add_argument("--map", action="append", default=[], metavar="WEEK:TEAM:POS:NAME=PLAYERID",
                    help="manual playerid override for an entry that can't be auto-resolved")
    ap.add_argument("files", nargs="+", help="newsletter.php:week pairs")
    args = ap.parse_args()

    overrides = {}
    for spec in args.map:
        loc, pid = spec.rsplit("=", 1)
        wk, team, pos, name = loc.split(":", 3)
        overrides[(int(wk), team, pos, norm(name))] = int(pid)

    players_by_pos, players_by_lastname = load_players(args.players)
    nflrosters = load_nflrosters(args.nflrosters)
    unit_ref = load_unit_ref(args.unit_ref)
    week_dates = load_week_dates(args.week_dates)
    teamids = load_teamnames(args.teamnames)
    teams_known = set(teamids)

    sql_parts = []
    all_ok = True
    for spec in args.files:
        path, week = spec.rsplit(":", 1)
        week = int(week)
        wdate = approx_date(args.season, week, week_dates)
        body = extract_breakdown(path)
        if is_tabbed_dual(body, teams_known):
            records = parse_tabbed_dual(body, teams_known)
        else:
            two_col = any(len(l) > SPLIT_COL + 4 and l[:SPLIT_COL].strip() in teams_known
                          and l[SPLIT_COL:].strip() in teams_known for l in body)
            records = (parse_two_column if two_col else parse_single_column)(body, teams_known)

        entries, subtotals, warnings = [], defaultdict(dict), []
        for r in records:
            if r[0] == "PLAYER":
                entries.append(Entry(r[1], r[2], r[3], r[4], r[5]))
            elif r[0] == "SUBTOTAL":
                subtotals[r[1]][r[2]] = r[3]
            else:
                warnings.append(f"UNPARSED line for {r[1]} {r[2]}: {r[3]!r}")

        for e in entries:
            key = (week, e.team, e.pos, norm(e.name))
            city = unit_city(e.name, e.pos)
            if key in overrides:
                e.playerid = overrides[key]
                warnings.append(f"OVERRIDE {e.team} {e.pos} '{e.name}' -> playerid {e.playerid}")
                continue
            if city is not None:
                pid, err = resolve_unit(e.pos, city, unit_ref, players_by_lastname)
            else:
                pid, err = resolve_real(e.pos, e.name, e.abbrev, wdate, players_by_pos,
                                        players_by_lastname, nflrosters, overrides, key)
            if pid is None:
                warnings.append(f"UNRESOLVED {e.team} {e.pos} '{e.name}' ({e.abbrev}): {err}")
                all_ok = False
            else:
                e.playerid = pid
                if err:
                    warnings.append(f"{e.team} {e.pos} '{e.name}': {err} -> playerid {pid}")

        # duplicate playerid within the same team+week (bad match collision)
        for team in {e.team for e in entries}:
            seen = {}
            for e in entries:
                if e.team != team or not e.playerid:
                    continue
                if e.playerid in seen:
                    warnings.append(f"DUPLICATE playerid {e.playerid} for {team} week {week}: "
                                    f"{seen[e.playerid]} and {e}")
                    all_ok = False
                seen[e.playerid] = e

        for team in sorted({e.team for e in entries}):
            n = sum(1 for e in entries if e.team == team)
            if n < 15:
                warnings.append(f"SHORT LINEUP {team} week {week}: only {n} of 15 slots printed "
                                f"(likely an illegal/fined lineup - not fabricating filler rows)")

        matched = [e for e in entries if e.playerid]
        header = [f"-- Week {week}: {path.split('/')[-1]} - "
                  f"{len(matched)} players, {len({e.team for e in entries})} teams "
                  f"(approx date {wdate})"]
        for w in warnings:
            header.append(f"-- !! {w}")
            print(f"week {week}: {w}", file=sys.stderr)

        team_order = list(dict.fromkeys(e.team for e in matched))
        matched.sort(key=lambda e: team_order.index(e.team))
        values = []
        cur_team = None
        for e in matched:
            if e.team != cur_team:
                cur_team = e.team
                values.append(f"-- {e.team} (teamid {teamids[e.team]})")
            values.append(f"({args.season}, {week:>2}, {teamids[e.team]:>2}, "
                          f"'{e.pos}', {e.playerid:>5}),  -- {e.name}"
                          + (f" ({e.abbrev})" if e.abbrev else "") + f" [{e.pts} pts]")
        if values:
            last = max(i for i, v in enumerate(values) if not v.startswith("--"))
            values[last] = values[last].replace("),  --", ");  --", 1)
            sql_parts.append("\n".join(header) + "\n"
                             + "INSERT INTO activations (season, week, teamid, pos, playerid) VALUES\n"
                             + "\n".join(values) + "\n")
        else:
            sql_parts.append("\n".join(header) + "\n-- (nothing resolved, no INSERT emitted)\n")

    preamble = f"""-- {args.season} season activations, weeks transcribed from the
-- newsletters in football/history/{args.season}Season/.
-- Generated by scripts/imports/newsletter_activations.py. Real players
-- resolved against `players` (disambiguated via `nflrosters` NFL-team/date
-- history when more than one candidate shares a name); unit players
-- (Team QB/Kicker/Offense) resolved against a reference table built from
-- every (position-slot, mascot) ever seen in real activations history.
-- Lines marked !! are anomalies - review before running. Nothing here has
-- been executed against the database.

"""
    with open(args.out, "w") as f:
        f.write(preamble + "\n".join(sql_parts))
    print(f"wrote {args.out}", file=sys.stderr)
    sys.exit(0 if all_ok else 1)


if __name__ == "__main__":
    main()
