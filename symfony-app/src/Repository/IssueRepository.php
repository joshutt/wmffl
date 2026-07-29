<?php

namespace App\Repository;

use App\Entity\Issue;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Queries backing the rule-proposals list, the ballot, and the admin
 * proposal management screen.
 */
class IssueRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function find(int $id): ?Issue
    {
        return $this->em->find(Issue::class, $id);
    }

    /**
     * Published proposals for a season, ordered by proposal number.
     * IssueNum is a dotted string ("2005.8a"), so ordering is by the
     * numeric season then the raw string (good enough for display).
     *
     * @return Issue[]
     */
    public function findPublishedBySeason(int $season): array
    {
        return $this->em->createQuery(
            'SELECT i FROM App\Entity\Issue i
             WHERE i.season = :season AND i.published = true
             ORDER BY i.issueNum ASC'
        )->setParameter('season', $season)->getResult();
    }

    /**
     * The open, published proposals the given team has an active ballot
     * row for (i.e. it's been put on the ballot and voting is open).
     *
     * @return Issue[]
     */
    public function findOpenBallotIssues(int $teamId): array
    {
        return $this->em->createQuery(
            'SELECT i FROM App\Entity\Issue i
             JOIN App\Entity\Ballot b WITH b.issue = i
             WHERE b.team = :teamId
               AND i.published = true
               AND (i.startDate IS NULL OR i.startDate <= :now)
               AND (i.deadline IS NULL OR i.deadline >= :now)
             ORDER BY i.issueNum ASC'
        )
            ->setParameter('teamId', $teamId)
            ->setParameter('now', new \DateTime())
            ->getResult();
    }

    /**
     * Unpublished proposals awaiting admin approval, newest season first.
     *
     * @return Issue[]
     */
    public function findPendingForAdmin(): array
    {
        return $this->em->createQuery(
            'SELECT i FROM App\Entity\Issue i
             WHERE i.published = false
             ORDER BY i.season DESC, i.issueNum ASC'
        )->getResult();
    }

    /**
     * All proposals for the admin list, newest season first.
     *
     * @return Issue[]
     */
    public function findAllForAdmin(): array
    {
        return $this->em->createQuery(
            'SELECT i FROM App\Entity\Issue i
             ORDER BY i.season DESC, i.issueNum ASC'
        )->getResult();
    }

    /**
     * Distinct seasons that have at least one published proposal, newest
     * first — drives the season selector on the public list.
     *
     * @return int[]
     */
    public function publishedSeasons(): array
    {
        $rows = $this->em->createQuery(
            'SELECT DISTINCT i.season FROM App\Entity\Issue i
             WHERE i.published = true
             ORDER BY i.season DESC'
        )->getScalarResult();

        return array_map(static fn ($r) => (int) $r['season'], $rows);
    }
}
