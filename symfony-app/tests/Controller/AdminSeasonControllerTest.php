<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminSeasonController;
use App\Entity\Season;
use App\Repository\SeasonRepository;
use App\Service\AuthenticationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminSeasonControllerTest extends TestCase
{
    public function testNonCommissionerIsRedirected(): void
    {
        $repo = $this->createMock(SeasonRepository::class);
        $repo->expects($this->never())->method('findAllDesc');

        $controller = $this->makeController(commissioner: false);
        $response = $controller->index($this->auth(false), $repo);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testIndexListsSeasonsNewestFirst(): void
    {
        $seasons = [$this->season(2026), $this->season(2025)];
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('findAllDesc')->willReturn($seasons);

        $controller = $this->makeController(commissioner: true);
        $controller->index($this->auth(true), $repo);

        $this->assertSame('admin/seasons/index.html.twig', $controller->renderedView);
        $this->assertSame($seasons, $controller->renderedParams['seasons']);
        $this->assertSame(2026, $controller->renderedParams['latest']->getSeason());
    }

    public function testSaveRoundTripsScoringValueAndTeamBudget(): void
    {
        $season = $this->season(1998);
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('find')->willReturn($season);

        $statements = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements) {
                $statements[] = ['sql' => $sql, 'params' => $params];
                return 1;
            }
        );

        $controller = $this->makeController(commissioner: true);
        $controller->save(1998, Request::create('/admin/seasons/1998', 'POST', [
            '_token' => 'ok',
            'entryFee' => '50',
            'regularSeasonWeeks' => '13',
            'rule_k_fg60' => '10',
            'rule_def_td' => '9',
            'scoringStrategy' => 'standard',
            'notes' => 'FG60 was 10 through 2023',
            'verified' => '1',
            'totalPts' => ['6' => '60'],
        ]), $this->auth(true), $repo, $this->em(), $conn);

        $this->assertSame(10, $season->getScoringRules()['k_fg60']);
        $this->assertSame(9, $season->getScoringRules()['def_td']);
        // Unposted categories are stored as null (not awarded)
        $this->assertNull($season->getScoringRules()['hc_tie']);
        $this->assertSame(50.0, $season->getEntryFee());
        $this->assertSame(13, $season->getRegularSeasonWeeks());
        $this->assertTrue($season->isVerified());
        $this->assertSame('FG60 was 10 through 2023', $season->getNotes());

        $this->assertCount(1, $statements);
        $this->assertStringContainsString('UPDATE transpoints', $statements[0]['sql']);
        $this->assertSame(['pts' => 60, 'teamId' => 6, 'season' => 1998], $statements[0]['params']);

        $this->assertSame(['admin_seasons_edit', ['season' => 1998]], $controller->redirectedTo);
        $this->assertSame('success', $controller->flashed[0]);
    }

    public function testSaveRejectsANonNumericScoringValue(): void
    {
        $season = $this->season(1998);
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('find')->willReturn($season);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $controller = $this->makeController(commissioner: true);
        $controller->save(1998, Request::create('/admin/seasons/1998', 'POST', [
            '_token' => 'ok',
            'rule_k_fg60' => 'ten',
        ]), $this->auth(true), $repo, $em, $this->createStub(Connection::class));

        $this->assertSame('error', $controller->flashed[0]);
        $this->assertSame([], $season->getScoringRules());
    }

    public function testCloneCreatesTheNextSeasonUnverified(): void
    {
        $latest = $this->season(2026)->setVerified(true)->setEntryFee(80.0)
            ->setScoringRules(['k_fg60' => 7])->setNotes('current');
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('findLatest')->willReturn($latest);
        $repo->method('find')->willReturn(null);

        $persisted = null;
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted = $entity;
        });

        $statements = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements) {
                $statements[] = ['sql' => $sql, 'params' => $params];
                return 12;
            }
        );

        $controller = $this->makeController(commissioner: true);
        $controller->clone(
            Request::create('/admin/seasons/clone', 'POST', ['_token' => 'ok']),
            $this->auth(true), $repo, $em, $conn
        );

        $this->assertInstanceOf(Season::class, $persisted);
        $this->assertSame(2027, $persisted->getSeason());
        $this->assertFalse($persisted->isVerified());
        $this->assertNull($persisted->getNotes());
        $this->assertSame(80.0, $persisted->getEntryFee());
        $this->assertSame(['k_fg60' => 7], $persisted->getScoringRules());

        $this->assertStringContainsString('INSERT INTO transpoints', $statements[0]['sql']);
        $this->assertSame(['next' => 2027, 'latest' => 2026], $statements[0]['params']);
        $this->assertSame(['admin_seasons_edit', ['season' => 2027]], $controller->redirectedTo);
    }

    public function testCloneRefusesAnExistingSeason(): void
    {
        $latest = $this->season(2026);
        $repo = $this->createStub(SeasonRepository::class);
        $repo->method('findLatest')->willReturn($latest);
        $repo->method('find')->willReturn($this->season(2027));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $controller = $this->makeController(commissioner: true);
        $controller->clone(
            Request::create('/admin/seasons/clone', 'POST', ['_token' => 'ok']),
            $this->auth(true), $repo, $em, $this->createStub(Connection::class)
        );

        $this->assertSame('error', $controller->flashed[0]);
        $this->assertSame(['admin_seasons', []], $controller->redirectedTo);
    }

    // ---- helpers ----

    private function season(int $year): Season
    {
        return (new Season())->setSeason($year);
    }

    private function auth(bool $commissioner): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        return $auth;
    }

    private function em(): EntityManagerInterface
    {
        return $this->createStub(EntityManagerInterface::class);
    }

    private function makeController(bool $commissioner, bool $csrfValid = true): AdminSeasonController
    {
        return new class($csrfValid) extends AdminSeasonController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectedTo = null;
            public ?array $flashed = null;

            public function __construct(private readonly bool $csrfValid)
            {
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return $response ?? new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectedTo = [$route, $parameters];
                return new RedirectResponse('/stub', $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashed = [$type, $message];
            }

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }
        };
    }
}
