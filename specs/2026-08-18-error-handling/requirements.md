# Phase 13 — Symfony-appropriate error handling: Requirements

## Goal

Fix the LegacyBridge fallback's error behavior, which today is inconsistent
and, in one common case, actively worse than either side alone (see
`specs/roadmap.md` Phase 13 for the full problem statement). Branch
`phase13-error-handling`, one PR.

## Problem recap

- `symfony-app/public/index.php` decides whether to fall back to legacy
  purely by checking `$response->isNotFound()` — it can't tell a genuinely
  unrouted URL apart from a deliberate `$this->createNotFoundException(...)`
  thrown by a matched Symfony controller (~20 call sites today, e.g.
  `TeamController.php:131,178`, `PlayerProfileController.php:56`,
  `ArticleController.php`, `AdminConfigController.php`,
  `AdminProposalController.php`).
- Both cases land in `LegacyBridge::getLegacyScript()`, which then tries to
  map the URL to a file under `/football/`. When the route is Symfony-only
  (a bad id, not a legacy page), it throws `"Unhandled legacy mapping for
  ..."` (`LegacyBridge.php:104`) — an uncaught exception raised *after* the
  Symfony kernel already finished handling the request, bypassing Symfony's
  exception handling, the branded error page, and Monolog entirely.
- Symfony's own default error pages (for the cases that do render normally)
  don't match site styling — no override template exists today.
- `config/packages/monolog.yaml`'s `when@prod` `main` handler floors at
  `level: error`, so real 401/403/404s are invisible in `logs/wmffl.log`.
- `LegacyBridge::handleRequest`'s `require $legacyScriptFilename;`
  (`LegacyBridge.php:158`) is unguarded — a fatal error or uncaught
  exception from legacy code produces a raw, unstyled PHP error dump, not
  logged through Monolog.

## Decisions

1. **Distinguish matched-controller vs. unrouted by the `_route` request
   attribute**, checked in `public/index.php` after `$kernel->handle()`.
   If `$request->attributes->get('_route')` is set, Symfony's router
   matched something — any response/exception from that dispatch (404,
   403, 500) is rendered by Symfony directly, never handed to
   `LegacyBridge`. Only a request with no matched route (Symfony's own
   404 with no `_route` attribute) falls back to legacy.
2. **`LegacyBridge`'s own "can't map this path" case also gets a branded
   404**, logged via Monolog, instead of the current uncaught `Exception`
   that bypasses everything. This turns `getLegacyScript()`'s throw into
   something `LegacyBridge::handleRequest()` (or its caller) catches and
   converts into the same branded 404 response used elsewhere — the
   "genuinely unrouted AND unmappable in legacy" dead end becomes a normal
   404, not a raw uncaught-exception page.
3. **Branded error templates** at
   `templates/bundles/TwigBundle/Exception/error404.html.twig`,
   `error403.html.twig`, and a generic `error.html.twig` fallback (500 and
   anything else) — extend `base.html.twig` so nav/footer/CSS stay intact,
   minimal content per status (a heading + one line of copy), no
   illustrations, no "did you mean" links, no per-code copywriting beyond
   the essentials. This is the smallest change that meets the roadmap's
   "matching site styling" goal.
4. **Lower the prod Monolog `main` handler floor from `error` to
   `warning`.** Symfony logs 404/403/401 at warning by default, so this is
   a one-line config change and is enough to close the visibility gap. No
   noise-filtering/dedicated-channel work in this phase — if bot/crawler
   404 volume turns out to be a real problem post-deploy, that's a
   follow-up, not a blocker here.
5. **Harden the legacy `require`** with `try`/`catch (\Throwable)` around
   it for catchable errors, **plus `register_shutdown_function`** to catch
   true fatals (E_ERROR and friends) that PHP doesn't let you catch
   normally. Both paths: log via Monolog (not just PHP's `error_log()`)
   and, if headers haven't already been sent, emit the branded 500 page
   instead of a raw PHP error dump.
6. **Testing: add WebTestCase functional tests**, the first in this
   codebase (everything under `tests/Controller/` today mocks `render()`).
   Justification: Phase 12 found a template-shape bug (`admin/config/
   edit.html.twig` reading `config.value` unconditionally) invisible to
   mocked-render unit tests — the whole point of this phase is rendering
   correctness on the error path, so an actual Twig render in the test
   suite is required, not just a manual E2E pass. Scope the new
   `WebTestCase` tests narrowly to error-page rendering (bad-id 404 on a
   real route, truly unrouted URL, forced legacy fatal) rather than
   converting existing controller tests.
7. **Merge bar:** existing unit/controller test suite green, plus the new
   WebTestCase coverage from decision 6, plus the manual fake-session E2E
   checklist in `validation.md`.

## Scope

1. `public/index.php`: branch on `$request->attributes->get('_route')`
   instead of `$response->isNotFound()` to decide Symfony-handles-it vs.
   LegacyBridge-fallback.
2. `LegacyBridge::getLegacyScript()` / `handleRequest()`: convert the
   "can't map this path" case into a branded, logged 404 instead of an
   uncaught `Exception` that escapes to the caller.
3. `LegacyBridge::handleRequest()`: wrap the legacy `require` in
   `try`/`catch (\Throwable)` + `register_shutdown_function`, both paths
   logging via Monolog and rendering the branded 500 (if nothing has been
   output yet).
4. New templates: `templates/bundles/TwigBundle/Exception/error404.html.twig`,
   `error403.html.twig`, `error.html.twig` (generic/500 fallback).
5. `config/packages/monolog.yaml`: `when@prod` `main` handler `level:
   error` → `level: warning`.
6. New `tests/Controller/ErrorHandling*Test.php` (or similar) using
   `WebTestCase`, first functional-test infra in the repo — add whatever
   minimal `phpunit.xml`/bootstrap wiring Symfony's `WebTestCase` needs if
   it isn't already present.
7. Existing unit tests updated wherever they assert on the old
   `isNotFound()`-based branching in `index.php`, if any test currently
   covers that file directly (check during implementation — `index.php`
   is typically untested directly, so this may be a no-op).

## Non-goals

- No noise-filtering/dedicated 4xx channel for Monolog — plain floor
  change only (decision 4).
- No per-status-code custom copy beyond a minimal heading + one line
  (decision 3) — no illustrations, no support-contact links, no retry
  buttons.
- No conversion of existing mocked-render controller tests to
  `WebTestCase` — the new functional-test infra is additive, scoped to
  the error-page paths this phase touches.
- No change to which controller actions throw 404/403 today (the ~20
  `createNotFoundException`/`createAccessDeniedException` call sites are
  unchanged) — this phase only fixes how those exceptions are routed and
  rendered, not where they're raised.
- No retry/circuit-breaker logic for legacy fatals — a fatal still means
  the request fails; the fix is presenting and logging that failure
  correctly, not making the legacy code more resilient.

## Context / gotchas carried forward

- Fake-session E2E recipe needs `fullname` in the session (prior-phase
  gotcha).
- `php -S` manual E2E must be run with `public/index.php` as the router
  script arg, or dotted URL segments 404 before reaching Symfony (see
  memory `gotcha_php_s_dotted_paths`).
- `btn-wmffl` + `text-center` is the established admin button convention —
  not directly relevant here (no admin UI in this phase) but worth keeping
  in mind if any error page ends up with a button/link.
- This is the first phase to introduce `WebTestCase` — check
  `symfony/browser-kit` and `symfony/css-selector` are present in
  `composer.json` (`symfony/test-pack` usually bundles these); add if
  missing.

**Found during implementation, not anticipated above:**
- Kernel-booting tests need `tests/bootstrap.php` to load `.env*` (nothing
  before this phase booted the kernel, so nothing needed it) — see the
  comment in `tests/bootstrap.php` and `phpunit.xml`'s `bootstrap` attribute.
- Kernel-booting tests run under PHP's CLI SAPI, which makes Symfony's
  `error_renderer` service resolve to `CliErrorRenderer` (plain-text dump)
  instead of the HTML/Twig one unless `kernel.runtime_mode.web` is forced
  — `phpunit.xml` sets `APP_RUNTIME_MODE=web=1` for this. Test-suite-only,
  no effect on real `dev`/`prod` traffic.
- Every real matched-route 404 in this app reads from the DB before it can
  404 (`SeasonWeekService` et al.), which would make `ErrorPagesTest.php`
  depend on a provisioned `wmffl_test` database. Sidestepped with two
  dedicated fixture routes (`tests/Fixtures/Controller/
  ErrorFixtureController.php`, wired only `when@test` in
  `config/routes.yaml`) that throw the target exceptions directly — see
  `validation.md`'s Automated section.
- `LegacyBridge`'s own branded-404 (unmappable-path case) always renders
  the branded template regardless of `kernel.debug`, since it calls
  `LegacyErrorPageService::renderErrorPage()` directly rather than going
  through Symfony's `TwigErrorRenderer` — an intentional asymmetry with
  the Symfony-routed 404/403/500 case (decision 3), not a bug. See
  `validation.md` manual E2E #7.
