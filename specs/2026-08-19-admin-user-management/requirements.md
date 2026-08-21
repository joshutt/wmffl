# Requirements — Admin User Management (Phase 14)

## Scope

Legacy has no dedicated user-management UI; the `user` table and the
`owners` team-assignment table are edited directly in the database today.
Add an admin tool, `AdminUserController` under `/admin/users`, covering:

1. List/add/edit users on the `user` table (note: the roadmap calls this
   the "users" table; the actual table — and existing `App\Entity\User`
   mapping — is singular `user`).
2. Assign a user to a team via a team picker, writing both `user.TeamID`
   and an `owners` row for the current season (see Decisions).

Out of scope this phase:
- Deleting users (no delete route — legacy has no concept of removing a
  user, only deactivating via `active`).
- Any password field, generation, or reset action (see Decisions).
- Any change to `commish` (site-admin flag) — not surfaced by this tool
  at all, not even read-only.
- Editing/backfilling historical `owners` rows for past seasons — this
  tool only ever writes the *current* season's row.
- Auth/login system changes — that's Phase 18.

## Context

- `user` table / `App\Entity\User` (`symfony-app/src/Entity/User.php`)
  already maps every relevant column: `UserID`, `TeamID` (direct FK to
  `team`), `Username`, `Password`, `Name`, `Email`, `primaryowner`,
  `lastlog`, `blogaddress`, `active` (`ActiveEnum` Y/N), `commish`.
  `blogaddress` is confirmed dead — grepped, zero references anywhere
  outside the entity itself and `schema.sql`'s column definition — and
  is being removed from the entity as part of this phase (see Decisions).
- `owners` table / `App\Entity\Owner` (`symfony-app/src/Entity/Owner.php`)
  is season-keyed: composite PK `(teamid, userid, season)` plus a
  `primary` smallint flag. This is the historical ownership record;
  `user.TeamID` is redundant with the *current* season's `owners` row —
  both exist in the live schema and both are populated by legacy code
  paths, so this tool keeps them in sync rather than picking one as the
  sole source of truth.
- No `UserRepository` or `OwnerRepository` exists yet — both need to be
  created.
- Legacy passwords are unsalted MD5 (`football/login/login.php`:
  `password=md5(?)`), set via the existing self-service
  `football/login/forgotpassword.php` (random password, emailed) and
  `football/login/newpassword.php` (change while logged in). Neither is
  touched by this phase.
- Admin controller pattern to follow: `AdminConfigController` /
  `AdminQuickLinkController` (`src/Controller/Admin/`,
  `templates/admin/{config,quicklinks}/`) — `AbstractAdminController`'s
  `requireCommissioner()` gate + `assertCsrfToken()`, flash messages,
  index/new/edit views, no delete for user (see above).
- Current season: `SeasonWeekService::getCurrentSeason()`
  (`symfony-app/src/Service/SeasonWeekService.php:76`).
- Team picker source: `TeamRepository::getActiveTeams()`
  (`symfony-app/src/Repository/TeamRepository.php:602`).

## Decisions (confirmed with Josh, 2026-08-19)

1. **Team assignment writes both places.** Assigning a user to a team
   updates `user.TeamID` to the new team AND upserts the `owners` row for
   `SeasonWeekService::getCurrentSeason()` (`primary = 1`) — insert if no
   row exists for that user/season, update the team on it if one does.
   This tool does not touch `owners` rows for any other season.
2. **No password field anywhere in this tool.** New users are created
   with no usable password (the column stays at its schema default,
   empty string) — Josh (or the user) sets it for the first time via the
   existing `/login/forgotpassword.php` self-service flow, keyed off the
   username/email this tool creates. No admin-triggered reset action is
   built this phase.
3. **`primaryowner` is editable, `commish` is not exposed.** The
   add/edit form includes a `primaryowner` checkbox alongside the other
   identity fields. `commish` does not appear on the form, the list, or
   any detail view produced by this tool — it stays a manual DB edit for
   now (a deliberate scoping decision, since it's the site's highest
   privilege level and this phase is explicitly not touching auth).
4. **`blogaddress` removed from the model, and dropped from the schema.**
   Not just left off the form — the property, getter, and setter come
   out of `App\Entity\User` (it's dead: unused anywhere in
   `symfony-app/`, `football/`, or `scripts/`), and a migration drops
   the `blogaddress` column from the `user` table itself. This lands
   with the rest of Phase 14's changes and runs on staging/prod at the
   same deploy — see `plan.md` step 1 and the migration note in
   `validation.md`.

## Field list for the add/edit form

- `Username` (required, unique — matches the existing `Username_2`
  unique key; surface a friendly error on collision rather than letting
  the DB constraint throw)
- `Name`
- `Email` (required)
- `active` (Y/N — reuse `ActiveEnum`)
- `primaryowner` (checkbox)
- Team assignment (dropdown from `getActiveTeams()`, optional — a user
  can exist with no team)

Not on the form: `Password` (per Decision 2), `commish` (per Decision 3),
`blogaddress` (removed from the entity entirely, per Decision 4),
`lastlog` (system-managed, display-only if shown at all).

## Non-goals / risks carried forward

- The `user.TeamID` / `owners` redundancy is pre-existing in the schema;
  this phase does not attempt to normalize it away, only keeps both
  updated consistently from this one tool.
- MD5-unsalted passwords are a known weakness inherited from legacy —
  out of scope to fix here per the roadmap's explicit instruction not to
  invent a new auth scheme this phase doesn't own.
