<?php

use PHPUnit\Framework\TestCase;

include '../football/base/scoring.php';

class ScoreTest extends TestCase
{
    public function testKScore()
    {
        $baseKScore = ['XP' => 0, 'MissXP' => 0, '2pt' => 0, 'FG30' => 0, 'FG40' => 0, 'FG50' => 0, 'FG60' => 0, 'MissFG30' => 0, 'specTD' => 0];

        // XP is worth 1
        $kScore = $baseKScore;
        $kScore['XP'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(1, $pts, 'XP is worth 1 pt');

        // Miss XP is -1
        $kScore = $baseKScore;
        $kScore['MissXP'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(-1, $pts, 'Miss XP is worth -1 pt');

        $kScore = $baseKScore;
        $kScore['2pt'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(2, $pts, '2 pt is worth 2 pt');

        $kScore = $baseKScore;
        $kScore['FG30'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(3, $pts, 'FG30 is worth 3 pt');

        $kScore = $baseKScore;
        $kScore['FG40'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(4, $pts, 'FG40 is worth 4 pts');

        $kScore = $baseKScore;
        $kScore['FG50'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(5, $pts, 'FG50 is worth 5 pt');

        $kScore = $baseKScore;
        $kScore['MissFG30'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(-1, $pts, 'Miss FG30 is worth -1 pt');

        $kScore = $baseKScore;
        $kScore['specTD'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(12, $pts, 'Special TD is worth 12 pt');

        $kScore = $baseKScore;
        $kScore['FG60'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(7, $pts, 'FG60 is worth 7 pt');
    }

}