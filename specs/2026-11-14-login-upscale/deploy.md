# Deploy notes — Login & Security Upscale (Phase 18 item 1)

For Josh. Nothing here has been run on staging or production.

## Before

1. Back up the `user` table (the migration blanks inactive passwords and
   drops `user.Email`; neither comes back with `down()`):
   `mysqldump wmffl user > user-backup-$(date +%F).sql`
2. Prod `symfony-app/.env.local`:
   - `APP_SECRET` set, non-empty and **stable**. It now signs the
     "Stay logged in" cookies (as well as CSRF tokens); changing it later
     logs out every remembered user. The tracked `.env` leaves it empty.
   - `MAILER_DSN` set to a real transport. Forgot-password links, new-user
     set-password links and account notices all depend on it.

## Deploy

Run the code deploy and the migration together, with as short a gap as you can.
Between the two, either the old code is reading a dropped `user.Email` (trade
mail, admin users) or the new code is reading a `user_email` table that doesn't
exist yet.

1. Deploy the code, then `cd symfony-app && composer install --no-dev -o`.
   This adds `symfony/security-bundle` and `symfony/rate-limiter`.
2. `php bin/console doctrine:migrations:migrate` applies
   `Version20261114000000`.
3. `php bin/console cache:clear`.

## What users will see

- Everyone logged in at deploy time is logged out once.
- Owners log in with their current password. It is silently rehashed on that
  first login.
- The 24 inactive accounts can't log in until you send them a set-password
  link from `/admin/users` ("Send link"), after reactivating them.

## After

- Check that one login works and that the row now holds a `$2y$`/`$argon2` hash.
- Track MD5 holdouts. The number should fall to 0 as owners log in:
  `SELECT COUNT(*) FROM user WHERE active='Y' AND LENGTH(Password)=32;`
- Owners can add extra addresses themselves at `/account`. Each user starts
  with one address, the old `user.Email`, as their primary.

## Rollback

Upgraded hashes and blanked inactive passwords can't be read by the old
`login.php`. Rolling back the code therefore means:
`doctrine:migrations:migrate prev` (re-adds `user.Email` from each user's
primary address), then restoring the `user` backup, or having the affected
users reset their passwords.
