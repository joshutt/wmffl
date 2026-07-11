<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use App\Service\AuthenticationService;
use App\Service\SeasonWeekService;
use App\Service\TransactionHistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Read-only transaction pages, ported from football/transactions/
 * {transactions,displayWaiverOrder,listwaiverpicks,showprotections}.php
 */
class TransactionController extends AbstractController
{
    public function __construct(
        private readonly TransactionRepository $transactions,
        private readonly SeasonWeekService $seasonWeek,
        private readonly AuthenticationService $auth
    ) {
    }

    #[Route('/transactions', name: 'transactions_history')]
    public function history(Request $request, TransactionHistoryService $historyService): Response
    {
        $latest = $this->transactions->getLastTransactionDate();
        $month = $request->query->getInt('month') ?: $latest['month'];
        $year = $request->query->getInt('year') ?: $latest['year'];

        // Month navigation wraps through the Jan-Aug offseason block:
        // ..., Sep, Oct, Nov, Dec, offseason(next year), Sep, ...
        $previous = $month > 8 ? ['year' => $year, 'month' => $month - 1] : ['year' => $year - 1, 'month' => 12];
        $next = $month < 12 ? ['year' => $year, 'month' => $month + 1] : ['year' => $year + 1, 'month' => 8];

        return $this->render('transactions/history.html.twig', [
            'lastUpdate' => $latest['lastupdate'],
            'history' => $historyService->buildHistory($year, $month),
            'previous' => $previous,
            'next' => $next,
        ]);
    }

    #[Route('/transactions/waivers', name: 'transactions_waivers')]
    public function waivers(): Response
    {
        $season = $this->seasonWeek->getCurrentSeason();
        $week = $this->seasonWeek->getCurrentWeek();

        $memberPicks = null;
        if ($this->auth->isLoggedIn()) {
            $memberPicks = $this->transactions->getMemberWaiverPicks(
                $season,
                $week,
                (int) $this->auth->getTeamNumber()
            );
        }

        return $this->render('transactions/waivers.html.twig', [
            'weekName' => $this->seasonWeek->getWeekName(),
            'order' => $this->transactions->getWaiverOrder($season, $week),
            'memberPicks' => $memberPicks,
            'awards' => $this->transactions->getWaiverAwards($season, $week),
        ]);
    }

    #[Route('/transactions/protections/show', name: 'transactions_protections_show')]
    public function showProtections(Request $request): Response
    {
        $season = $request->query->getInt('season') ?: $this->seasonWeek->getCurrentSeason();
        $byTeam = $request->query->get('order', 'team') !== 'pos';

        // Group into per-team (or per-position) cards like the legacy page
        $groups = [];
        foreach ($this->transactions->getProtections($season, $byTeam) as $row) {
            $key = $byTeam ? $row['name'] : $row['pos'];
            $groups[$key][] = [
                'player' => $row['player'],
                // second column is position when grouped by team, and the
                // owning team's abbreviation when grouped by position
                'detail' => $byTeam ? $row['pos'] : $row['abbrev'],
                'nfl' => $row['nflteamid'],
                'cost' => $row['cost'],
            ];
        }

        return $this->render('transactions/protections_show.html.twig', [
            'season' => $season,
            'byTeam' => $byTeam,
            'groups' => $groups,
            'detailLabel' => $byTeam ? 'Pos' : 'Team',
        ]);
    }
}
