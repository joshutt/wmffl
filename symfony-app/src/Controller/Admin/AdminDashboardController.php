<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Enum\ActiveEnum;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractAdminController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $users = $em->getRepository(User::class)->findBy(
            ['active' => ActiveEnum::Y],
            ['name' => 'ASC']
        );

        $activeTeams = $em->getRepository(Team::class)->findBy(['active' => true]);

        return $this->render('admin/dashboard/index.html.twig', [
            'season'      => $seasonWeek->getCurrentSeason(),
            'weekName'    => $seasonWeek->getWeekName(),
            'users'       => $users,
            'teamCount'   => count($activeTeams),
        ]);
    }
}
