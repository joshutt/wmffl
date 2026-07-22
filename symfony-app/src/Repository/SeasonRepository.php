<?php

namespace App\Repository;

use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository for per-season league rules: lookups for the rule
 * services and the admin Season Rules pages.
 */
class SeasonRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function find(int $season): ?Season
    {
        return $this->em->find(Season::class, $season);
    }

    /** @return Season[] newest first */
    public function findAllDesc(): array
    {
        return $this->em->createQuery(
            'SELECT s FROM App\Entity\Season s ORDER BY s.season DESC'
        )->getResult();
    }

    public function findLatest(): ?Season
    {
        return $this->em->createQuery(
            'SELECT s FROM App\Entity\Season s ORDER BY s.season DESC'
        )->setMaxResults(1)->getOneOrNullResult();
    }

    public function save(Season $season): void
    {
        $this->em->persist($season);
        $this->em->flush();
    }
}
