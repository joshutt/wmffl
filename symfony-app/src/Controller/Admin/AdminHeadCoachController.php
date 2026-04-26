<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/headcoach')]
class AdminHeadCoachController extends AbstractAdminController
{
    #[Route('', name: 'admin_headcoach')]
    public function index(
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $teams = $em->getRepository(Team::class)->findBy([], ['name' => 'ASC']);

        $coaches = $em->getConnection()->fetchAllAssociative(<<<SQL
            SELECT p.playerid, CONCAT(p.firstname, ' ', p.lastname) AS name,
                   p.team AS nflTeam, t.name AS wmfflTeam
            FROM newplayers p
            LEFT JOIN roster r ON p.playerid = r.playerid AND r.dateoff IS NULL
            LEFT JOIN team t ON r.teamid = t.teamid
            WHERE p.pos = 'HC'
              AND (p.team <> '' OR t.name IS NOT NULL)
              AND p.active = 1
            ORDER BY p.lastname
            SQL);

        return $this->render('admin/headcoach/index.html.twig', [
            'teams'   => $teams,
            'coaches' => $coaches,
        ]);
    }

    #[Route('/process', name: 'admin_headcoach_process', methods: ['POST'])]
    public function process(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $teamId   = (int) $request->request->get('team');
        $playerId = (int) $request->request->get('player');
        $now      = (new \DateTime())->format('Y-m-d H:i:s');
        $conn     = $em->getConnection();

        // Log Fire transaction for existing HC on this team
        $conn->executeStatement(
            "INSERT INTO transactions (teamid, playerid, method, Date)
             SELECT r.teamid, p.playerid, 'Fire', :now
             FROM roster r
             JOIN newplayers p ON r.playerid = p.playerid AND r.dateoff IS NULL
             WHERE p.pos = 'HC' AND r.teamid = :teamId",
            ['now' => $now, 'teamId' => $teamId]
        );

        // Log Hire transaction for new HC
        $conn->executeStatement(
            "INSERT INTO transactions (teamid, playerid, method, Date)
             VALUES (:teamId, :playerId, 'Hire', :now)",
            ['teamId' => $teamId, 'playerId' => $playerId, 'now' => $now]
        );

        // Close existing HC roster entry
        $conn->executeStatement(
            "UPDATE roster r
             JOIN newplayers p ON r.playerid = p.playerid AND r.dateoff IS NULL
             SET r.dateoff = :now
             WHERE p.pos = 'HC' AND r.teamid = :teamId",
            ['now' => $now, 'teamId' => $teamId]
        );

        // Open new HC roster entry
        $conn->executeStatement(
            "INSERT INTO roster (teamid, playerid, dateon) VALUES (:teamId, :playerId, :now)",
            ['teamId' => $teamId, 'playerId' => $playerId, 'now' => $now]
        );

        return $this->redirectToRoute('admin_headcoach');
    }
}
