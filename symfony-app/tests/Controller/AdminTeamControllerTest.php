<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminTeamController;
use App\Entity\Division;
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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminTeamControllerTest extends TestCase
{
    // ---- GET /admin/teams ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexListsAllTeamsWithDivisionNames(): void
    {
        $teams = [$this->makeTeam()];
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, teams: $teams);

        $controller->index($auth, $seasonWeek, $em);

        $this->assertSame('admin/team/index.html.twig', $controller->renderedView);
        $this->assertSame($teams, $controller->renderedParams['teams']);
        $this->assertSame([2 => 'Gold Division'], $controller->renderedParams['divisionNames']);
    }

    // ---- GET/POST /admin/teams/{id}/edit ----

    public function testEditRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->edit(2, new Request(), $auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testEditUnknownTeam404s(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, findTeam: null);

        $this->expectException(NotFoundHttpException::class);
        $controller->edit(999, new Request(), $auth, $seasonWeek, $em);
    }

    public function testEditGetRendersThePrefilledForm(): void
    {
        $team = $this->makeTeam();
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, findTeam: $team);

        $controller->edit(2, new Request(), $auth, $seasonWeek, $em);

        $this->assertSame('admin/team/edit.html.twig', $controller->renderedView);
        $this->assertSame($team, $controller->renderedParams['team']);
        $this->assertSame([2 => 'Gold Division'], $controller->renderedParams['divisionNames']);
    }

    public function testEditPostPersistsFieldsAndRedirectsWithFlash(): void
    {
        $team = $this->makeTeam();
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, findTeam: $team);
        $em->expects($this->once())->method('flush');

        $request = new Request(request: [
            'abbrev' => 'ZZ', 'motto' => ' We win ', 'logo' => 'zz.jpg',
            'fulllogo' => '1', 'active' => '1', 'division' => '3',
        ]);
        $request->setMethod('POST');

        $response = $controller->edit(2, $request, $auth, $seasonWeek, $em);

        $this->assertSame('ZZ', $team->getAbbreviation());
        $this->assertSame('We win', $team->getMotto());
        $this->assertSame('zz.jpg', $team->getLogo());
        $this->assertTrue($team->isFullLogo());
        $this->assertTrue($team->isActive());
        $this->assertSame(3, $team->getDivision());
        $this->assertSame(['success', 'Team updated'], $controller->flashed);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_teams', $response->getTargetUrl());
    }

    public function testEditPostEmptyMottoAndLogoStoredAsNullAndUncheckedFlagsCleared(): void
    {
        $team = $this->makeTeam();
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, findTeam: $team);

        $request = new Request(request: ['abbrev' => 'AE', 'motto' => '', 'logo' => '', 'division' => '2']);
        $request->setMethod('POST');

        $controller->edit(2, $request, $auth, $seasonWeek, $em);

        $this->assertNull($team->getMotto());
        $this->assertNull($team->getLogo());
        $this->assertFalse($team->isFullLogo());
        $this->assertFalse($team->isActive());
    }

    public function testEditPostRejectsInvalidCsrfToken(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true, findTeam: $this->makeTeam());
        $controller->csrfValid = false;

        $request = new Request(request: ['abbrev' => 'AE']);
        $request->setMethod('POST');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->edit(2, $request, $auth, $seasonWeek, $em);
    }

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

    private function makeTeam(): Team
    {
        $team = new Team();
        $team->setId(2);
        $team->setName('Amish Electricians');
        $team->setAbbreviation('AE');
        $team->setDivision(2);
        $team->setActive(false);
        $team->setFullLogo(false);
        $team->setLogo(null);

        return $team;
    }

    /**
     * @param Team|null|false $findTeam what $em->find() returns; false keeps
     *                                  the legacy default stub team
     * @param Team[]|null     $teams    what the Team repository findBy returns
     */
    private function makeController(bool $commissioner, Team|null|false $findTeam = false, ?array $teams = null): array
    {
        $controller = new class extends AdminTeamController {
            public bool $csrfValid = true;

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

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

            public ?array $flashed = null;

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashed = [$type, $message];
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
        $repo->method('findBy')->willReturn($teams ?? [$teamName]);

        $division = new Division();
        $division->setId(2);
        $division->setName('Gold Division');

        $query = $this->createStub(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getResult')->willReturn([$division]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $em->method('createQuery')->willReturn($query);
        $em->method('find')->willReturn($findTeam === false ? $team : $findTeam);

        return [$controller, $auth, $seasonWeek, $em];
    }
}
