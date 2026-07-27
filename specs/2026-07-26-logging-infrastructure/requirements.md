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

1. **Log path: `logs/` directory, Monolog gets its own rotating filename.**
   Originally decided as "keep the literal `logs/wmffl.log`" for a single
   shared file; superseded by decision #4 once rotation entered scope —
   Monolog's `rotating_file` handler can't rotate a static path in place,
   only its own dated filenames, so Monolog writes to
   `logs/wmffl-{date}.log` (see #4) while the legacy channel keeps the
   literal `logs/wmffl.log`. Both still live in the same `logs/` directory
   (not `symfony-app/var/log/`), which remains a deliberate deviation from
   the Symfony-idiomatic default — just not the same *file* anymore.
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
4. **Log rotation: daily, 14-day retention, via Monolog's own
   `rotating_file` handler — no external `logrotate`/system-level step.**
   Confirmed against the Symfony MonologBundle docs (2026-07-26): the
   `rotating_file` handler type self-manages rotation with `max_files`
   for retention and `filename_format`/`date_format` for the daily
   filename (defaults to `{filename}-{date}` / `Y-m-d`, e.g.
   `wmffl-2026-07-26.log`), purely in-app — no cron, no root install, no
   `deploy/logrotate.d/` file.
   - Tradeoff accepted explicitly (Josh, 2026-07-26): this only rotates
     Monolog's own entries. The legacy `error_log()` calls in
     `front_controller.php`/`LegacyBridge.php` keep writing to the
     literal `logs/wmffl.log`, unrotated, growing forever, in a
     **separate physical file** from Monolog's dated ones — same
     behavior as today for that channel, just no longer sharing a
     filename with the new Monolog output. An incident that spans both
     legacy and Symfony-native errors means checking two files instead
     of one.
   - This supersedes the earlier system-`logrotate`-on-shared-file
     approach (rejected: required a root-installed config outside the
     app, which Josh doesn't want to maintain).

## Scope

In scope:
- Add `symfony/monolog-bundle` to `symfony-app/composer.json`
- `symfony-app/config/packages/monolog.yaml` with a `when@prod` block at
  `error` level minimum, `type: rotating_file`, `path: logs/wmffl.log`
  (Monolog derives its own dated filename from this base — see decision
  #4), `max_files: 14`, independent of `APP_DEBUG`/`APP_ENV` request path
  (i.e. don't gate it behind debug mode)
- Broaden `AdminMoneyController::recordChange`'s `catch (\Exception $e)` to
  `catch (\Throwable $e)` and log it via the injected/autowired logger
  instead of silently swallowing
- Verification on prod: confirm the web server user can write to the
  `logs/` directory, that a forced error actually produces a new dated
  file, and that a second forced error on a later date rotates correctly
  (or verify via `max_files`/pruning behavior directly)

Out of scope:
- Routing legacy `error_log()` calls through Monolog (decision #2 above)
- Any catch-block audit beyond `AdminMoneyController`
- Dev/test environment logging changes beyond whatever Monolog's default
  recipe config provides out of the box
- Any system-level `logrotate` config — rejected per decision #4
- Compressing rotated logs — Monolog's `rotating_file` handler doesn't
  compress; not requested, add later if disk usage becomes a concern
- Any change to how/where legacy `error_log()` calls target their path —
  they keep pointing at the literal `logs/wmffl.log`, same as today,
  and stay unrotated (decision #4 tradeoff)

## Context

- This is Phase 10.5 in `specs/roadmap.md`, ahead of Phase 10.6 (issues
  table redesign — spec not yet started) and Phase 11 (activations UI).
- Mission-wise this is pure migration/infra work (mission.md thread 1 and
  3 — admin tooling, in this case admin *observability*, must keep pace).
- No template/UI changes; this phase is invisible to league members.
