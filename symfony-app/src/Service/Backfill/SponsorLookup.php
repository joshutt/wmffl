<?php

namespace App\Service\Backfill;

/**
 * The three data lookups sponsor resolution needs, behind an interface so
 * the resolver can be unit-tested without a database.
 */
interface SponsorLookup
{
    /**
     * User ids whose name matches exactly (case-insensitive).
     *
     * @return int[]
     */
    public function userIdsByExactName(string $name): array;

    /**
     * Team ids whose season-specific name or abbrev matches (that season's
     * teamnames row, not the current team name).
     *
     * @return int[]
     */
    public function teamIdsBySeasonName(int $season, string $name): array;

    /** The primary (primary=1) owner's user id for a team in a season. */
    public function primaryOwnerUserId(int $teamId, int $season): ?int;
}
