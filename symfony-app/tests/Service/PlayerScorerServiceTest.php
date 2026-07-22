<?php

namespace App\Tests\Service;

use App\Model\ScoreLine;
use App\Model\ScoringRules;
use App\Service\PlayerScorerService;
use PHPUnit\Framework\TestCase;

/**
 * Golden tests: expected totals hand-computed from legacy
 * football/base/scoring.php (the authority) before the old hardcoded
 * scorers were deleted, guarding that the parameterized port is
 * behavior-neutral under default (2024+) rules.
 */
class PlayerScorerServiceTest extends TestCase
{
    private PlayerScorerService $scorer;
    private ScoringRules $rules;

    protected function setUp(): void
    {
        $this->scorer = new PlayerScorerService();
        $this->rules = ScoringRules::defaults();
    }

    private function row(array $overrides = []): array
    {
        return array_merge([
            'played' => 1, 'yards' => 0, 'intthrow' => 0, 'rec' => 0, 'fum' => 0,
            'tackles' => 0, 'sacks' => 0.0, 'intcatch' => 0, 'passdefend' => 0,
            'returnyards' => 0, 'fumrec' => 0, 'forcefum' => 0, 'tds' => 0,
            '2pt' => 0, 'specTD' => 0, 'Safety' => 0, 'XP' => 0, 'MissXP' => 0,
            'FG30' => 0, 'FG40' => 0, 'FG50' => 0, 'FG60' => 0, 'MissFG30' => 0,
            'ptdiff' => 0, 'blockpunt' => 0, 'blockfg' => 0, 'blockxp' => 0,
            'penalties' => 0,
        ], $overrides);
    }

    private function assertTotal(int $expected, string $pos, array $row): void
    {
        $this->assertSame($expected, $this->scorer->total($pos, $row, $this->rules));
        // Invariant: the line items always sum to the total
        $lines = $this->scorer->score($pos, $row, $this->rules);
        $this->assertSame($expected, (int) array_sum(array_map(fn(ScoreLine $l) => $l->points, $lines)));
    }

    public function testHcWinWithMarginAndPenaltyTier(): void
    {
        // win 3 + floor(17/10) = 4, 7 penalties = +1
        $this->assertTotal(5, 'HC', $this->row(['ptdiff' => 17, 'penalties' => 7]));
    }

    public function testHcTieAndWorstPenaltyTier(): void
    {
        // tie 1, 15 penalties = -3
        $this->assertTotal(-2, 'HC', $this->row(['ptdiff' => 0, 'penalties' => 15]));
    }

    public function testHcDidNotPlayScoresNothing(): void
    {
        $this->assertSame([], $this->scorer->score('HC', $this->row(['played' => 0, 'ptdiff' => 20]), $this->rules));
    }

    public function testQbFullStatLine(): void
    {
        // floor((287-175)/25)=4, 2 TD=12, 2pt=2, 1 fum=-2, 2 int=-4
        $this->assertTotal(12, 'QB', $this->row([
            'yards' => 287, 'tds' => 2, '2pt' => 1, 'fum' => 1, 'intthrow' => 2,
        ]));
    }

    public function testQbYardsBelowThresholdScoreZero(): void
    {
        $this->assertTotal(0, 'QB', $this->row(['yards' => 199]));
    }

    public function testRbFullStatLine(): void
    {
        // floor((112-60)/10)=5, 6 rec=2, TD=6, specTD=12, fum=-2
        $this->assertTotal(23, 'RB', $this->row([
            'yards' => 112, 'rec' => 6, 'tds' => 1, 'specTD' => 1, 'fum' => 1,
        ]));
    }

    public function testTeReceptionBonuses(): void
    {
        // yards floor((75-60)/10)=1; 4 rec: base 0 + range bonus 1 + double bonus 1
        $this->assertTotal(3, 'TE', $this->row(['yards' => 75, 'rec' => 4]));
        // 13 rec: base 9 + overflow (13-12)=1
        $this->assertTotal(10, 'TE', $this->row(['rec' => 13]));
    }

    public function testKickerDefaultRules(): void
    {
        // 3 XP - 1 miss + FG30(3) + 2*FG40(8) + FG60(7) - 2 missed FG
        $this->assertTotal(18, 'K', $this->row([
            'XP' => 3, 'MissXP' => 1, 'FG30' => 1, 'FG40' => 2, 'FG60' => 1, 'MissFG30' => 2,
        ]));
    }

    public function testKickerPre2024Fg60Rule(): void
    {
        $rules2023 = ScoringRules::fromArray(['k_fg60' => 10]);
        $row = $this->row(['XP' => 3, 'MissXP' => 1, 'FG30' => 1, 'FG40' => 2, 'FG60' => 1, 'MissFG30' => 2]);

        $this->assertSame(21, $this->scorer->total('K', $row, $rules2023));
    }

    public function testOlYardsTouchdownsAndSackTier(): void
    {
        // TD 1 + floor((145-90)/10)=5 + 3 sacks tier 0
        $this->assertTotal(6, 'OL', $this->row(['tds' => 1, 'yards' => 145, 'sacks' => 3.0]));
    }

    public function testOlZeroSacksScoresFiveDespiteFloatStatValue(): void
    {
        // DBAL returns the float sacks column as 0.0; legacy loose switch
        // matched it (the old ScoreCalculatorService's === 0 did not)
        $this->assertTotal(5, 'OL', $this->row(['sacks' => 0.0]));
    }

    public function testOlSackOverflow(): void
    {
        // 9 sacks: (9-6) * -5
        $this->assertTotal(-15, 'OL', $this->row(['sacks' => 9.0]));
    }

    public function testOlFractionalSacksMatchNoTier(): void
    {
        // legacy switch: 0.5 matches no case and is below the overflow
        $this->assertTotal(0, 'OL', $this->row(['sacks' => 0.5]));
    }

    public function testOlNotPlayedStillScoresYardsAndTds(): void
    {
        // no sacks-allowed line when the line did not play
        $this->assertTotal(4, 'OL', $this->row(['played' => 0, 'tds' => 1, 'yards' => 120]));
    }

    public function testDefenseFullStatLine(): void
    {
        // 7 tackles + sacks floor(3.5*2)=7 + bonus floor(1.5)=1 + INT 4
        // + 2 passdef + fumrec 2 + forcefum 3 + floor(45/20)=2 + TD 9
        // + safety 6 + 2 blocks * 3 = 49
        $this->assertTotal(49, 'DL', $this->row([
            'tackles' => 7, 'sacks' => 3.5, 'intcatch' => 1, 'passdefend' => 2,
            'fumrec' => 1, 'forcefum' => 1, 'returnyards' => 45, 'tds' => 1,
            'Safety' => 1, 'blockpunt' => 1, 'blockfg' => 1,
        ]));
    }

    public function testNullRuleValueSuppressesTheCategory(): void
    {
        $noSpecTd = ScoringRules::fromArray(['spec_td' => null]);
        $row = $this->row(['yards' => 112, 'specTD' => 1]);

        $this->assertSame(5, $this->scorer->total('RB', $row, $noSpecTd));
        $labels = array_map(fn(ScoreLine $l) => $l->label, $this->scorer->score('RB', $row, $noSpecTd));
        $this->assertNotContains('special team touchdowns', $labels);
    }

    public function testLineItemsCarryCountsAndLegacyLabels(): void
    {
        $lines = $this->scorer->score('K', $this->row(['FG40' => 2]), $this->rules);

        $this->assertCount(1, $lines);
        $this->assertSame('field goals (40-49 yards)', $lines[0]->label);
        $this->assertSame(2, $lines[0]->count);
        $this->assertSame(8, $lines[0]->points);
    }

    public function testUnknownPositionScoresNothing(): void
    {
        $this->assertSame([], $this->scorer->score('XX', $this->row(['tds' => 5]), $this->rules));
    }
}
