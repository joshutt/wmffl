<?php

namespace App\Controller\Admin;

use App\Entity\Division;
use App\Entity\Team;
use App\Entity\TeamNames;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/team')]
class AdminTeamController extends AbstractAdminController
{
    #[Route('/updateTeamInfo', name: 'admin_team_update')]
    public function updateTeamInfo(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season = $seasonWeek->getCurrentSeason();

        $teamNames = $em->getRepository(TeamNames::class)->findBy(['season' => $season]);
        $divisions = $em->createQuery(
            'SELECT d FROM App\Entity\Division d WHERE d.endYear >= :season ORDER BY d.name ASC'
        )->setParameter('season', $season)->getResult();

        $rows = [];
        foreach ($teamNames as $tn) {
            $rows[] = [
                'teamName' => $tn,
                'team'     => $em->find(Team::class, $tn->getTeamId()),
            ];
        }

        return $this->render('admin/team/updateTeamInfo.html.twig', [
            'season'    => $season,
            'rows'      => $rows,
            'divisions' => $divisions,
        ]);
    }

    #[Route('/processUpdateTeam', name: 'admin_team_process', methods: ['POST'])]
    public function processUpdateTeam(
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $season = $seasonWeek->getCurrentSeason();
        $teamNames = $em->getRepository(TeamNames::class)->findBy(['season' => $season]);

        foreach ($teamNames as $tn) {
            $id   = $tn->getTeamId();
            $team = $em->find(Team::class, $id);

            $newName = trim($request->request->get("name-$id", ''));
            $newAbbv = trim($request->request->get("abbv-$id", ''));
            $newLogo = trim($request->request->get("logo-$id", ''));
            $newFull = $request->request->has("full-$id");
            $newDiv  = (int) $request->request->get("division-$id");

            if ($newName !== $tn->getName()) {
                $tn->setName($newName);
                $team->setName($newName);
            }
            if ($newAbbv !== $tn->getAbbrev()) {
                $tn->setAbbrev($newAbbv);
                $team->setAbbreviation($newAbbv);
            }
            if ($newDiv !== $tn->getDivisionId()) {
                $tn->setDivisionId($newDiv);
                $team->setDivision($newDiv);
            }
            if ($newLogo !== ($team->getLogo() ?? '')) {
                $team->setLogo($newLogo ?: null);
            }
            if ($newFull !== $team->isFullLogo()) {
                $team->setFullLogo($newFull);
            }
        }

        $em->flush();

        return $this->redirectToRoute('admin_team_update');
    }
}
