<?php

namespace App\Service;

use App\Repository\TradeOfferRepository;

/**
 * Server-side validation of offer terms, replacing legacy
 * checkambigous.inc.php (and sidestepping its unfinished "ambiguous
 * pick" flow: picks are selected from the giving team's actual
 * draftpicks rows, so the original owner is always known).
 *
 * Input per side: ['players' => int[] playerids,
 *                  'picks'   => int[] draftpicks row ids,
 *                  'points'  => array<season, amount>]
 *
 * Returns ['errors' => string[], 'terms' => [teamId => side]] where each
 * valid side carries the resolved player/pick/point details in the
 * TradeOfferRepository terms shape (picks additionally keep their
 * draftpicks row id).
 */
class TradeValidationService
{
    public const POINTS_FUTURE_SEASONS = 5;

    public function __construct(
        private TradeOfferRepository $offers,
        private SeasonWeekService $seasonWeek
    ) {
    }

    /**
     * First tradeable draft season: next season, except during the
     * offseason (week 0) when the coming draft's picks are still live
     * (legacy edittrade season-dropdown rule).
     */
    public function minPickSeason(): int
    {
        $season = $this->seasonWeek->getCurrentSeason();

        return $this->seasonWeek->getCurrentWeek() === 0 ? $season : $season + 1;
    }

    /**
     * @return array{errors: string[], terms: array<int, array{players: array, picks: array, points: array}>}
     */
    public function validate(int $youTeamId, int $theyTeamId, array $youInput, array $theyInput): array
    {
        $errors = [];
        $terms = [
            $youTeamId => $this->validateSide($youTeamId, 'You', $youInput, $errors),
            $theyTeamId => $this->validateSide($theyTeamId, 'They', $theyInput, $errors),
        ];

        $itemCount = 0;
        foreach ($terms as $side) {
            $itemCount += count($side['players']) + count($side['picks']) + count($side['points']);
        }
        if ($itemCount === 0) {
            $errors[] = 'The trade is empty — select at least one player, pick, or point.';
        }

        return ['errors' => $errors, 'terms' => $terms];
    }

    /**
     * @param array{players?: int[], picks?: int[], points?: array<int, int>} $input
     * @param string[] $errors
     */
    private function validateSide(int $teamId, string $label, array $input, array &$errors): array
    {
        $side = ['players' => [], 'picks' => [], 'points' => []];

        // Players: must be on the giving team's tradeable roster right
        // now, compared by id (the legacy array_search-by-object check
        // failed on index 0 and object identity)
        $roster = [];
        foreach ($this->offers->getTradeableRoster($teamId) as $player) {
            $roster[$player['playerid']] = $player;
        }
        foreach (array_unique(array_map('intval', $input['players'] ?? [])) as $playerId) {
            if (!isset($roster[$playerId])) {
                $errors[] = "$label offered a player (id $playerId) who is not on the roster.";
                continue;
            }
            $side['players'][] = $roster[$playerId];
        }

        // Picks: must be draftpicks rows the giving team currently owns
        $owned = [];
        foreach ($this->offers->getOwnedFuturePicks($teamId, $this->minPickSeason()) as $pick) {
            $owned[$pick['id']] = $pick;
        }
        foreach (array_unique(array_map('intval', $input['picks'] ?? [])) as $pickId) {
            if (!isset($owned[$pickId])) {
                $errors[] = "$label offered a draft pick the team does not own.";
                continue;
            }
            $side['picks'][] = $owned[$pickId];
        }

        // Points: positive amounts within the remaining balance, seasons
        // current..+5 (legacy points-season dropdown range)
        $fromSeason = $this->seasonWeek->getCurrentSeason();
        $toSeason = $fromSeason + self::POINTS_FUTURE_SEASONS;
        $balances = $this->offers->getPointsBalances($teamId, $fromSeason, $toSeason);
        foreach ($input['points'] ?? [] as $season => $amount) {
            $season = (int) $season;
            $amount = (int) $amount;
            if ($amount === 0) {
                continue;
            }
            if ($amount < 0) {
                $errors[] = "$label offered a negative point amount.";
                continue;
            }
            if (!array_key_exists($season, $balances)) {
                $errors[] = "$label offered points in $season, outside the tradeable seasons.";
                continue;
            }
            if ($amount > $balances[$season]) {
                $errors[] = "$label offered $amount points in $season but only {$balances[$season]} remain.";
                continue;
            }
            $side['points'][] = ['season' => $season, 'points' => $amount];
        }

        return $side;
    }
}
