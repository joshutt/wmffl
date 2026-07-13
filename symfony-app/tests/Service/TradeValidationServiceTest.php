<?php

namespace App\Tests\Service;

use App\Repository\TradeOfferRepository;
use App\Service\SeasonWeekService;
use App\Service\TradeValidationService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TradeValidationServiceTest extends TestCase
{
    private const YOU = 2;
    private const THEY = 5;

    public function testValidTradeResolvesFullTermDetails(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, [
            'players' => [50],
            'picks' => [9],
            'points' => [2026 => 5],
        ], [
            'players' => [60],
        ]);

        $this->assertSame([], $result['errors']);
        $this->assertSame('Al Kaline', $result['terms'][self::YOU]['players'][0]['name']);
        $this->assertSame(
            ['id' => 9, 'season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs'],
            $result['terms'][self::YOU]['picks'][0]
        );
        $this->assertSame([['season' => 2026, 'points' => 5]], $result['terms'][self::YOU]['points']);
        $this->assertSame('Bo Jackson', $result['terms'][self::THEY]['players'][0]['name']);
    }

    public function testPlayerAtRosterIndexZeroValidates(): void
    {
        // The legacy !array_search() check read a match at index 0 as a
        // failure; the first roster entry must validate
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['players' => [50]], []);

        $this->assertSame([], $result['errors']);
    }

    public function testPlayerNotOnGivingRosterIsRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['players' => [999]], ['players' => [60]]);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('not on the roster', $result['errors'][0]);
        $this->assertSame([], $result['terms'][self::YOU]['players']);
    }

    public function testUnownedPickIsRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['picks' => [777]], ['players' => [60]]);

        $this->assertStringContainsString('does not own', $result['errors'][0]);
    }

    public function testPointsBeyondRemainingBalanceAreRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['points' => [2026 => 13]], ['players' => [60]]);

        $this->assertStringContainsString('offered 13 points in 2026 but only 12 remain', $result['errors'][0]);
    }

    public function testPointsOutsideTheSeasonWindowAreRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['points' => [2040 => 1]], ['players' => [60]]);

        $this->assertStringContainsString('outside the tradeable seasons', $result['errors'][0]);
    }

    public function testNegativePointsAreRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['points' => [2026 => -3]], ['players' => [60]]);

        $this->assertStringContainsString('negative point amount', $result['errors'][0]);
    }

    public function testZeroPointAmountsAreIgnored(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, ['points' => [2026 => 0]], ['players' => [60]]);

        $this->assertSame([], $result['errors']);
        $this->assertSame([], $result['terms'][self::YOU]['points']);
    }

    public function testEmptyTradeIsRejected(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, [], []);

        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('empty', $result['errors'][0]);
    }

    public function testDuplicateSelectionsCollapse(): void
    {
        $service = $this->makeService();

        $result = $service->validate(self::YOU, self::THEY, [
            'players' => [50, 50],
            'picks' => [9, 9],
        ], []);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['terms'][self::YOU]['players']);
        $this->assertCount(1, $result['terms'][self::YOU]['picks']);
    }

    // ---- minPickSeason ----

    public function testMinPickSeasonIsNextSeasonDuringTheSeason(): void
    {
        $this->assertSame(2027, $this->makeService(week: 5)->minPickSeason());
    }

    public function testMinPickSeasonIsCurrentSeasonInTheOffseason(): void
    {
        $this->assertSame(2026, $this->makeService(week: 0)->minPickSeason());
    }

    // ---- helpers ----

    private function makeService(int $week = 5): TradeValidationService
    {
        $repo = $this->createStub(TradeOfferRepository::class);
        $repo->method('getTradeableRoster')->willReturnCallback(static fn (int $teamId) => match ($teamId) {
            self::YOU => [['playerid' => 50, 'name' => 'Al Kaline', 'pos' => 'WR', 'nflteam' => 'DET']],
            self::THEY => [['playerid' => 60, 'name' => 'Bo Jackson', 'pos' => 'RB', 'nflteam' => 'LV']],
        });
        $repo->method('getOwnedFuturePicks')->willReturnCallback(static fn (int $teamId) => match ($teamId) {
            self::YOU => [['id' => 9, 'season' => 2027, 'round' => 1, 'orgTeamId' => 2, 'orgTeamName' => 'Mustangs']],
            self::THEY => [],
        });
        $repo->method('getPointsBalances')->willReturn([
            2026 => 12, 2027 => 30, 2028 => 30, 2029 => 30, 2030 => 30, 2031 => 30,
        ]);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2026);
        $seasonWeek->method('getCurrentWeek')->willReturn($week);

        return new TradeValidationService($repo, $seasonWeek);
    }
}
