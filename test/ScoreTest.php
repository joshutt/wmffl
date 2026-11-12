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

    public function testOLOnByeWithNoStatsRowScoresZero()
    {
        // A player on a bye has no stats row (LEFT JOIN), so every stat is null.
        // scoreOL() must return an int 0, not null, or penalizePlayer() throws.
        $byeScore = array_fill_keys(['tds', 'yards', 'played', 'sacks'], null);
        $pts = scoreOL($byeScore);
        $this->assertSame(0, $pts);
    }

    /**
     * A player on a bye has no stats row (LEFT JOIN), so every stat column is
     * null. Every position's scorer must return a number (never null), since
     * the result is passed to penalizePlayer()'s int $pts parameter.
     */
    public function testAllPositionsScoreZeroWithNullStats()
    {
        $stats = array_fill_keys([
            'played', 'ptdiff', 'penalties', 'fum', 'intthrow', 'yards', 'tds', '2pt',
            'rec', 'specTD', 'blockpunt', 'blockxp', 'blockfg', 'sacks', 'XP', 'MissXP',
            'FG30', 'FG40', 'FG50', 'FG60', 'MissFG30', 'tackles', 'intcatch',
            'passdefend', 'fumrec', 'forcefum', 'returnyards', 'Safety',
        ], null);

        $scorers = [
            'HC' => 'scoreHC', 'QB' => 'scoreQB', 'RB' => 'scoreOffense',
            'WR' => 'scoreOffense', 'TE' => 'scoreTE', 'K' => 'scoreK',
            'OL' => 'scoreOL', 'DL' => 'scoreDefense', 'LB' => 'scoreDefense',
            'DB' => 'scoreDefense',
        ];

        foreach ($scorers as $pos => $fn) {
            $pts = $fn($stats);
            $this->assertNotNull($pts, "$pos scorer returned null for a bye-week player");
            $this->assertEquals(0, $pts, "$pos should score 0 with no stats");
            $this->assertSame('', scoreString($stats, $pos), "$pos scoreString should be empty");
        }
    }

}
