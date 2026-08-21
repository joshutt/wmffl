# Plan — Admin User Management (Phase 14)

Branch: `phase14-admin-user-management`

## 1. Entity cleanup: drop `blogaddress`

- Remove the `blogAddress` property, `getBlogAddress()`/
  `setBlogAddress()`, and the `#[ORM\Column(name: 'blogaddress', ...)]`
  attribute from `symfony-app/src/Entity/User.php`. Confirmed dead
  (no references outside the entity and `schema.sql`), so no other code
  changes ride along with this.
- New Doctrine migration (`symfony-app/migrations/Version<timestamp>.php`,
  matching the existing naming convention) with `ALTER TABLE user DROP
  COLUMN blogaddress` in `up()` and the reverse `ADD COLUMN blogaddress
  varchar(75) DEFAULT NULL` in `down()`. Regenerate
  `scripts/database/schema.sql` (or hand-edit the `user` table's
  definition) so it matches post-migration.
- Deploy note: this migration must run on staging and prod as part of
  landing this phase, same as prior phases' migrations (e.g. Phase 13's
  `Version20260714000000` pattern) — call this out explicitly in the PR,
  it's a real schema change riding along with what's otherwise a UI
  feature.

## 2. Repositories

- `symfony-app/src/Repository/UserRepository.php`: `findAllOrdered()`
  (by `Username`, for the index list), `findOneByUsername(string): ?User`
  (for the uniqueness check on create).
- `symfony-app/src/Repository/OwnerRepository.php`:
  `findForUserAndSeason(int $userId, int $season): ?Owner` (to decide
  insert-vs-update when syncing team assignment).
- Register both the standard Doctrine way (constructor
  `ManagerRegistry` + `parent::__construct`), matching
  `ConfigRepository`'s shape.

## 3. Service: keep `user.TeamID` and `owners` in sync

- Small method, either on `UserRepository`/`OwnerRepository` or a thin
  `UserTeamAssignmentService` (pick whichever keeps the controller
  thinnest — a service is preferable if the upsert logic needs its own
  transaction) that, given a `User` and a `?Team`:
  - sets `user.TeamID` to the new team (or null)
  - if a team was set: upserts the current-season `owners` row
    (`SeasonWeekService::getCurrentSeason()`) for that user, `primary=1`
  - if the team was cleared (unassigned): leave existing `owners` rows
    alone (they're historical record; only ever add/update the current
    season's row here, never delete)
  - wraps the write in one flush so both changes commit together

## 4. `AdminUserController`

`symfony-app/src/Controller/Admin/AdminUserController.php`, route prefix
`/admin/users`, extends `AbstractAdminController`, `requireCommissioner`
gate + `assertCsrfToken` on every mutating action — mirror
`AdminConfigController`'s structure:

- `index` (`GET /admin/users`, `admin_users`): list all users — Username,
  Name, Email, current Team (if any), active, primaryowner. No `commish`
  column.
- `new` (`GET/POST /admin/users/new`, `admin_users_new`): form per the
  field list in `requirements.md`. On POST: validate required fields,
  check username uniqueness via `findOneByUsername`, create the `User`
  (password left at schema default), run the team-assignment sync if a
  team was chosen, flash + redirect to index.
- `edit` (`GET/POST /admin/users/{id}/edit`, `admin_users_edit`): same
  form pre-filled; on POST, same validation (excluding self when checking
  username uniqueness), update fields, run the team-assignment sync
  (covers both "assign" and "reassign" — same code path), flash +
  redirect to index.
- No `delete` route (see `requirements.md` — out of scope).

## 5. Templates

`symfony-app/templates/admin/users/`:
- `index.html.twig` — table, `btn-wmffl` "Add User" button, edit link
  per row (follow `admin/quicklinks/index.html.twig` layout/styling).
- `edit.html.twig` — shared by new/edit like `admin/config/edit.html.twig`
  (pass plain scalars + `isEdit`, not the entity, to avoid the null-entity
  Twig bug fixed in Phase 12); team `<select>` populated from
  `getActiveTeams()` with a blank/"Unassigned" option; `active` as a
  Y/N select or checkbox (match existing enum-editing convention if one
  exists elsewhere in admin, e.g. season status fields); `primaryowner`
  checkbox.

## 6. Nav entry

Add a "Users" link to `templates/admin/base.html.twig` alongside Config/
Quicklinks, active-state class following the existing
`app.request.attributes.get('_route') starts with 'admin_users'` pattern.

## 7. Tests

- `UserRepositoryTest` / `OwnerRepositoryTest` (or the sync service's own
  test) covering: create with no team leaves `owners` untouched; create
  with a team writes both `user.TeamID` and a new current-season `owners`
  row; reassigning an existing user's team updates `user.TeamID` and
  updates (not duplicates) the current-season `owners` row for that user.
- Controller/template tests following the `AdminConfigController`
  precedent: commissioner gate redirects non-commissioners, CSRF
  rejection on POST without a valid token, username-uniqueness validation
  error re-renders the form without losing typed input (the Phase 12
  null-entity lesson), successful create/edit round-trip.
- Confirm no test or template ever reads/writes `commish` through this
  new code path.
- Confirm the app still boots/tests still pass after `blogaddress` comes
  off `User` (i.e. nothing else was silently depending on it).

## 8. Manual verification

See `validation.md`.
