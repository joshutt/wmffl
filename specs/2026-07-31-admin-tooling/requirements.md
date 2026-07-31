# Phase 12 — Admin tooling: Requirements

## Goal

Two small, self-contained admin-only tools, landed together on one branch as
one PR (branch `phase12-admin-tooling`), per the roadmap's Phase 12 grouping:

- **Part A — Quicklinks drag-and-drop ordering.** Replace the manual
  `sortOrder` number field on `/admin/quicklinks` with drag-and-drop
  reordering on the index page.
- **Part B — Admin config editor.** Build a generic CRUD tool over the
  `config` table (`App\Entity\Config` / `ConfigRepository` already exist as
  untouched scaffolding) at `/admin/config`.

## Decisions

1. **One branch, one PR.** Both parts are small and unrelated in domain but
   grouped by the roadmap as "admin tooling"; same pattern as prior
   multi-part phases (e.g. Phase 10).
2. **Drag-and-drop library: SortableJS via CDN.** The app already loads
   jQuery, Bootstrap, and js-cookie from CDNs in `templates/base.html.twig`
   (no local vendoring for those three); SortableJS follows the same
   pattern — one `<script src="https://cdn.jsdelivr.net/npm/sortablejs@.../Sortable.min.js">`
   tag, no jQuery dependency needed (SortableJS is vanilla JS). Chosen over
   jQuery UI Sortable (would drag in jQuery UI's CSS/JS for one widget) and
   over hand-rolled HTML5 drag-and-drop (more code, weaker touch support).
3. **Drop the manual `sortOrder` field once drag-and-drop lands.** The
   admin edit form (`templates/admin/quicklinks/edit.html.twig`) removes its
   `sortOrder` number input; the index page's drag-and-drop becomes the only
   way to reorder. New links still default to end-of-list (existing
   behavior in `AdminQuickLinkController::new()`).
4. **Config editor stays fully generic — no key-prefix special-casing.**
   The `config` table mixes real settings (`draft.hangout.url`,
   `protections.deadline`, ...) with per-team/per-user draft-runtime state
   (`draft.login.<userid>`, `draft.team.<teamid>`, `draft.order.*`) that
   churns during the draft. The editor does not group, filter, or hide
   these differently — plain key/value list, add/edit/delete on every row
   alike. (Matches the roadmap note that a generic editor is fine.) No
   caching or eager-load assumption that treats the table as small/static.
5. **Merge bar:** PHPUnit tests (unit + any template/controller tests
   following existing patterns) plus a manual fake-session E2E checklist
   (see `validation.md`). No new WebTestCase functional-test infrastructure
   beyond what the codebase already uses.

## Part A — Quicklinks drag-and-drop ordering

### Current state

- `templates/admin/quicklinks/index.html.twig`: plain `Order` column,
  numeric, not editable inline.
- `templates/admin/quicklinks/edit.html.twig`: a `sortOrder` number input
  (`AdminQuickLinkController::applyForm()` reads it via
  `$request->request->get('sortOrder', 0)`).
- `QuickLinkRepository::findAllOrdered()` / `findVisible()` both
  `ORDER BY sortOrder ASC, id ASC`.
- No drag-and-drop library anywhere in the codebase today
  (`jquery.tablesorter` is column-sort, unrelated).

### Scope

1. **New route** `admin_quicklinks_reorder`
   (`POST /admin/quicklinks/reorder`) on `AdminQuickLinkController`:
   commissioner-gated, CSRF-protected (same `admin_quicklink` token id as
   the other mutating actions), accepts an ordered list of quicklink ids
   (e.g. `ids[]=3&ids[]=1&ids[]=2` or a JSON body — implementer's choice,
   whichever pairs more naturally with the SortableJS `onEnd` handler),
   and rewrites `sortOrder` sequentially (1, 2, 3, ...) in that order, in
   one flush. Ids not present in the payload (shouldn't happen, but don't
   trust the client) are left alone rather than erroring.
2. **Index page**: rows become draggable via SortableJS
   (`new Sortable(tbody, {...})` on the `<tbody>`, handle = the whole row or
   a small drag-handle cell — implementer's choice). On drop, `onEnd` fires
   a `fetch` POST to the reorder route with the new id order and the CSRF
   token (read from a `data-` attribute or hidden input already on the
   page, matching how other admin pages embed `csrf_token('admin_quicklink')`
   today); on success, re-render or update the `Order` column values without
   a full page reload if straightforward, otherwise a simple redirect/reload
   is acceptable — no client-side framework, just enough vanilla JS to wire
   SortableJS to the fetch call.
3. **Edit form**: remove the `sortOrder` input and its `form-group` column
   from `edit.html.twig`; `AdminQuickLinkController::applyForm()` stops
   reading/setting `sortOrder` from the request. `new()` keeps its
   end-of-list default (`end($existing)->getSortOrder() + 1`).
4. **SortableJS CDN include**: add the `<script>` tag either globally in
   `templates/admin/base.html.twig` (simplest, consistent with how
   jQuery/Bootstrap are loaded once for the whole admin section) or scoped
   to `admin/quicklinks/index.html.twig` via a `{% block javascripts %}`
   if the base template doesn't already support per-page script blocks —
   check `templates/base.html.twig` for an existing `javascripts` block
   before deciding.

## Part B — Admin config editor

### Current state

- `App\Entity\Config` (table `config`): `key` (varchar, PK) / `value`
  (varchar), no relations. `ConfigRepository` is `ServiceEntityRepository`
  scaffolding with only commented-out example methods.
- Nothing in the Symfony app reads or writes this table today. 54 rows in
  the dev DB currently, e.g. `draft.hangout.url`, `protections.deadline`,
  `draft.clock.*`, plus ~30 `draft.login.<userid>` / `draft.team.<teamid>` /
  `draft.order.{team,word}.<teamid>` rows written by the draft flow.
- Legacy admin tooling for this table (if any existed) is out of scope to
  investigate — this is new Symfony-only functionality per the mission's
  "admin tools must exist" principle.

### Scope

1. **`AdminConfigController`** under `/admin/config`, extending
   `AbstractAdminController` (commissioner gate via `requireCommissioner`,
   same pattern as `AdminQuickLinkController`):
   - **Index** (`admin_config`): table of every row, key + value, sorted by
     key. No pagination needed at 54 rows; don't add a caching layer.
   - **New** (`admin_config_new`, GET+POST): form for a new key/value pair.
     Reject (flash error, no persist) if the key already exists (PK
     collision) or if key/value are blank after trimming.
   - **Edit** (`admin_config_edit/{key}`, GET+POST): change the `value` for
     an existing key. The `key` (PK) itself is not editable in place —
     changing a key is delete-and-recreate, consistent with it being an
     identity, not a mutable field. Route parameter needs a requirement
     that safely matches keys containing dots (e.g. `draft.login.14`) —
     confirm during implementation whether Symfony's default `{key}`
     matching (which stops at `/`) is sufficient or a `requirements` regex
     is needed.
   - **Delete** (`admin_config_delete/{key}`, POST): hard delete, CSRF.
   - All mutating routes CSRF-protected via `assertCsrfToken`, using a
     dedicated token id (e.g. `admin_config`), same convention as
     `admin_quicklink`.
2. **Templates** under `templates/admin/config/` (`index.html.twig`,
   `edit.html.twig`), following the quicklinks templates' structure and
   `btn-wmffl` / `text-center` button conventions.
3. **Admin nav**: add a "Config" entry to `templates/admin/base.html.twig`'s
   sidebar list, alongside the existing entries.
4. **No special handling for `draft.*` runtime rows** (decision #4) — they
   show and edit exactly like any other row.

## Non-goals

- No redesign of `/admin/quicklinks` beyond the ordering mechanism (window,
  active-flag, and other fields are unchanged from Phase 10).
- No validation/typing of config values (e.g. no "this key expects a
  boolean/timestamp" awareness) — plain strings in, plain strings out.
- No migration of legacy per-key business logic that happens to read this
  table today (if any exists in `/football/`) onto a new abstraction — this
  phase only builds the admin CRUD surface, per the roadmap's scope.
- No bulk import/export for config rows.

## Context / gotchas carried forward

- Fake-session E2E recipe needs `fullname` in the session (prior-phase
  gotcha).
- CSRF pattern: `assertCsrfToken($request, '<id>')` checked after the
  commissioner gate, on every mutating action — see
  `AbstractAdminController::assertCsrfToken()`.
- `btn-wmffl` + `text-center` wrapper is the established convention for all
  admin buttons (see memory `feedback_button_style`).
