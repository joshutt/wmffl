<?php

namespace App\Repository;

use App\Entity\DraftPick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
