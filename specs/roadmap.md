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

## Phase 6 — Table cleanup

Legacy: superseded/duplicate DB tables surfaced while auditing `players` vs
`newplayers`; a fuller schema audit may turn up more.

1. Retire the legacy `players` table — either port the six frozen 2003–2008
   history pages that still query it directly (`2003Season/leaders.php`,
   `2003Season/protectioncost2004.php`, `2004Season/protectioncost2005.php`,
   `2005Season/protectioncost.php`, `2006Season/protectioncost2007.php`,
   `2008Season/protectioncost.php`) over to `newplayers`, or confirm they're
   frozen archives safe to leave pointed at the old table, before dropping it
2. Delete `symfony-app/public/images/players/playerstats.php` — an orphaned
   leftover from the images move (`0ad1441`) that still queries `players`
   directly; its functionality is already covered by `StatsController` /
   `StatsRepository`, but it's a live `.php` file sitting in the public
   docroot
3. Audit the rest of the schema for other legacy-vs-current table pairs
   before the final decommission phase locks in the final schema

## Phase 7 — Reclaim canonical table names

Follow-up to Phase 6 (`table-cleanup` branch/PR), deliberately deferred out
of that PR to keep it small (decided 2026-07-12, see
`specs/2026-07-12-table-cleanup/plan.md`). Phase 6 drops the superseded
`activations`/`players`/`injuries` tables; this phase renames their
replacements back to those canonical short names.

1. `RENAME TABLE revisedactivations TO activations` — update ~27 files
   (`football/activate/`, `football/history/common/` + per-season leaders,
   `scripts/` livescore/logscores/updateactivations, `symfony-app`
   services/controllers/tests); rename `App\Entity\RevisedActivation` →
   `App\Entity\Activation`
2. `RENAME TABLE newplayers TO players` — update ~64 files (17
   `symfony-app` src files, legacy `football/` incl. per-season history
   pages, `scripts/`); `App\Entity\Player`'s `#[ORM\Table]` name changes;
   rename the FK constraints (`FK_newinjuries_newplayers`,
   `ir_newplayers_playerid_fk`) in the same migration
3. `RENAME TABLE newinjuries TO injuries` — update 6 files; rename
   `App\Entity\NewInjury` → `App\Entity\Injury` (the name freed by
   Phase 6's drop)
4. Regenerate `scripts/database/schema.sql` from the final schema
5. One rename per commit, DB rename + code sweep deploying together in
   the same maintenance window, ideally off-season; do not edit
   already-executed migrations (e.g. `Version20260118000000.php`
   mentions these names in comments only)

## Phase 8 — Trades

Legacy: `football/transactions/trades/` (~1,800 lines: `tradescreen.php`,
`edittrade.php`, offer/confirm/process flow, email notifications)

1. Trade list / trade screen (read-only views)
2. Offer → confirm → process workflow (writes + email) — the riskiest
   write path in the app; migrate one step at a time

## Phase 9 — History (remaining)

Legacy: `football/history/` (`pastchamps.php`, `pastdrafts.php`,
`alltimerecords.php`, `recordseason.php`, `recordsweek.php`,
per-season pages)

1. All-time records / past champions / past drafts
2. Per-season pages — likely a single generic Symfony route/template driven
   by data, replacing the 30+ individual `{year}Season.php` files

## Phase 10 — Boxscores redesign

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
   ported from `currentscore.php` as its own page. Phase 10 ports it
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
   submission flow) stays for Phase 11
6. Depends on Phase 7 table renames (final `activations`/`players`/
   `injuries` names); data coverage varies by era — degrade gracefully for
   seasons missing stat lines

## Phase 11 — Remaining odds and ends

Legacy: `login/`, `activate/`, `forum/`, `rules/`, `quicklinks.php`,
`info.php`, `scores.php`

1. Auth (login/activate) — highest risk, do last and carefully
2. Static/low-traffic pages (rules, info, quicklinks, forum)
3. Scores

## Unscheduled — Live scoreboard redesign

Phase 10 splits the live scoreboard out of `currentscore.php` onto its own
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
