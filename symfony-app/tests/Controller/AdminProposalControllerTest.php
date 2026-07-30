<?php

namespace App\Tests\Controller;

use App\Controller\Admin\AdminProposalController;
use App\Entity\Ballot;
use App\Entity\Issue;
use App\Entity\IssueSponsor;
use App\Entity\Team;
use App\Entity\User;
use App\Enum\IssueStatus;
use App\Enum\VoteEnum;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class AdminProposalControllerTest extends TestCase
{
    public function testNonCommissionerRedirectedAway(): void
    {
        $controller = $this->makeController();

        $response = $controller->index(
            $this->makeAuth(false),
            $this->createStub(IssueRepository::class),
            $this->createMock(EntityManagerInterface::class)
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testCreateHydratesFieldsAndOrderedSponsors(): void
    {
        $controller = $this->makeController();
        $userA = $this->makeUser(7);
        $userB = $this->makeUser(9);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback(fn ($class, $id) => match ($id) {
            7 => $userA,
            9 => $userB,
            default => null,
        });

        $persisted = null;
        $em->expects($this->once())->method('persist')
            ->willReturnCallback(function (Issue $i) use (&$persisted) {
                $persisted = $i;
            });
        $em->expects($this->once())->method('flush');

        $controller->create(
            $this->post([
                'issueNum' => '2026.5',
                'issueName' => 'New Rule',
                'season' => '2026',
                'status' => 'Open',
                'description' => 'Desc',
                'rationale' => 'Why',
                'ruleChange' => '> change',
                'published' => '1',
                'sponsors' => ['9', '', '7', '9'], // dup 9 ignored second time; order preserved
            ]),
            $this->makeAuth(true),
            $em
        );

        $this->assertNotNull($persisted);
        $this->assertSame('2026.5', $persisted->getIssueNum());
        $this->assertSame('New Rule', $persisted->getIssueName());
        $this->assertSame(2026, $persisted->getSeason());
        $this->assertTrue($persisted->isPublished());
        $this->assertSame('> change', $persisted->getRuleChangeText());

        $sponsors = $persisted->getSponsors()->toArray();
        $this->assertCount(2, $sponsors);
        // Order preserved: userB (9) first at sortOrder 0, userA (7) next at 1.
        $this->assertSame($userB, $sponsors[0]->getUser());
        $this->assertSame(0, $sponsors[0]->getSortOrder());
        $this->assertSame($userA, $sponsors[1]->getUser());
        $this->assertSame(1, $sponsors[1]->getSortOrder());
    }

    public function testUpdateKeepsUnchangedSponsorInPlace(): void
    {
        // Regression: editing an issue whose sponsor set is unchanged must
        // reuse the existing IssueSponsor rows. Removing + re-adding the
        // same composite (Issue, User) PK in one flush threw
        // EntityIdentityCollisionException.
        $controller = $this->makeController();
        $userB = $this->makeUser(9);

        $issue = (new Issue())->setIssueName('X')->setSeason(2025);
        $existing = new IssueSponsor($issue, $userB, 0);
        $issue->addSponsor($existing);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('find')->willReturn($issue);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback(fn ($class, $id) => $id === 9 ? $userB : null);
        // No new sponsor should be persisted for an unchanged set.
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $controller->update(
            1,
            $this->post([
                'issueNum' => '2025.7', 'issueName' => 'X', 'season' => '2025',
                'status' => 'Rejected', 'published' => '1', 'deadline' => '2025-12-01',
                'sponsors' => ['9'],
            ]),
            $this->makeAuth(true),
            $issues,
            $em
        );

        $sponsors = $issue->getSponsors()->toArray();
        $this->assertCount(1, $sponsors);
        // Same object instance kept (reconciled in place, not recreated).
        $this->assertSame($existing, $sponsors[0]);
        $this->assertSame($userB, $sponsors[0]->getUser());
    }

    public function testUpdateDropsRemovedSponsorAndAddsNew(): void
    {
        $controller = $this->makeController();
        $userA = $this->makeUser(7);
        $userB = $this->makeUser(9);

        $issue = (new Issue())->setIssueName('X')->setSeason(2025);
        $existing = new IssueSponsor($issue, $userB, 0);
        $issue->addSponsor($existing);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('find')->willReturn($issue);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback(fn ($class, $id) => match ($id) {
            7 => $userA, 9 => $userB, default => null,
        });
        $em->expects($this->once())->method('flush');

        // Replace sponsor 9 with sponsor 7.
        $controller->update(
            1,
            $this->post([
                'issueNum' => '2025.7', 'issueName' => 'X', 'season' => '2025',
                'status' => 'Open', 'published' => '1',
                'sponsors' => ['7'],
            ]),
            $this->makeAuth(true),
            $issues,
            $em
        );

        $sponsors = array_values($issue->getSponsors()->toArray());
        $this->assertCount(1, $sponsors);
        $this->assertSame($userA, $sponsors[0]->getUser());
        $this->assertSame(0, $sponsors[0]->getSortOrder());
    }

    public function testIndexBuildsBallotTallyAndOnBallotSet(): void
    {
        $controller = $this->makeController();

        $issue = (new Issue())->setIssueName('Rej')->setSeason(2025)->setStatus(IssueStatus::Rejected);
        (new \ReflectionProperty(Issue::class, 'id'))->setValue($issue, 132);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findAllForAdmin')->willReturn([$issue]);

        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([
            ['IssueID' => 132, 'forVotes' => 3, 'against' => 8, 'abstain' => 0, 'novote' => 1],
        ]);

        $teamRepo = $this->createStub(EntityRepository::class);
        $teamRepo->method('findBy')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($conn);
        $em->method('getRepository')->willReturn($teamRepo);

        $controller->index($this->makeAuth(true), $issues, $em);

        $this->assertTrue($controller->renderedParams['onBallot'][132]);
        $this->assertSame(
            ['for' => 3, 'against' => 8, 'abstain' => 0, 'novote' => 1],
            $controller->renderedParams['ballotTally'][132]
        );
    }

    public function testApprovePublishesPendingIssue(): void
    {
        $controller = $this->makeController();
        $issue = (new Issue())->setIssueName('X')->setPublished(false);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('find')->willReturn($issue);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $controller->approve(1, $this->post([]), $this->makeAuth(true), $issues, $em);

        $this->assertTrue($issue->isPublished());
    }

    public function testWithdrawSetsStatus(): void
    {
        $controller = $this->makeController();
        $issue = (new Issue())->setIssueName('X')->setStatus(IssueStatus::Open);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('find')->willReturn($issue);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $controller->withdraw(1, $this->post([]), $this->makeAuth(true), $issues, $em);

        $this->assertSame(IssueStatus::Withdrawn, $issue->getStatus());
    }

    public function testPutOnBallotCreatesBallotRowsAndPublishes(): void
    {
        $controller = $this->makeController();
        $issue = (new Issue())->setIssueName('X')->setPublished(false);

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('find')->willReturn($issue);

        $teamRepo = $this->createStub(EntityRepository::class);
        $teamRepo->method('find')->willReturnCallback(fn ($id) => (new Team()));

        $conn = $this->createStub(Connection::class);
        $conn->method('fetchOne')->willReturn(false); // no existing ballot rows

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($teamRepo);
        $em->method('getConnection')->willReturn($conn);

        $persisted = [];
        $em->method('persist')->willReturnCallback(function ($e) use (&$persisted) {
            $persisted[] = $e;
        });
        $em->expects($this->once())->method('flush');

        $controller->putOnBallot(
            1,
            $this->post(['startDate' => '2026-07-01', 'teams' => ['1', '2']]),
            $this->makeAuth(true),
            $issues,
            $em
        );

        $this->assertTrue($issue->isPublished());
        $ballots = array_filter($persisted, fn ($e) => $e instanceof Ballot);
        $this->assertCount(2, $ballots);
        foreach ($ballots as $b) {
            $this->assertSame(VoteEnum::NoVote, $b->getVote());
        }
    }

    public function testApproveRejectsInvalidCsrf(): void
    {
        $controller = $this->makeController();
        $controller->csrfValid = false;

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('flush');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->approve(1, $this->post([]), $this->makeAuth(true), $this->createStub(IssueRepository::class), $em);
    }

    // ---- helpers ----

    private function makeController(): AdminProposalController
    {
        return new class extends AdminProposalController {
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

            protected function createNotFoundException(string $message = 'Not Found', ?\Throwable $previous = null): \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            {
                return new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException($message);
            }
        };
    }

    private function makeAuth(bool $commissioner): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn(true);
        $auth->method('isCommissioner')->willReturn($commissioner);
        return $auth;
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
        return Request::create('/admin/proposals', 'POST', $params);
    }
}
