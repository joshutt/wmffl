<?php

namespace App\Tests\Controller\Admin;

use App\Controller\Admin\AdminDraftResultsController;
use App\Entity\DraftPick;
use App\Entity\Player;
use App\Repository\DraftPickRepository;
use App\Repository\PlayerRepository;
use App\Service\AuthenticationService;
use App\Service\DraftResultsService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminDraftResultsControllerTest extends TestCase
{
    // ---- GET /admin/draftresults/{year} ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        $controller = $this->makeController();

        $response = $controller->index(
            new Request(),
            $this->makeAuth(false),
            $this->createStub(DraftResultsService::class),
            $this->createStub(PlayerRepository::class)
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexThrowsNotFoundForAFutureSeason(): void
    {
        $controller = $this->makeController();
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $controller->index(new Request(), $this->makeAuth(true), $draftResults, $this->createStub(PlayerRepository::class), 2027);
    }

    public function testIndexResolvesDefaultYearWhenNoneGiven(): void
    {
        $controller = $this->makeController();
        $draftResults = $this->createMock(DraftResultsService::class);
        $draftResults->expects($this->once())->method('resolveDefaultYear')->willReturn(2026);
        $draftResults->method('isReachable')->with(2026)->willReturn(true);
        $draftResults->method('getBoard')->with(2026, [])->willReturn(['rows' => []]);

        $controller->index(new Request(), $this->makeAuth(true), $draftResults, $this->createStub(PlayerRepository::class));

        $this->assertSame(2026, $controller->renderedParams['year']);
    }

    public function testIndexRendersTheSeasonsBoard(): void
    {
        $controller = $this->makeController();
        $rows = [['id' => 1, 'round' => 1, 'pick' => 1, 'selection' => 'Steve Largent']];
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);
        $draftResults->method('getBoard')->willReturn(['rows' => $rows]);

        $controller->index(new Request(), $this->makeAuth(true), $draftResults, $this->createStub(PlayerRepository::class), 2019);

        $this->assertSame('admin/draftresults/index.html.twig', $controller->renderedView);
        $this->assertSame($rows, $controller->renderedParams['rows']);
    }

    public function testIndexSearchesForAPlayerOnlyWhenEditingAPickWithANonEmptyQuery(): void
    {
        $controller = $this->makeController();
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);
        $draftResults->method('getBoard')->willReturn(['rows' => []]);

        $players = $this->createMock(PlayerRepository::class);
        $players->expects($this->never())->method('searchPlayers');

        // No editPick query param -> no search, even with a q present
        $controller->index(new Request(query: ['q' => 'largent']), $this->makeAuth(true), $draftResults, $players, 2019);
    }

    public function testIndexSearchesForAPlayerWhenEditingAPick(): void
    {
        $controller = $this->makeController();
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);
        $draftResults->method('getBoard')->willReturn(['rows' => []]);

        $results = [['id' => 7, 'firstname' => 'Steve', 'lastname' => 'Largent']];
        $players = $this->createMock(PlayerRepository::class);
        $players->expects($this->once())->method('searchPlayers')
            ->with(['q' => 'largent'], 0, AdminDraftResultsController::MAX_RESULTS)
            ->willReturn($results);

        $controller->index(new Request(query: ['editPick' => '42', 'q' => 'largent']), $this->makeAuth(true), $draftResults, $players, 2019);

        $this->assertSame($results, $controller->renderedParams['searchResults']);
        $this->assertSame(42, $controller->renderedParams['editPickId']);
    }

    // ---- POST /admin/draftresults/{year}/pick/{id} ----

    public function testSetPickRedirectsWhenNotCommissioner(): void
    {
        $controller = $this->makeController();

        $response = $controller->setPick(
            2019,
            1,
            new Request(),
            $this->makeAuth(false),
            $this->createStub(DraftResultsService::class),
            $this->createStub(DraftPickRepository::class),
            $this->createStub(PlayerRepository::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testSetPickRejectsInvalidCsrfToken(): void
    {
        $controller = $this->makeController();
        $controller->csrfValid = false;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->setPick(
            2019,
            1,
            Request::create('/admin/draftresults/2019/pick/1', 'POST'),
            $this->makeAuth(true),
            $this->createStub(DraftResultsService::class),
            $this->createStub(DraftPickRepository::class),
            $this->createStub(PlayerRepository::class),
            $em
        );
    }

    public function testSetPickThrowsNotFoundWhenThePickDoesNotExist(): void
    {
        $controller = $this->makeController();
        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->setPick(
            2019,
            999,
            Request::create('/admin/draftresults/2019/pick/999', 'POST'),
            $this->makeAuth(true),
            $this->createStub(DraftResultsService::class),
            $draftPicks,
            $this->createStub(PlayerRepository::class),
            $this->createStub(EntityManagerInterface::class)
        );
    }

    public function testSetPickThrowsNotFoundWhenThePicksOwnSeasonIsUnreachable(): void
    {
        // A hand-crafted POST could put a reachable {year} in the path
        // while {id} actually belongs to a future-season pick; the cut-off
        // must be checked against the pick's own season, not the path.
        $controller = $this->makeController();
        $pick = $this->makePick(season: 2027);
        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn($pick);

        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(false);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $this->expectException(NotFoundHttpException::class);
        $controller->setPick(
            2026,
            1,
            Request::create('/admin/draftresults/2026/pick/1', 'POST'),
            $this->makeAuth(true),
            $draftResults,
            $draftPicks,
            $this->createStub(PlayerRepository::class),
            $em
        );
    }

    public function testSetPickWithEmptyPlayerIdClearsTheSelection(): void
    {
        $controller = $this->makeController();
        $pick = $this->makePick();
        $pick->setPlayer($this->makePlayer(7, 'Steve', 'Largent'));

        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn($pick);
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $response = $controller->setPick(
            2019,
            1,
            Request::create('/admin/draftresults/2019/pick/1', 'POST', ['playerId' => '']),
            $this->makeAuth(true),
            $draftResults,
            $draftPicks,
            $this->createStub(PlayerRepository::class),
            $em
        );

        $this->assertNull($pick->getPlayer());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame([['type' => 'success', 'message' => 'Cleared Round 1, Pick 1']], $controller->flashes);
    }

    public function testSetPickWithAPlayerIdPersistsTheSelection(): void
    {
        $controller = $this->makeController();
        $pick = $this->makePick();
        $player = $this->makePlayer(7, 'Steve', 'Largent');

        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn($pick);
        $draftPicks->method('isPlayerAlreadyDrafted')->willReturn(false);
        $players = $this->createMock(PlayerRepository::class);
        $players->expects($this->once())->method('find')->with(7)->willReturn($player);
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $controller->setPick(
            2019,
            1,
            Request::create('/admin/draftresults/2019/pick/1', 'POST', ['playerId' => '7']),
            $this->makeAuth(true),
            $draftResults,
            $draftPicks,
            $players,
            $em
        );

        $this->assertSame($player, $pick->getPlayer());
        $this->assertSame([['type' => 'success', 'message' => 'Round 1, Pick 1 set to Steve Largent']], $controller->flashes);
    }

    public function testSetPickRejectsAPlayerAlreadyDraftedThisSeason(): void
    {
        $controller = $this->makeController();
        $pick = $this->makePick();
        $player = $this->makePlayer(7, 'Steve', 'Largent');

        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn($pick);
        $draftPicks->method('isPlayerAlreadyDrafted')->willReturn(true);
        $players = $this->createStub(PlayerRepository::class);
        $players->method('find')->willReturn($player);
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $controller->setPick(
            2019,
            1,
            Request::create('/admin/draftresults/2019/pick/1', 'POST', ['playerId' => '7']),
            $this->makeAuth(true),
            $draftResults,
            $draftPicks,
            $players,
            $em
        );

        $this->assertNull($pick->getPlayer());
        $this->assertSame(
            [['type' => 'error', 'message' => 'Steve Largent was already drafted this season']],
            $controller->flashes
        );
    }

    public function testSetPickWithUnknownPlayerIdShowsAnErrorAndDoesNotPersist(): void
    {
        $controller = $this->makeController();
        $pick = $this->makePick();

        $draftPicks = $this->createStub(DraftPickRepository::class);
        $draftPicks->method('find')->willReturn($pick);
        $players = $this->createStub(PlayerRepository::class);
        $players->method('find')->willReturn(null);
        $draftResults = $this->createStub(DraftResultsService::class);
        $draftResults->method('isReachable')->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $controller->setPick(
            2019,
            1,
            Request::create('/admin/draftresults/2019/pick/1', 'POST', ['playerId' => '999']),
            $this->makeAuth(true),
            $draftResults,
            $draftPicks,
            $players,
            $em
        );

        $this->assertSame([['type' => 'error', 'message' => 'No such player']], $controller->flashes);
    }

    // ---- Helpers ----

    private function makeController(): AdminDraftResultsController
    {
        return new class extends AdminDraftResultsController {
            public bool $csrfValid = true;
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public array $flashes = [];

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }

            public function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = ['type' => $type, 'message' => $message];
            }
        };
    }

    private function makeAuth(bool $commissioner): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);
        return $auth;
    }

    private function makePick(int $season = 2019, int $round = 1, int $pick = 1): DraftPick
    {
        $draftPick = new DraftPick();
        $draftPick->setSeason($season);
        $draftPick->setRound($round);
        $draftPick->setPick($pick);
        $ref = new \ReflectionProperty(DraftPick::class, 'id');
        $ref->setValue($draftPick, 1);
        return $draftPick;
    }

    private function makePlayer(int $id, string $firstname, string $lastname): Player
    {
        $player = new Player();
        $player->setFirstname($firstname);
        $player->setLastname($lastname);
        $ref = new \ReflectionProperty(Player::class, 'id');
        $ref->setValue($player, $id);
        return $player;
    }
}
