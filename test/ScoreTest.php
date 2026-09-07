<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

include __DIR__ . '/../football/base/scoring.php';

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
        $this->assertEquals(2, $pts, 'FG30 is worth 2 pt (2026 rule change)');

        $kScore = $baseKScore;
        $kScore['FG40'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(2, $pts, 'FG40 is worth 2 pts (2026 rule change)');

        $kScore = $baseKScore;
        $kScore['FG50'] = 1;
        $pts = scoreK($kScore);
        $this->assertEquals(4, $pts, 'FG50 is worth 4 pt (2026 rule change)');

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
        $this->assertEquals(6, $pts, 'FG60 is worth 6 pt (2026 rule change)');
    }

    public function testOffenseBlockedKickScore()
    {
        $baseOffenseScore = [
            'fum' => 0, 'yards' => 0, 'rec' => 0, 'tds' => 0, '2pt' => 0,
            'specTD' => 0, 'blockpunt' => 0, 'blockxp' => 0, 'blockfg' => 0,
        ];

        // a blocked punt/XP/FG is worth 3 pts each, for general offense
        // (RB/WR/TE) in addition to defense (2026 rule change)
        $offenseScore = $baseOffenseScore;
        $offenseScore['blockpunt'] = 1;
        $offenseScore['blockxp'] = 1;
        $offenseScore['blockfg'] = 1;
        $pts = scoreOffense($offenseScore);
        $this->assertEquals(9, $pts, 'Each blocked kick is worth 3 pts for offense');
    }

}