# Plan — Phase 10.5: Logging infrastructure (Monolog)

## 1. Install symfony/monolog-bundle

- `cd symfony-app && composer require symfony/monolog-bundle`
- Confirm Symfony Flex drops a default `config/packages/monolog.yaml`
  recipe file; note whether it also touches `config/packages/dev/` or
  `config/packages/test/` — those are fine to keep as-is.

## 2. Configure the prod handler

- Edit `symfony-app/config/packages/monolog.yaml`: under `when@prod`,
  replace/confirm the handler writes to an absolute path resolving to the
  repo-root `logs/wmffl.log` (not `symfony-app/var/log/`), at `level: error`
  minimum, `type: stream` (**not** `rotating_file` — rotation is handled
  externally by `logrotate` in step 3a, so Monolog must keep writing to
  the literal `logs/wmffl.log` path rather than managing its own
  date-suffixed files).
- Make sure the handler is **not** gated by `when@prod && env(APP_DEBUG)`
  tricks or anything that would suppress it outside of a clean prod
  config — it should fire regardless of `APP_DEBUG`.
- Leave `when@dev`/`when@test` blocks (if the recipe created them) on
  their Flex defaults — out of scope per requirements.md.

## 3a. Add the logrotate config

- Create `deploy/logrotate.d/wmffl` (new `deploy/` dir if it doesn't
  exist yet) with:
  ```
  /absolute/path/to/wmffl/logs/wmffl.log {
      daily
      rotate 14
      missingok
      notifempty
      dateext
      dateformat -%Y-%m-%d
      create 0664 www-data www-data
  }
  ```
  Leave the path and `create` owner/group as placeholders with a comment
  telling Josh to substitute the actual prod absolute path and web-server
  user/group at install time — the repo doesn't otherwise encode prod
  filesystem layout.
  - Deliberately no `copytruncate`: both writers (Monolog's `stream`
    handler and the legacy `ini_set('error_log', ...)` calls) are
    per-request PHP processes with no long-lived open file handle across
    requests, so plain rotate+`create` is safe and loses nothing at the
    rotation boundary.
- Document as a manual one-time step (README note or comment in the file
  itself): `sudo cp deploy/logrotate.d/wmffl /etc/logrotate.d/wmffl` on
  the prod box, then verify with `sudo logrotate -d /etc/logrotate.d/wmffl`
  (dry run, no `-f`) to confirm the config parses before it's live.

## 3. Broaden the swallowing catch in AdminMoneyController

- `symfony-app/src/Controller/Admin/AdminMoneyController.php:86` —
  change `catch (\Exception $e)` to `catch (\Throwable $e)`.
- Inject `Psr\Log\LoggerInterface` (autowired Monolog channel) into the
  controller if not already present, and log the caught error
  (`$this->logger->error(...)` with message + exception) before whatever
  the existing catch body does (redirect/flash message, etc. — preserve
  current user-facing behavior, just stop swallowing silently).

## 4. Local verification

- Run the app locally (`APP_ENV=prod` config, or a temporary local
  override) and trigger a forced error path through
  `AdminMoneyController::recordChange` to confirm an entry lands in
  `logs/wmffl.log` in Monolog's format (distinguishable from the legacy
  `error_log()` lines already in that file).
- Confirm legacy routes (anything falling through `LegacyBridge`) are
  unaffected — their `ini_set('error_log', ...)` lines should look
  exactly as they did before.

## 5. Prod verification (Josh, at deploy)

- After deploy, confirm `logs/wmffl.log` is writable by the prod web
  server user.
- Force (or wait for) a real error through a Symfony-native route and
  confirm it appears in the log.
- Install the logrotate config per step 3a
  (`sudo cp deploy/logrotate.d/wmffl /etc/logrotate.d/wmffl`, substituting
  the real path/owner), dry-run it (`sudo logrotate -d ...`), then force
  one rotation (`sudo logrotate -f /etc/logrotate.d/wmffl`) to confirm
  `logs/wmffl.log` gets rotated to a dated file and a fresh empty
  `logs/wmffl.log` is created with correct permissions, and that both the
  app and legacy code can still write to the new file afterward.
- This step is called out explicitly in validation.md since it's the
  actual motivating problem ("prod logging was silently broken") and
  can't be verified from dev.
