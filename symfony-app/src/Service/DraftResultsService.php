<?php

namespace App\Service;

use App\Repository\DraftPickRepository;

/**
 * Builds the draft-board view model for a season, and enforces the
 * future-season cut-off (Phase 16b decision 6). Ports the three queries
 * from football/history/common/draftresults.php.
 */
class DraftResultsService
{
    public function __construct(
        private DraftPickRepository $repository,
        private SeasonWeekService $seasonWeek
    ) {
    }

    /**
     * The current season if it has any drafted picks, otherwise the most
     * recent season that does (never a future season — reachableSeasons()
     * already excludes those).
     */
    public function resolveDefaultYear(): int
    {
        $currentSeason = $this->seasonWeek->getCurrentSeason();
        $withSelections = array_filter(
            $this->repository->getSeasonsWithSelections(),
            fn (int $season) => $season <= $currentSeason
        );

        if (in_array($currentSeason, $withSelections, true)) {
            return $currentSeason;
        }

        return $withSelections === [] ? $currentSeason : max($withSelections);
    }

    /**
     * Whether a year is reachable: it has draftpicks rows and is no later
     * than the current season. A future season and a year that never
     * existed both come back false, so both 404 identically.
     */
    public function isReachable(int $year): bool
    {
        if ($year > $this->seasonWeek->getCurrentSeason()) {
            return false;
        }

        return in_array($year, $this->repository->getSeasonsWithPicks(), true);
    }

    /**
     * The full view model for the board: rows ready for display, the
     * filter dropdown options, the applied filters (echoed back into the
     * form), the as-of date, and prev/next year links.
     *
     * @param array<string, mixed> $filters raw filter input (round/pick/team/pos/nfl)
     */
    public function getBoard(int $year, array $filters): array
    {
        $normalized = $this->normalizeFilters($filters);
        $asOf = $this->repository->getAsOfDate($year);

        $rows = array_map(
            fn (array $row) => $this->buildRow($row),
            $this->repository->getBoard($year, $normalized, $asOf)
        );

        $seasons = $this->reachableSeasons();
        $index = array_search($year, $seasons, true);
        $prevYear = ($index !== false && $index > 0) ? $seasons[$index - 1] : null;
        $nextYear = ($index !== false && $index < count($seasons) - 1) ? $seasons[$index + 1] : null;

        return [
            'rows' => $rows,
            'options' => $this->repository->getFilterOptions($year, $asOf),
            'filters' => $normalized,
            'asOf' => $asOf,
            'prevYear' => $prevYear,
            'nextYear' => $nextYear,
        ];
    }

    /**
     * draftpicks seasons no later than the current season, ascending.
     * Drives both the 404 check and prev/next navigation, so no path can
     * hand back a future year.
     *
     * @return int[]
     */
    private function reachableSeasons(): array
    {
        $currentSeason = $this->seasonWeek->getCurrentSeason();

        return array_values(array_filter(
            $this->repository->getSeasonsWithPicks(),
            fn (int $season) => $season <= $currentSeason
        ));
    }

    /**
     * Collapses `ALL`, empty string and absent to null; casts round/pick to
     * int. team/pos/nfl stay as strings (nfl and pos are non-numeric, team
     * is bound as a plain parameter so a numeric string works the same as
     * an int).
     *
     * @return array{round: ?int, pick: ?int, team: ?string, pos: ?string, nfl: ?string}
     */
    private function normalizeFilters(array $filters): array
    {
        $clean = function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }
            $value = trim((string) $value);

            return ($value === '' || $value === 'ALL') ? null : $value;
        };

        $round = $clean($filters['round'] ?? null);
        $pick = $clean($filters['pick'] ?? null);

        return [
            'round' => $round !== null ? (int) $round : null,
            'pick' => $pick !== null ? (int) $pick : null,
            'team' => $clean($filters['team'] ?? null),
            'pos' => $clean($filters['pos'] ?? null),
            'nfl' => $clean($filters['nfl'] ?? null),
        ];
    }

    /**
     * Shapes one repository row for the template: a display-ready
     * selection/pos/nfl (null when undrafted, so the template just checks
     * for null rather than comparing empty strings), and fromFranchise set
     * only when the pick was traded (orgTeam !== teamid).
     */
    private function buildRow(array $row): array
    {
        $teamId = (int) $row['teamid'];
        $orgTeam = $row['orgteam'] !== null ? (int) $row['orgteam'] : null;

        $selection = ($row['firstname'] !== null || $row['lastname'] !== null)
            ? trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''))
            : null;

        return [
            'id' => (int) $row['id'],
            'round' => (int) $row['round'],
            'pick' => $row['pick'] !== null ? (int) $row['pick'] : null,
            'teamid' => $teamId,
            'team' => $row['team'],
            'selection' => $selection,
            'pos' => $row['pos'] !== null && $row['pos'] !== '' ? $row['pos'] : null,
            'nfl' => $row['nflteamid'],
            'fromFranchise' => ($orgTeam !== null && $orgTeam !== $teamId) ? $row['orgteamname'] : null,
        ];
    }
}
