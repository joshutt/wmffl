# Plan — Login & Security Upscale (Phase 18 item 1)

Branch: `login-upscale`

Design in one paragraph: Symfony Security is the only thing that
authenticates. `AuthenticationService` stays as the facade every
controller, template and test already uses. The six legacy `$_SESSION`
keys become a projection that Symfony writes, so legacy pages need no
changes. Steps are ordered so each one leaves the site working.

## 1. Schema migration

One Doctrine migration (`Version20261114000000`):

- `ALTER TABLE user MODIFY Password varchar(255) NOT NULL DEFAULT ''`.
- `UPDATE user SET Password = '' WHERE active = 'N'` (decision 2).
  `down()` cannot restore these — say so in the migration description.
- Create `user_email` (`id` PK, `user_id` FK → `user.UserID` ON DELETE
  CASCADE, `email` varchar(255), `is_primary` tinyint(1) NOT NULL
  DEFAULT 0, `created_at`; UNIQUE `(user_id, email)`; no uniqueness
  across users). Backfill one row per user from `user.Email` with
  `is_primary = 1` (skip empty). Then drop `user.Email`. `down()`
  re-adds the column and refills it from each user's primary address.
- Create `password_reset_token` (`id`, `user_id` FK, `token_hash`
  char(64) UNIQUE, `expires_at`, `used_at` nullable, `created_at`).
- Create `rememberme_token` only if step 5's spike picks the
  persistent-token variant.
- Update `scripts/database/schema.sql`.

## 2. Entities and repositories

- `App\Entity\UserEmail` (with `isPrimary`); `User::$emails` OneToMany
  (cascade persist, orphanRemoval); remove `User::$email`, add
  `getEmailAddresses(): string[]` and `getPrimaryEmail(): ?string`.
- `App\Service\UserEmailService` — the one place the rules live, used
  by both the account page and the admin tool: add (valid format, not
  already on this user, max 5), remove (an active user's last address
  cannot be removed; removing the primary promotes the oldest
  remaining one), set primary (clears the flag on the others in the
  same flush), and "exactly one primary whenever the user has any
  address". Inactive users may have zero addresses; activating a user
  with none is refused.
- `User` implements `UserInterface` +
  `PasswordAuthenticatedUserInterface`: `getUserIdentifier()` =
  username; `getRoles()` = `ROLE_USER`, plus `ROLE_COMMISH` when
  `commish`; `eraseCredentials()` no-op.
- `UserRepository` implements `PasswordUpgraderInterface`
  (`upgradePassword()` sets the hash and flushes).
- `App\Entity\PasswordResetToken` + repository.
- `TradeOfferRepository::getActiveUserEmails()` joins `user_email`
  instead of reading `user.Email` (one row per address; `TradeMailer`
  already loops).

## 3. Install Security; hashing with silent upgrade

- `composer require symfony/security-bundle symfony/rate-limiter`.
- `App\Security\LegacyMd5PasswordHasher` (implements
  `PasswordHasherInterface`): `verify()` returns false for an empty
  stored hash, otherwise `hash_equals` against `md5($plain)` and
  `md5(<mysqli-style escaped $plain>)`; `needsRehash()` always true;
  `hash()` throws (never used to create hashes).
- `security.yaml` `password_hashers`:
  `legacy_md5: { id: App\Security\LegacyMd5PasswordHasher }` and
  `App\Entity\User: { algorithm: auto, migrate_from: [legacy_md5] }`.
- `App\Security\UserChecker`: reject `active = 'N'` and empty password
  with the same generic "Invalid Username/Password" message.
- Entity user provider on `username`.

## 4. Make the firewall run for legacy requests (spike first)

Today a legacy URL is a router miss, so no firewall, no token, no
remember-me. Fix it before building login on top:

- Add a lowest-priority catch-all route `legacy_fallback`
  (`/{path}`, `path` = `.*`) with a no-op controller.
- `public/index.php`: treat `_route === 'legacy_fallback'` exactly like
  today's "no route" case and call `LegacyBridge::handleRequest()`.
  Phase 13 behaviour (matched-route 404s stay Symfony's; unmapped
  legacy paths get the branded 404) must be unchanged.
- `LegacyBridge` forwards the Symfony response's cookies (remember-me
  renewal, session) before requiring the legacy script.
- Spike checks: a POST to a GET-only Symfony route (now lands in the
  catch-all instead of 405 — acceptable, confirm it renders the branded
  404); session is reopened cleanly by `utils/start.php`; `/img/`,
  `.css`/`.js` legacy paths still serve.
- Fallback if the spike fails: keep the router-miss path and have
  `LegacyBridge` invoke a small `LegacyAuthBootstrap` service that
  loads the token and consumes the remember-me cookie manually.

## 5. Firewall, login, logout, stay logged in

- Firewall `main` (lazy), pattern everything; `dev` firewall for
  profiler/assets.
- `App\Security\LoginAuthenticator` (extends
  `AbstractLoginFormAuthenticator`) on `POST /login/login` (+ `.php`
  alias): `UserBadge`, `PasswordCredentials`, `CsrfTokenBadge`,
  `RememberMeBadge`, `PasswordUpgradeBadge`. Success → plain `Ok`;
  failure → `err - Invalid Username/Password`. Same contract, so the
  modal JS in `base.html.twig` and `menu.php` only gains the CSRF field
  and a "Stay logged in" checkbox (`_remember_me`).
- Success handler sets `lastlog`.
- `login_throttling`: 5 attempts per username+IP per 15 min; failure
  message stays in the `err - …` format.
- `logout`: path `/login/logout` (+ `.php`), POST, redirect to referer
  or `/`.
- `remember_me`: `lifetime: 2592000`, `secure: auto`, `httponly`,
  `samesite: lax`, opt-in. Sliding window: verify whether Symfony
  re-issues the cookie on each cookie-based re-auth; if that is not
  enough to meet "resets on every visit", add a response subscriber
  that re-issues the cookie at most once per day for
  remember-me-enabled sessions. Pick signature vs persistent token in
  the spike (signature needs no table and is invalidated by a password
  change, which is what we want).
- Delete `football/login/login.php` and `logout.php`.

## 6. Legacy identity projection

- `App\Security\LegacySessionProjector`: `project(User)` writes
  `isin`, `teamnum`, `usernum`, `user`, `fullname`, `commish`,
  `message`; `clear()` removes them.
- Subscriber: `LoginSuccessEvent` → project; `LogoutEvent` → clear;
  and on every main request after the firewall, re-project from the
  token (or clear if there is none) so deactivation, renames and
  remember-me logins propagate.
- Impersonation: `becomeTeam()` sets a session marker
  (`impersonating`) that the per-request sync respects instead of
  overwriting; logout clears it. Behaviour of `/admin/become` is
  unchanged (commish flag dropped while impersonating).
- `AuthenticationService` public API unchanged; internals read the
  projected keys only — one source for Symfony and legacy pages, and
  existing tests/mocks and the fake-session E2E recipe keep working.
- `utils/start.php` and `connect.php`: after `setup.php`'s request
  extraction, force `$isin`, `$teamnum`, `$usernum`, `$user`,
  `$fullname`, `$commish` from the session only (unset when absent), so
  request parameters can no longer spoof identity.
- `menu.php`: replace `$teamnum == 2` with the `commish` flag.

## 7. Access control

- `access_control`: `^/admin` → `ROLE_COMMISH`; `^/account` →
  `ROLE_USER`. Access-denied/entry-point handler redirects to `/` with
  the `showlogin` cookie so the modal opens (matches today).
- While impersonating, `/admin` must stay blocked as today — keep
  `AdminAccessSubscriber` and `requireCommissioner()` (they read the
  facade, which honours the dropped flag).

## 8. Forgot password

- `PasswordResetController`: `GET/POST /login/forgotpassword` (ask for
  username; always the same "if that account exists…" response);
  `GET/POST /login/reset/{token}` (new password twice, min 12).
- `PasswordResetService`: 32 random bytes, store SHA-256 only, 1-hour
  expiry, single use, invalidate a user's older tokens on issue and on
  success; rate-limit requests per username/IP. Mail goes to all of the
  user's addresses via Mailer.
- Skip inactive users silently.
- Delete `forgotpassword.php`, `thanksnew.php`; `make_password()` in
  `utils/StringUtils.php` goes if nothing else uses it.

## 9. Account page

- `AccountController` at `/account` (`ROLE_USER`), template
  `templates/account/index.html.twig`, `btn-wmffl` + `text-center`
  buttons.
- Change password: current password, new twice, min 12, must differ
  from current; CSRF; re-hash with the modern hasher; notice to all
  addresses; other sessions' remember-me cookies become invalid.
- Emails: list with the primary marked; add, remove and "make
  primary", each requiring the current password and going through
  `UserEmailService` (step 2 rules). Each change sends a notice to the
  addresses that existed before the change.
- Navbar: the name button in `base.html.twig` and `menu.php` links to
  `/account`; Commish link and Log Out move onto that page and stay in
  the navbar dropdown/modal so logout is still one click. Remove the
  "Change Password" modal button.
- Delete `newpassword.php`, `thankschange.php`; 301
  `/login/newpassword(.php)` → `/account`.

## 10. Admin user tool

- `AdminUserController` edit/new: replace the single email input with
  the address list (add/remove rows, primary radio button), validated
  by `UserEmailService`: 1–5 for an active user, 0–5 for an inactive
  one. The index shows the primary address.
- New action `POST /admin/users/{id}/send-reset` (CSRF): issues a
  set-password link to the user's addresses. New users are created with
  a blank password and sent this link automatically; reactivated users
  get it via the button.
- Index shows whether each user is on a modern hash, legacy hash, or
  has no password (label only — never the hash).

## 11. Remove dead legacy

Delete `loginblock.php`, `logininc.php`, `loginglob.php`,
`listsession.php`, `login/index.php`. `football/login/` should end up
empty and be removed.

## 12. Tests

- Unit: `LegacyMd5PasswordHasher` (raw, escaped, empty hash, wrong
  password), `UserChecker`, `LegacySessionProjector` (incl.
  impersonation marker), `PasswordResetService` (expiry, single use,
  invalidation), `User::getRoles()`, `UserEmailService` (per-user
  duplicate refused, same address on two users allowed, max 5, last
  address of an active user protected, inactive user may reach zero,
  primary promotion on removal, single primary invariant).
- Functional (WebTestCase): login with MD5 row → hash upgraded →
  second login; bad password; inactive; blank password; throttling;
  CSRF rejection; logout; remember-me cookie issued and honoured after
  session loss; `/admin` and `/account` gates; reset flow end to end;
  account password + email forms; admin multi-email + send-reset;
  trade mail goes to every address.
- LegacyBridge: catch-all route dispatches to legacy; unmapped path
  still gives branded 404; matched-route 404 unchanged.
- Full suites: `cd symfony-app && vendor/bin/phpunit tests/
  --coverage-clover coverage.xml`; `vendor/bin/phpunit test/` from root.

## 13. Docs and deploy

- Update `specs/roadmap.md` (Phase 18 item 1), `specs/tech-stack.md`,
  security backlog items C1–C4/H2, and CLAUDE.md's auth notes — spec
  doc changes wait for Josh's approval before commit.
- Deploy: take a `user` table backup → run migration → deploy code.
  Confirm prod `MAILER_DSN` and `APP_SECRET` (remember-me signing) are
  set.
- Rollback note: after deploy, upgraded hashes and blanked inactive
  passwords cannot be read by the old `login.php`; rollback means
  restoring the `user` backup or having affected users reset.
