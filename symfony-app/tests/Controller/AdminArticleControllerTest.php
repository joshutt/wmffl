<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminArticleController;
use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Service\ArticleImageService;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminArticleControllerTest extends TestCase
{
    private const VALID_FORM = [
        'title' => 'Week 5 Recap',
        'caption' => 'The big game',
        'link' => 'images/uploads/recap.jpg',
        'text' => 'It was a wild week.',
        'displayDate' => '2026-07-06T09:30',
        'priority' => '2',
        'author' => '0',
        'active' => '1',
    ];

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
    }

    // ---- GET /admin/articles ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $this->makeRepository());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersAllArticles(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $all = [new Article(), new Article()];
        $repo = $this->makeRepository();
        $repo->method('findAllForAdmin')->willReturn($all);

        $controller->index($auth, $repo);

        $this->assertSame('admin/article/index.html.twig', $controller->renderedView);
        $this->assertSame($all, $controller->renderedParams['articles']);
    }

    // ---- GET/POST /admin/articles/new ----

    public function testNewRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: false);

        $response = $controller->new(new Request(), $auth, $em, $images);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testNewGetRendersFormWithActiveDefault(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $controller->new(new Request(), $auth, $em, $images);

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertTrue($controller->renderedParams['isNew']);
        $this->assertTrue($controller->renderedParams['article']->isActive());
    }

    public function testNewPostPersistsArticle(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(Article::class));
        $em->expects($this->once())->method('flush');

        $response = $controller->new($this->post(self::VALID_FORM), $auth, $em, $images);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_articles', $response->getTargetUrl());
    }

    public function testNewPostWithoutTitleRerendersForm(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $em->expects($this->never())->method('persist');

        $controller->new($this->post(['title' => '  '] + self::VALID_FORM), $auth, $em, $images);

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertContains(['error', 'A title is required'], $controller->flashes);
    }

    public function testNewPostWithBadDateRerendersForm(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $em->expects($this->never())->method('persist');

        $controller->new($this->post(['displayDate' => 'garbage'] + self::VALID_FORM), $auth, $em, $images);

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertContains(['error', 'A valid display date is required'], $controller->flashes);
    }

    public function testNewPostUploadStoresImageAndSetsLink(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $upload = $this->makeUpload();
        $images->expects($this->once())->method('store')
            ->with($upload->getPathname())
            ->willReturn('img/l/abc123');

        $persisted = null;
        $em->method('persist')->willReturnCallback(function (Article $a) use (&$persisted) {
            $persisted = $a;
        });

        $controller->new($this->post(['link' => ''] + self::VALID_FORM, $upload), $auth, $em, $images);

        $this->assertSame('img/l/abc123', $persisted->getLink());
    }

    // ---- GET/POST /admin/articles/{id}/edit ----

    public function testEditRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: false);

        $response = $controller->edit(5, new Request(), $auth, $this->makeRepository(), $em, $images);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testEditThrowsNotFoundForUnknownArticle(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->edit(999, new Request(), $auth, $repo, $em, $images);
    }

    public function testEditGetRendersFormWithArticle(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, new Request(), $auth, $repo, $em, $images);

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['isNew']);
        $this->assertSame($article, $controller->renderedParams['article']);
    }

    public function testEditPostAppliesAllFields(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $em->expects($this->once())->method('flush');

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $response = $controller->edit(5, $this->post(self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertSame('/admin_articles', $response->getTargetUrl());
        $this->assertSame('Week 5 Recap', $article->getTitle());
        $this->assertSame('The big game', $article->getCaption());
        $this->assertSame('images/uploads/recap.jpg', $article->getLink());
        $this->assertSame('It was a wild week.', $article->getText());
        $this->assertSame('2026-07-06 09:30:00', $article->getDisplayDate()->format('Y-m-d H:i:s'));
        $this->assertSame(2, $article->getPriority());
        $this->assertTrue($article->isActive());
        $this->assertNull($article->getAuthor());
    }

    public function testEditPostAssignsAuthor(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $user = new User();
        $em->method('find')->with(User::class, 7)->willReturn($user);

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['author' => '7'] + self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertSame($user, $article->getAuthor());
    }

    public function testEditPostWithoutActiveCheckboxDeactivates(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $article = new Article();
        $article->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $form = self::VALID_FORM;
        unset($form['active']);
        $controller->edit(5, $this->post($form), $auth, $repo, $em, $images);

        $this->assertFalse($article->isActive());
    }

    // ---- lastEdited tracking ----

    public function testEditPostOnActiveArticleSetsLastEdited(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $article = new Article();
        $article->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertNotNull($article->getLastEdited());
        $this->assertEqualsWithDelta(time(), $article->getLastEdited()->getTimestamp(), 5);
    }

    public function testEditPostOnInactiveDraftLeavesLastEditedNull(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $article = new Article();
        $article->setActive(false);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertNull($article->getLastEdited());
    }

    public function testEditPostValidationFailureDoesNotSetLastEdited(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $article = new Article();
        $article->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['title' => ' '] + self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertNull($article->getLastEdited());
    }

    public function testNewPostDoesNotSetLastEdited(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);

        $persisted = null;
        $em->method('persist')->willReturnCallback(function (Article $a) use (&$persisted) {
            $persisted = $a;
        });

        $controller->new($this->post(self::VALID_FORM), $auth, $em, $images);

        $this->assertNull($persisted->getLastEdited());
    }

    // ---- image handling ----

    public function testEditPostPlainPathBypassesImageService(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $images->expects($this->never())->method('store');

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['link' => 'img/l/existinghash'] + self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertSame('img/l/existinghash', $article->getLink());
    }

    public function testEditPostRemoteUrlIsFetchedAndRehosted(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $images->expects($this->once())->method('store')
            ->with('https://example.com/photo.jpg')
            ->willReturn('img/l/rehosted');

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['link' => 'https://example.com/photo.jpg'] + self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertSame('img/l/rehosted', $article->getLink());
    }

    public function testEditPostUploadReplacesExistingLink(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $upload = $this->makeUpload();
        $images->expects($this->once())->method('store')
            ->with($upload->getPathname())
            ->willReturn('img/l/uploaded');

        $article = new Article();
        $article->setLink('img/l/oldhash');
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['link' => 'img/l/oldhash'] + self::VALID_FORM, $upload), $auth, $repo, $em, $images);

        $this->assertSame('img/l/uploaded', $article->getLink());
    }

    public function testEditPostBothUrlAndUploadErrors(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $images->expects($this->never())->method('store');
        $em->expects($this->never())->method('flush');

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(
            5,
            $this->post(['link' => 'https://example.com/photo.jpg'] + self::VALID_FORM, $this->makeUpload()),
            $auth,
            $repo,
            $em,
            $images
        );

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertContains(['error', 'Only include one of image URL and image upload file'], $controller->flashes);
    }

    public function testEditPostBadImageRerendersForm(): void
    {
        [$controller, $auth, $em, $images] = $this->makeController(commissioner: true);
        $images->method('store')->willThrowException(
            new \RuntimeException('Provide a full URL to a JPEG, GIF or PNG image')
        );
        $em->expects($this->never())->method('flush');

        $article = new Article();
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->edit(5, $this->post(['link' => 'https://example.com/not-an-image'] + self::VALID_FORM), $auth, $repo, $em, $images);

        $this->assertSame('admin/article/form.html.twig', $controller->renderedView);
        $this->assertContains(['error', 'Provide a full URL to a JPEG, GIF or PNG image'], $controller->flashes);
    }

    // ---- POST /admin/articles/{id}/toggle ----

    public function testToggleRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->toggle(5, $auth, $this->makeRepository(), $em);

        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testToggleThrowsNotFoundForUnknownArticle(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->toggle(999, $auth, $repo, $em);
    }

    public function testToggleDeactivatesActiveArticle(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);
        $em->expects($this->once())->method('flush');

        $article = new Article();
        $article->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $response = $controller->toggle(5, $auth, $repo, $em);

        $this->assertFalse($article->isActive());
        $this->assertSame('/admin_articles', $response->getTargetUrl());
    }

    public function testToggleActivatesInactiveArticle(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $article = new Article();
        $article->setActive(false);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($article);

        $controller->toggle(5, $auth, $repo, $em);

        $this->assertTrue($article->isActive());
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner): array
    {
        $controller = new class extends AdminArticleController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public array $flashes = [];

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView   = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = [$type, $message];
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $query = $this->createMock(Query::class);
        $query->method('getArrayResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('createQuery')->willReturn($query);

        $images = $this->createMock(ArticleImageService::class);

        return [$controller, $auth, $em, $images];
    }

    private function makeRepository(): ArticleRepository
    {
        return $this->createMock(ArticleRepository::class);
    }

    private function post(array $params, ?UploadedFile $upload = null): Request
    {
        $files = $upload ? ['imageUpload' => $upload] : [];

        return Request::create('/admin/articles/test', 'POST', $params, [], $files);
    }

    private function makeUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'wmffl_test_img');
        file_put_contents($path, 'fake image bytes');
        $this->tempFiles[] = $path;

        return new UploadedFile($path, 'photo.jpg', 'image/jpeg', \UPLOAD_ERR_OK, true);
    }
}
