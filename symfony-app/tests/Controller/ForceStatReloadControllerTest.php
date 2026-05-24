<?php

namespace App\Tests\Controller;

use App\Controller\ForceStatReloadController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ForceStatReloadControllerTest extends TestCase
{
    public function testReturnsPlainTextResponse(): void
    {
        $response = $this->makeController([])(projectDir: '/fake/project');

        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function testReturnsOk(): void
    {
        $response = $this->makeController([])(projectDir: '/fake/project');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testResponseBodyContainsScriptOutput(): void
    {
        $response = $this->makeController(
            ['Live Scores', 'Week: 5', 'Updated Scores']
        )(projectDir: '/fake/project');

        $this->assertStringContainsString('Live Scores', $response->getContent());
        $this->assertStringContainsString('Week: 5', $response->getContent());
        $this->assertStringContainsString('Updated Scores', $response->getContent());
    }

    // ---- Helpers ----

    private function makeController(array $output): ForceStatReloadController
    {
        return new class($output) extends ForceStatReloadController {
            public function __construct(private readonly array $fakeOutput) {}

            protected function execScript(string $script, array &$outArr): void
            {
                $outArr = $this->fakeOutput;
            }

            protected function appendLog(string $path, string $output): void {}
        };
    }
}
