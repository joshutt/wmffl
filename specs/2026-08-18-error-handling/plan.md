# Phase 13 — Symfony-appropriate error handling: Plan

Branch `phase13-error-handling`, one PR. Each task group should land as its
own commit (or a small number of commits). See `requirements.md` for scope
and decisions, `validation.md` for the merge bar.

## 1. Branded error templates

1. Add `templates/bundles/TwigBundle/Exception/error404.html.twig`,
   `error403.html.twig`, and `error.html.twig` (generic 500/other
   fallback), each extending `base.html.twig` so nav/footer/CSS stay
   intact. Minimal content per status: a heading + one line of copy, no
   illustrations, no dynamic links.
2. Confirm Symfony's `error_controller`/exception-listener picks these up
   automatically in `dev` (with `framework.exceptions` or by visiting a
   route with `?_error` debug tooling) and that they only render in `prod`
   by default (Symfony shows the debug exception page in `dev`/`APP_DEBUG`
   regardless — confirm this is still true and desired, no config change
   needed if so).
3. Manual check: force each status in isolation (bad-id 404 route,
   CSRF-403 route, a deliberately-thrown 500) with `APP_ENV=prod` locally
   and confirm the right template renders.

## 2. `index.php`: route matched-controller errors correctly

1. Change the `if (false === $response->isNotFound())` branch in
   `public/index.php` to check `$request->attributes->get('_route') !==
   null` instead — a matched route means Symfony owns the response
   (whatever its status), only an unmatched route falls back to
   `LegacyBridge`.
2. Confirm this doesn't change behavior for the common cases: a normal
   200 (matched route, unaffected), a truly unrouted legacy URL (no
   `_route`, still falls back), and the target bug case — a matched route
   throwing `createNotFoundException` now renders Symfony's branded 404
   directly instead of attempting a `LegacyBridge` mapping.
3. Check for any existing test coverage directly exercising `index.php`'s
   branching; update if present (likely none — this file is typically
   untested directly, confirm during implementation).

## 3. `LegacyBridge`: branded 404 for unmappable paths

1. In `LegacyBridge::getLegacyScript()`, replace the uncaught `throw new
   Exception("Unhandled legacy mapping for $requestPathInfo")` with a
   typed exception the caller can catch and distinguish from other
   failure modes (e.g. a new `LegacyRouteNotFoundException` or reuse
   Symfony's `NotFoundHttpException`).
2. In `LegacyBridge::handleRequest()` (or wherever it's invoked from
   `index.php`), catch that exception, log it via Monolog (include the
   original path), and render the same branded 404 template from task
   group 1 instead of letting the exception escape uncaught.
3. Confirm the img.php/directory-index/extension-inference logic in
   `getLegacyScript()` is unaffected — only the final `throw` at the
   bottom of the function changes.

## 4. `LegacyBridge`: harden the legacy `require`

1. Wrap `require $legacyScriptFilename;` in `try { ... } catch
   (\Throwable $e) { ... }` — log via Monolog, and if no output has been
   sent yet, render the branded 500 template; if output has already
   started (legacy code echoed something before failing), let it be —
   don't try to un-send a partial response.
2. Add `register_shutdown_function(...)` alongside the try/catch to catch
   true fatals (`error_get_last()` with `E_ERROR`/`E_PARSE`/etc. types)
   that PHP doesn't route through normal exception handling — log via
   Monolog, emit the branded 500 if headers/output haven't started.
3. Decide and document (in a code comment) how the shutdown handler
   avoids double-firing when a normal `\Throwable` was already caught and
   handled by the try/catch in the same request.

## 5. Monolog: close the prod 4xx logging gap

1. `config/packages/monolog.yaml`: change `when@prod`'s `main` handler
   `level: error` to `level: warning`.
2. Confirm `channels: ["!deprecation"]` on that handler is still
   appropriate (unaffected by the level change, but re-read while
   touching the file).

## 6. Functional test infra + coverage

1. Confirm `symfony/browser-kit` and `symfony/css-selector` are in
   `composer.json` (check for `symfony/test-pack`); `composer require
   --dev` whichever's missing.
2. Add the first `WebTestCase`-based test file(s) under
   `tests/Controller/` (naming per convention, e.g.
   `ErrorHandlingTest.php`), covering:
   - a bad-id 404 on a real matched route (e.g. hit an existing
     `createNotFoundException` call site) renders the branded 404
     directly — no `LegacyBridge` mapping attempt (assert via response
     content matching the branded template, not the legacy one)
   - a truly unrouted, legacy-unmappable URL renders the branded 404 via
     the `LegacyBridge` path from task group 3
   - a route that still legitimately falls through to legacy (pick a
     stable, still-legacy page) renders correctly, confirming the
     `_route`-based branching in task group 2 didn't break the common
     fallback case
   - a forced legacy fatal (a small fixture script, or a known
     already-broken legacy path if one safely exists) is caught, logged,
     and rendered as the branded 500
3. Confirm the new tests run cleanly alongside the existing suite (no
   shared state/session bleed — `WebTestCase` boots a fresh kernel per
   test by default, but check for interaction with the fake-session
   pattern other tests may rely on).

**As implemented:** the "matched-route 404" and "unrouted URL" cases landed
in `tests/Controller/ErrorPagesTest.php` (`WebTestCase`), using two new
fixture-only routes rather than a real feature route, to avoid depending on
a provisioned `wmffl_test` database (see `requirements.md`'s "found during
implementation" note). The "unmappable path" and "legacy script throws"
cases — which live in `LegacyBridge`, entirely outside the kernel's
request-handling boundary that `WebTestCase` operates within — landed as
direct static-method tests in `tests/LegacyBridgeTest.php` instead. The
real-fatal/shutdown-function case stayed manual-E2E-only, as anticipated;
its type-filtering logic is unit-tested via the extracted
`isFatalErrorType()` predicate. Two pieces of test infra needed adding
that weren't anticipated: `tests/bootstrap.php` (dotenv loading) and
`APP_RUNTIME_MODE=web=1` in `phpunit.xml` (forces the HTML error renderer
under PHPUnit's CLI SAPI) — see both files' comments.

## 7. Final pass

1. Run the full `validation.md` checklist (automated + manual E2E); fix
   anything it turns up.
2. Update `specs/roadmap.md`: move Phase 13 into `Done` with a summary
   entry once validation passes.
