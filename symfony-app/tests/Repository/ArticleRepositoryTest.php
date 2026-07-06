<?php

namespace App\Tests\Repository;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ArticleRepositoryTest extends TestCase
{
    public function testFindDelegatesToEntityManager(): void
    {
        $article = new Article();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('find')
            ->with(Article::class, 42)
            ->willReturn($article);

        $repo = new ArticleRepository($em);

        $this->assertSame($article, $repo->find(42));
    }

    public function testFindLatestActiveQueriesNewestActiveArticle(): void
    {
        $article = new Article();

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setMaxResults')->with(1)->willReturnSelf();
        $query->method('getOneOrNullResult')->willReturn($article);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT a FROM App\Entity\Article a WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority DESC')
            ->willReturn($query);

        $repo = new ArticleRepository($em);

        $this->assertSame($article, $repo->findLatestActive());
    }

    public function testFindActivePageQueriesActiveArticlesWithAuthors(): void
    {
        $articles = [new Article(), new Article()];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setMaxResults')->with(24)->willReturnSelf();
        $query->expects($this->once())->method('setFirstResult')->with(0)->willReturnSelf();
        $query->method('getResult')->willReturn($articles);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT a, u FROM App\Entity\Article a JOIN a.author u WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority ASC')
            ->willReturn($query);

        $repo = new ArticleRepository($em);

        $this->assertSame($articles, $repo->findActivePage(24));
    }

    public function testFindActivePageOffsetsByPageTimesLimit(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setMaxResults')->with(24)->willReturnSelf();
        $query->expects($this->once())->method('setFirstResult')->with(48)->willReturnSelf();
        $query->method('getResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        $repo = new ArticleRepository($em);
        $repo->findActivePage(24, 2);
    }

    public function testFindAllForAdminIncludesArticlesWithoutAuthors(): void
    {
        $articles = [new Article()];

        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn($articles);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT a, u FROM App\Entity\Article a LEFT JOIN a.author u ORDER BY a.displayDate DESC, a.priority ASC')
            ->willReturn($query);

        $repo = new ArticleRepository($em);

        $this->assertSame($articles, $repo->findAllForAdmin());
    }
}
