# Requirements — Phase 10.5: Logging infrastructure (Monolog)

## Problem

Found 2026-07-23 debugging a prod-only 500 on the `/admin/money` paid-status
tool: there is no Monolog anywhere in `symfony-app` — no
`symfony/monolog-bundle` dependency, no `config/packages/monolog.yaml`, no
exception listener. The only thing writing to `logs/wmffl.log` today is a
bare `ini_set('error_log', ...)` in `football/front_controller.php` and
`symfony-app/src/LegacyBridge.php`, and `LegacyBridge` only runs when the
Symfony kernel 404s and falls back to legacy
(`symfony-app/public/index.php`'s `$response->isNotFound()` branch) — so any
error in a genuine Symfony-routed controller (like `AdminMoneyController`)
never reaches that file, in dev or prod. Fatal errors there currently rely on
whatever the box's PHP/webserver `error_log` ini default happens to be, with
no visibility from inside the repo.

Confirmed during scoping: `logs/wmffl.log` exists, is 4.8MB, and was last
written today (2026-07-25) — it's an actively-used file, not dead. It's
served from a plain filesystem path outside both app roots (referenced via
`$projectRoot` in `LegacyBridge` and `$parent` in `front_controller.php`),
not `symfony-app/var/log/`.

## Decisions (confirmed with Josh 2026-07-26)

1. **Log path: keep `logs/wmffl.log`.** Monolog's prod handler writes to the
   same file the legacy `ini_set('error_log', ...)` channel already uses.
   One file to tail/grep during an incident, no new path to remember mid-fire.
   This is a deliberate deviation from the Symfony-idiomatic
   `var/log/prod.log` default.
2. **Legacy channel stays separate — no bridging.** The existing
   `ini_set('error_log', ...)` calls in `front_controller.php` and
   `LegacyBridge.php` are left untouched. Monolog is purely additive,
   covering the Symfony-native route path only. Both channels happen to
   land in the same file, but they are two independent writers, not one
   pipeline. Revisit unifying them only if `/football/` ever generates a
   log volume/format problem worth solving (unlikely before the Final
   phase deletes `/football/` entirely).
3. **Throwable-catch sweep is scoped to `AdminMoneyController::recordChange`
   only** — the specific swallowing `catch (\Exception $e)` block that
   caused this phase to exist (confirmed present at
   `symfony-app/src/Controller/Admin/AdminMoneyController.php:86`). A
   repo-wide audit of other narrow catches is explicitly out of scope; do
   it as a separate follow-up if warranted.
4. **Log rotation: daily, 14-day retention, via system `logrotate` on the
   shared literal file.** `logs/wmffl.log` is currently 4.8MB and
   unrotated. Monolog's own `rotating_file` handler was considered and
   rejected here: it rotates by writing to *its own* date-suffixed
   filenames (`wmffl-{date}.log`), not by rotating the literal
   `logs/wmffl.log` path in place — since the legacy `error_log()` calls
   in `front_controller.php`/`LegacyBridge.php` are staying on that exact
   literal path (decision #2), a Monolog-managed rotating filename would
   silently fork the two channels into different files and defeat
   decision #1 ("one file to tail"). Instead: a system `logrotate` config
   (`daily`, `rotate 14`) targets `logs/wmffl.log` directly, so both the
   Monolog stream handler and the legacy `error_log()` writes keep
   landing in the same literal path day-to-day, and logrotate rotates
   the whole file — both channels' entries together — once every 24h,
   pruning anything past 14 rotations.
   - Refinement over plain `copytruncate`: since both writers are
     per-request PHP processes (PHP-FPM/mod_php, no long-lived daemon
     holding the file open), a standard rotate+`create` cycle (rename old
     file, create a fresh empty one) is safe and avoids `copytruncate`'s
     small window where lines written between the copy and the truncate
     are lost. `copytruncate` is only needed when a long-running process
     keeps an fd open across rotation, which doesn't apply here.
   - Installing the logrotate config into `/etc/logrotate.d/` requires
     root and is outside anything the app itself can do — see Scope.

## Scope

In scope:
- Add `symfony/monolog-bundle` to `symfony-app/composer.json`
- `symfony-app/config/packages/monolog.yaml` with a `when@prod` block at
  `error` level minimum, writing to `logs/wmffl.log` via a plain `stream`
  handler (not `rotating_file` — see decision #4), independent of
  `APP_DEBUG`/`APP_ENV` request path (i.e. don't gate it behind debug mode)
- Broaden `AdminMoneyController::recordChange`'s `catch (\Exception $e)` to
  `catch (\Throwable $e)` and log it via the injected/autowired logger
  instead of silently swallowing
- A `logrotate` config file committed to the repo (e.g.
  `deploy/logrotate.d/wmffl`) targeting `logs/wmffl.log`: `daily`,
  `rotate 14`, `missingok`, `notifempty`, `create` (correct perms/owner
  for the web server user), `dateext`. Documented as a manual one-time
  install step (`cp`/symlink into `/etc/logrotate.d/` — requires root,
  can't be automated from within the app or a Symfony console command)
- Verification on prod: confirm the web server user can write to
  `logs/wmffl.log`, that a forced error actually produces an entry, and
  that logrotate's config is accepted (`logrotate -d` dry-run) and
  actually rotates the file (`logrotate -f` forced test run)

Out of scope:
- Routing legacy `error_log()` calls through Monolog (decision #2 above)
- Any catch-block audit beyond `AdminMoneyController`
- Dev/test environment logging changes beyond whatever Monolog's default
  recipe config provides out of the box
- Compressing rotated logs (`compress`/`delaycompress`) — not requested;
  add later if disk usage becomes a concern
- Any change to how/where legacy `error_log()` calls target their path —
  they keep pointing at the literal `logs/wmffl.log`, same as today

## Context

- This is Phase 10.5 in `specs/roadmap.md`, ahead of Phase 10.6 (issues
  table redesign — spec not yet started) and Phase 11 (activations UI).
- Mission-wise this is pure migration/infra work (mission.md thread 1 and
  3 — admin tooling, in this case admin *observability*, must keep pace).
- No template/UI changes; this phase is invisible to league members.
