<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Enum\VoteEnum;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use App\Service\ProposalMailer;
use App\Service\SeasonRuleService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The rule-proposal ballot: a logged-in team casts one vote per open,
 * published issue it's been put on the ballot for. Ports ballot.php /
 * ballotcount.php, closing the legacy SQL-injection hole (all parameters
 * are bound and only the team's own open issues are writable) and reading
 * the per-season pass/fail thresholds instead of the old .67/.51
 * constants.
 */
class BallotController extends AbstractController
{
    /**
     * Per-issue custom vote labels. Issue 87 asked "10 vs 12 teams", so
     * Accept/Reject/Abstain read differently there. Kept as an isolated
     * data map rather than branching in the template.
     *
     * @var array<int, array{accept: string, reject: string, abstain: string}>
     */
    private const CUSTOM_LABELS = [
        87 => ['accept' => '10 Teams', 'reject' => '12 Teams', 'abstain' => 'No Preference'],
    ];

    #[Route('/rules/ballot', name: 'ballot', methods: ['GET'])]
    public function show(
        AuthenticationService $auth,
        IssueRepository $issues,
        Connection $conn,
    ): Response {
        if (!$auth->isLoggedIn()) {
            return $this->render('proposals/ballot.html.twig', ['isLoggedIn' => false]);
        }

        $teamId = $auth->getTeamNumber();
        $openIssues = $issues->findOpenBallotIssues($teamId);

        return $this->render('proposals/ballot.html.twig', [
            'isLoggedIn' => true,
            'items' => $this->buildItems($openIssues, $this->currentVotes($conn, $teamId)),
        ]);
    }

    #[Route('/rules/ballot', name: 'ballot_cast', methods: ['POST'])]
    public function cast(
        Request $request,
        AuthenticationService $auth,
        IssueRepository $issues,
        Connection $conn,
        SeasonRuleService $seasonRules,
        ProposalMailer $mailer,
    ): Response {
        if (!$auth->isLoggedIn()) {
            return $this->render('proposals/ballot.html.twig', ['isLoggedIn' => false]);
        }

        $this->assertCsrfToken($request);

        $teamId = $auth->getTeamNumber();

        // Only the team's own open, published issues are writable; a
        // crafted issueid in the POST that isn't in this set is ignored,
        // and every value is bound — no interpolation (fixes the legacy
        // ballotcount.php injection).
        $openIssues = $issues->findOpenBallotIssues($teamId);
        $cast = [];

        foreach ($openIssues as $issue) {
            $raw = $request->request->get((string) $issue->getId());
            if ($raw === null) {
                continue;
            }
            $vote = VoteEnum::tryFrom((string) $raw);
            if ($vote === null || $vote === VoteEnum::NoVote) {
                continue; // reject anything that isn't a real Accept/Reject/Abstain
            }

            $conn->executeStatement(
                'UPDATE ballot SET Vote = :vote WHERE IssueID = :issueId AND TeamID = :teamId',
                ['vote' => $vote->value, 'issueId' => $issue->getId(), 'teamId' => $teamId]
            );

            $cast[] = ['issue' => $issue, 'vote' => $vote];

            $this->notifyOnThreshold($issue, $conn, $seasonRules, $mailer);
        }

        return $this->render('proposals/ballot_thanks.html.twig', [
            'cast' => $this->buildCastSummary($cast),
        ]);
    }

    /**
     * Re-tally the issue after a vote and email the commissioner if it
     * just crossed its season's pass or fail threshold.
     */
    private function notifyOnThreshold(
        Issue $issue,
        Connection $conn,
        SeasonRuleService $seasonRules,
        ProposalMailer $mailer,
    ): void {
        $row = $conn->fetchAssociative(
            "SELECT SUM(IF(Vote = 'Accept', 1, 0)) / NULLIF(SUM(IF(Vote <> 'Abstain', 1, 0)), 0) AS pass,
                    SUM(IF(Vote = 'Reject', 1, 0)) / NULLIF(SUM(IF(Vote <> 'Abstain', 1, 0)), 0) AS fail
             FROM ballot WHERE IssueID = :issueId",
            ['issueId' => $issue->getId()]
        );
        if ($row === false || $row['pass'] === null) {
            return;
        }

        $pass = (float) $row['pass'];
        $fail = (float) $row['fail'];
        $season = $issue->getSeason();

        if ($pass >= $seasonRules->getProposalPassThreshold($season)) {
            $mailer->sendThresholdCrossed($issue, true);
        } elseif ($fail >= $seasonRules->getProposalFailThreshold($season)) {
            $mailer->sendThresholdCrossed($issue, false);
        }
    }

    /**
     * The team's current vote per issue id, for pre-checking the radios.
     *
     * @return array<int, string>
     */
    private function currentVotes(Connection $conn, int $teamId): array
    {
        $rows = $conn->fetchAllAssociative(
            'SELECT IssueID, Vote FROM ballot WHERE TeamID = :teamId',
            ['teamId' => $teamId]
        );
        $votes = [];
        foreach ($rows as $row) {
            $votes[(int) $row['IssueID']] = (string) $row['Vote'];
        }

        return $votes;
    }

    /**
     * @param Issue[] $openIssues
     * @param array<int, string> $currentVotes
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(array $openIssues, array $currentVotes): array
    {
        $items = [];
        foreach ($openIssues as $issue) {
            $labels = self::CUSTOM_LABELS[$issue->getId()] ?? [
                'accept' => 'Accept', 'reject' => 'Reject', 'abstain' => 'Abstain',
            ];
            $current = $currentVotes[$issue->getId()] ?? '';

            $items[] = [
                'issue' => $issue,
                'labels' => $labels,
                'currentVote' => $current,
                'currentLabel' => $this->labelFor($current, $labels),
            ];
        }

        return $items;
    }

    /**
     * @param array<int, array{issue: Issue, vote: VoteEnum}> $cast
     * @return array<int, array{num: string, name: string, label: string}>
     */
    private function buildCastSummary(array $cast): array
    {
        $summary = [];
        foreach ($cast as $entry) {
            $issue = $entry['issue'];
            $labels = self::CUSTOM_LABELS[$issue->getId()] ?? [
                'accept' => 'Accept', 'reject' => 'Reject', 'abstain' => 'Abstain',
            ];
            $summary[] = [
                'num' => $issue->getIssueNum() ?: (string) $issue->getSeason(),
                'name' => $issue->getIssueName(),
                'label' => $this->labelFor($entry['vote']->value, $labels),
            ];
        }

        return $summary;
    }

    /** @param array{accept: string, reject: string, abstain: string} $labels */
    private function labelFor(string $vote, array $labels): string
    {
        return match ($vote) {
            'Accept' => $labels['accept'],
            'Reject' => $labels['reject'],
            'Abstain' => $labels['abstain'],
            default => '',
        };
    }

    protected function assertCsrfToken(Request $request): void
    {
        if (!$this->isCsrfTokenValid('ballot_cast', (string) $request->getPayload()->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
