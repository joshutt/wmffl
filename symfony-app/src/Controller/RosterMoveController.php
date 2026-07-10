<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\RosterMoveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Roster add/drop flow, ported from football/transactions/{list,confirm}.php:
 * search players, preview the moves (picks get either an immediate Add or a
 * waiver-priority control), then confirm to execute.
 */
class RosterMoveController extends AbstractController
{
    public const CSRF_TOKEN_ID = 'roster_move';

    /** NFL team dropdown options (legacy list.php), value => label */
    private const NFL_TEAMS = [
        'ANY' => 'Any', 'NONE' => 'None', 'RET' => 'Retired',
        'ARI' => 'Arizona', 'ATL' => 'Atlanta', 'BAL' => 'Baltimore', 'BUF' => 'Buffalo',
        'CAR' => 'Carolina', 'CHI' => 'Chicago', 'CIN' => 'Cincinnati', 'CLE' => 'Cleveland',
        'DAL' => 'Cowgirls', 'DEN' => 'Denver', 'DET' => 'Detroit', 'GB' => 'Green Bay',
        'IND' => 'Indianapolis', 'HOU' => 'Houston', 'JAC' => 'Jacksonville', 'KC' => 'Kansas City',
        'LV' => 'Las Vegas', 'LAC' => 'LA Chargers', 'LAR' => 'LA Rams', 'MIA' => 'Miami',
        'MIN' => 'Minnesota', 'NE' => 'New England', 'NO' => 'New Orleans',
        'NYG' => 'New York Giants', 'NYJ' => 'New York Jets', 'PHI' => 'Philadelphia',
        'PIT' => 'Pittsburgh', 'SEA' => 'Seattle', 'SF' => 'San Francisco',
        'TB' => 'Tampa Bay', 'TEN' => 'Tennessee', 'WAS' => 'Washington',
    ];

    private const POSITIONS = [
        'HC' => 'Head Coaches', 'QB' => 'Quarterbacks', 'RB' => 'Runningbacks',
        'WR' => 'Wide Recievers', 'TE' => 'Tight Ends', 'K' => 'Kickers',
        'OL' => 'Offensive Lines', 'DL' => 'Defensive Lines', 'LB' => 'Linebackers',
        'DB' => 'Defensive Backs',
    ];

    public function __construct(
        private readonly RosterMoveService $rosterMoves,
        private readonly AuthenticationService $auth
    ) {
    }

    #[Route('/transactions/list', name: 'transactions_list')]
    public function list(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('transactions/list.html.twig', ['loggedIn' => false, 'results' => null, 'criteria' => null]);
        }

        $criteria = [
            'last' => trim((string) $request->query->get('Last', '')),
            'first' => trim((string) $request->query->get('First', '')),
            'position' => (string) $request->query->get('Position', ''),
            'team' => (string) $request->query->get('Team', 'ANY'),
            'available' => (string) $request->query->get('Available', ''),
            'order' => (string) $request->query->get('Order', 'lastname'),
        ];

        $results = null;
        if ($request->query->has('submit')) {
            $results = $this->rosterMoves->searchPlayers($criteria);
        }

        return $this->render('transactions/list.html.twig', [
            'loggedIn' => true,
            'criteria' => $criteria,
            'results' => $results,
            'nflTeams' => self::NFL_TEAMS,
            'positions' => self::POSITIONS,
        ]);
    }

    #[Route('/transactions/confirm', name: 'transactions_confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        if (!$this->auth->isLoggedIn()) {
            return $this->render('transactions/confirm.html.twig', ['loggedIn' => false], new Response(status: Response::HTTP_UNAUTHORIZED));
        }
        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->getPayload()->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $teamId = (int) $this->auth->getTeamNumber();

        if ($request->getPayload()->get('submit') === 'Confirm') {
            $moves = $this->parseMoves($request);
            $errors = $this->rosterMoves->executeMoves(
                $teamId,
                $moves['picks'],
                $moves['drops'],
                $moves['priorities'],
                $moves['updateWaivers']
            );

            if ($errors === []) {
                return $this->redirectToRoute('transactions_history');
            }

            // Re-show the preview with the requested pickups still listed
            // (like legacy, waiver-bound requests reload from the DB only)
            return $this->preview($teamId, $moves['picks'], $errors);
        }

        // First arrival from the player list: pick<id> fields hold ids
        $playlist = [];
        foreach ($request->getPayload()->all() as $key => $value) {
            if (str_starts_with($key, 'pick') && $value !== '' && $value !== null) {
                $playlist[] = (int) $value;
            }
        }

        return $this->preview($teamId, $playlist, []);
    }

    /**
     * Split the confirm form back into moves. Field names mirror legacy:
     * keep<id>/injr<id> = y|n, pick<id> = y|n, prio<id> = priority|n.
     *
     * @return array{picks: int[], drops: int[], priorities: array<int, int>, updateWaivers: bool}
     */
    private function parseMoves(Request $request): array
    {
        $picks = $drops = $priorities = [];
        $updateWaivers = false;

        foreach ($request->getPayload()->all() as $key => $value) {
            if (!preg_match('/^(keep|injr|pick|prio)(\d+)$/', (string) $key, $m)) {
                continue;
            }
            $playerId = (int) $m[2];
            switch ($m[1]) {
                case 'keep':
                case 'injr':
                    if ($value === 'n') {
                        $drops[] = $playerId;
                    }
                    break;
                case 'pick':
                    if ($value === 'y') {
                        $picks[] = $playerId;
                    }
                    break;
                case 'prio':
                    $updateWaivers = true;
                    if ($value !== 'n') {
                        $priorities[(int) $value] = $playerId;
                    }
                    break;
            }
        }

        return ['picks' => $picks, 'drops' => $drops, 'priorities' => $priorities, 'updateWaivers' => $updateWaivers];
    }

    /** @param int[] $playlist requested pickups to show */
    private function preview(int $teamId, array $playlist, array $errors): Response
    {
        $context = $this->rosterMoves->getWaiverContext();
        $existingPicks = $this->rosterMoves->getExistingWaiverPicks($teamId, $context['season'], $context['week']);

        // A pickup goes through waivers when the whole week is in its
        // waiver period or the player is individually waiver-bound
        $waiverBound = $context['isWaiver']
            ? null
            : $this->rosterMoves->getWaiverEligiblePlayerIds($context['season'], $context['week']);

        $pickups = [];
        $newWaiverCount = 0;
        foreach ($this->rosterMoves->getPlayersInfo(array_values(array_unique($playlist))) as $player) {
            $player['isWaive'] = $waiverBound === null || in_array((int) $player['playerid'], $waiverBound, true);
            if ($player['isWaive'] && $player['pos'] !== 'HC') {
                // preselected priority: after the team's existing picks
                $player['defaultPriority'] = count($existingPicks) + ++$newWaiverCount;
            }
            $pickups[] = $player;
        }

        $counts = $this->rosterMoves->getTeamCounts($teamId, $context['season']);

        return $this->render('transactions/confirm.html.twig', [
            'loggedIn' => true,
            'errors' => $errors,
            'counts' => $counts,
            'availableSlots' => min(
                RosterMoveService::TOTAL_ROSTER - $counts['total'],
                RosterMoveService::MAX_ACTIVE_PLAYERS - $counts['active']
            ),
            'maxPlayers' => RosterMoveService::MAX_ACTIVE_PLAYERS,
            'pickups' => $pickups,
            'existingPicks' => $existingPicks,
            'waiverCount' => count($existingPicks) + $newWaiverCount,
            'displayWaiver' => $existingPicks !== [] || $newWaiverCount > 0,
            'roster' => $this->rosterMoves->getCurrentRoster($teamId),
        ]);
    }
}
