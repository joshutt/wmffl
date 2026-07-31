<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminConfigController;
use App\Entity\Config;
use App\Repository\ConfigRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminConfigControllerTest extends TestCase
{
    // ---- index ----

    public function testIndexRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $configs] = $this->makeController(commissioner: false);

        $response = $controller->index($auth, $configs);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testIndexPassesAllConfigsToTemplate(): void
    {
        $rows = [$this->config('a.b', '1'), $this->config('c.d', '2')];
        [$controller, $auth, $configs] = $this->makeController(commissioner: true, rows: $rows);

        $controller->index($auth, $configs);

        $this->assertSame($rows, $controller->renderedParams['configs']);
    }

    // ---- new ----

    public function testNewRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: false);

        $response = $controller->new(new Request(), $auth, $configs, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testNewPersistsAndRedirects(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true);

        $em->expects($this->once())->method('persist')->with($this->callback(
            fn (Config $c) => $c->getName() === 'my.key' && $c->getValue() === 'my value'
        ));
        $em->expects($this->once())->method('flush');

        $request  = Request::create('/admin/config/new', 'POST', ['key' => 'my.key', 'value' => 'my value']);
        $response = $controller->new($request, $auth, $configs, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_config', $response->getTargetUrl());
    }

    public function testNewRejectsBlankKeyOrValue(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true);

        $em->expects($this->never())->method('persist');

        $request = Request::create('/admin/config/new', 'POST', ['key' => '', 'value' => 'x']);
        $controller->new($request, $auth, $configs, $em);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testNewRejectsDuplicateKey(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(
            commissioner: true,
            rows: [$this->config('dup.key', 'existing')]
        );

        $em->expects($this->never())->method('persist');

        $request = Request::create('/admin/config/new', 'POST', ['key' => 'dup.key', 'value' => 'x']);
        $controller->new($request, $auth, $configs, $em);

        $this->assertCount(1, $controller->flashes);
        $this->assertSame('error', $controller->flashes[0]['type']);
    }

    public function testNewRejectsInvalidCsrfToken(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true);
        $controller->csrfValid = false;

        $em->expects($this->never())->method('persist');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->new(Request::create('/admin/config/new', 'POST', ['key' => 'a', 'value' => 'b']), $auth, $configs, $em);
    }

    // ---- edit ----

    public function testEditThrows404WhenKeyMissing(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true);

        $this->expectException(NotFoundHttpException::class);
        $controller->edit('missing.key', new Request(), $auth, $configs, $em);
    }

    public function testEditUpdatesValueAndRedirects(): void
    {
        $config = $this->config('draft.login.14', 'old');
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true, rows: [$config]);

        $em->expects($this->once())->method('flush');

        $request  = Request::create('/admin/config/draft.login.14/edit', 'POST', ['value' => 'new']);
        $response = $controller->edit('draft.login.14', $request, $auth, $configs, $em);

        $this->assertSame('new', $config->getValue());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_config', $response->getTargetUrl());
    }

    public function testEditRejectsBlankValue(): void
    {
        $config = $this->config('a.b', 'old');
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true, rows: [$config]);

        $em->expects($this->never())->method('flush');

        $request = Request::create('/admin/config/a.b/edit', 'POST', ['value' => '']);
        $controller->edit('a.b', $request, $auth, $configs, $em);

        $this->assertSame('old', $config->getValue());
        $this->assertCount(1, $controller->flashes);
    }

    public function testEditRejectsInvalidCsrfToken(): void
    {
        $config = $this->config('a.b', 'old');
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true, rows: [$config]);
        $controller->csrfValid = false;

        $this->expectException(AccessDeniedHttpException::class);
        $controller->edit('a.b', Request::create('/admin/config/a.b/edit', 'POST', ['value' => 'new']), $auth, $configs, $em);
    }

    // ---- delete ----

    public function testDeleteRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: false);

        $response = $controller->delete('a.b', new Request(), $auth, $configs, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testDeleteThrows404WhenKeyMissing(): void
    {
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true);

        $this->expectException(NotFoundHttpException::class);
        $controller->delete('missing.key', new Request(), $auth, $configs, $em);
    }

    public function testDeleteRemovesConfigAndRedirects(): void
    {
        $config = $this->config('a.b', 'x');
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true, rows: [$config]);

        $em->expects($this->once())->method('remove')->with($config);
        $em->expects($this->once())->method('flush');

        $response = $controller->delete('a.b', new Request(), $auth, $configs, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_config', $response->getTargetUrl());
    }

    public function testDeleteRejectsInvalidCsrfToken(): void
    {
        $config = $this->config('a.b', 'x');
        [$controller, $auth, $configs, $em] = $this->makeController(commissioner: true, rows: [$config]);
        $controller->csrfValid = false;

        $em->expects($this->never())->method('remove');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->delete('a.b', new Request(), $auth, $configs, $em);
    }

    // ---- Helpers ----

    private function config(string $key, string $value): Config
    {
        $config = new Config();
        $config->setName($key);
        $config->setValue($value);

        return $config;
    }

    /**
     * @param Config[] $rows
     */
    private function makeController(bool $commissioner, array $rows = []): array
    {
        $controller = new class extends AdminConfigController {
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
                return new RedirectResponse("/$route", $status);
            }

            public function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = ['type' => $type, 'message' => $message];
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $byKey = [];
        foreach ($rows as $row) {
            $byKey[$row->getName()] = $row;
        }

        $configs = $this->createStub(ConfigRepository::class);
        $configs->method('findAllOrdered')->willReturn($rows);
        $configs->method('find')->willReturnCallback(fn (string $key) => $byKey[$key] ?? null);

        $em = $this->createMock(EntityManagerInterface::class);

        return [$controller, $auth, $configs, $em];
    }
}
