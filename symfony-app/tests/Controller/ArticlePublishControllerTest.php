<?php

namespace App\Tests\Controller;

use App\Controller\ArticlePublishController;
use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Service\ArticleImageService;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class ArticlePublishControllerTest extends TestCase
{
    private const LONG_BODY = 'A body long enough to pass the two-hundred-character minimum. '
        . 'It keeps going with more filler prose about the fantasy football league, its teams, '
        . 'its trades and its rivalries, until the length requirement is comfortably met for the tests.';

    private const VALID_FORM = [
        'title' => 'Week 5 Recap',
        'url' => 'https://example.com/photo.jpg',
        'caption' => 'The big game',
        'text' => self::LONG_BODY,
    ];

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
    }

    // ---- GET /article/publish ----

    public function testFormShowsLoggedOutMessageToAnonymousVisitors(): void
    {
        $controller = $this->makeController();

        $controller->form(new Request(), $this->makeAuth(false), $this->makeRepository());

        $this->assertSame('article/publish.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    public function testFormRendersEmptyFormForMembers(): void
    {
        $controller = $this->makeController();

        $controller->form(new Request(), $this->makeAuth(true, userId: 7), $this->makeRepository());

        $this->assertSame('article/publish.html.twig', $controller->renderedView);
        $this->assertTrue($controller->renderedParams['isLoggedIn']);
        $this->assertNull($controller->renderedParams['draft']);
        $this->assertSame(
            ['title' => '', 'url' => '', 'caption' => '', 'text' => ''],
            $controller->renderedParams['form']
        );
    }

    public function testFormPrefillsFromOwnDraft(): void
    {
        $controller = $this->makeController();
        $draft = $this->makeArticle(9, author: $this->makeUser(7));
        $draft->setTitle('My Draft')->setCaption('Cap')->setText('Body text')->setLink('img/l/abc');

        $controller->form(
            new Request(query: ['draft' => '9']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($draft)
        );

        $this->assertSame($draft, $controller->renderedParams['draft']);
        $this->assertSame(
            ['title' => 'My Draft', 'url' => '', 'caption' => 'Cap', 'text' => 'Body text'],
            $controller->renderedParams['form']
        );
    }

    public function testFormThrowsNotFoundForUnknownDraft(): void
    {
        $controller = $this->makeController();

        $this->expectException(NotFoundHttpException::class);
        $controller->form(
            new Request(query: ['draft' => '9']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository()
        );
    }

    public function testFormDeniesOtherMembersDrafts(): void
    {
        $controller = $this->makeController();
        $draft = $this->makeArticle(9, author: $this->makeUser(7));

        $this->expectException(AccessDeniedHttpException::class);
        $controller->form(
            new Request(query: ['draft' => '9']),
            $this->makeAuth(true, userId: 8),
            $this->makeRepository($draft)
        );
    }

    public function testFormAllowsCommissionerOnAnyDraft(): void
    {
        $controller = $this->makeController();
        $draft = $this->makeArticle(9, author: $this->makeUser(7));

        $controller->form(
            new Request(query: ['draft' => '9']),
            $this->makeAuth(true, userId: 8, commissioner: true),
            $this->makeRepository($draft)
        );

        $this->assertSame($draft, $controller->renderedParams['draft']);
    }

    public function testFormAllowsAuthorOnPublishedArticle(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(9, author: $this->makeUser(7));
        $article->setActive(true);

        $controller->form(
            new Request(query: ['draft' => '9']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($article)
        );

        $this->assertSame($article, $controller->renderedParams['draft']);
    }

    // ---- POST /article/publish: auth + validations ----

    public function testSubmitShowsLoggedOutMessageToAnonymousVisitors(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $em->expects($this->never())->method('persist');

        $controller->submit(
            $this->post(self::VALID_FORM),
            $this->makeAuth(false),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertSame('article/publish.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    public function testSubmitRejectsMissingTitle(): void
    {
        $this->assertValidationError(
            ['title' => '   '] + self::VALID_FORM,
            'Must include a title'
        );
    }

    public function testSubmitRejectsOneCharacterTitle(): void
    {
        $this->assertValidationError(
            ['title' => ' X '] + self::VALID_FORM,
            'Must include a title'
        );
    }

    public function testSubmitRejectsLongTitle(): void
    {
        $this->assertValidationError(
            ['title' => str_repeat('t', 75)] + self::VALID_FORM,
            'Title can\'t be longer than 75 characters'
        );
    }

    public function testSubmitRejectsMissingImageSource(): void
    {
        $this->assertValidationError(
            ['url' => ''] + self::VALID_FORM,
            'Must include either an image URL or upload file'
        );
    }

    public function testSubmitRejectsBothImageSources(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $images->expects($this->never())->method('store');

        $controller->submit(
            $this->post(self::VALID_FORM, $this->makeUpload()),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertContains(
            ['error', 'Only include one of image URL and image upload file'],
            $controller->flashes
        );
    }

    public function testSubmitRejectsShortBody(): void
    {
        $this->assertValidationError(
            ['text' => str_repeat('x', 100)] + self::VALID_FORM,
            'Must include an article of at least 200 characters'
        );
    }

    public function testSubmitRerendersWithEnteredValuesOnFailure(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();

        $controller->submit(
            $this->post(['title' => '', 'url' => 'http://x.test/i.jpg', 'caption' => 'Cap', 'text' => 'short']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertSame('article/publish.html.twig', $controller->renderedView);
        $this->assertSame(
            ['title' => '', 'url' => 'http://x.test/i.jpg', 'caption' => 'Cap', 'text' => 'short'],
            $controller->renderedParams['form']
        );
    }

    public function testSubmitCollectsAllValidationErrorsAtOnce(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();

        $controller->submit(
            $this->post(['title' => '', 'url' => '', 'caption' => '', 'text' => '']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertCount(3, $controller->flashes);
    }

    public function testSubmitRejectsBadImage(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $images->method('store')->willThrowException(
            new \RuntimeException('Provide a full URL to a JPEG, GIF or PNG image')
        );
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $controller->submit(
            $this->post(self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertContains(
            ['error', 'Provide a full URL to a JPEG, GIF or PNG image'],
            $controller->flashes
        );
    }

    // ---- POST /article/publish: creating a draft ----

    public function testSubmitCreatesDraftAndRedirectsToPreview(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $user = $this->makeUser(7);
        $em->method('find')->with(User::class, 7)->willReturn($user);
        $images->method('store')->willReturn('img/l/abc123');

        $persisted = null;
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(function (Article $a) use (&$persisted) {
                $persisted = $a;
            });
        $em->expects($this->once())->method('flush');

        $response = $controller->submit(
            $this->post(self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertFalse($persisted->isActive());
        $this->assertSame(0, $persisted->getPriority());
        $this->assertSame($user, $persisted->getAuthor());
        $this->assertSame('Week 5 Recap', $persisted->getTitle());
        $this->assertSame('The big game', $persisted->getCaption());
        $this->assertSame('img/l/abc123', $persisted->getLink());
        $this->assertSame(self::LONG_BODY, $persisted->getText());
        $this->assertNull($persisted->getLastEdited());
        $this->assertNotNull($persisted->getDisplayDate());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringStartsWith('/article_preview', $response->getTargetUrl());
    }

    public function testSubmitStoresImageFromUrl(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $images->expects($this->once())->method('store')
            ->with('https://example.com/photo.jpg')
            ->willReturn('img/l/fromurl');

        $controller->submit(
            $this->post(self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );
    }

    public function testSubmitStoresImageFromUpload(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $upload = $this->makeUpload();
        $images->expects($this->once())->method('store')
            ->with($upload->getPathname())
            ->willReturn('img/l/fromupload');

        $controller->submit(
            $this->post(['url' => ''] + self::VALID_FORM, $upload),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );
    }

    // ---- POST /article/publish: editing an existing row ----

    public function testSubmitUpdatesExistingDraftInPlace(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $images->method('store')->willReturn('img/l/newimage');
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $draft = $this->makeArticle(9, author: $this->makeUser(7));
        $draft->setTitle('Old Title')->setLink('img/l/old');

        $response = $controller->submit(
            $this->post(['draft' => '9'] + self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($draft),
            $em,
            $images
        );

        $this->assertSame('Week 5 Recap', $draft->getTitle());
        $this->assertSame('img/l/newimage', $draft->getLink());
        $this->assertFalse($draft->isActive());
        $this->assertNull($draft->getLastEdited());
        $this->assertSame('/article_preview?id=9', $response->getTargetUrl());
    }

    public function testSubmitWithoutImageKeepsStoredLink(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $images->expects($this->never())->method('store');

        $draft = $this->makeArticle(9, author: $this->makeUser(7));
        $draft->setLink('img/l/existing');

        $controller->submit(
            $this->post(['draft' => '9', 'url' => ''] + self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($draft),
            $em,
            $images
        );

        $this->assertSame('img/l/existing', $draft->getLink());
        $this->assertEmpty($controller->flashes);
    }

    public function testSubmitOnPublishedArticleStaysLiveAndSetsLastEdited(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $images->method('store')->willReturn('img/l/new');

        $article = $this->makeArticle(9, author: $this->makeUser(7));
        $article->setActive(true);
        $article->setLink('img/l/existing');

        $controller->submit(
            $this->post(['draft' => '9'] + self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($article),
            $em,
            $images
        );

        $this->assertTrue($article->isActive());
        $this->assertNotNull($article->getLastEdited());
        $this->assertEqualsWithDelta(time(), $article->getLastEdited()->getTimestamp(), 5);
    }

    public function testSubmitDeniesOtherMembersDrafts(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $draft = $this->makeArticle(9, author: $this->makeUser(7));

        $this->expectException(AccessDeniedHttpException::class);
        $controller->submit(
            $this->post(['draft' => '9'] + self::VALID_FORM),
            $this->makeAuth(true, userId: 8),
            $this->makeRepository($draft),
            $em,
            $images
        );
    }

    public function testSubmitValidationFailureKeepsDraftUntouched(): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $em->expects($this->never())->method('flush');

        $draft = $this->makeArticle(9, author: $this->makeUser(7));
        $draft->setTitle('Old Title')->setLink('img/l/old');

        $controller->submit(
            $this->post(['draft' => '9', 'text' => 'too short'] + self::VALID_FORM),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($draft),
            $em,
            $images
        );

        $this->assertSame('Old Title', $draft->getTitle());
        $this->assertSame($draft, $controller->renderedParams['draft']);
    }

    // ---- GET /article/preview/{id} ----

    public function testPreviewThrowsNotFoundForUnknownId(): void
    {
        $controller = $this->makeController();

        $this->expectException(NotFoundHttpException::class);
        $controller->preview(999, $this->makeAuth(true, userId: 7), $this->makeRepository());
    }

    public function testPreviewDeniesNonAuthorNonCommissioner(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $this->expectException(AccessDeniedHttpException::class);
        $controller->preview(9, $this->makeAuth(true, userId: 8), $this->makeRepository($article));
    }

    public function testPreviewDeniesAnonymousVisitors(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $this->expectException(AccessDeniedHttpException::class);
        $controller->preview(9, $this->makeAuth(false), $this->makeRepository($article));
    }

    public function testPreviewRendersForAuthor(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $controller->preview(9, $this->makeAuth(true, userId: 7), $this->makeRepository($article));

        $this->assertSame('article/preview.html.twig', $controller->renderedView);
        $this->assertSame($article, $controller->renderedParams['article']);
    }

    public function testPreviewRendersForCommissioner(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $controller->preview(9, $this->makeAuth(true, userId: 8, commissioner: true), $this->makeRepository($article));

        $this->assertSame('article/preview.html.twig', $controller->renderedView);
    }

    // ---- POST /article/confirm/{id} ----

    public function testConfirmThrowsNotFoundForUnknownId(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();

        $this->expectException(NotFoundHttpException::class);
        $controller->confirm(999, $this->post(['Publish' => 'Publish']), $this->makeAuth(true, userId: 7), $this->makeRepository(), $em);
    }

    public function testConfirmDeniesNonAuthorNonCommissioner(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();
        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $this->expectException(AccessDeniedHttpException::class);
        $controller->confirm(9, $this->post(['Publish' => 'Publish']), $this->makeAuth(true, userId: 8), $this->makeRepository($article), $em);
    }

    public function testConfirmEditRedirectsToPrefilledFormWithoutDeleting(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();
        $em->expects($this->never())->method('remove');
        $em->expects($this->never())->method('flush');

        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $response = $controller->confirm(
            9,
            $this->post(['Edit' => 'Edit']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($article),
            $em
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/article_publish?draft=9', $response->getTargetUrl());
        $this->assertFalse($article->isActive());
    }

    public function testConfirmPublishActivatesAndSetsDisplayDate(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();
        $em->expects($this->once())->method('flush');

        $article = $this->makeArticle(9, author: $this->makeUser(7));
        $article->setDisplayDate(new \DateTime('2020-01-01 00:00:00'));

        $response = $controller->confirm(
            9,
            $this->post(['Publish' => 'Publish']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($article),
            $em
        );

        $this->assertTrue($article->isActive());
        $this->assertEqualsWithDelta(time(), $article->getDisplayDate()->getTimestamp(), 5);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testConfirmRepublishKeepsOriginalDisplayDate(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();
        $em->expects($this->once())->method('flush');

        $article = $this->makeArticle(9, author: $this->makeUser(7));
        $article->setActive(true);
        $article->setDisplayDate(new \DateTime('2020-01-01 00:00:00'));

        $controller->confirm(
            9,
            $this->post(['Publish' => 'Publish']),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository($article),
            $em
        );

        $this->assertTrue($article->isActive());
        $this->assertSame('2020-01-01 00:00:00', $article->getDisplayDate()->format('Y-m-d H:i:s'));
    }

    public function testConfirmAllowsCommissioner(): void
    {
        [$controller, $em] = $this->makeSubmitDeps();

        $article = $this->makeArticle(9, author: $this->makeUser(7));

        $controller->confirm(
            9,
            $this->post(['Publish' => 'Publish']),
            $this->makeAuth(true, userId: 8, commissioner: true),
            $this->makeRepository($article),
            $em
        );

        $this->assertTrue($article->isActive());
    }

    // ---- legacy POST targets ----

    public function testLegacyProcessAndConfirmRedirectToPublishForm(): void
    {
        $controller = $this->makeController();

        $response = $controller->legacyRedirect();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/article_publish', $response->getTargetUrl());
    }

    // ---- Helpers ----

    private function assertValidationError(array $form, string $expectedError): void
    {
        [$controller, $em, $images] = $this->makeSubmitDeps();
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $images->expects($this->never())->method('store');

        $controller->submit(
            $this->post($form),
            $this->makeAuth(true, userId: 7),
            $this->makeRepository(),
            $em,
            $images
        );

        $this->assertSame('article/publish.html.twig', $controller->renderedView);
        $this->assertContains(['error', $expectedError], $controller->flashes);
    }

    private function makeController(): ArticlePublishController
    {
        return new class extends ArticlePublishController {
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
                $url = "/$route" . ($parameters ? '?' . http_build_query($parameters) : '');
                return new RedirectResponse($url, $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = [$type, $message];
            }
        };
    }

    private function makeSubmitDeps(): array
    {
        $controller = $this->makeController();
        $em = $this->createMock(EntityManagerInterface::class);
        $images = $this->createMock(ArticleImageService::class);

        return [$controller, $em, $images];
    }

    private function makeAuth(bool $loggedIn, ?int $userId = null, bool $commissioner = false): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getUserId')->willReturn($userId);
        $auth->method('isCommissioner')->willReturn($commissioner);
        return $auth;
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setValue($user, $id);
        return $user;
    }

    private function makeArticle(int $id, ?User $author = null): Article
    {
        $article = new Article();
        $ref = new \ReflectionProperty(Article::class, 'id');
        $ref->setValue($article, $id);
        $article->setAuthor($author);
        return $article;
    }

    private function makeRepository(?Article $found = null): ArticleRepository
    {
        $repo = $this->createStub(ArticleRepository::class);
        $repo->method('find')->willReturn($found);
        return $repo;
    }

    private function post(array $params, ?UploadedFile $upload = null): Request
    {
        $files = $upload ? ['upload' => $upload] : [];

        return Request::create('/article/publish', 'POST', $params, [], $files);
    }

    private function makeUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'wmffl_test_img');
        file_put_contents($path, 'fake image bytes');
        $this->tempFiles[] = $path;

        return new UploadedFile($path, 'photo.jpg', 'image/jpeg', \UPLOAD_ERR_OK, true);
    }
}
