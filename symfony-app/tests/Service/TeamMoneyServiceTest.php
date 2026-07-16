<?php

namespace App\Tests\Service;

use App\Service\TeamMoneyService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TeamMoneyServiceTest extends TestCase
{
    private TeamMoneyService $service;

    protected function setUp(): void
    {
        $this->service = new TeamMoneyService(
            $this->createStub(Connection::class),
            $this->createStub(EntityManagerInterface::class)
        );
    }

    private function paidRow(array $overrides = []): array
    {
        return array_merge([
            'teamid' => 6, 'teamName' => 'Crusaders', 'paid' => true,
            'previous' => 0.0, 'entry' => 75.0, 'amtPaid' => 75.0, 'lateFee' => 0.0,
        ], $overrides);
    }

    public function testPotAndPayoutRates(): void
    {
        // two teams, no fines: pot = 2 * 75 (legacy teammoney.php:102-109)
        $ledger = $this->service->computeLedger(
            [$this->paidRow(), $this->paidRow(['teamid' => 7, 'teamName' => 'MeggaMen'])],
            [], [], [], false
        );

        $this->assertSame(150.0, (float) $ledger['payouts']['totalPot']);
        $this->assertSame(round(150 * 0.25 / 84, 2), $ledger['payouts']['perWin']);
        $this->assertSame(round(75 * 0.05, 2), $ledger['payouts']['divisionWin']);
        $this->assertSame(round(75 * 0.05, 2), $ledger['payouts']['playoffApp']);
        $this->assertSame(round(75 * 0.20, 2), $ledger['payouts']['champApp']);
        $this->assertSame(round(75 * 0.25, 2), $ledger['payouts']['champWin']);
    }

    public function testFinesFeedTheBalanceAndThePot(): void
    {
        // 2 illegal activations ($5 each), 1 bye-week ($1), 3 transaction
        // points over ($1 each), $10 late fee = $24 negative
        $ledger = $this->service->computeLedger(
            [$this->paidRow(['lateFee' => 10.0])],
            [6 => ['name' => 'Crusaders', 'illegal' => 2, 'byeWeek' => 1, 'Remaining' => -3]],
            [], [], false
        );

        $team = $ledger['teams'][6];
        $this->assertSame(3, $team['overage']);
        $this->assertSame(24.0, (float) $team['negative']);
        // pot picks up the fines: 75 + 24
        $this->assertSame(99.0, (float) $ledger['payouts']['totalPot']);
        // balance: 0 - 75 + 75 - 24 + 0 wins
        $this->assertSame(-24.0, (float) $team['balance']);
    }

    public function testTiesCountHalfAWinAndPlayoffLinesAccumulate(): void
    {
        $ledger = $this->service->computeLedger(
            [$this->paidRow()],
            [],
            [6 => ['wins' => 9, 'losses' => 4, 'ties' => 1]],
            [6 => ['division_winner' => 1, 'playoff_team' => 1, 'finalist' => 0, 'champion' => 0]],
            false
        );

        $team = $ledger['teams'][6];
        $this->assertSame(9.5, $team['wins']);
        $this->assertSame(['Division Title', 'Playoff Team'], array_column($team['playoffLines'], 'label'));

        $p = $ledger['payouts'];
        $expected = 0 - 75 + 75 + 9.5 * $p['perWin'] + $p['divisionWin'] + $p['playoffApp'];
        $this->assertEqualsWithDelta($expected, $team['balance'], 0.001);
    }

    public function testStillOweIncludesNextSeasonFeeForPastSeasons(): void
    {
        // balance ends at 0 → owes the full next-season entry fee
        $ledger = $this->service->computeLedger([$this->paidRow()], [], [], [], true);
        $this->assertSame(75.0, (float) $ledger['teams'][6]['stillOwe']);
        $this->assertSame([6 => 75.0], array_map('floatval', $ledger['amtOwed']));

        // current season: paid up and non-negative → owes nothing
        $ledger = $this->service->computeLedger([$this->paidRow()], [], [], [], false);
        $this->assertSame(0, $ledger['teams'][6]['stillOwe']);
        $this->assertSame([], $ledger['amtOwed']);
    }

    public function testPositiveBalanceClearsDelinquency(): void
    {
        $ledger = $this->service->computeLedger(
            [$this->paidRow(['paid' => false, 'previous' => 100.0, 'amtPaid' => 0.0])],
            [], [], [], false
        );

        // balance 100 - 75 = 25 >= 0 wipes the unpaid flag (legacy :137-139)
        $this->assertFalse($ledger['teams'][6]['deliquent']);
        $this->assertSame([], $ledger['amtOwed']);
    }

    public function testUnpaidNegativeBalanceOwesItsDebt(): void
    {
        $ledger = $this->service->computeLedger(
            [$this->paidRow(['paid' => false, 'amtPaid' => 50.0])],
            [], [], [], false
        );

        // balance 0 - 75 + 50 = -25, unpaid → owes 25
        $this->assertTrue($ledger['teams'][6]['deliquent']);
        $this->assertSame(25.0, (float) $ledger['teams'][6]['stillOwe']);
    }
}
