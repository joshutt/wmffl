<?php

use PHPUnit\Framework\TestCase;

include '../football/activate/scoreFunctions.php';

class LiveGameTest extends TestCase
{

    function testPenalizePlayerBye()
    {
        // Set up values
        $row = [
            'pos' => 'RB',
            'lastname' => 'Test',
            'firstname' => 'Player',
            'team' => 'NYJ',
            'kickoff' => '2023-10-15 16:25:00',
            'secRemain' => 0,
            'complete' => 1,
            'flmid' => 15708,
            'statid' => 15708,
            'Season' => 2023,
            'week' => 6,
            'played' => 1,
            'yards' => 93,
            'intthrow' => 0,
            'rec' => 5,
            'fum' => 0,
            'tackles' => 0,
            'sacks' => 0,
            'intcatch' => 0,
            'passdefend' => 0,
            'returnyards' => 0,
            'fumrec' => 0,
            'forcefum' => 0,
            'tds' => 1,
            '2pt' => 0,
            'specTD' => 0,
            'Safety' => 0,
            'XP' => 0,
            'MissXP' => 0,
            'FG30' => 0,
            'FG40' => 0,
            'FG50' => 0,
            'FG60' => 0,
            'MissFG30' => 0,
            'ptdiff' => null,
            'blockpunt' => 0,
            'blockfg' => 0,
            'blockxp' => 0,
            'penalties' => 0,
            'illegal' => 0,
            'startPos' => 'RB',
            'teamcheck1' => 3,
            'teamcheck2' => 3,
            'GPMe' => null,
            'GPThem' => null,
            'ActivationDue' => '2023-10-15 17:00:00'
        ];
        $pts = 21;
        $penalty = [3, 7];
        $defPoints = [14, 18];
        $offPoints = [26, 39];
        $totalPoints = [37, 50];

        // Call penalize player
        $i = 1;
        $result = penalizePlayer('bye', 5, $row, $pts, $penalty[$i], $defPoints[$i], $offPoints[$i], $totalPoints[$i]);

        // Make sure result is correct
        $this->assertNotEmpty($result, 'Results should be in final string');
        $this->assertStringContainsString('c2bye', $result, 'Result should have bye class indicated');
        $this->assertStringContainsString('-5', $result, 'Result should have a -5 for score');

        // Make sure scores get updated correctly
        $this->assertEquals(3, $penalty[0], 'Team 1 penalty should not change');
        $this->assertEquals(12, $penalty[1], 'Team 2 penalty should increase by 5');
        $this->assertEquals(14, $defPoints[0], 'Team 1 defense should not change');
        $this->assertEquals(18, $defPoints[1], 'Team 2 defense should not change');
        $this->assertEquals(26, $offPoints[0], 'Team 1 offense should not change');
        $this->assertEquals(18, $offPoints[1], 'Team 2 offense should decrease by 21');
        $this->assertEquals(37, $totalPoints[0], 'Team 1 total score should not change');
        $this->assertEquals(29, $totalPoints[1], 'Team 2 total score should decrease by 21');
    }


    function testPenalizePlayerByeDefense()
    {
        // Set up values
        $row = [
            'pos' => 'LB',
            'lastname' => 'Singleton',
            'firstname' => 'Alex',
            'team' => 'DEN',
            'kickoff' => '2023-10-12 20:15:00',
            'secRemain' => 0,
            'complete' => 1,
            'flmid' => 14412,
            'statid' => 14412,
            'Season' => 2023,
            'week' => 6,
            'played' => 1,
            'yards' => 0,
            'intthrow' => 0,
            'rec' => 0,
            'fum' => 0,
            'tackles' => 9,
            'sacks' => 0,
            'intcatch' => 0,
            'passdefend' => 0,
            'returnyards' => 0,
            'fumrec' => 0,
            'forcefum' => 0,
            'tds' => 0,
            '2pt' => 0,
            'specTD' => 0,
            'Safety' => 0,
            'XP' => 0,
            'MissXP' => 0,
            'FG30' => 0,
            'FG40' => 0,
            'FG50' => 0,
            'FG60' => 0,
            'MissFG30' => 0,
            'ptdiff' => null,
            'blockpunt' => 0,
            'blockfg' => 0,
            'blockxp' => 0,
            'penalties' => 0,
            'illegal' => 0,
            'startPos' => 'LB',
            'teamcheck1' => 3,
            'teamcheck2' => 3,
            'GPMe' => null,
            'GPThem' => null,
            'ActivationDue' => '2023-10-15 17:00:00'
        ];
        $pts = 9;
        $penalty = [3, 7];
        $defPoints = [33, 28];
        $offPoints = [69, 60];
        $totalPoints = [102, 88];

        // Call penalize player
        $i = 0;
        $result = penalizePlayer('bye', 5, $row, $pts, $penalty[$i], $defPoints[$i], $offPoints[$i], $totalPoints[$i]);

        // Make sure result is correct
        $this->assertNotEmpty($result, 'Results should be in final string');
        $this->assertStringContainsString('c2bye', $result, 'Result should have bye class indicated');
        $this->assertStringContainsString('-5', $result, 'Result should have a -5 for score');

        // Make sure scores get updated correctly
        $this->assertEquals(7, $penalty[1], 'Team 2 penalty should not change');
        $this->assertEquals(8, $penalty[0], 'Team 1 penalty should increase by 5');
        $this->assertEquals(28, $defPoints[1], 'Team 2 defense should not change');
        $this->assertEquals(24, $defPoints[0], 'Team 1 defense should decrease by 9');
        $this->assertEquals(60, $offPoints[1], 'Team 2 offense should not change');
        $this->assertEquals(69, $offPoints[0], 'Team 1 offense should not change');
        $this->assertEquals(88, $totalPoints[1], 'Team 2 total score should not change');
        $this->assertEquals(93, $totalPoints[0], 'Team 1 total score should decrease by 9');
    }

    function testPenalizePlayerIllegal()
    {
        // Set up values
        $row = [
            'pos' => 'RB',
            'lastname' => 'Test',
            'firstname' => 'Player',
            'team' => 'NYJ',
            'kickoff' => '2023-10-15 16:25:00',
            'secRemain' => 0,
            'complete' => 1,
            'flmid' => 15708,
            'statid' => 15708,
            'Season' => 2023,
            'week' => 6,
            'played' => 1,
            'yards' => 93,
            'intthrow' => 0,
            'rec' => 5,
            'fum' => 0,
            'tackles' => 0,
            'sacks' => 0,
            'intcatch' => 0,
            'passdefend' => 0,
            'returnyards' => 0,
            'fumrec' => 0,
            'forcefum' => 0,
            'tds' => 1,
            '2pt' => 0,
            'specTD' => 0,
            'Safety' => 0,
            'XP' => 0,
            'MissXP' => 0,
            'FG30' => 0,
            'FG40' => 0,
            'FG50' => 0,
            'FG60' => 0,
            'MissFG30' => 0,
            'ptdiff' => null,
            'blockpunt' => 0,
            'blockfg' => 0,
            'blockxp' => 0,
            'penalties' => 0,
            'illegal' => 0,
            'startPos' => 'RB',
            'teamcheck1' => 3,
            'teamcheck2' => 3,
            'GPMe' => null,
            'GPThem' => null,
            'ActivationDue' => '2023-10-15 17:00:00'
        ];
        $pts = 21;
        $penalty = [3, 7];
        $defPoints = [14, 18];
        $offPoints = [26, 39];
        $totalPoints = [37, 50];

        // Call penalize player
        $i = 1;
        $result = penalizePlayer('illegal', 10, $row, $pts, $penalty[$i], $defPoints[$i], $offPoints[$i], $totalPoints[$i]);

        // Make sure result is correct
        $this->assertNotEmpty($result, 'Results should be in final string');
        $this->assertStringContainsString('c2illegal', $result, 'Result should have illegal class indicated');
        $this->assertStringContainsString('-10', $result, 'Result should have a -10 for score');

        // Make sure scores get updated correctly
        $this->assertEquals(3, $penalty[0], 'Team 1 penalty should not change');
        $this->assertEquals(17, $penalty[1], 'Team 2 penalty should increase by 10');
        $this->assertEquals(14, $defPoints[0], 'Team 1 defense should not change');
        $this->assertEquals(18, $defPoints[1], 'Team 2 defense should not change');
        $this->assertEquals(26, $offPoints[0], 'Team 1 offense should not change');
        $this->assertEquals(18, $offPoints[1], 'Team 2 offense should decrease by 21');
        $this->assertEquals(37, $totalPoints[0], 'Team 1 total score should not change');
        $this->assertEquals(29, $totalPoints[1], 'Team 2 total score should decrease by 21');
    }


    function testPenalizePlayerIllegalDefense()
    {
        // Set up values
        $row = [
            'pos' => 'LB',
            'lastname' => 'Singleton',
            'firstname' => 'Alex',
            'team' => 'DEN',
            'kickoff' => '2023-10-12 20:15:00',
            'secRemain' => 0,
            'complete' => 1,
            'flmid' => 14412,
            'statid' => 14412,
            'Season' => 2023,
            'week' => 6,
            'played' => 1,
            'yards' => 0,
            'intthrow' => 0,
            'rec' => 0,
            'fum' => 0,
            'tackles' => 9,
            'sacks' => 0,
            'intcatch' => 0,
            'passdefend' => 0,
            'returnyards' => 0,
            'fumrec' => 0,
            'forcefum' => 0,
            'tds' => 0,
            '2pt' => 0,
            'specTD' => 0,
            'Safety' => 0,
            'XP' => 0,
            'MissXP' => 0,
            'FG30' => 0,
            'FG40' => 0,
            'FG50' => 0,
            'FG60' => 0,
            'MissFG30' => 0,
            'ptdiff' => null,
            'blockpunt' => 0,
            'blockfg' => 0,
            'blockxp' => 0,
            'penalties' => 0,
            'illegal' => 0,
            'startPos' => 'LB',
            'teamcheck1' => 3,
            'teamcheck2' => 3,
            'GPMe' => null,
            'GPThem' => null,
            'ActivationDue' => '2023-10-15 17:00:00'
        ];
        $pts = 9;
        $penalty = [3, 7];
        $defPoints = [33, 28];
        $offPoints = [69, 60];
        $totalPoints = [102, 88];

        // Call penalize player
        $i = 0;
        $result = penalizePlayer('illegal', 10, $row, $pts, $penalty[$i], $defPoints[$i], $offPoints[$i], $totalPoints[$i]);

        // Make sure result is correct
        $this->assertNotEmpty($result, 'Results should be in final string');
        $this->assertStringContainsString('c2illegal', $result, 'Result should have bye class indicated');
        $this->assertStringContainsString('-10', $result, 'Result should have a -10 for score');

        // Make sure scores get updated correctly
        $this->assertEquals(7, $penalty[1], 'Team 2 penalty should not change');
        $this->assertEquals(13, $penalty[0], 'Team 1 penalty should increase by 10');
        $this->assertEquals(28, $defPoints[1], 'Team 2 defense should not change');
        $this->assertEquals(24, $defPoints[0], 'Team 1 defense should decrease by 9');
        $this->assertEquals(60, $offPoints[1], 'Team 2 offense should not change');
        $this->assertEquals(69, $offPoints[0], 'Team 1 offense should decrease by 21');
        $this->assertEquals(88, $totalPoints[1], 'Team 2 total score should not change');
        $this->assertEquals(93, $totalPoints[0], 'Team 1 total score should decrease by 9');
    }

}