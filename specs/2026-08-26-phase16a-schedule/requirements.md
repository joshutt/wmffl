# Requirements — Phase 16a: Standalone Schedule Page

## Summary

Replace 34 legacy per-season schedule pages (26 DB-backed year wrappers for
2000–2025 + `common/schedule.php`, and 8 hand-written static pages for
1992–1999) with a single Symfony route, `/schedule/{season?}`, backed by one
service that drives all 34 seasons from the database.

This is the carve-out documented in `specs/roadmap.md` under "Phase 16a —
Standalone schedule page." Phase 16 (per-season history hub) depends on this
landing first.

## Scope

In scope:
- A `ScheduleService` (or repository + service) porting the two queries from
  `football/history/common/schedule.php`: the season matchup query
  (`schedule` joined to `weekmap`/`teamnames`) and the NFL bye-week query
  (`nflteams`/`nflgames`/`weekmap`).
- `ScheduleController` on `/schedule/{season?}` (`season` optional int,
  `requirements: ['season' => '\d+']`).
- A new Twig template rendering the season's schedule week by week.
- Updating both hardcoded nav links (`symfony-app/templates/base.html.twig`
  and `football/base/menu.php`) to the new route.
- Updating the known in-page season-hub links that point at the old URL
  patterns (`history/{year}Season.php` hub pages and
  `history/2001Season/lea010920.php`) to the new route.
- Deleting all 34 legacy schedule files plus `common/schedule.php`, replaced
  with 301 redirects for archival URLs.
- Spot-checking a couple of 1992–1999 seasons' final scores against the old
  static pages before deleting them (those pages were hand-typed and may
  have transcription drift).

Out of scope:
- Phase 16 itself (the per-season history hub content — champion/runner-up
  blurbs, draft results, etc.).
- Boxscores (Phase 17).
- Any backfill — the roadmap confirmed `schedule`/`weekmap` already have
  full rows for 1992–1999, so no data migration is needed here.

## Default-season resolution

- `/schedule` (no season): show the current season if `schedule` has any
  rows for it; otherwise fall back to the previous season (a season's rows
  aren't populated until matchups are set, so early in the year the new
  season has nothing yet).
- `/schedule/{season}` (explicit, including any year 1992–1999): always
  renders that season directly, no fallback logic.
- Use the existing `SeasonWeekService` for "current season," matching the
  convention already used by `HistoryStandingsController`.

## Data / rendering decisions

- **Zero-date guard**: `weekmap.displayDate`/`enddate` are `0000-00-00` for
  1992–1999. When the date is the zero date, render just the week label
  (`weekmap.weekname`, or `schedule.label` when present) instead of a
  computed date subheading.
- **Bye weeks**: `nflgames` has zero rows before 2000, so the bye list is
  naturally empty for 1992–1999 — matches the static pages, which never
  showed byes. No special-casing needed.
- **Past vs. upcoming games**: for games in weeks before "this week" (current
  week for the season being viewed, or week 17 — i.e. always "past" — for
  any season earlier than the current one), show final scores with the
  winner listed first. For games in the current or a future week, show
  "vs" with no scores, matching legacy behavior.
- **Grouping**: group rows by `(week, label)` in the order returned by
  `ORDER BY week, label, MD5(CONCAT(t1.name, t2.name))` (preserves legacy's
  incidental-but-stable ordering, including how postseason rounds with a
  shared week number but different `label` values get separate blocks).

## Decisions from user Q&A (2026-08-26)

1. **Visual style — modern lightweight.** Follow the convention already
   established by `history/standings.html.twig` (extends `base.html.twig`,
   `.cat` section header, plain table, no bespoke CSS) rather than
   replicating the legacy `SLTables1`/`bg0`–`bg4` colored-block styling.
   No new CSS file.
2. **Quick-jump nav — auto-generated.** The legacy per-year wrapper
   hardcoded a "Week1 | Week2 | ... | Playoffs | Championship" anchor strip
   above the table; it lived in the wrapper (being deleted), not in the
   shared `common/schedule.php` being ported, so it doesn't carry over for
   free. Build an equivalent from the actual week/label groups present for
   the season being rendered (works for every season, including irregular
   playoff labels, and is never stale).
3. **Team links — link team names.** Each team name in the schedule links
   to that team's page via the existing `team_schedule` route
   (`/team/{id}/schedule/{season?}`), matching the convention already used
   by `history/standings.html.twig`. `teamnames.teamid` is the same id
   `team_schedule` expects.

## Conventions to follow

- Repository/service style: DBAL `Connection` with named parameters,
  matching `StandingsRepository`/`HistoryStandingsController` (not the
  legacy Doctrine ORM setup — this is pure historical-data reads).
- Controller style: match `HistoryStandingsController`'s shape (thin
  controller, service does the work, `compact()` into `render()`).
- Redirect controller: a small dedicated controller in the same family as
  `LegacyHistoryRedirectController`, or an addition to it if appropriate —
  301 (`Response::HTTP_MOVED_PERMANENTLY`) for every archival path.

## Branch

Work happens on `phase16a-schedule` (already created, currently checked
out; roadmap entry for Phase 16a already committed at `18c1b04`).
