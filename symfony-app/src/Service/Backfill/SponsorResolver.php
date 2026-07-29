<?php

namespace App\Service\Backfill;

/**
 * Resolves a page sponsor string to a user id per backfill decision 2,
 * in order, with no fuzzy matching:
 *
 *   0. `Commissioner` (a role label, not a name) -> the user Josh Utterback.
 *   1. exact user.Name match -> that user.
 *   2. else the season's teamnames name/abbrev -> that team's primary
 *      owner for that season -> that owner's user.
 *   3. (no sponsor on the page is handled by the parser producing no
 *      sponsor string, so never reaches here.)
 *   4. anything still unresolved (unknown name, no teamnames row that
 *      season, a team with no owner that season, or an ambiguous match)
 *      is flagged, never guessed.
 */
class SponsorResolver
{
    private const COMMISSIONER_LABEL = 'commissioner';
    private const COMMISSIONER_NAME = 'Josh Utterback';

    public function __construct(private SponsorLookup $lookup)
    {
    }

    public function resolve(string $rawName, int $season): ResolvedSponsor
    {
        $name = trim($rawName);
        if ($name === '') {
            return ResolvedSponsor::flagged($rawName, 'empty sponsor string');
        }

        // 0. Commissioner role label -> Josh Utterback.
        if (strtolower($name) === self::COMMISSIONER_LABEL) {
            $ids = $this->lookup->userIdsByExactName(self::COMMISSIONER_NAME);
            if (count($ids) === 1) {
                return ResolvedSponsor::resolved($rawName, $ids[0], 'commissioner');
            }

            return ResolvedSponsor::flagged(
                $rawName,
                'Commissioner special-case could not resolve "' . self::COMMISSIONER_NAME . '"'
            );
        }

        // 1. Exact user name.
        $userIds = $this->lookup->userIdsByExactName($name);
        if (count($userIds) === 1) {
            return ResolvedSponsor::resolved($rawName, $userIds[0], 'user');
        }
        if (count($userIds) > 1) {
            return ResolvedSponsor::flagged($rawName, 'ambiguous: multiple users named "' . $name . '"');
        }

        // 2. Season team name/abbrev -> primary owner.
        $teamIds = $this->lookup->teamIdsBySeasonName($season, $name);
        if (count($teamIds) > 1) {
            return ResolvedSponsor::flagged($rawName, "ambiguous: multiple $season teams match \"$name\"");
        }
        if (count($teamIds) === 1) {
            $ownerId = $this->lookup->primaryOwnerUserId($teamIds[0], $season);
            if ($ownerId !== null) {
                return ResolvedSponsor::resolved($rawName, $ownerId, 'team-owner');
            }

            return ResolvedSponsor::flagged(
                $rawName,
                "team \"$name\" has no primary owner row for $season"
            );
        }

        // 4. Nothing matched.
        return ResolvedSponsor::flagged($rawName, "unknown sponsor \"$name\" (no user or $season team)");
    }
}
