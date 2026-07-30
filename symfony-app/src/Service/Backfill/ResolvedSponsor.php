<?php

namespace App\Service\Backfill;

/**
 * The outcome of resolving one page sponsor string to a user, per the
 * backfill's decision-2 rules.
 */
class ResolvedSponsor
{
    private function __construct(
        public readonly string $rawName,
        public readonly ?int $userId,
        public readonly string $method,
        public readonly ?string $flagReason,
    ) {
    }

    public static function resolved(string $rawName, int $userId, string $method): self
    {
        return new self($rawName, $userId, $method, null);
    }

    public static function flagged(string $rawName, string $reason): self
    {
        return new self($rawName, null, 'flagged', $reason);
    }

    public function isResolved(): bool
    {
        return $this->userId !== null;
    }
}
