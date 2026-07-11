<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

/**
 * Weekly score grids, ported from football/stats/weekbyweek.php: one
 * row per player with a column per completed week plus a season total,
 * scoped either to a team's current roster or to a position.
 */
class WeekByWeekService
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /** @return array{titles: array, rows: array} */
    public function getTeamGrid(int $season, int $teamId): array
    {
        return $this->buildGrid($this->connection->fetchAllAssociative(
            $this->baseQuery('r.teamid = :scope'),
            ['season' => $season, 'scope' => $teamId]
        ), sortByTotal: false);
    }

    /** @return array{titles: array, rows: array} */
    public function getPositionGrid(int $season, string $pos): array
    {
        return $this->buildGrid($this->connection->fetchAllAssociative(
            $this->baseQuery('p.pos = :scope and p.active=1'),
            ['season' => $season, 'scope' => $pos]
        ), sortByTotal: true);
    }

    /** The season's team names for the selector (legacy utils/teamList.php) */
    public function getTeamList(int $season): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT name, teamid, abbrev FROM teamnames WHERE season = :season ORDER BY name ASC',
            ['season' => $season]
        );
    }

    private function baseQuery(string $where): string
    {
        return "select p.playerid, p.firstname, p.lastname, p.pos, ps.season, ps.week, ps.pts, t.abbrev, p.team as nfl
                from newplayers p
                left join roster r on p.playerid=r.playerid and r.dateoff is null
                left join teamnames t on r.teamid=t.teamid and t.season = :season
                left join playerscores ps on p.playerid=ps.playerid and ps.season = :season
                where $where
                order by p.pos, p.lastname, p.firstname, ps.week";
    }

    /**
     * Pivot player-week rows into display rows
     * [name, pos, nfl, team, week1..weekN, total]. Position grids drop
     * scoreless players and sort by total.
     */
    private function buildGrid(array $scoreRows, bool $sortByTotal): array
    {
        $players = [];
        $maxWeek = 0;
        $lastWeek = 0;

        foreach ($scoreRows as $row) {
            $id = $row['playerid'];
            if (!isset($players[$id])) {
                $players[$id] = [
                    'name' => $row['firstname'] . ' ' . $row['lastname'],
                    'pos' => $row['pos'],
                    'nfl' => $row['nfl'],
                    'team' => $row['abbrev'],
                    'total' => 0,
                    'weeks' => [],
                ];
                $lastWeek = 0;
            }

            $week = (int) ($row['week'] ?? 1) ?: 1;
            for ($i = $lastWeek + 1; $i < $week; $i++) {
                $players[$id]['weeks'][$i] = null;
            }
            $players[$id]['weeks'][$week] = $row['pts'];
            $players[$id]['total'] += $row['pts'];
            $lastWeek = $week;
            $maxWeek = max($maxWeek, $week);
        }

        if ($sortByTotal) {
            $players = array_filter($players, function (array $player) {
                if ($player['total'] != 0) {
                    return true;
                }
                foreach ($player['weeks'] as $pts) {
                    if ($pts !== null) {
                        return true;
                    }
                }
                return false;
            });
            usort($players, fn (array $a, array $b) => [$b['total'], $a['name']] <=> [$a['total'], $b['name']]);
        }

        $rows = [];
        foreach ($players as $player) {
            $row = [$player['name'], $player['pos'], $player['nfl'], $player['team']];
            for ($week = 1; $week <= $maxWeek; $week++) {
                $row[] = $player['weeks'][$week] ?? null;
            }
            $row[] = $player['total'];
            $rows[] = $row;
        }

        return [
            'titles' => array_merge(['Name', 'Pos', 'NFL', 'Team'], $maxWeek > 0 ? range(1, $maxWeek) : [], ['Tot']),
            'rows' => $rows,
        ];
    }
}
