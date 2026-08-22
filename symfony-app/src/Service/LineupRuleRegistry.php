<?php

namespace App\Service;

/**
 * The single source of truth for lineup legality: how many players a
 * team activates at each position, plus the cross-position flex
 * constraint. Mirrors ScoringRuleRegistry's shape so the same machinery
 * (defaults + per-season overrides in `seasons.lineup_rules`, generated
 * admin fieldsets) applies to both.
 *
 * Consumed by the submit form, the server-side validator
 * (App\Model\LineupRules), the admin override tool and the JS counters,
 * so no page can invent its own position limits.
 *
 * Types: 'position' (a ['min' => n, 'max' => n] pair), 'group' (a list
 * of position keys the flex total spans) and 'int' (the flex total).
 */
final class LineupRuleRegistry
{
    private const DEFINITIONS = [
        'HC' => ['type' => 'position', 'label' => 'Head Coach', 'default' => ['min' => 1, 'max' => 1]],
        'QB' => ['type' => 'position', 'label' => 'Quarterback', 'default' => ['min' => 1, 'max' => 1]],
        'RB' => ['type' => 'position', 'label' => 'Running Back', 'default' => ['min' => 1, 'max' => 2]],
        'WR' => ['type' => 'position', 'label' => 'Wide Receiver', 'default' => ['min' => 2, 'max' => 3]],
        'TE' => ['type' => 'position', 'label' => 'Tight End', 'default' => ['min' => 1, 'max' => 2]],
        'K'  => ['type' => 'position', 'label' => 'Kicker', 'default' => ['min' => 1, 'max' => 1]],
        'OL' => ['type' => 'position', 'label' => 'Offensive Line', 'default' => ['min' => 1, 'max' => 1]],
        'DL' => ['type' => 'position', 'label' => 'Defensive Line', 'default' => ['min' => 2, 'max' => 2]],
        'LB' => ['type' => 'position', 'label' => 'Linebacker', 'default' => ['min' => 2, 'max' => 2]],
        'DB' => ['type' => 'position', 'label' => 'Defensive Back', 'default' => ['min' => 2, 'max' => 2]],

        'flex_group' => [
            'type' => 'group', 'label' => 'Flex positions',
            'default' => ['RB', 'WR', 'TE'],
            'help' => 'Positions whose activations share one combined total',
        ],
        'flex_total' => [
            'type' => 'int', 'label' => 'Flex total',
            'default' => 5,
            'help' => 'Combined activations across the flex positions; blank for no combined limit',
        ],
    ];

    /** @return array<string, array{type: string, label: string, default: mixed, help?: string}> */
    public static function definitions(): array
    {
        return self::DEFINITIONS;
    }

    /** @return array<string, mixed> key => current default value */
    public static function defaults(): array
    {
        return array_map(fn (array $def) => $def['default'], self::DEFINITIONS);
    }

    /**
     * Position keys in lineup order (HC first, DB last). This order also
     * decides the order of the validator's error messages, matching the
     * legacy processActivations.php checks.
     *
     * @return list<string>
     */
    public static function positions(): array
    {
        return array_keys(array_filter(self::DEFINITIONS, fn (array $def) => $def['type'] === 'position'));
    }

    public static function has(string $key): bool
    {
        return isset(self::DEFINITIONS[$key]);
    }

    public static function isPosition(string $key): bool
    {
        return (self::DEFINITIONS[$key]['type'] ?? null) === 'position';
    }

    public static function label(string $key): string
    {
        return self::DEFINITIONS[$key]['label'] ?? $key;
    }
}
