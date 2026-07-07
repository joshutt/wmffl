# Validation — Player Profiles (Phase 2)

Mergeable when all of the below pass.

## Automated tests + coverage bar

- `cd symfony-app && vendor/bin/phpunit tests/` — all green.
- Coverage: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`,
  then **≥95% combined line coverage across this branch's Phase 2 PHP
  files** — including the pre-existing untested ones:
  `PlayerRepository`, `PlayerProfileController`, `AdminPlayerController`.
  Measure from coverage.xml the same way as the articles phases.

Required test coverage areas:

- Profile page: known id 200s with bio/roster/history/stats sections;
  unknown id 404s; all-zero stat columns hidden; column shown when any
  season has a non-zero value; player with no stats and no roster history
  renders without errors.
- Repository: the three profile queries and the index search/count bind
  the right parameters and shape their result rows; search matches
  lastname and firstname substrings; position filter applies; pagination
  offsets correctly.
- Index page: default listing paginated at 50 and excludes retired
  players (`retired IS NULL` only); `inactive` param includes them; `q`
  matches lastname and firstname substrings; `team` filters by current
  WMFFL roster including the Free Agents option; `nfl` and `pos` filter;
  all filters combine; pagination links preserve active filters; empty
  results render a message; rows link to `player_profile`.
- Admin: non-commissioner blocked from `/admin/players*`; partial-name
  search finds players; edit form prefills every field; valid POST
  persists changes and redirects with flash; empty lastname re-renders
  with error and persists nothing; unknown id 404s.

## Manual end-to-end (real DB, dev server)

Use the fake-session recipe from the articles phase: run
`php -d session.save_path=<dir> -S 127.0.0.1:8321 -t public`, write a
`sess_<id>` file (`isin|b:1;usernum|i:N;...`) and set the matching
`PHPSESSID` cookie — one member session, one commissioner session.

- [x] `/player/{id}` for a long-tenured player with multiple team stints:
      bio, current team, every stint with correct season-spanning team
      names, games activated and active points per stint, per-season stat
      rows with sensible totals (spot-check one season against the legacy
      stats pages).
- [x] Edge cases: a player never rostered by any WMFFL team; a rostered
      player with no stats yet; a retired player; a kicker (FG columns
      appear) vs a QB (they don't).
- [x] `/players`: loads unfiltered and paginates showing active players
      only; search a partial last name, then a partial first name; filter
      by a WMFFL team, then by Free Agents; filter by an NFL team; filter
      by position; combine filters; tick "include non-active" and confirm
      a known retired player appears (and is absent by default); filters
      survive clicking to page 2; a rostered player's WMFFL team shows
      and a free agent's is blank; clicking a row lands on the right
      profile.
- [x] "Players" nav link present and working, logged in and out.
- [x] Legacy links: on `/teams/roster` and each page wired in task 3,
      player names are links and land on the correct profile; the pages
      otherwise render exactly as before (via LegacyBridge).
- [x] Admin (commissioner session): search a player at `/admin/players`,
      edit their position/NFL team, save — flash shows, change appears on
      the public profile and index immediately. Non-commissioner member
      session is blocked from both admin routes.

## Regression

- [x] Untouched legacy routes still load (`/teams/roster`,
      `/transactions/transactions`, `/history/`, `/stats/playerstats`).
- [x] Homepage, `/articles`, `/article/{id}`, standings, and admin
      dashboard unaffected.
- [x] `public/players.html` still serves at `/players.html` (deliberately
      untouched) and does not shadow the `/players` route.
- [x] Root legacy suite (`vendor/bin/phpunit test/`) shows only the
      pre-existing failures.

## Merge gate

All tests green, ≥95% Phase 2 line coverage, every checkbox above ticked,
and `specs/roadmap.md` marks Phase 2 complete (with the `players.html`
item re-homed to draft tooling).
