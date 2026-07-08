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

## 7. Loose Ends

Switch the `/players` search to filter on the `active` flag rather than
the `retired` column, disambiguate the two concepts, and do a final
quality pass:

- **Search filters on `active`, not `retired`.** In
  `PlayerRepository::buildSearchWhere()`, replace the default
  `np.retired IS NULL` restriction with `np.active = 1`; the `inactive`
  filter (checkbox) includes `active = 0` players instead of retired
  ones. This makes the existing "Include non-active" label
  (`templates/player/index.html.twig:48`) accurate as-is. Update the
  search tests (default excludes `active = 0`, checkbox includes them)
  and keep showing `retired` in the result rows where it already
  renders.
- **`active` vs `retired` semantics.** After the switch: `active`
  (boolean) drives both search visibility and — with `usePos` — the
  legacy transaction player pool (`football/transactions/list.php:17`,
  `p.active=1 AND p.usePos=1`); `retired` (year(4), NULL = still
  playing) is informational only. Document this with brief comments on
  the three `App\Entity\Player` fields.
- **Expose `active`/`usePos` in admin player editing.** With `active`
  now controlling search visibility, admins must be able to set it; the
  edit form covers bio/draft fields and `retired` but not these two
  flags, so managing them still requires direct SQL. Add both as
  checkboxes to `AdminPlayerController::edit()` + template, persist on
  POST, cover in the controller tests.
- **Hide position-less players.** Exclude players with no position from
  the `/players` search results: add a
  `np.pos IS NOT NULL AND np.pos <> ''` clause in
  `PlayerRepository::buildSearchWhere()`, applied regardless of the
  include-non-active checkbox. Since `AdminPlayerController` reuses
  `searchPlayers()`, gate the clause on a filter key (e.g.
  `requirePos`) that `PlayerProfileController::index()` always sets and
  the admin search omits, so position-less records stay reachable for
  fixing in admin. Test: a player with NULL/empty `pos` never appears
  in `/players` results but is still found by the admin search.
- **"Players" link in the legacy nav.** Task 2 added "Players" only to
  the Symfony nav (`templates/base.html.twig:72`); legacy pages render
  their own nav from `football/base/menu.php` (~line 104). Add the same
  `<li class="nav-item"><a class="nav-link pl-2" href="/players">`
  entry there, in the matching slot (between Teams and Stats), so the
  link is present regardless of which app served the page.
- **Prominent player name on the profile page.** The name currently
  renders in a `div.cat` (`templates/player/profile.html.twig:59`) — the
  same 25px maroon/gold section-header bar (`core.css:144`) used for
  every other section on the page, so it doesn't read as the page
  title. Replace it with a real page heading: an `<h1>` with the full
  name, sized clearly above the section bars (keep the site's
  maroon/gold palette), with a small subtitle line (position · #number
  · NFL team, omitting empty fields) so it's unmistakable whose profile
  is shown. Update the profile-page test's heading assertion.
- **Clear-search button.** Add a "Clear" button next to the Search
  button on the `/players` filter form
  (`templates/player/index.html.twig`) that resets all criteria — name,
  WMFFL team, NFL team, position, include-non-active — by linking back
  to the bare `player_index` route (no query params, no JS needed);
  style per the `btn-wmffl` + `text-center` convention. Test: with
  filters applied, the clear control returns the default listing.
- **Logged-in name on Symfony pages.** Legacy pages show the user's
  name on the navbar button when logged in
  (`football/base/menu.php:113`, via the `$isin`/`$fullname` globals
  `LegacyBridge` fills from `AuthenticationService`); Symfony pages
  always show "Log In" even when logged in. The conditional already
  exists (`templates/base.html.twig:80`) but reads
  `app.request.session.get('isin')` — Symfony's attribute bag — while
  the login flow writes raw `$_SESSION['isin']`/`$_SESSION['fullname']`
  (`AuthenticationService::login()`), which `session.get()` never sees.
  Fix by exposing `AuthenticationService` to Twig (global via
  `twig.yaml` or a lightweight extension) and switching the template to
  `auth.isLoggedIn()` / `auth.fullName()`, matching the legacy button
  markup (name opens `#profileModal`). Test with the fake-session E2E
  recipe: logged-in request renders the fullname button, anonymous
  renders "Log In".
- **Quality pass.** Run a code review + simplification pass over the
  full branch diff; apply findings; suites stay green.
