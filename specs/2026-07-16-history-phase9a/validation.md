# Phase 9a — History (non-season-specific): Validation

How to know the implementation succeeded and the PR can be merged.
Automated tests run from `symfony-app/` (`vendor/bin/phpunit tests/`);
E2E against `php -S 127.0.0.1:8099 -t public public/index.php` (the
explicit router argument matters — without it `.php` legacy routes
404 instead of reaching LegacyBridge), with the fake-session recipe
from earlier phases where a logged-in team is needed (teammoney's
Pay Now button). Fake-session gotcha reconfirmed: the session id must
be 22+ chars AND the blob needs `fullname` (`isin|b:1;teamnum|i:6;
fullname|s:9:"Test Six ";`) or legacy pages 500 in menu.php.

Content-parity technique: legacy pages captured via curl before any
change (baselines), new routes diffed cell-by-cell with a table
parser, ignoring markup/entity differences.

**VALIDATION PASS 2026-07-16 — all gates green** except the four DB
rows Josh opted to fix manually (see Outcomes). Suite: 635 tests,
1971 assertions, OK.

## Per-group gates

Every commit: suite green, CSRF on any new POST, no interpolated SQL,
no new functionality added to `/football/`.
✅ Held for all 8 commits (no new POST endpoints were added; the one
write flow, titles sync, hangs off the existing CSRF-protected
processFlags).

### 1. History hub
- [x] `/history` renders all six feature links + 34 season links
      (1992–2025; feature links point at the new routes)
- [x] Season links still resolve through LegacyBridge (spot-checked
      `/history/1992Season.php`, `/history/2005Season/schedule.php`,
      `/history/2025Season/`, `/history/2023Season/teammoney` — all
      200 after the retirement sweep too)

### 2. Past champions
- [x] Division/league/toilet rows match the legacy page (era-rename
      headers included), with these known deltas, all DB-authoritative:
      ZEN/Ravaging Camel Clutch casing (legacy typos), 2004 toilet
      score 19 not 17 (frozen 2004Season.php agrees with the DB),
      2024/2025 rows present (absent from the stale legacy page),
      1992 absent from division tables (pre-division era) as on legacy
- [x] Championship + toilet tables winner/loser/scores straight from
      `schedule`; 2023 toilet game = Amish Electricians 51–0 ✓;
      OT markers (1999, 2009) match the overtime flag
- [x] MVP column matches legacy for every listed season (2025 has no
      entry yet — renders blank by design)
- [x] Unit: repository queries assert season-correct teamnames joins,
      era-correct division names, League-era exclusion
- [~] 2009/2010/2011 championship scores + the 2022 White Division
      row: **excluded from validation per Josh (2026-07-16)** — the
      corrections live in `scripts/database/migration/
      2026-07-16-history-data-fixes.sql`; Josh applies them manually

### 3. Titles admin sync + backfill
- [x] Unit: flags save creates League/Division titles; unchecking
      removes them; re-saving is a no-op; seasons without
      season_flags rows untouched; Toilet never synced
- [x] Dev DB: saving 2024 and 2025 flags pages through the real
      endpoint (commissioner fake session + CSRF token) produced
      titles rows matching the legacy hardcoded champions; re-save
      verified idempotent (counts stable)
- [x] ≤2023 titles untouched: League 32→34, Division 77→83, Toilet
      25 unchanged
- [x] Deploy note recorded (roadmap + commit): run the data-fixes
      SQL, then re-save the 2024/2025 flags pages on prod

### 4. Past drafts
- [x] 1992–2005 rows match legacy verbatim (static const; 2005 moved
      to the static list — its selections were never entered in
      draftpicks, playerid NULL)
- [x] 2006–2023 DB-derived rows match the legacy page (name, pos,
      NFL team at draft time via nflrosters as-of week 1, WMFFL team
      name for that season) — 2019 shows "Amish Electricians"
      (teamnames; legacy row had a typo missing the s)
- [x] 2024/2025/2026 rows appear (new; 2026's draft is partially
      recorded and its #1 is in)
- [x] Summary tables recompute to legacy's groupings under current
      franchise names (fresher than the stale page: Omar's
      Coming/Juicy Juicy Yum Yum renames, 2024+ seasons included)
- [~] 2006 franchise attribution (Go Balls Deep, teamid 8, per
      draftsummary.txt): **in the data-fixes SQL, Josh applies
      manually** — until then the row shows Gallic Warriors

### 5. All-time records
- [x] All six splits cell-identical to the legacy page for every team
      (full-table diff on all six, not just spot checks), including
      exact tie order (teamid-desc tiebreak reproduces legacy's
      usort+array_reverse behavior)
- [x] Inactive teams italicized; tablesorter wired (same
      table.base.js as the team pages)
- [x] Unit: sort order incl. tie break, ties-count-half pct, split
      whitelist rejects unknown/injected split keys

### 6. Player records
- [x] Both pages cell-identical to legacy (101 rows recordseason,
      106 rows recordsweek) including supplemental pre-2003 entries
      interleaved at the right ranks, the top-10-plus-ties cutoff,
      and legacy's GROUP BY tie order (playerid tiebreak)
- [x] Current-season highlight class applied (currentSeason/oSeason,
      history.css carried over)
- [x] Unit: merge/cutoff logic — interleaving, tie-goes-first, ties
      at rank 10 kept, and the two pages' divergent cutoff behavior
      (recordseason `break 2` vs recordsweek inner break)

### 7. Team money
- [x] Default (rollover → 2025) and ?season=2024 output cell-identical
      to the legacy page for every team and column, including payouts
      box and last-update stamp
- [x] Pre-August rollover quirk ported (week 0 + non-August shows
      last season)
- [x] Pay Now button: logged-in team owing $56.93 sees the button
      with the computed amount on legacy and new alike (the dynamic
      legacy page already computed it — the hardcoded array only
      existed in frozen snapshots, so no behavior change after all);
      no button anonymous or for a paid-up team
- [x] Unit: ledger math on fixtures (pot/payout rates, fines→pot,
      half-win ties, playoff lines, delinquency cleared by positive
      balance, next-season fee, amtOwed)

### 8. Legacy retirement
- [x] Each deleted URL 301s to its new route, `.php` aliases
      included; `teammoney.php?season=2022` carries the param;
      `2024Season/teammoney(.php)` → `?season=2024` (that file was a
      second dynamic copy, retired with the original so
      `common/moneyUtil.php` could go too)
- [x] `paststreaks.php` dropped with no redirect. Note: it returns
      500, not 404 — LegacyBridge 500s on *any* missing legacy path
      (verified `/history/nonexistent.php` behaves identically);
      pre-existing bridge behavior, out of scope
- [x] Grep: no remaining references to the deleted files (statbar
      "Finances"/stats-index "Accounting" repointed from the frozen
      2024Season URL to `/history/teammoney`, legacy
      `football/base/statbar.html` included); only doc comments
      mention moneyUtil.php
- [x] Per-season pages under `football/history/` still render
      (LegacyBridge fall-through unbroken)

## Phase-wide gates

- [x] Full suite green from `symfony-app/`: 635 tests, 1971
      assertions, OK
- [x] `bin/console doctrine:schema:validate` — only the pre-existing
      "Unknown database type enum" baseline noise
- [x] No Doctrine migrations needed; dev-DB changes were the titles
      backfill (through the admin tool) and the pending manual data
      fixes
- [x] `/history` reachable from the site nav (`/history/` 301s to
      `/history` via Symfony trailing-slash handling)
- [x] Roadmap updated (Phase 9a → Done); this file records outcomes

## Outcomes

Validation pass run 2026-07-16 against the dev DB with
`php -S 127.0.0.1:8099 -t public public/index.php`.

- All gates green except the two marked [~]: the four data-row
  corrections in `scripts/database/migration/
  2026-07-16-history-data-fixes.sql` (2022 White Division title
  teamid 8→7, championship scores for gameids 1179/1267/1355, 2006
  #1-pick teamid 9→8). **Josh fixes these manually** (decision
  2026-07-16); the pages are already coded to read the corrected
  values, no code change needed when the rows land.
- Deploy checklist: run the data-fixes SQL on prod, deploy code,
  re-save the 2024 and 2025 admin season-flags pages to backfill
  prod titles.
- Discovered for Phase 9b: `2018Season/`–`2023Season/` each carry a
  frozen `teammoney.php`; the ≤2017 ones are named `money.php` or
  absent. The hub's 34 season links and the frozen-snapshot
  `prevLink` chain in `templates/history/teammoney.html.twig`
  (`season >= 2025 ? dynamic : /history/{n}Season/teammoney`) both
  need revisiting when 9b collapses the per-season pages.
