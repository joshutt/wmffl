<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository for article display and admin management.
 * Ports the queries from football/article/articleUtils.php
 */
class ArticleRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Any article by id, active or not — direct links to inactive
     * articles still render (legacy getArticle() behavior).
     */
    public function find(int $id): ?Article
    {
        return $this->em->find(Article::class, $id);
    }

    /**
     * The most recent active article (legacy getArticle() with no uid)
     */
    public function findLatestActive(): ?Article
    {
        return $this->em->createQuery(
            'SELECT a FROM App\Entity\Article a WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority DESC'
        )->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * A page of active articles with their authors (legacy getArticles());
     * the offset is page * limit.
     *
     * @return Article[]
     */
    public function findActivePage(int $limit, int $page = 0): array
    {
        return $this->em->createQuery(
            'SELECT a, u FROM App\Entity\Article a JOIN a.author u WHERE a.active = 1 ORDER BY a.displayDate DESC, a.priority ASC'
        )->setMaxResults($limit)->setFirstResult($page * $limit)->getResult();
    }

    /**
     * Every article, active or not, for the admin list
     *
     * @return Article[]
     */
    public function findAllForAdmin(): array
    {
        return $this->em->createQuery(
            'SELECT a, u FROM App\Entity\Article a LEFT JOIN a.author u ORDER BY a.displayDate DESC, a.priority ASC'
        )->getResult();
    }
}
