<?php

namespace App\Tests\Enum;

use App\Enum\IssueStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IssueStatusTest extends TestCase
{
    /** @return array<string, array{?string, IssueStatus}> */
    public static function legacyResults(): array
    {
        return [
            'PASS'            => ['PASS', IssueStatus::Passed],
            'Passed'          => ['Passed', IssueStatus::Passed],
            'REJECT'          => ['REJECT', IssueStatus::Rejected],
            'REJECTED'        => ['REJECTED', IssueStatus::Rejected],
            'Rejected'        => ['Rejected', IssueStatus::Rejected],
            'FAIL'            => ['FAIL', IssueStatus::Rejected],
            'WITHDRAWN'       => ['WITHDRAWN', IssueStatus::Withdrawn],
            'null'            => [null, IssueStatus::Open],
            'empty'           => ['', IssueStatus::Open],
            'typo (Joel)'     => ['Joel', IssueStatus::Open],
            'whitespace pass' => ['  pass  ', IssueStatus::Passed],
        ];
    }

    #[DataProvider('legacyResults')]
    public function testFromLegacyResult(?string $result, IssueStatus $expected): void
    {
        $this->assertSame($expected, IssueStatus::fromLegacyResult($result));
    }

    public function testEnumValuesMatchDbEnum(): void
    {
        $this->assertSame(
            ['Open', 'Passed', 'Rejected', 'Withdrawn'],
            array_map(static fn (IssueStatus $s) => $s->value, IssueStatus::cases())
        );
    }
}
