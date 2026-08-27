<?php

namespace App\Service;

use App\Repository\ScheduleRepository;

/**
 * Builds the week-by-week schedule view model for a season.
 * Ports the grouping/rendering logic from football/history/common/schedule.php.
 */
class ScheduleService
{
    public function __construct(
        private ScheduleRepository $repository,
        private SeasonWeekService $seasonWeek
    ) {
    }

    /**
     * Current season if `schedule` has rows for it yet, otherwise the
     * previous season (a season's rows aren't populated until matchups
     * are set, so early in the year the new season has nothing).
     */
    public function resolveDefaultSeason(): int
    {
        $currentSeason = $this->seasonWeek->getCurrentSeason();

        return $this->repository->hasRows($currentSeason) ? $currentSeason : $currentSeason - 1;
    }

    /**
     * @return array{weeks: array} week/label groups, each with a heading,
     *   an optional date line, an optional bye-week line, and its games
     */
    public function getSchedule(int $season): array
    {
        $rows = $this->repository->getSeasonSchedule($season);
        $byeList = $this->buildByeList($this->repository->getByeWeeks($season));

        // Past weeks show final scores; the current/future week (or all of
        // it, for an earlier season) shows "vs" with no scores. Matches
        // schedule.php lines 2-6.
        $currentSeason = $this->seasonWeek->getCurrentSeason();
        $thisWeek = $season < $currentSeason ? 17 : $this->seasonWeek->getCurrentWeek();

        $weeks = [];
        $group = null;
        $lastWeek = null;
        $lastLabel = null;

        foreach ($rows as $row) {
            $week = (int) $row['week'];
            $label = (string) ($row['label'] ?? '');

            if ($group === null || $week !== $lastWeek || $label !== $lastLabel) {
                if ($group !== null) {
                    $weeks[] = $group;
                }

                $heading = $label !== '' ? $label : $row['weekname'];
                $group = [
                    'week' => $week,
                    // Anchor off the displayed heading, not the raw
                    // weekname: postseason rounds that share a week
                    // number (e.g. "Playoffs" and "Toilet Bowl" both at
                    // week 15) share a weekname too, which would collide
                    // if the anchor didn't follow the label that tells
                    // them apart.
                    'anchor' => str_replace(' ', '', (string) $heading),
                    'heading' => $heading,
                    'dateLine' => $this->formatDateLine($row['displayDate']),
                    'byes' => $byeList[$week] ?? null,
                    'games' => [],
                ];
                $lastWeek = $week;
                $lastLabel = $label;
            }

            $group['games'][] = $this->buildGame($row, $thisWeek);
        }
        if ($group !== null) {
            $weeks[] = $group;
        }

        return ['weeks' => $weeks];
    }

    /**
     * Groups bye-week rows into a "Team A, Team B" string per week,
     * applying the New York/Los Angeles nickname-suffix rule so the two
     * cities' teams are distinguishable. Ports the bye-list builder from
     * schedule.php lines 31-51.
     */
    private function buildByeList(array $byeRows): array
    {
        $byeList = [];
        foreach ($byeRows as $row) {
            $week = (int) $row['week'];
            $name = $row['name'];
            if ($name === 'New York' || $name === 'Los Angeles') {
                $name .= ' ' . $row['nickname'];
            }

            $byeList[$week] = isset($byeList[$week]) ? $byeList[$week] . ', ' . $name : $name;
        }

        return $byeList;
    }

    /**
     * Zero-date guard: weekmap.displayDate is a DATETIME column, stored
     * as 0000-00-00 00:00:00 (not the bare-date 0000-00-00) for
     * 1992-1999. Render nothing rather than a garbled date in that case.
     */
    private function formatDateLine(?string $displayDate): ?string
    {
        if ($displayDate === null || str_starts_with($displayDate, '0000-00-00')) {
            return null;
        }

        return (new \DateTimeImmutable($displayDate))->format('l, F j');
    }

    /**
     * Determines winner/loser ordering (schedule.php lines 113-123) and
     * whether to reveal scores (lines 129-142).
     */
    private function buildGame(array $row, int $thisWeek): array
    {
        if ($row['scoreb'] > $row['scorea']) {
            $winName = $row['teamb_name'];
            $winId = $row['teamb_id'];
            $winScore = $row['scoreb'];
            $loseName = $row['teama_name'];
            $loseId = $row['teama_id'];
            $loseScore = $row['scorea'];
        } else {
            $winName = $row['teama_name'];
            $winId = $row['teama_id'];
            $winScore = $row['scorea'];
            $loseName = $row['teamb_name'];
            $loseId = $row['teamb_id'];
            $loseScore = $row['scoreb'];
        }

        return [
            'winName' => $winName,
            'winId' => $winId,
            'winScore' => $winScore,
            'loseName' => $loseName,
            'loseId' => $loseId,
            'loseScore' => $loseScore,
            'showScores' => (int) $row['week'] < $thisWeek,
        ];
    }
}
