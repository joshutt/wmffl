<?php

namespace App\Repository;

use App\Entity\QuickLink;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository for the homepage quicklinks: the visible set for the
 * homepage widget and the full set for the admin CRUD.
 */
class QuickLinkRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function find(int $id): ?QuickLink
    {
        return $this->em->find(QuickLink::class, $id);
    }

    /**
     * Links visible on the given day (default today): active, and the day
     * within the inclusive [startDate, endDate] window, where either bound
     * may be null (open-ended). Mirrors QuickLink::isVisibleOn().
     *
     * @return QuickLink[]
     */
    public function findVisible(?\DateTimeInterface $day = null): array
    {
        $day ??= new \DateTimeImmutable('today');

        return $this->em->createQuery(
            'SELECT q FROM App\Entity\QuickLink q'
            . ' WHERE q.active = 1'
            . ' AND (q.startDate IS NULL OR q.startDate <= :day)'
            . ' AND (q.endDate IS NULL OR q.endDate >= :day)'
            . ' ORDER BY q.sortOrder ASC, q.id ASC'
        )->setParameter('day', $day->format('Y-m-d'))->getResult();
    }

    /**
     * Every link, including inactive and out-of-window ones, for the
     * admin list.
     *
     * @return QuickLink[]
     */
    public function findAllOrdered(): array
    {
        return $this->em->createQuery(
            'SELECT q FROM App\Entity\QuickLink q ORDER BY q.sortOrder ASC, q.id ASC'
        )->getResult();
    }
}
