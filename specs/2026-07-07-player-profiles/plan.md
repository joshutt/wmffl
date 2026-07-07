# Plan — Player Profiles (Phase 2)

Task groups in order; each leaves the suite green and the app working.

## 0. Already done (commits `654d2de`, `47d29a3` on `player-pages`)

- `App\Entity\Player` mapped to `newplayers`.
- `PlayerRepository::getCurrentRoster()`, `getRosterHistory()`,
  `getStatsBySeason()`.
- `PlayerProfileController` (`GET /player/{id}`, 404 on unknown id,
  active-stat-column filtering) + `templates/player/profile.html.twig`.

No changes planned to this code; task 1 backfills its tests.

## 1. Test backfill for the profile page

- `tests/Controller/PlayerProfileControllerTest.php`: known id renders 200
  with bio, current roster, history, and stats sections; unknown id 404s;
  stat columns that are zero across all seasons don't render; a player
  with no stats/no roster history renders cleanly (empty-state, no SQL
  errors).
- Repository coverage for the three query methods (mock the DBAL
  connection per existing repository-test conventions, plus assertions on
  parameter binding; the real-schema correctness burden falls on the
  validation.md E2E pass).

## 2. `/players` index page

- `PlayerRepository::searchPlayers(array $filters, int $page,
  int $perPage = 50): array` + a matching count method, joined with the
  current roster (`DateOff IS NULL`) for the WMFFL-team column; ordered
  lastname, firstname. Filters:
  - `q` — substring match against `lastname` AND `firstname`;
  - `team` — current WMFFL team id, or the sentinel free-agent value
    (no current roster row);
  - `nfl` — NFL team abbreviation;
  - `pos` — position;
  - `inactive` — when absent/false, restrict to `retired IS NULL`
    (active only, the default); when set, include retired players.
- Dropdown sources: WMFFL teams from the current-season team list (+
  "Free Agents" entry), NFL teams and positions from distinct values in
  `newplayers` (or the `NflTeam` entity for teams if it's current).
- `PlayerProfileController::index()` — `GET /players` (name
  `player_index`); reads `q`, `team`, `nfl`, `pos`, `inactive`, `page`
  query params; renders `templates/player/index.html.twig`: GET filter
  form (name search box, WMFFL-team / NFL-team / position dropdowns,
  include-non-active checkbox), result table (name → profile link, pos,
  NFL team, WMFFL team), pagination in the `/articles` style carrying the
  active filters through page links.
- Add "Players" to the main nav in `templates/base.html.twig`.
- Tests: default listing paginates and excludes retired players;
  `inactive` includes them; `q` matches first and last names; `team`
  filters, including the Free Agents option; `nfl` and `pos` filter;
  combined filters; empty result set renders a friendly message; profile
  links present; pagination links preserve filters.

## 3. Legacy team-page link wiring

- Audit `football/teams/` (`display.php`, `teamroster.php`,
  `teamschedule.php`, `teamhistory.php`, `h2h.php`, `compareteams.php`,
  `indschedule.php`) for rendered player names; `roster.php` is already
  linked.
- Where a player name renders with a player id in reach (known:
  `compareteams.php`), wrap it in `<a href='/player/{id}'>` — minimal
  edit, same pattern as `roster.php:61`. Where no id is available in the
  existing query, note it in the spec dir rather than rewriting the query
  (Phase 3 replaces these pages).

## 4. Admin player tooling

- `AdminPlayerController` extending `AbstractAdminController`:
  - `GET /admin/players` — name search form + results (name, pos, NFL
    team, retired flag, Edit link). Empty query → prompt to search, not a
    full table dump.
  - `GET /admin/players/{id}/edit` + POST — form for lastname, firstname,
    pos, NFL team, number, retired, height, weight, dob, draftTeam,
    draftYear; lastname required; flash + redirect back to the search on
    save; 404 unknown id.
- Templates under `templates/admin/player/` (`btn-wmffl` + `text-center`
  per UI convention); "Players" link in the admin sidebar.
- Tests: non-commissioner gated out (per `AdminAccessSubscriber`
  conventions); search finds by partial name; edit form prefills; valid
  POST persists and redirects; empty lastname re-renders with error;
  unknown id 404s.

## 5. Roadmap + spec bookkeeping

- `specs/roadmap.md`: move Phase 2 to Done; re-home the `players.html`
  retirement item to a future draft-tooling note (it's a draft-board
  prototype, not a player list — see requirements.md).
- Note any unlinkable legacy pages found in task 3 for the Phase 3 spec.

## 6. Validation pass

- Full `validation.md` run: suites green, ≥95% line coverage on the
  branch's new+existing-Phase-2 PHP files via `--coverage-clover`, manual
  E2E checklist against real league data, regression checks. Fix anything
  it surfaces before opening the PR.
