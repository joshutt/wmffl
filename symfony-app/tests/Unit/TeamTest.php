<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WMFFL\Team;

class TeamTest extends TestCase
{
    // ---- addGame ----

    public function testAddGameRecordsWin(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, 100, 80, 99);

        $this->assertSame(1, $team->record[0], 'Win count');
        $this->assertSame(0, $team->record[1], 'Loss count');
        $this->assertSame(0, $team->record[2], 'Tie count');
        $this->assertSame(100, $team->ptsFor);
        $this->assertSame(80, $team->ptsAgt);
        $this->assertCount(1, $team->games);
    }

    public function testAddGameRecordsLoss(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, 70, 90, 99);

        $this->assertSame(0, $team->record[0]);
        $this->assertSame(1, $team->record[1]);
        $this->assertSame(70, $team->ptsFor);
        $this->assertSame(90, $team->ptsAgt);
    }

    public function testAddGameRecordsTie(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, 85, 85, 99);

        $this->assertSame(0, $team->record[0]);
        $this->assertSame(0, $team->record[1]);
        $this->assertSame(1, $team->record[2]);
    }

    public function testAddGameUpdatesDivisionRecordWhenDivisionMatches(): void
    {
        // Division is '3' (string); oppDiv 3 (int) — loose equality holds in PHP
        $team = new Team('Alpha', '3', 1);
        $team->addGame(2, 100, 80, 3);

        $this->assertSame(1, $team->divRecord[0], 'Div win');
        $this->assertSame(0, $team->divRecord[1]);
        $this->assertSame(100, $team->divPtsFor);
        $this->assertSame(80, $team->divPtsAgt);
    }

    public function testAddGameDoesNotUpdateDivisionRecordForNonDivisionOpponent(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, 100, 80, 99); // oppDiv 99 ≠ 'East'

        $this->assertSame(0, $team->divRecord[0]);
        $this->assertSame(0, $team->divPtsFor);
    }

    public function testAddGameAccumulatesAcrossMultipleGames(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, 100, 80, 99); // win
        $team->addGame(3, 70, 90, 99);  // loss

        $this->assertSame(1, $team->record[0]);
        $this->assertSame(1, $team->record[1]);
        $this->assertSame(170, $team->ptsFor);
        $this->assertSame(170, $team->ptsAgt);
        $this->assertCount(2, $team->games);
    }

    public function testAddGameSkipsGameWithNullScore(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->addGame(2, null, null, 99);

        $this->assertCount(0, $team->games);
        $this->assertSame(0, $team->ptsFor);
    }

    // ---- getWinPCT ----

    public function testGetWinPCTWithNoGames(): void
    {
        $team = new Team('Alpha', 'East', 1);

        $this->assertSame(0.000, $team->getWinPCT());
    }

    public function testGetWinPCTWithWinsAndLosses(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->record = [3, 1, 0]; // 3-1 = 0.750

        $this->assertEqualsWithDelta(0.750, $team->getWinPCT(), 0.0001);
    }

    public function testGetWinPCTCountsTiesAsHalfWin(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->record = [1, 1, 2]; // 1W + 2T×0.5 = 2 effective wins out of 4 = 0.500

        $this->assertEqualsWithDelta(0.500, $team->getWinPCT(), 0.0001);
    }

    // ---- getDivWinPCT ----

    public function testGetDivWinPCTWithNoDivGames(): void
    {
        $team = new Team('Alpha', 'East', 1);

        $this->assertSame(0.000, $team->getDivWinPCT());
    }

    public function testGetDivWinPCTWithDivRecord(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->divRecord = [2, 1, 1]; // 2W + 0.5T = 2.5 out of 4 = 0.625

        $this->assertEqualsWithDelta(0.625, $team->getDivWinPCT(), 0.0001);
    }

    // ---- printShortRecord ----

    public function testPrintShortRecordWithoutTies(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->record = [3, 2, 0];

        $this->assertSame('3 - 2', $team->printShortRecord());
    }

    public function testPrintShortRecordWithTies(): void
    {
        $team = new Team('Alpha', 'East', 1);
        $team->record = [3, 2, 1];

        $this->assertSame('3 - 2 - 1', $team->printShortRecord());
    }

    // ---- getTeamId ----

    public function testGetTeamIdReturnsTeamid(): void
    {
        $team = new Team('Alpha', 'East', 42);

        $this->assertSame(42, Team::getTeamId($team));
    }
}
