# Requirements — Player Profiles (roadmap Phase 2)

Roadmap Phase 2: player profile pages, new functionality built directly in
Symfony (no legacy counterpart to port). Branch: `player-profiles`, built on
top of `player-pages`, which already carries the core implementation
(commits `654d2de` + `47d29a3`, unmerged): `App\Entity\Player`
(`newplayers` table), `PlayerRepository` (current-roster / roster-history /
season-stats queries), `PlayerProfileController` (`/player/{id}`), and
`templates/player/profile.html.twig`.

## Scope

1. **Backfill tests for the existing implementation** — the committed
   controller/repository/template have zero coverage; they get the same
   test treatment as if built fresh in this phase.
2. **`/players` index page** (new) — a searchable, browsable player list in
   Symfony so profiles are discoverable without going through a roster;
   linked from the site navigation.
3. **Legacy link wiring** (roadmap item 3, partially done) —
   `football/teams/roster.php` already links player names to
   `/player/{id}`; wire the remaining legacy **team** pages that render
   player names (e.g. `compareteams.php`; audit the rest of
   `football/teams/`) with minimal edits.
4. **Admin player tooling** (per mission.md: a feature isn't done until
   admins can manage it without raw DB access) — an admin page to find a
   player and correct their data.

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Existing commits | Build on them as-is | The committed entity/repository/controller/template are treated as done. No refactor of the raw-DBAL queries in `PlayerRepository` — direct `fetchAllAssociative` matches the established pattern in `ScoresRepository`/`StandingsRepository` for read-heavy aggregation queries. |
| `players.html` prototype | **Not** retired here | Roadmap item 4 says to retire it, but the file is actually a "Live Draft Board with Announcer" prototype (Tailwind CDN + AWS Polly) — its use case is draft tooling, not player profiles. Re-home the roadmap item to a future draft-tooling phase instead of deleting the file. |
| Legacy link breadth | `football/teams/` only | Stats pages (`playerstats.php`, `leaders.php`, …) and transaction pages also show player names, but they're Phase 4/5 migration targets — they'll link to profiles when they're ported, not patched now. |
| Admin tooling shape | Edit-only, no create/delete | Players enter the system via stat imports; admins only need to *correct* records (names, position, NFL team, number, retired flag, bio fields). Duplicate-player merging is out of scope (separate, riskier problem). |
| Index page shape | Server-rendered search + pagination | GET form (`?q=`, `?team=`, `?nfl=`, `?pos=`, `?inactive=`, `?page=`) per the tech-stack no-SPA rule; same pagination style as `/articles`. |
| "Active" player definition | `retired IS NULL` | Matches legacy usage (`transactions/list.php` filters retired players with `retired is not null`); the index hides retired players by default with an opt-in to include them. |

## Profile page (already built — documented for the test backfill)

- `GET /player/{id}` (`player_profile`): 404s on unknown id (note: Symfony
  404s fall through to LegacyBridge, which 500s "Unhandled legacy mapping"
  in dev — pre-existing site-wide behavior, not fixed here).
- Shows: player bio (from `newplayers`), current WMFFL roster spot (team +
  date on, if any), roster history (stints with team names per season via
  `teamnames`, games activated, active points), and per-season stat totals
  joined with `playerscores` (total vs active points).
- Stat table only renders columns that are non-zero for at least one of the
  player's seasons (`activeStatColumns` filtering in the controller).

## `/players` index design

- `GET /players` (name `player_index`), same controller
  (`PlayerProfileController`) — new repository method for the listing.
- Columns: name (link to profile), position, NFL team, current WMFFL team
  (if rostered).
- Filters (all combinable):
  - **Name** (`q`): substring match against first name *and* last name.
  - **WMFFL team** (`team`): dropdown of current teams (all by default),
    plus a **"Free Agents"** option matching players with no current
    roster row (`roster.DateOff IS NULL` absent).
  - **NFL team** (`nfl`): dropdown (all by default).
  - **Position** (`pos`): dropdown (all by default).
  - **Include non-active players** (`inactive`): checkbox, off by
    default — the default result set is active players only
    (`retired IS NULL`); checking it adds retired players.
- Paginated at 50/page; filters persist across pagination links. Default
  view (no filters) is browsable, not empty.
- Navigation: "Players" link added to the main nav in `base.html.twig`.
- No route conflict: the static prototype serves at `/players.html`, and
  legacy `stats/playerlist.php` lives under `/stats/`.

## Admin player tooling design

- `AdminPlayerController` extending `AbstractAdminController`:
  - `GET /admin/players` — search box (name substring) + result list; no
    unfiltered full dump required (thousands of rows).
  - `GET/POST /admin/players/{id}/edit` — edit form for: lastname,
    firstname, pos, NFL team, number, retired flag, height, weight, dob,
    draftTeam, draftYear. Persist via the entity/EM.
- Validation: lastname required (non-empty after trim); other fields
  nullable per the schema.
- Templates under `templates/admin/player/`; `btn-wmffl` buttons in
  `text-center` wrappers; "Players" link in the admin sidebar.

## Out of scope

- Retiring/porting `public/players.html` (draft-board prototype — future
  draft-tooling work; roadmap updated to say so).
- Links from legacy stats/transactions pages (added when those phases
  migrate).
- Admin create/delete/merge of players.
- Player photos, week-by-week stat breakdowns on the profile (Phase 5
  stats territory), game logs.
- Any schema changes — Phase 2 is read-only against existing tables except
  for admin edits to `newplayers` rows.

## Risks / notes

- `PlayerRepository` queries join `roster`, `teamnames`,
  `revisedactivations`, `playerscores`, `stats` — column-name casing is
  inconsistent across these legacy tables (`PlayerID` vs `playerid`);
  tests against the real schema matter more than mocks here.
- Legacy team pages are procedural PHP echoing table rows — link edits must
  stay minimal (wrap the existing name output in an anchor, nothing more)
  to avoid destabilizing pages that Phase 3 will replace anyway.
- The stat-column label map lives in the controller; the index page must
  not need it (totals only), so no extraction is forced.
