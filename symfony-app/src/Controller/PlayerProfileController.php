<?php

namespace App\Controller;

use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerProfileController extends AbstractController
{
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
