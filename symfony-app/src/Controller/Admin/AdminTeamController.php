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

class AdminTeamController extends AbstractAdminController
{
    #[Route('/admin/teams', name: 'admin_teams')]
    public function index(
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        return $this->render('admin/team/index.html.twig', [
            'teams' => $em->getRepository(Team::class)->findBy([], ['name' => 'ASC']),
            'divisionNames' => $this->divisionNames($em, $seasonWeek->getCurrentSeason()),
        ]);
    }

    #[Route('/admin/teams/{id}/edit', name: 'admin_teams_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        AuthenticationService $auth,
        SeasonWeekService $seasonWeek,
        EntityManagerInterface $em
    ): Response {
        if ($redirect = $this->requireCommissioner($auth)) {
            return $redirect;
        }

        $team = $em->find(Team::class, $id);
        if (!$team) {
            throw $this->createNotFoundException("No team with id $id");
        }

        $divisionNames = $this->divisionNames($em, $seasonWeek->getCurrentSeason());

        if ($request->isMethod('POST')) {
            $this->assertCsrfToken($request, 'admin_team');

            $division = $request->request->getInt('division');
            if (!isset($divisionNames[$division])) {
                $this->addFlash('error', 'Pick a current division');
            } else {
                $team->setAbbreviation(trim($request->request->get('abbrev', '')));
                $team->setMotto(trim($request->request->get('motto', '')) ?: null);
                $team->setLogo(trim($request->request->get('logo', '')) ?: null);
                $team->setFullLogo($request->request->getBoolean('fulllogo'));
                $team->setActive($request->request->getBoolean('active'));
                $team->setDivision($division);
                $em->flush();
                $this->addFlash('success', 'Team updated');

                return $this->redirectToRoute('admin_teams');
            }
        }

        return $this->render('admin/team/edit.html.twig', [
            'team' => $team,
            'divisionNames' => $divisionNames,
        ]);
    }

    /** @return Division[] divisions spanning the given season */
    private function currentDivisions(EntityManagerInterface $em, int $season): array
    {
        return $em->createQuery(
            'SELECT d FROM App\Entity\Division d WHERE d.endYear >= :season ORDER BY d.name ASC'
        )->setParameter('season', $season)->getResult();
    }

    /** @return array<int, string> current-season division names keyed by id */
    private function divisionNames(EntityManagerInterface $em, int $season): array
    {
        $names = [];
        foreach ($this->currentDivisions($em, $season) as $division) {
            $names[$division->getId()] = $division->getName();
        }

        return $names;
    }

    #[Route('/admin/team/updateTeamInfo', name: 'admin_team_update')]
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
        $divisions = $this->currentDivisions($em, $season);

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
        $this->assertCsrfToken($request, 'admin_team');

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
