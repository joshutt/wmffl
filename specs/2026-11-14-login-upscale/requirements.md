# Requirements — Login & Security Upscale (Phase 18 item 1)

Branch: `login-upscale`

## Scope

Replace the legacy login with Symfony Security as the single
authenticator, while every remaining legacy page under `/football/`
keeps working. Modernise password storage with a silent, per-user
upgrade at next login. Add self-service account management and extend
the admin user tool.

1. Symfony Security owns login, logout, password hashing, "stay logged
   in", and access control.
2. Passwords move from unsalted MD5 to Symfony's `auto` hasher. Each
   user is upgraded the first time they log in after deploy; they see
   nothing different.
3. Legacy pages keep reading the same identity globals/session keys
   they read today.
4. Clicking your name in the navbar opens an account page where you can
   change your password and manage your email addresses.
5. A user can have more than one email address.
6. Forgot-password becomes an emailed, expiring, single-use link.
7. The admin user tool (`/admin/users`) edits the multi-email list and
   can send a set-password link.

Out of scope:
- Changing username or display name from the account page (admin only,
  as today).
- Two-factor auth, social login, self-registration.
- Rewriting the ~70 files that call `AuthenticationService` to use
  `#[IsGranted]`/`getUser()` — the facade stays; that cleanup is later.
- Migrating the remaining legacy pages (`forum/`, `services/picks.php`,
  etc.) — they only need to keep working.
- `football/rules/` (never touched).

## Context (verified 2026-11-14)

- `symfony/security-bundle` is not installed; there is no
  `security.yaml`. `symfony/security-csrf` and `symfony/mailer` are.
- Login is `football/login/login.php`, called by AJAX from the
  `#loginModal` in both `symfony-app/templates/base.html.twig` and
  `football/base/menu.php`. Contract: body `Ok` on success,
  `err - <message>` on failure.
- `user` table: 36 rows, 12 active. Every `Password` is a 32-char
  unsalted MD5. Column is `varchar(50)` — too short for bcrypt (60) or
  argon2 (~97).
- `login.php` runs `mysqli_real_escape_string` on the password *before*
  hashing it, so the effective legacy check is
  `md5(escaped(input)) == stored`. `forgotpassword.php` stores
  `md5(raw)`. The legacy verifier must accept both to guarantee nobody
  who can log in today is locked out.
- Identity is six raw `$_SESSION` keys: `isin`, `teamnum`, `usernum`,
  `user`, `fullname`, `commish`. Symfony reads them only through
  `App\Service\AuthenticationService`; legacy reads them via
  `utils/start.php` (session → globals) and the globals `LegacyBridge`
  sets.
- **Legacy requests never pass through a Symfony firewall.**
  `public/index.php` hands off to `LegacyBridge` when the router has no
  match, and the router throws before the firewall listener runs. Any
  design that relies on the security token must solve this explicitly
  (plan step 4).
- `user.Email` readers: `TradeOfferRepository::getActiveUserEmails()`
  (trade notifications), `AdminUserController`, and the legacy
  `forgotpassword.php`/`newpassword.php` being deleted. Nothing else.
- The dev DB has the same address on every user row, so email cannot be
  globally unique.
- Existing weaknesses this work closes (security backlog C1–C4, H2):
  SQL injection and request-supplied target user in `newpassword.php`;
  plaintext, time-seeded generated password emailed by
  `forgotpassword.php` with no proof of ownership; dead SQL-injectable
  cookie login in `loginblock.php`; `listsession.php` dumping the
  session; request variables able to overwrite `$isin` via
  `setup.php`'s extraction.

## Decisions (confirmed with Josh 2026-11-14)

1. **Sessions at deploy**: users logged in at the moment of deploy are
   logged out once and log in again. No legacy-session adopter.
2. **Inactive accounts**: the 24 `active = 'N'` rows have their
   password blanked in the migration. A blank password can never
   authenticate. Reactivating a user means sending them a set-password
   link from the admin tool.
3. **Active users who don't return**: see decision 12.
4. **Email change**: requires the current password; a notice goes to
   the old address(es). No confirmation link on the new address.
5. **Stay logged in**: opt-in checkbox on the login form, 30 days,
   sliding — the 30-day clock restarts whenever the user visits.
6. **Password rules**: new passwords (change, reset, set) need at least
   12 characters. Passwords being migrated are never length-checked;
   whatever the user has today keeps working.
7. **Multiple emails**: stored in a new `user_email` table; `user.Email`
   is dropped.
8. **Primary address**: exactly one of a user's addresses is flagged
   primary. Every address — primary or not — receives league mail
   (trade notifications) and security notices. The primary is the one
   shown in lists (e.g. the admin user index).
9. **Limits**: an active user has at least one and at most five
   addresses. An inactive user may have none.
10. **Uniqueness**: an address may appear only once per user. The same
    address on different users is allowed and not checked.
11. **Password reset**: requested by username; the link goes to all of
    that user's addresses. The response is identical whether or not
    the username exists.
12. **MD5 holdouts**: active users keep their MD5 hash until they next
    log in; no forced cutoff (all are expected to log in within about
    a week).

## Assumption

- The `commish` flag stays out of the admin user tool, as in Phase 14.

## Acceptance

- A user with an MD5 hash logs in with their existing password; the row
  then holds a modern hash; the second login also succeeds.
- Login/logout work from the modal on both a Symfony page and a legacy
  page, and the logged-in state is the same on both.
- A user who ticked "stay logged in" and returns after the PHP session
  has expired is still logged in — including when their first request
  is to a legacy page.
- Inactive users and blank passwords cannot authenticate.
- Account page: password change and email add/remove work and send
  notices; reset link works once and expires.
- Commissioner impersonation (`/admin/become`) still works.
- All existing tests stay green.
