# Validation — Phase 10.5: Logging infrastructure (Monolog)

Implementation is done and mergeable when all of the following hold:

1. **Dependency present**: `symfony-app/composer.json` requires
   `symfony/monolog-bundle`; `composer.lock` updated accordingly.
2. **Config exists and is correct**: `symfony-app/config/packages/monolog.yaml`
   has a `when@prod` handler at `error` level or lower (i.e. catches error
   and above), pointed at `logs/wmffl.log` (repo-root path, not
   `symfony-app/var/log/`), active regardless of `APP_DEBUG`, using a
   plain `stream` handler — **not** `rotating_file` (that would fork
   Monolog's writes onto its own date-suffixed filename instead of the
   literal path logrotate manages).
3. **AdminMoneyController fix**: `recordChange`'s catch block is
   `catch (\Throwable $e)`, not `catch (\Exception $e)`, and the caught
   error is logged (not silently swallowed) before existing
   redirect/flash behavior runs. Existing tests for this controller (if
   any) still pass; add/update a test that forces a `\Throwable`
   (e.g. `\TypeError`) into that path and asserts it no longer produces a
   blank 500 with nothing logged.
4. **Legacy channel untouched**: `git diff` shows no changes to the
   `ini_set('error_log', ...)` lines in `football/front_controller.php`
   or `symfony-app/src/LegacyBridge.php`.
4a. **Logrotate config present and correct**: `deploy/logrotate.d/wmffl`
   exists with `daily`, `rotate 14`, `missingok`, `notifempty`, `create`
   (no `copytruncate`), and a placeholder comment telling the installer
   to substitute the real absolute path and web-server user/group.
   `logrotate -d` against the file with a real path substituted in must
   parse cleanly (no syntax errors) in a local/staging check.
5. **Local smoke test**: forcing an error through a Symfony-native route
   (e.g. the `AdminMoneyController` path from plan.md step 4) produces a
   new line in `logs/wmffl.log` in Monolog's format, alongside — not
   replacing — existing legacy `error_log()` lines in the same file.
6. **Prod verification** (Josh, post-deploy, per plan.md step 5): confirms
   the web server user can write to `logs/wmffl.log` on prod, and that a
   real or forced error actually lands there. This is the step that
   closes out the original motivating incident — don't mark this phase
   done in `specs/roadmap.md`'s Done section until Josh confirms it.
6a. **Logrotate installed and verified on prod** (Josh, post-deploy, per
   plan.md step 5): config copied to `/etc/logrotate.d/wmffl`, dry-run
   clean, a forced rotation (`logrotate -f`) produces a dated rotated
   file plus a fresh empty `logs/wmffl.log` with correct permissions,
   and both the Symfony app and legacy code can still write to it
   afterward.
7. **No regressions**: `symfony-app/vendor/bin/phpunit tests/` passes.
8. **Scope discipline**: no other `catch (\Exception ...)` blocks were
   touched, no legacy files were modified, no `var/log/prod.log` or other
   new log path was introduced, no `compress`/`delaycompress` added to
   the logrotate config.
