<?php

namespace App\Tests\Fixtures\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Test-only routes that throw the exceptions ErrorPagesTest needs to
 * trigger, without depending on any real feature or the database — every
 * real matched-route 404 in this app (TeamController, PlayerProfileController,
 * ...) reads from the DB before it can 404, which would make these tests
 * depend on a provisioned wmffl_test database. Registered only for
 * APP_ENV=test via the `when@test` import in config/routes.yaml, and never
 * autoloaded outside dev/test composer installs (autoload-dev only) — see
 * specs/2026-08-18-error-handling/ (Phase 13).
 */
class ErrorFixtureController
{
    #[Route('/_test/throws-404', name: 'test_throws_404')]
    public function throws404(): Response
    {
        throw new NotFoundHttpException('fixture 404');
    }

    #[Route('/_test/throws-403', name: 'test_throws_403')]
    public function throws403(): Response
    {
        throw new AccessDeniedHttpException('fixture 403');
    }
}
