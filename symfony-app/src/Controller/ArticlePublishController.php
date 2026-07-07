<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Service\ArticleImageService;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Member article publishing: write → preview → Edit/Publish.
 * Ports football/article/publish.php, process.php, preview.php, confirm.php,
 * but edits in place on one article row instead of legacy's
 * delete-and-reinsert. Drafts stay active = 0 until the author confirms;
 * an already-published article stays live while being edited.
 */
class ArticlePublishController extends AbstractController
{
    #[Route('/article/publish', name: 'article_publish', methods: ['GET'])]
    public function form(
        Request $request,
        AuthenticationService $auth,
        ArticleRepository $articles
    ): Response {
        if (!$auth->isLoggedIn()) {
            return $this->render('article/publish.html.twig', ['isLoggedIn' => false]);
        }

        $form = ['title' => '', 'url' => '', 'caption' => '', 'text' => ''];
        $draft = null;

        $draftId = $request->query->getInt('draft');
        if ($draftId > 0) {
            $draft = $this->findOwnedArticle($draftId, $auth, $articles);
            $form = [
                'title' => $draft->getTitle(),
                'url' => '',
                'caption' => $draft->getCaption(),
                'text' => $draft->getText(),
            ];
        }

        return $this->render('article/publish.html.twig', [
            'isLoggedIn' => true,
            'form' => $form,
            'draft' => $draft,
        ]);
    }

    #[Route('/article/publish', name: 'article_publish_post', methods: ['POST'])]
    public function submit(
        Request $request,
        AuthenticationService $auth,
        ArticleRepository $articles,
        EntityManagerInterface $em,
        ArticleImageService $images,
        HtmlSanitizerInterface $appArticle
    ): Response {
        if (!$auth->isLoggedIn()) {
            return $this->render('article/publish.html.twig', ['isLoggedIn' => false]);
        }

        if (!$this->isCsrfTokenValid('article_publish', (string) $request->getPayload()->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }

        $draft = null;
        $draftId = $request->request->getInt('draft');
        if ($draftId > 0) {
            $draft = $this->findOwnedArticle($draftId, $auth, $articles);
        }

        $title = trim($request->request->get('title', ''));
        $url = trim($request->request->get('url', ''));
        $upload = $request->files->get('upload');
        $caption = trim($request->request->get('caption', ''));
        $text = trim($request->request->get('text', ''));

        // Validations ported from process.php
        $errors = [];
        if (strlen($title) < 2) {
            $errors[] = 'Must include a title';
        } elseif (strlen($title) >= 75) {
            $errors[] = 'Title can\'t be longer than 75 characters';
        }
        if ($url !== '' && $upload) {
            $errors[] = 'Only include one of image URL and image upload file';
        } elseif ($url === '' && !$upload && !($draft && $draft->getLink())) {
            // When editing, no image input keeps the stored one
            $errors[] = 'Must include either an image URL or upload file';
        }
        if (strlen($text) < 200) {
            $errors[] = 'Must include an article of at least 200 characters';
        }

        $link = null;
        if (!$errors) {
            try {
                if ($upload) {
                    $link = $images->store($upload->getPathname());
                } elseif ($url !== '') {
                    $link = $images->store($url);
                }
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->render('article/publish.html.twig', [
                'isLoggedIn' => true,
                'form' => ['title' => $title, 'url' => $url, 'caption' => $caption, 'text' => $text],
                'draft' => $draft,
            ]);
        }

        if (!$draft) {
            $draft = new Article();
            $draft->setActive(false);
            $draft->setPriority(0);
            $draft->setAuthor($em->find(User::class, $auth->getUserId()));
            // Placeholder as in legacy; overwritten at first publish
            $draft->setDisplayDate(new \DateTime());
            $em->persist($draft);
        } elseif ($draft->isActive()) {
            // Saving an already-published article is an edit of live content
            $draft->setLastEdited(new \DateTime());
        }

        $draft->setTitle($title);
        $draft->setCaption($caption ?: null);
        // Strip everything the article sanitizer disallows at save time, so the
        // stored HTML is already safe rather than relying only on render-time
        // sanitizing. Keeps the full TinyMCE formatting set (see app.article).
        $draft->setText($appArticle->sanitize($text));
        if ($link !== null) {
            $draft->setLink($link);
        }

        $em->flush();

        return $this->redirectToRoute('article_preview', ['id' => $draft->getId()]);
    }

    #[Route('/article/preview/{id}', name: 'article_preview', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function preview(
        int $id,
        AuthenticationService $auth,
        ArticleRepository $articles
    ): Response {
        $article = $this->findOwnedArticle($id, $auth, $articles);

        return $this->render('article/preview.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/confirm/{id}', name: 'article_confirm', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function confirm(
        int $id,
        Request $request,
        AuthenticationService $auth,
        ArticleRepository $articles,
        EntityManagerInterface $em
    ): Response {
        if (!$this->isCsrfTokenValid('article_confirm', (string) $request->getPayload()->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }

        $article = $this->findOwnedArticle($id, $auth, $articles);

        if ($request->request->has('Edit')) {
            return $this->redirectToRoute('article_publish', ['draft' => $article->getId()]);
        }

        if (!$article->isActive()) {
            // First publish: this is when the article actually goes live,
            // so homepage/list ordering uses this moment. Re-publishing an
            // edited live article keeps its original date.
            $article->setDisplayDate(new \DateTime());
            $article->setActive(true);
        }
        $em->flush();

        return $this->redirect('/');
    }

    /**
     * Legacy POST targets (football/article/process.php, confirm.php)
     * for stale tabs/bookmarks: send them back to the publish form.
     */
    #[Route('/article/process', name: 'article_process_legacy')]
    #[Route('/article/confirm', name: 'article_confirm_legacy')]
    public function legacyRedirect(): Response
    {
        return $this->redirectToRoute('article_publish');
    }

    /**
     * The article by id when the current user may work on it: its author or
     * a commissioner. 404 for unknown ids, 403 for everyone else — legacy
     * did no ownership check at all (anyone could activate any article).
     */
    private function findOwnedArticle(
        int $id,
        AuthenticationService $auth,
        ArticleRepository $articles
    ): Article {
        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        $isAuthor = $article->getAuthor() && $article->getAuthor()->getId() === $auth->getUserId();
        if (!$isAuthor && !$auth->isCommissioner()) {
            throw new AccessDeniedHttpException('Only the author or a commissioner may do this');
        }

        return $article;
    }
}
