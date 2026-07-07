<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerProfileController extends AbstractController
{
    public const PER_PAGE = 50;

    #[Route('/players', name: 'player_index')]
    public function index(
        Request $request,
        PlayerRepository $players,
        EntityManagerInterface $em
    ): Response {
        $filters = [
            'q' => trim($request->query->get('q', '')),
            'team' => $request->query->get('team', ''),
            'nfl' => $request->query->get('nfl', ''),
            'pos' => $request->query->get('pos', ''),
            'inactive' => $request->query->getBoolean('inactive'),
        ];
        $page = max(0, $request->query->getInt('page'));

        $total = $players->countPlayers($filters);

        return $this->render('player/index.html.twig', [
            'players' => $players->searchPlayers($filters, $page, self::PER_PAGE),
            'filters' => $filters,
            'page' => $page,
            'totalPages' => (int) ceil($total / self::PER_PAGE),
            'total' => $total,
            'freeAgentValue' => PlayerRepository::FREE_AGENTS,
            'teams' => $em->getRepository(Team::class)->findBy(['active' => true], ['name' => 'ASC']),
            'nflTeams' => $players->getDistinctNflTeams(),
            'positions' => $players->getDistinctPositions(),
        ]);
    }

    #[Route('/player/{id}', name: 'player_profile')]
    public function profile(int $id, PlayerRepository $playerRepository): Response
    {
        $player = $playerRepository->find($id);

        if (!$player) {
            throw $this->createNotFoundException('Player not found.');
        }

        $currentRoster = $playerRepository->getCurrentRoster($id);
        $rosterHistory = $playerRepository->getRosterHistory($id);
        $statsBySeason = $playerRepository->getStatsBySeason($id);

        $allStatColumns = [
            'yards'      => 'Yards',
            'tds'        => 'TDs',
            'rec'        => 'Rec',
            'intthrow'   => 'Int Thrown',
            'fum'        => 'Fumbles',
            'two_pt'     => '2PT Conv',
            'tackles'    => 'Tackles',
            'sacks'      => 'Sacks',
            'intcatch'   => 'Int',
            'passdefend' => 'Pass Def',
            'returnyards'=> 'Ret Yds',
            'fumrec'     => 'Fum Rec',
            'forcefum'   => 'FF',
            'spec_td'    => 'Spec TDs',
            'safety'     => 'Safety',
            'xp'         => 'XP Made',
            'miss_xp'    => 'XP Miss',
            'fg30'       => 'FG <30',
            'fg40'       => 'FG 30-39',
            'fg50'       => 'FG 40-49',
            'fg60'       => 'FG 50+',
            'miss_fg30'  => 'FG Miss',
            'block_punt' => 'Blk Punt',
            'block_fg'   => 'Blk FG',
            'block_xp'   => 'Blk XP',
            'penalties'  => 'Penalties',
        ];

        $activeStatColumns = [];
        foreach ($allStatColumns as $key => $label) {
            foreach ($statsBySeason as $row) {
                if (!empty($row[$key])) {
                    $activeStatColumns[$key] = $label;
                    break;
                }
            }
        }

        return $this->render('player/profile.html.twig', compact('player', 'currentRoster', 'rosterHistory', 'statsBySeason', 'activeStatColumns'));
    }
}
