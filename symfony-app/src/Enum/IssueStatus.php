<?php

namespace App\Enum;

/**
 * The voting lifecycle of a rule proposal, orthogonal to Issue::Published
 * (which is the admin "visible to members" gate). Backfilled from the
 * legacy free-text `Result` column; `Withdrawn` has no legacy source and
 * is used going forward.
 */
enum IssueStatus: string
{
    case Open = 'Open';
    case Passed = 'Passed';
    case Rejected = 'Rejected';
    case Withdrawn = 'Withdrawn';

    /**
     * Map a legacy `issues.Result` string to a Status. Covers the values
     * actually present (PASS/Passed/REJECT/REJECTED/Rejected/FAIL/
     * WITHDRAWN) plus the spec's "anything else / null -> Open" default;
     * shared by the migration's intent and the backfill parser.
     */
    public static function fromLegacyResult(?string $result): self
    {
        return match (strtoupper(trim((string) $result))) {
            'PASS', 'PASSED' => self::Passed,
            'REJECT', 'REJECTED', 'FAIL' => self::Rejected,
            'WITHDRAWN' => self::Withdrawn,
            default => self::Open,
        };
    }

    public function label(): string
    {
        return $this->value;
    }
}
