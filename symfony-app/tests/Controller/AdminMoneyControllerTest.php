<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminMoneyController;
use App\Entity\Paid;
use App\Entity\SeasonFlag;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AllowMockObjectsWithoutExpectations]
class AdminMoneyControllerTest extends TestCase
{
    // ---- GET /admin/money/updatePaid ----

    public function testUpdatePaidRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->updatePaid($auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testUpdatePaidRendersCorrectTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updatePaid($auth, $seasonWeek, $em);

        $this->assertSame('admin/money/updatePaid.html.twig', $controller->renderedView);
    }

    public function testUpdatePaidDefaultsToCurrentSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updatePaid($auth, $seasonWeek, $em);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testUpdatePaidUsesExplicitSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updatePaid($auth, $seasonWeek, $em, 2022);

        $this->assertSame(2022, $controller->renderedParams['season']);
    }

    public function testUpdatePaidPassesCurrentSeasonSeparately(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updatePaid($auth, $seasonWeek, $em, 2022);

        $this->assertSame(2025, $controller->renderedParams['currentSeason']);
    }

    public function testUpdatePaidPassesPaidListToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updatePaid($auth, $seasonWeek, $em);

        $this->assertArrayHasKey('paid', $controller->renderedParams);
    }

    // ---- POST /admin/money/recordChange ----

    public function testRecordChangeReturnsForbiddenWhenNotCommissioner(): void
    {
        [$controller, $auth, , $em] = $this->makeController(commissioner: false);

        $response = $controller->recordChange(new Request(), $auth, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testRecordChangeReturnsOkOnSuccess(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $em->method('find')->willReturn($this->createStub(Paid::class));

        $request = new Request(request: ['field' => 'paid-1', 'val' => 'true']);
        $response = $controller->recordChange($request, $auth, $em);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['ok' => true], json_decode($response->getContent(), true));
    }

    public function testRecordChangeSetsPaidField(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $paid = $this->createMock(Paid::class);
        $paid->expects($this->once())->method('setPaid')->with(true);
        $em->method('find')->willReturn($paid);

        $request = new Request(request: ['field' => 'paid-1', 'val' => 'true']);
        $controller->recordChange($request, $auth, $em);
    }

    public function testRecordChangeSetsLateFeeField(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $paid = $this->createMock(Paid::class);
        $paid->expects($this->once())->method('setLateFee')->with(9.99);
        $em->method('find')->willReturn($paid);

        $request = new Request(request: ['field' => 'late-1', 'val' => '9.99']);
        $controller->recordChange($request, $auth, $em);
    }

    public function testRecordChangeSetsAmtPaidField(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $paid = $this->createMock(Paid::class);
        $paid->expects($this->once())->method('setAmtPaid')->with(75.0);
        $em->method('find')->willReturn($paid);

        $request = new Request(request: ['field' => 'amt-1', 'val' => '75.00']);
        $controller->recordChange($request, $auth, $em);
    }

    public function testRecordChangeFlushesOnSuccess(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $em->method('find')->willReturn($this->createStub(Paid::class));
        $em->expects($this->once())->method('flush');

        $request = new Request(request: ['field' => 'paid-1', 'val' => 'true']);
        $controller->recordChange($request, $auth, $em);
    }

    public function testRecordChangeReturnsErrorOnException(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $em->method('find')->willThrowException(new \RuntimeException('DB error'));

        $request = new Request(request: ['field' => 'paid-1', 'val' => 'true']);
        $response = $controller->recordChange($request, $auth, $em);

        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    // ---- GET /admin/money/updateFlags ----

    public function testUpdateFlagsRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->updateFlags($auth, $seasonWeek, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testUpdateFlagsRendersCorrectTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateFlags($auth, $seasonWeek, $em);

        $this->assertSame('admin/money/updateFlags.html.twig', $controller->renderedView);
    }

    public function testUpdateFlagsDefaultsToCurrentSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateFlags($auth, $seasonWeek, $em);

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testUpdateFlagsUsesExplicitSeason(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateFlags($auth, $seasonWeek, $em, 2021);

        $this->assertSame(2021, $controller->renderedParams['season']);
    }

    public function testUpdateFlagsPassesFlagsToTemplate(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $controller->updateFlags($auth, $seasonWeek, $em);

        $this->assertArrayHasKey('flags', $controller->renderedParams);
    }

    // ---- POST /admin/money/processFlags ----

    public function testProcessFlagsRedirectsWhenNotCommissioner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: false);

        $response = $controller->processFlags(new Request(), $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testProcessFlagsRedirectsToFlagsPageAfterSave(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $request = new Request(request: ['season' => '2025', 'flag-5' => 'W']);
        $response = $controller->processFlags($request, $auth, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin_money_flags', $response->getTargetUrl());
    }

    public function testProcessFlagsSkipsMissingEntities(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);
        $em->method('find')->willReturn(null);

        $request = new Request(request: ['season' => '2025', 'flag-99' => 'W']);

        // Should not throw even when find() returns null
        $response = $controller->processFlags($request, $auth, $em);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testProcessFlagsCallsFlushForEachFlag(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $flag1 = $this->makeSeasonFlag();
        $flag2 = $this->makeSeasonFlag();
        $em->method('find')->willReturnOnConsecutiveCalls($flag1, $flag2);
        $em->expects($this->exactly(2))->method('flush');

        $request = new Request(request: ['season' => '2025', 'flag-1' => 'W', 'flag-2' => 'L']);
        $controller->processFlags($request, $auth, $em);
    }

    public function testProcessFlagsUpdatesChangedFlags(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $flag = $this->makeSeasonFlag(flags: 'W');
        $em->method('find')->willReturn($flag);
        $flag->expects($this->once())->method('setFlags')->with('L');

        $request = new Request(request: ['season' => '2025', 'flag-5' => 'L']);
        $controller->processFlags($request, $auth, $em);
    }

    public function testProcessFlagsSetsDivisionWinner(): void
    {
        [$controller, $auth, $seasonWeek, $em] = $this->makeController(commissioner: true);

        $flag = $this->makeSeasonFlag();
        $em->method('find')->willReturn($flag);
        $flag->expects($this->once())->method('setDivisionWinner')->with(true);

        $request = new Request(request: ['season' => '2025', 'flag-5' => 'W', 'div-5' => '1']);
        $controller->processFlags($request, $auth, $em);
    }

    // ---- Helpers ----

    private function makeController(bool $commissioner): array
    {
        $controller = new class extends AdminMoneyController {
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
                return new RedirectResponse("/$route", $status);
            }
        };

        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isCommissioner')->willReturn($commissioner);

        $seasonWeek = $this->createStub(SeasonWeekService::class);
        $seasonWeek->method('getCurrentSeason')->willReturn(2025);

        $repo = $this->createStub(EntityRepository::class);
        $repo->method('findBy')->willReturn([]);

        $conn = $this->createStub(Connection::class);
        $conn->method('executeStatement')->willReturn(0);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);
        $em->method('getConnection')->willReturn($conn);

        return [$controller, $auth, $seasonWeek, $em];
    }

    private function makeSeasonFlag(
        ?string $flags = null,
        bool $div = false,
        bool $po = false,
        bool $fin = false,
        bool $cham = false,
    ): SeasonFlag {
        $flag = $this->createMock(SeasonFlag::class);
        $flag->method('getFlags')->willReturn($flags);
        $flag->method('isDivisionWinner')->willReturn($div);
        $flag->method('isPlayoffTeam')->willReturn($po);
        $flag->method('isFinalist')->willReturn($fin);
        $flag->method('isChampion')->willReturn($cham);
        return $flag;
    }
}
