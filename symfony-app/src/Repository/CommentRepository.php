<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repository for article comments: the threaded display on the article
 * view page and the admin moderation list.
 */
class CommentRepository
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function find(int $id): ?Comment
    {
        return $this->em->find(Comment::class, $id);
    }

    /**
     * Active comments for an article with their authors, oldest first.
     * comments.active is nullable; NULL counts as inactive, so only
     * active = 1 rows are returned.
     *
     * @return Comment[]
     */
    public function findActiveByArticle(int $articleId): array
    {
        return $this->em->createQuery(
            'SELECT c, u FROM App\Entity\Comment c JOIN c.author u WHERE c.articleId = :articleId AND c.active = 1 ORDER BY c.dateCreated ASC'
        )->setParameter('articleId', $articleId)->getResult();
    }

    /**
     * The article's active comments as a thread: a list of
     * ['comment' => Comment, 'children' => <same shape>] roots. A comment
     * whose parent is not visible (inactive or NULL-active) is dropped with
     * its whole subtree — children of hidden parents are not re-parented.
     *
     * @return array<array{comment: Comment, children: array}>
     */
    public function findThreadByArticle(int $articleId): array
    {
        $comments = $this->findActiveByArticle($articleId);

        $byParent = [];
        foreach ($comments as $comment) {
            $byParent[$comment->getParent()?->getId() ?? 0][] = $comment;
        }

        $build = function (Comment $comment) use (&$build, $byParent): array {
            return [
                'comment' => $comment,
                'children' => array_map($build, $byParent[$comment->getId()] ?? []),
            ];
        };

        // Only roots (no parent) seed the tree, so a comment under a hidden
        // parent — absent from the active set — never renders
        return array_map($build, $byParent[0] ?? []);
    }

    /**
     * A slice of recent comments for moderation, newest first, including
     * inactive and NULL-active rows.
     *
     * @return Comment[]
     */
    public function findPageForAdmin(int $limit, int $offset = 0): array
    {
        return $this->em->createQuery(
            'SELECT c, u FROM App\Entity\Comment c LEFT JOIN c.author u ORDER BY c.dateCreated DESC'
        )->setMaxResults($limit)->setFirstResult($offset)->getResult();
    }
}
