<?php

namespace App\Model;

/**
 * One season's finance parameters (entry fee, fines, payout split),
 * sourced from the seasons table with the pre-2026 constants as
 * defaults.
 */
class FinanceRules
{
    public function __construct(
        public readonly float $entryFee = 75.0,
        public readonly float $illegalActivationFine = 5.0,
        public readonly float $byeWeekActivationFine = 1.0,
        public readonly float $extraTransactionFine = 1.0,
        public readonly int $numOfGames = 84,
        public readonly float $winPercent = 0.25,
        public readonly float $postPercent = 0.5,
        public readonly float $divPercent = 0.05,
        public readonly float $playoffPercent = 0.05,
        public readonly float $finalPercent = 0.25,
        public readonly float $champPercent = 0.50,
    ) {
    }
}
