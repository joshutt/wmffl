<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminCommentController;
use App\Entity\Comment;
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
class AdminCommentControllerTest extends TestCase
{
    // ---- GET /admin/comments ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: false);

        $response = $controller->index(new Request(), $auth, $this->makeRepository());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexRendersRecentComments(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $comments = [new Comment(), new Comment()];
        $repo = $this->makeRepository();
        $repo->expects($this->once())->method('findPageForAdmin')
            ->with(AdminCommentController::PER_PAGE + 1, 0)
            ->willReturn($comments);

        $controller->index(new Request(), $auth, $repo);

        $this->assertSame('admin/comment/index.html.twig', $controller->renderedView);
        $this->assertSame($comments, $controller->renderedParams['comments']);
        $this->assertSame(0, $controller->renderedParams['page']);
        $this->assertFalse($controller->renderedParams['hasMore']);
    }

    public function testIndexPaginatesBeyondFiftyComments(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $rows = array_map(fn () => new Comment(), range(0, AdminCommentController::PER_PAGE));
        $repo = $this->makeRepository();
        $repo->expects($this->once())->method('findPageForAdmin')
            ->with(AdminCommentController::PER_PAGE + 1, AdminCommentController::PER_PAGE)
            ->willReturn($rows);

        $controller->index(new Request(query: ['page' => '1']), $auth, $repo);

        $this->assertCount(AdminCommentController::PER_PAGE, $controller->renderedParams['comments']);
        $this->assertSame(1, $controller->renderedParams['page']);
        $this->assertTrue($controller->renderedParams['hasMore']);
    }

    public function testIndexClampsNegativePageToZero(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true);
        $repo = $this->makeRepository();
        $repo->expects($this->once())->method('findPageForAdmin')
            ->with(AdminCommentController::PER_PAGE + 1, 0)
            ->willReturn([]);

        $controller->index(new Request(query: ['page' => '-2']), $auth, $repo);

        $this->assertSame(0, $controller->renderedParams['page']);
    }

    // ---- POST /admin/comments/{id}/toggle ----

    public function testToggleRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: false);

        $response = $controller->toggle(5, new Request(), $auth, $this->makeRepository(), $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testToggleThrowsNotFoundForUnknownComment(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $controller->toggle(999, new Request(), $auth, $repo, $em);
    }

    public function testToggleDeactivatesActiveComment(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);
        $em->expects($this->once())->method('flush');

        $comment = (new Comment())->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($comment);

        $response = $controller->toggle(5, new Request(), $auth, $repo, $em);

        $this->assertFalse($comment->isActive());
        $this->assertSame('/admin_comments?page=0', $response->getTargetUrl());
    }

    public function testToggleActivatesInactiveComment(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $comment = (new Comment())->setActive(false);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($comment);

        $controller->toggle(5, new Request(), $auth, $repo, $em);

        $this->assertTrue($comment->isActive());
    }

    public function testToggleActivatesNullActiveComment(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $comment = (new Comment())->setActive(null);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($comment);

        $controller->toggle(5, new Request(), $auth, $repo, $em);

        $this->assertTrue($comment->isActive());
    }

    public function testToggleRedirectsBackToSubmittedPage(): void
    {
        [$controller, $auth, $em] = $this->makeController(commissioner: true);

        $comment = (new Comment())->setActive(true);
        $repo = $this->makeRepository();
        $repo->method('find')->willReturn($comment);

        $request = Request::create('/admin/comments/5/toggle', 'POST', ['page' => '3']);
        $response = $controller->toggle(5, $request, $auth, $repo, $em);

        $this->assertSame('/admin_comments?page=3', $response->getTargetUrl());
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner): array
    {
        $controller = new class extends AdminCommentController {
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

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $em = $this->createMock(EntityManagerInterface::class);

        return [$controller, $auth, $em];
    }

    private function makeRepository(): CommentRepository
    {
        return $this->createMock(CommentRepository::class);
    }
}
