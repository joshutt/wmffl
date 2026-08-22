<?php

namespace App\Model;

use App\Service\LineupRuleRegistry;

/**
 * One season's lineup limits: the season's stored overrides merged over
 * the registry defaults. The only place lineup legality is decided —
 * the submit form, the admin override and the JS counters all read
 * their limits from here.
 */
class LineupRules
{
    /** @param array<string, mixed> $values complete key => value map */
    private function __construct(private readonly array $values)
    {
    }

    /** @param array<string, mixed> $overrides the season's stored lineup_rules map */
    public static function fromArray(array $overrides): self
    {
        $values = array_replace(
            LineupRuleRegistry::defaults(),
            array_intersect_key($overrides, LineupRuleRegistry::definitions())
        );

        return new self($values);
    }

    public static function defaults(): self
    {
        return new self(LineupRuleRegistry::defaults());
    }

    /** @return list<string> position keys in lineup order */
    public function positions(): array
    {
        return LineupRuleRegistry::positions();
    }

    public function min(string $pos): int
    {
        return $this->bound($pos, 'min');
    }

    public function max(string $pos): int
    {
        return $this->bound($pos, 'max');
    }

    /** @return list<string> positions sharing the combined flex total */
    public function flexGroup(): array
    {
        $group = $this->values['flex_group'] ?? [];

        return is_array($group)
            ? array_values(array_filter($group, fn ($pos) => is_string($pos) && LineupRuleRegistry::isPosition($pos)))
            : [];
    }

    /** Combined activations across the flex positions; null = no combined limit. */
    public function flexTotal(): ?int
    {
        $total = $this->values['flex_total'] ?? null;

        return is_numeric($total) ? (int) $total : null;
    }

    /**
     * Every way the given lineup breaks the rules, as human-readable
     * sentences in position order (the legacy processActivations.php
     * message set and ordering). Empty means the lineup is legal.
     *
     * @param array<string, int> $countsByPos position => number activated
     * @return list<string>
     */
    public function validate(array $countsByPos): array
    {
        $errors = [];
        $flexGroup = $this->flexGroup();
        $lastFlex = $flexGroup === [] ? null : $flexGroup[array_key_last($flexGroup)];

        foreach ($this->positions() as $pos) {
            $count = (int) ($countsByPos[$pos] ?? 0);
            $min = $this->min($pos);
            $max = $this->max($pos);

            if ($min === $max) {
                if ($count !== $min) {
                    $errors[] = sprintf('You must activate exactly %s', $this->quantity($min, $pos));
                }
            } elseif ($count < $min) {
                $errors[] = sprintf('You must activate at least %s', $this->quantity($min, $pos));
            } elseif ($count > $max) {
                $errors[] = sprintf('You can activate at most %s', $this->quantity($max, $pos));
            }

            if ($pos === $lastFlex) {
                $flexError = $this->flexError($countsByPos);
                if ($flexError !== null) {
                    $errors[] = $flexError;
                }
            }
        }

        return $errors;
    }

    /**
     * The limits the browser needs for its live counters, as a plain
     * map safe to json_encode into a data attribute.
     *
     * @return array{positions: array<string, array{min: int, max: int}>, flexGroup: list<string>, flexTotal: ?int}
     */
    public function toJson(): array
    {
        $positions = [];
        foreach ($this->positions() as $pos) {
            $positions[$pos] = ['min' => $this->min($pos), 'max' => $this->max($pos)];
        }

        return [
            'positions' => $positions,
            'flexGroup' => $this->flexGroup(),
            'flexTotal' => $this->flexTotal(),
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * "You must activate 1 RB, 2 WR, 1 TE and 1 flex" — built from the
     * flex positions' minimums plus whatever the combined total leaves
     * over, so a season that changes the limits changes the message.
     */
    private function flexError(array $countsByPos): ?string
    {
        $flexTotal = $this->flexTotal();
        $group = $this->flexGroup();
        if ($flexTotal === null || $group === []) {
            return null;
        }

        $actual = 0;
        foreach ($group as $pos) {
            $actual += (int) ($countsByPos[$pos] ?? 0);
        }
        if ($actual === $flexTotal) {
            return null;
        }

        $parts = [];
        $required = 0;
        foreach ($group as $pos) {
            $parts[] = $this->min($pos) . ' ' . $pos;
            $required += $this->min($pos);
        }
        $spare = $flexTotal - $required;

        $requirement = implode(', ', $parts);
        if ($spare > 0) {
            $requirement .= sprintf(' and %d flex', $spare);
        }

        return 'You must activate ' . $requirement;
    }

    /** "1 HC", "2 DLs" — legacy pluralizes the position code above one. */
    private function quantity(int $count, string $pos): string
    {
        return $count . ' ' . $pos . ($count === 1 ? '' : 's');
    }

    private function bound(string $pos, string $which): int
    {
        $value = $this->values[$pos] ?? null;
        if (is_array($value) && isset($value[$which]) && is_numeric($value[$which])) {
            return max(0, (int) $value[$which]);
        }

        $default = LineupRuleRegistry::definitions()[$pos]['default'] ?? null;

        return is_array($default) ? (int) ($default[$which] ?? 0) : 0;
    }
}
