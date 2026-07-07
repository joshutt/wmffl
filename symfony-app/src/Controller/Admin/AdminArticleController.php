<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Service\ArticleImageService;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/articles')]
class AdminArticleController extends AbstractAdminController
{
    #[Route('', name: 'admin_articles')]
    public function index(AuthenticationService $auth, ArticleRepository $articles): Response
    {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/article/index.html.twig', [
            'articles' => $articles->findAllForAdmin(),
        ]);
    }

    #[Route('/new', name: 'admin_articles_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
        ArticleImageService $images,
        HtmlSanitizerInterface $appArticle
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $article = new Article();
        $article->setDisplayDate(new \DateTime());
        $article->setActive(true);

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_article');
        }
        if ($request->isMethod('POST') && $this->applyForm($request, $article, $em, $images, $appArticle)) {
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article created');

            return $this->redirectToRoute('admin_articles');
        }

        return $this->renderEditor($article, $em, true);
    }

    #[Route('/{id}/edit', name: 'admin_articles_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AuthenticationService $auth,
        ArticleRepository $articles,
        EntityManagerInterface $em,
        ArticleImageService $images,
        HtmlSanitizerInterface $appArticle
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_article');
        }
        $wasActive = (bool) $article->isActive();
        if ($request->isMethod('POST') && $this->applyForm($request, $article, $em, $images, $appArticle)) {
            if ($wasActive) {
                // Editing live content; drafts never get a lastEdited date
                $article->setLastEdited(new \DateTime());
            }
            $em->flush();
            $this->addFlash('success', 'Article updated');

            return $this->redirectToRoute('admin_articles');
        }

        return $this->renderEditor($article, $em, false);
    }

    #[Route('/{id}/toggle', name: 'admin_articles_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(
        int $id,
        Request $request,
        AuthenticationService $auth,
        ArticleRepository $articles,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_article_toggle');

        $article = $articles->find($id);
        if (!$article) {
            throw $this->createNotFoundException("No article with id $id");
        }

        $article->setActive(!$article->isActive());
        $em->flush();

        return $this->redirectToRoute('admin_articles');
    }

    /**
     * Copy submitted fields onto the article. Returns false (with an error
     * flash) when validation fails, leaving the article unchanged.
     */
    private function applyForm(
        Request $request,
        Article $article,
        EntityManagerInterface $em,
        ArticleImageService $images,
        HtmlSanitizerInterface $appArticle
    ): bool {
        $title = trim($request->request->get('title', ''));
        if ($title === '') {
            $this->addFlash('error', 'A title is required');

            return false;
        }

        $displayDate = \DateTime::createFromFormat('Y-m-d\TH:i:s|', $request->request->get('displayDate', ''))
            ?: \DateTime::createFromFormat('Y-m-d\TH:i|', $request->request->get('displayDate', ''));
        if (!$displayDate) {
            $this->addFlash('error', 'A valid display date is required');

            return false;
        }

        // Image handling matches the member publish flow: an upload or a
        // remote URL is resized and stored in the images table; a plain
        // stored path (e.g. img/l/...) passes through untouched.
        $upload = $request->files->get('imageUpload');
        $link = trim($request->request->get('link', ''));
        $linkIsUrl = (bool) preg_match('#^https?://#i', $link);

        if ($upload && $linkIsUrl) {
            $this->addFlash('error', 'Only include one of image URL and image upload file');

            return false;
        }

        try {
            if ($upload) {
                $link = $images->store($upload->getPathname());
            } elseif ($linkIsUrl) {
                $link = $images->store($link);
            }
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }

        $article->setTitle($title);
        $article->setCaption(trim($request->request->get('caption', '')) ?: null);
        $article->setLink($link ?: null);
        // Sanitize at save time so stored HTML is already safe (keeps the full
        // TinyMCE formatting set; see the app.article sanitizer config).
        $text = $appArticle->sanitize($request->request->get('text', ''));
        $article->setText($text ?: null);
        $article->setDisplayDate($displayDate);
        $article->setPriority($request->request->getInt('priority'));
        $article->setActive($request->request->has('active'));

        $authorId = $request->request->getInt('author');
        $article->setAuthor($authorId > 0 ? $em->find(User::class, $authorId) : null);

        return true;
    }

    private function renderEditor(Article $article, EntityManagerInterface $em, bool $isNew): Response
    {
        // Scalar select: hydrating User entities trips on legacy rows whose
        // active column holds '' (not a valid ActiveEnum backing value)
        $users = $em->createQuery(
            'SELECT u.id AS id, u.name AS name FROM App\Entity\User u ORDER BY u.name ASC'
        )->getArrayResult();

        return $this->render('admin/article/form.html.twig', [
            'article' => $article,
            'users' => $users,
            'isNew' => $isNew,
        ]);
    }
}
