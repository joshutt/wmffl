<?php

namespace App\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Roster add/drop rules, ported from football/transactions/
 * {list,confirm}.php.
 *
 * Differences from legacy (documented in the phase requirements):
 *  - roster-limit counts come from the database instead of the posted
 *    keep/drop form fields, so a forged POST cannot exceed the limits;
 *  - drops are scoped to the member's own roster (legacy's drop UPDATE
 *    had no teamid filter);
 *  - everything runs with bound parameters.
 */
class RosterMoveService
{
    public const MAX_ACTIVE_PLAYERS = 25;
    public const TOTAL_ROSTER = 26;

    public function __construct(
        private Connection $connection
    ) {
    }

    // ---- player search (list.php) ----

    /**
     * @param array{last: string, first: string, position: string, team: string, available: string, order: string} $criteria
     */
    public function searchPlayers(array $criteria): array
    {
        $select = "s.name as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid";
        $onTeam = "SELECT $select FROM players p, roster r, team s
                   WHERE p.playerid=r.playerid AND r.teamid=s.teamid AND r.dateoff IS NULL ";
        $neverOnTeam = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid
                        FROM players p LEFT JOIN roster r ON p.playerid = r.playerid WHERE r.dateon IS NULL ";
        $noLongerOnTeam = "SELECT 'Available' as teamname, p.lastname, p.firstname, p.pos, p.team, p.playerid
                           FROM players p, roster r WHERE p.playerid = r.playerid ";
        $noLongerGroup = 'GROUP BY p.playerid HAVING COUNT(r.dateon) = COUNT(r.dateoff) ';

        $where = 'AND p.active=1 AND p.usePos=1 ';
        $params = [];

        // Team filter first: 'RET' replaces the active-player clause so
        // retired players remain findable (legacy list.php:42-54)
        switch ($criteria['team']) {
            case '':
                break;
            case 'NONE':
                $where .= "AND (p.team='' OR p.team is null) ";
                break;
            case 'ANY':
                $where .= "AND p.team<>'' AND p.team is not null ";
                break;
            case 'RET':
                $where = 'AND p.retired is not null ';
                break;
            default:
                $where .= 'AND p.team = :team ';
                $params['team'] = $criteria['team'];
        }
        if ($criteria['position'] !== '') {
            $where .= 'AND p.pos = :pos ';
            $params['pos'] = $criteria['position'];
        }
        if ($criteria['last'] !== '') {
            $where .= 'AND p.lastname like :last ';
            $params['last'] = '%' . $criteria['last'] . '%';
        }
        if ($criteria['first'] !== '') {
            $where .= 'AND p.firstname like :first ';
            $params['first'] = '%' . $criteria['first'] . '%';
        }

        $sql = match ($criteria['available']) {
            'available' => "( $neverOnTeam $where ) UNION ( $noLongerOnTeam $where $noLongerGroup ) ",
            'taken' => $onTeam . $where,
            default => "( $neverOnTeam $where ) UNION ( $noLongerOnTeam $where $noLongerGroup ) UNION ( $onTeam $where ) ",
        };

        // Whitelisted: an ORDER BY can't take a bound parameter. Legacy's
        // position/nflteam sort links referenced source column names that
        // don't exist in the union output and crashed the page.
        $sql .= 'ORDER BY ' . match ($criteria['order']) {
            'teamname' => 'teamname',
            'firstname' => 'firstname',
            'position' => 'pos',
            'nflteam' => 'team',
            default => 'lastname',
        };

        return $this->connection->fetchAllAssociative($sql, $params);
    }

    // ---- confirm-page context (confirm.php) ----

    /**
     * Whether the current week is in its waiver period, plus the
     * season/week the moves will be recorded against. Week 0 (offseason)
     * is always a waiver period.
     *
     * @return array{isWaiver: bool, season: int, week: int}
     */
    public function getWaiverContext(): array
    {
        $row = $this->connection->fetchAssociative(
            "SELECT IF(now()>ActivationDue,1,0) AS waiverperiod, season, week
             FROM weekmap WHERE now() BETWEEN startdate AND enddate"
        );

        $week = (int) ($row['week'] ?? 0);

        return [
            'isWaiver' => $week === 0 || (bool) ($row['waiverperiod'] ?? false),
            'season' => (int) ($row['season'] ?? date('Y')),
            'week' => $week,
        ];
    }

    /** The team's pending waiver picks with player info, by priority */
    public function getExistingWaiverPicks(int $teamId, int $season, int $week): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT w.playerid, p.lastname, p.firstname, p.team, p.pos, w.priority
             FROM waiverpicks w, players p
             WHERE w.playerid=p.playerid AND teamid = :teamId
             AND season = :season AND week = :week
             ORDER BY w.priority',
            ['teamId' => $teamId, 'season' => $season, 'week' => $week]
        );
    }

    /**
     * Players who must clear waivers this week: dropped within the last
     * seven days, or whose NFL game has already kicked off.
     *
     * @return int[]
     */
    public function getWaiverEligiblePlayerIds(int $season, int $week): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT DISTINCT playerid FROM roster r, weekmap w WHERE
             ((r.dateoff between w.startdate and now() and now() < DATE_ADD(w.startdate, INTERVAL 7 DAY))
              OR (w.week=1 AND r.dateoff between DATE_SUB(w.enddate, INTERVAL 7 DAY) AND now()))
             AND w.season = :season AND w.week = :week
             UNION
             select DISTINCT r.playerid from nflrosters r
             JOIN nflgames g on r.nflteamid in (g.homeTeam, g.roadTeam)
             where r.dateoff is null and g.season = :season and g.week = :week and now() >= g.kickoff',
            ['season' => $season, 'week' => $week]
        ));
    }

    /** @param int[] $playerIds */
    public function getPlayersInfo(array $playerIds): array
    {
        if ($playerIds === []) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            'SELECT playerid, lastname, firstname, team, pos FROM players WHERE playerid in (:ids)',
            ['ids' => array_map('intval', $playerIds)],
            ['ids' => ArrayParameterType::INTEGER]
        );
    }

    /** Current roster rows with an IR marker (confirm.php query 2) */
    public function getCurrentRoster(int $teamId): array
    {
        return $this->connection->fetchAllAssociative(
            "select p.playerid, p.lastname, p.firstname, p.team, p.pos,
                    if(ir.id is null, '', 'IR') as ir
             from players p
             join roster r on p.playerid = r.playerid and r.dateoff is null
             join team t on r.teamid = t.teamid
             left join ir on p.playerid=ir.playerid and ir.dateoff is null
             where t.teamid = :teamId
             order by p.pos, p.lastname",
            ['teamId' => $teamId]
        );
    }

    /**
     * Non-HC roster counts and remaining transaction points
     * (confirm.php query 3).
     *
     * @return array{total: int, ir: int, active: int, ptsLeft: int}
     */
    public function getTeamCounts(int $teamId, int $season): array
    {
        $row = $this->connection->fetchAssociative(
            "select count(*) as total,
                    sum(if(ir.id is null, 0, 1)) as irplayers,
                    sum(if(ir.id is null, 1, 0)) as activeplayers,
                    t.totalpts - t.protectionpts - t.transpts as ptsleft
             from players p
             join roster r on r.PlayerID=p.playerid and r.DateOff is null
             join transpoints t on r.TeamID=t.TeamID
             left join ir on p.playerid = ir.playerid and ir.dateoff is null
             where r.teamid = :teamId and p.pos <> 'HC' and t.season = :season
             group by t.teamid",
            ['teamId' => $teamId, 'season' => $season]
        );

        return [
            'total' => (int) ($row['total'] ?? 0),
            'ir' => (int) ($row['irplayers'] ?? 0),
            'active' => (int) ($row['activeplayers'] ?? 0),
            'ptsLeft' => (int) ($row['ptsleft'] ?? 0),
        ];
    }

    // ---- executing moves ----

    /**
     * Validate and execute a set of roster moves. Returns the list of
     * error messages; an empty list means the moves were written.
     *
     * @param int[] $picks players to add immediately
     * @param int[] $drops players to drop
     * @param array<int, int> $waiverPriorities priority => playerid
     * @param bool $updateWaivers whether the form included waiver fields
     */
    public function executeMoves(
        int $teamId,
        array $picks,
        array $drops,
        array $waiverPriorities,
        bool $updateWaivers
    ): array {
        $context = $this->getWaiverContext();
        $season = $context['season'];
        $week = $context['week'];

        $errors = [];

        if ($picks !== [] && $week === 16 && $context['isWaiver']) {
            $errors[] = 'Pickups are no longer allowed this season';
        }

        // Roster limits from DB state: drops only count when the player
        // is actually on this team's roster (also the write scope below)
        $roster = $this->getCurrentRoster($teamId);
        $onRoster = [];
        foreach ($roster as $player) {
            if ($player['pos'] !== 'HC') {
                $onRoster[(int) $player['playerid']] = $player['ir'] === 'IR';
            }
        }
        $drops = array_values(array_filter(
            array_map('intval', $drops),
            fn (int $id) => array_key_exists($id, $onRoster)
        ));
        $activeDrops = count(array_filter($drops, fn (int $id) => !$onRoster[$id]));

        $counts = $this->getTeamCounts($teamId, $season);
        $resultingActive = $counts['active'] - $activeDrops + count($picks);
        $resultingTotal = $counts['total'] - count($drops) + count($picks);

        if ($resultingActive > self::MAX_ACTIVE_PLAYERS) {
            $errors[] = "That would give you $resultingActive players on your roster!!  You must drop someone!!";
        }
        if ($resultingTotal > self::TOTAL_ROSTER) {
            $errors[] = "That would give you $resultingTotal players, including IR!  You must drop someone!";
        }

        // Entry-fee gate (confirm.php $allowedTran)
        $payment = $this->connection->fetchAssociative(
            "SELECT p.paid, tp.TotalPts - tp.ProtectionPts - tp.TransPts as remain
             FROM transpoints tp
             JOIN paid p on tp.teamid=p.teamid and tp.season=p.season
             WHERE tp.teamid = :teamId and tp.season = :season",
            ['teamId' => $teamId, 'season' => $season]
        );
        if ($payment && count($picks) > (int) $payment['remain'] && !$payment['paid']) {
            $errors[] = "You haven't paid entry fee and are out of free transactions.  No pick-ups allowed.";
        }

        // Each pickup must be a free agent
        $picks = array_map('intval', $picks);
        foreach ($picks as $playerId) {
            $taken = $this->connection->fetchAssociative(
                'SELECT r.playerid, p.lastname, p.firstname FROM roster r, players p
                 WHERE r.DateOff is null and r.playerid=p.playerid and r.Playerid = :playerId',
                ['playerId' => $playerId]
            );
            if ($taken) {
                $errors[] = "{$taken['firstname']} {$taken['lastname']} is already on a roster!!";
            }
        }

        if ($errors !== []) {
            return $errors;
        }

        $this->connection->transactional(function (Connection $conn) use (
            $teamId, $season, $week, $picks, $drops, $waiverPriorities, $updateWaivers
        ) {
            if ($drops !== []) {
                $conn->executeStatement(
                    'UPDATE roster SET DateOff=now() WHERE DateOff is null AND teamid = :teamId AND playerid in (:drops)',
                    ['teamId' => $teamId, 'drops' => $drops],
                    ['drops' => ArrayParameterType::INTEGER]
                );
            }
            foreach ($picks as $playerId) {
                $conn->executeStatement(
                    'INSERT INTO roster (Playerid, Teamid, Dateon) VALUES (:playerId, :teamId, now())',
                    ['playerId' => $playerId, 'teamId' => $teamId]
                );
            }
            if ($picks !== []) {
                $conn->executeStatement(
                    'UPDATE transpoints SET TransPts=Transpts + :count WHERE teamid = :teamId AND season = :season',
                    ['count' => count($picks), 'teamId' => $teamId, 'season' => $season]
                );
            }
            foreach ($picks as $playerId) {
                $this->logTransaction($conn, $teamId, $playerId, 'Sign');
            }
            foreach ($drops as $playerId) {
                $this->logTransaction($conn, $teamId, $playerId, 'Cut');
            }

            if ($updateWaivers) {
                $conn->executeStatement(
                    'DELETE FROM waiverpicks WHERE season = :season AND week = :week AND teamid = :teamId',
                    ['season' => $season, 'week' => $week, 'teamId' => $teamId]
                );
                ksort($waiverPriorities);
                $priority = 1;
                foreach ($waiverPriorities as $playerId) {
                    $conn->executeStatement(
                        'INSERT INTO waiverpicks (teamid, season, week, playerid, priority)
                         VALUES (:teamId, :season, :week, :playerId, :priority)',
                        [
                            'teamId' => $teamId, 'season' => $season, 'week' => $week,
                            'playerId' => (int) $playerId, 'priority' => $priority++,
                        ]
                    );
                }
            }
        });

        return [];
    }

    private function logTransaction(Connection $conn, int $teamId, int $playerId, string $method): void
    {
        $conn->executeStatement(
            'INSERT INTO transactions (Teamid, Playerid, Method, Date) VALUES (:teamId, :playerId, :method, now())',
            ['teamId' => $teamId, 'playerId' => $playerId, 'method' => $method]
        );
    }
}
