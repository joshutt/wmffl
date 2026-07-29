<?php

namespace App\Tests\Controller;

use App\Controller\ProposalController;
use App\Entity\Issue;
use App\Entity\User;
use App\Enum\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use App\Service\ProposalMailer;
use App\Service\SeasonWeekService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class ProposalControllerTest extends TestCase
{
    // ---- list ----

    public function testListDefaultsToCurrentSeason(): void
    {
        $controller = $this->makeController();
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('publishedSeasons')->willReturn([2026, 2025]);
        $issues->method('findPublishedBySeason')->willReturn([]);

        $controller->list(new Request(), $issues, $this->makeSeasonWeek(2026), $this->makeListEm());

        $this->assertSame('proposals/list.html.twig', $controller->renderedView);
        $this->assertSame(2026, $controller->renderedParams['season']);
    }

    public function testListHonoursSeasonQuery(): void
    {
        $controller = $this->makeController();
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('publishedSeasons')->willReturn([2026, 2005]);
        $issues->method('findPublishedBySeason')->willReturn([]);

        $controller->list(new Request(query: ['season' => '2005']), $issues, $this->makeSeasonWeek(2026), $this->makeListEm());

        $this->assertSame(2005, $controller->renderedParams['season']);
    }

    public function testListFallsBackToNewestPublishedWhenCurrentEmpty(): void
    {
        $controller = $this->makeController();
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('publishedSeasons')->willReturn([2025, 2024]);
        $issues->method('findPublishedBySeason')->willReturn([]);

        $controller->list(new Request(), $issues, $this->makeSeasonWeek(2026), $this->makeListEm());

        $this->assertSame(2025, $controller->renderedParams['season']);
    }

    public function testListPassesOnBallotSetKeyedByIssueId(): void
    {
        $controller = $this->makeController();
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('publishedSeasons')->willReturn([2026]);
        $issues->method('findPublishedBySeason')->willReturn([]);

        $controller->list(new Request(), $issues, $this->makeSeasonWeek(2026), $this->makeListEm([12, 34]));

        $this->assertSame([12 => true, 34 => true], $controller->renderedParams['onBallot']);
    }

    // ---- submit form ----

    public function testSubmitFormShowsLoggedOut(): void
    {
        $controller = $this->makeController();

        $controller->submitForm($this->makeAuth(false));

        $this->assertSame('proposals/submit.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    // ---- submit ----

    public function testSubmitRejectsAnonymous(): void
    {
        [$controller, $em, $mailer] = $this->submitDeps();
        $em->expects($this->never())->method('persist');

        $controller->submit(
            $this->post(['name' => 'Test', 'description' => 'x']),
            $this->makeAuth(false),
            $this->makeSeasonWeek(2026),
            $em,
            $mailer
        );

        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    public function testSubmitRejectsShortName(): void
    {
        [$controller, $em, $mailer] = $this->submitDeps();
        $em->expects($this->never())->method('persist');

        $controller->submit(
            $this->post(['name' => 'ab', 'description' => 'Something']),
            $this->makeAuth(true, userId: 7),
            $this->makeSeasonWeek(2026),
            $em,
            $mailer
        );

        $this->assertSame('proposals/submit.html.twig', $controller->renderedView);
        $this->assertNotEmpty($controller->flashes);
    }

    public function testSubmitRejectsMissingDescriptionAndRationale(): void
    {
        [$controller, $em, $mailer] = $this->submitDeps();
        $em->expects($this->never())->method('persist');

        $controller->submit(
            $this->post(['name' => 'A real name', 'description' => '', 'rationale' => '']),
            $this->makeAuth(true, userId: 7),
            $this->makeSeasonWeek(2026),
            $em,
            $mailer
        );

        $this->assertContains(
            ['error', 'Must include a description or rationale explaining the proposal'],
            $controller->flashes
        );
    }

    public function testSubmitWritesPendingRowWithSubmitterAsFirstSponsor(): void
    {
        [$controller, $em, $mailer] = $this->submitDeps();
        $user = $this->makeUser(7);
        $em->method('find')->with(User::class, 7)->willReturn($user);

        $persisted = null;
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(function (Issue $i) use (&$persisted) {
                $persisted = $i;
            });
        $em->expects($this->once())->method('flush');
        $mailer->expects($this->once())->method('sendProposalSubmitted');

        $response = $controller->submit(
            $this->post([
                'name' => 'Reduce Roster Size',
                'description' => 'Go from 16 to 14 rounds',
                'rationale' => 'Because **reasons**',
                'ruleChange' => '> Change rule IV.A',
            ]),
            $this->makeAuth(true, userId: 7, fullName: 'Rich Lawson'),
            $this->makeSeasonWeek(2026),
            $em,
            $mailer
        );

        $this->assertNotNull($persisted);
        $this->assertFalse($persisted->isPublished());
        $this->assertSame(IssueStatus::Open, $persisted->getStatus());
        $this->assertSame(2026, $persisted->getSeason());
        $this->assertSame('Reduce Roster Size', $persisted->getIssueName());
        $this->assertSame('Because **reasons**', $persisted->getRationale());
        $this->assertCount(1, $persisted->getSponsors());
        $first = $persisted->getSponsors()->first();
        $this->assertSame($user, $first->getUser());
        $this->assertSame(0, $first->getSortOrder());
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testSubmitRejectsInvalidCsrf(): void
    {
        [$controller, $em, $mailer] = $this->submitDeps();
        $controller->csrfValid = false;
        $em->expects($this->never())->method('persist');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->submit(
            $this->post(['name' => 'A real name', 'description' => 'x']),
            $this->makeAuth(true, userId: 7),
            $this->makeSeasonWeek(2026),
            $em,
            $mailer
        );
    }

    // ---- helpers ----

    private function submitDeps(): array
    {
        return [
            $this->makeController(),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ProposalMailer::class),
        ];
    }

    private function makeController(): ProposalController
    {
        return new class extends ProposalController {
            public bool $csrfValid = true;
            public ?string $renderedView = null;
            public ?array $renderedParams = null;
            public array $flashes = [];

            protected function isCsrfTokenValid(string $id, #[\SensitiveParameter] ?string $token): bool
            {
                return $this->csrfValid;
            }

            protected function render(string $view, array $parameters = [], ?Response $response = null): Response
            {
                $this->renderedView = $view;
                $this->renderedParams = $parameters;
                return new Response();
            }

            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
            {
                return new RedirectResponse("/$route", $status);
            }

            protected function addFlash(string $type, mixed $message): void
            {
                $this->flashes[] = [$type, $message];
            }
        };
    }

    private function makeAuth(bool $loggedIn, ?int $userId = null, ?string $fullName = null): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getUserId')->willReturn($userId);
        $auth->method('getFullName')->willReturn($fullName);
        return $auth;
    }

    private function makeSeasonWeek(int $season): SeasonWeekService
    {
        $sw = $this->createStub(SeasonWeekService::class);
        $sw->method('getCurrentSeason')->willReturn($season);
        return $sw;
    }

    /**
     * EntityManager whose connection returns the given IssueIDs as the
     * "on ballot" set for the list view.
     *
     * @param int[] $onBallotIssueIds
     */
    private function makeListEm(array $onBallotIssueIds = []): EntityManagerInterface
    {
        $rows = array_map(static fn (int $id) => ['IssueID' => $id], $onBallotIssueIds);
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn($rows);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);
        return $em;
    }

    private function makeUser(int $id): User
    {
        $user = new User();
        $ref = new \ReflectionProperty(User::class, 'id');
        $ref->setValue($user, $id);
        return $user;
    }

    private function post(array $params): Request
    {
        return Request::create('/rules/proposals/submit', 'POST', $params);
    }
}
