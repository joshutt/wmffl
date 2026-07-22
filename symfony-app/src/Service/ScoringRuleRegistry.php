<?php

namespace App\Service;

/**
 * The single source of truth for every scoring parameter: key, label,
 * group, type and the current (2024+) default value. Drives ScoringRules
 * hydration (missing keys fall back to defaults), the admin season form
 * (fieldsets generated per group) and PlayerScorerService line-item
 * labels, so the math, the admin UI and the box-score explanation text
 * can never drift apart.
 *
 * Types: 'int' (a plain point value or threshold), 'tiers' (a list of
 * [max, points] pairs evaluated first-match, null max = "everything
 * else"), 'map' (exact-count => points, with overflow keys handled by
 * the scorer). A season may store null for a key to mean "category not
 * awarded that season".
 */
final class ScoringRuleRegistry
{
    public const GROUPS = [
        'general' => 'General',
        'hc'      => 'Head Coach',
        'qb'      => 'Quarterback',
        'off'     => 'RB / WR (and TE base)',
        'te'      => 'TE reception bonuses',
        'ol'      => 'Offensive Line',
        'k'       => 'Kicker',
        'def'     => 'DL / LB / DB',
    ];

    private const DEFINITIONS = [
        // General
        'illegal_lineup_penalty' => ['group' => 'general', 'type' => 'int', 'default' => 2, 'label' => 'illegal activation penalty', 'help' => 'Team points deducted per illegal or non-playing activation'],
        'spec_td'                => ['group' => 'general', 'type' => 'int', 'default' => 12, 'label' => 'special team touchdowns'],
        'two_pt'                 => ['group' => 'general', 'type' => 'int', 'default' => 2, 'label' => '2-pt conversions'],

        // Head Coach
        'hc_tie'                 => ['group' => 'hc', 'type' => 'int', 'default' => 1, 'label' => 'tie game'],
        'hc_win_base'            => ['group' => 'hc', 'type' => 'int', 'default' => 3, 'label' => 'win'],
        'hc_win_margin_divisor'  => ['group' => 'hc', 'type' => 'int', 'default' => 10, 'label' => 'point difference', 'help' => '+1 per this many points of victory margin'],
        'hc_penalty_tiers'       => ['group' => 'hc', 'type' => 'tiers', 'default' => [[3, 3], [6, 2], [8, 1], [10, 0], [12, -1], [14, -2], [null, -3]], 'label' => 'penalties', 'help' => 'Pairs of [max team penalties, points]; null max matches everything above'],

        // Quarterback
        'qb_yards_min'           => ['group' => 'qb', 'type' => 'int', 'default' => 200, 'label' => 'combined yards', 'help' => 'Yards needed before any yardage points score'],
        'qb_yards_offset'        => ['group' => 'qb', 'type' => 'int', 'default' => 175, 'label' => 'yards offset', 'help' => 'Points = floor((yards - offset) / divisor)'],
        'qb_yards_divisor'       => ['group' => 'qb', 'type' => 'int', 'default' => 25, 'label' => 'yards divisor'],
        'qb_td'                  => ['group' => 'qb', 'type' => 'int', 'default' => 6, 'label' => 'touchdowns'],
        'qb_turnover'            => ['group' => 'qb', 'type' => 'int', 'default' => -2, 'label' => 'turnovers', 'help' => 'Per fumble and per interception thrown'],

        // RB / WR (also the TE base formula)
        'off_yards_min'          => ['group' => 'off', 'type' => 'int', 'default' => 70, 'label' => 'combined yards', 'help' => 'Yards needed before any yardage points score'],
        'off_yards_offset'       => ['group' => 'off', 'type' => 'int', 'default' => 60, 'label' => 'yards offset', 'help' => 'Points = floor((yards - offset) / divisor)'],
        'off_yards_divisor'      => ['group' => 'off', 'type' => 'int', 'default' => 10, 'label' => 'yards divisor'],
        'off_rec_min'            => ['group' => 'off', 'type' => 'int', 'default' => 5, 'label' => 'receptions', 'help' => 'Receptions needed before reception points score'],
        'off_rec_offset'         => ['group' => 'off', 'type' => 'int', 'default' => 4, 'label' => 'receptions offset', 'help' => 'Points = receptions - offset'],
        'off_td'                 => ['group' => 'off', 'type' => 'int', 'default' => 6, 'label' => 'touchdowns'],
        'off_fumble'             => ['group' => 'off', 'type' => 'int', 'default' => -2, 'label' => 'fumbles'],

        // TE reception bonuses (added on top of the RB/WR formula)
        'te_rec_bonus_min'       => ['group' => 'te', 'type' => 'int', 'default' => 2, 'label' => 'bonus from', 'help' => '+1 when receptions are within [from, to]'],
        'te_rec_bonus_max'       => ['group' => 'te', 'type' => 'int', 'default' => 6, 'label' => 'bonus to'],
        'te_rec_double_bonus_at' => ['group' => 'te', 'type' => 'int', 'default' => 4, 'label' => 'double bonus at', 'help' => 'A second +1 at exactly this many receptions'],
        'te_rec_overflow_min'    => ['group' => 'te', 'type' => 'int', 'default' => 12, 'label' => 'overflow above', 'help' => '+1 per reception above this'],

        // Offensive Line
        'ol_td'                  => ['group' => 'ol', 'type' => 'int', 'default' => 1, 'label' => 'rushing touchdowns'],
        'ol_yards_min'           => ['group' => 'ol', 'type' => 'int', 'default' => 100, 'label' => 'rushing yards', 'help' => 'Team rushing yards needed before yardage points score'],
        'ol_yards_offset'        => ['group' => 'ol', 'type' => 'int', 'default' => 90, 'label' => 'yards offset', 'help' => 'Points = floor((yards - offset) / divisor)'],
        'ol_yards_divisor'       => ['group' => 'ol', 'type' => 'int', 'default' => 10, 'label' => 'yards divisor'],
        'ol_sack_map'            => ['group' => 'ol', 'type' => 'map', 'default' => [0 => 5, 1 => 2, 2 => 1, 3 => 0, 4 => 0, 5 => -1, 6 => -2, 7 => -5], 'label' => 'sacks allowed', 'help' => 'Exact sacks-allowed => points'],
        'ol_sack_overflow_offset' => ['group' => 'ol', 'type' => 'int', 'default' => 6, 'label' => 'sack overflow offset', 'help' => 'Above the map: points = (sacks - offset) * per'],
        'ol_sack_overflow_per'   => ['group' => 'ol', 'type' => 'int', 'default' => -5, 'label' => 'sack overflow per'],

        // Kicker
        'k_xp'                   => ['group' => 'k', 'type' => 'int', 'default' => 1, 'label' => 'extra points'],
        'k_miss_xp'              => ['group' => 'k', 'type' => 'int', 'default' => -1, 'label' => 'missed extra points'],
        'k_fg30'                 => ['group' => 'k', 'type' => 'int', 'default' => 3, 'label' => 'field goals (0-39 yards)'],
        'k_fg40'                 => ['group' => 'k', 'type' => 'int', 'default' => 4, 'label' => 'field goals (40-49 yards)'],
        'k_fg50'                 => ['group' => 'k', 'type' => 'int', 'default' => 5, 'label' => 'field goals (50-59 yards)'],
        'k_fg60'                 => ['group' => 'k', 'type' => 'int', 'default' => 7, 'label' => 'field goals (60+ yards)'],
        'k_miss_fg'              => ['group' => 'k', 'type' => 'int', 'default' => -1, 'label' => 'missed field goals'],

        // Defense
        'def_tackle'             => ['group' => 'def', 'type' => 'int', 'default' => 1, 'label' => 'tackles'],
        'def_pass_defend'        => ['group' => 'def', 'type' => 'int', 'default' => 1, 'label' => 'pass defense'],
        'def_sack'               => ['group' => 'def', 'type' => 'int', 'default' => 2, 'label' => 'sacks'],
        'def_sack_bonus_min'     => ['group' => 'def', 'type' => 'int', 'default' => 3, 'label' => 'sack bonus from', 'help' => 'At this many sacks, bonus = floor(sacks - bonus offset)'],
        'def_sack_bonus_offset'  => ['group' => 'def', 'type' => 'int', 'default' => 2, 'label' => 'sack bonus offset'],
        'def_int'                => ['group' => 'def', 'type' => 'int', 'default' => 4, 'label' => 'interceptions'],
        'def_fum_rec'            => ['group' => 'def', 'type' => 'int', 'default' => 2, 'label' => 'fumbles recovered'],
        'def_force_fum'          => ['group' => 'def', 'type' => 'int', 'default' => 3, 'label' => 'forced fumbles'],
        'def_return_yards_divisor' => ['group' => 'def', 'type' => 'int', 'default' => 20, 'label' => 'return yards', 'help' => '+1 per this many return yards'],
        'def_td'                 => ['group' => 'def', 'type' => 'int', 'default' => 9, 'label' => 'touchdowns'],
        'def_safety'             => ['group' => 'def', 'type' => 'int', 'default' => 6, 'label' => 'safeties'],
        'def_block'              => ['group' => 'def', 'type' => 'int', 'default' => 3, 'label' => 'blocked kicks'],
    ];

    /** @return array<string, array{group: string, type: string, default: mixed, label: string, help?: string}> */
    public static function definitions(): array
    {
        return self::DEFINITIONS;
    }

    /** @return array<string, mixed> key => current default value */
    public static function defaults(): array
    {
        return array_map(fn(array $def) => $def['default'], self::DEFINITIONS);
    }

    /** Definitions grouped for the admin form: group key => [param key => definition] */
    public static function groups(): array
    {
        $grouped = array_fill_keys(array_keys(self::GROUPS), []);
        foreach (self::DEFINITIONS as $key => $def) {
            $grouped[$def['group']][$key] = $def;
        }

        return $grouped;
    }

    public static function has(string $key): bool
    {
        return isset(self::DEFINITIONS[$key]);
    }
}
