<?php

namespace App\Tests\Controller;

use App\Controller\ArticleController;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class ArticleControllerTest extends TestCase
{
    // ---- GET /article/{id} ----

    public function testViewRendersArticleWithCommentThread(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42);
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $tree = [['comment' => new Comment(), 'children' => []]];
        $comments = $this->createMock(CommentRepository::class);
        $comments->expects($this->once())->method('findThreadByArticle')
            ->with(42)
            ->willReturn($tree);

        $controller->view(42, $articles, $comments, $this->makeAuth(true));

        $this->assertSame('article/view.html.twig', $controller->renderedView);
        $this->assertSame($article, $controller->renderedParams['article']);
        $this->assertSame($tree, $controller->renderedParams['comments']);
        $this->assertTrue($controller->renderedParams['isLoggedIn']);
    }

    public function testViewThrowsNotFoundForUnknownId(): void
    {
        $controller = $this->makeController();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->view(999, $articles, $this->makeComments(), $this->makeAuth(false));
    }

    // ---- Edit link visibility ----

    public function testViewShowsEditLinkToAuthor(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42, $this->makeUser(7));
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $controller->view(42, $articles, $this->makeComments(), $this->makeAuth(true, userId: 7));

        $this->assertTrue($controller->renderedParams['canEdit']);
    }

    public function testViewShowsEditLinkToCommissioner(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42, $this->makeUser(7));
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $controller->view(42, $articles, $this->makeComments(), $this->makeAuth(true, userId: 8, commissioner: true));

        $this->assertTrue($controller->renderedParams['canEdit']);
    }

    public function testViewHidesEditLinkFromOtherMembers(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42, $this->makeUser(7));
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $controller->view(42, $articles, $this->makeComments(), $this->makeAuth(true, userId: 8));

        $this->assertFalse($controller->renderedParams['canEdit']);
    }

    public function testViewHidesEditLinkFromAnonymousVisitors(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42, $this->makeUser(7));
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $controller->view(42, $articles, $this->makeComments(), $this->makeAuth(false));

        $this->assertFalse($controller->renderedParams['canEdit']);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    // ---- GET /article ----

    public function testLatestRendersMostRecentActiveArticle(): void
    {
        $controller = $this->makeController();
        $article = $this->makeArticle(42);
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('findLatestActive')->willReturn($article);

        $controller->latest($articles, $this->makeComments(), $this->makeAuth(false));

        $this->assertSame('article/view.html.twig', $controller->renderedView);
        $this->assertSame($article, $controller->renderedParams['article']);
    }

    public function testLatestThrowsNotFoundWhenNoActiveArticles(): void
    {
        $controller = $this->makeController();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('findLatestActive')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->latest($articles, $this->makeComments(), $this->makeAuth(false));
    }

    // ---- GET /articles ----

    public function testListRendersRequestedPage(): void
    {
        $controller = $this->makeController();
        $articles = $this->createMock(ArticleRepository::class);
        $articles->expects($this->once())->method('findActivePage')
            ->with(ArticleController::PER_PAGE, 2)
            ->willReturn([]);

        $request = new Request(query: ['page' => '2']);
        $controller->list($request, $articles, $this->makeAuth(true));

        $this->assertSame('article/list.html.twig', $controller->renderedView);
        $this->assertSame(2, $controller->renderedParams['page']);
        $this->assertTrue($controller->renderedParams['isLoggedIn']);
    }

    public function testListClampsNegativePageToZero(): void
    {
        $controller = $this->makeController();
        $articles = $this->createMock(ArticleRepository::class);
        $articles->expects($this->once())->method('findActivePage')
            ->with(ArticleController::PER_PAGE, 0)
            ->willReturn([]);

        $request = new Request(query: ['page' => '-3']);
        $controller->list($request, $articles, $this->makeAuth(false));

        $this->assertSame(0, $controller->renderedParams['page']);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    // ---- POST /article/{id}/comment ----

    public function testCommentThrowsNotFoundForUnknownArticle(): void
    {
        $controller = $this->makeController();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->comment(
            999,
            $this->post(['text' => 'hello']),
            $articles,
            $this->makeComments(),
            $this->makeAuth(true, userId: 7),
            $this->makeEntityManager()
        );
    }

    public function testCommentRejectsAnonymousVisitors(): void
    {
        $controller = $this->makeController();
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $response = $controller->comment(
            42,
            $this->post(['text' => 'hello']),
            $this->makeArticles(42),
            $this->makeComments(),
            $this->makeAuth(false),
            $em
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/article_view?id=42', $response->getTargetUrl());
        $this->assertContains(['error', 'You must be logged in to comment'], $controller->flashes);
    }

    public function testCommentRejectsEmptyText(): void
    {
        $controller = $this->makeController();
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $response = $controller->comment(
            42,
            $this->post(['text' => "  \n "]),
            $this->makeArticles(42),
            $this->makeComments(),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertSame('/article_view?id=42', $response->getTargetUrl());
        $this->assertContains(['error', 'A comment cannot be empty'], $controller->flashes);
    }

    public function testCommentRejectsParentFromDifferentArticle(): void
    {
        $controller = $this->makeController();
        $parent = $this->makeComment(5, articleId: 43, active: true);
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $response = $controller->comment(
            42,
            $this->post(['text' => 'hello', 'parent' => '5']),
            $this->makeArticles(42),
            $this->makeComments($parent),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertSame('/article_view?id=42', $response->getTargetUrl());
        $this->assertContains(['error', 'That comment cannot be replied to'], $controller->flashes);
    }

    public function testCommentRejectsInactiveParent(): void
    {
        $controller = $this->makeController();
        $parent = $this->makeComment(5, articleId: 42, active: false);
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $controller->comment(
            42,
            $this->post(['text' => 'hello', 'parent' => '5']),
            $this->makeArticles(42),
            $this->makeComments($parent),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertContains(['error', 'That comment cannot be replied to'], $controller->flashes);
    }

    public function testCommentRejectsNullActiveParent(): void
    {
        $controller = $this->makeController();
        $parent = $this->makeComment(5, articleId: 42, active: null);
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $controller->comment(
            42,
            $this->post(['text' => 'hello', 'parent' => '5']),
            $this->makeArticles(42),
            $this->makeComments($parent),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertContains(['error', 'That comment cannot be replied to'], $controller->flashes);
    }

    public function testCommentRejectsUnknownParent(): void
    {
        $controller = $this->makeController();
        $em = $this->makeEntityManager();
        $em->expects($this->never())->method('persist');

        $controller->comment(
            42,
            $this->post(['text' => 'hello', 'parent' => '5']),
            $this->makeArticles(42),
            $this->makeComments(),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertContains(['error', 'That comment cannot be replied to'], $controller->flashes);
    }

    public function testCommentPersistsTopLevelComment(): void
    {
        $controller = $this->makeController();
        $user = $this->makeUser(7);

        $persisted = null;
        $em = $this->makeEntityManager();
        $em->method('find')->with(User::class, 7)->willReturn($user);
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(function (Comment $c) use (&$persisted) {
                $persisted = $c;
            });
        $em->expects($this->once())->method('flush');

        $response = $controller->comment(
            42,
            $this->post(['text' => '  Nice article!  ']),
            $this->makeArticles(42),
            $this->makeComments(),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertSame(42, $persisted->getArticleId());
        $this->assertSame('Nice article!', $persisted->getCommentText());
        $this->assertSame($user, $persisted->getAuthor());
        $this->assertTrue($persisted->isActive());
        $this->assertNull($persisted->getParent());
        $this->assertEqualsWithDelta(time(), $persisted->getDateCreated()->getTimestamp(), 5);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringStartsWith('/article_view?id=42', $response->getTargetUrl());
        $this->assertStringContainsString('_fragment=comment-', $response->getTargetUrl());
    }

    public function testCommentPersistsReplyToActiveParent(): void
    {
        $controller = $this->makeController();
        $parent = $this->makeComment(5, articleId: 42, active: true);

        $persisted = null;
        $em = $this->makeEntityManager();
        $em->method('find')->willReturn($this->makeUser(7));
        $em->method('persist')->willReturnCallback(function (Comment $c) use (&$persisted) {
            $persisted = $c;
        });

        $controller->comment(
            42,
            $this->post(['text' => 'I agree', 'parent' => '5']),
            $this->makeArticles(42),
            $this->makeComments($parent),
            $this->makeAuth(true, userId: 7),
            $em
        );

        $this->assertSame($parent, $persisted->getParent());
        $this->assertSame('I agree', $persisted->getCommentText());
    }

    // ---- legacy redirects ----

    public function testLegacyViewRedirectsPermanentlyToArticle(): void
    {
        $controller = $this->makeController();

        $response = $controller->legacyView(new Request(query: ['uid' => '17']));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('/article_view?id=17', $response->getTargetUrl());
    }

    public function testLegacyViewWithoutUidRedirectsToLatest(): void
    {
        $controller = $this->makeController();

        $response = $controller->legacyView(new Request());

        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('/article_latest', $response->getTargetUrl());
    }

    public function testLegacyListRedirectsStartToPage(): void
    {
        $controller = $this->makeController();

        $response = $controller->legacyList(new Request(query: ['start' => '2']));

        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('/article_list?page=2', $response->getTargetUrl());
    }

    public function testLegacyListWithoutStartRedirectsToFirstPage(): void
    {
        $controller = $this->makeController();

        $response = $controller->legacyList(new Request());

        $this->assertSame(Response::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
        $this->assertSame('/article_list', $response->getTargetUrl());
    }

    // ---- Helpers ----

    private function makeController(): ArticleController
    {
        return new class extends ArticleController {
            public bool $csrfValid = true;

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

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

    private function makeComment(int $id, int $articleId, ?bool $active): Comment
    {
        $comment = new Comment();
        $ref = new \ReflectionProperty(Comment::class, 'id');
        $ref->setValue($comment, $id);
        $comment->setArticleId($articleId);
        $comment->setActive($active);
        return $comment;
    }

    private function makeArticles(int $id): ArticleRepository
    {
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($this->makeArticle($id));
        return $articles;
    }

    private function makeComments(?Comment $found = null): CommentRepository
    {
        $comments = $this->createStub(CommentRepository::class);
        $comments->method('find')->willReturn($found);
        $comments->method('findThreadByArticle')->willReturn([]);
        return $comments;
    }

    private function makeEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    private function post(array $params): Request
    {
        return Request::create('/article/42/comment', 'POST', $params);
    }
}
