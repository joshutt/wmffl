<?php

namespace App\Service\Backfill;

use Doctrine\DBAL\Connection;

/**
 * Database-backed {@see SponsorLookup} over the user / teamnames / owners
 * tables.
 */
class DbSponsorLookup implements SponsorLookup
{
    public function __construct(private Connection $conn)
    {
    }

    public function userIdsByExactName(string $name): array
    {
        $rows = $this->conn->fetchFirstColumn(
            'SELECT UserID FROM user WHERE LOWER(Name) = LOWER(:name)',
            ['name' => $name]
        );

        return array_map('intval', $rows);
    }

    public function teamIdsBySeasonName(int $season, string $name): array
    {
        $rows = $this->conn->fetchFirstColumn(
            'SELECT DISTINCT teamid FROM teamnames
             WHERE season = :season AND (LOWER(name) = LOWER(:name) OR LOWER(abbrev) = LOWER(:name))',
            ['season' => $season, 'name' => $name]
        );

        return array_map('intval', $rows);
    }

    public function primaryOwnerUserId(int $teamId, int $season): ?int
    {
        $id = $this->conn->fetchOne(
            'SELECT userid FROM owners WHERE teamid = :teamId AND season = :season AND `primary` = 1 LIMIT 1',
            ['teamId' => $teamId, 'season' => $season]
        );

        return $id === false ? null : (int) $id;
    }
}
