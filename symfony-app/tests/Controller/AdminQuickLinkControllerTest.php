<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminQuickLinkController;
use App\Entity\QuickLink;
use App\Repository\QuickLinkRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminQuickLinkControllerTest extends TestCase
{
    public function testReorderRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $quickLinks, $em] = $this->makeController(commissioner: false);

        $response = $controller->reorder(new Request(), $auth, $quickLinks, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testReorderRejectsInvalidCsrfToken(): void
    {
        [$controller, $auth, $quickLinks, $em] = $this->makeController(commissioner: true, links: [
            $this->link(1, 1), $this->link(2, 2),
        ]);
        $controller->csrfValid = false;

        $em->expects($this->never())->method('flush');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->reorder(new Request(request: ['ids' => ['2', '1']]), $auth, $quickLinks, $em);
    }

    public function testReorderRewritesSortOrderInGivenOrder(): void
    {
        $one   = $this->link(1, 1);
        $two   = $this->link(2, 2);
        $three = $this->link(3, 3);
        [$controller, $auth, $quickLinks, $em] = $this->makeController(commissioner: true, links: [$one, $two, $three]);

        $em->expects($this->once())->method('flush');

        $response = $controller->reorder(
            new Request(request: ['ids' => ['3', '1', '2']]),
            $auth,
            $quickLinks,
            $em
        );

        $this->assertSame(1, $three->getSortOrder());
        $this->assertSame(2, $one->getSortOrder());
        $this->assertSame(3, $two->getSortOrder());
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testReorderIgnoresUnknownIds(): void
    {
        $one = $this->link(1, 1);
        $two = $this->link(2, 2);
        [$controller, $auth, $quickLinks, $em] = $this->makeController(commissioner: true, links: [$one, $two]);

        $controller->reorder(
            new Request(request: ['ids' => ['999', '2', '1']]),
            $auth,
            $quickLinks,
            $em
        );

        $this->assertSame(1, $two->getSortOrder());
        $this->assertSame(2, $one->getSortOrder());
    }

    public function testReorderLeavesOmittedLinksUnchanged(): void
    {
        $one = $this->link(1, 1);
        $two = $this->link(2, 5);
        [$controller, $auth, $quickLinks, $em] = $this->makeController(commissioner: true, links: [$one, $two]);

        $controller->reorder(new Request(request: ['ids' => ['1']]), $auth, $quickLinks, $em);

        $this->assertSame(1, $one->getSortOrder());
        $this->assertSame(5, $two->getSortOrder());
    }

    // ---- Helpers ----

    private function link(int $id, int $sortOrder): QuickLink
    {
        $link = new QuickLink();
        $ref  = new \ReflectionProperty(QuickLink::class, 'id');
        $ref->setAccessible(true);
        $ref->setValue($link, $id);
        $link->setSortOrder($sortOrder);

        return $link;
    }

    /**
     * @param QuickLink[] $links
     */
    private function makeController(bool $commissioner, array $links = []): array
    {
        $controller = new class extends AdminQuickLinkController {
            public bool $csrfValid = true;

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                return new Response();
            }

            public function addFlash(string $type, mixed $message): void
            {
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $quickLinks = $this->createStub(QuickLinkRepository::class);
        $quickLinks->method('findAllOrdered')->willReturn($links);

        $em = $this->createMock(EntityManagerInterface::class);

        return [$controller, $auth, $quickLinks, $em];
    }
}
