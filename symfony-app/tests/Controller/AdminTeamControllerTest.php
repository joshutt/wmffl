<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminTeamController;
use App\Entity\Team;
use App\Entity\TeamNames;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminTeamControllerTest extends TestCase
{
    // ---- GET /admin/team/updateTeamInfo ----

    public function testUpdateTeamInfoRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->updateTeamInfo($auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testUpdateTeamInfoRendersCorrectTemplateForCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateTeamInfo($auth, $seasonWeek, $em);

        $this->assertSame('admin/team/updateTeamInfo.html.twig', $controller->renderedView);
    }

    public function testUpdateTeamInfoPassesSeasonToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateTeamInfo($auth, $seasonWeek, $em);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testUpdateTeamInfoPassesRowsAndDivisionsToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateTeamInfo($auth, $seasonWeek, $em);

        $this->assertArrayHasKey('rows', $controller->renderedParams);
        $this->assertArrayHasKey('divisions', $controller->renderedParams);
    }

    // ---- POST /admin/team/processUpdateTeam ----

    public function testProcessUpdateTeamRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->processUpdateTeam(new Request(), $auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testProcessUpdateTeamRedirectsToUpdateFormAfterSave(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $response = $controller->processUpdateTeam(new Request(), $auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_team_update', $response->getTargetUrl());
    }

    public function testProcessUpdateTeamFlushesChanges(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $em->expects($this->once())->method('flush');

        $controller->processUpdateTeam(new Request(), $auth, $seasonWeek, $em);
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner): array
    {
        $controller = new class extends AdminTeamController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $teamName = $this->createStub(TeamNames::class);
        $teamName->method('getTeamId')->willReturn(1);
        $teamName->method('getName')->willReturn('Test Team');
        $teamName->method('getAbbrev')->willReturn('TT');
        $teamName->method('getDivisionId')->willReturn(1);

        $team = $this->createStub(Team::class);
        $team->method('getLogo')->willReturn('');
        $team->method('isFullLogo')->willReturn(false);

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findBy')->willReturn([$teamName]);

        $query = $this->createStub(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $em->method('createQuery')->willReturn($query);
        $em->method('find')->willReturn($team);

        return [$controller, $auth, $seasonWeek, $em];
    }
}
