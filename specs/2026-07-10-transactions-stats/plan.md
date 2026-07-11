# Plan — Transactions (Phase 4) + Stats (Phase 5)

Single branch `transactions-stats`, one PR. Task groups in order;
transactions first (roadmap order). Each group should leave the app
working (Symfony + LegacyBridge) and be a natural commit boundary.

## 1. Transactions read-only pages

1. `TransactionRepository` (Doctrine DBAL/ORM queries, bound params):
   transaction history by month/year, waiver order, waiver picks,
   waiver awards, protections by season.
2. `TransactionController`:
   - `/transactions` — history with month/year navigation (port
     `transactions.php` rendering: drops, pickups, IR moves, trades as
     sentences; keep player-profile links).
   - `/transactions/waivers` — waiver order + (if logged in) member's
     waiver priority + last week's pickups (`displayWaiverOrder.php` +
     `listwaiverpicks.php`).
   - `/transactions/protections/show` — protections list
     (`showprotections.php`), `season`/`order` params bound.
3. `_transmenu.html.twig` partial replicating `transmenu.php` buttons;
   trade link keeps pointing at the legacy
   `/transactions/trades/tradescreen` until Phase 6.
4. Functional tests for each route.

## 2. Injured Reserve (first write)

1. Port `IRResource` to an `InjuredReserveService` (eligibility rules,
   current IR list, add/remove with date handling).
2. `/transactions/ir` page (login-gated actions, read view public like
   legacy) + JSON endpoints for Add/Remove replacing `updateIR.php`
   (login + CSRF; keep the request/response shape `base/js/injury.js`
   expects, or port that JS alongside).
3. Tests incl. unauthorized (401) and DB state assertions.

## 3. Protections (second write)

1. `/transactions/protections` form (`protections.php`): roster with
   per-position costs, protection years, checkbox selection.
2. POST save (`saveprotections.php`) with login + CSRF, transactional
   insert/replace into `protections`.
3. Replace the hardcoded `2025-08-17` deadline with a data/config-driven
   value (there's precedent in `weekmap`/config tables — investigate).
4. Tests incl. deadline-passed and not-logged-in paths, DB assertions.

## 4. Roster add/drop (the big write)

1. `/transactions/list` — player search form (port `list.php` filters:
   position, NFL team, name, availability; bound params) feeding a
   selection into the confirm flow.
2. Port `confirm.php` into a `RosterMoveService` + controller:
   validation (roster limits 25 active / 26 total, waiver-period rules,
   player availability), preview → confirm → execute shape, writes to
   `roster` + `transactions` in a DB transaction, login + CSRF.
3. This group is the riskiest — port behavior as-is, write tests for
   every branch of the validation before wiring the execute step.

## 5. Legacy transactions deletion + redirects

1. Delete migrated files from `football/transactions/`, plus
   `draftorder/` (obsolete word game) and `injury/` (unlinked).
   **Keep `trades/` fully working**: first check which shared includes
   trades uses (`transmenu.php`, `utils/`, `IRResource`?) and leave
   those in place.
2. 301 redirects for every removed URL incl. `.php` aliases (pattern:
   `LegacyTeamRedirectController` from Phase 3).
3. Update nav/quicklinks references; verify trades pages still render
   via LegacyBridge.

## 6. Stats core: leaders + player stats + CSV exports

1. `StatsRepository`: leaders (points by team/position), player stats
   per position (the `posMap` column sets), CSV variants.
2. `/stats/leaders` (main-nav "Stats" target), `/stats/players`
   (`playerstats.php` with `pos`/`sort`/`season`), `/stats` index page.
3. CSV endpoints replacing `statcsv.php` and `playerlist.php` (Symfony
   `Response` with `text/csv`, bound params — statcsv shares the
   playerstats query base, extract once).
4. Tests: HTML routes + CSV content shape.

## 7. Stats: week-by-week + power rankings + luck

1. Port the potential-vs-actual points calculation (shared by
   `powerrate.php`/`powerlist.php`, `weekbyweek.php`, `luck.php`) into
   one service — three pages, one computation core.
2. Routes: `/stats/weekbyweek` (by-team/by-pos selector from
   `weekbyweekinc.php`), `/stats/power`, `/stats/luck`.
3. Tests for the service math (this is where regressions would hide)
   plus route smoke tests.

## 8. Stats: records + injury report

1. `/stats/records` (`playerrecord.php`) and `/stats/lastplayer`
   (`lastplayer.php`): port with their hardcoded record-threshold
   arrays as PHP constants/fixtures (note future data-driving in the
   code, don't build it now).
2. `/stats/injuries`: port `InjuryReportResource` to a service;
   Current/Full tabs, tablesorter kept.
3. Tests.

## 9. Legacy stats deletion + redirects

1. Delete `football/stats/`: migrated pages plus dead ones (`info.php`,
   `standings.php`, `teamcompare.php`). Resolve `weekstandings.php`
   inbound includes first (history season pages use
   `history/common/weekstandings.php` — a different file; verify
   nothing else includes the stats one).
2. 301 redirects for all URLs incl. `.php` aliases and old query
   params (`/stats/leaders?season=` links exist on history pages).
3. Update main-nav "Stats" link and stats-index "Accounting" link
   (points at legacy `teammoney` — leave that URL as-is).

## 10. Wrap-up

1. Full validation pass per `validation.md` (tests, E2E, DB checks,
   redirect sweep, trades-still-works regression).
2. Mark Phases 4 and 5 done in `specs/roadmap.md` (move to Done with
   date + spec pointer, matching the existing entries' style).
