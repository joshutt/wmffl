<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-only vote-tally dashboard for the currently open ballot. Issue
 * authoring, approval and putting issues on the ballot all live in
 * AdminProposalController (/admin/proposals); this page just shows how
 * the open issues are trending.
 */
#[Route('/admin/ballot')]
class AdminBallotController extends AbstractAdminController
{
    #[Route('', name: 'admin_ballot')]
    public function index(
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
                SUM(IF(b.vote = 'Accept',  1, 0)) / NULLIF(SUM(IF(b.vote <> 'Abstain', 1, 0)), 0) AS passRate,
                SUM(IF(b.vote = 'Reject',  1, 0)) / NULLIF(SUM(IF(b.vote <> 'Abstain', 1, 0)), 0) AS rejectRate
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

        return $this->render('admin/ballot/index.html.twig', [
            'summary' => $summary,
            'byIssue' => $byIssue,
        ]);
    }
}
