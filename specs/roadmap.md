# Roadmap

Implementation proceeds one feature area at a time: pick an area, migrate it
fully to Symfony in small phases, then move to the next area. Each phase
should be small enough to land as its own PR.

## Done

- Standings: `StandingsController`, `StandingsCalculatorService`
- History standings: `HistoryStandingsController` (`/history/standings/{season}/{week}`)
- Team model migrated to `App\Model\Team`

## Phase 1 — Articles

Legacy: `football/article.php`, `football/article/` (`articleDisplay.php`,
`articleUtils.php`, `list.php`, `view.php`, `view-snip.php`, `preview.php`,
`process.php`, `publish.php`, `confirm.php`)

1. Article entity/repository + read-only display (`view.php`, `view-snip.php`,
   the homepage article-card block in `article.php`) and list page
2. Article authoring/publishing flow (`process.php`, `publish.php`,
   `preview.php`, `confirm.php`) — involves writes, migrate carefully

## Phase 2 — Player Profiles (in progress)

Legacy: none (new functionality) — currently being built directly in Symfony.

1. `Player` entity + `PlayerRepository` (roster/history/season-stat queries)
2. `PlayerProfileController` (`/player/{id}`) + `player/profile.html.twig`
3. Wire up navigation/links from roster and team pages to player profiles
4. Retire `symfony-app/public/players.html` static prototype once the real
   page covers its use case

## Phase 3 — Teams

Legacy: `football/teams/` (`roster.php`, `display.php`, `teamroster.php`,
`teamschedule.php`, `teamhistory.php`, `h2h.php`, `compareteams.php`,
`indschedule.php`)

1. Team roster page (builds on Phase 2's `Player` entity)
2. Team schedule page
3. Team history / head-to-head / compare-teams pages

## Phase 4 — Transactions

Legacy: `football/transactions/` (`list.php`, `transactions.php`,
`injuredReserve.php`, `protections.php`, `trades/`, `draftorder/`)

1. Transaction list (read-only view)
2. Waiver order / waiver picks
3. IR moves, protections, trades (these involve writes — migrate carefully,
   one action at a time)

## Phase 5 — Stats

Legacy: `football/stats/` (`playerstats.php`, `leaders.php`, `powerlist.php`,
`powerrate.php`, `weekbyweek.php`, `injuryReport.php`)

1. Player stats / leaders pages
2. Power rankings
3. Week-by-week and injury report pages

## Phase 6 — History (remaining)

Legacy: `football/history/` (`pastchamps.php`, `pastdrafts.php`,
`alltimerecords.php`, `recordseason.php`, `recordsweek.php`,
per-season pages)

1. All-time records / past champions / past drafts
2. Per-season pages — likely a single generic Symfony route/template driven
   by data, replacing the 30+ individual `{year}Season.php` files

## Phase 7 — Remaining odds and ends

Legacy: `login/`, `activate/`, `forum/`, `rules/`, `quicklinks.php`,
`info.php`, `scores.php`

1. Auth (login/activate) — highest risk, do last and carefully
2. Static/low-traffic pages (rules, info, quicklinks, forum)
3. Scores

## Final phase — Decommission legacy

1. Remove `LegacyBridge` fallback
2. Delete `/football/`, legacy `bootstrap.php`, legacy Doctrine setup,
   `conf/db.ini`
3. Drop `ext-mysqli` and legacy Doctrine deps from root `composer.json`
