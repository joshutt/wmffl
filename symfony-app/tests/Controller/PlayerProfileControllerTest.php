<?php

namespace App\Tests\Controller;

use App\Controller\PlayerProfileController;
use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class PlayerProfileControllerTest extends TestCase
{
    // ---- GET /players ----

    public function testIndexRendersSearchResultsWithFiltersAndDropdowns(): void
    {
        $controller = $this->makeController();
        $rows = [['id' => 7, 'lastname' => 'Largent', 'firstname' => 'Steve', 'pos' => 'WR', 'nfl_team' => 'SEA', 'retired' => null, 'wmffl_team' => 'Aardvarks']];

        $repo = $this->createMock(PlayerRepository::class);
        $repo->expects($this->once())->method('searchPlayers')
            ->with(
                ['q' => 'larg', 'team' => '3', 'nfl' => 'SEA', 'pos' => 'WR', 'inactive' => true],
                1,
                PlayerProfileController::PER_PAGE
            )
            ->willReturn($rows);
        $repo->method('countPlayers')->willReturn(120);
        $repo->method('getDistinctNflTeams')->willReturn(['GB', 'SEA']);
        $repo->method('getDistinctPositions')->willReturn(['K', 'WR']);

        $request = new Request(query: ['q' => ' larg ', 'team' => '3', 'nfl' => 'SEA', 'pos' => 'WR', 'inactive' => '1', 'page' => '1']);
        $controller->index($request, $repo, $this->makeEntityManager([new Team()]));

        $this->assertSame('player/index.html.twig', $controller->renderedView);
        $params = $controller->renderedParams;
        $this->assertSame($rows, $params['players']);
        $this->assertSame(1, $params['page']);
        $this->assertSame(120, $params['total']);
        $this->assertSame(3, $params['totalPages']);
        $this->assertSame('larg', $params['filters']['q']);
        $this->assertTrue($params['filters']['inactive']);
        $this->assertSame(PlayerRepository::FREE_AGENTS, $params['freeAgentValue']);
        $this->assertCount(1, $params['teams']);
        $this->assertSame(['GB', 'SEA'], $params['nflTeams']);
        $this->assertSame(['K', 'WR'], $params['positions']);
    }

    public function testIndexDefaultsToFirstPageActiveOnlyNoFilters(): void
    {
        $controller = $this->makeController();

        $repo = $this->createMock(PlayerRepository::class);
        $repo->expects($this->once())->method('searchPlayers')
            ->with(
                ['q' => '', 'team' => '', 'nfl' => '', 'pos' => '', 'inactive' => false],
                0,
                PlayerProfileController::PER_PAGE
            )
            ->willReturn([]);
        $repo->method('countPlayers')->willReturn(0);
        $repo->method('getDistinctNflTeams')->willReturn([]);
        $repo->method('getDistinctPositions')->willReturn([]);

        $controller->index(new Request(), $repo, $this->makeEntityManager());

        $this->assertSame(0, $controller->renderedParams['page']);
        $this->assertSame(0, $controller->renderedParams['totalPages']);
        $this->assertSame([], $controller->renderedParams['players']);
    }

    public function testIndexClampsNegativePageToZero(): void
    {
        $controller = $this->makeController();

        $repo = $this->createMock(PlayerRepository::class);
        $repo->expects($this->once())->method('searchPlayers')
            ->with($this->anything(), 0, PlayerProfileController::PER_PAGE)
            ->willReturn([]);
        $repo->method('countPlayers')->willReturn(10);
        $repo->method('getDistinctNflTeams')->willReturn([]);
        $repo->method('getDistinctPositions')->willReturn([]);

        $controller->index(new Request(query: ['page' => '-2']), $repo, $this->makeEntityManager());

        $this->assertSame(0, $controller->renderedParams['page']);
    }

    public function testIndexQueriesActiveTeamsOrderedByNameForDropdown(): void
    {
        $controller = $this->makeController();

        $repo = $this->createStub(PlayerRepository::class);
        $repo->method('searchPlayers')->willReturn([]);
        $repo->method('countPlayers')->willReturn(0);
        $repo->method('getDistinctNflTeams')->willReturn([]);
        $repo->method('getDistinctPositions')->willReturn([]);

        $teamRepo = $this->createMock(EntityRepository::class);
        $teamRepo->expects($this->once())->method('findBy')
            ->with(['active' => true], ['name' => 'ASC'])
            ->willReturn([]);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($teamRepo);

        $controller->index(new Request(), $repo, $em);
    }

    // ---- GET /player/{id} ----

    public function testProfileRendersPlayerWithAllSections(): void
    {
        $controller = $this->makeController();
        $player = $this->makePlayer(7);
        $roster = ['team_name' => 'Aardvarks', 'team_id' => 3, 'date_on' => '2024-09-01', 'date_off' => null];
        $history = [
            ['team_id' => 3, 'date_on' => '2024-09-01', 'date_off' => null, 'team_name' => 'Aardvarks', 'games_activated' => 12, 'active_pts' => 88],
        ];
        $stats = [$this->statRow(2024, ['yards' => 1200, 'tds' => 9])];

        $repo = $this->makeRepo($player, $roster, $history, $stats);

        $controller->profile(7, $repo);

        $this->assertSame('player/profile.html.twig', $controller->renderedView);
        $this->assertSame($player, $controller->renderedParams['player']);
        $this->assertSame($roster, $controller->renderedParams['currentRoster']);
        $this->assertSame($history, $controller->renderedParams['rosterHistory']);
        $this->assertSame($stats, $controller->renderedParams['statsBySeason']);
    }

    public function testProfileThrowsNotFoundForUnknownId(): void
    {
        $controller = $this->makeController();
        $repo = $this->createStub(PlayerRepository::class);
        $repo->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->profile(999, $repo);
    }

    public function testProfileQueriesRosterHistoryAndStatsForThePlayer(): void
    {
        $controller = $this->makeController();
        $repo = $this->createMock(PlayerRepository::class);
        $repo->method('find')->willReturn($this->makePlayer(7));
        $repo->expects($this->once())->method('getCurrentRoster')->with(7)->willReturn(null);
        $repo->expects($this->once())->method('getRosterHistory')->with(7)->willReturn([]);
        $repo->expects($this->once())->method('getStatsBySeason')->with(7)->willReturn([]);

        $controller->profile(7, $repo);
    }

    // ---- activeStatColumns filtering ----

    public function testAllZeroStatColumnsAreHidden(): void
    {
        $controller = $this->makeController();
        $stats = [$this->statRow(2024, ['yards' => 850, 'rec' => 60])];
        $repo = $this->makeRepo($this->makePlayer(7), null, [], $stats);

        $controller->profile(7, $repo);

        $columns = $controller->renderedParams['activeStatColumns'];
        $this->assertSame(['yards' => 'Yards', 'rec' => 'Rec'], $columns);
        $this->assertArrayNotHasKey('tds', $columns);
        $this->assertArrayNotHasKey('fg30', $columns);
    }

    public function testStatColumnShownWhenAnySeasonHasNonZeroValue(): void
    {
        $controller = $this->makeController();
        $stats = [
            $this->statRow(2024, ['yards' => 500]),
            $this->statRow(2023, ['yards' => 0, 'tds' => 4]),
        ];
        $repo = $this->makeRepo($this->makePlayer(7), null, [], $stats);

        $controller->profile(7, $repo);

        $columns = $controller->renderedParams['activeStatColumns'];
        $this->assertArrayHasKey('yards', $columns);
        $this->assertArrayHasKey('tds', $columns);
    }

    public function testKickerColumnsSurviveFiltering(): void
    {
        $controller = $this->makeController();
        $stats = [$this->statRow(2024, ['xp' => 30, 'fg30' => 5, 'fg40' => 3, 'miss_fg30' => 1])];
        $repo = $this->makeRepo($this->makePlayer(7), null, [], $stats);

        $controller->profile(7, $repo);

        $columns = $controller->renderedParams['activeStatColumns'];
        $this->assertSame(
            ['xp' => 'XP Made', 'fg30' => 'FG <30', 'fg40' => 'FG 30-39', 'miss_fg30' => 'FG Miss'],
            $columns
        );
    }

    public function testPlayerWithNoStatsAndNoRosterHistoryRendersCleanly(): void
    {
        $controller = $this->makeController();
        $repo = $this->makeRepo($this->makePlayer(7), null, [], []);

        $controller->profile(7, $repo);

        $this->assertSame('player/profile.html.twig', $controller->renderedView);
        $this->assertNull($controller->renderedParams['currentRoster']);
        $this->assertSame([], $controller->renderedParams['rosterHistory']);
        $this->assertSame([], $controller->renderedParams['statsBySeason']);
        $this->assertSame([], $controller->renderedParams['activeStatColumns']);
    }

    // ---- Helpers ----

    private function makeController(): PlayerProfileController
    {
        return new class extends PlayerProfileController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }
        };
    }

    private function makePlayer(int $id): Player
    {
        $player = new Player();
        $ref = new \ReflectionProperty(Player::class, 'id');
        $ref->setValue($player, $id);
        return $player;
    }

    private function makeRepo(Player $player, ?array $roster, array $history, array $stats): PlayerRepository
    {
        $repo = $this->createStub(PlayerRepository::class);
        $repo->method('find')->willReturn($player);
        $repo->method('getCurrentRoster')->willReturn($roster);
        $repo->method('getRosterHistory')->willReturn($history);
        $repo->method('getStatsBySeason')->willReturn($stats);
        return $repo;
    }

    private function makeEntityManager(array $teams = []): EntityManagerInterface
    {
        $teamRepo = $this->createStub(EntityRepository::class);
        $teamRepo->method('findBy')->willReturn($teams);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($teamRepo);
        return $em;
    }

    /**
     * A season row shaped like PlayerRepository::getStatsBySeason(), with
     * every stat column zero except the given overrides.
     */
    private function statRow(int $season, array $overrides): array
    {
        $row = array_fill_keys([
            'yards', 'tds', 'rec', 'intthrow', 'fum', 'tackles', 'sacks',
            'intcatch', 'passdefend', 'returnyards', 'fumrec', 'forcefum',
            'spec_td', 'safety', 'xp', 'miss_xp', 'fg30', 'fg40', 'fg50',
            'fg60', 'miss_fg30', 'two_pt', 'block_punt', 'block_fg',
            'block_xp', 'penalties',
        ], 0);
        $row['season'] = $season;
        $row['weeks_played'] = 10;
        $row['total_pts'] = 100;
        $row['active_pts'] = 50;

        return array_merge($row, $overrides);
    }
}
