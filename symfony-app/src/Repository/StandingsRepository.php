<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository for fetching standings data
 * Ports queries from football/history/common/weekstandings.php
 */
class StandingsRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Get aggregated standings data for all teams
     * Ports the main query from weekstandings.php lines 12-38
     *
     * @return array Array of team standings data
     */
    public function getCurrentStandings(int $season, int $week): array
    {
        $query = <<<EOD
SELECT tn.name as 'team', d.name as 'division', t.teamid as 'teamid',
sum(if(t.teamid=s.teama, s.scorea, s.scoreb)) as 'ptsfor',
sum(if(t.teamid=s.teama, s.scoreb, s.scorea)) as 'ptsagt',
sum(IF(t.teamid=s.teama AND s.scorea>s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb>s.scorea, 1, 0))) as 'win',
sum(IF(t.teamid=s.teama AND s.scorea<s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb<s.scorea, 1, 0))) as 'lose',
sum(IF(s.scorea=s.scoreb, 1, 0)) as 'tie',

sum(if(t.divisionid=t2.divisionid, if(t.teamid=s.teama, s.scorea, s.scoreb), 0)) as 'divpf',
sum(if(t.divisionid=t2.divisionid, if(t.teamid=s.teama, s.scoreb, s.scorea), 0)) as 'divpa',
sum(if(t.divisionid=t2.divisionid, IF(t.teamid=s.teama AND s.scorea>s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb>s.scorea, 1, 0)),0)) as 'divwin',
sum(if(t.divisionid=t2.divisionid, IF(t.teamid=s.teama AND s.scorea<s.scoreb, 1, if(t.teamid=s.teamb AND s.scoreb<s.scorea, 1, 0)),0)) as 'divlose',
sum(if(t.divisionid=t2.divisionid, IF(s.scorea=s.scoreb, 1, 0),0)) as 'divtie'


FROM schedule s
JOIN team t on t.teamid in (s.teama, s.teamb)
JOIN teamnames tn ON t.teamid=tn.teamid AND tn.season=s.season
JOIN division d ON d.divisionid=tn.divisionid AND d.startYear <= s.season and (d.endYear >= s.season or d.endYear is null)
JOIN team t2 ON t2.teamid in (s.teama, s.teamb) and t2.teamid<>t.teamid
JOIN weekmap wm ON wm.season=s.season and wm.week=s.week and (DATE(wm.endDate) <= DATE(DATE_ADD(now(), INTERVAL -5 HOUR)) or wm.week=1)
WHERE YEAR(s.season) = :season
AND s.week <= :week
AND s.week<=14
GROUP BY d.name, tn.name

EOD;

        return $this->connection->fetchAllAssociative($query, [
            'season' => $season,
            'week' => $week,
        ]);
    }

    /**
     * Get individual game results for all teams
     * Ports the second query from weekstandings.php lines 40-53
     *
     * @return array Array of game results
     */
    public function getTeamGames(int $season, int $week): array
    {
        $query = <<<EOD
SELECT t.teamid as 'teamid', t2.teamid as 'oppid', if(t.teamid=s.teama, s.scorea, s.scoreb) as 'ptsfor',
if(t.teamid=s.teama, s.scoreb, s.scorea) as 'ptsagt', wm.week, tn.divisionid, tn2.divisionid as 'oppdiv'
FROM schedule s
JOIN team t ON t.teamid in (s.teama, s.teamb)
JOIN teamnames tn ON t.teamid=tn.teamid AND tn.season=s.season
JOIN division d ON d.divisionid=tn.divisionid AND d.startYear <= s.season and (d.endYear >= s.season or d.endYear is null)
JOIN team t2 ON t2.teamid in (s.teama, s.teamb) AND t2.teamid <> t.teamid
JOIN teamnames tn2 ON t2.teamid=tn2.teamid and tn2.season=s.season
JOIN weekmap wm ON wm.season=s.season and wm.week=s.week and DATE(wm.enddate)<=DATE(DATE_ADD(now(), INTERVAL -5 HOUR))
WHERE YEAR(s.season) = :season
AND s.week <= :week
AND s.week<=14
EOD;

        return $this->connection->fetchAllAssociative($query, [
            'season' => $season,
            'week' => $week,
        ]);
    }
}
