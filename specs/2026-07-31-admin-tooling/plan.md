# Phase 12 — Admin tooling: Plan

Branch `phase12-admin-tooling`, one PR. Each task group should land as its
own commit (or a small number of commits). See `requirements.md` for scope
and decisions, `validation.md` for the merge bar.

## 1. Quicklinks: reorder route + service logic

1. Confirm whether `templates/base.html.twig` already has a per-page
   `javascripts`/`stylesheets`-style block for admin templates to hook into
   (`{% block javascripts %}` or similar); if not, decide where the
   SortableJS `<script>` tag lives (global in `admin/base.html.twig` vs.
   scoped to the quicklinks index).
2. `AdminQuickLinkController::reorder()` — `POST /admin/quicklinks/reorder`,
   commissioner-gated, CSRF (`admin_quicklink` token), reads the ordered id
   list from the request, rewrites `sortOrder` sequentially in one flush,
   ignores unknown ids rather than erroring.
3. Unit/controller test for `reorder()`: full reorder, partial payload
   (subset of ids — decide and test the actual behavior chosen), unknown id
   in payload, CSRF rejection, non-commissioner rejection.

## 2. Quicklinks: drag-and-drop UI

1. Add SortableJS via CDN (`<script src="https://cdn.jsdelivr.net/npm/sortablejs@1/Sortable.min.js">`).
2. `admin/quicklinks/index.html.twig`: wire `Sortable` on the `<tbody>`,
   `onEnd` handler posts the new order via `fetch` (CSRF token from a
   `data-` attribute or hidden field already on the page) to
   `admin_quicklinks_reorder`.
3. Remove the `sortOrder` `form-group` from `edit.html.twig`; drop the
   corresponding read in `AdminQuickLinkController::applyForm()`.
4. Manual check (see `validation.md`) that drag order persists across a
   page reload and that `findVisible()`'s homepage ordering follows suit.

## 3. Config editor: controller + routes

1. `AdminConfigController` (`/admin/config`), extending
   `AbstractAdminController`: `index`, `new` (GET+POST), `edit/{key}`
   (GET+POST), `delete/{key}` (POST). Confirm the `{key}` route-parameter
   matching handles dotted keys (`draft.login.14`) correctly — add a
   `requirements` regex if the default isn't sufficient.
2. Validation in `new`/`edit`: trim, reject blank key/value, reject
   duplicate key on `new`.
3. CSRF (`admin_config` token id) on all three mutating routes.

## 4. Config editor: templates + nav

1. `templates/admin/config/index.html.twig` — full table, key + value,
   sorted by key, edit/delete actions per row, "Add" button
   (`btn-wmffl` + `text-center`).
2. `templates/admin/config/edit.html.twig` (shared by new/edit, matching
   the quicklinks edit-form pattern) — key input (disabled/hidden on edit,
   editable on new), value input.
3. Add "Config" to `templates/admin/base.html.twig`'s sidebar.

## 5. Tests + final pass

1. Controller/unit tests for `AdminConfigController` (CRUD round-trip,
   duplicate-key rejection, CSRF, commissioner gate) following existing
   admin-controller test patterns in `tests/`.
2. Run the full `validation.md` checklist; fix anything it turns up.
3. Update `specs/roadmap.md`: move Phase 12 into `Done` with a summary
   entry once validation passes.
