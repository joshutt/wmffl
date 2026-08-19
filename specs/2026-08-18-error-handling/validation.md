# Phase 13 — Symfony-appropriate error handling: Validation

The phase is done and mergeable when everything below passes. Merge bar
(decision 7 in `requirements.md`): existing unit/controller test suite
green, plus new `WebTestCase` functional coverage for the error paths, plus
manual fake-session E2E.

## Automated

Run from `symfony-app/`: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`

All pre-existing tests still green, plus new coverage for:

- **`public/index.php` routing decision** (via the new `WebTestCase` tests,
  since this file has no direct unit test) — a matched route's
  `createNotFoundException` renders Symfony's branded 404 directly, no
  `LegacyBridge` mapping attempted; an unmatched route still falls through
  to `LegacyBridge` and serves the legacy page correctly.
- **`LegacyBridge::getLegacyScript()`** — unmappable path throws the new
  typed exception (not a bare `Exception`); existing img.php / directory /
  extension-inference behavior unchanged (regression-check existing tests
  if any cover this method, or add if none do).
- **`LegacyBridge::handleRequest()`**
  - unmappable path → branded 404 rendered, logged via Monolog (assert on
    a test/mock log handler or captured log output)
  - legacy script throwing a catchable `\Throwable` → branded 500
    rendered, logged via Monolog, no raw PHP error output in the response
    body
  - legacy fatal (shutdown-function path) → branded 500 rendered, logged
    via Monolog
- **New `WebTestCase` suite** (first in the repo) boots cleanly, runs
  alongside the existing mocked-render `tests/Controller/` suite without
  interference.

## Manual E2E (fake-session, `php -S` with `public/index.php` as router
script — see `gotcha_php_s_dotted_paths`)

1. Hit a real Symfony route with a bad id (e.g. `/team/999999`) →
   branded 404 page, site nav/footer/CSS intact, correct HTTP 404 status
   (check via browser devtools/`curl -I`). Confirm this happened *without*
   `LegacyBridge` attempting a mapping (no legacy-side log entry / no
   `football/team/999999.php` lookup).
2. Hit a genuinely nonexistent URL with no legacy equivalent (e.g.
   `/this-page-does-not-exist-anywhere`) → branded 404, not the raw
   `"Unhandled legacy mapping for ..."` exception dump.
3. Hit a route that legitimately still falls through to legacy (pick a
   live, unmigrated `/football/` page) → renders correctly as before,
   confirming the `_route`-based branching didn't regress the common
   fallback case.
4. Trigger a CSRF-403 on an existing protected POST route (e.g. a trade or
   admin action with a bad/missing token) → branded 403 page, not a
   generic Symfony debug page or legacy fallback.
5. With `APP_ENV=prod` locally, force a legacy fatal (temporarily break a
   known legacy script, or use a scratch fixture) → branded 500 page shown
   to the user (no raw PHP error dump), and the error appears in
   `logs/wmffl.log` with a stack trace/message, not just PHP's
   `error_log()`.
6. With `APP_ENV=prod` locally, confirm a plain 404 (case 1 or 2) now
   appears in `logs/wmffl.log` — previously invisible below the `error`
   floor, should now log at `warning`.
7. Confirm `APP_ENV=dev`/`APP_DEBUG=1` still shows Symfony's normal debug
   exception page (stack trace, etc.) rather than the branded error
   templates — the branded pages are a prod-only concern; don't lose the
   dev debugging experience.

## Data / deploy

- No schema migration needed — this phase touches only application code,
  templates, and Monolog config.
- Deploy note: after deploy, watch `logs/wmffl.log` for a period to gauge
  the volume increase from the `warning` floor change (decision 4 in
  `requirements.md` explicitly deferred noise-filtering — if bot/crawler
  404 volume is a real problem, that's a fast-follow, not a blocker for
  this merge).
- No new env vars or config keys beyond the `monolog.yaml` level change.
