<?php

namespace App\Model;

use App\Service\ScoringRuleRegistry;

/**
 * One season's scoring parameters: the season's stored overrides merged
 * over the registry defaults. A null value means the category is not
 * awarded that season.
 */
class ScoringRules
{
    /** @param array<string, mixed> $values complete key => value map */
    private function __construct(
        private readonly array $values,
        private readonly string $strategy
    ) {
    }

    /** @param array<string, mixed> $overrides the season's stored scoring_rules map */
    public static function fromArray(array $overrides, string $strategy = 'standard'): self
    {
        $values = array_replace(ScoringRuleRegistry::defaults(), array_intersect_key($overrides, ScoringRuleRegistry::definitions()));

        return new self($values, $strategy);
    }

    public static function defaults(): self
    {
        return new self(ScoringRuleRegistry::defaults(), 'standard');
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /** Whether the category scores at all this season (null = not awarded). */
    public function awards(string $key): bool
    {
        return ($this->values[$key] ?? null) !== null;
    }

    /** Point value / threshold; 0 when the category is not awarded. */
    public function int(string $key): int
    {
        $value = $this->values[$key] ?? null;

        return $value === null ? 0 : (int) $value;
    }

    /** @return list<array{0: ?int, 1: int}> [max, points] pairs, first match wins */
    public function tiers(string $key): array
    {
        $value = $this->values[$key] ?? null;

        return is_array($value) ? array_values($value) : [];
    }

    /** @return array<int, int> exact count => points */
    public function map(string $key): array
    {
        $value = $this->values[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->values;
    }
}
