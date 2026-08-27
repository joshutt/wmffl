<?php

namespace App\Tests\Controller;

use App\Controller\ScheduleController;
use App\Service\ScheduleService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class ScheduleControllerTest extends TestCase
{
    public function testNoSeasonResolvesViaTheService(): void
    {
        $service = $this->createMock(ScheduleService::class);
        $service->expects($this->once())->method('resolveDefaultSeason')->willReturn(2025);
        $service->method('getSchedule')->with(2025)->willReturn(['weeks' => []]);

        $controller = $this->makeController($service);
        $controller->index(null);

        $this->assertSame('schedule/index.html.twig', $controller->renderedView);
        $this->assertSame(2025, $controller->renderedParams['season']);
        $this->assertSame([], $controller->renderedParams['weeks']);
    }

    public function testExplicitSeasonRendersThatSeasonDirectly(): void
    {
        $service = $this->createMock(ScheduleService::class);
        $service->expects($this->never())->method('resolveDefaultSeason');
        $service->method('getSchedule')->with(1994)->willReturn(['weeks' => [['week' => 1]]]);

        $controller = $this->makeController($service);
        $controller->index(1994);

        $this->assertSame(1994, $controller->renderedParams['season']);
        $this->assertSame([['week' => 1]], $controller->renderedParams['weeks']);
    }

    private function makeController(ScheduleService $service): ScheduleController
    {
        return new class($service) extends ScheduleController {
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }
        };
    }
}
