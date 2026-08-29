# Plan — Phase 16b: Draft Results under Transactions

## 1. Repository layer

1. Extend `symfony-app/src/Repository/DraftPickRepository.php` (DBAL
   `Connection`, named params, alongside the existing
   `getNumberOnePicks()`):
   - `getAsOfDate(int $season): ?string` — the season's `weekmap` week-1
     `ActivationDue`, or null when the season has no week-1 row (future
     seasons). Null means "current roster" downstream.
   - `getBoard(int $season, array $filters, ?string $asOf): array` — port
     the pick-list query with three changes from legacy:
     - `LEFT JOIN players` instead of the inner join, so undrafted rows
       survive (decision 1) — 2006 is 37 of 120, and the current season's
       board is mostly blank until its draft runs.
     - `teamnames` joined **twice and as LEFT joins** — once on `teamid`
       (owning franchise) and once on `orgTeam` (original franchise) —
       each `COALESCE`d to the `team` table's current name. Every
       reachable season has `teamnames` rows today, so this is a rollover
       guard, not a live fix: it keeps the board from collapsing to zero
       rows (legacy's inner join) in the window after a new season becomes
       current and gets its `draftpicks` skeleton but before its
       `teamnames` rows exist.
     - Selects `orgTeam` and the original franchise's name alongside the
       owner's, for the provenance note (decision 5).
     Otherwise selects round, pick, teamid, team name, firstname,
     lastname, pos, nflteamid. Ordering: `ORDER BY round, pick IS NULL,
     pick` — no reachable season has a NULL pick today (only the cut-off
     future seasons do), so the NULL clause is a rollover guard like the
     name fallback above. All five
     filters bound as parameters, each applied only when non-null/non-`ALL`;
     the team filter matches the **owning** franchise only (decision 5).
   - `getFilterOptions(int $season, ?string $asOf): array` — the two
     legacy distinct lookups (teams for the season from `teamnames`,
     falling back to `team` for seasons `teamnames` doesn't cover; NFL
     teams from `nflrosters` as of `$asOf`) plus the two the legacy form
     hardcoded and got wrong: the season's actual round range and pick
     range, and the distinct positions actually drafted that season.
   - `getSeasonsWithPicks(): array` — every season with `draftpicks` rows,
     ascending. The service filters this to the reachable range (no later
     than the current season) before it drives prev/next links or the 404
     check — the repository stays a plain data accessor and the cut-off
     lives in one place.
   - `getSeasonsWithSelections(): array` (or a `hasSelections(int)`
     helper) — seasons with at least one non-NULL `playerid`; drives
     default-year resolution.
2. Keep the `nflrosters` as-of join in the shape `getNumberOnePicks()`
   already uses (correlated subquery with `dateon <= :asOf AND (dateoff IS
   NULL OR dateoff >= :asOf)`), degrading to `dateoff IS NULL` when
   `$asOf` is null.

## 2. Service layer

3. Create `symfony-app/src/Service/DraftResultsService.php`, depending on
   `DraftPickRepository` and `SeasonWeekService`:
   - `resolveDefaultYear(): int` — current season if it has any drafted
     picks, else the most recent season that does.
   - `getBoard(int $year, array $filters): array` — runs the repository
     calls and returns the view model: rows (with a display-ready
     `selection`/`pos`/`nfl` per row, em dash when undrafted, and a
     `fromFranchise` set only when `orgTeam !== teamid` — null otherwise,
     so the template never has to compare ids), filter option lists, the currently applied filters echoed back for the form,
     the as-of date for the subheading, and `prevYear`/`nextYear` (null at
     each end of `getSeasonsWithPicks()`).
   - `isReachable(int $year): bool` — for the controller's 404: true when
     the year has `draftpicks` rows **and** `$year <= SeasonWeekService::
     getCurrentSeason()` (decision 6). A future season and a season that
     never existed both come back false, so both 404 identically.
   - `resolveDefaultYear()` and the prev/next computation both draw from
     the same reachable-range list, so no path can hand back a future
     year.
   - Normalizes filter input: `ALL`, empty string and absent all collapse
     to null; round/pick cast to int; team/pos/nfl kept as strings.

## 3. Controller + template

4. Create `symfony-app/src/Controller/DraftResultsController.php`:
   - `#[Route('/transactions/draftresults/{year?}', name: 'transactions_draft_results', requirements: ['year' => '\d+'])]`
   - `?int $year = null`; when null, `resolveDefaultYear()`. When
     `isReachable()` is false — no rows, or a future season —
     `throw $this->createNotFoundException()`.
   - Reads the five filters off the query string, renders
     `transactions/draftresults.html.twig`.
5. Create `symfony-app/templates/transactions/draftresults.html.twig`:
   - Extends `base.html.twig`; includes `_transmenu.html.twig`; `.cat`
     header (`{{ year }} DRAFT RESULTS`) with the as-of date as a
     subheading (omitted when there's no as-of date).
   - Prev/next year links flanking the heading (`« 2025` / `2027 »`),
     each omitted at the range edge — and the current season *is* the
     upper edge, so the current year's board shows no "next" link at all
     (decision 6). They preserve no filters (a filtered view is
     year-specific).
   - GET filter form with the five derived dropdowns and a `btn-wmffl`
     submit, plus a "Clear filters" link back to the bare year URL when
     any filter is active.
   - Board table (Rd / Pick / Franchise / Selection / Pos / NFL), keeping
     the legacy `id="pick_{round}_{pick}"` row anchors for archival deep
     links, em dashes on undrafted rows, and a row count / "no picks match
     these filters" empty state.
   - Traded picks show the owning franchise with a muted `from
     <original franchise>` line beneath it in the same cell (small,
     secondary text — it must not compete with the owner's name, since
     most rows won't have one).
6. Add a "Draft Results" `btn-wmffl` to
   `symfony-app/templates/transactions/_transmenu.html.twig`, linking to
   `path('transactions_draft_results')` (no year — the controller
   resolves it, so it never goes stale).

## 4. Admin page

7. Create `symfony-app/src/Controller/Admin/AdminDraftResultsController.php`
   extending `AbstractAdminController`:
   - `#[Route('/admin/draftresults')]` class prefix; `GET /{year}`
     (`admin_draft_results`) lists the season's pick rows with the current
     selection and an Edit control per row; the year defaults the same way
     the public page does, and applies the same future-season cut-off —
     a commissioner cannot edit a 2027 pick either (decision 6). The
     `POST` endpoint re-checks reachability rather than trusting the form.
   - `POST /{year}/pick/{id}` (`admin_draft_results_set`): set or clear
     that pick's player. `requireCommissioner` first, then
     `assertCsrfToken`, then persist via the Doctrine `DraftPick` entity
     (`setPlayer(null)` clears). Redirect back with a flash.
   - Player lookup reuses `PlayerRepository::searchPlayers()`, matching
     `AdminPlayerController`'s search shape.
   - Guard the `draftpicks` `Season_playerid_uniq` constraint: reject (with
     a flash, not a 500) an attempt to assign a player already drafted in
     that season.
8. Create `symfony-app/templates/admin/draftresults/index.html.twig`
   following `templates/admin/` conventions (extends the admin base,
   `btn-wmffl` in `text-center` wrappers), and link it from the admin
   dashboard (`templates/admin/dashboard/index.html.twig`) alongside the
   other season-data tools.

## 5. Link updates

9. Repoint all 19 legacy link sites at `/transactions/draftresults/{year}`:
   - 11 flat hub pages, `football/history/{year}Season.php` for 2007–2017
     (line ~25, `2017Season.php` is line 21's extensionless variant).
   - 8 season-directory nav lists,
     `football/history/{year}Season/index.php` for 2018–2025 (two markup
     styles: the old `<td><A HREF="draftresults">` for 2018–2019 and the
     Bootstrap `nav-item` list for 2020–2025).
   Re-derive the full set with `grep -rn "draftresults" football/` rather
   than trusting this list.
10. Note in `specs/roadmap.md`'s Phase 16 entry that the generic season-hub
    template, when built, links `Draft Results` to
    `/transactions/draftresults/{year}` — do not resurrect a history-local
    draft page. (Roadmap edits stay uncommitted until Josh approves them.)

## 6. Delete legacy + redirects

11. `git rm` `football/history/common/draftresults.php` and all 19
    `football/history/{year}Season/draftresults.php` wrappers (2007–2025).
12. Create `symfony-app/src/Controller/LegacyDraftResultsRedirectController.php`
    (Phase 16a's `LegacyScheduleRedirectController` is the template): one
    route, `/history/{year}Season/{file}` with
    `requirements: ['year' => '20(0[7-9]|1[0-9]|2[0-5])', 'file' => 'draftresults|draftresults\.php']`,
    301ing to `transactions_draft_results` with that year. `methods:
    ['GET', 'POST']` so the legacy POST-to-self filter form redirects
    rather than 405ing.

## 7. Tests

13. `tests/Repository/DraftPickRepositoryTest.php` additions: a complete
    season (2019 — every pick filled), the partial historical season
    (2006 — 37 of 120, undrafted rows present with blank selection), the
    in-progress season (2026), the rollover guards exercised directly
    against a season the cut-off hides (2027 — NULL picks order last
    within a round, and both franchise names resolve via the `team`
    fallback rather than coming back blank; the repository is reached
    directly here, since the route 404s), each
    filter applied individually and in combination, the as-of date
    resolution (including the null/future-season fallback), and traded
    picks: `orgTeam` returned alongside `teamid`, the original franchise
    named by its **season-specific** `teamnames` value, and the team
    filter matching only the owning side of a traded pick.
14. `tests/Service/DraftResultsServiceTest.php`: default-year resolution
    (current season with selections → itself; without → most recent that
    has them), filter normalization (`ALL`/empty/absent all mean no
    filter), prev/next year computation at both range edges, and
    `fromFranchise` being null on an untraded pick and the original
    franchise's name on a traded one. Plus the cut-off itself:
    `isReachable()` false for a future season and for an unknown year,
    true for the current season and for a past skeleton-only season
    (2005), and `resolveDefaultYear()` never returning a future year even
    when `draftpicks` holds later seasons.
15. `tests/Controller/DraftResultsControllerTest.php`: no-year resolves,
    explicit year renders (2006 and a modern year), unknown year 404s,
    a filtered URL renders the filtered board and echoes the selection
    back into the form, and a season with traded picks renders the
    "from <franchise>" note on exactly those rows and no others. Plus:
    a future season 404s, the current season's board renders no "next"
    link, and 2005 still renders.
16. `tests/Controller/AdminDraftResultsControllerTest.php`: non-commissioner
    redirected, missing/invalid CSRF 403s, set-player and clear-player
    both persist, duplicate-player-in-season rejected with a flash, and a
    future season 404ing on both the GET page and the POST endpoint.
17. `tests/Controller/LegacyDraftResultsRedirectControllerTest.php`: both
    filename forms for a sample of years 301 to the right new URL, and a
    POST redirects too.

## 8. Manual validation

18. Diff the new board against the legacy rendering for a few seasons
    before the delete lands (see `validation.md`), then follow
    `validation.md` end to end before opening the PR.
