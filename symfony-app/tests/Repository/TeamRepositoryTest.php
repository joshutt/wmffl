<?php

namespace App\Tests\Repository;

use App\Repository\TeamRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class TeamRepositoryTest extends TestCase
{
    // ---- resolveTeamId ----

    public function testResolveTeamIdMatchesIdAbbrevOrSpaceStrippedName(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchOne')
            ->with(
                $this->logicalAnd(
                    $this->stringContains("REPLACE(LOWER(:viewteam), ' ', '')"),
                    $this->stringContains('LOWER(t.TeamID)'),
                    $this->stringContains('LOWER(t.abbrev)'),
                    $this->stringContains("REPLACE(LOWER(t.Name), ' ', '')")
                ),
                ['viewteam' => 'Amish Electricians']
            )
            ->willReturn('2');

        $this->assertSame(2, $this->makeRepo($conn)->resolveTeamId('Amish Electricians'));
    }

    public function testResolveTeamIdReturnsNullWhenNothingMatches(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchOne')->willReturn(false);

        $this->assertNull($this->makeRepo($conn)->resolveTeamId('bogus'));
    }

    // ---- getTeamHeader ----

    public function testGetTeamHeaderJoinsCoOwnersWithAnd(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn([
            'teamid' => 4, 'name' => 'Hempaholics', 'member' => 1993,
            'motto' => 'Fun-Lovin', 'logo' => 'Hemp1.jpg', 'fulllogo' => 0,
        ]);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains("u.active = 'Y'"),
                    $this->stringContains('ORDER BY u.primaryowner DESC, u.Name')
                ),
                ['id' => 4]
            )
            ->willReturn([
                ['name' => 'Tim Shoobridge', 'since' => '1993'],
                ['name' => 'Jon Solomon', 'since' => '1997'],
            ]);

        $header = $this->makeRepo($conn)->getTeamHeader(4);

        $this->assertSame('Tim Shoobridge and Jon Solomon', $header['owners']);
        $this->assertSame(2, $header['owner_count']);
        // owner-since is the primary owner's first season
        $this->assertSame('1993', $header['owner_since']);
        $this->assertFalse($header['fulllogo']);
    }

    public function testGetTeamHeaderSingleOwnerAndFullLogoFlag(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn([
            'teamid' => 7, 'name' => 'MeggaMen', 'member' => 2000,
            'motto' => null, 'logo' => 'mm.jpg', 'fulllogo' => 1,
        ]);
        $conn->method('fetchAllAssociative')->willReturn([['name' => 'Tom Marsh', 'since' => '2000']]);

        $header = $this->makeRepo($conn)->getTeamHeader(7);

        $this->assertSame('Tom Marsh', $header['owners']);
        $this->assertSame(1, $header['owner_count']);
        $this->assertTrue($header['fulllogo']);
    }

    public function testGetTeamHeaderReturnsNullForUnknownTeam(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(false);

        $this->assertNull($this->makeRepo($conn)->getTeamHeader(999));
    }

    public function testGetTeamHeaderWithNoActiveOwnersRendersEmptyOwnerList(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn([
            'teamid' => 11, 'name' => 'Fighting Squirrels (1996)', 'member' => 1996,
            'motto' => null, 'logo' => null, 'fulllogo' => 0,
        ]);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $header = $this->makeRepo($conn)->getTeamHeader(11);

        $this->assertSame('', $header['owners']);
        $this->assertSame(0, $header['owner_count']);
        $this->assertNull($header['owner_since']);
    }

    // ---- getChampionshipSeasons ----

    public function testGetChampionshipSeasonsQueriesLeagueTitlesInOrder(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with(
                $this->logicalAnd(
                    $this->stringContains("type = 'League'"),
                    $this->stringContains('ORDER BY season')
                ),
                ['id' => 2]
            )
            ->willReturn(['1998', '2002']);

        $this->assertSame([1998, 2002], $this->makeRepo($conn)->getChampionshipSeasons(2));
    }

    // ---- getTeamsByDivision ----

    public function testGetTeamsByDivisionFiltersToSeasonSpanOrderedByDivisionThenTeam(): void
    {
        $rows = [['teamid' => 3, 'team' => 'Norsemen', 'division' => 'Burgundy Division', 'divisionid' => 1, 'owner' => 'Byron Williams']];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains(':season BETWEEN d.startYear AND d.endYear'),
                    $this->stringContains('ORDER BY d.Name, t.Name')
                ),
                ['season' => 2026]
            )
            ->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getTeamsByDivision(2026));
    }

    // ---- getCurrentRoster ----

    public function testGetCurrentRosterShortensInjuryStatuses(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('r.dateoff IS NULL'),
                    $this->stringContains('ORDER BY p.pos, p.lastname')
                ),
                ['id' => 2]
            )
            ->willReturn([
                $this->rosterRow(['injury' => 'Questionable']),
                $this->rosterRow(['injury' => 'O']),
                $this->rosterRow(['injury' => 'IR-PUP']),
                $this->rosterRow(['injury' => null]),
            ]);

        $roster = $this->makeRepo($conn)->getCurrentRoster(2);

        $this->assertSame('Ques', $roster[0]['injury']);
        $this->assertSame('Out', $roster[1]['injury']);
        $this->assertSame('NFL IR', $roster[2]['injury']);
        $this->assertSame('', $roster[3]['injury']);
    }

    public function testGetCurrentRosterIrStintOverridesInjuryStatus(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            $this->rosterRow(['injury' => 'Questionable', 'ir' => 1]),
        ]);

        $roster = $this->makeRepo($conn)->getCurrentRoster(2);

        $this->assertSame('IR', $roster[0]['injury']);
    }

    public function testGetCurrentRosterEmptyForTeamWithNoPlayers(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $this->assertSame([], $this->makeRepo($conn)->getCurrentRoster(11));
    }

    // ---- getTransactionSummary ----

    public function testGetTransactionSummaryComputesRemainingPoints(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAssociative')
            ->with(
                $this->stringContains('tp.TransPts + tp.ProtectionPts'),
                ['id' => 2, 'season' => 2026]
            )
            ->willReturn(['used' => '5', 'total' => '60', 'roster_count' => '26']);

        $summary = $this->makeRepo($conn)->getTransactionSummary(2, 2026);

        $this->assertSame(55, $summary['remaining']);
        $this->assertSame(26, $summary['roster_count']);
    }

    public function testGetTransactionSummaryOverspendGoesNegative(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(['used' => '64', 'total' => '60', 'roster_count' => '25']);

        $this->assertSame(-4, $this->makeRepo($conn)->getTransactionSummary(2, 2026)['remaining']);
    }

    public function testGetTransactionSummaryNullWithoutTranspointsRow(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(false);

        $this->assertNull($this->makeRepo($conn)->getTransactionSummary(11, 2026));
    }

    // ---- schedule + head-to-head ----

    public function testGetSeasonScheduleLabelBeatsWeekName(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('IF(ISNULL(s.label), wm.weekname, s.label)'),
                    $this->stringContains('ORDER BY s.season, s.week')
                ),
                ['id' => 2, 'season' => 2024]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getSeasonSchedule(2, 2024);
    }

    public function testGetSeasonsPlayedReturnsIntsNewestFirst(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchFirstColumn')
            ->with($this->stringContains('ORDER BY season DESC'), ['id' => 2])
            ->willReturn(['2025', '2024']);

        $this->assertSame([2025, 2024], $this->makeRepo($conn)->getSeasonsPlayed(2));
    }

    public function testGetOpponentListUsesMostRecentTeamName(): void
    {
        $rows = [['name' => 'Aint Nothing But a Jew Thing', 'teamid' => 4]];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('MAX(t.season)'),
                    $this->stringContains('ORDER BY t.name')
                )
            )
            ->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getOpponentList());
    }

    public function testGetHeadToHeadQueriesAllMeetingsInSeasonWeekOrder(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('s.teama IN (:id, :opp)'),
                    $this->stringContains('s.teamb IN (:id, :opp)'),
                    $this->stringContains('ORDER BY wm.season, wm.week')
                ),
                ['id' => 2, 'opp' => 4]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getHeadToHead(2, 4);
    }

    public function testGetHeadToHeadRecordComputesPct(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(['win' => '37', 'tie' => '1', 'loss' => '31']);

        $record = $this->makeRepo($conn)->getHeadToHeadRecord(2, 4);

        $this->assertSame(['win' => 37, 'loss' => 31, 'tie' => 1, 'pct' => '0.543'], $record);
    }

    public function testGetHeadToHeadRecordZeroGamesIsZeroPct(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAssociative')->willReturn(['win' => null, 'tie' => null, 'loss' => null]);

        $record = $this->makeRepo($conn)->getHeadToHeadRecord(2, 11);

        $this->assertSame(['win' => 0, 'loss' => 0, 'tie' => 0, 'pct' => '0.000'], $record);
    }

    // ---- history records ----

    public function testGetPlayoffRecordSplitsPlayoffsAndToiletBowl(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains("IF(playoffs = 0, 'Toilet Bowl', 'Playoffs')"),
                    $this->stringContains('postseason = 1')
                ),
                ['id' => 2]
            )
            ->willReturn([
                ['label' => 'Playoffs', 'win' => '10', 'lose' => '8', 'tie' => '0'],
                ['label' => 'Toilet Bowl', 'win' => '1', 'lose' => '3', 'tie' => '0'],
            ]);

        $records = $this->makeRepo($conn)->getPlayoffRecord(2);

        $this->assertSame('Playoffs', $records[0]['label']);
        $this->assertSame('0.556', $records[0]['pct']);
        $this->assertSame('Toilet Bowl', $records[1]['label']);
        $this->assertSame('0.250', $records[1]['pct']);
    }

    public function testGetRegularSeasonRecordsSkipsInProgressSeasonAtWeekZeroAndZeroGameSeasons(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('postseason = 0'),
                    $this->stringContains('IF(:week = 0, s.season <> :season, true)'),
                    $this->stringContains('ORDER BY s.season DESC')
                ),
                ['id' => 2, 'week' => 0, 'season' => 2026]
            )
            ->willReturn([
                ['label' => '2025', 'win' => '9', 'lose' => '4', 'tie' => '1'],
                ['label' => '2024', 'win' => '0', 'lose' => '0', 'tie' => '0'],
            ]);

        $records = $this->makeRepo($conn)->getRegularSeasonRecords(2, 0, 2026);

        // the zero-game season is dropped
        $this->assertCount(1, $records);
        $this->assertSame(['label' => '2025', 'win' => 9, 'lose' => 4, 'tie' => 1, 'pct' => '0.679'], $records[0]);
    }

    public function testTotalRecordSumsSeasonRecords(): void
    {
        $total = TeamRepository::totalRecord([
            ['label' => '2025', 'win' => 9, 'lose' => 4, 'tie' => 1, 'pct' => '0.679'],
            ['label' => '2024', 'win' => 5, 'lose' => 9, 'tie' => 0, 'pct' => '0.357'],
        ]);

        $this->assertSame(['label' => 'All-Time', 'win' => 14, 'lose' => 13, 'tie' => 1, 'pct' => '0.518'], $total);
    }

    public function testTotalRecordOfNoSeasonsIsZeroPct(): void
    {
        $total = TeamRepository::totalRecord([]);

        $this->assertSame(['label' => 'All-Time', 'win' => 0, 'lose' => 0, 'tie' => 0, 'pct' => '0.000'], $total);
    }

    public function testGetPlayoffResultsFlagsWins(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains("IF(s.playoffs = 0, 'Toilet Bowl', IF(s.championship = 0, 'Playoffs', 'Championship'))"),
                ['id' => 4]
            )
            ->willReturn([
                ['event' => 'Playoffs', 'season' => '1993', 'opponent' => 'Slayers', 'myscore' => '121', 'otherscore' => '87'],
                ['event' => 'Championship', 'season' => '1993', 'opponent' => 'Tsunami', 'myscore' => '87', 'otherscore' => '121'],
            ]);

        $results = $this->makeRepo($conn)->getPlayoffResults(4);

        $this->assertTrue($results[0]['won']);
        $this->assertFalse($results[1]['won']);
    }

    public function testGetTitlesSplitsLeagueAndDivision(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['season' => '1993', 'type' => 'Division', 'divname' => 'Orange Division'],
            ['season' => '1998', 'type' => 'League', 'divname' => 'Blue Division'],
            ['season' => '2001', 'type' => 'Toilet', 'divname' => 'Blue Division'],
        ]);

        $titles = $this->makeRepo($conn)->getTitles(2);

        $this->assertSame([1998], $titles['league']);
        $this->assertSame([['season' => 1993, 'division' => 'Orange Division']], $titles['division']);
    }

    // ---- past names / past owners run-length encoding ----

    public function testGetPastNamesBuildsRangesAcrossAMidHistoryChange(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['season' => '1993', 'name' => 'Tsunami'],
            ['season' => '1994', 'name' => 'Tsunami'],
            ['season' => '1995', 'name' => 'Hempaholics'],
            ['season' => '1996', 'name' => 'Hempaholics'],
        ]);

        $names = $this->makeRepo($conn)->getPastNames(4);

        $this->assertSame([
            ['start' => 1993, 'end' => 1994, 'name' => 'Tsunami'],
            ['start' => 1995, 'end' => 0, 'name' => 'Hempaholics'],
        ], $names);
    }

    public function testGetPastOwnersJoinsCoOwnersAndClosesRangeOnChange(): void
    {
        $conn = $this->createMock(Connection::class);
        // primary=0 co-owner rows sort before the season's primary row
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->stringContains('ORDER BY o.season ASC, o.`primary` ASC'),
                ['id' => 4]
            )
            ->willReturn([
                ['name' => 'Jon Solomon', 'season' => '1993', 'primary' => '0'],
                ['name' => 'Tim Shoobridge', 'season' => '1993', 'primary' => '1'],
                ['name' => 'Tim Shoobridge', 'season' => '1994', 'primary' => '1'],
                ['name' => 'Andrew Kadish', 'season' => '1995', 'primary' => '1'],
            ]);

        $owners = $this->makeRepo($conn)->getPastOwners(4);

        $this->assertSame([
            ['start' => 1993, 'end' => 1993, 'name' => 'Tim Shoobridge and Jon Solomon'],
            ['start' => 1994, 'end' => 1994, 'name' => 'Tim Shoobridge'],
            ['start' => 1995, 'end' => 0, 'name' => 'Andrew Kadish'],
        ], $owners);
    }

    // ---- compare rosters ----

    public function testGetActiveTeamsOrderedByName(): void
    {
        $rows = [['name' => 'Amish Electricians', 'teamid' => 2]];

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with($this->logicalAnd(
                $this->stringContains('active = 1'),
                $this->stringContains('ORDER BY Name')
            ))
            ->willReturn($rows);

        $this->assertSame($rows, $this->makeRepo($conn)->getActiveTeams());
    }

    public function testGetRostersForComparisonBindsBothTeamIds(): void
    {
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->once())->method('fetchAllAssociative')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('t.TeamID IN (:a, :b)'),
                    $this->stringContains('ORDER BY t.Name, p.pos, p.lastname'),
                    // regression guard for the legacy SQL injection: the ids
                    // must be bound parameters, never interpolated
                    $this->logicalNot($this->stringContains('IN (2, 4)'))
                ),
                ['a' => 2, 'b' => 4]
            )
            ->willReturn([]);

        $this->makeRepo($conn)->getRostersForComparison(2, 4);
    }

    // ---- winPercentage ----

    public function testWinPercentageCountsTiesAsHalfWins(): void
    {
        $this->assertSame('0.679', TeamRepository::winPercentage(9, 4, 1));
        $this->assertSame('1.000', TeamRepository::winPercentage(3, 0, 0));
        $this->assertSame('0.000', TeamRepository::winPercentage(0, 0, 0));
    }

    // ---- helpers ----

    private function makeRepo(Connection $conn): TeamRepository
    {
        return new TeamRepository($conn);
    }

    private function rosterRow(array $overrides): array
    {
        return array_merge([
            'lastname' => 'Largent', 'firstname' => 'Steve', 'pos' => 'WR',
            'team' => 'SEA', 'bye' => 8, 'playerid' => 7,
            'date_on' => '2025-09-01', 'injury' => null, 'cost' => 10,
            'age' => 25, 'pts' => 100, 'ir' => null,
        ], $overrides);
    }
}
