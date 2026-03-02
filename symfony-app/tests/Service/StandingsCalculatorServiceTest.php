<?php

namespace App\Tests\Service;

use App\Service\StandingsCalculatorService;
use PHPUnit\Framework\TestCase;
use App\Model\Team;

class StandingsCalculatorServiceTest extends TestCase
{
    private StandingsCalculatorService $service;

    protected function setUp(): void
    {
        $this->service = new StandingsCalculatorService();
    }

    // ---- buildTeamArray ----

    public function testBuildTeamArrayCreatesCorrectNumberOfTeams(): void
    {
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        $this->assertCount(2, $teams);
    }

    public function testBuildTeamArraySetsTeamProperties(): void
    {
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        $teamA = $this->findTeam($teams, 1);
        $this->assertSame('Alpha', $teamA->name);
        $this->assertSame('East', $teamA->division);
        $this->assertSame([1, 0, 0], $teamA->divRecord);
        $this->assertSame(100, $teamA->divPtsFor);
        $this->assertSame(80, $teamA->divPtsAgt);
    }

    public function testBuildTeamArrayAddsGameRecords(): void
    {
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        $teamA = $this->findTeam($teams, 1);
        $this->assertSame(1, $teamA->record[0], '1 win');
        $this->assertSame(0, $teamA->record[1], '0 losses');
        $this->assertSame(100, $teamA->ptsFor);
        $this->assertSame(80, $teamA->ptsAgt);

        $teamB = $this->findTeam($teams, 2);
        $this->assertSame(0, $teamB->record[0], '0 wins');
        $this->assertSame(1, $teamB->record[1], '1 loss');
    }

    public function testBuildTeamArrayReturnsIndexedArray(): void
    {
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        // Keys should be 0 and 1, NOT teamids (1 and 2)
        $this->assertArrayHasKey(0, $teams);
        $this->assertArrayHasKey(1, $teams);
        $this->assertArrayNotHasKey(2, $teams);
    }

    public function testBuildTeamArrayPrecomputesSovAsFloat(): void
    {
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        foreach ($teams as $team) {
            $this->assertIsFloat($team->sov);
            $this->assertGreaterThanOrEqual(0.0, $team->sov);
            $this->assertLessThanOrEqual(1.0, $team->sov);
        }
    }

    public function testBuildTeamArraySovIsHigherWhenBeatingStrongerOpponents(): void
    {
        // A (id=1): beat C (strong, 2-1 final record), lost to E
        // B (id=2): beat D (weak, 0-1 final record), lost to F
        // C ends up 2-1 (beat E, beat F, lost to A)
        // D ends up 0-1 (only lost to B)
        // E: beat A, lost to C → 1-1
        // F: beat B, lost to C → 1-1
        [$teamData, $gameData] = $this->sovFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        $teamA = $this->findTeam($teams, 1);
        $teamB = $this->findTeam($teams, 2);

        $this->assertGreaterThan($teamB->sov, $teamA->sov,
            'Team beating a stronger opponent should have higher SOV');
    }

    public function testBuildTeamArraySovIsZeroForTeamWithNoWins(): void
    {
        // Bravo loses its only game — SOV stays 0 (no wins to count opponents for)
        [$teamData, $gameData] = $this->twoTeamFixture();
        $teams = $this->service->buildTeamArray($teamData, $gameData);

        $teamB = $this->findTeam($teams, 2);
        $this->assertSame(0.0, $teamB->sov);
    }

    // ---- precomputeSovs ----

    public function testPrecomputeSovsSetsValueOnPrebuiltTeams(): void
    {
        // Build teams manually (as legacy weekstandings.php does) and call precomputeSovs directly
        $teamA = new Team('Alpha', 'East', 1);
        $teamB = new Team('Bravo', 'East', 2);

        // A beat B
        $teamA->addGame(2, 100, 80, 99);
        $teamB->addGame(1, 80, 100, 99);

        $teamArray = [1 => $teamA, 2 => $teamB];
        $this->service->precomputeSovs($teamArray);

        // A won so has SOV based on B's record (0-1) → 0.0
        $this->assertSame(0.0, $teamA->sov, 'SOV when only win is against 0-1 opponent should be 0.0');
        // B has no wins so SOV stays 0
        $this->assertSame(0.0, $teamB->sov);
    }

    public function testPrecomputeSovsMatchesBuildTeamArraySovResults(): void
    {
        // precomputeSovs() on a manually built array should produce the same SOV
        // as buildTeamArray() for the same data
        [$teamData, $gameData] = $this->sovFixture();

        // Path 1: via buildTeamArray
        $builtTeams = $this->service->buildTeamArray($teamData, $gameData);
        $builtSovs = array_combine(
            array_column($builtTeams, 'teamid'),
            array_column($builtTeams, 'sov')
        );

        // Path 2: manually build equivalent Team objects, then call precomputeSovs
        $manualArray = [];
        foreach ($teamData as $row) {
            $manualArray[$row['teamid']] = new Team($row['team'], $row['division'], $row['teamid']);
        }
        foreach ($gameData as $row) {
            $manualArray[$row['teamid']]->addGame($row['oppid'], $row['ptsfor'], $row['ptsagt'], $row['oppdiv']);
        }
        $this->service->precomputeSovs($manualArray);

        foreach ($manualArray as $id => $team) {
            $this->assertEqualsWithDelta(
                $builtSovs[$id],
                $team->sov,
                0.0001,
                "SOV for team $id should match between buildTeamArray and precomputeSovs paths"
            );
        }
    }

    // ---- sortTeams: division ordering ----

    public function testSortTeamsByDivisionAlphabetically(): void
    {
        $teamA = $this->makeTeam('Alpha', 'AFC', 1, [2, 0, 0]);
        $teamB = $this->makeTeam('Bravo', 'NFC', 2, [2, 0, 0]);

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'AFC should precede NFC');
    }

    // ---- sortTeams: win PCT ----

    public function testSortTeamsByWinPctDescending(): void
    {
        $teamA = $this->makeTeam('Alpha', 'East', 1, [3, 1, 0]); // 0.750
        $teamB = $this->makeTeam('Bravo', 'East', 2, [1, 3, 0]); // 0.250

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0]);
    }

    // ---- sortTeams: H2H wins tiebreaker ----

    public function testSortTeamsByH2HWins(): void
    {
        $teamA = $this->makeTeam('Alpha', 'East', 1, [2, 2, 0]);
        $teamA->games = [[2, 100, 80]]; // beat B

        $teamB = $this->makeTeam('Bravo', 'East', 2, [2, 2, 0]);
        $teamB->games = [[1, 80, 100]]; // lost to A

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'H2H winner should rank higher');
    }

    // ---- sortTeams: division win PCT tiebreaker ----

    public function testSortTeamsByDivWinPct(): void
    {
        // Both 2-2 overall, no H2H games, but different division records
        $teamA = $this->makeTeam('Alpha', 'East', 1, [2, 2, 0]);
        $teamA->divRecord = [2, 0, 0]; // 1.000 div PCT

        $teamB = $this->makeTeam('Bravo', 'East', 2, [2, 2, 0]);
        $teamB->divRecord = [0, 2, 0]; // 0.000 div PCT

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'Higher division win PCT should rank higher');
    }

    // ---- sortTeams: SOV tiebreaker ----

    public function testSortTeamsBySov(): void
    {
        // Manually pre-set sov (normally done by buildTeamArray)
        $teamA = $this->makeTeam('Alpha', 'East', 1, [1, 1, 0]);
        $teamA->sov = 0.600;

        $teamB = $this->makeTeam('Bravo', 'East', 2, [1, 1, 0]);
        $teamB->sov = 0.400;

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'Higher SOV should rank higher');
    }

    // ---- sortTeams: H2H points differential tiebreaker ----

    public function testSortTeamsByH2HPtsDiff(): void
    {
        // A and B split their two H2H games (1 win each), but A has better pts differential
        $teamA = $this->makeTeam('Alpha', 'East', 1, [1, 1, 0]);
        $teamA->games = [
            [2, 120, 80],  // beat B by 40
            [2, 80, 100],  // lost to B by 20
        ]; // net h2h: ptsFor=200, ptsAgt=180 → positive

        $teamB = $this->makeTeam('Bravo', 'East', 2, [1, 1, 0]);
        $teamB->games = [
            [1, 100, 120], // lost to A
            [1, 100, 80],  // beat A
        ];

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'Better H2H points differential should rank higher');
    }

    // ---- sortTeams: alphabetical last-resort tiebreaker ----

    public function testSortTeamsByNameAlphabetically(): void
    {
        $teamA = $this->makeTeam('Aardvark', 'East', 1, [1, 1, 0]);
        $teamB = $this->makeTeam('Zebra', 'East', 2, [1, 1, 0]);

        $teams = [$teamB, $teamA];
        $this->service->sortTeams($teams);

        $this->assertSame($teamA, $teams[0], 'Alphabetically earlier name should rank higher');
    }

    // ---- sortTeams: multi-team division ordering ----

    public function testSortTeamsGroupsAllTeamsByDivision(): void
    {
        $e1 = $this->makeTeam('E1', 'East', 1, [3, 0, 0]);
        $e2 = $this->makeTeam('E2', 'East', 2, [2, 1, 0]);
        $w1 = $this->makeTeam('W1', 'West', 3, [3, 0, 0]);
        $w2 = $this->makeTeam('W2', 'West', 4, [2, 1, 0]);

        $teams = [$w1, $e2, $w2, $e1];
        $this->service->sortTeams($teams);

        // East teams should all come before West teams
        $this->assertSame('East', $teams[0]->division);
        $this->assertSame('East', $teams[1]->division);
        $this->assertSame('West', $teams[2]->division);
        $this->assertSame('West', $teams[3]->division);

        // Within East: 3-0 before 2-1
        $this->assertSame($e1, $teams[0]);
        $this->assertSame($e2, $teams[1]);
    }

    // ---- Helpers ----

    /**
     * Find a team in the array by teamid.
     *
     * @param Team[] $teams
     */
    private function findTeam(array $teams, int $teamid): Team
    {
        foreach ($teams as $team) {
            if ($team->teamid === $teamid) {
                return $team;
            }
        }
        $this->fail("Team with id $teamid not found in array");
    }

    /**
     * Create a Team with its record set directly.
     */
    private function makeTeam(string $name, string $division, int $id, array $record): Team
    {
        $team = new Team($name, $division, $id);
        $team->record = $record;
        return $team;
    }

    /**
     * Minimal two-team fixture: Alpha (id=1) beat Bravo (id=2) 100–80.
     * teamData has SQL-aggregate div stats; gameData has individual game rows.
     */
    private function twoTeamFixture(): array
    {
        $teamData = [
            [
                'team'     => 'Alpha',
                'division' => 'East',
                'teamid'   => 1,
                'divwin'   => 1,
                'divlose'  => 0,
                'divtie'   => 0,
                'divpf'    => 100,
                'divpa'    => 80,
            ],
            [
                'team'     => 'Bravo',
                'division' => 'East',
                'teamid'   => 2,
                'divwin'   => 0,
                'divlose'  => 1,
                'divtie'   => 0,
                'divpf'    => 80,
                'divpa'    => 100,
            ],
        ];

        $gameData = [
            ['teamid' => 1, 'oppid' => 2, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            ['teamid' => 2, 'oppid' => 1, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
        ];

        return [$teamData, $gameData];
    }

    /**
     * Six-team SOV fixture.
     *
     * A (id=1): 1-1 — beat C (strong), lost to E
     * B (id=2): 1-1 — beat D (weak),  lost to F
     * C (id=3): 2-1 — beat E, beat F, lost to A
     * D (id=4): 0-1 — lost to B only
     * E (id=5): 1-1 — beat A, lost to C
     * F (id=6): 1-1 — beat B, lost to C
     *
     * A's SOV = C's record (2-1) applied for the one win = 2/3 ≈ 0.667
     * B's SOV = D's record (0-1) applied for the one win = 0/1 = 0.000
     */
    private function sovFixture(): array
    {
        $teamData = [];
        foreach ([
            [1, 'Alpha', 'East'],
            [2, 'Bravo', 'East'],
            [3, 'Champ', 'West'],
            [4, 'Dregs', 'West'],
            [5, 'Eagle', 'North'],
            [6, 'Falcon', 'South'],
        ] as [$id, $name, $div]) {
            $teamData[] = [
                'team' => $name, 'division' => $div, 'teamid' => $id,
                'divwin' => 0, 'divlose' => 0, 'divtie' => 0, 'divpf' => 0, 'divpa' => 0,
            ];
        }

        $gameData = [
            // A beat C
            ['teamid' => 1, 'oppid' => 3, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            ['teamid' => 3, 'oppid' => 1, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
            // A lost to E
            ['teamid' => 1, 'oppid' => 5, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
            ['teamid' => 5, 'oppid' => 1, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            // B beat D
            ['teamid' => 2, 'oppid' => 4, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            ['teamid' => 4, 'oppid' => 2, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
            // B lost to F
            ['teamid' => 2, 'oppid' => 6, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
            ['teamid' => 6, 'oppid' => 2, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            // C beat E
            ['teamid' => 3, 'oppid' => 5, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            ['teamid' => 5, 'oppid' => 3, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
            // C beat F
            ['teamid' => 3, 'oppid' => 6, 'ptsfor' => 100, 'ptsagt' => 80, 'oppdiv' => 99],
            ['teamid' => 6, 'oppid' => 3, 'ptsfor' => 80,  'ptsagt' => 100, 'oppdiv' => 99],
        ];

        return [$teamData, $gameData];
    }
}
