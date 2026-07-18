<?php

namespace App\Service;

use App\Model\ScoreLine;
use App\Model\ScoringRules;

/**
 * The one place fantasy scoring math lives. Ported from legacy
 * football/base/scoring.php (the authority), parameterized by a
 * season's ScoringRules. Returns the score as labeled line items whose
 * sum is the player's total, so box-score explanation text is produced
 * by the same computation as the score itself (legacy duplicated every
 * constant between scoreXX() and scoreString()).
 *
 * Line visibility mirrors legacy scoreString(): zero-count categories
 * emit no line, except the HC lines and the OL sacks-allowed line,
 * which always show when the player's team played. A null rule value
 * (category not awarded that season) suppresses the category entirely.
 *
 * Sacks-allowed lookup is deliberately loose (legacy switch): a
 * fractional sack count matches no tier and the overflow only starts a
 * full sack past the last tier. The old ScoreCalculatorService used
 * strict === against DBAL's float sacks values, which silently scored
 * every OL sack tier as 0.
 */
class PlayerScorerService
{
    /** @return ScoreLine[] */
    public function score(string $pos, array $row, ScoringRules $rules): array
    {
        return match ($pos) {
            'HC'             => $this->scoreHC($row, $rules),
            'QB'             => $this->scoreQB($row, $rules),
            'RB', 'WR'       => $this->scoreOffense($row, $rules),
            'TE'             => $this->scoreOffense($row, $rules, teBonuses: true),
            'K'              => $this->scoreK($row, $rules),
            'OL'             => $this->scoreOL($row, $rules),
            'DL', 'LB', 'DB' => $this->scoreDefense($row, $rules),
            default          => [],
        };
    }

    public function total(string $pos, array $row, ScoringRules $rules): int
    {
        return (int) array_sum(array_map(fn(ScoreLine $l) => $l->points, $this->score($pos, $row, $rules)));
    }

    /** @return ScoreLine[] */
    private function scoreHC(array $row, ScoringRules $rules): array
    {
        if ($this->num($row, 'played') <= 0) {
            return [];
        }

        $lines = [];
        $ptdiff = (int) $this->num($row, 'ptdiff');
        if ($rules->awards('hc_win_base')) {
            $pts = 0;
            if ($ptdiff == 0) {
                $pts = $rules->int('hc_tie');
            } elseif ($ptdiff > 0) {
                $pts = $rules->int('hc_win_base') + (int) floor($ptdiff / $rules->int('hc_win_margin_divisor'));
            }
            $lines[] = new ScoreLine('point difference', $pts, $ptdiff);
        }

        if ($rules->awards('hc_penalty_tiers')) {
            $penalties = (int) $this->num($row, 'penalties');
            $pts = 0;
            foreach ($rules->tiers('hc_penalty_tiers') as [$max, $tierPts]) {
                if ($max === null || $penalties <= $max) {
                    $pts = (int) $tierPts;
                    break;
                }
            }
            $lines[] = new ScoreLine('penalties', $pts, $penalties);
        }

        return $lines;
    }

    /** @return ScoreLine[] */
    private function scoreQB(array $row, ScoringRules $rules): array
    {
        $lines = [];
        $yards = (int) $this->num($row, 'yards');
        if ($yards != 0 && $rules->awards('qb_yards_min')) {
            $pts = $yards >= $rules->int('qb_yards_min')
                ? (int) floor(($yards - $rules->int('qb_yards_offset')) / $rules->int('qb_yards_divisor'))
                : 0;
            $lines[] = new ScoreLine('combined yards', $pts, $yards);
        }

        $this->countLine($lines, $row, 'tds', 'touchdowns', $rules, 'qb_td');
        $this->countLine($lines, $row, '2pt', '2-pt conversions', $rules, 'two_pt');
        $this->countLine($lines, $row, 'fum', 'fumbles', $rules, 'qb_turnover');
        $this->countLine($lines, $row, 'intthrow', 'interceptions', $rules, 'qb_turnover');

        return $lines;
    }

    /** @return ScoreLine[] */
    private function scoreOffense(array $row, ScoringRules $rules, bool $teBonuses = false): array
    {
        $lines = [];
        $yards = (int) $this->num($row, 'yards');
        if ($yards != 0 && $rules->awards('off_yards_min')) {
            $pts = $yards >= $rules->int('off_yards_min')
                ? (int) floor(($yards - $rules->int('off_yards_offset')) / $rules->int('off_yards_divisor'))
                : 0;
            $lines[] = new ScoreLine('combined yards', $pts, $yards);
        }

        $rec = (int) $this->num($row, 'rec');
        if ($rec > 0 && $rules->awards('off_rec_min')) {
            $pts = $rec >= $rules->int('off_rec_min') ? $rec - $rules->int('off_rec_offset') : 0;
            if ($teBonuses) {
                if ($rec >= $rules->int('te_rec_bonus_min') && $rec <= $rules->int('te_rec_bonus_max')) {
                    $pts += 1;
                }
                if ($rec == $rules->int('te_rec_double_bonus_at')) {
                    $pts += 1;
                }
                if ($rec > $rules->int('te_rec_overflow_min')) {
                    $pts += $rec - $rules->int('te_rec_overflow_min');
                }
            }
            $lines[] = new ScoreLine('receptions', $pts, $rec);
        }

        $this->countLine($lines, $row, 'tds', 'touchdowns', $rules, 'off_td');
        $this->countLine($lines, $row, 'specTD', 'special team touchdowns', $rules, 'spec_td');
        $this->countLine($lines, $row, '2pt', '2-pt conversions', $rules, 'two_pt');
        $this->countLine($lines, $row, 'fum', 'fumbles', $rules, 'off_fumble');

        return $lines;
    }

    /** @return ScoreLine[] */
    private function scoreK(array $row, ScoringRules $rules): array
    {
        $lines = [];
        $this->countLine($lines, $row, 'XP', 'extra points', $rules, 'k_xp');
        $this->countLine($lines, $row, 'MissXP', 'missed extra points', $rules, 'k_miss_xp');
        $this->countLine($lines, $row, '2pt', '2-pt conversions', $rules, 'two_pt');
        $this->countLine($lines, $row, 'FG30', 'field goals (0-39 yards)', $rules, 'k_fg30');
        $this->countLine($lines, $row, 'FG40', 'field goals (40-49 yards)', $rules, 'k_fg40');
        $this->countLine($lines, $row, 'FG50', 'field goals (50-59 yards)', $rules, 'k_fg50');
        $this->countLine($lines, $row, 'FG60', 'field goals (60+ yards)', $rules, 'k_fg60');
        $this->countLine($lines, $row, 'MissFG30', 'missed field goals', $rules, 'k_miss_fg');
        $this->countLine($lines, $row, 'specTD', 'special team touchdowns', $rules, 'spec_td');

        return $lines;
    }

    /** @return ScoreLine[] */
    private function scoreOL(array $row, ScoringRules $rules): array
    {
        $lines = [];
        $this->countLine($lines, $row, 'tds', 'rushing touchdowns', $rules, 'ol_td');

        $yards = (int) $this->num($row, 'yards');
        if ($yards > 0 && $rules->awards('ol_yards_min')) {
            $pts = $yards >= $rules->int('ol_yards_min')
                ? (int) floor(($yards - $rules->int('ol_yards_offset')) / $rules->int('ol_yards_divisor'))
                : 0;
            $lines[] = new ScoreLine('rushing yards', $pts, $yards);
        }

        if ($this->num($row, 'played') && $rules->awards('ol_sack_map')) {
            $sacks = $this->num($row, 'sacks');
            $map = $rules->map('ol_sack_map');
            $pts = 0;
            $matched = false;
            foreach ($map as $count => $tierPts) {
                if ($sacks == $count) {
                    $pts = (int) $tierPts;
                    $matched = true;
                    break;
                }
            }
            if (!$matched && $map !== [] && $sacks >= max(array_keys($map)) + 1) {
                $pts = (int) (($sacks - $rules->int('ol_sack_overflow_offset')) * $rules->int('ol_sack_overflow_per'));
            }
            $lines[] = new ScoreLine('sacks allowed', $pts, $sacks == (int) $sacks ? (int) $sacks : $sacks);
        }

        return $lines;
    }

    /** @return ScoreLine[] */
    private function scoreDefense(array $row, ScoringRules $rules): array
    {
        $lines = [];
        $this->countLine($lines, $row, 'tackles', 'tackles', $rules, 'def_tackle');
        $this->countLine($lines, $row, 'passdefend', 'pass defense', $rules, 'def_pass_defend');

        $sacks = $this->num($row, 'sacks');
        if ($sacks > 0 && $rules->awards('def_sack')) {
            $pts = (int) floor($sacks * $rules->int('def_sack'));
            if ($sacks >= $rules->int('def_sack_bonus_min')) {
                $pts += (int) floor($sacks - $rules->int('def_sack_bonus_offset'));
            }
            $lines[] = new ScoreLine('sacks', $pts, $sacks == (int) $sacks ? (int) $sacks : $sacks);
        }

        $this->countLine($lines, $row, 'intcatch', 'interceptions', $rules, 'def_int');
        $this->countLine($lines, $row, 'fumrec', 'fumbles recovered', $rules, 'def_fum_rec');
        $this->countLine($lines, $row, 'forcefum', 'forced fumbles', $rules, 'def_force_fum');

        $returnYards = (int) $this->num($row, 'returnyards');
        if ($returnYards > 0 && $rules->awards('def_return_yards_divisor')) {
            $lines[] = new ScoreLine('return yards', (int) floor($returnYards / $rules->int('def_return_yards_divisor')), $returnYards);
        }

        $this->countLine($lines, $row, 'tds', 'touchdowns', $rules, 'def_td');
        $this->countLine($lines, $row, 'specTD', 'special team touchdowns', $rules, 'spec_td');
        $this->countLine($lines, $row, '2pt', '2-pt conversions', $rules, 'two_pt');
        $this->countLine($lines, $row, 'Safety', 'safeties', $rules, 'def_safety');

        $blocks = (int) ($this->num($row, 'blockpunt') + $this->num($row, 'blockxp') + $this->num($row, 'blockfg'));
        if ($blocks > 0 && $rules->awards('def_block')) {
            $lines[] = new ScoreLine('blocked kicks', $blocks * $rules->int('def_block'), $blocks);
        }

        return $lines;
    }

    /** Append a simple count * per-unit-points line when the count is positive and the category is awarded. */
    private function countLine(array &$lines, array $row, string $statKey, string $label, ScoringRules $rules, string $ruleKey): void
    {
        $count = (int) $this->num($row, $statKey);
        if ($count > 0 && $rules->awards($ruleKey)) {
            $lines[] = new ScoreLine($label, $count * $rules->int($ruleKey), $count);
        }
    }

    private function num(array $row, string $key): float
    {
        return (float) ($row[$key] ?? 0);
    }
}
