# Phase 13 — Symfony-appropriate error handling: Validation

The phase is done and mergeable when everything below passes. Merge bar
(decision 7 in `requirements.md`): existing unit/controller test suite
green, plus new `WebTestCase` functional coverage for the error paths, plus
manual fake-session E2E.

## Automated

Run from `symfony-app/`: `vendor/bin/phpunit tests/ --coverage-clover coverage.xml`

All pre-existing tests still green (848 total as of this phase), plus new
coverage for:

- **`tests/Controller/ErrorPagesTest.php`** (`WebTestCase`, first in the
  repo) — boots the real kernel and asserts on actually-rendered HTML,
  covering what mocked-`render()` unit tests structurally can't (Phase 12
  found exactly this class of bug in a non-error template):
  - a matched-route `NotFoundHttpException`/`AccessDeniedHttpException`
    renders the branded `error404`/`error403` template, not Symfony's
    debug page or a generic one
  - a genuinely unrouted URL (Symfony's own router 404, before any
    controller runs) also renders the branded 404
  - uses two dedicated fixture routes (`tests/Fixtures/Controller/
    ErrorFixtureController.php`, wired only `when@test` in
    `config/routes.yaml`) rather than a real feature route, specifically
    so these tests don't depend on a provisioned `wmffl_test` database —
    every real matched-route 404 in this app reads from the DB before it
    can 404
  - boots with `createClient(['debug' => false])`: Symfony only renders
    the branded templates when `kernel.debug` is off (see manual E2E #7)
  - **environment gotcha found and fixed**: kernel-booting tests
    (`WebTestCase`/`KernelTestCase`) run under PHP's CLI SAPI, and
    Symfony's `error_renderer` service resolves to `CliErrorRenderer`
    (a VarDumper-style text dump, not HTML) unless
    `kernel.runtime_mode.web` is forced true — `phpunit.xml` now sets
    `APP_RUNTIME_MODE=web=1` for this reason. Also required adding
    `tests/bootstrap.php` (this repo's first kernel-booting test needed
    `.env*` loaded, which nothing before it did) — see the comments in
    both files.
- **`tests/LegacyBridgeTest.php`** — direct static-method calls (no HTTP
  layer, since `LegacyBridge` runs entirely outside the kernel's
  request-handling boundary — `WebTestCase` cannot reach it at all):
  - `getLegacyScript()` throws `LegacyRouteNotFoundException` (not a bare
    `Exception`) for an unmappable path; still resolves a real legacy file
    correctly for a mappable one (regression check)
  - `handleRequest()` on an unmappable path logs via `LegacyErrorPageService
    ::logNotFound()` and echoes the branded 404, without touching any of
    the legacy globals/require path
  - `handleRequest()` catching a `\Throwable` thrown by the required
    legacy script logs via `logFatal()` (with the exception attached) and
    echoes the branded 500
  - `isFatalErrorType()` (the pure, extracted predicate behind the
    `register_shutdown_function` fatal-type filter) — true for
    `E_ERROR`/`E_PARSE`/`E_CORE_ERROR`/`E_COMPILE_ERROR`/`E_USER_ERROR`/
    `E_RECOVERABLE_ERROR`, false for warnings/notices/deprecations/null.
    **Deliberately not covered directly**: the shutdown-function's actual
    firing on a real fatal — PHP only runs registered shutdown functions
    at real script termination, not observable from within a single
    PHPUnit process without ending it. Covered by manual E2E #5 instead.
- **`tests/Service/LegacyErrorPageServiceTest.php`** — template selection
  by status code (404/403/other→generic), and that `logNotFound`/
  `logFatal` delegate to the logger at the right level with the exception
  attached when given one.

## Manual E2E (fake-session, `php -S` with `public/index.php` as router
script — see `gotcha_php_s_dotted_paths`)

Not run end-to-end as part of this implementation pass — attempts to drive
a backgrounded `php -S` under `APP_ENV=prod` in the agent sandbox didn't
reliably pick up the env vars (background-process env propagation is
unreliable in that harness specifically; a synchronous, non-backgrounded
`php -r` reproduction of the same request *did* confirm correct behavior
for both debug=on and debug=off). Josh should run this checklist for real
before merging:

1. Hit a real Symfony route with a bad id (e.g. `/team/999999`) →
   branded 404 page, site nav/footer/CSS intact, correct HTTP 404 status
   (check via browser devtools/`curl -I`). Confirm this happened *without*
   `LegacyBridge` attempting a mapping (no legacy-side log entry / no
   `football/team/999999.php` lookup).
2. Hit a genuinely nonexistent URL with no legacy equivalent (e.g.
   `/this-page-does-not-exist-anywhere`) → branded 404, not the raw
   `"Unhandled legacy mapping for ..."` exception dump. (Confirmed already
   during implementation via a direct `php -r` request — see above — but
   worth re-confirming through a real browser.)
3. Hit a route that legitimately still falls through to legacy (pick a
   live, unmigrated `/football/` page) → renders correctly as before,
   confirming the `_route`-based branching didn't regress the common
   fallback case. (Also spot-checked during implementation.)
4. Trigger a CSRF-403 on an existing protected POST route (e.g. a trade or
   admin action with a bad/missing token) → branded 403 page, not a
   generic Symfony debug page or legacy fallback.
5. With `APP_ENV=prod` (or any `APP_DEBUG=0` config), force a legacy fatal
   (temporarily break a known legacy script, or use a scratch fixture) →
   branded 500 page shown to the user (no raw PHP error dump), and the
   error appears in `logs/wmffl.log` with a stack trace/message, not just
   PHP's `error_log()`.
6. With `APP_DEBUG=0`, confirm a plain 404 (case 1 or 2) now appears in
   `logs/wmffl.log` — previously invisible below the `error` floor, should
   now log at `warning`.
7. Confirm `APP_DEBUG=1` still shows Symfony's normal debug exception page
   (stack trace, etc.) for a **Symfony-routed** 404/403/500 — the branded
   pages there are a debug-off concern by design (`TwigErrorRenderer` only
   picks a custom template when `kernel.debug` is false). Note this does
   **not** apply to `LegacyBridge`'s own branded 404 for an unmappable
   path (case 2): that path calls `LegacyErrorPageService::renderErrorPage()`
   directly rather than going through Symfony's `TwigErrorRenderer`, so it
   renders the branded template unconditionally, in every environment —
   confirmed harmless/reasonable during implementation (there's no legacy
   mapping detail worth debugging beyond "this genuinely doesn't map to
   anything"), but worth knowing it's an intentional asymmetry, not a bug.

## Data / deploy

- No schema migration needed — this phase touches only application code,
  templates, and Monolog config.
- Deploy note: after deploy, watch `logs/wmffl.log` for a period to gauge
  the volume increase from the `warning` floor change (decision 4 in
  `requirements.md` explicitly deferred noise-filtering — if bot/crawler
  404 volume is a real problem, that's a fast-follow, not a blocker for
  this merge).
- No new env vars or config keys in the deployed app itself. The only new
  env var (`APP_RUNTIME_MODE=web=1`) is test-suite-only, set in
  `phpunit.xml`, and has no effect on `dev`/`prod` runtime.
