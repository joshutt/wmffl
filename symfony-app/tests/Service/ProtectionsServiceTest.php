<?php

namespace App\Tests\Service;

use App\Service\ProtectionsService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ProtectionsServiceTest extends TestCase
{
    // ---- deadline ----

    public function testDeadlineComesFromTheConfigTable(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')
            ->with($this->anything(), ['key' => 'protections.deadline'])
            ->willReturn('2030-08-16 23:59:00');

        $service = new ProtectionsService($conn);

        $this->assertSame('2030-08-16', $service->getDeadline()->format('Y-m-d'));
        $this->assertFalse($service->isDeadlinePassed());
    }

    public function testMissingDeadlineMeansProtectionsAreClosed(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn(false);

        $service = new ProtectionsService($conn);

        $this->assertNull($service->getDeadline());
        $this->assertTrue($service->isDeadlinePassed());
    }

    public function testUnparseableDeadlineMeansClosed(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn('not a date');

        $this->assertTrue((new ProtectionsService($conn))->isDeadlinePassed());
    }

    public function testPastDeadlineIsPassed(): void
    {
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn('2020-08-16 23:59:00');

        $this->assertTrue((new ProtectionsService($conn))->isDeadlinePassed());
    }

    // ---- save ----

    public function testOverBudgetSelectionSavesNothing(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturn(['9', '9']); // player costs
        $conn->method('fetchOne')->willReturn('10'); // totalpts allowed
        $conn->expects($this->never())->method('transactional');

        $result = (new ProtectionsService($conn))->saveProtections(2, 2026, [7, 8]);

        $this->assertFalse($result['ok']);
        $this->assertSame(18, $result['totalCost']);
        $this->assertSame(10, $result['allowed']);
    }

    public function testWithinBudgetReplacesProtectionsInATransaction(): void
    {
        $inner = $this->createMock(Connection::class);
        $statements = [];
        $inner->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements) {
                $statements[] = ['sql' => $sql, 'params' => $params];
                return 1;
            }
        );

        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturn(['3', '4']);
        $conn->method('fetchOne')->willReturn('55');
        $conn->expects($this->once())->method('transactional')->willReturnCallback(
            fn (callable $fn) => $fn($inner)
        );

        $result = (new ProtectionsService($conn))->saveProtections(2, 2026, ['7', '8']);

        $this->assertTrue($result['ok']);
        $this->assertSame(7, $result['totalCost']);

        $this->assertStringContainsString('DELETE FROM protections', $statements[0]['sql']);
        $this->assertStringContainsString('INSERT INTO protections', $statements[1]['sql']);
        $this->assertSame([7, 8], $statements[1]['params']['players']);
        $this->assertStringContainsString('UPDATE transpoints SET protectionpts', $statements[2]['sql']);
        $this->assertSame(7, $statements[2]['params']['cost']);
    }

    public function testEmptySelectionClearsProtectionsUsingTheZeroSentinel(): void
    {
        $inner = $this->createMock(Connection::class);
        $statements = [];
        $inner->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = []) use (&$statements) {
                $statements[] = ['sql' => $sql, 'params' => $params];
                return 1;
            }
        );

        $conn = $this->createMock(Connection::class);
        $conn->method('fetchFirstColumn')->willReturn([]);
        $conn->method('fetchOne')->willReturn('55');
        $conn->method('transactional')->willReturnCallback(fn (callable $fn) => $fn($inner));

        $result = (new ProtectionsService($conn))->saveProtections(2, 2026, []);

        $this->assertTrue($result['ok']);
        $this->assertSame(0, $result['totalCost']);
        $this->assertSame([0], $statements[1]['params']['players']);
    }
}
