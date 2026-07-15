<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository for the team pages (index, roster, schedule, head-to-head,
 * history, compare). Ports the queries from the legacy football/teams/
 * scripts with bound parameters.
 */
class TeamRepository
{
    /**
     * Injury statuses shortened for the roster table, per legacy
     * football/utils/injuryUtils.php shortenInjury(). Unknown statuses
     * render as blank; a current IR stint overrides everything.
     */
    private const INJURY_SHORT = [
        'P' => 'Prob', 'Probable' => 'Prob',
        'Q' => 'Ques', 'Questionable' => 'Ques',
        'D' => 'Doub', 'Doubtful' => 'Doub',
        'O' => 'Out', 'Out' => 'Out',
        'I' => 'NFL IR', 'IR' => 'NFL IR', 'IR-NFI' => 'NFL IR', 'IR-PUP' => 'NFL IR',
        'S' => 'Susp', 'Suspended' => 'Susp',
        'Covid' => 'Covid', 'COVID-IR' => 'Covid', 'Holdout' => 'Covid',
    ];

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Resolve a legacy `viewteam` value — team id, abbreviation, or
     * lowercased/space-stripped team name — to a team id
     * (teamheader.php:21 semantics). Null when nothing matches.
     */
    public function resolveTeamId(string $viewteam): ?int
    {
        $id = $this->connection->fetchOne(
            "SELECT t.TeamID FROM team t
             WHERE REPLACE(LOWER(:viewteam), ' ', '')
                   IN (LOWER(t.TeamID), LOWER(t.abbrev), REPLACE(LOWER(t.Name), ' ', ''))",
            ['viewteam' => $viewteam]
        );

        return $id === false ? null : (int) $id;
    }

    /**
     * The shared team-page header: team row plus its active owners
     * (primary first, "and"-joined) and the primary owner's first season.
     * Null for an unknown team id.
     */
    public function getTeamHeader(int $teamId): ?array
    {
        $team = $this->connection->fetchAssociative(
            'SELECT t.TeamID AS teamid, t.Name AS name, t.member, t.motto, t.logo, t.fulllogo
             FROM team t WHERE t.TeamID = :id',
            ['id' => $teamId]
        );
        if (!$team) {
            return null;
        }

        $owners = $this->connection->fetchAllAssociative(
            "SELECT u.Name AS name, MIN(o.season) AS since
             FROM user u
             LEFT JOIN owners o ON o.teamid = u.TeamID AND o.userid = u.UserID
             WHERE u.TeamID = :id AND u.active = 'Y'
             GROUP BY u.UserID, u.Name, u.primaryowner
             ORDER BY u.primaryowner DESC, u.Name",
            ['id' => $teamId]
        );

        $team['fulllogo'] = (bool) $team['fulllogo'];
        $team['owners'] = implode(' and ', array_column($owners, 'name'));
        $team['owner_count'] = count($owners);
        $team['owner_since'] = $owners ? $owners[0]['since'] : null;

        return $team;
    }

    /** @return int[] seasons this team won the league title */
    public function getChampionshipSeasons(int $teamId): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            "SELECT season FROM titles WHERE teamid = :id AND type = 'League' ORDER BY season",
            ['id' => $teamId]
        ));
    }

    /**
     * Teams of the given season's divisions with their owner, sorted by
     * division then team name (legacy teams/index.php).
     */
    public function getTeamsByDivision(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT t.TeamID AS teamid, t.Name AS team, d.Name AS division,
                    d.DivisionID AS divisionid, u.Name AS owner
             FROM team t
             JOIN user u ON u.TeamID = t.TeamID
             JOIN division d ON t.DivisionID = d.DivisionID
             WHERE :season BETWEEN d.startYear AND d.endYear
             ORDER BY d.Name, t.Name",
            ['season' => $season]
        );
    }

    /**
     * The current roster with bye week, shortened injury status (IR
     * overrides), age, acquired date, next protection cost and season
     * points (legacy roster.php:31). The pts/cost seasons flip at the
     * season boundary inside the query (weekmap week <= 1).
     */
    public function getCurrentRoster(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT p.lastname, p.firstname, p.pos, p.team, b.week AS bye,
                    p.playerid, r.DateOn AS date_on, i.status AS injury,
                    MAX(pocos.cost) AS cost,
                    TIMESTAMPDIFF(YEAR, p.dob, now()) AS age,
                    IFNULL(ps.pts, 0) AS pts, ir.current AS ir
             FROM players p
             JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
             JOIN team t ON r.teamid = t.teamid
             JOIN weekmap wm ON wm.StartDate <= now() AND wm.EndDate >= now()
             LEFT JOIN nflbyes b ON p.team = b.nflteam AND b.season = wm.season
             LEFT JOIN injuries i ON i.playerid = p.playerid AND i.season = wm.season AND i.week = wm.week
             LEFT JOIN ir ON p.playerid = ir.playerid AND ir.dateoff IS NULL
             LEFT JOIN protectioncost pc ON p.playerid = pc.playerid
                    AND pc.season = IF(wm.week <= 1, wm.season, wm.season + 1)
             JOIN positioncost pocos ON p.pos = pocos.position AND pocos.endSeason IS NULL
                    AND pocos.years <= IFNULL(pc.years, 0)
             LEFT JOIN (
                   SELECT playerid, season, SUM(pts) AS pts
                   FROM playerscores
                   GROUP BY playerid, season
                  ) ps ON p.playerid = ps.playerid
                    AND ps.season = IF(wm.week <= 1, wm.season - 1, wm.season)
             WHERE t.teamid = :id
             GROUP BY p.playerid
             ORDER BY p.pos, p.lastname",
            ['id' => $teamId]
        );

        foreach ($rows as &$row) {
            $row['injury'] = $row['ir']
                ? 'IR'
                : (self::INJURY_SHORT[$row['injury']] ?? '');
        }

        return $rows;
    }

    /**
     * The season's transaction-points line: points used vs allowed and the
     * on-roster player count (legacy roster.php:5). Null when the team has
     * no transpoints row for the season.
     */
    public function getTransactionSummary(int $teamId, int $season): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT tp.TransPts + tp.ProtectionPts AS used, tp.TotalPts AS total,
                    COUNT(r.dateon) - COUNT(r.dateoff) - 1 AS roster_count
             FROM roster r, team t, transpoints tp
             WHERE r.teamid = t.teamid AND t.teamid = tp.teamid
               AND t.teamid = :id AND tp.season = :season
             GROUP BY t.teamid',
            ['id' => $teamId, 'season' => $season]
        );
        if (!$row) {
            return null;
        }

        return [
            'remaining' => (int) $row['total'] - (int) $row['used'],
            'roster_count' => (int) $row['roster_count'],
        ];
    }

    /**
     * One season's schedule with the opponent's name for that season.
     * `label` beats the weekmap week name (legacy indschedule.php:30).
     */
    public function getSeasonSchedule(int $teamId, int $season): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT IF(ISNULL(s.label), wm.weekname, s.label) AS weekname,
                    t.name AS opponent, wm.week,
                    IF(s.teama = :id, s.scorea, s.scoreb) AS score,
                    IF(s.teamb = :id, s.scorea, s.scoreb) AS oppscore
             FROM schedule s, teamnames t, weekmap wm
             WHERE s.season = :season
               AND ((s.teama = :id AND s.teamb = t.teamid) OR (s.teamb = :id AND s.teama = t.teamid))
               AND s.season = wm.season AND s.week = wm.week
               AND t.season = s.season
             ORDER BY s.season, s.week",
            ['id' => $teamId, 'season' => $season]
        );
    }

    /** @return int[] seasons the team appears in the schedule, newest first */
    public function getSeasonsPlayed(int $teamId): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT DISTINCT season FROM schedule WHERE :id IN (teama, teamb) ORDER BY season DESC',
            ['id' => $teamId]
        ));
    }

    /** Every team by its most recent teamnames name, for the h2h dropdown */
    public function getOpponentList(): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT t.name, t.teamid
             FROM teamnames t
             JOIN (
               SELECT t.teamid, MAX(t.season) AS season
               FROM teamnames t
               GROUP BY t.teamid) ts
             ON t.teamid = ts.teamid AND t.season = ts.season
             ORDER BY t.name"
        );
    }

    /** Every meeting between the two teams across all seasons (legacy h2h.php) */
    public function getHeadToHead(int $teamId, int $oppId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT wm.season, IF(ISNULL(s.label), wm.weekname, s.label) AS weekname,
                    t.name AS opponent, wm.week,
                    IF(s.teama = :id, s.scorea, s.scoreb) AS score,
                    IF(s.teamb = :id, s.scorea, s.scoreb) AS oppscore
             FROM schedule s
             JOIN teamnames t ON t.teamid IN (s.teama, s.teamb) AND t.season = s.season
             JOIN weekmap wm ON s.season = wm.season AND s.week = wm.week
             WHERE s.teama IN (:id, :opp)
               AND s.teamb IN (:id, :opp)
               AND t.teamid = :opp
             ORDER BY wm.season, wm.week',
            ['id' => $teamId, 'opp' => $oppId]
        );
    }

    /** Aggregate all-time record vs one opponent: win/loss/tie counts */
    public function getHeadToHeadRecord(int $teamId, int $oppId): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT SUM(IF(s.teama = :id, IF(s.scorea > s.scoreb, 1, 0), IF(s.scoreb > s.scorea, 1, 0))) AS win,
                    SUM(IF(s.scorea = s.scoreb, 1, 0)) AS tie,
                    SUM(IF(s.teama = :id, IF(s.scorea < s.scoreb, 1, 0), IF(s.scoreb < s.scorea, 1, 0))) AS loss
             FROM schedule s
             JOIN teamnames t ON t.teamid IN (s.teama, s.teamb) AND t.season = s.season
             JOIN weekmap wm ON s.season = wm.season AND s.week = wm.week
             WHERE s.teama IN (:id, :opp)
               AND s.teamb IN (:id, :opp)
               AND t.teamid = :opp',
            ['id' => $teamId, 'opp' => $oppId]
        );

        $win = (int) $row['win'];
        $tie = (int) $row['tie'];
        $loss = (int) $row['loss'];

        return [
            'win' => $win,
            'loss' => $loss,
            'tie' => $tie,
            'pct' => self::winPercentage($win, $loss, $tie),
        ];
    }

    /**
     * Playoff and Toilet Bowl all-time records
     * (legacy dataRetrieval.php getPlayoffRecord).
     */
    public function getPlayoffRecord(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT IF(playoffs = 0, 'Toilet Bowl', 'Playoffs') AS label,
                    SUM(IF(s.teama = :id, IF(s.scorea > s.scoreb, 1, 0), IF(s.scoreb > s.scorea, 1, 0))) AS win,
                    SUM(IF(s.teama = :id, IF(s.scorea < s.scoreb, 1, 0), IF(s.scoreb < s.scorea, 1, 0))) AS lose,
                    SUM(IF(s.scorea = s.scoreb, 1, 0)) AS tie
             FROM schedule s
             WHERE :id IN (s.teama, s.teamb) AND postseason = 1
             GROUP BY playoffs DESC",
            ['id' => $teamId]
        );

        return array_map(self::recordRow(...), $rows);
    }

    /**
     * Per-season regular-season records, newest first, skipping seasons
     * with no completed games and the in-progress season during week 0
     * (legacy dataRetrieval.php getRegularSeasonRecords).
     */
    public function getRegularSeasonRecords(int $teamId, int $currentWeek, int $currentSeason): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT s.season AS label,
                    SUM(IF(s.teama = :id, IF(s.scorea > s.scoreb, 1, 0), IF(s.scoreb > s.scorea, 1, 0))) AS win,
                    SUM(IF(s.teama = :id, IF(s.scorea < s.scoreb, 1, 0), IF(s.scoreb < s.scorea, 1, 0))) AS lose,
                    SUM(IF(s.scorea = s.scoreb, 1, 0)) AS tie
             FROM schedule s
             WHERE :id IN (s.teama, s.teamb) AND postseason = 0
               AND IF(:week = 0, s.season <> :season, true)
             GROUP BY s.season
             ORDER BY s.season DESC',
            ['id' => $teamId, 'week' => $currentWeek, 'season' => $currentSeason]
        );

        $records = [];
        foreach ($rows as $row) {
            if ((int) $row['win'] + (int) $row['lose'] + (int) $row['tie'] !== 0) {
                $records[] = self::recordRow($row);
            }
        }

        return $records;
    }

    /** Sum a set of record rows into a single labelled record */
    public static function totalRecord(array $records, string $label = 'All-Time'): array
    {
        $win = array_sum(array_column($records, 'win'));
        $lose = array_sum(array_column($records, 'lose'));
        $tie = array_sum(array_column($records, 'tie'));

        return self::recordRow(['label' => $label, 'win' => $win, 'lose' => $lose, 'tie' => $tie]);
    }

    /**
     * Every postseason game with the opponent's name for that season
     * (legacy dataRetrieval.php getPlayoffResults).
     */
    public function getPlayoffResults(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT IF(s.playoffs = 0, 'Toilet Bowl', IF(s.championship = 0, 'Playoffs', 'Championship')) AS event,
                    s.season, n.name AS opponent,
                    IF(s.TeamA = :id, s.scorea, s.scoreb) AS myscore,
                    IF(s.TeamA = :id, s.scoreb, s.scorea) AS otherscore
             FROM schedule s, teamnames n
             WHERE :id IN (s.TeamA, s.TeamB) AND s.postseason = 1
               AND n.season = s.season AND n.teamid <> :id AND n.teamid IN (s.TeamA, s.TeamB)
             ORDER BY s.season ASC, s.week ASC",
            ['id' => $teamId]
        );

        foreach ($rows as &$row) {
            $row['won'] = $row['myscore'] > $row['otherscore'];
        }

        return $rows;
    }

    /**
     * League and division titles, in season order
     * (legacy dataRetrieval.php getTitles).
     *
     * @return array{league: int[], division: array<array{season: int, division: string}>}
     */
    public function getTitles(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT t.season, t.type, d.name AS divname
             FROM titles t, teamnames n, division d
             WHERE t.teamid = :id AND t.teamid = n.teamid AND t.season = n.season
               AND n.divisionid = d.divisionid AND t.season BETWEEN d.startYear AND d.endYear
             ORDER BY t.season ASC",
            ['id' => $teamId]
        );

        $titles = ['league' => [], 'division' => []];
        foreach ($rows as $row) {
            if ($row['type'] === 'League') {
                $titles['league'][] = (int) $row['season'];
            } elseif ($row['type'] === 'Division') {
                $titles['division'][] = ['season' => (int) $row['season'], 'division' => $row['divname']];
            }
        }

        return $titles;
    }

    /**
     * The team's names as season ranges (legacy dataRetrieval.php
     * getPastNames run-length encoding). `end` of 0 means current.
     *
     * @return array<array{start: int, end: int, name: string}>
     */
    public function getPastNames(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT season, name FROM teamnames WHERE teamid = :id ORDER BY season ASC',
            ['id' => $teamId]
        );

        $ranges = [];
        $prevName = '';
        $startSeason = 0;
        foreach ($rows as $row) {
            if ($row['name'] !== $prevName) {
                if ($startSeason !== 0) {
                    $ranges[] = ['start' => $startSeason, 'end' => (int) $row['season'] - 1, 'name' => $prevName];
                }
                $startSeason = (int) $row['season'];
                $prevName = $row['name'];
            }
        }
        $ranges[] = ['start' => $startSeason, 'end' => 0, 'name' => $prevName];

        return $ranges;
    }

    /**
     * The team's owners as season ranges, co-owners "and"-joined onto the
     * primary owner (legacy dataRetrieval.php getPastOwners). `end` of 0
     * means current.
     *
     * @return array<array{start: int, end: int, name: string}>
     */
    public function getPastOwners(int $teamId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT u.name, o.season, o.`primary`
             FROM owners o, user u
             WHERE o.userid = u.userid AND o.teamid = :id
             ORDER BY o.season ASC, o.`primary` ASC',
            ['id' => $teamId]
        );

        $ranges = [];
        $prevName = '';
        $coOwners = '';
        $startSeason = 0;
        foreach ($rows as $row) {
            // Co-owner rows (primary=0) sort first within a season and are
            // collected until the season's primary-owner row closes them out.
            if ((int) $row['primary'] === 0) {
                $coOwners .= ' and ' . $row['name'];
                continue;
            }
            $seasonName = $row['name'] . $coOwners;
            $coOwners = '';

            if ($seasonName !== $prevName) {
                if ($startSeason !== 0) {
                    $ranges[] = ['start' => $startSeason, 'end' => (int) $row['season'] - 1, 'name' => $prevName];
                }
                $startSeason = (int) $row['season'];
                $prevName = $seasonName;
            }
        }
        $ranges[] = ['start' => $startSeason, 'end' => 0, 'name' => $prevName];

        return $ranges;
    }

    /** Active teams for the compare-rosters dropdowns, by name */
    public function getActiveTeams(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT Name AS name, TeamID AS teamid FROM team WHERE active = 1 ORDER BY Name'
        );
    }

    /**
     * Both teams' current rosters, ordered team name / pos / lastname
     * (legacy compareteams.php — with bound parameters, closing its SQL
     * injection).
     */
    public function getRostersForComparison(int $teamA, int $teamB): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT CONCAT(p.firstname, ' ', p.lastname) AS name, p.playerid,
                    p.pos, p.team, t.Name AS teamname
             FROM players p, roster r, team t
             WHERE p.playerid = r.playerid AND r.teamid = t.TeamID AND r.dateoff IS NULL
               AND t.TeamID IN (:a, :b)
             ORDER BY t.Name, p.pos, p.lastname",
            ['a' => $teamA, 'b' => $teamB]
        );
    }

    /**
     * Win percentage as the legacy 3-decimal string
     * (dataFormatting.php calculateWinPercentage).
     */
    public static function winPercentage(int $wins, int $losses, int $ties): string
    {
        $games = $wins + $losses + $ties;
        if ($games === 0) {
            return '0.000';
        }

        return sprintf('%5.3f', ($wins + $ties * 0.5) / $games);
    }

    /** Normalize a win/lose/tie row to ints and attach the pct string */
    private static function recordRow(array $row): array
    {
        $row['win'] = (int) $row['win'];
        $row['lose'] = (int) $row['lose'];
        $row['tie'] = (int) $row['tie'];
        $row['pct'] = self::winPercentage($row['win'], $row['lose'], $row['tie']);

        return $row;
    }
}
