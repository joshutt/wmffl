---
name: newsletter-import-activations
description: Build activations (weekly lineups) for a historical season by parsing the weekly newsletters in football/history/<season>Season/, for seasons where activations don't exist yet (nothing before 2001 has been backfilled). Use when asked to reconstruct, backfill, or import lineups/rosters from old season newsletters. Companion to the newsletter-import skill, which backfills playerscores but requires activations to already exist - use this one first for pre-2001 seasons.
---

# Build activations from historical newsletters

Generates (never runs) a SQL file inserting `activations` rows for a
historical season, transcribed from the "WMFFL Scoring Breakdown"
sections of the weekly newsletter pages. The engine is
`scripts/imports/newsletter_activations.py`; this skill is the workflow
around it. Validated against `football/history/1999Season/99wk7.php`,
`99wk1.php`, `99wkp.php`, and `football/history/1993Season/93wk7.php`
(a third, materially harder layout - see below).

## How this differs from newsletter-import (playerscores)

That skill resolves playerids by joining newsletter names against an
*already-backfilled* `activations` table. This skill has no such table
to lean on - activations only goes back to 2001, so for anything older
there is no existing per-team roster to constrain candidates against.
Instead it resolves against `players` directly, disambiguated with:

- **`nflrosters`** (which NFL team a real player was on, and when) -
  narrows same-name candidates using the NFL team abbreviation printed
  next to their name and an approximate calendar date for that week.
- **A unit-player reference table**, built from *every* (position slot,
  NFL mascot) pair ever seen in real activations across all of
  2001-2025. Unit players (Team QB / Kicker / Offense fakes, e.g.
  "Saints, New Orleans" for New Orleans's Team QB) turn out to have
  exactly one stable playerid per (slot, mascot) for the entire
  history - verified directly, zero exceptions found. Match on mascot,
  not city: a newsletter may print a since-relocated team's old city
  ("St. Louis Offense" for the Rams); `CITY_MASCOTS` in the script maps
  the printed city to the stable mascot.
- **Confirmed Team QB/Kicker flmid bands** (Josh, 2026-07-26): every
  Team QB unit row has `flmid` 651-682, every Team Kicker unit row has
  `flmid` 701-732 (one row per NFL team in each band). `UNIT_FLMID_RANGES`
  in the script uses this directly whenever the unit was never itself
  activated post-2001 (common: a team switched to a real individually-
  scored starter early, so the fake unit row has zero real-activation
  history to match against via `unit_ref`). This is authoritative, not
  inferred - it fully replaced the old "flag QB as UNRESOLVED, no
  heuristic exists" behavior. The older `Kicker_id = OL_id - 1` inference
  (holds with zero exceptions across every team independently confirmed)
  is kept as a second-line fallback in case `flmid` is ever missing.
- **Cross-position fallback**: a newsletter's slot heading (e.g. `DL`)
  doesn't have to match a player's default `pos` in the `players` table
  (e.g. `LB`) - this was common for DL/LB tweeners in older lineups. The
  widen-to-all-positions retry fires whenever the same-position candidate
  pool doesn't contain the right first name too, not only when that pool
  is empty outright - a same-position, same-lastname pool with the wrong
  first name (e.g. 11 unrelated DL "Taylor"s when the real match is a DL
  slot filed under DB in `players`) used to silently resolve to the wrong
  player via "matched on last name only"; fixed 2026-07-26.
- **Abbreviation aliasing**: newsletters print the common/AP team
  abbreviation, which can differ from what's stored in `nflrosters` -
  e.g. Jacksonville is `JAX` in newsletters but `JAC` in `nflrosters`.
  `ABBREV_ALIASES` in the script normalizes before comparing; add to it
  if a new season surfaces another mismatch (symptom: an nflrosters-
  narrowable ambiguous case reports "no abbrev/date to narrow" instead
  of actually narrowing).

## The 1993-era layout

1993 newsletters use a third format, tab-delimited and structurally
unlike either 1999/2001 variant: `<pts>\t<name>\t<name>\t<pts>` pairs,
two teams side by side, **no per-line position label at all** and **no
NFL abbreviation column**. The slot is implied purely by row order -
confirmed against each newsletter's own "LEADING POINT SCORERS BY
POSITION" section, which lists positions in the same fixed order used
in the box score: `QB,RB,RB,WR,WR,TE,K,OL,DB,DB,DL,DL,LB,LB,HC` - note
**HC is last** here (2001+ has it first) and **DB comes before DL/LB**
(2001+ has DL/LB first). `is_tabbed_dual`/`parse_tabbed_dual` in the
script auto-detect and handle this; `TAB_SLOT_ORDER` encodes the row
order.

Other things that differ in 1993 and that the script accounts for:
- **Names are printed `First Last`, not `Last,First`** - no comma at
  all. `resolve_real` detects the absence of a comma and splits on the
  first space instead (first token = given name, everything after =
  surname - keeps multi-word surnames like "Del Rio" together).
- **Unit players (QB/K/OL) have no "Team Qb"/"Kicker"/"Offense" suffix
  word** - the slot's own position code is the literal suffix instead
  (`"Dallas QB"`, `"Kansas City K"`, `"Denver OL"`), and in this era
  *every* QB/K/OL slot is a unit - there are no individually-scored
  starting QBs or kickers yet. `unit_city()` handles both suffix styles.
- **Mascots are sometimes spelled out instead of just the city**
  (`"New York Giants QB"`, `"L.A. Rams QB"`) rather than relying on
  `CITY_MASCOTS` to map a bare city to its mascot. `candidate_mascots()`
  also tries the phrase's last word as a literal mascot name.
- Real newsletter typos recur across weeks in ways worth pre-seeding as
  aliases rather than re-discovering per week - e.g. "San Fransico" for
  San Francisco (added to `CITY_MASCOTS`), "Micheal" for "Michael"
  (added to the nickname groups).

Even with all of that, **expect a meaningfully higher `UNRESOLVED` rate
for 1993 than for 1999+** - roughly 20% on the one week tested, versus
under 10% for 1999. With no NFL abbreviation printed at all, a common
surname (there is no way to tell *which* "Burnett" or "McDonald" from
the name alone) can only be resolved if exactly one candidate shares
that position - there is no second signal to fall back on. This is a
real data limitation, not a parser gap; flag and move on.

## What it cannot do

- **Single-column playoff/championship newsletters, and all of 1993,
  print no NFL abbreviation column at all** (unlike two-column
  regular-season weeks in 1999+), so the nflrosters disambiguation step
  has nothing to narrow with. Expect more `UNRESOLVED` (ambiguous)
  real-player anomalies on those weeks/eras than on regular 1999+ weeks.
- It does not fabricate placeholder rows for illegal/short lineups
  (fewer than 15 printed lines) - it inserts what's printed and emits a
  `SHORT LINEUP` warning.
- Approximate week dates (from `nflgames`, falling back to a plain
  Sept-plus-7-days-per-week heuristic when a season/week isn't in that
  table) are only accurate to roughly a week - fine for nflrosters'
  year-scale tenures, not for anything date-precise.

## Workflow

1. **Scope the season.** List `football/history/<season>Season/`.
   Regular weeks are usually `<season>wk<N>.php` (older seasons may use
   a 2-digit year prefix, e.g. `99wk7.php`); `wkp.php` is the playoff
   week, `wkc.php` the championship. Confirm activations are actually
   missing: `SELECT COUNT(*) FROM activations WHERE season=<season>`
   should be 0 (or check per-week and skip populated ones).

2. **Spot-check the newsletter's layout** before trusting the parser.
   Two known two-column variants exist: 2001-style (team name directly
   above the `====` rule) and 1999-style (team name, then an owner-name
   line, then the rule) - both are auto-detected. Single-column
   playoff/championship layouts are also auto-detected, and come in two
   sub-variants: stat detail on its own indented line below the player
   (2001-era) or inline on the same line (1999-era) - also both
   handled. If a newer/older season differs further, extend the parser
   rather than hand-editing its output.

3. **Export the reference TSVs** (creds: parse `DATABASE_URL` in
   `symfony-app/.env.local`; use `mysql ... -B` and redirect each to a
   file, header included):

   ```sql
   -- players.tsv
   SELECT playerid, flmid, pos, lastname, firstname FROM players;

   -- nflrosters.tsv
   SELECT playerid, nflteamid, dateon, IFNULL(dateoff,'9999-12-31') FROM nflrosters;

   -- unit_ref.tsv (built from ALL activations history, not the target season)
   SELECT DISTINCT a.pos, p.lastname, p.firstname, a.playerid
   FROM activations a JOIN players p ON p.playerid=a.playerid
   WHERE a.pos IN ('QB','K','OL') AND a.playerid<>0;

   -- weekdates.tsv
   SELECT season, week, MIN(kickoff) FROM nflgames GROUP BY season, week;

   -- teamnames_<season>.tsv
   SELECT teamid, name FROM teamnames WHERE season=<season>;
   ```

4. **Run the parser**, one week or many at once:

   ```
   python3 scripts/imports/newsletter_activations.py --season 1999 \
     --players players.tsv --nflrosters nflrosters.tsv \
     --unit-ref unit_ref.tsv --week-dates weekdates.tsv \
     --teamnames teamnames_1999.tsv \
     --out scripts/database/historicalCatchup/insert_1999_wk<a>-<b>_activations.sql \
     <file>:<week> ...
   ```

   Exit code 1 means at least one entry is genuinely `UNRESOLVED` -
   that's expected and not itself a bug; a nonzero exit from a crash or
   an `UNPARSED`/`BAD` line (the line didn't match the expected shape
   at all) is a parsing bug and must be fixed before anything else.

5. **Triage warnings** (also embedded as `-- !!` comments in the SQL):
   - Informational, safe to accept: `matched via nickname`,
     `matched on last name only` (single unambiguous candidate),
     `disambiguated via nflrosters`, the `slot 'DL' differs from
     default pos` flex note, the Kicker `inferred as OL_id-1` note.
   - `UNRESOLVED`: no confident match, or still ambiguous after every
     narrowing step. The message lists every candidate id considered
     (unit-player hint list, or the ambiguous real-player id list).
     Investigate using `nflrosters`/`roster`/adjacent weeks, or ask
     Josh. Resolve with `--map "<week>:<team>:<pos>:<Name,First>=<playerid>"`.
   - `SHORT LINEUP`: fewer than 15 slots printed - normally an illegal
     lineup the newsletter itself calls out with a fine. Not an error.

6. **Deliver.** Show the SQL (or a summary plus the file path for big
   files), list every anomaly and how it was resolved (or why it's
   still open), and **do not execute the SQL** - Josh applies it
   manually, same as the playerscores skill.
