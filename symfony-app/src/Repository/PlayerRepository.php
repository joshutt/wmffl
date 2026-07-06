<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function getCurrentRoster(int $playerId): ?array
    {
        $row = $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT t.Name AS team_name, t.TeamID AS team_id, r.DateOn AS date_on, r.DateOff AS date_off
             FROM roster r JOIN team t ON r.TeamID = t.TeamID
             WHERE r.PlayerID = :pid AND r.DateOff IS NULL
             LIMIT 1',
            ['pid' => $playerId]
        );

        return $row ?: null;
    }

    public function getRosterHistory(int $playerId): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT r.TeamID AS team_id, r.DateOn AS date_on, r.DateOff AS date_off,
                    GROUP_CONCAT(DISTINCT tn.name ORDER BY tn.season SEPARATOR \'/\') AS team_name,
                    (SELECT COUNT(*)
                     FROM revisedactivations a
                     WHERE a.teamid = r.TeamID
                       AND a.playerid = r.PlayerID
                       AND a.season BETWEEN YEAR(r.DateOn) AND YEAR(COALESCE(r.DateOff, NOW()))
                    ) AS games_activated,
                    (SELECT COALESCE(SUM(ps.pts), 0)
                     FROM revisedactivations a
                     JOIN playerscores ps ON ps.playerid = r.PlayerID AND ps.season = a.season AND ps.week = a.week
                     WHERE a.teamid = r.TeamID
                       AND a.playerid = r.PlayerID
                       AND a.season BETWEEN YEAR(r.DateOn) AND YEAR(COALESCE(r.DateOff, NOW()))
                    ) AS active_pts
             FROM roster r
             JOIN teamnames tn ON tn.teamid = r.TeamID
                 AND tn.season BETWEEN YEAR(r.DateOn) AND YEAR(COALESCE(r.DateOff, NOW()))
             WHERE r.PlayerID = :pid
             GROUP BY r.TeamID, r.DateOn, r.DateOff
             ORDER BY r.DateOn DESC',
            ['pid' => $playerId]
        );
    }

    public function getStatsBySeason(int $playerId): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT s.Season AS season,
                    SUM(s.yards)       AS yards,
                    SUM(s.tds)         AS tds,
                    SUM(s.rec)         AS rec,
                    SUM(s.intthrow)    AS intthrow,
                    SUM(s.fum)         AS fum,
                    SUM(s.tackles)     AS tackles,
                    SUM(s.sacks)       AS sacks,
                    SUM(s.intcatch)    AS intcatch,
                    SUM(s.passdefend)  AS passdefend,
                    SUM(s.returnyards) AS returnyards,
                    SUM(s.fumrec)      AS fumrec,
                    SUM(s.forcefum)    AS forcefum,
                    SUM(s.specTD)      AS spec_td,
                    SUM(s.Safety)      AS safety,
                    SUM(s.XP)          AS xp,
                    SUM(s.MissXP)      AS miss_xp,
                    SUM(s.FG30)        AS fg30,
                    SUM(s.FG40)        AS fg40,
                    SUM(s.FG50)        AS fg50,
                    SUM(s.FG60)        AS fg60,
                    SUM(s.MissFG30)    AS miss_fg30,
                    SUM(s.`2pt`)       AS two_pt,
                    SUM(s.blockpunt)   AS block_punt,
                    SUM(s.blockfg)     AS block_fg,
                    SUM(s.blockxp)     AS block_xp,
                    SUM(s.penalties)   AS penalties,
                    COUNT(s.week)      AS weeks_played,
                    SUM(ps.pts)        AS total_pts,
                    SUM(CASE WHEN ra.playerid IS NOT NULL THEN ps.pts ELSE 0 END) AS active_pts
             FROM newplayers np
             JOIN stats s ON s.statid = np.flmid
             JOIN playerscores ps ON ps.playerid = np.playerid
                  AND ps.season = s.Season AND ps.week = s.week
             LEFT JOIN revisedactivations ra ON ra.playerid = np.playerid
                  AND ra.season = s.Season AND ra.week = s.week
             WHERE np.playerid = :pid
             GROUP BY s.Season
             ORDER BY s.Season DESC',
            ['pid' => $playerId]
        );
    }
}
