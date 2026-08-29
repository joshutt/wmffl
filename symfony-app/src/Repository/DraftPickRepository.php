<?php

namespace App\Repository;

use App\Entity\DraftPick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DraftPick>
 */
class DraftPickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DraftPick::class);
    }

    private function connection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }

    /**
     * The #1 overall pick of every recorded draft: franchise (teamid,
     * plus its name that season), player name/pos and the player's NFL
     * team as of that season's week 1 (nflrosters as-of join — same
     * pattern as the legacy recordsweek.php query). Drafts whose
     * selections were never entered (pre-2006 rows have the pick
     * skeleton but playerid NULL) don't appear; the history page fills
     * those from its static list.
     *
     * @return array<array{season: int, teamid: int, team: string, player: string, pos: string, nflteam: string}>
     */
    public function getNumberOnePicks(): array
    {
        $rows = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            "SELECT dp.Season AS season, dp.teamid, tn.name AS team,
                    CONCAT(p.firstname, ' ', p.lastname) AS player, p.pos,
                    COALESCE(
                        (SELECT nr.nflteamid
                         FROM nflrosters nr
                         JOIN weekmap wm ON wm.Season = dp.Season AND wm.Week = 1
                         WHERE nr.playerid = dp.playerid
                           AND nr.dateon <= wm.ActivationDue
                           AND (nr.dateoff IS NULL OR nr.dateoff >= wm.ActivationDue)
                         LIMIT 1),
                        '') AS nflteam
             FROM draftpicks dp
             JOIN players p ON p.playerid = dp.playerid
             JOIN teamnames tn ON tn.teamid = dp.teamid AND tn.season = dp.Season
             WHERE dp.Round = 1 AND dp.Pick = 1 AND dp.playerid IS NOT NULL
             ORDER BY dp.Season"
        );

        foreach ($rows as &$row) {
            $row['season'] = (int) $row['season'];
            $row['teamid'] = (int) $row['teamid'];
        }

        return $rows;
    }

    /**
     * The season's week-1 activation due date, used as the NFL-team as-of
     * date for the draft board (Phase 16b decision 2) — same pattern as
     * getNumberOnePicks(). Null when the season has no week-1 weekmap row
     * yet (a newly-current season before its weekmap rows are seeded);
     * callers degrade to "today" (dateoff IS NULL) in that case.
     */
    public function getAsOfDate(int $season): ?string
    {
        $value = $this->connection()->fetchOne(
            'SELECT ActivationDue FROM weekmap WHERE Season = :season AND Week = 1',
            ['season' => $season]
        );

        return $value === false ? null : (string) $value;
    }

    /**
     * The full pick board for a season, including undrafted rows (decision
     * 1) and traded-pick provenance (decision 5). Filters are applied only
     * when non-null; the team filter matches the owning franchise
     * (teamid), never the original (orgTeam) — decision 5.
     *
     * teamnames is LEFT JOINed (not INNER) and COALESCEd against team's
     * current name, on both the owning and original side, so a season
     * whose teamnames rows aren't seeded yet doesn't collapse to zero rows
     * (the rollover hazard documented in requirements.md).
     *
     * @param array{round: ?int, pick: ?int, team: ?string, pos: ?string, nfl: ?string} $filters
     * @return array<array{id: int, round: int, pick: ?int, teamid: int, team: string,
     *     orgteam: ?int, orgteamname: ?string, firstname: ?string,
     *     lastname: ?string, pos: ?string, nflteamid: ?string}>
     */
    public function getBoard(int $season, array $filters, ?string $asOf): array
    {
        $where = ['dp.Season = :season'];
        $params = ['season' => $season];

        if ($filters['round'] !== null) {
            $where[] = 'dp.Round = :round';
            $params['round'] = $filters['round'];
        }
        if ($filters['pick'] !== null) {
            $where[] = 'dp.Pick = :pick';
            $params['pick'] = $filters['pick'];
        }
        if ($filters['team'] !== null) {
            $where[] = 'dp.teamid = :team';
            $params['team'] = $filters['team'];
        }
        if ($filters['pos'] !== null) {
            $where[] = 'p.pos = :pos';
            $params['pos'] = $filters['pos'];
        }

        $dateClause = $this->nflAsOfDateClause($asOf, $params);

        $having = '';
        if ($filters['nfl'] !== null) {
            $having = 'HAVING nflteamid = :nfl';
            $params['nfl'] = $filters['nfl'];
        }

        $sql = "SELECT dp.id AS id, dp.Round AS round, dp.Pick AS pick, dp.teamid AS teamid,
                    COALESCE(tn.name, t.name) AS team,
                    dp.orgTeam AS orgteam,
                    COALESCE(otn.name, ot.name) AS orgteamname,
                    p.firstname AS firstname, p.lastname AS lastname, p.pos AS pos,
                    (SELECT nr.nflteamid
                     FROM nflrosters nr
                     WHERE nr.playerid = dp.playerid AND {$dateClause}
                     LIMIT 1) AS nflteamid
                FROM draftpicks dp
                LEFT JOIN players p ON p.playerid = dp.playerid
                LEFT JOIN teamnames tn ON tn.teamid = dp.teamid AND tn.season = dp.Season
                LEFT JOIN team t ON t.teamid = dp.teamid
                LEFT JOIN teamnames otn ON otn.teamid = dp.orgTeam AND otn.season = dp.Season
                LEFT JOIN team ot ON ot.teamid = dp.orgTeam
                WHERE " . implode(' AND ', $where) . "
                {$having}
                ORDER BY dp.Round, dp.Pick IS NULL, dp.Pick";

        return $this->connection()->fetchAllAssociative($sql, $params);
    }

    /**
     * The dropdown option lists for a season's filter form, derived from
     * that season's actual data rather than the legacy hardcoded 1-12
     * round/pick range and broken OL/DL/LB/DB options.
     *
     * @return array{rounds: int[], picks: int[], teams: array<array{teamid: int, name: string}>,
     *     positions: string[], nflTeams: string[]}
     */
    public function getFilterOptions(int $season, ?string $asOf): array
    {
        $conn = $this->connection();

        $rounds = array_map('intval', $conn->fetchFirstColumn(
            'SELECT DISTINCT Round FROM draftpicks WHERE Season = :season ORDER BY Round',
            ['season' => $season]
        ));

        $picks = array_map('intval', $conn->fetchFirstColumn(
            'SELECT DISTINCT Pick FROM draftpicks WHERE Season = :season AND Pick IS NOT NULL ORDER BY Pick',
            ['season' => $season]
        ));

        $teams = $conn->fetchAllAssociative(
            'SELECT DISTINCT teamid, name FROM teamnames WHERE season = :season ORDER BY name',
            ['season' => $season]
        );
        if ($teams === []) {
            // Rollover guard: this season has no teamnames rows yet.
            $teams = $conn->fetchAllAssociative('SELECT teamid, name FROM team ORDER BY name');
        }
        foreach ($teams as &$team) {
            $team['teamid'] = (int) $team['teamid'];
        }
        unset($team);

        $positions = $conn->fetchFirstColumn(
            "SELECT DISTINCT p.pos FROM draftpicks dp
             JOIN players p ON p.playerid = dp.playerid
             WHERE dp.Season = :season AND p.pos IS NOT NULL AND p.pos <> ''
             ORDER BY p.pos",
            ['season' => $season]
        );

        $nflParams = [];
        $dateClause = $this->nflAsOfDateClause($asOf, $nflParams);
        $nflTeams = $conn->fetchFirstColumn(
            "SELECT DISTINCT nr.nflteamid FROM nflrosters nr WHERE {$dateClause} ORDER BY nr.nflteamid",
            $nflParams
        );

        return [
            'rounds' => $rounds,
            'picks' => $picks,
            'teams' => $teams,
            'positions' => $positions,
            'nflTeams' => $nflTeams,
        ];
    }

    /**
     * Every season with draftpicks rows, ascending — 2005 (skeleton-only)
     * through the latest seeded skeleton. Callers filter this to the
     * reachable range (no later than the current season); this repository
     * stays a plain data accessor.
     *
     * @return int[]
     */
    public function getSeasonsWithPicks(): array
    {
        return array_map('intval', $this->connection()->fetchFirstColumn(
            'SELECT DISTINCT Season FROM draftpicks ORDER BY Season'
        ));
    }

    /**
     * Seasons with at least one drafted (non-NULL playerid) pick, ascending.
     * Drives default-year resolution.
     *
     * @return int[]
     */
    public function getSeasonsWithSelections(): array
    {
        return array_map('intval', $this->connection()->fetchFirstColumn(
            'SELECT DISTINCT Season FROM draftpicks WHERE playerid IS NOT NULL ORDER BY Season'
        ));
    }

    /**
     * Whether some other pick in the season already has this player
     * (the `Season_playerid_uniq` constraint), so the admin controller can
     * reject the assignment with a flash instead of a 500.
     */
    public function isPlayerAlreadyDrafted(int $season, int $playerId, int $excludePickId): bool
    {
        return (bool) $this->connection()->fetchOne(
            'SELECT 1 FROM draftpicks
             WHERE Season = :season AND playerid = :playerId AND id != :excludeId
             LIMIT 1',
            ['season' => $season, 'playerId' => $playerId, 'excludeId' => $excludePickId]
        );
    }

    /**
     * The nflrosters as-of WHERE fragment (date portion only — callers add
     * their own `nr.playerid = ...` join condition), matching the shape
     * getNumberOnePicks() already uses. Degrades to "today" (dateoff IS
     * NULL) when $asOf is null, per decision 2. Binds :asOf into $params
     * when used.
     */
    private function nflAsOfDateClause(?string $asOf, array &$params): string
    {
        if ($asOf !== null) {
            $params['asOf'] = $asOf;

            return 'nr.dateon <= :asOf AND (nr.dateoff IS NULL OR nr.dateoff >= :asOf)';
        }

        return 'nr.dateoff IS NULL';
    }

//    /**
//     * @return DraftPick[] Returns an array of DraftPick objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DraftPick
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
