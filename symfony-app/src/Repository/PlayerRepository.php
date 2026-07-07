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
    /** Sentinel `team` filter value for players with no current roster row */
    public const FREE_AGENTS = 'fa';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    /**
     * A page of the player index joined with the current WMFFL roster spot
     * (if any). Filters: q (name substring), team (WMFFL team id or
     * FREE_AGENTS), nfl (NFL abbreviation), pos, inactive (include retired).
     */
    public function searchPlayers(array $filters, int $page, int $perPage = 50): array
    {
        $params = [];
        $where = $this->buildSearchWhere($filters, $params);

        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT np.playerid AS id, np.lastname, np.firstname, np.pos,
                    np.team AS nfl_team, np.retired, t.Name AS wmffl_team'
            . self::SEARCH_FROM . $where .
            ' ORDER BY np.lastname, np.firstname
             LIMIT ' . max(1, $perPage) . ' OFFSET ' . (max(0, $page) * max(1, $perPage)),
            $params
        );
    }

    public function countPlayers(array $filters): int
    {
        $params = [];
        $where = $this->buildSearchWhere($filters, $params);

        return (int) $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT COUNT(*)' . self::SEARCH_FROM . $where,
            $params
        );
    }

    /** @return string[] distinct NFL abbreviations present in newplayers */
    public function getDistinctNflTeams(): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            "SELECT DISTINCT team FROM newplayers WHERE team IS NOT NULL AND team <> '' ORDER BY team"
        );
    }

    /** @return string[] distinct positions present in newplayers */
    public function getDistinctPositions(): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            "SELECT DISTINCT pos FROM newplayers WHERE pos IS NOT NULL AND pos <> '' ORDER BY pos"
        );
    }

    private const SEARCH_FROM =
        ' FROM newplayers np
         LEFT JOIN roster r ON r.PlayerID = np.playerid AND r.DateOff IS NULL
         LEFT JOIN team t ON t.TeamID = r.TeamID';

    private function buildSearchWhere(array $filters, array &$params): string
    {
        $where = [];

        if (empty($filters['inactive'])) {
            $where[] = 'np.retired IS NULL';
        }
        if (($filters['q'] ?? '') !== '') {
            $where[] = '(np.lastname LIKE :q OR np.firstname LIKE :q)';
            $params['q'] = '%' . addcslashes($filters['q'], '%_\\') . '%';
        }
        if (($filters['pos'] ?? '') !== '') {
            $where[] = 'np.pos = :pos';
            $params['pos'] = $filters['pos'];
        }
        if (($filters['nfl'] ?? '') !== '') {
            $where[] = 'np.team = :nfl';
            $params['nfl'] = $filters['nfl'];
        }
        if (($filters['team'] ?? '') !== '') {
            if ($filters['team'] === self::FREE_AGENTS) {
                $where[] = 'r.PlayerID IS NULL';
            } else {
                $where[] = 'r.TeamID = :team';
                $params['team'] = (int) $filters['team'];
            }
        }

        return $where ? ' WHERE ' . implode(' AND ', $where) : '';
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
