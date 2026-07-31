# Phase 12 — Admin tooling: Validation

The phase is done and mergeable when everything below passes. Merge bar
(decision #5 in `requirements.md`): unit/controller tests (mocked-service
`TestCase` style, matching e.g. `tests/Controller/AdminDraftDatesControllerTest.php`
— no new WebTestCase functional-test infrastructure) plus manual
fake-session E2E.

## Automated

Run from `symfony-app/`: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`

All pre-existing tests still green, plus new coverage for:

- **`AdminQuickLinkController::reorder()`**
  - full reordered id list → `sortOrder` rewritten sequentially in the new
    order
  - unknown id in the payload → ignored, other rows still reordered
    correctly, no error
  - non-commissioner → redirect, no changes persisted
  - missing/invalid CSRF token → 403, no changes persisted
- **`AdminQuickLinkController::applyForm()`** no longer reads `sortOrder`
  from the request (existing sortOrder-related tests, if any, updated to
  match; new-link end-of-list default still covered)
- **`AdminConfigController`**
  - `index` — non-commissioner redirect; commissioner sees all rows
  - `new` — valid submission persists; blank key/value rejected with flash,
    nothing persisted; duplicate key rejected with flash, nothing persisted
  - `edit` — valid submission updates `value`; key itself not mutable
    through this action; 404 on unknown key
  - `delete` — removes the row; CSRF enforced; 404 on unknown key
  - a dotted key (e.g. `draft.login.14`) round-trips correctly through
    edit/delete routes (confirms the route-parameter matching decision from
    `plan.md` task 3.1 works)

## Manual E2E (fake-session, `php -S` from `symfony-app/public`)

Use the established fake-session recipe (session must include `fullname`);
one commissioner session (both tools are commissioner-only) and one
non-commissioner/logged-out check per tool.

### Part A — quicklinks drag-and-drop

1. `/admin/quicklinks` as commissioner with 3+ links: rows are draggable
   (visible drag affordance/cursor change).
2. Drag the last row to the top → order updates on the page; reload the
   page → new order persists (confirms the `sortOrder` rewrite actually
   flushed, not just a client-side reorder).
3. Homepage `/` "Other Links" widget reflects the new order
   (`findVisible()` follows `sortOrder`).
4. Edit form (`/admin/quicklinks/{id}/edit`) no longer shows a Sort Order
   field; saving an edit doesn't reset the row's position.
5. Add a new link → lands at the end of the current drag-established order,
   not wherever `sortOrder` arithmetic might otherwise place it.
6. Non-commissioner/logged-out hitting the reorder POST route directly →
   redirected like the other admin actions, no change persisted.
7. Tamper with the CSRF token (or omit it) on the reorder POST → 403, order
   unchanged.

### Part B — admin config editor

1. `/admin/config` as commissioner: all current rows listed (54 in dev DB
   as of this spec — count may drift), including both plain settings
   (`protections.deadline`) and draft-runtime rows (`draft.login.<id>`)
   shown identically, no grouping/filtering.
2. Add a new key/value pair → appears in the list.
3. Attempt to add a key that already exists → rejected with a flash, list
   unchanged.
4. Edit an existing value (e.g. bump `protections.deadline`) → change
   reflected in the list; confirm elsewhere in the app that reads this key
   (if anything currently does) sees the new value — grep first to check
   whether anything does.
5. Delete a row → gone from the list.
6. Edit/delete a dotted key like `draft.login.14` specifically (not just a
   simple key) — round-trips without truncating at the dot or erroring on
   route matching.
7. Non-commissioner/logged-out on `/admin/config` and its mutating routes →
   redirected, no changes.
8. Admin sidebar (`templates/admin/base.html.twig`) shows a "Config" link
   that highlights active on all `/admin/config*` routes.

## Data / deploy

- No schema migration needed for either part — `quicklinks.sort_order`
  already exists; `config` table already exists and is unchanged.
- Deploy note: no special sequencing; both tools are additive UI over
  existing tables. Verify prod's `config` row count/contents look sane in
  `/admin/config` post-deploy (first real look at this data through the
  Symfony app).
