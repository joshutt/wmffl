<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    public const PER_PAGE = 24;

    #[Route('/article/{id}', name: 'article_view', requirements: ['id' => '\d+'])]
    public function view(
        int $id,
        ArticleRepository $articles,
        CommentRepository $comments,
        AuthenticationService $auth
    ): Response {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        return $this->renderArticlePage($article, $comments, $auth);
    }

    #[Route('/article', name: 'article_latest')]
    public function latest(
        ArticleRepository $articles,
        CommentRepository $comments,
        AuthenticationService $auth
    ): Response {
        $article = $articles->findLatestActive();
        if (!$article) {
            throw $this->createNotFoundException('No active articles');
        }

        return $this->renderArticlePage($article, $comments, $auth);
    }

    #[Route('/articles', name: 'article_list')]
    public function list(
        Request $request,
        ArticleRepository $articles,
        AuthenticationService $auth
    ): Response {
        $page = max(0, $request->query->getInt('page'));

        return $this->render('article/list.html.twig', [
            'articles' => $articles->findActivePage(self::PER_PAGE, $page),
            'page' => $page,
            'isLoggedIn' => $auth->isLoggedIn(),
        ]);
    }

    /**
     * Post a comment (or a reply, via the parent field) on an article.
     */
    #[Route('/article/{id}/comment', name: 'article_comment', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function comment(
        int $id,
        Request $request,
        ArticleRepository $articles,
        CommentRepository $comments,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        if (!$auth->isLoggedIn()) {
            $this->addFlash('error', 'You must be logged in to comment');

            return $this->redirectToRoute('article_view', ['id' => $id]);
        }

        $text = trim($request->request->get('text', ''));
        if ($text === '') {
            $this->addFlash('error', 'A comment cannot be empty');

            return $this->redirectToRoute('article_view', ['id' => $id]);
        }

        $parent = null;
        $parentId = $request->request->getInt('parent');
        if ($parentId > 0) {
            $parent = $comments->find($parentId);
            // NULL active counts as inactive: no replying to hidden comments
            if (!$parent || $parent->isActive() !== true || $parent->getArticleId() !== $id) {
                $this->addFlash('error', 'That comment cannot be replied to');

                return $this->redirectToRoute('article_view', ['id' => $id]);
            }
        }

        $comment = new Comment();
        $comment->setArticleId($id);
        $comment->setCommentText($text);
        $comment->setAuthor($em->find(User::class, $auth->getUserId()));
        $comment->setDateCreated(new \DateTime());
        $comment->setActive(true);
        $comment->setParent($parent);

        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('article_view', [
            'id' => $id,
            '_fragment' => 'comment-' . $comment->getId(),
        ]);
    }

    /**
     * Legacy URL: /article/view?uid=N (football/article/view.php)
     */
    #[Route('/article/view', name: 'article_view_legacy')]
    public function legacyView(Request $request): Response
    {
        $uid = $request->query->getInt('uid');
        if ($uid > 0) {
            return $this->redirectToRoute('article_view', ['id' => $uid], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->redirectToRoute('article_latest', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Legacy URL: /article/list?start=N (football/article/list.php)
     */
    #[Route('/article/list', name: 'article_list_legacy')]
    public function legacyList(Request $request): Response
    {
        $start = $request->query->getInt('start');
        $params = $start > 0 ? ['page' => $start] : [];

        return $this->redirectToRoute('article_list', $params, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * The article view page with its comment thread; the Edit link shows
     * only for the article's author or a commissioner.
     */
    private function renderArticlePage(
        Article $article,
        CommentRepository $comments,
        AuthenticationService $auth
    ): Response {
        $isAuthor = $article->getAuthor() && $article->getAuthor()->getId() === $auth->getUserId();

        return $this->render('article/view.html.twig', [
            'article' => $article,
            'comments' => $comments->findThreadByArticle($article->getId()),
            'isLoggedIn' => $auth->isLoggedIn(),
            'canEdit' => $isAuthor || $auth->isCommissioner(),
        ]);
    }
}
