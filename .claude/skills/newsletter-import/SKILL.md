---
name: newsletter-import
description: Backfill playerscores for a historical season by parsing the weekly newsletters in football/history/<season>Season/ and resolving playerids through the activations table. Use when asked to import, transcribe, or backfill player scores from old season newsletters.
---

# Import playerscores from historical newsletters

Generates (never runs) a SQL file inserting `playerscores` rows for a
historical season, transcribed from the "WMFFL Scoring Breakdown"
sections of the weekly newsletter pages. The engine is
`scripts/imports/newsletter_playerscores.py`; this skill is the
workflow around it. 2001 was done this way — see
`scripts/database/historicalCatchup/insert_2001_wk3-16_playerscores.sql`
for the expected output shape.

## Semantics you must not change

- `playerscores.pts` = the player's score that week; `playerscores.active`
  = the same value when the player was activated, NULL otherwise (see
  `scripts/logscores/transferscores.php`). Newsletters only list
  activated players, so every generated row has `pts = active`.
- The `activations` table is the source of truth for playerids: it is
  already backfilled per week and joins to `players`. Never fuzzy-match
  newsletter names against the whole players table.
- `activations.playerid = 0` rows are placeholder slots for illegal
  (incomplete) lineups — expected, not an error. They get no
  playerscores row.

## Workflow

1. **Scope the season.** List `football/history/<season>Season/`.
   Regular weeks are `<season>wk<N>.php`; `<season>wkp.php` is the
   playoff week (regular_season_weeks + 1), `<season>wkc.php` the
   championship (+2). Check the `seasons` table for that season's
   `regular_season_weeks`/`total_weeks` rather than assuming 14/16.
   Ask which weeks are already done, or check
   `SELECT week, COUNT(*) FROM playerscores WHERE season=<season> GROUP BY week`
   and skip populated weeks.

2. **Confirm activations exist** for every target week
   (`SELECT week, COUNT(*) FROM activations WHERE season=<season> GROUP BY week`).
   If a week is missing, stop and flag it — the newsletter rosters
   can't be mapped to playerids without it.

3. **Export the activations TSV** (creds: parse `DATABASE_URL` in
   `symfony-app/.env.local`):

   ```sql
   SELECT a.week, a.teamid, tn.name, a.pos, a.playerid,
          IFNULL(p.lastname,''), IFNULL(p.firstname,'')
   FROM activations a
   JOIN teamnames tn ON tn.teamid=a.teamid AND tn.season=a.season
   LEFT JOIN players p ON p.playerid=a.playerid
   WHERE a.season=<season> AND a.week BETWEEN <min> AND <max>
   ORDER BY a.week, a.teamid
   ```

   Run with `mysql -B` and redirect to a temp file, header included.

4. **Spot-check the first newsletter's format** before trusting the
   parser. It auto-detects three known layouts:
   - two-column (teams side by side at column 36, `POS: Name  NFL  pts`)
   - single-column (playoff/championship style, indented stat lines,
     may lack a "Scoring Breakdown" header entirely)
   - tabbed-dual (1993-era: `<pts>\t<name>\t<name>\t<pts>` pairs, no
     position label, no NFL abbreviation, position implied by row order
     via `TAB_SLOT_ORDER`; checksums against the per-team "Final Score"
     total instead of Offensive/Defensive subtotals, since 1993 doesn't
     print those). Also handles `First Last` names (no comma) for that
     era - `resolve_real`'s exact-match and last-name fallback both
     accept either name order.
   Older seasons may differ further; adapt the parser rather than
   hand-editing its output.

5. **Run the parser:**

   ```
   python3 scripts/imports/newsletter_playerscores.py --season <season> \
     --activations <tsv> --out scripts/database/historicalCatchup/insert_<season>_wk<a>-<b>_playerscores.sql \
     <file>:<week> ...
   ```

   Exit code 1 means a checksum failed — that is a parsing bug, fix it
   before anything else.

6. **Triage warnings** (also embedded as `-- !!` comments in the SQL):
   - `matched on last name only`: informational (nickname variants like
     Michael/Mike). Verify once per player, then accept.
   - `UNRESOLVED` / `ACTIVATED-BUT-UNSCORED` in the same team+pos:
     newsletter and activations disagree. Investigate: check the
     `roster` table (dateon/dateoff) and adjacent weeks' activations to
     decide which side is right. The newsletter is the primary source;
     activations are themselves a backfill. If the newsletter player
     was on the roster, resolve with
     `--map "<week>:<team>:<pos>:<Name,First>=<playerid>"` and add a
     commented `UPDATE activations ...` suggestion to the SQL. If you
     cannot decide from the data, leave it flagged for Josh.
   - `CHECKSUM`: per-team parsed points don't sum to the newsletter's
     own Offensive/Defensive subtotals. Never ship a file with these.

7. **Reconcile counts.** Rows per week must equal activations rows
   minus playerid=0 slots minus still-unresolved entries. Playoff week
   covers 6 teams (semifinals + toilet bowl), championship 2.

8. **Deliver.** Show the SQL (or a summary plus the file path for big
   files), list every anomaly and how it was resolved, and **do not
   execute the SQL** — Josh applies it manually.

## Known pitfalls

- Unit players (team QBs, kickers, offenses) are stored under modern
  franchise names ("San Diego Kicker" = `Chargers, Los Angeles`); the
  parser's mascot map handles the check. Single-slot positions (HC,
  QB, TE, K, OL) match by slot, so renames don't block matching.
- Newsletters truncate long names into the NFL-abbrev column
  ("...Team QbIND"); the fixed-width parse handles it — don't "fix"
  the source files.
- Same-lastname teammates (Anderson/Carter/Barber style) are matched
  with first names; if a week still reports ambiguity, resolve via
  `--map`, never by editing generated SQL (regeneration loses edits).
- 2001 wk2 cancellation/reschedule notes etc. are prose above the
  breakdown; the parser only reads below the "Scoring Breakdown"
  header when present.
- **Resolve every activations anomaly (via the newsletter-import-
  activations skill's `--map`) before running this skill, don't leave
  some `UNRESOLVED` and proceed.** If a team's activations pool for a
  position is missing one of two same-slot players (e.g. only one of
  two LBs got resolved upstream), `match_entries`'s "one candidate left,
  use it" fallback can match that lone candidate against the *wrong*
  newsletter line (whichever one it sees first) - it still flags a
  `name check` warning when this happens, but the *correct* line for
  that candidate then reports `UNRESOLVED` right after, which reads like
  two separate problems instead of one root cause. Found by testing
  against 1993 with a deliberately-partial activations set.
