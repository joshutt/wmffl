# Phase 10 — Dynamic quicklinks & draft date scheduling: Requirements

## Goal

Two small feature areas, landed together on one branch as one PR
(branch `quicklinks-draftdates`):

- **Part A — Dynamic quicklinks:** replace the static, hand-edited homepage
  "Other Links" list with an admin-managed set of links, each with an optional
  date window controlling when it appears.
- **Part B — Draft date scheduling:** replace the 25+ frozen per-season
  draft-date vote pages with a single member-facing vote form, plus a schedule
  builder on the existing Draft Dates admin tool that generates the rows the
  vote form and the existing tally view depend on.

## Decisions

1. **One branch, one PR.** Both parts are small; they land together
   (same pattern as Phases 4+5).
2. **Literal URLs only** for quicklinks — no `{season}` placeholder or token
   substitution. Season-specific links (Draft Order, Protection Costs) get
   their URL edited by the admin each year, along with their date window.
3. **Schedule builder merges on re-run:** add `draftdate` rows for newly
   selected dates, delete rows for dates no longer selected, and leave existing
   `attend` values and `draftvote.lastUpdate` untouched. A re-run also picks up
   owners added to the season since the last run (missing rows are created for
   every active owner). No wipe-and-regenerate; no block-if-exists.
4. **Merge bar:** PHPUnit unit tests for repository/service logic plus a
   manual fake-session E2E checklist (see `validation.md`). No WebTestCase
   functional tests.

## Part A — Dynamic quicklinks

### Legacy / current state

- `football/quicklinks.php`: static three-item list, season number hardcoded,
  re-edited by hand each year. No longer rendered anywhere (homepage is
  Symfony), but the file still exists.
- Already ported **as-is** (still static) to
  `symfony-app/templates/home/_quicklinks.html.twig`, included from
  `templates/home/index.html.twig`, rendered by `HomeController` (`/`), which
  passes `season`. Two of the three links embed the season in their URL:
  - Draft Order → `/history/{{ season }}Season/draftorder`
  - Protection Costs → `/history/{{ season }}Season/protectioncost`
  - Finances → `/history/teammoney` (evergreen)

### Scope

1. **Entity `App\Entity\QuickLink`** (table `quicklinks`):
   - `id` (auto PK), `label` (varchar), `url` (varchar),
     `startDate` (date, nullable), `endDate` (date, nullable),
     `active` (bool, default true), `sortOrder` (int).
   - Doctrine migration creates the table **and seeds the three current
     links** (with the literal current-season URLs, all evergreen/no window,
     active, sort order preserving today's display order) so the homepage
     doesn't go blank on deploy.
2. **`QuickLinkRepository::findVisible()`**: active AND today within
   `[startDate, endDate]`, where either bound may be null (open-ended).
   Bounds are **inclusive** (a link with `endDate` = today still shows).
   Ordered by `sortOrder`, then `id`.
3. **Homepage**: `HomeController` passes `findVisible()` results;
   `_quicklinks.html.twig` loops over them. Empty state: when no links are
   visible, the whole "Other Links" block (heading included) is not rendered.
4. **Admin CRUD**: `AdminQuickLinkController` at `/admin/quicklinks`
   (commissioner-gated via `AbstractAdminController::requireCommissioner`,
   same as other admin controllers):
   - List **all** links (including inactive / out-of-window) with their
     window, active flag, sort order, and a visible-today indicator.
   - Add and edit forms (label, url, start date, end date, active, sort
     order). CSRF-protected POSTs; `btn-wmffl` + `text-center` button styling.
   - Delete (hard delete, with CSRF) **and** activate/deactivate toggle —
     deactivation is the "keep it around for next season" path.
   - Link the page from the admin index alongside the other tools.
5. **Retire `football/quicklinks.php`** (delete; nothing includes it anymore —
   no redirect needed, it was an include partial, not a routed page).

## Part B — Draft date scheduling

### Legacy / current state

- **Vote form** `football/history/{year}Season/draftdate.php` (one frozen copy
  per season back to 2000): member-facing; requires login; queries the user's
  `draftdate` rows `BETWEEN '{season}-07-01' AND '{season}-10-01'`; renders a
  Y/N radio pair per date (default reflects stored `attend`); intro blurb text.
- **Processor** `football/history/common/processdraftdate.php`: counts `N`
  values in the POST; **more than 4 "No" votes → red alert, nothing saved**;
  otherwise updates `draftdate.attend` per date and stamps
  `draftvote.lastUpdate = now()` for that user+season, then re-renders the
  form with a green success alert. 16 per-season `processdraftdate.php`
  copies (2002–2017) bypass the common one.
- **Entities already exist**: `App\Entity\DraftDate` (composite PK
  user+date, `Attend` via `DraftDateAttendEnum`, default `Y`) and
  `App\Entity\DraftVote` (composite PK user+season, nullable `lastUpdate`).
- **Admin tally** `AdminDraftDatesController` (`/admin/draftdates/{season}`)
  reads per-date yes/no tallies (grouped by team, `MIN(Attend)` so any owner's
  No counts as the team's No) and lists teams whose owners all have
  `lastUpdate IS NULL`. It only *reads* — nothing today creates the rows;
  each season they've been inserted by hand.

### Scope

1. **Admin schedule builder**, added to `AdminDraftDatesController` /
   `admin/draftdates/index.html.twig`:
   - Step 1: pick a first and last possible date.
   - Step 2: a calendar/checkbox list of every date in that range,
     **default-checked: every Saturday and Sunday**; admin can check/uncheck
     any individual date.
   - Submit (CSRF-protected POST) runs the **merge** for every active owner of
     that season (`owners` table):
     - Missing `DraftVote(user, season)` rows created with
       `lastUpdate = null`; existing ones untouched.
     - For each owner × selected date, missing `DraftDate` rows created with
       `attend = 'Y'` (legacy default-yes-until-you-say-no model); existing
       rows keep their `attend`.
     - `DraftDate` rows for that season's window whose date is **not** in the
       selected set are deleted. "That season's window" = the legacy
       July 1 – Oct 1 span of the season, so dates outside the newly chosen
       range are cleaned up too.
   - Generation/merge logic lives in a `DraftScheduleService` so it's unit
     testable; the controller stays thin.
   - After submit, redirect back to the tally view (which now shows the rows).
2. **Member vote page** — new `DraftDateController`, route `/draftdate`:
   - Login required (`AuthenticationService`); logged-out visitors get the
     "must be logged in" message (match legacy behavior, no crash).
   - GET: the current season's `DraftDate` rows for the logged-in user
     (July 1 – Oct 1 window), Y/N radio per date reflecting stored values,
     intro blurb ported from the legacy page (genericized — no hardcoded
     "August 23th" default date), submit button.
   - POST (CSRF): **at most 4 "No" votes** — more than 4 rejects the whole
     submission with the legacy red-alert wording and saves nothing; otherwise
     update the user's `attend` values, stamp
     `DraftVote.lastUpdate = now()`, re-render with the green success alert.
   - The 4-No rule lives somewhere unit-testable (service or small validator),
     not inline in the controller.
   - Empty state: no schedule generated yet → friendly "no draft dates are up
     for a vote yet" message.
3. **Legacy deletion** (no redirects, per roadmap — these were per-season
   deep pages, not linked from anywhere current):
   - `football/history/{year}Season/draftdate.php` — all seasons.
   - `football/history/common/processdraftdate.php`.
   - The 16 per-season `history/{year}Season/processdraftdate.php` copies.

## Non-goals

- `{season}` URL token substitution in quicklinks (decision #2).
- WebTestCase functional tests (decision #4).
- Redesign of the admin draft-dates tally view (it stays as-is; the builder is
  added alongside).
- Per-season history pages (`draftorder`, `protectioncost`, hubs, …) — Phase 11.
- Announcing/locking the chosen draft date — out of scope; the admin still
  communicates the final date manually.

## Context / gotchas carried forward

- Fake-session E2E recipe needs `fullname` in the session (prior-phase gotcha).
- `php -S` doesn't route `.php` paths through Symfony — test legacy-path
  behavior accordingly; LegacyBridge 500s on missing paths rather than 404ing.
- `draftdate.Date`/`Attend` columns are capitalized in the schema
  (`UserID`/`Date`/`Attend`); `draftvote` uses lowercase `userid`/`season`.
- Homepage quicklinks partial currently receives `season` from
  `HomeController`; after Part A it no longer needs it.
