<?php

namespace App\Tests\Service\Backfill;

use App\Service\Backfill\SponsorLookup;
use App\Service\Backfill\SponsorResolver;
use PHPUnit\Framework\TestCase;

class SponsorResolverTest extends TestCase
{
    public function testCommissionerResolvesToJoshUtterback(): void
    {
        $resolver = new SponsorResolver($this->lookup(
            usersByName: ['josh utterback' => [2]],
        ));

        $result = $resolver->resolve('Commissioner', 2005);

        $this->assertTrue($result->isResolved());
        $this->assertSame(2, $result->userId);
        $this->assertSame('commissioner', $result->method);
    }

    public function testExactUserNameResolves(): void
    {
        $resolver = new SponsorResolver($this->lookup(
            usersByName: ['richard lawson' => [17]],
        ));

        $result = $resolver->resolve('Richard Lawson', 2026);

        $this->assertTrue($result->isResolved());
        $this->assertSame(17, $result->userId);
        $this->assertSame('user', $result->method);
    }

    public function testTeamNameResolvesToSeasonOwner(): void
    {
        // "Gallic Warriors" is not a user, but is the 2005 name of team 4,
        // whose 2005 primary owner is user 8.
        $resolver = new SponsorResolver($this->lookup(
            teamsBySeasonName: ['2005|gallic warriors' => [4]],
            ownersByTeamSeason: ['4|2005' => 8],
        ));

        $result = $resolver->resolve('Gallic Warriors', 2005);

        $this->assertTrue($result->isResolved());
        $this->assertSame(8, $result->userId);
        $this->assertSame('team-owner', $result->method);
    }

    public function testTeamWithNoOwnerThatSeasonIsFlagged(): void
    {
        $resolver = new SponsorResolver($this->lookup(
            teamsBySeasonName: ['2005|norsemen' => [5]],
            ownersByTeamSeason: [], // no owner row for team 5 in 2005
        ));

        $result = $resolver->resolve('Norsemen', 2005);

        $this->assertFalse($result->isResolved());
        $this->assertStringContainsString('no primary owner', $result->flagReason);
    }

    public function testUnknownNameIsFlagged(): void
    {
        $resolver = new SponsorResolver($this->lookup());

        $result = $resolver->resolve('Someone Unknown', 2005);

        $this->assertFalse($result->isResolved());
        $this->assertStringContainsString('unknown sponsor', $result->flagReason);
    }

    public function testAmbiguousUserNameIsFlagged(): void
    {
        $resolver = new SponsorResolver($this->lookup(
            usersByName: ['chris' => [3, 9]],
        ));

        $result = $resolver->resolve('Chris', 2005);

        $this->assertFalse($result->isResolved());
        $this->assertStringContainsString('ambiguous', $result->flagReason);
    }

    public function testAmbiguousTeamNameIsFlagged(): void
    {
        $resolver = new SponsorResolver($this->lookup(
            teamsBySeasonName: ['2005|eagles' => [4, 6]],
        ));

        $result = $resolver->resolve('Eagles', 2005);

        $this->assertFalse($result->isResolved());
        $this->assertStringContainsString('ambiguous', $result->flagReason);
    }

    public function testUserMatchTakesPrecedenceOverTeamName(): void
    {
        // A name that is both a user and a team name resolves to the user.
        $resolver = new SponsorResolver($this->lookup(
            usersByName: ['illuminati' => [11]],
            teamsBySeasonName: ['2005|illuminati' => [7]],
            ownersByTeamSeason: ['7|2005' => 99],
        ));

        $result = $resolver->resolve('Illuminati', 2005);

        $this->assertSame(11, $result->userId);
        $this->assertSame('user', $result->method);
    }

    /**
     * @param array<string, int[]> $usersByName keyed by lowercased name
     * @param array<string, int[]> $teamsBySeasonName keyed "season|lowername"
     * @param array<string, int>   $ownersByTeamSeason keyed "teamid|season"
     */
    private function lookup(
        array $usersByName = [],
        array $teamsBySeasonName = [],
        array $ownersByTeamSeason = [],
    ): SponsorLookup {
        return new class ($usersByName, $teamsBySeasonName, $ownersByTeamSeason) implements SponsorLookup {
            public function __construct(
                private array $usersByName,
                private array $teamsBySeasonName,
                private array $ownersByTeamSeason,
            ) {
            }

            public function userIdsByExactName(string $name): array
            {
                return $this->usersByName[strtolower($name)] ?? [];
            }

            public function teamIdsBySeasonName(int $season, string $name): array
            {
                return $this->teamsBySeasonName[$season . '|' . strtolower($name)] ?? [];
            }

            public function primaryOwnerUserId(int $teamId, int $season): ?int
            {
                return $this->ownersByTeamSeason[$teamId . '|' . $season] ?? null;
            }
        };
    }
}
