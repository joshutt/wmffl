# Validation — Phase 16a: Standalone Schedule Page

Implementation is ready to merge when all of the following hold.

## Automated tests

- [x] `symfony-app/vendor/bin/phpunit tests/` passes in full (existing
      suite stays green; use `--coverage-clover coverage.xml` if coverage
      is requested per project convention, never `--coverage-text`).
- [x] New repository/service tests cover: a post-2000 season (dates +
      byes render correctly), a pre-2000 season (zero-date guard, empty
      byes), and a season with postseason `label` grouping.
- [x] New controller tests cover: `/schedule` with no season resolving to
      current season (when `schedule` has rows) and falling back to the
      previous season (when it doesn't); `/schedule/{season}` rendering
      that exact season for a representative 2000+ year and a 1992–1999
      year.
- [x] New redirect-controller tests cover every legacy path pattern being
      replaced (both `.php` and extensionless where legacy had both) and
      assert a 301 to the correct `/schedule/{year}` URL.

## Manual / data spot-check

- [x] For at least 2 seasons in 1992–1999, compare every game's final
      score on the new `/schedule/{year}` page against the corresponding
      legacy static page (`football/history/{year}Season/9Xschedule.php`)
      before it's deleted — confirms no transcription drift between the
      hand-typed page and the `schedule` table. (1992 and 1995 checked,
      via `git show HEAD:...` against the deleted files; every score
      matched exactly, including both postseason rounds.)
- [x] Spot-check at least 1 season 2000–2025 against its current legacy
      rendering (before deletion) for: correct week grouping/order, correct
      winner-first-with-scores for past weeks, correct "vs"-no-score for
      current/future weeks, and correct bye-week list. (2024 and 2000
      checked live against dev DB.)
- [x] Confirm `/schedule` (no season) matches expectations both mid-season
      (current season has rows) and check the early-season fallback logic
      by testing against a season that legitimately has zero `schedule`
      rows for the current year, if timing allows — otherwise verify by
      code inspection / a unit test substituting for a live early-season
      check. (Verified live: 2026 has zero `schedule` rows yet, so
      `/schedule` correctly fell back to 2025; also covered by
      `ScheduleServiceTest`/`ScheduleControllerTest`.)
- [x] Load `/schedule/1992` through `/schedule/2025` and visually confirm
      no season renders a blank/garbled date line (the zero-date guard is
      working across the whole 1992–1999 range, not just the sampled
      seasons above). (Full 1992–2025 sweep via local `php -S` against
      dev DB: all 34 seasons 200, no garbled/blank date lines, no PHP
      warnings in the server log.)
- [x] Confirm the quick-jump nav strip's anchors work end-to-end (clicking
      each jumps to the right week/label block) for a season with a
      typical week set and a season with postseason rounds. (Confirmed
      live for 2024, a postseason-rounds season: anchors are unique per
      week/label group — this caught and fixed a real bug where two
      postseason groups sharing a week number collided on the same
      anchor.)
- [x] Confirm team names on the schedule link to the correct team page
      (`team_schedule` route) for a couple of teams, including a team
      whose name changed across seasons (name shown should be the
      season-specific `teamnames.name`, link should still resolve to the
      right team). (Confirmed live: teamid 1 renamed Archers → Archers
      Who Say Ni → War Eagles across eras; `/schedule/1995` shows
      "Archers" linking to `/team/1/schedule/1995`.)
- [x] Confirm both nav links (`base.html.twig` top nav, `football/base/menu.php`
      legacy nav) point at the new route and no longer need an annual
      manual edit (i.e. they link to `/schedule` with no hardcoded year).
      (Confirmed live on both a Symfony-rendered page and a
      LegacyBridge-rendered page.)
- [x] Spot-check a couple of the in-page season-hub links identified in
      `plan.md` step 7 (e.g. a `{year}Season.php` hub page, and
      `lea010920.php`) to confirm they land on the correct new URL.
      (Confirmed live: `/history/1995Season.php` links to `/schedule/1995`,
      `/history/2001Season/lea010920.php` links to `/schedule/2001`.)

## Cleanup confirmation

- [x] All 34 legacy schedule files + `common/schedule.php` are deleted.
- [x] Every legacy schedule URL pattern (both `2000Season/schedule[.php]`
      and `9Xschedule.php` styles) 301s to `/schedule/{year}` rather than
      falling through to a 404 or the LegacyBridge.
- [x] No remaining references to the deleted legacy files anywhere in
      `football/` or `symfony-app/` (`grep -rn "schedule.php\|9Xschedule"
      football/ symfony-app/templates/` comes back clean aside from the
      redirect controller itself).

## Sign-off

- [x] Josh has reviewed the rendered page for at least one 1990s season,
      one 2000s–2010s season, and the current season, and confirms it's
      an acceptable replacement for the legacy pages before the branch is
      merged. (Confirmed 2026-08-26.)

## Follow-up bug found and fixed after initial sign-off review

Relocated NFL franchises (Oakland→Las Vegas, San Diego→LA Chargers,
St. Louis→LA Rams) showed as on bye every week for every pre-move
season, because `nflgames` keeps each game under the code the franchise
used that season while `nflteams` only has one row per franchise holding
today's code. Fixed in `ScheduleRepository` with a `RELOCATED_CODES` map
translating retired codes to current ones before the bye-week join.
Verified live (2019 and 2015) and covered by a repository test.
