#!/usr/bin/env python3
"""Generate playerscores INSERT SQL from historical season newsletters.

Parses the weekly "WMFFL Scoring Breakdown" sections out of
football/history/<season>Season/<season>wk*.php, maps each listed player
to a playerid via the already-backfilled activations table, verifies the
parsed per-player points against the newsletter's own offensive/defensive
subtotals, and emits one INSERT statement per week.

Column semantics (per scripts/logscores/transferscores.php): pts is the
player's score for the week, active is the same value when activated and
NULL otherwise. Newsletters only record activated players, so pts=active.

Handles both newsletter layouts:
  - two-column (side-by-side teams, name/NFL-abbrev/points, weeks 1-14)
  - single-column (one team block with stat detail lines, playoff and
    championship newsletters)

Usage:
  1. Export activations (tab-separated, with header):
       SELECT a.week, a.teamid, tn.name, a.pos, a.playerid,
              IFNULL(p.lastname,''), IFNULL(p.firstname,'')
       FROM activations a
       JOIN teamnames tn ON tn.teamid=a.teamid AND tn.season=a.season
       LEFT JOIN players p ON p.playerid=a.playerid
       WHERE a.season=<season> AND a.week BETWEEN <min> AND <max>
       ORDER BY a.week, a.teamid
  2. python3 newsletter_playerscores.py --season 2001 \
       --activations acts.tsv --out out.sql \
       <file:week> [<file:week> ...]
     e.g. 2001wk3.php:3 2001wkp.php:15 2001wkc.php:16

Anomalies (unmatched players, subtotal mismatches, unscored activations)
are printed to stderr and embedded as comments in the SQL; exit code 1
if any week failed its checksum.
"""

import argparse
import re
import sys
from collections import defaultdict

POSITIONS = ("HC", "QB", "RB", "WR", "TE", "K", "OL", "DL", "LB", "DB")
OFFENSE = {"HC", "QB", "RB", "WR", "TE", "K", "OL"}
SPLIT_COL = 36  # column where the right-hand team block starts

# newsletter city -> NFL mascot, to sanity-check unit players (kickers,
# offenses, team QBs) whose players-table rows may carry modern city
# names (San Diego Kicker is stored as "Chargers, Los Angeles").
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
    "tennessee": {"titans", "oilers"},
    "washington": {"redskins", "commanders", "football team"},
    "sanfransico": {"49ers"},  # recurring newsletter typo for "San Francisco"
}


def norm(s):
    return re.sub(r"[^a-z0-9]", "", s.lower())


class Entry:
    def __init__(self, team, pos, name, pts):
        self.team, self.pos, self.name, self.pts = team, pos, name, pts
        self.playerid = None

    def __repr__(self):
        return f"{self.team} {self.pos} {self.name} = {self.pts}"


def parse_pts(text):
    m = re.search(r"(-?\d+)\s*$", text)
    return int(m.group(1)) if m else None


def parse_player_line(line):
    """Parse 'POS: Name...  <pts>' from a block; None if not a player line."""
    m = re.match(r"^(HC|QB|RB|WR|TE|DL|LB|DB):|^(K) :|^(Off) ", line)
    if not m:
        return None
    pos = next(g for g in m.groups() if g)
    pos = "OL" if pos == "Off" else pos
    return pos, line[4:]


def parse_two_column(lines, teams_known):
    """Weeks in the side-by-side layout. Yields (team, pos, name, pts) and
    ('SUBTOTAL', team, kind, pts) records."""
    blocks = defaultdict(list)  # side -> pending lines
    out = []
    cur = {}  # side -> team name
    for i, raw in enumerate(lines):
        halves = {"L": raw[:SPLIT_COL].rstrip(), "R": raw[SPLIT_COL:].rstrip()}
        for side, text in halves.items():
            if not text.strip():
                continue
            nxt = ""
            if i + 1 < len(lines):
                nxt = (lines[i + 1][:SPLIT_COL] if side == "L" else lines[i + 1][SPLIT_COL:]).strip()
            if set(nxt) == {"="} and len(nxt) > 10 and text.strip() in teams_known:
                cur[side] = text.strip()
                continue
            team = cur.get(side)
            if team is None:
                continue
            p = parse_player_line(text)
            if p:
                pos, rest = p
                # name occupies cols 4-23 of the block; NFL abbrev+pts follow
                name = text[4:24].strip()
                pts = parse_pts(text)
                if pts is None:
                    out.append(("BAD", team, pos, text))
                else:
                    out.append(("PLAYER", team, pos, name, pts))
            elif "Offensive Points" in text or "Defensive Points" in text:
                kind = "off" if "Offensive" in text else "def"
                out.append(("SUBTOTAL", team, kind, parse_pts(text)))
    return out


def parse_single_column(lines, teams_known):
    """Playoff/championship layout: one team per block, indented stat lines."""
    out = []
    team = None
    for i, raw in enumerate(lines):
        text = raw.rstrip()
        stripped = text.strip()
        if not stripped:
            continue
        if set(stripped) == {"="} and len(stripped) >= 8 and i >= 2:
            cand = lines[i - 2].strip()
            if cand in teams_known:
                team = cand
                continue
        if text[:1].isspace():
            if team and ("Offensive Points" in text or "Defensive Points" in text):
                kind = "off" if "Offensive" in text else "def"
                out.append(("SUBTOTAL", team, kind, parse_pts(text)))
            continue  # stat detail / decoration
        p = parse_player_line(text)
        if p and team:
            pos, rest = p
            m = re.match(r"\s*(.+?)\s+(-?\d+)\s*$", text[4:])
            if m:
                out.append(("PLAYER", team, pos, m.group(1), int(m.group(2))))
            else:
                out.append(("BAD", team, pos, text))
    return out


# 1993-era box scores: '<pts>\t<name>\t<name>\t<pts>' pairs, two teams per
# block, no per-line position label and no NFL abbreviation - slot is
# implied by row order. Confirmed against each newsletter's own "LEADING
# POINT SCORERS BY POSITION" section: HC is LAST (2001+ has it first) and
# DB comes before DL/LB (2001+ has DL/LB first).
TAB_SLOT_ORDER = ["QB", "RB", "RB", "WR", "WR", "TE", "K", "OL",
                  "DB", "DB", "DL", "DL", "LB", "LB", "HC"]


def is_tabbed_dual(lines, teams_known):
    teams_lower = {t.lower() for t in teams_known}
    for l in lines:
        parts = [p.strip() for p in re.split(r"\t+", l.strip()) if p.strip()]
        if len(parts) == 2 and parts[0].lower() in teams_lower and parts[1].lower() in teams_lower:
            return True
    return False


def parse_tabbed_dual(lines, teams_known):
    """1993-style layout. No Offensive/Defensive subtotal is printed, only
    a per-team 'Final Score' total; yields ('SUBTOTAL', team, 'total', n)
    for that instead of the usual off/def pair."""
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
        out.append(("PLAYER", pair[0], pos, parts[1], lp))
        out.append(("PLAYER", pair[1], pos, parts[2], rp))
        idx += 1
    return out


def extract_breakdown(path):
    lines = open(path, encoding="latin-1").read().splitlines()
    for i, l in enumerate(lines):
        if "Scoring Breakdown" in l:
            return lines[i + 1:]
    # championship newsletters have no Scoring Breakdown header; the box
    # score is the only part where a team name sits two lines above a
    # ==== rule, so parsing the whole file is safe
    return lines


def is_unit_entry(name, pos):
    """True for 'San Diego Kicker' (1999+) or bare 'Dallas QB' (1993,
    where every QB/K/OL slot is always a unit, no individual players)."""
    if re.search(r"(Kicker|Offense|Team Qb|Team QB)\s*$", name, flags=re.I):
        return True
    return pos in ("QB", "K", "OL") and bool(re.match(rf"^.*\S\s+{pos}$", name))


def unit_name_ok(news_name, pos, lastname, firstname):
    """Check a unit-player entry's printed location against its roster
    row's mascot. Accepts both a bare city ('Carolina') and a spelled-out
    mascot ('New York Giants', 1993-style - last word is the mascot)."""
    m = re.match(r"^(.*?)\s*(Kicker|Offense|Team Qb|Team QB)\s*$", news_name, flags=re.I)
    city = m.group(1) if m else re.sub(rf"\s+{pos}$", "", news_name)
    mascots = set(CITY_MASCOTS.get(norm(city), ()))
    words = city.split()
    if len(words) >= 2:
        mascots |= set(CITY_MASCOTS.get(norm(" ".join(words[:-1])), ()))
        mascots.add(norm(words[-1]))
    if not mascots:
        return False
    return norm(lastname) in mascots


def match_entries(entries, acts, warnings, week, overrides):
    """Assign playerids from activations to parsed newsletter entries."""
    by_team = defaultdict(list)
    for e in entries:
        by_team[e.team].append(e)
    for team, tentries in by_team.items():
        pool = [a for a in acts.get(team, [])]
        used = set()
        for e in tentries:
            ov = overrides.get((week, team, e.pos, norm(e.name)))
            if ov is not None:
                e.playerid = ov
                warnings.append(f"OVERRIDE {team} {e.pos} '{e.name}' -> playerid {ov} "
                                f"(manual mapping; activations row disagrees)")
                continue
            cands = [a for a in pool if a["pos"] == e.pos and id(a) not in used
                     and a["playerid"] != 0]
            nn = norm(e.name)
            # order-flexible: 2001+ prints 'Last,First', 1993 prints
            # 'First Last' (no comma) - accept either concatenation order.
            exact = [a for a in cands if norm(a["lastname"] + a["firstname"]) == nn
                     or norm(a["firstname"] + a["lastname"]) == nn]
            pick, note = None, None
            if len(exact) == 1:
                pick = exact[0]
            elif len(cands) == 1:
                pick = cands[0]
                a = pick
                if is_unit_entry(e.name, e.pos):
                    if not unit_name_ok(e.name, e.pos, a["lastname"], a["firstname"]):
                        note = (f"unit-name check: newsletter '{e.name}' vs "
                                f"roster '{a['lastname']}, {a['firstname']}' (id {a['playerid']})")
                elif norm(a["lastname"]) not in nn:
                    note = (f"name check: newsletter '{e.name}' vs roster "
                            f"'{a['lastname']}, {a['firstname']}' (id {a['playerid']})")
            else:
                # several candidates: match on last name alone. 'Last,First'
                # (2001+) puts the last name before the comma; 'First Last'
                # (1993, no comma) puts it after the first space-separated
                # token.
                if "," in e.name:
                    last = norm(e.name.split(",")[0])
                else:
                    toks = e.name.split(None, 1)
                    last = norm(toks[1]) if len(toks) == 2 else norm(toks[0])
                bylast = [a for a in cands if norm(a["lastname"]) == last]
                if len(bylast) == 1:
                    pick = bylast[0]
                    note = (f"matched on last name only: '{e.name}' -> "
                            f"'{pick['lastname']}, {pick['firstname']}' (id {pick['playerid']})")
            if pick:
                e.playerid = pick["playerid"]
                used.add(id(pick))
                if note:
                    warnings.append(f"{team} {e.pos}: {note}")
            else:
                warnings.append(f"UNRESOLVED {team} {e.pos} '{e.name}' ({e.pts} pts): "
                                f"no unambiguous activation match")
        for a in acts.get(team, []):
            if a["playerid"] != 0 and id(a) not in used:
                warnings.append(f"ACTIVATED-BUT-UNSCORED {team} {a['pos']} "
                                f"{a['lastname']}, {a['firstname']} (id {a['playerid']}): "
                                f"in activations but absent from newsletter")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--season", type=int, required=True)
    ap.add_argument("--activations", required=True, help="TSV export, see docstring")
    ap.add_argument("--out", required=True)
    ap.add_argument("--map", action="append", default=[], metavar="WEEK:TEAM:POS:NAME=PLAYERID",
                    help="manual playerid override for a newsletter entry the "
                         "activations table cannot resolve")
    ap.add_argument("files", nargs="+", help="newsletter.php:week pairs")
    args = ap.parse_args()

    overrides = {}
    for spec in args.map:
        loc, pid = spec.rsplit("=", 1)
        wk, team, pos, name = loc.split(":", 3)
        overrides[(int(wk), team, pos, norm(name))] = int(pid)

    # activations: week -> teamname -> [row]
    acts = defaultdict(lambda: defaultdict(list))
    teams_known = set()
    with open(args.activations, encoding="utf-8") as f:
        next(f)
        for line in f:
            wk, teamid, name, pos, pid, last, first = line.rstrip("\n").split("\t")
            acts[int(wk)][name].append(
                {"teamid": int(teamid), "pos": pos, "playerid": int(pid),
                 "lastname": last, "firstname": first})
            teams_known.add(name)

    sql_parts = []
    all_ok = True
    for spec in args.files:
        path, week = spec.rsplit(":", 1)
        week = int(week)
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
                entries.append(Entry(r[1], r[2], r[3], r[4]))
            elif r[0] == "SUBTOTAL":
                subtotals[r[1]][r[2]] = r[3]
            else:
                warnings.append(f"UNPARSED line for {r[1]} {r[2]}: {r[3]!r}")

        match_entries(entries, acts[week], warnings, week, overrides)

        # checksum against the newsletter's own subtotals (off/def split,
        # or - when that's not printed, e.g. 1993 - the single game total)
        for team in sorted({e.team for e in entries}):
            want = subtotals.get(team, {})
            if "total" in want:
                total = sum(e.pts for e in entries if e.team == team)
                if want["total"] != total:
                    warnings.append(f"CHECKSUM {team}: parsed total {total} vs "
                                    f"newsletter Final Score {want['total']}")
                    all_ok = False
            else:
                off = sum(e.pts for e in entries if e.team == team and e.pos in OFFENSE)
                dfn = sum(e.pts for e in entries if e.team == team and e.pos not in OFFENSE)
                if want.get("off") != off or want.get("def") != dfn:
                    warnings.append(f"CHECKSUM {team}: parsed off/def {off}/{dfn} vs "
                                    f"newsletter {want.get('off')}/{want.get('def')}")
                    all_ok = False

        # PK safety: one row per player per week
        seen = {}
        for e in entries:
            if e.playerid and e.playerid in seen:
                warnings.append(f"DUPLICATE playerid {e.playerid} in week {week}: "
                                f"{seen[e.playerid]} and {e}")
                all_ok = False
            seen[e.playerid] = e

        matched = [e for e in entries if e.playerid]
        header = [f"-- Week {week}: {path.split('/')[-1]} - "
                  f"{len(matched)} players, {len({e.team for e in entries})} teams"]
        for w in warnings:
            header.append(f"-- !! {w}")
            print(f"week {week}: {w}", file=sys.stderr)
        # two-column weeks parse rows left/right interleaved; group per team
        team_order = list(dict.fromkeys(e.team for e in matched))
        matched.sort(key=lambda e: team_order.index(e.team))
        values = []
        cur_team = None
        for e in matched:
            if e.team != cur_team:
                cur_team = e.team
                tid = next(a["teamid"] for a in acts[week][e.team])
                total = subtotals.get(e.team, {})
                summary = (f"total {total.get('total')}" if "total" in total
                          else f"off {total.get('off')}, def {total.get('def')}")
                values.append(f"-- {e.team} (teamid {tid}) - {summary}")
            values.append(f"({e.playerid:>4}, {args.season}, {week:>2}, {e.pts:>3}, "
                          f"{e.pts:>3}),  -- {e.pos:<2} {e.name}")
        if values:
            last = max(i for i, v in enumerate(values) if not v.startswith("--"))
            values[last] = values[last].replace("),  --", ");  --", 1)
            sql_parts.append("\n".join(header) + "\n"
                             + f"INSERT INTO playerscores (playerid, season, week, pts, active) VALUES\n"
                             + "\n".join(values) + "\n")

    preamble = f"""-- {args.season} season player scores, weeks transcribed from the
-- newsletters in football/history/{args.season}Season/.
-- Generated by scripts/imports/newsletter_playerscores.py; player ids
-- resolved through the activations table, per-team totals verified
-- against each newsletter's Offensive/Defensive Points subtotals (or,
-- for eras that print only a game total instead - e.g. 1993 - the
-- Final Score line).
-- pts = weekly score; active = same value (only activated players
-- appear in newsletters). Lines marked !! are unresolved anomalies.

"""
    with open(args.out, "w") as f:
        f.write(preamble + "\n".join(sql_parts))
    print(f"wrote {args.out}", file=sys.stderr)
    sys.exit(0 if all_ok else 1)


if __name__ == "__main__":
    main()
