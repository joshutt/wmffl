<?php

namespace App\Service\Backfill;

use App\Enum\IssueStatus;

/**
 * One rule proposal extracted from a legacy proposals{year}.php page,
 * before reconciliation against the issues table.
 */
class ParsedProposal
{
    /**
     * @param string[] $sponsorNames raw sponsor strings as written on the page
     */
    public function __construct(
        public readonly int $season,
        public readonly string $issueNum,
        public readonly string $issueName,
        public readonly array $sponsorNames,
        public readonly ?string $statusBlurb,
        public readonly IssueStatus $status,
        public readonly ?string $rationaleMarkdown,
        public readonly ?string $ruleChangeMarkdown,
    ) {
    }
}
