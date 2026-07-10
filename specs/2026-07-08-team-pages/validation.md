# Validation — Team Pages (Phase 3)

Mergeable when all of the below pass.

## Automated tests + coverage bar

- `cd symfony-app && vendor/bin/phpunit tests/` — all green.
- Coverage: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`,
  then **≥95% combined line coverage across this branch's new PHP
  files** (`TeamRepository`, `TeamController`, the legacy-redirect
  controller, `AdminTeamController`), measured from coverage.xml the
  same way as prior phases.

Required test coverage areas:

- Foundation: `resolveTeamId` matches id, abbrev, and
  lowercased/space-stripped name, and returns null otherwise; header
  data incl. co-owner "and"-joining and championship seasons; unknown
  team 404s on every `/team/{id}/*` route.
- Index: every current-season team appears under the right division
  with owner name and a working roster link; defunct/other-features
  cards render; squirrels page renders.
- Roster: full column set renders from a stubbed roster; player names
  link to `player_profile`; IR overrides the injury status; column
  header seasons flip correctly at `currentWeek <= 1`; transaction
  summary shows both "remaining" and "extra used" phrasings; empty
  roster renders cleanly.
- Schedule: completed weeks show result + score, current/future weeks
  opponent only; `schedule.label` beats `weekmap.weekname`; week 0
  falls back to the prior season; season dropdown lists only seasons
  played; the schedule view offers the head-to-head opponent selector
  and it links into the h2h view; h2h aggregate W-L-T/pct correct
  incl. the zero-games case; unknown `vs` id 404s; no season param on
  h2h (legacy `viewseasom` removed).
- History: all-time/playoffs/Toilet Bowl/per-season records with
  correct pct math; in-progress season excluded at week 0; past
  names/owners ranges with a mid-history change and a co-owner stint;
  playoff results Beat/Lost-to phrasing; league vs division titles
  split.
- Compare: form renders; both rosters side by side with profile links;
  parameters are bound (no raw interpolation — regression guard for
  the legacy injection).
- Redirects: each legacy URL 301s to the right new route, with
  `viewteam` as id, abbrev, and team name; `vsTeam` and
  `teamone`/`teamtwo` carried; missing/bogus `viewteam` lands on
  `/teams`.
- Admin: non-commissioner blocked from `/admin/teams*`; list shows all
  teams; edit form prefills; valid POST persists and redirects with
  flash; unknown id 404s.

## Manual end-to-end (real DB, dev server)

Use the fake-session recipe from prior phases: run
`php -d session.save_path=<dir> -S 127.0.0.1:8321 -t public`, write a
`sess_<id>` file (`isin|b:1;usernum|i:N;...`) and set the matching
`PHPSESSID` cookie — one member session, one commissioner session.

### Legacy parity (run BEFORE deleting `football/teams/` in task 7)

For one long-tenured team (multiple owners/names in its history) and
one newer team, compare legacy vs Symfony side by side:

- [ ] Roster: same players, same injury/IR flags, same acquired dates,
      same cost and pts columns, same free-transactions and
      roster-count lines.
- [ ] Schedule: same results and scores for the current season and one
      past season; in-progress week hidden identically.
- [ ] H2H vs one opponent: same game list and same aggregate record.
- [ ] History: identical per-season records, all-time totals, playoff
      results, titles, past owners, past names.
- [ ] Index: same teams under the same divisions with the same owners.

### Post-port checks

- [ ] `/teams`: division cards styled (`bg-div-*`), every team clicks
      through to its roster; Squirrels page loads; Kingsmen not a link;
      Compare Rosters and Waiver Wire Order links work.
- [ ] Team header on all three pages: logo (and one `fulllogo` team),
      owners, member-since, motto, trophy row for a champion team,
      pill nav highlights the active page.
- [ ] Roster table sorts by clicking column headers (tablesorter).
- [ ] Schedule season dropdown navigates; the new head-to-head
      selector on the schedule page reaches the h2h view; the h2h
      opponent dropdown navigates; player links from roster and
      compare land on the right profiles.
- [ ] Old URLs 301: `/teams/teamroster?viewteam={id}`, `?viewteam={abbrev}`,
      `?viewteam={name}`, `/teams/teamschedule?viewteam=X&vsTeam=Y`,
      `/teams/teamhistory?viewteam=X`, `/teams/compareteams`,
      `/teams/squirrels` — each lands on the equivalent new page.
- [ ] Nav "Teams" entries (Symfony base nav and legacy menu.php) point
      at the new routes; standings/homepage team links still work.
- [ ] Admin (commissioner session): edit a team's motto + active flag,
      save — flash shows, change visible on `/teams` and the team
      header immediately. Member session blocked from admin routes.

## Regression

- [ ] Untouched legacy routes still load via LegacyBridge
      (`/transactions/transactions`, `/history/`, `/stats/playerstats`,
      `/scores`).
- [ ] Homepage, `/articles`, `/players`, `/player/{id}`, standings,
      history standings, and admin dashboard unaffected.
- [ ] Root legacy suite (`vendor/bin/phpunit test/`) shows only the
      pre-existing failures.
- [ ] `football/teams/` is gone and no template/include references it
      (`grep -r "teams/" football/ symfony-app/templates/`).

## Merge gate

All tests green, ≥95% line coverage on the branch's new PHP files,
every checkbox above ticked (parity checks completed before deletion),
and `specs/roadmap.md` marks Phase 3 complete.
