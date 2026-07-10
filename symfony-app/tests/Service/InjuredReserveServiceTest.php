<?php

namespace App\Tests\Service;

use App\Service\InjuredReserveService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class InjuredReserveServiceTest extends TestCase
{
    public function testAddLogsAToIrTransactionWhenTheInsertLands(): void
    {
        $statements = [];
        $conn = $this->connectionRecording($statements, returning: 1);

        $service = new InjuredReserveService($conn);
        $this->assertTrue($service->addPlayerToIr(2, 77));

        $this->assertCount(2, $statements);
        $this->assertStringContainsString('insert into ir', $statements[0]['sql']);
        $this->assertSame(77, $statements[0]['params']['playerId']);
        $this->assertStringContainsString('INSERT INTO transactions', $statements[1]['sql']);
        $this->assertSame('To IR', $statements[1]['params']['method']);
    }

    public function testAddOfIneligiblePlayerDoesNotLogATransaction(): void
    {
        $statements = [];
        $conn = $this->connectionRecording($statements, returning: 0);

        $service = new InjuredReserveService($conn);
        $this->assertFalse($service->addPlayerToIr(2, 77));

        // Legacy wrote a phantom 'To IR' transaction here; the port does not
        $this->assertCount(1, $statements);
    }

    public function testRemoveLogsAFromIrTransactionWhenTheUpdateLands(): void
    {
        $statements = [];
        $conn = $this->connectionRecording($statements, returning: 1);

        $service = new InjuredReserveService($conn);
        $this->assertTrue($service->removePlayerFromIr(2, 77));

        $this->assertCount(2, $statements);
        $this->assertStringContainsString('set ir.dateoff=now()', $statements[0]['sql']);
        $this->assertSame('From IR', $statements[1]['params']['method']);
    }

    public function testRemoveOfPlayerNotOnIrDoesNotLogATransaction(): void
    {
        $statements = [];
        $conn = $this->connectionRecording($statements, returning: 0);

        $service = new InjuredReserveService($conn);
        $this->assertFalse($service->removePlayerFromIr(2, 77));

        $this->assertCount(1, $statements);
    }

    public function testEligibilityQueryScopesToTeamAndIrStatuses(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('ir.id is null'),
                ['teamId' => 2, 'statuses' => InjuredReserveService::IR_STATUSES],
                $this->anything()
            )
            ->willReturn([]);

        (new InjuredReserveService($conn))->getEligiblePlayers(2);
    }

    /** @param array<int, array{sql: string, params: array}> $statements */
    private function connectionRecording(array &$statements, int $returning): Connection
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements, $returning) {
                $statements[] = ['sql' => $sql, 'params' => $params];
                // The write itself reports rows; the transaction log's return is unused
                return count($statements) === 1 ? $returning : 1;
            }
        );

        return $conn;
    }
}
