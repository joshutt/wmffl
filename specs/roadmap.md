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
- Season Rules foundation (pre-Phase-14/15, 2026-07-18,
  `specs/2026-07-18-season-rules/`). Per-season league rules moved
  from hardcoded constants to a `seasons` table (`Season` entity):
  typed structure/finance columns + `scoring_rules` JSON +
  `scoring_strategy` seam, seeded 1992–2026 with current rules and
  the known FG60=10-through-2023 delta (migration
  Version20260718000000). `ScoringRuleRegistry` defines every scoring
  parameter once (drives DTOs, the admin form and scorer labels);
  `SeasonRuleService` (cached, missing-row-safe) feeds
  `PlayerScorerService` — the single scoring engine, emitting labeled
  `ScoreLine[]` for Phase 16 box scores, golden-tested equivalent to
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
- Logging infrastructure (Phase 10.5 complete, 2026-07-26,
  `specs/2026-07-26-logging-infrastructure/`): `symfony/monolog-bundle`
  added; `config/packages/monolog.yaml` `when@prod` handler is
  `rotating_file` at `logs/wmffl.log` (Monolog derives its own dated
  filenames, e.g. `logs/wmffl-2026-07-26.log`), `max_files: 14`,
  `error` level minimum, active regardless of `APP_DEBUG`. Legacy
  `error_log()` writers (`front_controller.php`, `LegacyBridge.php`)
  deliberately left untouched — separate, unrotated channel into the
  literal `logs/wmffl.log`, same as before, just no longer sharing a
  filename with Monolog's dated output (accepted tradeoff, not
  revisited unless `/football/` log volume becomes a problem before
  the Final phase deletes it). `AdminMoneyController::recordChange`'s
  swallowing `catch (\Exception $e)` broadened to `catch (\Throwable
  $e)`, now logs before returning the error response. Verified locally
  via a forced error producing a new dated file without touching the
  legacy file; prod write-access/rotation verification is Josh's
  post-deploy step.
- Rule proposals (Phase 10.6 complete, 2026-07-27,
  `specs/2026-07-27-rule-proposals-issues/`, PR #54): `issues` redesigned
  and the full member/admin/ballot UI built. The original plan was
  data-layer-only prep for the Phase 17 `rules` item; the delivered scope
  went further, replacing the hand-written `proposals{year}.php` pages
  outright. Migration `Version20260727020000`: widened `IssueName`, added
  `Rationale`/`RuleChangeText`/`Published` + a `Status` enum
  (Open/Passed/Rejected/Withdrawn, backfilled from the legacy `Result`),
  dropped legacy `Sponsor`/`Result`; new `issue_sponsors` ordered
  co-sponsor join; `seasons` proposal pass/fail thresholds (.67 pre-2022,
  .51 from 2022); `ballot` FK integrity. Member: `/rules/proposals`
  season-by-season list (CommonMark-rendered), `/rules/proposals/submit`
  (pending issue + commissioner email), `/rules/ballot` voting with
  per-season thresholds (injection-safe writes). Admin: `/admin/proposals`
  CRUD/approve/withdraw/put-on-ballot with in-place co-sponsor
  reconciliation (fixes an `IssueSponsor` identity-collision on edit) and a
  For-Against-Abstain-NoVote tally column for decided proposals;
  `AdminBallotController` reduced to a read-only tally; season-threshold
  editing on `/admin/seasons`. `MarkdownService` (CommonMark,
  `html_input => escape`) + `markdown_to_html` Twig filter;
  `ProposalPageParser` preserves `<br>` line breaks and `&nbsp;`
  indentation and strips block-spanning italics. Historical backfill:
  `app:backfill-proposals` parses the archived pages into idempotent SQL
  (38 inserts / 149 enriched / 0 unresolved / 0 conflicts); the 27
  `proposals{year}.php` pages moved to `archive/proposals/` (the command's
  default input); legacy `propose*`/`ballot*` stubs deleted and
  301-redirected (`LegacyRulesRedirectController`). Deploy: run migration
  `Version20260727020000`, then apply
  `scripts/database/migration/2026-07-27-issues-backfill.sql` (regenerate
  per-env recommended), then confirm `MAILER_DSN` in prod `.env.local`.
- Proposals formatting fixes (Phase 11 items 1-2 complete, 2026-07-31,
  `specs/2026-07-31-proposals-formatting/`, branch
  `phase11-proposals-formatting`): `MarkdownService` now sets CommonMark's
  `soft_break => "<br>\n"` and normalizes leading whitespace to `&nbsp;`
  before rendering, so a member/admin typing directly into the
  `Rationale`/`RuleChangeText` fields gets line breaks and indentation
  that match the imported (Phase 10.6) content instead of having single
  newlines collapsed; submit-form live-preview JS updated to match.
  `templates/proposals/list.html.twig` now shows `Rationale` instead of
  the short `Description`; `ballot.html.twig` confirmed already
  `Description`-only. New `MarkdownServiceTest` cases +
  `tests/Template/ProposalTemplateTest.php`; 793 tests green.
- Article comment-count badges + login-modal buttons (Phase 11 items 3-4
  complete, 2026-07-31, `specs/2026-07-31-comments-login-polish/`, branch
  `phase11-proposals-formatting`, PR #55): `CommentRepository::countByArticleIds()`
  batch-counts active comments per article, wired through
  `ArticleController::list`/`HomeController::index` into a `counts` map that
  `article/_card.html.twig` reads via Twig's inherited include context —
  every card always shows a `💬 N` badge, including 0. New shared
  `templates/_login_required.html.twig` partial (message + a `btn-wmffl`
  button opening the existing navbar `#loginModal`) swapped into all ten
  gated "must be logged in" pages (transactions
  protections/ir/list/confirm, draftdate, proposals ballot/submit, trades,
  article publish); `ArticleController::addComment`'s logged-out flash
  confirmed sufficient as-is (renders on a page where the navbar's login
  button is already visible), no code change there. New
  `ArticleCardTemplateTest` + `LoginRequiredTemplateTest` (real Twig
  renders, not mocked) pin the actual badge/button markup; 812 tests
  green. Phase 11 complete.
- Admin tooling (Phase 12 complete, 2026-07-31,
  `specs/2026-07-31-admin-tooling/`, branch `phase12-admin-tooling`):
  `AdminQuickLinkController::reorder()` (`POST /admin/quicklinks/reorder`)
  rewrites `sortOrder` sequentially from a client-posted ordered id list;
  the admin index (`templates/admin/quicklinks/index.html.twig`) gets
  SortableJS-powered drag-and-drop rows (SortableJS pulled in via CDN,
  matching the existing jQuery/Bootstrap CDN-include pattern rather than
  adding a build pipeline); the manual `sortOrder` field is removed from
  the edit form — the index's drag-and-drop is now the only way to
  reorder. New `AdminConfigController` (`/admin/config` index/new/edit/
  delete) gives the previously-untouched `App\Entity\Config`/
  `ConfigRepository` scaffolding (the `config` table, ~54 rows mixing real
  settings with per-team/per-user draft-runtime state) a generic key/value
  CRUD, deliberately with no special-casing between the two kinds of row
  per the roadmap's original scope; nav entry added to
  `templates/admin/base.html.twig`. A validation-failure Twig bug was
  caught and fixed along the way: `admin/config/edit.html.twig` originally
  read `config.value` unconditionally, which threw on the `new` form's
  failure-path re-render (`config` is `null` there) and would have
  silently dropped the user's typed input — fixed by passing plain
  `key`/`value`/`isEdit` scalars to the template instead of the entity.
  832 tests green; manually verified end-to-end (drag-reorder persistence
  + homepage widget ordering, CSRF/non-commissioner rejection, config CRUD
  round-trip incl. a dotted key) against `php -S -t public public/index.php`
  with a fake commissioner session. Not yet PR'd.
- Symfony-appropriate error handling (Phase 13 complete, 2026-08-19,
  `specs/2026-08-18-error-handling/`, branch `phase13-error-handling`):
  `public/index.php` now falls back to `LegacyBridge` only when
  `$request->attributes->get('_route')` is unset (a real router miss),
  not merely on a 404 status — a matched controller's own
  `createNotFoundException()`/`createAccessDeniedException()` now renders
  Symfony's own response directly instead of `LegacyBridge` trying (and
  failing) to map it to a `/football/` file. New branded
  `error404`/`error403`/`error.html.twig` templates under
  `templates/bundles/TwigBundle/Exception/`, matching site chrome.
  `LegacyBridge::getLegacyScript()`'s "can't map this path" case now
  throws a typed `LegacyRouteNotFoundException`, caught by
  `handleRequest()` and rendered as the branded 404 (logged) instead of
  escaping as an uncaught exception; the legacy `require` itself is now
  wrapped in try/catch plus a `register_shutdown_function` fatal-type
  guard, both paths logging via the new `LegacyErrorPageService` and
  rendering the branded 500. Prod Monolog's `main` handler floor dropped
  from `error` to `warning`, closing the previously-invisible 401/403/404
  logging gap. First `WebTestCase`-based tests in the repo
  (`tests/Controller/ErrorPagesTest.php`), needed two new pieces of test
  infra (`tests/bootstrap.php` for dotenv loading;
  `APP_RUNTIME_MODE=web=1` in `phpunit.xml`, since kernel-booting tests
  run under CLI SAPI and Symfony's `error_renderer` otherwise picks the
  plain-text `CliErrorRenderer` over the HTML one) plus two `when@test`
  fixture routes to avoid needing a provisioned `wmffl_test` database.
  848 tests green. Manual E2E checklist in `validation.md` not run
  end-to-end this pass (see that file); Josh to confirm before merge. Not
  yet PR'd.

## Phase 11 — Small fixes (complete)

A catch-all phase for small, self-contained fixes and polish that don't
warrant their own feature phase. Batched here so each could land as a
small PR (or a few together) rather than blocking on a larger effort. All
four items complete 2026-07-31 — see the `Done` entries above
(`specs/2026-07-31-proposals-formatting/`,
`specs/2026-07-31-comments-login-polish/`).

1. ~~**Proposal Markdown authoring — line breaks and indentation.**~~
2. ~~**Proposal vs. ballot field visibility.**~~
3. ~~**Article cards — comment count indicator.**~~
4. ~~**Login form alongside "must be logged in" messages.**~~

## Phase 12 — Admin tooling (complete)

Two self-contained admin-only tools, split out of Phase 11 since they're
scoped work in their own right rather than one-line polish. Both complete
2026-07-31 — see the `Done` entry above
(`specs/2026-07-31-admin-tooling/`).

1. ~~**Quicklinks admin — drag-and-drop ordering.**~~ The admin quicklinks
   index (`templates/admin/quicklinks/index.html.twig`) lists links with a
   plain `Order` column; reordering today means opening each link's edit
   form and hand-editing the `sortOrder` number input
   (`templates/admin/quicklinks/edit.html.twig`) until the list order
   changes — tedious and error-prone with more than a couple of links.
   Replace with drag-and-drop reordering on the index page:
   - make the table rows draggable (e.g. SortableJS, consistent with any
     drag-and-drop library already used elsewhere in the admin UI, or a
     small vanilla HTML5 drag-and-drop handler if none exists);
   - on drop, POST the new ordering to a new route (e.g.
     `admin_quicklinks_reorder`) that accepts an ordered list of link IDs
     and rewrites `sortOrder` sequentially in `AdminQuickLinkController`;
   - keep the manual `sortOrder` field on the edit form as a fallback (or
     drop it if drag-and-drop fully replaces it — decide during
     implementation), but the index page becomes the primary way to
     reorder.

2. ~~**Admin config editor.**~~ `App\Entity\Config` / `ConfigRepository`
   (`symfony-app/src/Entity/Config.php`) map the existing `config` table
   (flat `key` varchar PK / `value` varchar, 54 rows today) but nothing in
   the Symfony app reads or writes it yet — it's untouched scaffolding.
   Build a generic admin CRUD tool for it, following the
   `AdminQuickLinkController` pattern (`src/Controller/Admin/AdminQuickLinkController.php`,
   `templates/admin/quicklinks/`):
   - `AdminConfigController` under `/admin/config`: index (table of all
     key/value pairs, `requireCommissioner` gate like other admin
     controllers), edit (change a value), new (add a key), delete;
   - note the table currently mixes true settings (`draft.hangout.url`,
     `draft.clock.*`, `protections.deadline`, `draft.start`) with
     per-team/per-user runtime state written by the draft flow
     (`draft.login.<userid>`, `draft.team.<teamid>`,
     `draft.order.team.<teamid>`, `draft.order.word.<teamid>`) — the tool
     itself doesn't need to distinguish these (plain generic editor is
     fine), but don't build any caching/eager-load assumption that treats
     the table as small/static, since the draft-state rows churn.

## Phase 13 — Symfony-appropriate error handling (complete)

Legacy fallback (`symfony-app/public/index.php`, `LegacyBridge.php`) currently
makes error behavior inconsistent and, in one common case, actively worse than
either side alone. `index.php` decides whether to fall back to legacy purely
by checking `$response->isNotFound()` (status 404) — it can't tell a genuinely
unrouted URL apart from a deliberate `$this->createNotFoundException(...)`
thrown by a matched Symfony controller (there are ~20 of these today, e.g.
`TeamController.php:131,178`, `PlayerProfileController.php:56`,
`ArticleController.php`, `AdminSeasonController.php`,
`AdminProposalController.php`). Both land in `LegacyBridge`, which then tries
to map the URL to a file under `/football/`; when the route is Symfony-only
(a bad id, not a legacy page), `LegacyBridge::getLegacyScript()` throws
`"Unhandled legacy mapping for ..."` (`LegacyBridge.php:104`) — an uncaught
exception raised *after* the Symfony kernel has already finished handling the
request, so it bypasses Symfony's exception handling, the branded error page,
and Monolog entirely. Recorded as a known site-wide gotcha since the Articles
migration; this phase fixes it instead of carrying it to the Final phase.

1. **Stop routing controller-thrown 404s into LegacyBridge.** Only a request
   where Symfony's router itself found no matching route should ever reach
   `LegacyBridge` — a 404 thrown from inside a matched controller
   (`createNotFoundException` on a bad id) should render Symfony's own 404
   response directly. Distinguish the two in `public/index.php` (e.g. check
   whether the request carries a matched `_route` attribute) rather than
   branching on status code alone.
2. **Custom branded error templates.** Add
   `templates/bundles/TwigBundle/Exception/error.html.twig` (plus
   `error404.html.twig`, `error403.html.twig` as needed) so genuine 404s,
   403s (`createAccessDeniedException` in `TradeController.php`,
   `RosterMoveController.php`), and 500s get a page matching site styling
   instead of Symfony's generic default — currently shown as-is since no
   override exists.
3. **Close the prod logging gap for 4xx.** `config/packages/monolog.yaml`'s
   `when@prod` `main` handler floors at `level: error`; Symfony logs most
   4xx client errors below that, so real 401/403/404s are invisible in
   `logs/wmffl.log` today. Decide what's worth capturing (e.g. a lower floor
   or a dedicated 4xx channel) without letting routine bot/crawler noise
   flood the file.
4. **Harden the legacy `require` in `LegacyBridge::handleRequest`.** Wrap
   `require $legacyScriptFilename;` (`LegacyBridge.php:158`) so a fatal error
   or uncaught exception from legacy code gets logged through Monolog (not
   just the unrotated `error_log()` channel) and shown the same branded
   error page as case 2, rather than a raw, unstyled PHP error dump at
   whatever status code PHP happens to send.
5. Add tests covering: a bad-id 404 on a real route renders the branded 404
   directly (no `LegacyBridge` mapping attempt), a truly unrouted legacy URL
   still falls through and serves correctly, and a forced legacy fatal error
   is captured by Monolog.

## Phase 14 — Activations UI modernization

Legacy: `football/activate/submitactivations.php`, `processActivations.php`,
`currentactivations.php`, `activations.php`/`index.php`,
`activationButtons.php` — the weekly lineup-submission flow (set who's
starting/reserve, see every team's current lineup for the week).
Explicitly **not** in scope: `currentscore.php`/`scoreFunctions.php`, the
live/historical box-score rendering that also lives in this directory —
that's Phase 16's Boxscores redesign; this phase only touches the
"set your lineup" and "see everyone's current lineup" pages.

1. Read side: port the roster/opponent/injury/lock query
   (`submitactivations.php`'s main JOIN plus its `noActivateSql`/
   `opponentRoster`/`actingHCsql` companions) into a repository/service.
   Keep the `activations` table (canonical name since Phase 7) and the
   position-limit rules (1 HC, 1 QB, 1–2 RB, 2–3 WR, 1–2 TE with
   RB+WR+TE=5, 1 K, 1 OL, 2 DL, 2 LB, 2 DB) as a single canonical
   definition rather than duplicated validation logic.
2. Submit flow: controller + form handling to replace
   `submitactivations.php`/`processActivations.php` — same lock
   semantics (5 minutes before kickoff per player, the "joined team
   after the week-14 activation deadline" quirk) and the acting-HC
   special case (free-agent HC pickup when a team has none rostered).
3. **Fix the SQL-injection surface**: `processActivations.php` builds
   `INSERT INTO activations (...) VALUES ($season, $week, $teamnum,
   '$key', $item)` by interpolating `$_REQUEST` values directly with no
   validation or binding — bind parameters, same fix pattern as Phase
   3's `teams/compare`.
4. Current-activations view: port `currentactivations.php`'s
   per-team lineup cards (shown on `/activations` and after submit) to
   a repository + controller.
5. Mobile-friendly redesign of that view and the submit form: replace
   the fixed 2-column desktop card grid with a layout that reflows to
   one column on narrow viewports, and use larger tap targets for the
   starter/reserve checkboxes and the HC picker instead of the current
   cramped inline controls; follow the `btn-wmffl` button convention
   used elsewhere in member/admin pages.
6. Retire dead code found along the way: `info.php` (phpinfo dump, same
   class of dead page as Phase 5's `info.php`), `submitthanks.php`
   (orphaned — `processActivations.php` redirects to `activations.php`,
   not this page), and the already-commented-out `gameplan`/`myGP`/
   `oppGP` remnants in `submitactivations.php` and
   `processActivations.php`.
7. `football/activate/` deleted for everything covered here, with 301s
   (`LegacyActivationRedirectController`) from `activations.php`,
   `submitactivations.php`, `processActivations.php`; `currentscore.php`
   and `scoreFunctions.php` stay on the LegacyBridge until Phase 16.

## Phase 15 — History (per-season)

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
in scope — that's Phase 16.

1. Design a data model for the per-season hub content (champion,
   runner-up, playoff scores) currently hardcoded per file
2. A single generic Symfony route/template driven by that data,
   replacing the 30+ individual season files and directories

## Phase 16 — Boxscores redesign

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
   ported from `currentscore.php` as its own page. Phase 16 ports it
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
   submission flow) was carved out separately as Phase 14
6. Phase 7 table renames are done (final `activations`/`players`/
   `injuries` names in place); data coverage varies by era — degrade
   gracefully for seasons missing stat lines

## Phase 17 — Remaining odds and ends

Legacy: `login/`, `forum/`, `rules/`, `info.php`, `scores.php`

1. Auth (login) — highest risk, do last and carefully
2. Static/low-traffic pages (rules, info, forum)
3. Scores

## Phase 18 — Scripts: legacy → Symfony console commands

Legacy: `/scripts/` — standalone PHP invoked directly (`php scripts/foo.php`,
presumably via cron), each pulling in `base.php` (raw `mysqli_connect`
against `conf/wmffl.conf`, `$_REQUEST` splatted into local vars via
`foreach ($_REQUEST as $key => $val) { $$key = $val; }`, current
season/week re-derived by hand from `weekmap`). In scope: the scripts
that run on a schedule against prod data — `resolvewaivers.php`,
`updateactivations.php`, `logscores/fixtransfer.php`,
`logscores/transferscores.php`, `livescore/updatescores.php`,
`imports/games.php`. Out of scope: one-off/offline data-backfill
tools (`imports/newsletter_activations.py`,
`imports/newsletter_playerscores.py`, the `insert_*_draftpicks.sql`
scripts, `python/` injury/covid feeds) — no framework benefit to
porting something run once and discarded.

1. One `App\Command\*` class per in-scope script (`symfony-app/src/Command/`),
   e.g. `app:waivers:resolve`, `app:activations:advance-week`,
   `app:scores:transfer` / `app:scores:fix-transfer`,
   `app:livescore:fetch` — real `InputArgument`/`InputOption` instead of
   the `$_REQUEST` splat, `Command::SUCCESS`/`FAILURE` instead of
   `print "Success: ..."` / `die()`
2. Constructor-inject `Doctrine\DBAL\Connection` / existing
   repositories instead of `base.php`'s manual `mysqli_connect` +
   `parse_ini_file('wmffl.conf')` — removes one of the two
   credential stores this class of script currently needs
   (`conf/wmffl.conf` vs `symfony-app/.env.local`, kept in sync by hand)
3. Where a script's SQL re-derives a rule that now lives in a service
   from an earlier phase (e.g. `updateactivations.php`'s roster
   carryover vs. `SeasonRuleService`'s roster limits), call the
   service instead of duplicating the query
4. Re-point cron at `php symfony-app/bin/console app:...`; consider
   `symfony/scheduler` (`#[AsPeriodicTask]`/`#[AsCronTask]`) only if
   in-app visibility/retries are wanted over plain cron
5. Add `CommandTester`-based tests per command — none of the legacy
   scripts have any test coverage today

## Unscheduled — Live scoreboard redesign

Phase 16 splits the live scoreboard out of `currentscore.php` onto its own
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
