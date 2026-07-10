<?php

namespace App\Service;

use App\Repository\TransactionRepository;

/**
 * Builds the nested transaction-history structure that legacy
 * transactions.php rendered inline: date -> team -> moves, with each
 * team's trades for a day expanded into "Traded X to the Y in exchange
 * for Z" token streams (text vs. player-link segments).
 */
class TransactionHistoryService
{
    private const METHOD_LABELS = [
        'Cut' => 'Dropped',
        'Sign' => 'Picked Up',
        'Fire' => 'Fired',
        'Hire' => 'Hired',
        'To IR' => 'Moved to IR',
        'From IR' => 'Activated from IR',
        'To COVID' => 'Moved to Covid List',
        'From COVID' => 'Activated Covid List',
    ];

    public function __construct(
        private TransactionRepository $transactions
    ) {
    }

    /**
     * @return array<int, array{date: string, teams: array<int, array{name: string, moves: array, trades: array}>}>
     */
    public function buildHistory(int $year, int $month): array
    {
        $history = [];
        $dateIdx = $teamIdx = -1;
        $currentDate = $currentTeam = $currentMethod = null;

        foreach ($this->transactions->getTransactions($year, $month) as $row) {
            if ($row['displaydate'] !== $currentDate) {
                $history[++$dateIdx] = ['date' => $row['displaydate'], 'teams' => []];
                $currentDate = $row['displaydate'];
                $currentTeam = null;
                $teamIdx = -1;
            }
            if ($row['teamname'] !== $currentTeam) {
                $history[$dateIdx]['teams'][++$teamIdx] = [
                    'name' => $row['teamname'],
                    'moves' => [],
                    'trades' => [],
                ];
                $currentTeam = $row['teamname'];
                $currentMethod = null;
            }

            $team = &$history[$dateIdx]['teams'][$teamIdx];

            if ($row['method'] === 'Trade') {
                // One trade row per traded player, but the whole day's
                // trades render once (legacy $tradeonce flag)
                if ($team['trades'] === []) {
                    $team['trades'] = $this->buildTradeSentences((int) $row['teamid'], $row['rawdate']);
                }
                $currentMethod = null;
                continue;
            }

            if ($row['method'] !== $currentMethod) {
                $team['moves'][] = [
                    'label' => self::METHOD_LABELS[$row['method']] ?? $row['method'],
                    'players' => [],
                ];
                $currentMethod = $row['method'];
            }

            $team['moves'][count($team['moves']) - 1]['players'][] = [
                'playerid' => $row['playerid'],
                'name' => $row['player'],
                'pos' => $row['pos'],
                'nflteam' => $row['nflteam'],
            ];
        }

        return $history;
    }

    /**
     * Ports the trade() sentence builder. Each sentence is a token list;
     * tokens are ['text' => ...] or ['player' => [...]] so the template
     * can link players while keeping "other" legs (cash, picks) as text.
     */
    private function buildTradeSentences(int $teamId, string $date): array
    {
        $sentences = [];
        $tokens = [];
        $currentGroup = null;
        $currentTeam = null;
        $firstPlayer = true;

        foreach ($this->transactions->getTradeDetails($teamId, $date) as $row) {
            if ($row['tradegroup'] !== $currentGroup) {
                if ($tokens !== []) {
                    $sentences[] = $tokens;
                }
                $tokens = [['text' => 'Traded ']];
                $currentGroup = $row['tradegroup'];
                $currentTeam = $row['teamfrom'];
                $firstPlayer = true;
            }
            if ($row['teamfrom'] !== $currentTeam) {
                $tokens[] = ['text' => " to the {$row['teamfrom']} in exchange for "];
                $currentTeam = $row['teamfrom'];
                $firstPlayer = true;
            }
            if (!$firstPlayer) {
                $tokens[] = ['text' => ', '];
            }
            if ($row['other']) {
                $tokens[] = ['text' => $row['other']];
            } else {
                $tokens[] = ['player' => [
                    'playerid' => $row['playerid'],
                    'name' => trim($row['firstname'] . ' ' . $row['lastname']),
                    'pos' => $row['pos'],
                    'nflteam' => $row['team'],
                ]];
            }
            $firstPlayer = false;
        }
        if ($tokens !== []) {
            $sentences[] = $tokens;
        }

        return $sentences;
    }
}
