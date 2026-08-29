<?php

namespace App\Tests\Controller;

use App\Controller\DraftResultsController;
use App\Service\DraftResultsService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowMockObjectsWithoutExpectations]
class DraftResultsControllerTest extends TestCase
{
    public function testNoYearResolvesViaTheService(): void
    {
        $service = $this->createMock(DraftResultsService::class);
        $service->expects($this->once())->method('resolveDefaultYear')->willReturn(2026);
        $service->method('isReachable')->with(2026)->willReturn(true);
        $service->method('getBoard')->with(2026)->willReturn(['rows' => []]);

        $controller = $this->makeController($service);
        $controller->index(new Request(), null);

        $this->assertSame('transactions/draftresults.html.twig', $controller->renderedView);
        $this->assertSame(2026, $controller->renderedParams['year']);
    }

    public function testExplicitYearRendersThatYearDirectly(): void
    {
        $service = $this->createMock(DraftResultsService::class);
        $service->expects($this->never())->method('resolveDefaultYear');
        $service->method('isReachable')->with(2006)->willReturn(true);
        $service->method('getBoard')->with(2006)->willReturn(['rows' => [['round' => 1]]]);

        $controller = $this->makeController($service);
        $controller->index(new Request(), 2006);

        $this->assertSame(2006, $controller->renderedParams['year']);
        $this->assertSame([['round' => 1]], $controller->renderedParams['rows']);
    }

    public function testModernYearRendersDirectly(): void
    {
        $service = $this->createMock(DraftResultsService::class);
        $service->method('isReachable')->with(2019)->willReturn(true);
        $service->method('getBoard')->with(2019)->willReturn(['rows' => []]);

        $controller = $this->makeController($service);
        $controller->index(new Request(), 2019);

        $this->assertSame(2019, $controller->renderedParams['year']);
    }

    public function testUnreachableYear404s(): void
    {
        $service = $this->createStub(DraftResultsService::class);
        $service->method('isReachable')->willReturn(false);

        $controller = $this->makeController($service);

        $this->expectException(NotFoundHttpException::class);
        $controller->index(new Request(), 2050);
    }

    public function testFutureSeason404s(): void
    {
        $service = $this->createStub(DraftResultsService::class);
        $service->method('isReachable')->willReturn(false);

        $controller = $this->makeController($service);

        $this->expectException(NotFoundHttpException::class);
        $controller->index(new Request(), 2027);
    }

    public function testFilteredQueryStringIsPassedThroughToTheService(): void
    {
        $service = $this->createMock(DraftResultsService::class);
        $service->method('isReachable')->willReturn(true);
        $service->expects($this->once())->method('getBoard')
            ->with(2019, ['round' => '2', 'pick' => null, 'team' => '5', 'pos' => null, 'nfl' => null])
            ->willReturn(['rows' => [], 'filters' => ['round' => 2, 'team' => '5']]);

        $controller = $this->makeController($service);
        $request = new Request(query: ['round' => '2', 'team' => '5']);
        $controller->index($request, 2019);

        $this->assertSame(['round' => 2, 'team' => '5'], $controller->renderedParams['filters']);
    }

    private function makeController(DraftResultsService $service): DraftResultsController
    {
        return new class($service) extends DraftResultsController {
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
