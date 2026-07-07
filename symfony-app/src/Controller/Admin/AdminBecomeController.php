<?php

namespace App\Controller\Admin;

use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/become')]
class AdminBecomeController extends AbstractAdminController
{
    #[Route('', name: 'admin_become_as', methods: ['POST'])]
    public function becomeAs(
        Request $request,
        AuthenticationService $auth,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }
        $this->assertCsrfToken($request, 'admin_become');

        $teamId = (int) $request->request->get('teamId');

        $user = $em->getConnection()->fetchAssociative(
            'SELECT teamid, name, username, userid FROM user WHERE teamid = :teamId',
            ['teamId' => $teamId]
        );

        if (!$user) {
            $this->addFlash('error', 'Team not found.');
            return $this->redirectToRoute('admin_dashboard');
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
