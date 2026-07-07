<?php

namespace App\Controller\Admin;

use App\Repository\CommentRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Comment moderation: recent comments (including inactive/NULL-active)
 * with an activate/deactivate toggle. Deactivating a parent hides its
 * whole subtree from the article page.
 */
#[Route('/admin/comments')]
class AdminCommentController extends AbstractAdminController
{
    public const PER_PAGE = 50;

    #[Route('', name: 'admin_comments')]
    public function index(
        Request $request,
        AuthenticationService $auth,
        CommentRepository $comments
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $page = max(0, $request->query->getInt('page'));

        // One extra row tells us whether a next page exists
        $rows = $comments->findPageForAdmin(self::PER_PAGE + 1, $page * self::PER_PAGE);

        return $this->render('admin/comment/index.html.twig', [
            'comments' => array_slice($rows, 0, self::PER_PAGE),
            'page' => $page,
            'hasMore' => count($rows) > self::PER_PAGE,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_comments_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(
        int $id,
        Request $request,
        AuthenticationService $auth,
        CommentRepository $comments,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $comment = $comments->find($id);
        if (!$comment) {
            throw $this->createNotFoundException("No comment with id $id");
        }

        // NULL active counts as inactive, so toggling it activates
        $comment->setActive($comment->isActive() !== true);
        $em->flush();

        return $this->redirectToRoute('admin_comments', [
            'page' => max(0, $request->request->getInt('page')),
        ]);
    }
}
