# Plan — Team Pages (Phase 3)

Task groups in order; each leaves the suite green and the app working.
Legacy pages keep serving via LegacyBridge until task 7 removes them, so
there is no mid-branch breakage window.

## 1. Team foundation: repository, header, layout

- `TeamRepository` (raw DBAL, per `StandingsRepository` precedent):
  - `resolveTeamId(string $viewteam): ?int` — id, abbrev, or
    lowercased/space-stripped name → teamid (redirects use this;
    `teamheader.php:21` semantics).
  - `getTeamHeader(int $teamId): ?array` — team row + active owners
    (primary first, "and"-joined), member-since, owner-since season,
    motto, logo/fulllogo.
  - `getChampionshipSeasons(int $teamId): array` (titles, type League).
- `TeamController` with `GET /team/{id}` → 301 to `team_roster`; shared
  `templates/team/_header.html.twig` (logo/fulllogo layouts, trophy row)
  and `_linkbar.html.twig` (History/Roster/Schedule pills, active state).
- Tests: resolver matches id/abbrev/name and misses cleanly; header
  shape incl. co-owner joining; unknown team 404s.

## 2. Teams index + Squirrels page

- `TeamRepository::getTeamsByDivision(int $season): array` (current
  divisions via startYear/endYear, team + owner, sorted division/name).
- `TeamController::index()` — `GET /teams` (name `team_index`):
  division cards (`bg-div-{divisionid}`), Defunct Teams card (Squirrels
  link, Kingsmen plain), Other Features card (Compare Rosters; Waiver
  Wire Order still pointing at legacy `/transactions/displayWaiverOrder`).
- `GET /teams/squirrels` — static template ported from `squirrels.php`.
- Tests: index 200s with every current team grouped under its division,
  owner names shown, roster links correct; squirrels renders.

## 3. Roster page

- `TeamRepository::getCurrentRoster(int $teamId): array` — port the
  `roster.php:31` query with bound params (bye, injury, IR, age,
  acquired, protection cost, season pts) and
  `getTransactionSummary(int $teamId, int $season): ?array`
  (`transpoints`: free-transactions remaining / extra used, roster
  count).
- `TeamController::roster()` — `GET /team/{id}/roster` (name
  `team_roster`): header + summary lines + roster table, player names
  linking to `player_profile`, season-boundary column headers
  (`currentWeek <= 1` → prior-season pts / current-season cost),
  injury shortening + IR override ported from `injuryUtils`/`reportUtils`
  into the repository/controller (or a small service) rather than
  include files. Keep the tablesorter include so column sorting works.
- Tests: table renders all columns for a stubbed roster; profile links;
  IR overrides injury; week<=1 vs mid-season header years; empty roster
  renders without errors; transaction summary phrasing both directions.

## 4. Schedule + head-to-head

- `TeamRepository::getSeasonSchedule(int $teamId, int $season): array`,
  `getSeasonsPlayed(int $teamId): array`,
  `getHeadToHead(int $teamId, int $oppId): array` +
  `getHeadToHeadRecord(...)`, and `getOpponentList(): array` (every
  team by most recent `teamnames` name).
- `TeamController::schedule()` — `GET /team/{id}/schedule/{season?}`
  (name `team_schedule`): defaults to current season (week 0 → prior
  season per `indschedule.php:11-20`); WIN/TIE/LOSS + score only for
  completed weeks; `schedule.label` over `weekmap.weekname`;
  past-season dropdown (GET form, no JS required — keep the
  onChange-submit as progressive enhancement), and a "Head-to-head
  vs …" opponent selector linking into the h2h view (new — legacy had
  no navigation path to h2h; it only rendered when `vsTeam` was already
  in the URL).
- H2H on the same route via `?vs={teamid}`: all-time meetings table,
  aggregate W-L-T + pct header, opponent dropdown. No season filter —
  legacy's was the dead `viewseasom` typo; it is removed, not ported.
- Tests: current/past season rendering, hidden in-progress results,
  label fallback, season rollback at week 0; schedule page links into
  h2h via the opponent selector; h2h record math incl. zero-games pct;
  dropdowns populated; unknown opponent 404s.

## 5. History page

- `TeamRepository` ports of `dataRetrieval.php`: playoff/Toilet Bowl
  records, per-season regular-season records (skipping the in-progress
  season at week 0), all-time totals, playoff game results, titles
  (league/division), past names, past owners (run-length seasons,
  co-owner "and"-joining).
- `TeamController::history()` — `GET /team/{id}/history` (name
  `team_history`): modern Twig tables (drop the vestigial FINISH
  column), All-Time row bold and first.
- Tests: record aggregation (incl. tie/pct math and the zero-game
  guard), past-name/owner range building with a mid-history change and
  a co-owner, playoff results phrasing (Beat/Lost to), titles split.

## 6. Compare rosters

- `TeamRepository::getActiveTeams(): array` and
  `getRostersForComparison(int $a, int $b): array` (bound params —
  this closes the legacy SQL injection).
- `TeamController::compare()` — `GET /teams/compare` (name
  `team_compare`): two-dropdown GET form; side-by-side roster columns
  with profile links when both teams chosen.
- Tests: bare form renders; comparison shows both rosters with links;
  same-team-twice and invalid ids handled without errors.

## 7. Legacy retirement: redirects, link updates, deletion

- Redirect controller/actions for the old URLs, resolving `viewteam`
  via `resolveTeamId()` (id, abbrev, name), all 301:
  - `/teams/teamroster?viewteam=X` → `team_roster`
  - `/teams/teamschedule?viewteam=X[&vsTeam=Y]` → `team_schedule`
    (carrying `vs`)
  - `/teams/teamhistory?viewteam=X` → `team_history`
  - `/teams/compareteams` → `team_compare` (carrying
    `teamone`/`teamtwo`; legacy form POSTed — accept both methods on
    the redirect)
  - `/teams/squirrels` → the new squirrels route; missing/unresolvable
    `viewteam` → `/teams`.
  - `/teams` and `/teams/` already route to `team_index` natively.
- Update internal callers found by the link audit (legacy
  `football/base/menu.php` nav, Symfony base nav, standings templates,
  homepage widgets — grep for `teamroster|teamschedule|teamhistory|compareteams`)
  to the new routes.
- Delete `football/teams/` entirely (all 18 files, including
  `dataRetrieval.php`/`dataFormatting.php`/`display.php` helpers,
  `linkbar.php`, `htaccess.default`).
- Tests: each redirect (id, abbrev, and name forms; vsTeam and
  compare-param carrying); LegacyBridge no longer resolves the old
  paths.

## 8. Admin team editing

- `AdminTeamController` extending `AbstractAdminController`:
  - `GET /admin/teams` — full team table (name, abbrev, division,
    active, Edit link).
  - `GET/POST /admin/teams/{id}/edit` — abbrev, motto, logo, fulllogo,
    active, division dropdown; persist via EM; flash + redirect; 404
    unknown id.
- Templates under `templates/admin/team/` (`btn-wmffl` +
  `text-center`); "Teams" link in the admin sidebar.
- Tests: non-commissioner gated; list renders; form prefills; valid
  POST persists + redirects; unknown id 404s.

## 9. Bookkeeping + validation pass

- `specs/roadmap.md`: move Phase 3 to Done (note the teams index and
  admin editing additions); drop the Phase-2 audit note.
- Full `validation.md` run: suites green, ≥95% line coverage on the
  branch's new PHP files, fake-session E2E checklist including
  legacy-parity spot checks done **before** task 7's deletion, redirect
  and regression checks. Code-review + simplification pass over the
  branch diff; fix findings before opening the PR.
