# Requirements — Team Pages (roadmap Phase 3)

Roadmap Phase 3: migrate the legacy team pages (`football/teams/`, ~1,100
lines of procedural PHP) to Symfony. Branch: `team-pages`. Unlike Phase 2
this is a port, not new functionality — every page has a legacy
counterpart whose behavior is the reference.

## Scope

Full Phase 3 plus the teams index page (decided 2026-07-08):

1. **Teams index** (`/teams`) — division cards listing current teams +
   owners, defunct-teams card, other-features card. Includes the static
   Fighting Squirrels page it links to.
2. **Team roster page** — header (logo, name, owners, motto,
   championships), current roster table (pos, name→profile link, NFL
   team, bye, age, injury/IR, acquired date, next protection cost, season
   pts), free-transactions-remaining and roster-count lines.
3. **Team schedule page** — per-season schedule with W/L/T + scores,
   past-season selector, and the head-to-head view (all-time games and
   aggregate record vs a chosen opponent, opponent selector).
4. **Team history page** — per-season + all-time + playoffs/Toilet Bowl
   records, playoff game results, division/league titles, past names,
   past owners.
5. **Compare rosters** (`/teams/compare`) — two-team picker,
   side-by-side current rosters.
6. **Legacy retirement** — delete `football/teams/` once ported, with
   301 redirects from every old URL (decided 2026-07-08).
7. **Admin team editing** (per mission.md: a feature area isn't done
   until admins can manage it without raw DB access) — edit a team's
   profile fields.

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Route scheme | `/teams` (index), `/teams/compare`, `/team/{id}/roster`, `/team/{id}/schedule`, `/team/{id}/history`; bare `/team/{id}` redirects to roster | Mirrors the Phase 2 `/players` + `/player/{id}` convention; canonical URLs use numeric ids. |
| Legacy `viewteam` flexibility | Honored in redirects only | `teamheader.php:21` matched `viewteam` against teamid, abbrev, or lowercased/space-stripped name. The redirect resolver accepts all three and 301s to the numeric-id route; new pages take ids only. Unresolvable `viewteam` → 404. Legacy's silent default of team 2 when `viewteam` was absent is **not** preserved — redirect the bare URL to `/teams`. |
| Legacy files | Delete + 301 redirects | Same pattern as the articles migration. Old URLs: `/teams/`, `/teams/teamroster`, `/teams/teamschedule` (incl. `vsTeam` → h2h), `/teams/teamhistory`, `/teams/compareteams`, `/teams/squirrels`. |
| Data access | `TeamRepository` with raw-DBAL queries | Matches `StandingsRepository`/`ScoresRepository`/`PlayerRepository` precedent for read-heavy aggregation; entities exist for all tables touched (`Team`, `TeamNames`, `Division`, `Owner`, `User`, `Schedule`, `Title`, `Roster`, `TransPoint`, `WeekMap`, `NflBye`, `NewInjury`, `Ir`, `ProtectionCost`, `PositionCost`, `PlayerScore`). |
| H2H `viewseasom` typo | Removed, not ported | `h2h.php:4` reads `$_REQUEST['viewseasom']` (typo), so the season param never worked — h2h always showed all seasons with current-week hiding. Port that de facto behavior; the h2h view takes no season param (confirmed 2026-07-08). |
| H2H discoverability | New entry point on the schedule page | Legacy has no way to reach h2h from the schedule page — it only renders when `vsTeam` is already in the URL. The Symfony schedule page adds a "Head-to-head vs …" opponent selector (GET form/link) so h2h is reachable by navigation, not just by URL editing. |
| `compareteams.php` SQL injection | Fixed by the port | Legacy interpolates `$teamone`/`$teamtwo` from the request straight into SQL. Symfony version uses bound parameters; noted so the fix is deliberate, not incidental. |
| Fighting Squirrels page | Static Twig template | Pure static content (story + hard-coded 1996 record). Not worth a DB model; Kingsmen stays a non-link card entry as in legacy. |
| Admin tooling shape | Edit-only, `team`-row fields | Edit name-independent profile fields: abbrev, motto, logo, fulllogo flag, active flag, division. Seasonal team names (`teamnames`), owner history (`owners`), and schedule data are separate, riskier write paths — out of scope. No create/delete (expansion is a rare, manual event). |
| Trans-points display | Port as-is | "N Remaining Free Transactions" / "N extra transactions used" from `transpoints` for the current season, plus the on-roster player count, exactly as `roster.php:12-25` computes them. |

## Page behavior (from the legacy reference)

### Teams index (`/teams`)
- Division cards (`bg-div-{divisionid}` styling from `/base/css/team.css`)
  for the current season's divisions (`division.startYear/endYear` spans
  `$currentSeason`), teams joined to their user/owner, sorted by division
  then team name. Each team links to its roster page.
- Defunct Teams card: Fighting Squirrels (link to the static page),
  Kingsmen (no link). Other Features card: Compare Rosters link; the
  legacy Waiver Wire Order link points at `/transactions/displayWaiverOrder`
  and keeps doing so (Phase 4 territory).

### Shared team header (roster/schedule/history)
- Logo (with the `fulllogo` alternate layout), team name, "Established
  {member}", owner(s) — active users for the team, primary first,
  joined with "and" — plus owner-since season and motto.
- Championship trophy row (one trophy image per `titles` row of type
  `League`), then the History / Roster / Schedule pill nav with the
  active page highlighted (`newlinkbar.php`).

### Roster page
- The big roster query (`roster.php:31-51`): current roster
  (`roster.dateoff IS NULL`) with bye week, injury status (shortened, IR
  overrides), age from dob, acquired date, protection cost (via
  `protectioncost`/`positioncost`), and season points. Season-boundary
  logic: when `currentWeek <= 1`, points come from the prior season and
  protection cost from the current one; otherwise points are current
  season, cost next season. Column headers show the season years.
- Player names link to `/player/{id}` (kept from Phase 2).

### Schedule page
- Default: current season (rolling back one season when `currentWeek`
  is 0, matching `indschedule.php:11-20`); results (WIN/TIE/LOSS +
  scores) shown only for weeks before the check week; future/current
  weeks show just the opponent. Week label prefers `schedule.label` over
  `weekmap.weekname`. Past-season dropdown from seasons the team appears
  in `schedule`.
- H2H (`?vs={teamid}` or `/team/{id}/schedule/vs/{opponent}` — pick one
  during implementation): every meeting across all seasons with result +
  score, aggregate W-L-T and pct header, opponent dropdown listing every
  team by its most recent name. No season parameter (the legacy one was
  the dead `viewseasom` typo — removed).
- The plain schedule view includes a "Head-to-head vs …" opponent
  selector alongside the past-season selector, linking into the h2h
  view — new; legacy offered no navigation path to h2h.

### History page
- Records table: All-Time first (bold), then Playoffs and Toilet Bowl
  rows, then per-season regular-season records (desc), each with W/L/T
  and pct; in-progress current season excluded when `currentWeek` is 0.
- Playoff results list ("{event} {season} — Beat/Lost to {team} X-Y"),
  division titles, league titles, past owners (season ranges, co-owners
  joined with "and"), past names (season ranges from `teamnames` runs).
- Port the data logic from `dataRetrieval.php`; the layout can be cleaned
  up into a modern Twig table (legacy markup is 90s-era `<TABLE WIDTH=75%>`
  with a vestigial empty FINISH column — don't reproduce that).

### Compare rosters (`/teams/compare`)
- Form: two dropdowns of active teams (`team.active=1`), GET submit.
- Result: side-by-side rosters (name→profile link, pos, NFL team),
  ordered by team name, pos, lastname.

### Admin (`/admin/teams`)
- List of teams (name, abbrev, division, active) — small enough for a
  full table, no search needed (unlike players).
- `GET/POST /admin/teams/{id}/edit`: abbrev, motto, logo filename,
  fulllogo flag, active flag, division dropdown. Flash + redirect on
  save; 404 unknown id. `AbstractAdminController` gating, `btn-wmffl` +
  `text-center` buttons, admin-sidebar "Teams" link.

## Link audit (internal callers of the old URLs)

Redirects protect bookmarks, but internal links should be updated to the
new routes directly. Audit and update at port time; known callers of
`/teams/teamroster?viewteam=`, `/teams/teamschedule`, `/teams/` etc.
include the legacy nav (`football/base/menu.php`), the Symfony base nav,
standings templates, and the homepage widgets. Grep both apps for
`teamroster`, `teamschedule`, `teamhistory`, `compareteams`, `/teams`.

## Out of scope

- Waiver order / transaction pages (Phase 4) — links keep pointing at
  legacy.
- Editing seasonal team names, owner history, schedules, or titles in
  admin; creating/deleting teams.
- `linkbar.php` (superseded by `newlinkbar.php` in legacy already) — not
  ported.
- Any schema changes; writes are limited to admin edits of `team` rows.
- Restyling beyond dropping obviously-dead legacy markup; reuse the
  existing site CSS (`team.css`, `stats.css`) as prior phases did.

## Risks / notes

- The roster query is the hairiest in the phase: 8 joins, `weekmap`
  resolved by `now()`, season-boundary conditionals. Real-schema E2E
  matters more than unit mocks here (same lesson as Phase 2).
- `jquery.tablesorter` powers roster-table sorting on the legacy page
  (`teamheader.php:67`, `team.js`). Keep the same include on the Symfony
  roster template so sorting still works — this is existing vendored JS,
  not a new dependency.
- Legacy history/records math (`getPastOwners` run-length encoding,
  playoff aggregation) has edge cases around co-owners and mid-season
  changes; parity-check a long-tenured team against the live legacy page
  before deleting it.
- `teamnames` drives historical names everywhere; teams whose id appears
  in `schedule` but not current `team`/`user` rows (defunct) should 404
  gracefully on the team routes rather than render half-empty pages.
