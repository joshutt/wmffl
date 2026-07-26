# Validation — Phase 10.5: Logging infrastructure (Monolog)

Implementation is done and mergeable when all of the following hold:

1. **Dependency present**: `symfony-app/composer.json` requires
   `symfony/monolog-bundle`; `composer.lock` updated accordingly.
2. **Config exists and is correct**: `symfony-app/config/packages/monolog.yaml`
   has a `when@prod` handler at `error` level or lower (i.e. catches error
   and above), `type: rotating_file`, `path` resolving to the repo-root
   `logs/wmffl.log` base (not `symfony-app/var/log/`), `max_files: 14`,
   active regardless of `APP_DEBUG`.
3. **AdminMoneyController fix**: `recordChange`'s catch block is
   `catch (\Throwable $e)`, not `catch (\Exception $e)`, and the caught
   error is logged (not silently swallowed) before existing
   redirect/flash behavior runs. Existing tests for this controller (if
   any) still pass; add/update a test that forces a `\Throwable`
   (e.g. `\TypeError`) into that path and asserts it no longer produces a
   blank 500 with nothing logged.
4. **Legacy channel untouched**: `git diff` shows no changes to the
   `ini_set('error_log', ...)` lines in `football/front_controller.php`
   or `symfony-app/src/LegacyBridge.php` — they keep writing to the
   literal `logs/wmffl.log`, unrotated, as a separate file from
   Monolog's dated output (accepted tradeoff, decision #4).
5. **Local smoke test**: forcing an error through a Symfony-native route
   (e.g. the `AdminMoneyController` path from plan.md step 4) produces a
   new dated file under `logs/` (e.g. `logs/wmffl-<today>.log`) in
   Monolog's format, without touching the literal `logs/wmffl.log` that
   legacy code writes to.
6. **Prod verification** (Josh, post-deploy, per plan.md step 5): confirms
   the web server user can create/write files in `logs/` on prod, and
   that a real or forced error actually produces a dated Monolog file
   there. This is the step that closes out the original motivating
   incident — don't mark this phase done in `specs/roadmap.md`'s Done
   section until Josh confirms it.
7. **No regressions**: `symfony-app/vendor/bin/phpunit tests/` passes.
8. **Scope discipline**: no other `catch (\Exception ...)` blocks were
   touched, no legacy files were modified, no `var/log/prod.log` or other
   new log path was introduced, no `logrotate` config or `deploy/`
   directory was added — rotation is Monolog-only per decision #4.
