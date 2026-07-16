# Phase 9a — History (non-season-specific): Validation

How to know the implementation succeeded and the PR can be merged.
Automated tests run from `symfony-app/` (`vendor/bin/phpunit tests/`);
E2E against `php -S 127.0.0.1:8088 -t public public/index.php` (the
explicit router argument matters — without it `.php` legacy routes
404 instead of reaching LegacyBridge), with the fake-session recipe
from earlier phases where a logged-in team is needed (teammoney's
Pay Now button).

Content-parity technique (from Phase 7): curl the legacy page off
`main` (`git show main:football/history/<file>` served via a main
checkout, or capture before deletion) and diff the rendered tables
against the new route.

## Per-group gates

Every commit: suite green, CSRF on any new POST, no interpolated SQL,
no new functionality added to `/football/`.

### 1. History hub
- [ ] `/history` renders all six feature links + 35 season links
- [ ] Season links still resolve through LegacyBridge (spot-check
      `/history/1992Season.php`, `/history/2005Season/schedule.php`,
      `/history/2025Season/`)

### 2. Past champions
- [ ] 1992–2023 division/league/toilet rows match the legacy page
      exactly (era-rename headers included)
- [ ] Championship + toilet tables show winner/loser/scores straight
      from `schedule`; 2023 toilet game = Amish Electricians 51–0
      (cross-checked vs `titles` during design)
- [ ] MVP column matches legacy for every listed season
- [ ] Unit: repository methods return season-correct team names (a
      team renamed between seasons shows its name *that* season)

### 3. Titles admin sync + backfill
- [ ] Unit: flags save creates League/Division titles; unchecking
      removes them; re-saving is a no-op
- [ ] Dev DB: saving 2024 and 2025 flags pages produces titles rows
      matching the legacy hardcoded 2024/2025 champions; pastchamps
      page now complete through 2025
- [ ] ≤2023 titles untouched (row counts unchanged: League 32+2,
      Division 77+n, Toilet 25 — toilet is never synced)
- [ ] Deploy note in PR: run the same two flag saves on prod after
      deploy

### 4. Past drafts
- [ ] 1992–2004 rows match legacy verbatim (static const)
- [ ] 2005–2023 DB-derived rows match the legacy page (name, pos,
      NFL team at draft time, WMFFL team name for that season)
- [ ] 2024/2025 rows appear (new — absent from the stale legacy page)
- [ ] Summary tables (per-team, per-position) recompute to legacy's
      values for 1992–2023 data plus the new seasons

### 5. All-time records
- [ ] All six splits match the legacy page for every team (spot-check
      full-table diff on at least overall + toilet bowl)
- [ ] Inactive teams italicized; sort order identical (pct → games →
      wins, descending)
- [ ] tablesorter works on each card

### 6. Player records
- [ ] Both pages match legacy output for every position card,
      including supplemental pre-2003 entries interleaved at the
      right ranks and the top-10-plus-ties cutoff
- [ ] Current-season highlight class applied to current-season rows
- [ ] Unit: merge/cutoff logic (supplemental entry ties, boundary at
      rank 10)

### 7. Team money
- [ ] Current season output matches legacy `teammoney.php` for every
      team and column (ledger math ported, not re-derived)
- [ ] `?season=` back-navigation works for a handful of past seasons;
      pre-August rollover shows last season by default
- [ ] Pay Now button appears only for the logged-in team with a
      positive amount due, links the computed balance (fake-session
      E2E); behavior change vs legacy's hardcoded array noted in PR
- [ ] Unit: ledger math on fixtures (wins/ties payout split, playoff
      payouts from flags, debt rendering)

### 8. Legacy retirement
- [ ] Each deleted URL 301s to its new route, `.php` aliases
      included; `teammoney.php?season=2022` carries the param
- [ ] `paststreaks.php` returns 404 (no redirect, dropped)
- [ ] Grep: no remaining references to the deleted files anywhere in
      `football/` or `symfony-app/` (menus, quicklinks, per-season
      pages repointed)
- [ ] Per-season pages under `football/history/` still render
      (LegacyBridge fall-through unbroken)

## Phase-wide gates

- [ ] Full suite green from `symfony-app/`
- [ ] `bin/console doctrine:schema:validate` — only the pre-existing
      "Unknown database type enum" baseline noise, nothing new
- [ ] No schema migrations were needed (this phase adds none); dev DB
      row-count check on `titles` per Group 3 above
- [ ] `/history` hub reachable from the site nav exactly where the
      legacy link was
- [ ] Roadmap updated; this file updated with outcomes per gate

## Outcomes

(recorded at validation pass)
