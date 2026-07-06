<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Repository for the homepage scores widget.
 * Ports the queries from football/scores.php
 */
class ScoresRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Scores for the latest visible week of a season. A week's games become
     * visible 12 hours before its weekmap display date; week 1 is always
     * visible.
     *
     * @return array{week: int, weekName: string, games: array}|null
     *         null when the season has no visible week
     */
    public function getLatestWeekScores(int $season): ?array
    {
        $weekSql = <<<EOD
select wm.week, wm.weekname
from weekmap wm
JOIN (select wm.season, max(wm.week) as 'week'
      from weekmap wm
      WHERE (DATE_SUB(wm.DisplayDate, INTERVAL 12 HOUR) < now() or wm.week = 1)
        and wm.season = :season) maxweek
ON wm.Season=maxweek.season and wm.week=maxweek.week
EOD;
        $weekRow = $this->connection->fetchAssociative($weekSql, ['season' => $season]);
        if (!$weekRow) {
            return null;
        }

        $gamesSql = <<<EOD
SELECT wm.weekname, wm.week, s.teama, if(s.scorea>=s.scoreb,t1.name, t2.name) as 'leadname',
if(s.scorea>=s.scoreb,s.scorea,s.scoreb) as 'leadscore', if(s.scorea>=s.scoreb,t2.name, t1.name) as 'trailname',
if(s.scorea>=s.scoreb,s.scoreb,s.scorea) as 'trailscore', s.label, s.overtime
FROM weekmap wm, schedule s, team t1, team t2
where (DATE_SUB(wm.displaydate, INTERVAL 12 HOUR) < now() OR wm.week=1)
and wm.season=:season and s.season=wm.season and s.week=wm.week
and s.teama=t1.teamid and s.teamb=t2.teamid
and wm.week=:week
order by wm.week DESC, s.label, MD5(CONCAT(t1.name, t2.name))
EOD;
        $games = $this->connection->fetchAllAssociative($gamesSql, [
            'season' => $season,
            'week' => $weekRow['week'],
        ]);

        return [
            'week' => (int) $weekRow['week'],
            'weekName' => $weekRow['weekname'],
            'games' => $games,
        ];
    }
}
