# Requirements — Transactions (Phase 4) + Stats (Phase 5)

Branch: `transactions-stats`. One PR covering both phases, transactions
first (roadmap order). See `plan.md` for task groups and `validation.md`
for merge criteria.

## Goal

Migrate `football/transactions/` (except `trades/`) and all of
`football/stats/` to Symfony controllers, then delete the legacy
directories with 301 redirects, per the mission in `specs/mission.md`.

## Scope decisions (agreed 2026-07-10)

1. **Transactions scope: reads + all non-trade writes.** Transaction
   history, waiver order/picks, protections display, IR add/remove,
   protections save, and roster add/drop (`list.php` → `confirm.php`)
   are all in this spec.
2. **Trades are OUT.** `football/transactions/trades/` gets its own
   roadmap phase (Phase 6) and keeps working via the LegacyBridge until
   then. Do not break it: check what shared includes it uses
   (`transmenu.php`, `utils/*`) before deleting anything.
3. **Draft-order word game is deleted, not migrated.**
   `football/transactions/draftorder/` (word-submission lottery) is
   obsolete. Its only inbound links are archival 2008/2009 history pages;
   those may 404 until the history phase.
4. **Stats scope: roadmap six + index-linked extras + CSV exports.**
   Migrate `playerstats`, `leaders`, `powerlist`/`powerrate`,
   `weekbyweek`, `injuryReport`, plus `luck`, `playerrecord`,
   `lastplayer`, and the CSV endpoints `statcsv` and `playerlist`.
5. **Dead stats pages are deleted, not migrated:** `info.php` (a
   `phpinfo()` dump — security liability), `standings.php` (hardcodes
   season 2009), `teamcompare.php` (no inbound links), and
   `football/transactions/injury/` (self-contained older injury-report
   variant, no inbound links).
6. **Validation level:** PHPUnit functional tests + fake-session manual
   E2E + 301 checks + explicit before/after DB state checks for every
   write flow. See `validation.md`.

## Context

### Phase 4 — Transactions (`football/transactions/`)

| Legacy file | What it does | Disposition |
|---|---|---|
| `transactions.php` | Transaction history by month/year (drops, pickups, IR, trades rendered as sentences) | Migrate (read) |
| `displayWaiverOrder.php` + `listwaiverpicks.php` | Waiver order for current week, member's waiver priority (login), last week's awards | Migrate (read) |
| `showprotections.php` | Protections list by season/team | Migrate (read) |
| `list.php` | Player search + selection form for roster moves | Migrate (read, feeds add/drop) |
| `confirm.php` (~19KB) | Roster add/drop processing: validation, roster limits (25 active / 26 total), waiver rules, writes to `roster`/`transactions` | Migrate (write) |
| `injuredReserve.php` + `IRResource.php` + `updateIR.php` | IR page + AJAX add/remove (JSON, login-gated) | Migrate (write) |
| `protections.php` + `saveprotections.php` | Protection selection form + save (login-gated, per-position costs, deadline logic — note hardcoded `2025-08-17` deadline in `protections.php`; make it data-driven or config) | Migrate (write) |
| `transmenu.php` / `transmenu.html` | Shared nav across transaction pages | Twig partial; legacy copy must keep working for `trades/` until Phase 6 |
| `trades/` (whole dir) | Trade workflow | **Out — Phase 6** |
| `draftorder/` | Word-game lottery | **Delete** |
| `injury/` | Unlinked older injury report | **Delete** |
| `index.php` | Redirect/stub | Delete with the directory |

### Phase 5 — Stats (`football/stats/`)

| Legacy file | What it does | Disposition |
|---|---|---|
| `leaders.php` | Team points by position (the main-nav "Stats" link target) | Migrate |
| `playerstats.php` | Per-position player stat tables (`pos`, `sort`, `season` params, position→columns map) | Migrate |
| `weekbyweek.php` + `weekbyweekinc.php` | Weekly scores by team or position | Migrate |
| `powerrate.php` / `powerlist.php` | Power rankings from potential vs. actual points | Migrate |
| `injuryReport.php` + `InjuryReportResource.php` | IR/injury tables, tablesorter, Current/Full tabs | Migrate |
| `luck.php` | Luck ratings (potential wins vs. actual) | Migrate |
| `playerrecord.php` | Player records vs. historical thresholds (hardcoded record arrays — port as-is, note for future data-driving) | Migrate |
| `lastplayer.php` | Similar record-threshold page (not index-linked but in scope by decision #4) | Migrate |
| `statcsv.php` | CSV export of player stats (shares `playerstats` query logic) | Migrate as CSV response |
| `playerlist.php` | Plain-text CSV of active player scores | Migrate as CSV response |
| `info.php`, `standings.php`, `teamcompare.php` | Dead/debug | **Delete** |
| `weekstandings.php` | Already Symfony-backed (uses `App\Model\Team` + `StandingsCalculatorService`); check inbound includes (`history/*/standings.php` uses `common/weekstandings.php`, a different file) before removing | Resolve during implementation |
| `index.php` | Button index to the stats pages | Migrate as `/stats` index or fold links into nav |

### Shared/technical context

- **Tables touched:** `transactions`, `roster`, `waiverorder`,
  `waiverpicks`, `waiveraward`, `protections`, `protectioncost`,
  `positioncost`, `injuredreserve` (via IRResource), `newplayers`,
  `teamnames`, `team`, `weekmap`, `playerscores`, `revisedactivations`,
  `season_flags`, `trade` (read-only here, for history rendering).
- **Auth:** legacy uses `$isin`/`$teamnum` session vars. Symfony side has
  the session/auth pattern from Phases 1–3 (navbar auth Twig global,
  fake-session E2E recipe). All write endpoints must be login-gated and
  CSRF-protected (see the Phase 2 session/CSRF POST fix).
- **Current season/week** come from `weekmap`/config (legacy
  `utils/start.php`); Symfony equivalent already exists
  (`ConfigRepository`).
- **Frontend:** server-rendered Twig, keep tablesorter where legacy used
  it, small vanilla JS only (per `specs/tech-stack.md`). IR add/remove is
  AJAX/JSON in legacy — keep that shape.
- **Bound parameters everywhere.** Legacy pages interpolate request
  params into SQL (`showprotections.php` `$season`, `statcsv.php`
  params, etc.); the migration closes these injection holes like
  Phase 3 did for `/teams/compare`.
- All new SQL goes through repositories (`App\Repository`), business
  logic in services (pattern: `StandingsCalculatorService`).

## Non-goals

- Trades workflow (Phase 6).
- `teammoney.php` / accounting (history phase; the stats index links to
  it — point that link at the existing legacy URL for now).
- Redesigning scoring/waiver business rules — port behavior as-is,
  fixing only outright bugs (document any found).
- Admin tooling beyond what legacy had: these pages are member-facing;
  existing admin pages already cover player/team management. Roster,
  IR, and protection writes are member self-service and are the admin
  surface here.
