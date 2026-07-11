<?php

namespace App\Tests\Service;

use App\Repository\TransactionRepository;
use App\Service\TransactionHistoryService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TransactionHistoryServiceTest extends TestCase
{
    public function testGroupsRowsByDateTeamAndMethod(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getTransactions')->willReturn([
            $this->row('September 8, 2025', 'Amish Electricians', 'Cut', 'Al Kaline', 10),
            $this->row('September 8, 2025', 'Amish Electricians', 'Cut', 'Bo Jackson', 11),
            $this->row('September 8, 2025', 'Amish Electricians', 'Sign', 'Cy Young', 12),
            $this->row('September 8, 2025', 'Norsemen', 'Sign', 'Don Drysdale', 13),
            $this->row('September 1, 2025', 'Norsemen', 'To IR', 'Ed Walsh', 14),
        ]);

        $history = (new TransactionHistoryService($repo))->buildHistory(2025, 9);

        $this->assertCount(2, $history);
        $this->assertSame('September 8, 2025', $history[0]['date']);
        $this->assertSame(['Amish Electricians', 'Norsemen'], array_column($history[0]['teams'], 'name'));

        $amish = $history[0]['teams'][0];
        $this->assertSame(['Dropped', 'Picked Up'], array_column($amish['moves'], 'label'));
        $this->assertCount(2, $amish['moves'][0]['players']);
        $this->assertSame('Al Kaline', $amish['moves'][0]['players'][0]['name']);

        $this->assertSame('Moved to IR', $history[1]['teams'][0]['moves'][0]['label']);
    }

    public function testTradeRowsCollapseIntoSentencesOncePerTeamAndDay(): void
    {
        $repo = $this->createMock(TransactionRepository::class);
        $repo->method('getTransactions')->willReturn([
            $this->row('September 8, 2025', 'Amish Electricians', 'Trade', 'Al Kaline', 10),
            $this->row('September 8, 2025', 'Amish Electricians', 'Trade', 'Bo Jackson', 11),
        ]);
        // Two Trade rows, but the trade detail lookup happens exactly once
        $repo->expects($this->once())->method('getTradeDetails')->with(2, '2025-09-08')->willReturn([
            $this->tradeRow(1, 'Amish Electricians', 'Al', 'Kaline', 10),
            $this->tradeRow(1, 'Norsemen', 'Bo', 'Jackson', 11),
        ]);

        $history = (new TransactionHistoryService($repo))->buildHistory(2025, 9);

        $team = $history[0]['teams'][0];
        $this->assertSame([], $team['moves']);
        $this->assertCount(1, $team['trades']);

        $texts = array_column($team['trades'][0], 'text');
        $this->assertSame('Traded ', $texts[0]);
        $this->assertContains(' to the Norsemen in exchange for ', $texts);
        $players = array_column($team['trades'][0], 'player');
        $this->assertSame(['Al Kaline', 'Bo Jackson'], array_column($players, 'name'));
    }

    public function testTradeSentenceSeparatesPlayersWithCommasAndKeepsOtherText(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getTransactions')->willReturn([
            $this->row('September 8, 2025', 'Amish Electricians', 'Trade', 'Al Kaline', 10),
        ]);
        $repo->method('getTradeDetails')->willReturn([
            $this->tradeRow(1, 'Amish Electricians', 'Al', 'Kaline', 10),
            $this->tradeRow(1, 'Amish Electricians', 'Bo', 'Jackson', 11),
            $this->tradeRow(1, 'Norsemen', '', '', null, other: 'a 2026 2nd-round pick'),
        ]);

        $history = (new TransactionHistoryService($repo))->buildHistory(2025, 9);
        $sentence = $history[0]['teams'][0]['trades'][0];

        // Traded [Al Kaline], [Bo Jackson] to the Norsemen in exchange for "a 2026 2nd-round pick"
        $rendered = '';
        foreach ($sentence as $token) {
            $rendered .= $token['text'] ?? '[' . $token['player']['name'] . ']';
        }
        $this->assertSame(
            'Traded [Al Kaline], [Bo Jackson] to the Norsemen in exchange for a 2026 2nd-round pick',
            $rendered
        );
    }

    public function testMultipleTradeGroupsProduceSeparateSentences(): void
    {
        $repo = $this->createStub(TransactionRepository::class);
        $repo->method('getTransactions')->willReturn([
            $this->row('September 8, 2025', 'Amish Electricians', 'Trade', 'Al Kaline', 10),
        ]);
        $repo->method('getTradeDetails')->willReturn([
            $this->tradeRow(1, 'Amish Electricians', 'Al', 'Kaline', 10),
            $this->tradeRow(1, 'Norsemen', 'Bo', 'Jackson', 11),
            $this->tradeRow(2, 'Amish Electricians', 'Cy', 'Young', 12),
            $this->tradeRow(2, 'Crusaders', 'Don', 'Drysdale', 13),
        ]);

        $history = (new TransactionHistoryService($repo))->buildHistory(2025, 9);

        $this->assertCount(2, $history[0]['teams'][0]['trades']);
    }

    private function row(string $date, string $team, string $method, string $player, int $playerid): array
    {
        return [
            'displaydate' => $date,
            'teamname' => $team,
            'method' => $method,
            'player' => $player,
            'pos' => 'QB',
            'nflteam' => 'SEA',
            'teamid' => 2,
            'rawdate' => '2025-09-08',
            'playerid' => $playerid,
        ];
    }

    private function tradeRow(int $group, string $from, string $first, string $last, ?int $playerid, ?string $other = null): array
    {
        return [
            'tradegroup' => $group,
            'date' => '2025-09-08',
            'teamfrom' => $from,
            'lastname' => $last,
            'firstname' => $first,
            'pos' => 'QB',
            'team' => 'SEA',
            'other' => $other,
            'playerid' => $playerid,
        ];
    }
}
