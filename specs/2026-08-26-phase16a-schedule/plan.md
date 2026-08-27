# Plan — Phase 16a: Standalone Schedule Page

## 1. Service layer

1. Create `symfony-app/src/Repository/ScheduleRepository.php`:
   - `getSeasonSchedule(int $season): array` — port the matchup query from
     `common/schedule.php` (schedule JOIN weekmap, LEFT JOIN teamnames x2),
     using DBAL `Connection` + named params, selecting real column names
     (not positional `$row[n]`) so the twig template and service stay
     readable: week, teama_name, teama_id, scorea, teamb_name, teamb_id,
     scoreb, weekname, displayDate, endDate, label, postseason.
   - `getByeWeeks(int $season): array` — port the bye-week query
     (nflteams/weekmap/nflgames), same param style.
   - `hasRows(int $season): bool` — small helper (`SELECT 1 FROM schedule
     WHERE season = :season LIMIT 1`) for the controller's fallback check.
2. Create `symfony-app/src/Service/ScheduleService.php`:
   - Depends on `ScheduleRepository` and `SeasonWeekService`.
   - `getSchedule(int $season): array` (or a small DTO) that:
     - Runs both repository queries.
     - Groups matchup rows by `(week, label)`, preserving query order.
     - For each group, computes the display label: `label` if non-empty,
       else `weekname`; and the date subheading, guarded against the
       zero-date (`0000-00-00`) — omit/blank the date line rather than
       rendering "January 0" or similar when `displayDate` is the zero
       date.
     - Attaches the matching bye-week string (if any) to each week group,
       replicating the "New York"/"Los Angeles" nickname-suffix rule from
       the legacy bye-list builder.
     - For each matchup, determines winner/loser ordering and whether to
       show scores, using the "this week" cutoff described in
       `requirements.md` (current week if `season == currentSeason`, else
       always past/17).
   - `resolveDefaultSeason(): int` — current season if `hasRows()` is true,
     else current season − 1. Used by the controller's no-season route.

## 2. Controller

3. Create `symfony-app/src/Controller/ScheduleController.php`:
   - `#[Route('/schedule/{season?}', name: 'schedule', requirements: ['season' => '\d+'])]`
   - `int $season = null` in the signature; when null, resolve via
     `ScheduleService::resolveDefaultSeason()`.
   - Fetch the schedule data and render
     `schedule/index.html.twig`.

## 3. Template

4. Create `symfony-app/templates/schedule/index.html.twig`:
   - `{% extends 'base.html.twig' %}`, `.cat`-style header
     ("`{{ season }} SCHEDULE`"), following `standings.html.twig`'s
     conventions (plain table, no bespoke CSS).
   - Auto-generated quick-jump nav strip at the top: one anchor per
     week/label group (e.g. "Week 1 | Week 2 | ... | Wild Card |
     Championship"), linking to `#`-anchors on each group's heading.
   - Per week/label group: heading with the display label, date
     subheading (omitted when the group has no date to show), bye-week
     line when present, then one row per matchup — team names linked via
     `path('team_schedule', {id: ..., season: season})`, scores shown or
     "vs" per the past/upcoming rule.

## 4. Nav + in-page link updates

5. Update `symfony-app/templates/base.html.twig`: change the hardcoded
   `/history/2025Season/schedule` nav link to `{{ path('schedule') }}`
   (no season — lets the controller resolve it, fixing the annual-edit
   problem the roadmap calls out).
6. Update `football/base/menu.php` the same way — link to `/schedule`
   (legacy code, so a plain href, not a Twig `path()`).
7. Update the known in-page season-hub links to the new per-season route
   (`/schedule/{year}`):
   - `football/history/{year}Season.php:12` pattern, 2000–2025 (spot the
     full set via `grep -rl "Season/schedule" football/history/`, not
     just the two examples the roadmap names).
   - `football/history/{year}Season.php:12` pattern, 1992–1999
     (`9Xschedule.php` links, e.g. `1992Season.php`).
   - `football/history/2001Season/lea010920.php:19`.

## 5. Delete legacy + add redirects

8. Delete:
   - `football/history/common/schedule.php`
   - `football/history/{year}Season/schedule.php` for 2000–2025 (26 files)
   - `football/history/{year}Season/9Xschedule.php` for 1992–1999 (8 files)
9. Add redirect routes (new controller, e.g.
   `LegacyScheduleRedirectController`, or extend
   `LegacyHistoryRedirectController` if that reads more naturally):
   - `/history/{year}Season/schedule` and `/history/{year}Season/schedule.php`
     → `/schedule/{year}`, for 2000–2025.
   - `/history/{year}Season/9Xschedule.php` → `/schedule/{year}`, for
     1992–1999.
   - All 301 (`Response::HTTP_MOVED_PERMANENTLY`).

## 6. Tests

10. `symfony-app/tests/Repository/ScheduleRepositoryTest.php` and/or
    `tests/Service/ScheduleServiceTest.php`: cover a normal post-2000
    season (dates render, byes present), a pre-2000 season (zero-date
    guard kicks in, byes empty), and a season with postseason
    `label`-grouped rows.
11. Controller test(s) for `ScheduleController`: no-season resolves to
    current or previous season correctly (mock/seed both cases), explicit
    `{season}` always renders that season directly (including a
    1992–1999 year), 404/invalid season handling if any.
12. Redirect controller test(s): each legacy path pattern 301s to the
    correct new URL.

## 7. Manual validation

13. Spot-check final scores for a couple of 1992–1999 seasons against the
    old static pages (see `validation.md`) before/as part of confirming
    the delete in step 8 is safe.
14. Follow `validation.md` end to end before merging.
