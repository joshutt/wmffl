<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArticleController extends AbstractController
{
    public const PER_PAGE = 24;

    #[Route('/article/{id}', name: 'article_view', requirements: ['id' => '\d+'])]
    public function view(int $id, ArticleRepository $articles): Response
    {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        return $this->render('article/view.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article', name: 'article_latest')]
    public function latest(ArticleRepository $articles): Response
    {
        $article = $articles->findLatestActive();
        if (!$article) {
            throw $this->createNotFoundException('No active articles');
        }

        return $this->render('article/view.html.twig', [
            'article' => $article,
        ]);
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
}
