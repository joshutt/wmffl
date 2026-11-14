# Validation — Login & Security Upscale (Phase 18 item 1)

## Automated

- `cd symfony-app && vendor/bin/phpunit tests/ --coverage-clover
  coverage.xml` green, including every new test in `plan.md` step 12.
- `vendor/bin/phpunit test/` from project root green.

## Migration

- Before migrating dev, save `SELECT UserID, Password, Email, active
  FROM user` so the checks below have a baseline.
- `php bin/console doctrine:migrations:migrate` applies cleanly.
- `SHOW COLUMNS FROM user`: `Password` is `varchar(255)`, `Email` is
  gone.
- `user_email` has one row per user that had a non-empty address, each
  with `is_primary = 1`. The dev DB's shared address across users
  migrates without a constraint error.
- Every `active = 'N'` row has `Password = ''`; every `active = 'Y'`
  row still has its original 32-char hash.
- `down()` then `up()` again works (inactive passwords stay blank — by
  design, noted in the migration).
- `scripts/database/schema.sql` matches.

## Manual E2E

Run with `php -S host:port -t public public/index.php` (router script
argument required). Use a real dev user whose password is known, not a
fake session, for the login checks.

1. **Silent upgrade**: log in from the modal with an MD5-hash user.
   Login succeeds, page reloads logged in. DB row now holds a modern
   hash (`$2y$`/`$argon2`). Log out, log in again with the same
   password — succeeds.
2. **Short existing password**: a user whose current password is under
   12 characters still logs in and is upgraded.
3. **Escaped-password variant**: set a test user's hash to
   `md5("it\'s-a-test")` (the escaped form) and log in with
   `it's-a-test` — succeeds and upgrades.
4. **Failures**: wrong password, unknown user, inactive user, and
   blank-password user all show the same "Invalid Username/Password" in
   the modal. Six rapid failures trigger throttling.
5. **Legacy page login**: open a legacy page (e.g. `/forum/blog`), log
   in from its modal — navbar shows the name; navigate to a Symfony
   page — still logged in. Repeat in the other direction.
6. **Logout** from a legacy page and from a Symfony page clears the
   state on both.
7. **Stay logged in**: log in with the box ticked; delete the PHP
   session cookie (keep the remember-me cookie); request a **legacy**
   URL first — logged in. Repeat requesting a Symfony URL first. Check
   the remember-me cookie's expiry moves forward on a later visit.
   Without the box ticked, deleting the session cookie logs you out.
8. **Password change invalidates remember-me**: with a remembered
   session in a second browser, change the password in the first; the
   second browser is logged out once its session lapses.
9. **Identity spoofing closed**: logged out, request a legacy page with
   `?isin=1&teamnum=2&commish=1` — still treated as logged out.
10. **Admin gate**: `/admin` as anonymous and as a non-commish user
    redirects to `/`; as commish it loads.
11. **Impersonation**: `/admin/become` a team — acting as that team on
    Symfony and legacy pages, `/admin` blocked, logout returns to
    anonymous.
12. **Forgot password**: request for a real username → mail (check the
    dev mail catcher) to every address with a link; link sets a new
    password (11 chars rejected, 12 accepted); link fails on second
    use; an expired token fails; unknown username gives the identical
    response and sends nothing.
13. **Account page**: name in the navbar goes to `/account` from both
    a Symfony and a legacy page. Change password (wrong current
    rejected, mismatch rejected, <12 rejected, success sends notice).
    Add a second email and make it primary; remove the first (notice
    goes to the old address). Removing the primary promotes another;
    there is always exactly one primary. Adding the same address twice
    is refused; an address already used by another user is accepted;
    removing the last address is refused; a sixth is refused.
14. **Trade mail**: make a trade offer involving a user with two
    addresses — both receive it.
15. **Admin user tool**: edit a user's address list and primary; an
    inactive user can be saved with no address, an active one cannot,
    and activating a user with no address is refused; "send reset"
    delivers a link; creating a user sends a set-password link and the
    user cannot log in until they use it; `commish` appears nowhere.
16. **Retired URLs**: `/login/newpassword`, `/login/newpassword.php`
    301 to `/account`; `/login/listsession`, `/login/loginblock` give
    the branded 404.
17. **Phase 13 regression**: an unknown URL gives the branded 404; a
    Symfony route's own 404 (e.g. a missing player id) still renders
    Symfony's page, not LegacyBridge's.

## Deploy checklist (Josh)

- Back up the `user` table.
- Prod `.env.local`: `MAILER_DSN` set, `APP_SECRET` set and stable.
- Run the migration, then deploy code.
- Expect everyone to be logged out once.
- Afterwards: `SELECT COUNT(*) FROM user WHERE active='Y' AND
  LENGTH(Password)=32` shows how many active users are still on MD5;
  it should fall as owners log in.
