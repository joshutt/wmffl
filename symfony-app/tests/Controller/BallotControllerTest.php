<?php

namespace App\Tests\Controller;

use App\Controller\BallotController;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use App\Service\ProposalMailer;
use App\Service\SeasonRuleService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AllowMockObjectsWithoutExpectations]
class BallotControllerTest extends TestCase
{
    public function testShowRejectsAnonymous(): void
    {
        $controller = $this->makeController();

        $controller->show($this->makeAuth(false), $this->createStub(IssueRepository::class), $this->createStub(Connection::class));

        $this->assertSame('proposals/ballot.html.twig', $controller->renderedView);
        $this->assertFalse($controller->renderedParams['isLoggedIn']);
    }

    public function testShowUsesCustomLabelsForIssue87(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(87, '2010.5', 'Expand League');

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([['IssueID' => 87, 'Vote' => 'Accept']]);

        $controller->show($this->makeAuth(true, teamId: 3), $issues, $conn);

        $item = $controller->renderedParams['items'][0];
        $this->assertSame('10 Teams', $item['labels']['accept']);
        $this->assertSame('12 Teams', $item['labels']['reject']);
        $this->assertSame('No Preference', $item['labels']['abstain']);
        $this->assertSame('10 Teams', $item['currentLabel']);
    }

    public function testShowUsesDefaultLabelsForNormalIssue(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2026.1', 'Roster');

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);
        $conn = $this->createStub(Connection::class);
        $conn->method('fetchAllAssociative')->willReturn([]);

        $controller->show($this->makeAuth(true, teamId: 3), $issues, $conn);

        $item = $controller->renderedParams['items'][0];
        $this->assertSame('Accept', $item['labels']['accept']);
        $this->assertSame('', $item['currentVote']);
    }

    // ---- injection neutralised ----

    public function testCastOnlyUpdatesTeamsOwnOpenIssuesWithBoundParams(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2026.1', 'Roster');

        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $updates = [];
        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturnCallback(function (string $sql, array $params) use (&$updates) {
            $updates[] = ['sql' => $sql, 'params' => $params];
            return 1;
        });
        // No threshold email in this scenario.
        $conn->method('fetchAssociative')->willReturn(['pass' => null, 'fail' => null]);

        $mailer = $this->createMock(ProposalMailer::class);
        $mailer->expects($this->never())->method('sendThresholdCrossed');

        // Craft a POST that tries to touch other issue ids and inject SQL.
        $request = $this->post([
            '50' => 'Accept',
            '999' => 'Reject',
            "50' OR '1'='1" => 'Reject',
            '_token' => 't',
        ]);

        $controller->cast(
            $request,
            $this->makeAuth(true, teamId: 3),
            $issues,
            $conn,
            $this->seasonRules(0.51, 0.51),
            $mailer
        );

        // Exactly one UPDATE, for the legitimate issue only, fully bound.
        $this->assertCount(1, $updates);
        $this->assertSame(50, $updates[0]['params']['issueId']);
        $this->assertSame(3, $updates[0]['params']['teamId']);
        $this->assertSame('Accept', $updates[0]['params']['vote']);
        $this->assertStringContainsString(':issueId', $updates[0]['sql']);
        $this->assertStringContainsString(':teamId', $updates[0]['sql']);
        $this->assertStringNotContainsString("OR '1'='1", $updates[0]['sql']);
    }

    public function testCastIgnoresInvalidVoteValues(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2026.1', 'Roster');
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $conn = $this->createMock(Connection::class);
        $conn->expects($this->never())->method('executeStatement');

        $controller->cast(
            $this->post(['50' => 'DROP TABLE ballot', '_token' => 't']),
            $this->makeAuth(true, teamId: 3),
            $issues,
            $conn,
            $this->seasonRules(0.51, 0.51),
            $this->createMock(ProposalMailer::class)
        );
    }

    // ---- threshold crossing ----

    public function testCastEmailsCommissionerWhenPassThresholdCrossed(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2026.1', 'Roster', season: 2026);
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturn(1);
        $conn->method('fetchAssociative')->willReturn(['pass' => '0.80', 'fail' => '0.20']);

        $mailer = $this->createMock(ProposalMailer::class);
        $mailer->expects($this->once())->method('sendThresholdCrossed')
            ->with($issue, true);

        $controller->cast(
            $this->post(['50' => 'Accept', '_token' => 't']),
            $this->makeAuth(true, teamId: 3),
            $issues,
            $conn,
            $this->seasonRules(0.51, 0.51),
            $mailer
        );
    }

    public function testCastThresholdRespectsSeasonSetting(): void
    {
        // 60% pass rate is below a .67 threshold (pre-2022) -> no pass email.
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2010.1', 'Roster', season: 2010);
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturn(1);
        $conn->method('fetchAssociative')->willReturn(['pass' => '0.60', 'fail' => '0.40']);

        $mailer = $this->createMock(ProposalMailer::class);
        $mailer->expects($this->never())->method('sendThresholdCrossed');

        $controller->cast(
            $this->post(['50' => 'Accept', '_token' => 't']),
            $this->makeAuth(true, teamId: 3),
            $issues,
            $conn,
            $this->seasonRules(0.67, 0.51),
            $mailer
        );
    }

    public function testCastRejectsInvalidCsrf(): void
    {
        $controller = $this->makeController();
        $controller->csrfValid = false;
        $conn = $this->createMock(Connection::class);
        $conn->expects($this->never())->method('executeStatement');

        $this->expectException(AccessDeniedHttpException::class);
        $controller->cast(
            $this->post(['50' => 'Accept']),
            $this->makeAuth(true, teamId: 3),
            $this->createStub(IssueRepository::class),
            $conn,
            $this->seasonRules(0.51, 0.51),
            $this->createMock(ProposalMailer::class)
        );
    }

    public function testCastShowsConfirmationOfCastVotes(): void
    {
        $controller = $this->makeController();
        $issue = $this->makeIssue(50, '2026.1', 'Roster');
        $issues = $this->createStub(IssueRepository::class);
        $issues->method('findOpenBallotIssues')->willReturn([$issue]);

        $conn = $this->createMock(Connection::class);
        $conn->method('executeStatement')->willReturn(1);
        $conn->method('fetchAssociative')->willReturn(['pass' => null, 'fail' => null]);

        $controller->cast(
            $this->post(['50' => 'Reject', '_token' => 't']),
            $this->makeAuth(true, teamId: 3),
            $issues,
            $conn,
            $this->seasonRules(0.51, 0.51),
            $this->createMock(ProposalMailer::class)
        );

        $this->assertSame('proposals/ballot_thanks.html.twig', $controller->renderedView);
        $cast = $controller->renderedParams['cast'];
        $this->assertCount(1, $cast);
        $this->assertSame('2026.1', $cast[0]['num']);
        $this->assertSame('Reject', $cast[0]['label']);
    }

    // ---- helpers ----

    private function makeController(): BallotController
    {
        return new class extends BallotController {
            public bool $csrfValid = true;
            public ?string $renderedView = null;
            public ?array $renderedParams = null;

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
        };
    }

    private function makeAuth(bool $loggedIn, ?int $teamId = null): AuthenticationService
    {
        $auth = $this->createStub(AuthenticationService::class);
        $auth->method('isLoggedIn')->willReturn($loggedIn);
        $auth->method('getTeamNumber')->willReturn($teamId);
        return $auth;
    }

    private function seasonRules(float $pass, float $fail): SeasonRuleService
    {
        $s = $this->createStub(SeasonRuleService::class);
        $s->method('getProposalPassThreshold')->willReturn($pass);
        $s->method('getProposalFailThreshold')->willReturn($fail);
        return $s;
    }

    private function makeIssue(int $id, string $num, string $name, int $season = 2026): Issue
    {
        $issue = (new Issue())->setIssueNum($num)->setIssueName($name)->setSeason($season);
        $ref = new \ReflectionProperty(Issue::class, 'id');
        $ref->setValue($issue, $id);
        return $issue;
    }

    private function post(array $params): Request
    {
        return Request::create('/rules/ballot', 'POST', $params);
    }
}
