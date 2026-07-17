<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use App\Service\TeamMoneyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Public team-finances ledger, ported from legacy
 * football/history/teammoney.php. The commissioner edit flows stay on
 * /admin/money/*.
 */
class TeamMoneyController extends AbstractController
{
    #[Route('/history/teammoney', name: 'history_teammoney')]
    public function show(
        Request $request,
        TeamMoneyService $money,
        SeasonWeekService $seasonWeek,
        AuthenticationService $auth
    ): Response {
        $currentSeason = $seasonWeek->getCurrentSeason();
        $season = $request->query->getInt('season', $currentSeason);

        // Legacy rollover quirk: during the offseason (week 0, outside
        // August) the current season's ledger isn't meaningful yet —
        // show last season's instead.
        if ($seasonWeek->getCurrentWeek() == 0 && $season == $currentSeason && date('m') != 8) {
            $season = $currentSeason - 1;
        }
        $showNextSeasonFee = $season != $currentSeason;

        $ledger = $money->getLedger($season, $showNextSeasonFee);

        $teamnum = $auth->getTeamNumber();
        $owed = ($auth->isLoggedIn() && $teamnum !== null)
            ? ($ledger['amtOwed'][$teamnum] ?? null)
            : null;

        return $this->render('history/teammoney.html.twig', [
            'season'            => $season,
            'showNextSeasonFee' => $showNextSeasonFee,
            'teams'             => $ledger['teams'],
            'payouts'           => $ledger['payouts'],
            'lastUpdate'        => $ledger['lastUpdate'],
            'owed'              => $owed,
        ]);
    }
}
