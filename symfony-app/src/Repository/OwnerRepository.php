<?php

namespace App\Repository;

use App\Entity\Owner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Owner>
 */
class OwnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Owner::class);
    }

    /**
     * The owners row for this user in this season, if one exists yet.
     * Used to decide insert-vs-update when syncing a team assignment.
     */
    public function findForUserAndSeason(int $userId, int $season): ?Owner
    {
        return $this->createQueryBuilder('o')
            ->where('o.user = :userId')
            ->andWhere('o.season = :season')
            ->setParameter('userId', $userId)
            ->setParameter('season', $season)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
