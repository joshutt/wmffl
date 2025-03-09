<?php

// football/teams/history/dataFormatting.php

/**
 * @param $wins
 * @param $losses
 * @param $ties
 * @return string
 */
function calculateWinPercentage($wins, $losses, $ties): string
{
    if ($wins + $losses + $ties == 0) {
        return '0.000';
    } else {
        $pct = ($wins * 1.0 + $ties * 0.5) / ($wins + $losses + $ties);
        return sprintf('%5.3f', $pct);
    }
}