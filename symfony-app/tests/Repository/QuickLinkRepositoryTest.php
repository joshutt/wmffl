<?php

namespace App\Tests\Repository;

use App\Entity\QuickLink;
use App\Repository\QuickLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class QuickLinkRepositoryTest extends TestCase
{
    public function testFindDelegatesToEntityManager(): void
    {
        $link = new QuickLink();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('find')
            ->with(QuickLink::class, 7)
            ->willReturn($link);

        $repo = new QuickLinkRepository($em);

        $this->assertSame($link, $repo->find(7));
    }

    public function testFindVisibleFiltersActiveWindowedLinksInOrder(): void
    {
        $links = [new QuickLink(), new QuickLink()];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setParameter')
            ->with('day', '2026-07-17')
            ->willReturnSelf();
        $query->method('getResult')->willReturn($links);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT q FROM App\Entity\QuickLink q'
                . ' WHERE q.active = 1'
                . ' AND (q.startDate IS NULL OR q.startDate <= :day)'
                . ' AND (q.endDate IS NULL OR q.endDate >= :day)'
                . ' ORDER BY q.sortOrder ASC, q.id ASC')
            ->willReturn($query);

        $repo = new QuickLinkRepository($em);

        $this->assertSame($links, $repo->findVisible(new \DateTimeImmutable('2026-07-17')));
    }

    public function testFindVisibleDefaultsToToday(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setParameter')
            ->with('day', (new \DateTimeImmutable('today'))->format('Y-m-d'))
            ->willReturnSelf();
        $query->method('getResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        (new QuickLinkRepository($em))->findVisible();
    }

    public function testFindAllOrderedIncludesInactiveLinks(): void
    {
        $links = [new QuickLink()];

        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn($links);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT q FROM App\Entity\QuickLink q ORDER BY q.sortOrder ASC, q.id ASC')
            ->willReturn($query);

        $repo = new QuickLinkRepository($em);

        $this->assertSame($links, $repo->findAllOrdered());
    }
}
