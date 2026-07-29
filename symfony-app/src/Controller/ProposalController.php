<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\IssueSponsor;
use App\Entity\User;
use App\Enum\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\AuthenticationService;
use App\Service\ProposalMailer;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Member-facing rule proposals: the season-by-season published list
 * (replacing the hand-written proposals{year}.php pages) and the submit
 * form (replacing propose.php/proposesubmit.php), which writes a pending
 * issue for an admin to approve.
 */
class ProposalController extends AbstractController
{
    #[Route('/rules/proposals', name: 'proposals_list', methods: ['GET'])]
    public function list(
        Request $request,
        IssueRepository $issues,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
    ): Response {
        $seasons = $issues->publishedSeasons();

        $requested = $request->query->getInt('season', 0);
        if ($requested > 0) {
            $season = $requested;
        } else {
            $season = $seasonWeek->getCurrentSeason();
            // Fall back to the newest season that actually has proposals
            // if the current one has none yet.
            if (!in_array($season, $seasons, true) && $seasons !== []) {
                $season = $seasons[0];
            }
        }

        $proposals = $issues->findPublishedBySeason($season);

        // Which of these issues have ballot rows (are "On Ballot" vs merely
        // "In Discussion"). Keyed by IssueID for O(1) lookup in the template.
        $onBallot = [];
        foreach ($em->getConnection()->fetchAllAssociative('SELECT DISTINCT IssueID FROM ballot') as $row) {
            $onBallot[(int) $row['IssueID']] = true;
        }

        return $this->render('proposals/list.html.twig', [
            'season' => $season,
            'seasons' => $seasons,
            'proposals' => $proposals,
            'onBallot' => $onBallot,
        ]);
    }

    #[Route('/rules/proposals/submit', name: 'proposals_submit_form', methods: ['GET'])]
    public function submitForm(AuthenticationService $auth): Response
    {
        return $this->render('proposals/submit.html.twig', [
            'isLoggedIn' => $auth->isLoggedIn(),
            'form' => ['name' => '', 'description' => '', 'rationale' => '', 'ruleChange' => ''],
        ]);
    }

    #[Route('/rules/proposals/submit', name: 'proposals_submit', methods: ['POST'])]
    public function submit(
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
        ProposalMailer $mailer,
    ): Response {
        if (!$auth->isLoggedIn()) {
            return $this->render('proposals/submit.html.twig', [
                'isLoggedIn' => false,
                'form' => ['name' => '', 'description' => '', 'rationale' => '', 'ruleChange' => ''],
            ]);
        }

        $this->assertCsrfToken($request);

        $form = [
            'name' => trim((string) $request->request->get('name', '')),
            'description' => trim((string) $request->request->get('description', '')),
            'rationale' => trim((string) $request->request->get('rationale', '')),
            'ruleChange' => trim((string) $request->request->get('ruleChange', '')),
        ];

        $errors = $this->validate($form);
        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->render('proposals/submit.html.twig', [
                'isLoggedIn' => true,
                'form' => $form,
            ]);
        }

        $season = $seasonWeek->getCurrentSeason();

        $issue = (new Issue())
            ->setIssueNum('')
            ->setIssueName($form['name'])
            ->setDescription($form['description'] ?: null)
            ->setRationale($form['rationale'] ?: null)
            ->setRuleChangeText($form['ruleChange'] ?: null)
            ->setSeason($season)
            ->setStatus(IssueStatus::Open)
            ->setPublished(false);

        $submitter = $em->find(User::class, $auth->getUserId());
        if ($submitter !== null) {
            $issue->addSponsor(new IssueSponsor($issue, $submitter, 0));
        }

        $em->persist($issue);
        $em->flush();

        $mailer->sendProposalSubmitted($issue, $auth->getFullName() ?? 'A member');

        $this->addFlash(
            'success',
            'Your proposal has been submitted and is awaiting approval by the commissioner.'
        );

        return $this->redirectToRoute('proposals_submit_form');
    }

    /** @return string[] */
    private function validate(array $form): array
    {
        $errors = [];
        if (strlen($form['name']) < 3) {
            $errors[] = 'Must include a proposal name of at least 3 characters';
        }
        if (strlen($form['name']) > 120) {
            $errors[] = 'Proposal name can\'t be longer than 120 characters';
        }
        if ($form['description'] === '' && $form['rationale'] === '') {
            $errors[] = 'Must include a description or rationale explaining the proposal';
        }

        return $errors;
    }

    protected function assertCsrfToken(Request $request): void
    {
        if (!$this->isCsrfTokenValid('proposal_submit', (string) $request->getPayload()->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token');
        }
    }
}
