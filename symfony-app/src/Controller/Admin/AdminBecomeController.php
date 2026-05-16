<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/become')]
class AdminBecomeController extends AbstractAdminController
{
    #[Route('', name: 'admin_become', methods: ['GET'])]
    public function index(
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $teams = $em->getRepository(Team::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/become/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route('', name: 'admin_become_as', methods: ['POST'])]
    public function becomeAs(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $teamId = (int) $request->request->get('teamId');

        $user = $em->getConnection()->fetchAssociative(
            'SELECT teamid, name, username, userid FROM user WHERE teamid = :teamId',
            ['teamId' => $teamId]
        );

        if (!$user) {
            $this->addFlash('error', 'Team not found.');
            return $this->redirectToRoute('admin_become');
        }

        $auth->becomeTeam(
            (int) $user['teamid'],
            $user['name'],
            $user['username'],
            (int) $user['userid']
        );

        return $this->redirect('/');
    }
}
