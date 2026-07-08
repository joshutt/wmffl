<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminPlayerController;
use App\Entity\Player;
use App\Enum\PosEnum;
use App\Repository\PlayerRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminPlayerControllerTest extends TestCase
{
    // ---- GET /admin/players ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        $controller = $this->makeController();

        $response = $controller->index(new Request(), $this->makeAuth(false), $this->createStub(PlayerRepository::class));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexWithoutQueryPromptsInsteadOfDumpingAllPlayers(): void
    {
        $controller = $this->makeController();

        $repo = $this->createMock(PlayerRepository::class);
        $repo->expects($this->never())->method('searchPlayers');

        $controller->index(new Request(), $this->makeAuth(true), $repo);

        $this->assertSame('admin/player/index.html.twig', $controller->renderedView);
        $this->assertNull($controller->renderedParams['players']);
    }

    public function testIndexSearchesByPartialNameIncludingRetiredPlayers(): void
    {
        $controller = $this->makeController();
        $rows = [['id' => 7, 'lastname' => 'Largent', 'firstname' => 'Steve', 'pos' => 'WR', 'nfl_team' => 'SEA', 'retired' => 1989, 'wmffl_team' => null]];

        $repo = $this->createMock(PlayerRepository::class);
        $repo->expects($this->once())->method('searchPlayers')
            ->with(['q' => 'larg', 'inactive' => true], 0, AdminPlayerController::MAX_RESULTS)
            ->willReturn($rows);

        $controller->index(new Request(query: ['q' => ' larg ']), $this->makeAuth(true), $repo);

        $this->assertSame($rows, $controller->renderedParams['players']);
        $this->assertSame('larg', $controller->renderedParams['q']);
    }

    // ---- GET /admin/players/{id}/edit ----

    public function testEditRedirectsWhenNotCommissioner(): void
    {
        $controller = $this->makeController();

        $response = $controller->edit(
            7,
            new Request(),
            $this->makeAuth(false),
            $this->createStub(PlayerRepository::class),
            $this->createStub(EntityManagerInterface::class)
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testEditThrowsNotFoundForUnknownId(): void
    {
        $controller = $this->makeController();
        $repo = $this->createStub(PlayerRepository::class);
        $repo->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->edit(999, new Request(), $this->makeAuth(true), $repo, $this->createStub(EntityManagerInterface::class));
    }

    public function testEditGetRendersFormPrefilledWithThePlayer(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer();

        $controller->edit(7, new Request(), $this->makeAuth(true), $this->makeRepo($player), $this->createStub(EntityManagerInterface::class));

        $this->assertSame('admin/player/edit.html.twig', $controller->renderedView);
        $this->assertSame($player, $controller->renderedParams['player']);
        $this->assertSame(PosEnum::cases(), $controller->renderedParams['positions']);
    }

    // ---- POST /admin/players/{id}/edit ----

    public function testEditPostPersistsAllFieldsAndRedirectsToSearch(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $request = Request::create('/admin/players/7/edit', 'POST', [
            'lastname' => ' Largent ',
            'firstname' => 'Steve',
            'pos' => 'WR',
            'team' => 'SEA',
            'number' => '80',
            'retired' => '1989',
            'height' => '71',
            'weight' => '191',
            'dob' => '1954-09-28',
            'draftTeam' => 'HOU',
            'draftYear' => '1976',
            'active' => '1',
            'usePos' => '1',
        ]);
        $response = $controller->edit(7, $request, $this->makeAuth(true), $this->makeRepo($player), $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_players', $response->getTargetUrl());
        $this->assertSame(['q' => 'Largent'], $controller->redirectParams);
        $this->assertSame([['type' => 'success', 'message' => 'Player updated']], $controller->flashes);

        $this->assertSame('Largent', $player->getLastname());
        $this->assertSame('Steve', $player->getFirstname());
        $this->assertSame(PosEnum::WR, $player->getPos());
        $this->assertSame('SEA', $player->getTeam());
        $this->assertSame(80, $player->getNumber());
        $this->assertSame(1989, $player->getRetired());
        $this->assertSame(71, $player->getHeight());
        $this->assertSame(191, $player->getWeight());
        $this->assertSame('1954-09-28', $player->getDob()->format('Y-m-d'));
        $this->assertSame('HOU', $player->getDraftTeam());
        $this->assertSame(1976, $player->getDraftYear());
        $this->assertTrue($player->isActive());
        $this->assertTrue($player->isUsePos());
    }

    public function testEditPostBlankOptionalFieldsPersistAsNull(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer();
        $player->setFirstname('Steve')->setTeam('SEA')->setNumber(80)->setRetired(1989)
            ->setHeight(71)->setWeight(191)->setDob(new \DateTime('1954-09-28'))
            ->setDraftTeam('HOU')->setDraftYear(1976)->setPos(PosEnum::WR)
            ->setActive(true)->setUsePos(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $request = Request::create('/admin/players/7/edit', 'POST', [
            'lastname' => 'Largent',
            'firstname' => '',
            'pos' => '',
            'team' => '',
            'number' => '',
            'retired' => '',
            'height' => '',
            'weight' => '',
            'dob' => '',
            'draftTeam' => '',
            'draftYear' => '',
        ]);
        $controller->edit(7, $request, $this->makeAuth(true), $this->makeRepo($player), $em);

        $this->assertNull($player->getFirstname());
        $this->assertNull($player->getPos());
        $this->assertNull($player->getTeam());
        $this->assertNull($player->getNumber());
        $this->assertNull($player->getRetired());
        $this->assertNull($player->getHeight());
        $this->assertNull($player->getWeight());
        $this->assertNull($player->getDob());
        $this->assertNull($player->getDraftTeam());
        $this->assertNull($player->getDraftYear());
        // checkboxes absent from the POST mean unchecked
        $this->assertFalse($player->isActive());
        $this->assertFalse($player->isUsePos());
    }

    public function testEditPostEmptyLastnameRerendersWithErrorAndPersistsNothing(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/players/7/edit', 'POST', ['lastname' => '   ', 'firstname' => 'Steve']);
        $controller->edit(7, $request, $this->makeAuth(true), $this->makeRepo($player), $em);

        $this->assertSame('admin/player/edit.html.twig', $controller->renderedView);
        $this->assertSame([['type' => 'error', 'message' => 'A last name is required']], $controller->flashes);
        $this->assertSame('Largent', $player->getLastname());
        $this->assertNull($player->getFirstname());
    }

    public function testEditPostInvalidDobRerendersWithErrorAndPersistsNothing(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/players/7/edit', 'POST', ['lastname' => 'Largent', 'dob' => 'not-a-date']);
        $controller->edit(7, $request, $this->makeAuth(true), $this->makeRepo($player), $em);

        $this->assertSame('admin/player/edit.html.twig', $controller->renderedView);
        $this->assertSame([['type' => 'error', 'message' => 'Date of birth must be a valid date']], $controller->flashes);
    }

    public function testEditPostRejectsInvalidCsrfToken(): void
    {
        $controller = $this->makeController();
        $controller->csrfValid = false;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/players/7/edit', 'POST', ['lastname' => 'Largent']);

        $this->expectException(AccessDeniedHttpException::class);
        $controller->edit(7, $request, $this->makeAuth(true), $this->makeRepo($this->makePlayer()), $em);
    }

    // ---- Helpers ----

    private function makeController(): AdminPlayerController
    {
        return new class extends AdminPlayerController {
            public bool $csrfValid = true;
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public ?array $redirectParams = null;
            public array $flashes = [];

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                $this->redirectParams = $parameters;
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

    private function makePlayer(): Player
    {
        $player = new Player();
        $player->setLastname('Largent');
        $ref = new \ReflectionProperty(Player::class, 'id');
        $ref->setValue($player, 7);
        return $player;
    }

    private function makeRepo(Player $player): PlayerRepository
    {
        $repo = $this->createStub(PlayerRepository::class);
        $repo->method('find')->willReturn($player);
        return $repo;
    }
}
