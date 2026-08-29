<?php

namespace App\Tests\Service;

use App\Repository\DraftPickRepository;
use App\Service\DraftResultsService;
use App\Service\SeasonWeekService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DraftResultsServiceTest extends TestCase
{
    // ---- resolveDefaultYear ----

    public function testResolveDefaultYearUsesCurrentSeasonWhenItHasSelections(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithSelections')->willReturn([2006, 2025, 2026]);

        $this->assertSame(2026, $this->makeService($repo, currentSeason: 2026)->resolveDefaultYear());
    }

    public function testResolveDefaultYearFallsBackToMostRecentSeasonWithSelections(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithSelections')->willReturn([2006, 2024, 2025]);

        $this->assertSame(2025, $this->makeService($repo, currentSeason: 2026)->resolveDefaultYear());
    }

    public function testResolveDefaultYearNeverReturnsAFutureYearEvenWhenDraftpicksHoldsLaterSeasons(): void
    {
        // getSeasonsWithSelections would only ever include drafted seasons,
        // but a season later than current still must not win even if it
        // somehow had selections recorded against it.
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithSelections')->willReturn([2024, 2025, 2027]);

        $this->assertSame(2025, $this->makeService($repo, currentSeason: 2026)->resolveDefaultYear());
    }

    // ---- isReachable ----

    public function testIsReachableFalseForAFutureSeason(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026, 2027, 2028, 2029]);

        $this->assertFalse($this->makeService($repo, currentSeason: 2026)->isReachable(2027));
    }

    public function testIsReachableFalseForAYearThatNeverExisted(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026]);

        $this->assertFalse($this->makeService($repo, currentSeason: 2026)->isReachable(2050));
    }

    public function testIsReachableTrueForTheCurrentSeason(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026]);

        $this->assertTrue($this->makeService($repo, currentSeason: 2026)->isReachable(2026));
    }

    public function testIsReachableTrueForAPastSkeletonOnlySeason(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026]);

        $this->assertTrue($this->makeService($repo, currentSeason: 2026)->isReachable(2005));
    }

    // ---- getBoard: filter normalization ----

    public function testGetBoardNormalizesAllAndEmptyAndAbsentToNull(): void
    {
        $repo = $this->createMock(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2019]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->expects($this->once())->method('getBoard')
            ->with(2019, ['round' => null, 'pick' => null, 'team' => null, 'pos' => null, 'nfl' => null], null)
            ->willReturn([]);

        $this->makeService($repo, currentSeason: 2026)->getBoard(2019, [
            'round' => 'ALL', 'pick' => '', 'team' => null, 'pos' => 'ALL', 'nfl' => '',
        ]);
    }

    public function testGetBoardCastsRoundAndPickToInt(): void
    {
        $repo = $this->createMock(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2019]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->expects($this->once())->method('getBoard')
            ->with(2019, $this->callback(fn ($filters) => $filters['round'] === 3 && $filters['pick'] === 7), null)
            ->willReturn([]);

        $this->makeService($repo, currentSeason: 2026)->getBoard(2019, ['round' => '3', 'pick' => '7']);
    }

    public function testGetBoardKeepsTeamPosNflAsStrings(): void
    {
        $repo = $this->createMock(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2019]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->expects($this->once())->method('getBoard')
            ->with(2019, $this->callback(fn ($filters) => $filters['team'] === '5' && $filters['pos'] === 'OL' && $filters['nfl'] === 'SEA'), null)
            ->willReturn([]);

        $this->makeService($repo, currentSeason: 2026)->getBoard(2019, ['team' => '5', 'pos' => 'OL', 'nfl' => 'SEA']);
    }

    // ---- getBoard: prev/next ----

    public function testGetBoardPrevAndNextYearAreNullAtEachEndOfTheRange(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getBoard')->willReturn([]);
        $repo->method('getFilterOptions')->willReturn([]);

        $service = $this->makeService($repo, currentSeason: 2026);

        $first = $service->getBoard(2005, []);
        $this->assertNull($first['prevYear']);
        $this->assertSame(2006, $first['nextYear']);

        $last = $service->getBoard(2026, []);
        $this->assertSame(2006, $last['prevYear']);
        $this->assertNull($last['nextYear']);
    }

    public function testGetBoardPrevAndNextExcludeFutureSeasonsFromTheRange(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        // draftpicks holds 2027-2029 skeletons too, but the current season
        // (2026) is the upper bound — 2026's board must show no "next".
        $repo->method('getSeasonsWithPicks')->willReturn([2005, 2006, 2026, 2027, 2028, 2029]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getBoard')->willReturn([]);
        $repo->method('getFilterOptions')->willReturn([]);

        $board = $this->makeService($repo, currentSeason: 2026)->getBoard(2026, []);

        $this->assertNull($board['nextYear']);
    }

    // ---- getBoard: row shaping / fromFranchise ----

    public function testGetBoardRowFromFranchiseIsNullOnAnUntradedPick(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2019]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->method('getBoard')->willReturn([$this->row(['teamid' => 5, 'orgteam' => 5, 'orgteamname' => 'Aardvarks'])]);

        $board = $this->makeService($repo, currentSeason: 2026)->getBoard(2019, []);

        $this->assertNull($board['rows'][0]['fromFranchise']);
    }

    public function testGetBoardRowFromFranchiseIsTheOriginalFranchiseNameOnATradedPick(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2007]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->method('getBoard')->willReturn([$this->row([
            'teamid' => 1, 'team' => 'Gallic Warriors',
            'orgteam' => 9, 'orgteamname' => 'Pretend I\'m Not Here',
        ])]);

        $board = $this->makeService($repo, currentSeason: 2026)->getBoard(2007, []);

        $this->assertSame('Gallic Warriors', $board['rows'][0]['team']);
        $this->assertSame("Pretend I'm Not Here", $board['rows'][0]['fromFranchise']);
    }

    public function testGetBoardRowSelectionIsNullWhenUndrafted(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2006]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->method('getBoard')->willReturn([$this->row(['firstname' => null, 'lastname' => null, 'pos' => null, 'nflteamid' => null])]);

        $board = $this->makeService($repo, currentSeason: 2026)->getBoard(2006, []);

        $this->assertNull($board['rows'][0]['selection']);
        $this->assertNull($board['rows'][0]['pos']);
        $this->assertNull($board['rows'][0]['nfl']);
    }

    public function testGetBoardRowPickIsNullForASkeletonRow(): void
    {
        $repo = $this->createStub(DraftPickRepository::class);
        $repo->method('getSeasonsWithPicks')->willReturn([2027]);
        $repo->method('getAsOfDate')->willReturn(null);
        $repo->method('getFilterOptions')->willReturn([]);
        $repo->method('getBoard')->willReturn([$this->row(['pick' => null])]);

        // 2027 itself would 404 through the controller, but the repository/
        // service layer can still be exercised directly, per validation.md.
        $board = $this->makeService($repo, currentSeason: 2027)->getBoard(2027, []);

        $this->assertNull($board['rows'][0]['pick']);
    }

    // ---- Helpers ----

    private function row(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'round' => 1,
            'pick' => 1,
            'teamid' => 5,
            'team' => 'Aardvarks',
            'orgteam' => 5,
            'orgteamname' => 'Aardvarks',
            'firstname' => 'Steve',
            'lastname' => 'Largent',
            'pos' => 'WR',
            'nflteamid' => 'SEA',
        ], $overrides);
    }

    private function makeService(DraftPickRepository $repo, int $currentSeason): DraftResultsService
    {
        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn($currentSeason);

        return new DraftResultsService($repo, $seasonWeek);
    }
}
