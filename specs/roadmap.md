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

## Phase 4 — Transactions (spec: `specs/2026-07-10-transactions-stats/`)

Legacy: `football/transactions/` (`list.php`, `transactions.php`,
`injuredReserve.php`, `protections.php`, `confirm.php`, waiver pages)

1. Transaction list (read-only view)
2. Waiver order / waiver picks
3. IR moves, protections, roster add/drop (`list.php` → `confirm.php`) —
   these involve writes; migrate carefully, one action at a time
4. Delete `football/transactions/draftorder/` (draft-order word game) —
   obsolete, remove rather than migrate

Trades (`football/transactions/trades/`) are excluded — they get their own
phase (Phase 6) and stay on the LegacyBridge until then.

## Phase 5 — Stats (spec: `specs/2026-07-10-transactions-stats/`)

Legacy: `football/stats/` (`playerstats.php`, `leaders.php`, `powerlist.php`,
`powerrate.php`, `weekbyweek.php`, `injuryReport.php`, plus extras)

1. Player stats / leaders pages
2. Power rankings
3. Week-by-week and injury report pages
4. Index-linked extras: `luck.php`, `playerrecord.php`, `lastplayer.php`
5. CSV exports: `statcsv.php`, `playerlist.php` (port as CSV-returning routes)
6. Delete dead pages instead of migrating: `info.php` (phpinfo dump),
   `standings.php` (hardcoded 2009), `teamcompare.php` (unlinked)

## Phase 6 — Trades

Legacy: `football/transactions/trades/` (~1,800 lines: `tradescreen.php`,
`edittrade.php`, offer/confirm/process flow, email notifications)

1. Trade list / trade screen (read-only views)
2. Offer → confirm → process workflow (writes + email) — the riskiest
   write path in the app; migrate one step at a time

## Phase 7 — History (remaining)

Legacy: `football/history/` (`pastchamps.php`, `pastdrafts.php`,
`alltimerecords.php`, `recordseason.php`, `recordsweek.php`,
per-season pages)

1. All-time records / past champions / past drafts
2. Per-season pages — likely a single generic Symfony route/template driven
   by data, replacing the 30+ individual `{year}Season.php` files

## Phase 8 — Remaining odds and ends

Legacy: `login/`, `activate/`, `forum/`, `rules/`, `quicklinks.php`,
`info.php`, `scores.php`

1. Auth (login/activate) — highest risk, do last and carefully
2. Static/low-traffic pages (rules, info, quicklinks, forum)
3. Scores

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
