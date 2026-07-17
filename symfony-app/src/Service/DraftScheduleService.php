<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Draft-date scheduling: builds each season's candidate-date rows
 * (draftdate/draftvote) that the member vote page and the admin tally
 * read. Replaces the hand-inserted rows the per-season legacy pages
 * depended on.
 *
 * A season's schedule is the draftdate rows whose date falls in that
 * season's July 1 – Oct 1 window (the table has no season column; the
 * legacy pages used the same window).
 */
class DraftScheduleService
{
    /**
     * Ported from history/common/processdraftdate.php: a member may mark
     * at most this many dates "No".
     */
    public const MAX_NO_VOTES = 4;

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public static function windowStart(int $season): string
    {
        return $season . '-07-01';
    }

    public static function windowEnd(int $season): string
    {
        return $season . '-10-01';
    }

    /**
     * Every date from $first to $last inclusive, flagged default-checked
     * when it falls on a Saturday or Sunday.
     *
     * @return array<array{date: \DateTimeImmutable, checked: bool}>
     */
    public function candidateDates(\DateTimeInterface $first, \DateTimeInterface $last): array
    {
        $dates = [];
        $day = \DateTimeImmutable::createFromInterface($first);
        $end = \DateTimeImmutable::createFromInterface($last);
        for (; $day <= $end; $day = $day->modify('+1 day')) {
            $dates[] = [
                'date' => $day,
                'checked' => in_array($day->format('N'), ['6', '7'], true),
            ];
        }

        return $dates;
    }

    /**
     * The distinct dates already scheduled for the season (any user's
     * draftdate rows in the window), 'Y-m-d' ascending.
     *
     * @return string[]
     */
    public function existingDates(int $season): array
    {
        return $this->em->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT Date FROM draftdate WHERE Date BETWEEN :start AND :end ORDER BY Date',
            ['start' => self::windowStart($season), 'end' => self::windowEnd($season)]
        );
    }

    /**
     * The merge plan for applying a selected-date set to the existing
     * rows, keeping every vote already cast:
     *  - createVotes: owner user ids with no draftvote row yet
     *  - createDates: [userId, 'Y-m-d'] pairs missing from draftdate
     *  - deleteDates: dates present in draftdate (for any user) but no
     *    longer selected — deleted for all users
     * Existing rows for selected dates are never touched, so attend
     * values and lastUpdate stamps survive a re-run, and owners added
     * since the last run are filled in.
     *
     * @param string[] $selectedDates       'Y-m-d' strings
     * @param int[]    $ownerUserIds        active owners for the season
     * @param int[]    $existingVoteUserIds users with a draftvote row
     * @param array<int, string[]> $existingDatesByUser userId => 'Y-m-d' rows in the window
     * @return array{createVotes: int[], createDates: array<array{int, string}>, deleteDates: string[]}
     */
    public function planMerge(
        array $selectedDates,
        array $ownerUserIds,
        array $existingVoteUserIds,
        array $existingDatesByUser
    ): array {
        $selected = array_values(array_unique($selectedDates));
        sort($selected);

        $createVotes = array_values(array_diff(array_unique($ownerUserIds), $existingVoteUserIds));

        $createDates = [];
        foreach (array_unique($ownerUserIds) as $userId) {
            foreach ($selected as $date) {
                if (!in_array($date, $existingDatesByUser[$userId] ?? [], true)) {
                    $createDates[] = [$userId, $date];
                }
            }
        }

        $allExisting = [];
        foreach ($existingDatesByUser as $dates) {
            foreach ($dates as $date) {
                $allExisting[$date] = true;
            }
        }
        $deleteDates = array_values(array_diff(array_keys($allExisting), $selected));
        sort($deleteDates);

        return [
            'createVotes' => $createVotes,
            'createDates' => $createDates,
            'deleteDates' => $deleteDates,
        ];
    }

    /**
     * Apply a selected-date set for the season in one transaction.
     * Every selected date must fall inside the season's window.
     *
     * @param string[] $selectedDates 'Y-m-d' strings
     * @return array{createVotes: int, createDates: int, deleteDates: int} row counts
     */
    public function applySchedule(int $season, array $selectedDates): array
    {
        foreach ($selectedDates as $date) {
            if ($date < self::windowStart($season) || $date > self::windowEnd($season)) {
                throw new \InvalidArgumentException(
                    "Date $date is outside the $season draft window ("
                    . self::windowStart($season) . ' to ' . self::windowEnd($season) . ')'
                );
            }
        }

        $conn = $this->em->getConnection();

        $ownerUserIds = array_map('intval', $conn->fetchFirstColumn(
            'SELECT DISTINCT userid FROM owners WHERE season = :season',
            ['season' => $season]
        ));
        $existingVoteUserIds = array_map('intval', $conn->fetchFirstColumn(
            'SELECT userid FROM draftvote WHERE season = :season',
            ['season' => $season]
        ));
        $existingDatesByUser = [];
        foreach ($conn->fetchAllAssociative(
            'SELECT UserID, Date FROM draftdate WHERE Date BETWEEN :start AND :end',
            ['start' => self::windowStart($season), 'end' => self::windowEnd($season)]
        ) as $row) {
            $existingDatesByUser[(int) $row['UserID']][] = $row['Date'];
        }

        $plan = $this->planMerge($selectedDates, $ownerUserIds, $existingVoteUserIds, $existingDatesByUser);

        $conn->beginTransaction();
        try {
            foreach ($plan['createVotes'] as $userId) {
                $conn->executeStatement(
                    'INSERT INTO draftvote (userid, season, lastUpdate) VALUES (:userId, :season, NULL)',
                    ['userId' => $userId, 'season' => $season]
                );
            }
            foreach ($plan['createDates'] as [$userId, $date]) {
                $conn->executeStatement(
                    "INSERT INTO draftdate (UserID, Date, Attend) VALUES (:userId, :date, 'Y')",
                    ['userId' => $userId, 'date' => $date]
                );
            }
            if ($plan['deleteDates'] !== []) {
                $conn->executeStatement(
                    'DELETE FROM draftdate WHERE Date IN (:dates)',
                    ['dates' => $plan['deleteDates']],
                    ['dates' => \Doctrine\DBAL\ArrayParameterType::STRING]
                );
            }
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }

        return [
            'createVotes' => count($plan['createVotes']),
            'createDates' => count($plan['createDates']),
            'deleteDates' => count($plan['deleteDates']),
        ];
    }
}
