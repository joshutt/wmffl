<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Keeps the titles table in step with the season-flags checkboxes
 * (Phase 9a): champion → a League title, division_winner → a Division
 * title for that team-season. Runs after every season-flags save so
 * the past-champions page never needs a hand edit for a new season.
 *
 * Toilet titles are never touched — the toilet-bowl table on the
 * past-champions page is derived from schedule at read time, and
 * season_flags has no toilet flag.
 */
class TitleSyncService
{
    private const FLAG_TO_TYPE = [
        'champion'        => 'League',
        'division_winner' => 'Division',
    ];

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Reconcile the season's League/Division titles with its
     * season_flags rows. Idempotent. A season with no season_flags rows
     * at all is left untouched — historical seasons (≤2023) predate
     * season_flags, and syncing them would wipe their titles.
     */
    public function syncSeason(int $season): void
    {
        $hasFlags = $this->connection->fetchOne(
            'SELECT 1 FROM season_flags WHERE season = :season LIMIT 1',
            ['season' => $season]
        );
        if ($hasFlags === false) {
            return;
        }

        foreach (self::FLAG_TO_TYPE as $flag => $type) {
            $this->connection->executeStatement(
                "INSERT INTO titles (season, type, teamid)
                 SELECT sf.season, :type, sf.teamid
                 FROM season_flags sf
                 WHERE sf.season = :season AND sf.$flag = 1
                   AND NOT EXISTS (
                       SELECT 1 FROM titles t
                       WHERE t.season = sf.season AND t.type = :type
                         AND t.teamid = sf.teamid
                   )",
                ['season' => $season, 'type' => $type]
            );

            $this->connection->executeStatement(
                "DELETE t FROM titles t
                 WHERE t.season = :season AND t.type = :type
                   AND NOT EXISTS (
                       SELECT 1 FROM season_flags sf
                       WHERE sf.season = t.season AND sf.teamid = t.teamid
                         AND sf.$flag = 1
                   )",
                ['season' => $season, 'type' => $type]
            );
        }
    }
}
