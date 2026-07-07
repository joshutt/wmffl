<?php

namespace App\Tests\Repository;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentRepositoryTest extends TestCase
{
    public function testFindDelegatesToEntityManager(): void
    {
        $comment = new Comment();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('find')
            ->with(Comment::class, 42)
            ->willReturn($comment);

        $repo = new CommentRepository($em);

        $this->assertSame($comment, $repo->find(42));
    }

    public function testFindActiveByArticleQueriesActiveCommentsOldestFirst(): void
    {
        $comments = [new Comment(), new Comment()];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setParameter')
            ->with('articleId', 42)
            ->willReturnSelf();
        $query->method('getResult')->willReturn($comments);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT c, u FROM App\Entity\Comment c JOIN c.author u WHERE c.articleId = :articleId AND c.active = 1 ORDER BY c.dateCreated ASC')
            ->willReturn($query);

        $repo = new CommentRepository($em);

        $this->assertSame($comments, $repo->findActiveByArticle(42));
    }

    // ---- findThreadByArticle ----

    public function testThreadNestsChildrenUnderParents(): void
    {
        $root = $this->makeComment(1);
        $child = $this->makeComment(2, parent: $root);
        $grandchild = $this->makeComment(3, parent: $child);
        $otherRoot = $this->makeComment(4);

        $repo = $this->makeRepositoryReturning([$root, $child, $grandchild, $otherRoot]);

        $tree = $repo->findThreadByArticle(42);

        $this->assertCount(2, $tree);
        $this->assertSame($root, $tree[0]['comment']);
        $this->assertSame($otherRoot, $tree[1]['comment']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertSame($child, $tree[0]['children'][0]['comment']);
        $this->assertSame($grandchild, $tree[0]['children'][0]['children'][0]['comment']);
        $this->assertSame([], $tree[1]['children']);
    }

    public function testThreadDropsSubtreeOfHiddenParent(): void
    {
        // The parent (id 5) is inactive, so the active query never returns
        // it; its reply and the reply's reply must not surface as roots
        $hiddenParent = $this->makeComment(5);
        $orphan = $this->makeComment(6, parent: $hiddenParent);
        $orphanChild = $this->makeComment(7, parent: $orphan);
        $visibleRoot = $this->makeComment(8);

        $repo = $this->makeRepositoryReturning([$orphan, $orphanChild, $visibleRoot]);

        $tree = $repo->findThreadByArticle(42);

        $this->assertCount(1, $tree);
        $this->assertSame($visibleRoot, $tree[0]['comment']);
    }

    public function testThreadOfNoCommentsIsEmpty(): void
    {
        $repo = $this->makeRepositoryReturning([]);

        $this->assertSame([], $repo->findThreadByArticle(42));
    }

    // ---- findPageForAdmin ----

    public function testFindPageForAdminIncludesInactiveCommentsNewestFirst(): void
    {
        $comments = [new Comment(), new Comment()];

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setMaxResults')->with(51)->willReturnSelf();
        $query->expects($this->once())->method('setFirstResult')->with(0)->willReturnSelf();
        $query->method('getResult')->willReturn($comments);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('createQuery')
            ->with('SELECT c, u FROM App\Entity\Comment c LEFT JOIN c.author u ORDER BY c.dateCreated DESC')
            ->willReturn($query);

        $repo = new CommentRepository($em);

        $this->assertSame($comments, $repo->findPageForAdmin(51));
    }

    public function testFindPageForAdminAppliesOffset(): void
    {
        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('setMaxResults')->with(51)->willReturnSelf();
        $query->expects($this->once())->method('setFirstResult')->with(100)->willReturnSelf();
        $query->method('getResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        $repo = new CommentRepository($em);
        $repo->findPageForAdmin(51, 100);
    }

    // ---- Helpers ----

    private function makeComment(int $id, ?Comment $parent = null): Comment
    {
        $comment = new Comment();
        $ref = new \ReflectionProperty(Comment::class, 'id');
        $ref->setValue($comment, $id);
        $comment->setParent($parent);
        return $comment;
    }

    private function makeRepositoryReturning(array $comments): CommentRepository
    {
        $query = $this->createMock(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getResult')->willReturn($comments);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        return new CommentRepository($em);
    }
}
