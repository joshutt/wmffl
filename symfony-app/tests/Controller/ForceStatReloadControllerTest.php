<?php

namespace App\Tests\Controller;

use App\Controller\ForceStatReloadController;
use App\Service\AuthenticationService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class ForceStatReloadControllerTest extends TestCase
{
    public function testReturnsForbiddenWhenNotCommissioner(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: false);

        $response = $controller($auth, '/fake/project');

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testReturnsPlainTextResponse(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true, output: []);

        $response = $controller($auth, '/fake/project');

        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function testReturnsOkWhenCommissioner(): void
    {
        [$controller, $auth] = $this->makeController(commissioner: true, output: []);

        $response = $controller($auth, '/fake/project');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testResponseBodyContainsScriptOutput(): void
    {
        [$controller, $auth] = $this->makeController(
            commissioner: true,
            output: ['Live Scores', 'Week: 5', 'Updated Scores']
        );

        $response = $controller($auth, '/fake/project');

        $this->assertStringContainsString('Live Scores', $response->getContent());
        $this->assertStringContainsString('Week: 5', $response->getContent());
        $this->assertStringContainsString('Updated Scores', $response->getContent());
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner, array $output = []): array
    {
        $controller = new class($output) extends ForceStatReloadController {
            public function __construct(private readonly array $fakeOutput) {}

            protected function execScript(string $script, array &$outArr): void
            {
                $outArr = $this->fakeOutput;
            }

            protected function appendLog(string $path, string $output): void {}
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        return [$controller, $auth];
    }
}
