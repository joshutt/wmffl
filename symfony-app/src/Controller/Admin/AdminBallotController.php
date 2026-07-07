<?php

namespace App\Controller\Admin;

use App\Entity\Ballot;
use App\Entity\Issue;
use App\Entity\Team;
use App\Enum\VoteEnum;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/ballot')]
class AdminBallotController extends AbstractAdminController
{
    private const PAGE_SIZE = 20;

    #[Route('', name: 'admin_ballot')]
    public function index(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $conn = $em->getConnection();

        $summary = $conn->fetchAllAssociative(
            "SELECT i.issuenum,
                SUM(IF(b.vote = 'Accept',  1, 0)) AS yes,
                SUM(IF(b.vote = 'Reject',  1, 0)) AS no,
                SUM(IF(b.vote = 'Abstain', 1, 0)) AS abstain,
                SUM(IF(b.vote = 'No Vote', 1, 0)) AS novote,
                SUM(IF(b.vote = 'Accept',  1, 0)) / SUM(IF(b.vote <> 'Abstain', 1, 0)) AS passRate,
                SUM(IF(b.vote = 'Reject',  1, 0)) / SUM(IF(b.vote <> 'Abstain', 1, 0)) AS rejectRate
             FROM issues i
             JOIN ballot b ON i.issueid = b.issueid
             WHERE i.startDate <= NOW()
               AND (i.deadline IS NULL OR i.deadline >= NOW())
             GROUP BY i.issuenum"
        );

        $detail = $conn->fetchAllAssociative(
            "SELECT i.issuenum, t.name AS team, b.vote
             FROM team t
             JOIN ballot b ON b.teamid = t.teamid
             JOIN issues i ON i.issueid = b.issueid
             WHERE i.startDate <= NOW()
               AND (i.deadline IS NULL OR i.deadline >= NOW())
             ORDER BY i.issuenum, b.vote, t.name"
        );

        $byIssue = [];
        foreach ($detail as $row) {
            $byIssue[$row['issuenum']][] = $row;
        }

        $page      = max(1, (int) $request->query->get('page', 1));
        $total     = $em->getRepository(Issue::class)->count([]);
        $totalPages = (int) ceil($total / self::PAGE_SIZE);
        $page      = min($page, max(1, $totalPages));

        $allIssues = $em->getRepository(Issue::class)->findBy(
            [],
            ['season' => 'DESC', 'issueNum' => 'ASC'],
            self::PAGE_SIZE,
            ($page - 1) * self::PAGE_SIZE
        );

        $teams = $em->getRepository(Team::class)->findBy(['active' => true], ['name' => 'ASC']);

        return $this->render('admin/ballot/index.html.twig', [
            'summary'    => $summary,
            'byIssue'    => $byIssue,
            'allIssues'  => $allIssues,
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
            'teams'      => $teams,
        ]);
    }

    #[Route('/new', name: 'admin_ballot_new', methods: ['GET'])]
    public function new(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $teams = $em->getRepository(Team::class)->findBy(['active' => true], ['name' => 'ASC']);

        return $this->render('admin/ballot/form.html.twig', [
            'issue'         => null,
            'teams'         => $teams,
            'currentSeason' => $seasonWeek->getCurrentSeason(),
        ]);
    }

    #[Route('/new', name: 'admin_ballot_create', methods: ['POST'])]
    public function create(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_ballot');

        $issue = new Issue();
        $this->hydrateIssue($issue, $request, $em);
        $em->persist($issue);
        $em->flush();

        return $this->redirectToRoute('admin_ballot');
    }

    #[Route('/{id}/edit', name: 'admin_ballot_edit', methods: ['GET'])]
    public function edit(
        int $id,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $issue = $em->getRepository(Issue::class)->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $teams = $em->getRepository(Team::class)->findBy(['active' => true], ['name' => 'ASC']);

        return $this->render('admin/ballot/form.html.twig', [
            'issue'         => $issue,
            'teams'         => $teams,
            'currentSeason' => $issue->getSeason(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_ballot_update', methods: ['POST'])]
    public function update(
        int $id,
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $this->assertCsrfToken($request, 'admin_ballot');

        $issue = $em->getRepository(Issue::class)->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $this->hydrateIssue($issue, $request, $em);
        $em->flush();

        return $this->redirectToRoute('admin_ballot');
    }

    #[Route('/{id}/publish', name: 'admin_ballot_publish', methods: ['POST'])]
    public function publish(
        int $id,
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em,
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_ballot_publish');

        $issue = $em->getRepository(Issue::class)->find($id);
        if (!$issue) {
            throw $this->createNotFoundException();
        }

        $issue->setStartDate(new \DateTime($request->request->get('startDate')));

        $selectedTeamIds = $request->request->all('teams');
        foreach ($selectedTeamIds as $teamId) {
            $team = $em->getRepository(Team::class)->find((int) $teamId);
            if (!$team) {
                continue;
            }

            $ballot = new Ballot();
            $ballot->setIssue($issue);
            $ballot->setTeam($team);
            $ballot->setVote(VoteEnum::NoVote);
            $em->persist($ballot);
        }

        $em->flush();

        return $this->redirectToRoute('admin_ballot');
    }

    private function hydrateIssue(Issue $issue, Request $request, EntityManagerInterface $em): void
    {
        $issue->setIssueNum($request->request->get('issueNum'));
        $issue->setIssueName($request->request->get('issueName'));
        $issue->setSeason((int) $request->request->get('season'));
        $issue->setDescription($request->request->get('description') ?: null);
        $issue->setResult($request->request->get('result') ?: null);

        $sponsorId = $request->request->get('sponsor');
        $issue->setSponsor($sponsorId ? $em->getRepository(Team::class)->find($sponsorId) : null);

        $startDate = $request->request->get('startDate');
        $issue->setStartDate($startDate ? new \DateTime($startDate) : null);

        $deadline = $request->request->get('deadline');
        $issue->setDeadline($deadline ? new \DateTime($deadline) : null);
    }
}
