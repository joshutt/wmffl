# Plan — Phase 10.5: Logging infrastructure (Monolog)

## 1. Install symfony/monolog-bundle

- `cd symfony-app && composer require symfony/monolog-bundle`
- Confirm Symfony Flex drops a default `config/packages/monolog.yaml`
  recipe file; note whether it also touches `config/packages/dev/` or
  `config/packages/test/` — those are fine to keep as-is.

## 2. Configure the prod handler (rotating_file)

- Edit `symfony-app/config/packages/monolog.yaml`: under `when@prod`,
  configure a handler with:
  - `type: rotating_file`
  - `path`: resolves to the repo-root `logs/wmffl.log` (not
    `symfony-app/var/log/`) — Monolog treats this as the base name and
    derives its own dated filename from it (default `filename_format`
    `{filename}-{date}`, `date_format: Y-m-d`, e.g.
    `logs/wmffl-2026-07-26.log`); leave those two at their defaults
    unless there's a reason to change them
  - `max_files: 14`
  - `level: error` minimum
- Make sure the handler is **not** gated by `when@prod && env(APP_DEBUG)`
  tricks or anything that would suppress it outside of a clean prod
  config — it should fire regardless of `APP_DEBUG`.
- Leave `when@dev`/`when@test` blocks (if the recipe created them) on
  their Flex defaults — out of scope per requirements.md.
- No external `logrotate` config, no `deploy/` directory changes —
  rotation is entirely Monolog's job per decision #4 in requirements.md.

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
  `AdminMoneyController::recordChange` to confirm a new dated file
  appears under `logs/` (e.g. `logs/wmffl-<today>.log`) with the error in
  Monolog's format.
- Confirm legacy routes (anything falling through `LegacyBridge`) are
  unaffected — their `ini_set('error_log', ...)` lines should keep
  landing in the literal `logs/wmffl.log`, exactly as they did before,
  as a separate file from Monolog's dated one.

## 5. Prod verification (Josh, at deploy)

- After deploy, confirm the `logs/` directory is writable by the prod web
  server user (Monolog will create new dated files there; it doesn't
  need the literal `logs/wmffl.log` to pre-exist).
- Force (or wait for) a real error through a Symfony-native route and
  confirm a new dated file (or an appended line in today's dated file)
  shows up in `logs/`.
- No logrotate install step — Monolog handles retention itself. Confirm
  after ~14+ days that older dated files are actually being pruned (or
  reason about `max_files: 14` behavior directly from the config if
  waiting two weeks isn't practical for sign-off).
- This step is called out explicitly in validation.md since it's the
  actual motivating problem ("prod logging was silently broken") and
  can't be verified from dev.
