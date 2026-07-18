<?php

namespace App\Tests\Service;

use App\Service\DraftScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class DraftScheduleServiceTest extends TestCase
{
    private DraftScheduleService $service;

    protected function setUp(): void
    {
        $this->service = new DraftScheduleService($this->createStub(EntityManagerInterface::class));
    }

    // ---- candidateDates ----

    public function testCandidateDatesChecksSaturdaysAndSundaysByDefault(): void
    {
        // 2026-08-01 is a Saturday
        $dates = $this->service->candidateDates(
            new \DateTimeImmutable('2026-08-01'),
            new \DateTimeImmutable('2026-08-09')
        );

        $this->assertCount(9, $dates);
        $byDay = [];
        foreach ($dates as $d) {
            $byDay[$d['date']->format('Y-m-d')] = $d['checked'];
        }
        $this->assertTrue($byDay['2026-08-01']);   // Sat
        $this->assertTrue($byDay['2026-08-02']);   // Sun
        $this->assertFalse($byDay['2026-08-03']);  // Mon
        $this->assertFalse($byDay['2026-08-07']);  // Fri
        $this->assertTrue($byDay['2026-08-08']);   // Sat
        $this->assertTrue($byDay['2026-08-09']);   // Sun
    }

    public function testCandidateDatesSpansMonthBoundary(): void
    {
        $dates = $this->service->candidateDates(
            new \DateTimeImmutable('2026-07-30'),
            new \DateTimeImmutable('2026-08-02')
        );

        $this->assertSame(
            ['2026-07-30', '2026-07-31', '2026-08-01', '2026-08-02'],
            array_map(fn($d) => $d['date']->format('Y-m-d'), $dates)
        );
    }

    public function testCandidateDatesSingleDayRange(): void
    {
        $dates = $this->service->candidateDates(
            new \DateTimeImmutable('2026-08-01'),
            new \DateTimeImmutable('2026-08-01')
        );

        $this->assertCount(1, $dates);
        $this->assertTrue($dates[0]['checked']);
    }

    // ---- planMerge ----

    public function testPlanMergeOnEmptySeasonCreatesEverything(): void
    {
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01', '2026-08-02'],
            ownerUserIds: [10, 20],
            existingVoteUserIds: [],
            existingDatesByUser: []
        );

        $this->assertSame([10, 20], $plan['createVotes']);
        $this->assertSame([
            [10, '2026-08-01'],
            [10, '2026-08-02'],
            [20, '2026-08-01'],
            [20, '2026-08-02'],
        ], $plan['createDates']);
        $this->assertSame([], $plan['deleteDates']);
    }

    public function testPlanMergeAddedDateOnlyCreatesNewRows(): void
    {
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01', '2026-08-08'],
            ownerUserIds: [10, 20],
            existingVoteUserIds: [10, 20],
            existingDatesByUser: [10 => ['2026-08-01'], 20 => ['2026-08-01']]
        );

        $this->assertSame([], $plan['createVotes']);
        $this->assertSame([
            [10, '2026-08-08'],
            [20, '2026-08-08'],
        ], $plan['createDates']);
        $this->assertSame([], $plan['deleteDates']);
    }

    public function testPlanMergeRemovedDateIsDeletedForAllUsers(): void
    {
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01'],
            ownerUserIds: [10, 20],
            existingVoteUserIds: [10, 20],
            existingDatesByUser: [
                10 => ['2026-08-01', '2026-08-08'],
                20 => ['2026-08-01', '2026-08-08'],
            ]
        );

        $this->assertSame([], $plan['createVotes']);
        $this->assertSame([], $plan['createDates']);
        $this->assertSame(['2026-08-08'], $plan['deleteDates']);
    }

    public function testPlanMergeIsIdempotent(): void
    {
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01'],
            ownerUserIds: [10],
            existingVoteUserIds: [10],
            existingDatesByUser: [10 => ['2026-08-01']]
        );

        $this->assertSame([], $plan['createVotes']);
        $this->assertSame([], $plan['createDates']);
        $this->assertSame([], $plan['deleteDates']);
    }

    public function testPlanMergePicksUpNewOwnerOnRerun(): void
    {
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01'],
            ownerUserIds: [10, 30],
            existingVoteUserIds: [10],
            existingDatesByUser: [10 => ['2026-08-01']]
        );

        $this->assertSame([30], $plan['createVotes']);
        $this->assertSame([[30, '2026-08-01']], $plan['createDates']);
        $this->assertSame([], $plan['deleteDates']);
    }

    public function testPlanMergeNeverTouchesExistingSelectedRows(): void
    {
        // Rows for selected dates never appear in the plan at all, so a
        // stored attend = 'N' or a lastUpdate stamp can't be overwritten
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01', '2026-08-02'],
            ownerUserIds: [10],
            existingVoteUserIds: [10],
            existingDatesByUser: [10 => ['2026-08-01', '2026-08-02']]
        );

        $this->assertSame([], $plan['createVotes']);
        $this->assertSame([], $plan['createDates']);
        $this->assertSame([], $plan['deleteDates']);
    }

    public function testPlanMergeDeletesRowsOfFormerOwnersDates(): void
    {
        // User 99 is no longer an owner; a date they alone still have a row
        // for is still cleaned up when deselected
        $plan = $this->service->planMerge(
            selectedDates: ['2026-08-01'],
            ownerUserIds: [10],
            existingVoteUserIds: [10],
            existingDatesByUser: [
                10 => ['2026-08-01'],
                99 => ['2026-08-08'],
            ]
        );

        $this->assertSame(['2026-08-08'], $plan['deleteDates']);
    }

    // ---- vote submission ----

    public function testExactlyFourNoVotesIsAllowed(): void
    {
        $this->assertFalse($this->service->exceedsNoVoteLimit([
            '2026-08-01' => 'N',
            '2026-08-02' => 'N',
            '2026-08-08' => 'N',
            '2026-08-09' => 'N',
            '2026-08-15' => 'Y',
        ]));
    }

    public function testFiveNoVotesExceedsTheLimit(): void
    {
        $this->assertTrue($this->service->exceedsNoVoteLimit([
            '2026-08-01' => 'N',
            '2026-08-02' => 'N',
            '2026-08-08' => 'N',
            '2026-08-09' => 'N',
            '2026-08-15' => 'N',
        ]));
    }

    public function testAllYesVotesIsAllowed(): void
    {
        $this->assertFalse($this->service->exceedsNoVoteLimit([
            '2026-08-01' => 'Y',
            '2026-08-02' => 'Y',
        ]));
    }

    public function testSubmitVotesRejectsOverLimitWithoutTouchingTheDatabase(): void
    {
        // The stubbed EntityManager has no working connection — reaching
        // for it would blow up, so a false return proves nothing ran
        $result = $this->service->submitVotes(6, 2026, [
            '2026-08-01' => 'N',
            '2026-08-02' => 'N',
            '2026-08-08' => 'N',
            '2026-08-09' => 'N',
            '2026-08-12' => 'N',
        ]);

        $this->assertFalse($result);
    }

    public function testSubmitVotesRejectsMalformedValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->submitVotes(6, 2026, ['2026-08-01' => 'MAYBE']);
    }

    public function testSubmitVotesRejectsMalformedDates(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->submitVotes(6, 2026, ['0801; DROP TABLE draftdate' => 'Y']);
    }

    // ---- applySchedule window guard ----

    public function testApplyScheduleRejectsDateOutsideSeasonWindow(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->applySchedule(2026, ['2026-06-30']);
    }

    public function testWindowBounds(): void
    {
        $this->assertSame('2026-07-01', DraftScheduleService::windowStart(2026));
        $this->assertSame('2026-10-01', DraftScheduleService::windowEnd(2026));
    }
}
