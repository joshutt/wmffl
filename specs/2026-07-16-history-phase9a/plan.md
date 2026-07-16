# Phase 9a — History (non-season-specific): Plan

Branch `history-9a`, one PR, one commit per group. Scope, decisions
and data-model findings in `requirements.md`; merge gates in
`validation.md`.

## 1. History hub

- `HistoryController` (`symfony-app/src/Controller/`) with
  `#[Route('/history', name: 'history_index')]` rendering
  `templates/history/index.html.twig` (directory already exists,
  holds `standings.html.twig`).
- Nav-pill layout ported from `football/history/index.php`: the six
  feature links point at the new Symfony routes (added in later
  groups — fine to emit the route paths now), the 35 season links
  keep their current legacy hrefs (`/history/1992Season.php` …
  `/history/2025Season/`) until 9b. The `paststreaks` entry (already
  `d-none`) is not carried over.
- Legacy `football/history/index.php` is NOT deleted yet (Group 8
  does retirement in one sweep); until then Symfony wins the route
  since LegacyBridge only fires on 404.

## 2. Past champions

- `TeamRepository`: add `getDivisionTitles()` (titles type=Division
  joined to season-correct `teamnames` and `division` era names,
  grouped by divisionid), `getChampionshipGames()` and
  `getToiletBowlGames()` (schedule `championship=1` / `postseason=1
  AND playoffs=0 AND championship=0`, winner/loser + scores via
  season-correct teamnames).
- `HistoryController::pastchamps` at `/history/pastchamps`; League
  Champions list derives from the championship games (winner side).
- Championship MVP names: static season→name const (no DB source),
  lifted verbatim from `pastchamps.php`.
- Template `history/pastchamps.html.twig`: three division tables with
  era-rename header rows driven by `division.startYear`/`endYear`,
  League Champions table (year/winner/score/loser/score/MVP), Toilet
  Bowl table.
- Content check against legacy: 1992–2023 rows must match the
  hardcoded page; 2024–2025 division/league rows will be missing
  until Group 3 backfills `titles` (schedule-derived tables are
  complete immediately).

## 3. Titles admin sync + backfill

- Extend the season-flags save flow
  (`AdminMoneyController::processFlags`) to reconcile that season's
  `titles` rows after the flags flush: champion→League,
  division_winner→Division (division via `teamnames.divisionId`);
  unchecking removes the matching row. Idempotent — saving twice
  changes nothing.
- Backfill 2024 and 2025 by saving those seasons' flags pages through
  the tool (dev + prod deploy note), verifying the created rows match
  the legacy hardcoded 2024/2025 champions. No schema migration —
  `titles` already exists.
- Unit-test the reconciliation (create, update, uncheck-removes,
  idempotence).

## 4. Past drafts

- `DraftPickRepository`: implement `getNumberOnePicks()` — Round=1,
  Pick=1, joined to `players` for name/pos and `nflrosters` as-of the
  pick date for the NFL team at draft time (as-of join pattern from
  `recordsweek.php`), team name via season-correct `teamnames`.
- Static const for the 1992–2004 picks (verbatim from
  `pastdrafts.php`, incl. "Seattle QB"-style entries).
- `HistoryController::pastdrafts` at `/history/pastdrafts` merges
  static + DB rows and computes the two summary tables (picks per
  team, picks per position) from the merged list — legacy hardcodes
  these and is stale at 2023; computing them fixes 2024/2025 for
  free.
- Template `history/pastdrafts.html.twig`.

## 5. All-time records

- `TeamRepository::getAllTimeRecords(int $beforeSeason, string
  $split)` — the `alltimerecords.php` aggregation (games/wins/losses/
  ties + PCT per team across all seasons `< $beforeSeason`), with the
  six split variants (overall, `postseason=0`, `postseason=1`,
  `playoffs=1`, `championship=1`, `postseason=1 AND playoffs=0`) as
  bound/whitelisted variants, not string-interpolated WHERE clauses.
  Inactive teams flagged (legacy renders them italic).
- `HistoryController::alltimerecords` at `/history/alltimerecords`;
  PHP-side sort matches legacy `cmp()` (pct, then games, then wins).
- Template `history/alltimerecords.html.twig`: six cards, tablesorter
  wired the same way `team/roster.html.twig` does it.

## 6. Player records (season + week)

- `PlayerRepository`: `getSeasonRecords(string $pos)` (sum of
  `playerscores.active` per player-season, top 30) and
  `getWeekRecords(string $pos)` (single-week actives with the
  roster/teamnames/nflrosters as-of joins from `recordsweek.php`),
  position bound as a parameter.
- Supplemental pre-2003 records (`$qbList` … `$dbList`, both pages)
  move to constants in `PlayerRecordsService` next to the thresholds
  they duplicate; the controller merges them into the ranked list
  with legacy's top-10-plus-ties cutoff logic (extract that merge
  into `PlayerRecordsService` so both routes share it).
- `HistoryController::recordseason` / `recordsweek` at
  `/history/recordseason` and `/history/recordsweek`; current-season
  rows get the highlight class, same as legacy.
- Templates `history/recordseason.html.twig` /
  `recordsweek.html.twig` (per-position cards, `history.css` styles
  brought into the Twig/base pipeline).

## 7. Team money

- `TeamMoneyService` (`symfony-app/src/Service/`): port
  `moneyUtil.php`'s `getExtraCharges`, `getWins`, `getSeasonFlags`,
  `getLastUpdate` onto the injected DBAL `Connection`, plus the
  ledger assembly currently inline in `teammoney.php` (previous
  balance chain, per-win/per-tie payout math, playoff payouts from
  flags, next-season fee).
- `TeamMoneyController` at `/history/teammoney`, `?season=` with
  `SeasonWeekService` default incl. the pre-August rollover quirk;
  `format()` money-rendering becomes a Twig helper/macro (debt in
  parens styling from `money.css`).
- Template `history/teammoney.html.twig`; the logged-in team's
  PayPal "Pay Now" button carries over (amount from the computed
  balance — legacy hardcodes a per-team array each year; compute it
  instead and note the behavior change in the PR).
- Unit tests for the ledger math on fixture rows.

## 8. Legacy retirement + roadmap

- Delete `football/history/{index,pastchamps,pastdrafts,
  alltimerecords,recordseason,recordsweek,teammoney,paststreaks}.php`
  and `football/history/common/moneyUtil.php`. Everything else under
  `football/history/` stays (9b's content — note
  `common/weekstandings.php` etc. are still included by per-season
  pages).
- `LegacyHistoryRedirectController`: 301s for each deleted URL incl.
  `.php` aliases (pattern from `LegacyStatsRedirectController`),
  carrying `?season=` through for `teammoney`. `paststreaks.php` gets
  no redirect (dropped) — it will 404 like any other unknown legacy
  path.
- Grep sweep for links to the deleted files (menus, quicklinks,
  per-season pages) and repoint them at the new routes.
- Roadmap: mark Phase 9a done, note anything discovered for 9b.
- Full validation pass per `validation.md`, outcomes recorded there.
