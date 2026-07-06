<?php

namespace App\Tests\Controller;

use App\Controller\ArticleController;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\AuthenticationService;
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

    public function testViewRendersArticle(): void
    {
        $controller = $this->makeController();
        $article = new Article();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn($article);

        $controller->view(42, $articles);

        $this->assertSame('article/view.html.twig', $controller->renderedView);
        $this->assertSame($article, $controller->renderedParams['article']);
    }

    public function testViewThrowsNotFoundForUnknownId(): void
    {
        $controller = $this->makeController();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->view(999, $articles);
    }

    // ---- GET /article ----

    public function testLatestRendersMostRecentActiveArticle(): void
    {
        $controller = $this->makeController();
        $article = new Article();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('findLatestActive')->willReturn($article);

        $controller->latest($articles);

        $this->assertSame('article/view.html.twig', $controller->renderedView);
        $this->assertSame($article, $controller->renderedParams['article']);
    }

    public function testLatestThrowsNotFoundWhenNoActiveArticles(): void
    {
        $controller = $this->makeController();
        $articles = $this->createStub(ArticleRepository::class);
        $articles->method('findLatestActive')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->latest($articles);
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
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

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
        };
    }

    private function makeAuth(bool $loggedIn): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        return $auth;
    }
}
