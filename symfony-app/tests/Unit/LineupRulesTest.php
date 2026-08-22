<?php

namespace App\Tests\Unit;

use App\Model\LineupRules;
use App\Service\LineupRuleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Lineup legality lives in exactly one place. These cover the rule
 * branches and the exact wording the legacy processActivations.php
 * checks produced, so the messages owners have read for years survive
 * the port.
 */
class LineupRulesTest extends TestCase
{
    /** 1 HC, 1 QB, 1-2 RB, 2-3 WR, 1-2 TE with RB+WR+TE = 5, 1 K, 1 OL, 2 DL, 2 LB, 2 DB */
    private const LEGAL = [
        'HC' => 1, 'QB' => 1, 'RB' => 2, 'WR' => 2, 'TE' => 1,
        'K' => 1, 'OL' => 1, 'DL' => 2, 'LB' => 2, 'DB' => 2,
    ];

    public function testTheCurrentDefaultLineupIsLegal(): void
    {
        $this->assertSame([], LineupRules::defaults()->validate(self::LEGAL));
    }

    public function testExactCountPositionsRejectTooFewAndTooMany(): void
    {
        $this->assertSame(
            ['You must activate exactly 1 HC'],
            $this->errorsFor(['HC' => 0])
        );
        $this->assertSame(
            ['You must activate exactly 1 QB'],
            $this->errorsFor(['QB' => 2])
        );
        $this->assertSame(
            ['You must activate exactly 1 K'],
            $this->errorsFor(['K' => 0])
        );
        $this->assertSame(
            ['You must activate exactly 1 OL'],
            $this->errorsFor(['OL' => 2])
        );
    }

    public function testExactCountPositionsAboveOnePluralizeLikeLegacy(): void
    {
        $this->assertSame(['You must activate exactly 2 DLs'], $this->errorsFor(['DL' => 1]));
        $this->assertSame(['You must activate exactly 2 LBs'], $this->errorsFor(['LB' => 3]));
        $this->assertSame(['You must activate exactly 2 DBs'], $this->errorsFor(['DB' => 0]));
    }

    public function testRangePositionsReportTheBoundTheyBroke(): void
    {
        // RB 0 also breaks the flex total, which is reported separately
        $this->assertContains('You must activate at least 1 RB', $this->errorsFor(['RB' => 0]));
        $this->assertContains('You can activate at most 2 RBs', $this->errorsFor(['RB' => 3, 'WR' => 2, 'TE' => 0]));
        $this->assertContains('You must activate at least 2 WRs', $this->errorsFor(['WR' => 1, 'TE' => 2]));
        $this->assertContains('You can activate at most 3 WRs', $this->errorsFor(['RB' => 1, 'WR' => 4, 'TE' => 0]));
        $this->assertContains('You must activate at least 1 TE', $this->errorsFor(['RB' => 2, 'WR' => 3, 'TE' => 0]));
        $this->assertContains('You can activate at most 2 TEs', $this->errorsFor(['RB' => 1, 'WR' => 2, 'TE' => 3]));
    }

    /**
     * The whole point of the cross-position rule: each position can be
     * inside its own range while the lineup is still wrong.
     */
    public function testFlexTotalCatchesLineupsEveryPositionAcceptsIndividually(): void
    {
        // 1 RB + 2 WR + 1 TE = 4: every position legal, one slot short
        $short = $this->errorsFor(['RB' => 1, 'WR' => 2, 'TE' => 1]);
        $this->assertSame(['You must activate 1 RB, 2 WR, 1 TE and 1 flex'], $short);

        // 2 RB + 3 WR + 1 TE = 6: every position legal, one too many
        $long = $this->errorsFor(['RB' => 2, 'WR' => 3, 'TE' => 1]);
        $this->assertSame(['You must activate 1 RB, 2 WR, 1 TE and 1 flex'], $long);
    }

    public function testFlexTotalIsSatisfiedByAnyLegalDistribution(): void
    {
        foreach ([[2, 2, 1], [1, 3, 1], [1, 2, 2], [2, 3, 0], [1, 2, 1]] as [$rb, $wr, $te]) {
            $errors = $this->errorsFor(['RB' => $rb, 'WR' => $wr, 'TE' => $te]);
            $flexBroken = in_array('You must activate 1 RB, 2 WR, 1 TE and 1 flex', $errors, true);
            $this->assertSame($rb + $wr + $te !== 5, $flexBroken, "RB $rb / WR $wr / TE $te");
        }
    }

    public function testErrorsComeBackInLegacyOrder(): void
    {
        $errors = LineupRules::defaults()->validate([]);

        $this->assertSame([
            'You must activate exactly 1 HC',
            'You must activate exactly 1 QB',
            'You must activate at least 1 RB',
            'You must activate at least 2 WRs',
            'You must activate at least 1 TE',
            'You must activate 1 RB, 2 WR, 1 TE and 1 flex',
            'You must activate exactly 1 K',
            'You must activate exactly 1 OL',
            'You must activate exactly 2 DLs',
            'You must activate exactly 2 LBs',
            'You must activate exactly 2 DBs',
        ], $errors);
    }

    public function testASeasonOverrideChangesWhatIsAccepted(): void
    {
        $rules = LineupRules::fromArray(['WR' => ['min' => 2, 'max' => 4], 'flex_total' => 6]);

        // Four WRs are legal under the override and not under the defaults
        $counts = ['RB' => 1, 'WR' => 4, 'TE' => 1] + self::LEGAL;
        $this->assertSame([], $rules->validate($counts));
        $this->assertNotSame([], LineupRules::defaults()->validate($counts));

        $this->assertSame(4, $rules->max('WR'));
        $this->assertSame(6, $rules->flexTotal());
    }

    public function testTheOverriddenFlexMessageDescribesTheOverriddenRules(): void
    {
        $rules = LineupRules::fromArray(['flex_total' => 7]);

        $this->assertContains(
            'You must activate 1 RB, 2 WR, 1 TE and 3 flex',
            $rules->validate(['RB' => 2, 'WR' => 2, 'TE' => 1] + self::LEGAL)
        );
    }

    public function testUnknownOverrideKeysAreDropped(): void
    {
        $rules = LineupRules::fromArray(['P' => ['min' => 1, 'max' => 1], 'nonsense' => 5]);

        $this->assertArrayNotHasKey('P', $rules->toArray());
        $this->assertArrayNotHasKey('nonsense', $rules->toArray());
        $this->assertSame(LineupRuleRegistry::positions(), $rules->positions());
    }

    public function testAMalformedOverrideFallsBackToTheRegistryDefault(): void
    {
        $rules = LineupRules::fromArray(['RB' => 'two']);

        $this->assertSame(1, $rules->min('RB'));
        $this->assertSame(2, $rules->max('RB'));
    }

    public function testClearingTheFlexTotalDropsTheCrossPositionRule(): void
    {
        $rules = LineupRules::fromArray(['flex_total' => null]);

        $this->assertNull($rules->flexTotal());
        $this->assertSame([], $rules->validate(['RB' => 2, 'WR' => 3, 'TE' => 2] + self::LEGAL));
    }

    public function testTheJsPayloadCarriesEveryLimitTheCountersNeed(): void
    {
        $json = LineupRules::defaults()->toJson();

        $this->assertSame(['min' => 2, 'max' => 3], $json['positions']['WR']);
        $this->assertSame(['RB', 'WR', 'TE'], $json['flexGroup']);
        $this->assertSame(5, $json['flexTotal']);
        $this->assertSame(LineupRuleRegistry::positions(), array_keys($json['positions']));
    }

    /** @return list<string> */
    private function errorsFor(array $overrides): array
    {
        return LineupRules::defaults()->validate($overrides + self::LEGAL);
    }
}
