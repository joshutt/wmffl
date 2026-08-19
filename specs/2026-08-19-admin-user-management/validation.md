# Validation — Admin User Management (Phase 14)

## Automated

- Full suite green: `cd symfony-app && vendor/bin/phpunit tests/
  --coverage-clover coverage.xml` (per `feedback_test_coverage`
  convention — never `--coverage-text`).
- New tests from `plan.md` step 6 pass, including the commissioner-gate,
  CSRF-rejection, and uniqueness-validation-error cases.

## Migration

- `cd symfony-app && php bin/console doctrine:migrations:migrate` applies
  cleanly against a dev DB copy; `doctrine:schema:update --dump-sql`
  afterward shows no remaining diff for the `user` table.
- Confirm the migration's `down()` actually reverses it (re-adds
  `blogaddress`) — run migrate, then migrate down one step, then back up,
  to prove it's not a one-way trip.
- `scripts/database/schema.sql` regenerated/updated to match — no stale
  `blogaddress` column definition left behind for anyone diffing schema
  by hand.
- **Deploy step, called out separately for Josh**: this migration must
  run against staging and prod at deploy time, same as every other
  schema-bearing phase — it is not optional/deferred cleanup.

## Manual E2E (fake commissioner session, `php -S -t public
public/index.php` — must be started with the router script argument per
the `gotcha_php_s_dotted_paths` note)

1. **List**: `/admin/users` loads for a commissioner session, shows every
   existing user, no `commish` column/value anywhere on the page (view
   source check, not just visual).
2. **Non-commissioner gate**: hitting `/admin/users`,
   `/admin/users/new`, `/admin/users/{id}/edit` without a commissioner
   session redirects to `/` (no 500, no leaking the form).
3. **Create, no team**: add a user with username/name/email only, no
   team selected. Confirm in DB: new `user` row exists, `TeamID` is
   NULL, `Password` is the schema default (empty string, not NULL and
   not some placeholder hash), no new `owners` row was written.
4. **Create, with team**: add a user and assign a team in the same
   form. Confirm: `user.TeamID` set to that team, and a matching
   `owners` row exists for the current season
   (`SeasonWeekService::getCurrentSeason()`) with `primary=1`.
5. **Reassign**: edit an existing user (with an existing current-season
   `owners` row) to a different team. Confirm `user.TeamID` updated,
   the existing current-season `owners` row's `teamid` updated in
   place (row count for that user/season unchanged — no duplicate row
   created), and no other season's `owners` rows touched.
6. **Unassign**: edit a user to clear their team (blank/"Unassigned").
   Confirm `user.TeamID` becomes NULL and any existing `owners` rows
   are left alone (not deleted) — matches the "historical record"
   decision in `requirements.md`.
7. **Username uniqueness**: try to create a user with a username that
   already exists. Confirm a friendly flash/form error, not a raw DB
   constraint exception, and the rest of the typed form values are
   still in the re-rendered form (Phase 12 null-entity-style
   regression check).
8. **primaryowner checkbox**: toggle it on an existing user, confirm it
   persists.
9. **No password/reset surface**: confirm neither the new nor edit form
   has any password field, and there is no reset/generate-password
   button or route added by this tool.
10. **Self-service handoff still works**: for a user created via this
    tool with no password, run the existing
    `/login/forgotpassword.php` flow against their username/email and
    confirm it successfully sets a working password (proves the "blank
    password, self-service first-set" decision actually works
    end-to-end, not just in isolation).
11. **Nav entry**: "Users" link visible in the admin sidebar, active
    state highlights on all `/admin/users*` pages.

## Merge gate

- All automated tests green, all manual steps above confirmed by Josh.
- Per `feedback_spec_commit_approval`: do not self-commit changes to
  these three spec docs — wait for explicit approval each time they're
  revised.
