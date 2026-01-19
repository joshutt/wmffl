<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to manage current season and week information.
 * Replaces legacy globals $currentSeason, $currentWeek from connect.php
 */
class SeasonWeekService
{
    private ?array $weekData = null;

    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack
    ) {
    }

    /**
     * Load week data from database (with per-request caching)
     */
    private function loadWeekData(): void
    {
        if ($this->weekData !== null) {
            return;
        }

        // This query matches the legacy logic from football/utils/connect.php lines 29-33
        $sql = <<<EOD
SELECT w1.season, w1.week, w1.weekname, w2.weekname as 'previous'
FROM weekmap w1, weekmap w2
WHERE now() BETWEEN w1.startDate and w1.endDate
and IF(w1.week=0, w2.season=w1.season-1 and w2.week=16, w2.week=w1.week-1 and w2.season=w1.season)
EOD;

        $result = $this->connection->fetchAssociative($sql);

        if (!$result) {
            // Fallback to safe defaults if no current week found
            $this->weekData = [
                'currentSeason' => date('Y'),
                'currentWeek' => 0,
                'weekName' => 'Off Season',
                'previousWeekName' => '',
                'previousWeek' => 16,
                'previousWeekSeason' => date('Y') - 1,
            ];
            return;
        }

        $currentSeason = (int) $result['season'];
        $currentWeek = (int) $result['week'];

        // Handle week 0 (off-season) - matches logic from connect.php lines 37-43
        if ($currentWeek == 0) {
            $previousWeekSeason = $currentSeason - 1;
            $previousWeek = 16;
        } else {
            $previousWeekSeason = $currentSeason;
            $previousWeek = $currentWeek - 1;
        }

        $this->weekData = [
            'currentSeason' => $currentSeason,
            'currentWeek' => $currentWeek,
            'weekName' => $result['weekname'],
            'previousWeekName' => $result['previous'],
            'previousWeek' => $previousWeek,
            'previousWeekSeason' => $previousWeekSeason,
        ];
    }

    public function getCurrentSeason(): int
    {
        $this->loadWeekData();
        return $this->weekData['currentSeason'];
    }

    public function getCurrentWeek(): int
    {
        $this->loadWeekData();
        return $this->weekData['currentWeek'];
    }

    public function getWeekName(): string
    {
        $this->loadWeekData();
        return $this->weekData['weekName'];
    }

    public function getPreviousWeekName(): string
    {
        $this->loadWeekData();
        return $this->weekData['previousWeekName'];
    }

    public function getPreviousWeek(): int
    {
        $this->loadWeekData();
        return $this->weekData['previousWeek'];
    }

    public function getPreviousWeekSeason(): int
    {
        $this->loadWeekData();
        return $this->weekData['previousWeekSeason'];
    }
}
