# Roadmap

Implementation proceeds one feature area at a time: pick an area, migrate it
fully to Symfony in small phases, then move to the next area. Each phase
should be small enough to land as its own PR.

## Done

- Standings: `StandingsController`, `StandingsCalculatorService`
- History standings: `HistoryStandingsController` (`/history/standings/{season}/{week}`)
- Team model migrated to `App\Model\Team`
- Articles read-only display: `ArticleController` (`/article/{id}`, `/articles`,
  301s from legacy URLs), `ArticleRepository`, homepage migrated to
  `HomeController` (`/`, incl. scores/standings/trash-talk/quicklinks widgets,
  `ScoresRepository`), admin article management (`AdminArticleController`)
- Articles publishing + comments (Phase 1 complete, 2026-07,
  `specs/2026-07-06-articles-publish-comments/`): member publish flow
  (`ArticlePublishController` — write → preview → Edit/Publish, edit-in-place),
  last-edited tracking, threaded article comments (`CommentRepository`,
  `article_comment` route), admin moderation (`AdminCommentController`);
  `football/article/` deleted — every article route is Symfony-served
- Player profiles (Phase 2 complete, 2026-07,
  `specs/2026-07-07-player-profiles/`): `Player` entity + `PlayerRepository`
  (roster/history/season-stat/search queries), `PlayerProfileController`
  (`/player/{id}` profile, `/players` searchable index, "Players" main-nav
  link), legacy team pages (`roster.php`, `compareteams.php`) link player
  names to profiles, admin player editing (`AdminPlayerController`,
  `/admin/players`). Note: `symfony-app/public/players.html` was NOT retired —
  it's a Live Draft Board prototype, not a player list; see the
  draft-tooling note below.
- Team pages (Phase 3 complete, 2026-07,
  `specs/2026-07-08-team-pages/`): `TeamRepository` + `TeamController` —
  `/teams` division-card index (added beyond the original phase scope) with
  the static Squirrels page, `/team/{id}/roster` (tablesorter kept),
  `/team/{id}/schedule` with past-season selector and a new head-to-head
  entry point (`?vs=`, all-time; the legacy `viewseasom` typo filter was
  dead and is gone), `/team/{id}/history`, `/teams/compare` (bound params —
  closes the legacy SQL injection); admin editing stays on the existing
  Team Info page (`/admin/team/updateTeamInfo` — a separate `/admin/teams`
  list+edit was built then dropped as duplicate functionality).
  `football/teams/` deleted with 301 redirects
  (`LegacyTeamRedirectController`) resolving `viewteam` as id, abbrev, or
  space-stripped name, incl. `.php` aliases for archival links.
- Transactions (Phase 4 complete, 2026-07-10,
  `specs/2026-07-10-transactions-stats/`): `TransactionController`
  (`/transactions` history with trade sentences, `/transactions/waivers`,
  `/transactions/protections/show`), `InjuredReserveController`
  (`/transactions/ir` + CSRF-protected JSON add/remove),
  `ProtectionsController` (form + save; deadline moved from a hardcoded
  date to the config key `protections.deadline`), `RosterMoveController` +
  `RosterMoveService` (`/transactions/list` search →
  `/transactions/confirm` preview/execute with the 25-active/26-total
  limits, waiver-priority handling, entry-fee gate).
  `football/transactions/` deleted with 301s
  (`LegacyTransactionRedirectController`) except `transmenu.php` (still
  included by trades) and `trades/` (Phase 8, still on the LegacyBridge);
  `draftorder/` (word game) and `injury/` (unlinked report) deleted
  without replacement.
- Stats (Phase 5 complete, 2026-07-10,
  `specs/2026-07-10-transactions-stats/`): `StatsController` +
  `StatsRepository` (`/stats` index, `/stats/leaders`, `/stats/players`
  with html/ajax/csv/json formats replacing `statcsv.php`,
  `/stats/playerlist` text feed), `WeekByWeekService`,
  `PowerRatingService` (one potential-vs-actual core for
  `powerrate`/`powerlist`), `LuckService`, `PlayerRecordsService`
  (`/stats/records`, `/stats/lastplayer` — the legacy file had a parse
  error and never rendered), `InjuryReportService` (`/stats/injuries`).
  `football/stats/` deleted with 301s (`LegacyStatsRedirectController`,
  query-param carry for history `?season=` deep links); dead pages
  (`info.php` phpinfo dump, 2009-hardcoded `standings.php` +
  `weekstandings.php`, `teamcompare.php`) deleted, their URLs land on
  `/stats`.
- Table cleanup (Phase 6 complete, 2026-07-12,
  `specs/2026-07-12-table-cleanup/`): dropped the three superseded legacy
  tables plus two scratch tables. `activations` → the 2005/2006 boxscore
  pages rewritten to the tall `revisedactivations` join (byte-identical
  output). `players` → the six frozen 2003–2008 history pages ported to
  `newplayers` (also un-broke them: MySQL 5+ join-precedence and PHP 8
  undefined-variable rot had all six returning 500s), orphaned
  `public/images/players/playerstats.php` deleted. `injuries` (2010–2019,
  73,482 rows) **merged into** `newinjuries` (status letters → words,
  `details` widened to varchar(50)) before the drop — 130,457 rows total,
  per-status counts verified. `tmp_players`/`tmp_scan` dropped. Dead
  `App\Entity\{Activation,Injury}` + `App\Enum\InjuryStatusEnum` deleted.
  Full-schema keep/drop audit in that spec's `audit.md`; the rename back
  to canonical short names is deferred to Phase 7.
- Trades (Phase 8 complete, 2026-07-13, `specs/2026-07-13-trades/` —
  built against the pre-Phase-7 table names, since swept): `TradeController` (`/trades` screen,
  `/trades/offer` builder shared by new/amend/counter with owned-pick and
  points-balance pickers, `/trades/offer/confirm` preview→submit,
  `/trades/respond/{id}` accept/reject/withdraw), `TradeOfferRepository`
  (read-time 7-day expiry, LastOfferID team-id quirk isolated,
  transactional `saveOffer`), `TradeValidationService` (replaces
  `checkambigous` and the unfinished ambiguous-pick flow),
  `TradeExecutionService` (accept re-validation + the whole execution in
  ONE transaction; auto-reject of stale offers), `TradeMailer`
  (offered/accepted/rejected/voided, symfony/mailer, `null://` in dev).
  New: stored trade comments (`offercomments` table +
  `offer.PrevOfferID` chain, migration Version20260713000000) shown as
  negotiation history on `/trades` and `/admin/trades`; commissioner
  oversight (`AdminTradeController` — status filter, void with reason +
  email); withdrawals now write `Withdrawn` (legacy wrote `Reject`).
  `football/transactions/` deleted entirely (trades/ was its last
  content) with 301s; transmenu partial points at `/trades`.
- Table renames (Phase 7 complete, 2026-07-14,
  `specs/2026-07-14-table-renames/` — deferred from Phase 6, landed
  after Phase 8): reclaimed the canonical names freed by Phase 6's
  drops. One combined migration (`Version20260714000000`) renames
  `revisedactivations`→`activations`, `newplayers`→`players`,
  `newinjuries`→`injuries` atomically and re-creates the two FK
  constraints as `FK_injuries_players` / `ir_players_playerid_fk`;
  code swept one table per commit (31/75/9 files across `symfony-app`,
  `football/`, `scripts/` incl. the Python injury feeds).
  `App\Entity\RevisedActivation`→`Activation`,
  `App\Entity\NewInjury`→`Injury`; `schema.sql` regenerated. Index
  names still carry old-name prefixes (cosmetic, out of scope).
  Deploy: migration + code together in one maintenance window.
- History non-season-specific (Phase 9a complete, 2026-07-16,
  `specs/2026-07-16-history-phase9a/`): `HistoryController` —
  `/history` hub (season links still legacy until 9b),
  `/history/pastchamps` now data-driven (division titles from
  `titles` with era-correct division names, championship/toilet-bowl
  tables derived from `schedule`; MVP names stay a hardcoded map — no
  DB source), `/history/pastdrafts` hybrid (1992–2005 static const,
  2006+ from `draftpicks`, both summaries computed — page was frozen
  at 2023, now updates itself), `/history/alltimerecords`
  (whitelisted six-split query, exact legacy sort incl. tie order),
  `/history/recordseason` + `/history/recordsweek` (bound-parameter
  top-30 queries; pre-2003 supplemental records moved next to
  `PlayerRecordsService`'s matching thresholds; the two pages'
  divergent cutoff quirks ported and unit-tested).
  `TeamMoneyService`/`TeamMoneyController` (`/history/teammoney`)
  port the dynamic ledger + `moneyUtil.php`; the duplicate dynamic
  copy `2024Season/teammoney.php` was retired with it (301 →
  `?season=2024`); frozen ≤2023 snapshots stay for 9b.
  `TitleSyncService`: admin season-flags saves now reconcile `titles`
  (champion→League, division_winner→Division), backfilled 2024/2025.
  `paststreaks.php` dropped (hidden, frozen at 2015). Legacy
  top-level files deleted with 301s
  (`LegacyHistoryRedirectController`). Data fixes in
  `scripts/database/migration/2026-07-16-history-data-fixes.sql`
  (2022 White Division title teamid, 2009–2011 championship scores,
  2006 #1-pick franchise) — **run on prod at deploy, then re-save the
  2024/2025 admin flags pages**.
- Dynamic quicklinks & draft date scheduling (Phase 10 complete,
  2026-07-17, `specs/2026-07-17-quicklinks-draftdates/`). Part A:
  `QuickLink` entity + `quicklinks` table (label, url, nullable
  inclusive start/end window, active, sort order; migration
  Version20260717000000 seeds the three former static links — literal
  season URLs, the admin edits seasonal ones yearly),
  `QuickLinkRepository::findVisible()`, homepage widget now DB-driven
  with the whole card hidden when nothing is visible, admin CRUD
  (`AdminQuickLinkController`, `/admin/quicklinks` — list with
  visible-today indicator, add/edit, activate/deactivate, delete);
  `football/quicklinks.php` deleted. Part B: `DraftScheduleService` —
  admin schedule builder on `/admin/draftdates` (range picker →
  checkbox calendar, Sat/Sun default-checked on fresh seasons,
  existing dates pre-checked on re-runs; merge keeps every cast vote
  and `lastUpdate`, fills in new owners, deletes deselected dates; a
  season's schedule = draftdate rows in its July 1 – Oct 1 window) and
  member vote page `/draftdate` (`DraftDateController` — default-Yes
  radios, max-4-"No" rule ported from `processdraftdate.php`, valid
  submit stamps `draftvote.lastUpdate`). All 26 per-season
  `draftdate.php`, `common/processdraftdate.php`, and the 16
  per-season `processdraftdate.php` copies deleted, no redirects
  (only archival newsletter links pointed at them).
- Season Rules foundation (pre-Phase-11/12, 2026-07-18,
  `specs/2026-07-18-season-rules/`). Per-season league rules moved
  from hardcoded constants to a `seasons` table (`Season` entity):
  typed structure/finance columns + `scoring_rules` JSON +
  `scoring_strategy` seam, seeded 1992–2026 with current rules and
  the known FG60=10-through-2023 delta (migration
  Version20260718000000). `ScoringRuleRegistry` defines every scoring
  parameter once (drives DTOs, the admin form and scorer labels);
  `SeasonRuleService` (cached, missing-row-safe) feeds
  `PlayerScorerService` — the single scoring engine, emitting labeled
  `ScoreLine[]` for Phase 12 box scores, golden-tested equivalent to
  legacy `scoring.php` over all 451k stat rows (and fixing the old
  Symfony recalc's OL strict-compare bug). ScoreCalculatorService,
  TeamMoneyService (constants deleted), the six `week<=14` hardcodes
  and the 25/26 roster limits are all season-driven. Admin
  `/admin/seasons`: list/edit every season (scoring form generated
  from the registry, blank = not awarded), per-team transpoints
  budgets, effective positioncost rows + start-a-new-cost flow,
  notes/verified workflow, clone-latest button; reprocess page warns
  before overwriting historical scores. Historical rule backfill is
  Josh's ongoing task via that UI. Known pre-existing limitation:
  recalculating old weeks mis-penalizes players who changed NFL teams
  (current-roster join) — reprocess remains a current-week tool.

## Phase 11 — History (per-season)

Legacy: `football/history/{year}Season.php` (1992–2017, frozen flat
pages) and `football/history/{year}Season/` (1992–2026, directories —
old ones mostly redirect to the flat file above but hold real
subpages; 2018+ have no flat-file counterpart)

Scope recorded, design deferred until 9a lands. `/history/{season}Season/standings`
(`history_season_standings`) already covers per-season standings, so
the remaining surface is: the season hub/index pages themselves (each
with hardcoded playoff-result blurbs — champion, runner-up, scores),
`schedule`, `draftresults`, `draftdate`, `draftorder`,
`protectioncost`, `seasonposition`, the frozen `teammoney`/`money`
snapshots, and the old-season-only one-offs (`awards`, `newsletters`,
`breakdown`, `championpreview`, `playoffexplain`/`preview`/`scenewk*`,
`summary*.inc`, `weeklyscores`, `weeksummary`). Boxscores
(`{year}Season/boxscores.php`, 2005/2006 only) are explicitly **not**
in scope — that's Phase 12.

1. Design a data model for the per-season hub content (champion,
   runner-up, playoff scores) currently hardcoded per file
2. A single generic Symfony route/template driven by that data,
   replacing the 30+ individual season files and directories

## Phase 12 — Boxscores redesign

Legacy: the live box score page is `football/activate/currentscore.php`
(+ `scoreFunctions.php`, `base/scoring.php`) — addressed by
`teamid`/`season`/`week`, renders both teams' activated lineups with
per-player stat lines, and handles in-progress games (live scoring, time
remaining, reserves). Deep-linked from the Symfony homepage scores widget
(`templates/home/_scores.html.twig`), legacy `scores.php`, and
`base/scores.php`; its only "other game" navigation is an on-page
team/week picker form. Also two frozen per-season pages
(`football/history/2005Season/boxscores.php`, `2006Season/boxscores.php`).
This is a port-and-redesign of `currentscore.php`, re-keyed by game, that
**deliberately splits the two roles that page serves today**: historical
box scores and the live scoreboard become separate routes and, over time,
two different experiences.

1. Historical box score (`/game/{gameid}`): make every completed game in
   history reachable by its `schedule.gameid`, plus a browse path
   (season → week → game) replacing the on-page team/week picker. Port
   the `currentscore.php`/`scoreFunctions.php` rendering — both teams'
   activated lineups with per-player points and stat lines, team totals,
   final score/overtime — with **no live-scoring logic**: this view is
   final-result only
2. Live scoreboard (separate route, e.g. `/scoreboard`): the in-progress
   current-week experience — live scoring, time remaining, reserves —
   ported from `currentscore.php` as its own page. Phase 12 ports it
   as-is to establish the split; its own redesign (auto-refresh, richer
   game-day experience) is deferred to the Unscheduled section
3. Week scoreboard on the box score page: the other games from the same
   season/week shown alongside (new — today that list only exists on
   `scores.php`), each linking to its own box score; current-week games
   link to the live scoreboard instead
4. Schedule integration: link each game on the schedule pages
   (`/team/{id}/schedule`) to its box score — completed games to
   `/game/{gameid}`, current-week in-progress games to the live
   scoreboard, future games get no link
5. Retire `football/activate/currentscore.php` with a 301 that routes by
   game state: completed `teamid`+`season`+`week` combos map to the
   gameid route, current-week to the live scoreboard; update the three
   deep-linking entry points. The rest of `football/activate/` (lineup
   submission flow) stays for Phase 13
6. Phase 7 table renames are done (final `activations`/`players`/
   `injuries` names in place); data coverage varies by era — degrade
   gracefully for seasons missing stat lines

## Phase 13 — Remaining odds and ends

Legacy: `login/`, `activate/`, `forum/`, `rules/`, `info.php`,
`scores.php`

1. Auth (login/activate) — highest risk, do last and carefully
2. Static/low-traffic pages (rules, info, forum)
3. Scores

## Unscheduled — Live scoreboard redesign

Phase 12 splits the live scoreboard out of `currentscore.php` onto its own
route as a faithful port. Its actual redesign — a richer game-day
experience (auto-refresh/streaming scores, in-progress stat lines,
whatever else game day wants) — happens here, decoupled from the
historical box score, which stays a static final-result page.

## Unscheduled — Draft tooling

`symfony-app/public/players.html` is a "Live Draft Board with Announcer"
prototype (Tailwind CDN + AWS Polly), not a player list. When draft tooling
gets built, port or retire it then; it serves at `/players.html` and does
not conflict with the `/players` index.

## Final phase — Decommission legacy

1. Remove `LegacyBridge` fallback
2. Delete `/football/`, legacy `bootstrap.php`, legacy Doctrine setup,
   `conf/db.ini`
3. Drop `ext-mysqli` and legacy Doctrine deps from root `composer.json`
