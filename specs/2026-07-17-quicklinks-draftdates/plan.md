# Phase 10 — Dynamic quicklinks & draft date scheduling: Plan

Branch `quicklinks-draftdates`, one PR. Each task group should land as its own
commit (or a small number of commits). See `requirements.md` for scope and
decisions, `validation.md` for the merge bar.

## 1. QuickLink entity + data

1. `App\Entity\QuickLink` (table `quicklinks`): id, label, url,
   startDate (nullable date), endDate (nullable date), active (bool),
   sortOrder (int).
2. Doctrine migration: create table + seed the three current links
   (Draft Order `/history/2026Season/draftorder`, Protection Costs
   `/history/2026Season/protectioncost`, Finances `/history/teammoney`) —
   active, no date windows, sortOrder 1/2/3.
3. `QuickLinkRepository` with `findVisible(\DateTimeInterface $today)`:
   active AND (start IS NULL OR start <= today) AND
   (end IS NULL OR end >= today), ORDER BY sortOrder, id.
4. Unit tests for the window logic (null bounds, inclusive edges, inactive).

## 2. Homepage wiring

1. `HomeController`: inject `QuickLinkRepository`, pass `quicklinks` to the
   template (drop the now-unneeded `season` from the partial's inputs if
   nothing else uses it).
2. `_quicklinks.html.twig`: loop over `quicklinks`; render nothing at all
   (heading included) when the list is empty.
3. Delete `football/quicklinks.php`.

## 3. Admin quicklinks CRUD

1. `AdminQuickLinkController` (`/admin/quicklinks`), extending
   `AbstractAdminController` with the commissioner gate.
2. Templates under `templates/admin/quicklinks/`: list (all links, window,
   active flag, visible-today indicator, sort order) + add/edit form.
3. POST handlers: create, update, delete (hard), activate/deactivate toggle —
   all CSRF-protected; `btn-wmffl` + `text-center` styling.
4. Link the page from the admin index.

## 4. Draft schedule builder (admin)

1. `DraftScheduleService`:
   - `candidateDates(first, last)`: every date in range flagged
     default-checked when Sat/Sun.
   - `applySchedule(season, selectedDates)`: the merge — create missing
     `DraftVote` rows (lastUpdate null) for every active owner; create missing
     `DraftDate` rows (attend Y) for owner × selected date; delete `DraftDate`
     rows in the season's July 1 – Oct 1 window not in the selected set;
     never touch existing attend values or lastUpdate. One transaction.
2. Unit tests: Sat/Sun defaults across month boundaries; merge adds/deletes/
   preserves; new-owner pickup on re-run.
3. `AdminDraftDatesController` + `admin/draftdates/index.html.twig`: range
   picker → checkbox calendar (server-rendered two-step form) → CSRF POST →
   `applySchedule` → redirect to the tally view with a flash.

## 5. Member vote page

1. `DraftDateController`, route `/draftdate`:
   - GET: login check, current season's rows for the user (July 1 – Oct 1),
     Y/N radios, blurb, empty-state message when no schedule exists.
   - POST: CSRF; max-4-No rule (unit-testable helper — reject all with red
     alert on >4); update attend rows; stamp `DraftVote.lastUpdate = now()`;
     green success alert.
2. Template `templates/draftdate/index.html.twig` matching the legacy layout
   (Bootstrap rows, `btn-wmffl` submit).
3. Unit tests: 4 No accepted, 5 No rejected, nothing persisted on rejection.

## 6. Legacy deletion, docs, final pass

1. Delete all `football/history/{year}Season/draftdate.php`,
   `football/history/common/processdraftdate.php`, and the 16 per-season
   `processdraftdate.php` copies. No redirects.
2. Update `specs/roadmap.md`: move Phase 10 into Done with a summary entry.
3. Run the full `validation.md` checklist; fix anything it turns up.
