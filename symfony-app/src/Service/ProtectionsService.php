<?php

namespace App\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Player-protection rules, ported from football/transactions/
 * {protections,saveprotections}.php.
 *
 * The submission deadline is data-driven via the config table key
 * `protections.deadline` (legacy hard-coded a date that had to be edited
 * every year). No configured deadline means protections are closed.
 */
class ProtectionsService
{
    public const DEADLINE_CONFIG_KEY = 'protections.deadline';

    public function __construct(
        private Connection $connection
    ) {
    }

    public function getDeadline(): ?\DateTimeImmutable
    {
        $value = $this->connection->fetchOne(
            'SELECT `value` FROM config WHERE `key` = :key',
            ['key' => self::DEADLINE_CONFIG_KEY]
        );
        if (!$value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value, new \DateTimeZone('America/New_York'));
        } catch (\Exception) {
            return null;
        }
    }

    public function isDeadlinePassed(): bool
    {
        $deadline = $this->getDeadline();

        return $deadline === null || $deadline->getTimestamp() <= time();
    }

    /**
     * The team's protectable roster with per-position costs, protection
     * years and current protected flags (protections.php roster query).
     */
    public function getRosterWithCosts(int $teamId, int $season): array
    {
        return $this->connection->fetchAllAssociative(
            "select p.playerid, p.firstname, p.lastname, p.pos, p.team,
                    if (pc.years is null, 0, pc.years) as years,
                    max(pos.cost) as cost,
                    if (pro.cost is null, 0, 1) as protected
             from players p
             join roster r on p.playerid=r.playerid and r.dateoff is null
             join positioncost pos on p.pos=pos.position and pos.startSeason <= :season and pos.endSeason is null
             left join protectioncost pc on p.playerid=pc.playerid and pc.season = :season
             left join protections pro on pro.playerid=p.playerid and pro.season = :season
             where r.teamid = :teamId and (pos.years<=pc.years or pos.years=0)
             GROUP BY p.playerid
             ORDER BY p.pos, p.lastname, p.firstname",
            ['teamId' => $teamId, 'season' => $season]
        );
    }

    /**
     * Points budget and paid standing. `paid` requires good standing in
     * either this season or last (protections.php $ptsQuery).
     *
     * @return array{totalPts: int, protectionPts: int, paid: bool}|null
     */
    public function getPointsSummary(int $teamId, int $season): ?array
    {
        $row = $this->connection->fetchAssociative(
            "select tp.TotalPts, tp.ProtectionPts, (p1.paid | p2.paid) as paid
             from transpoints tp
             JOIN paid p1 on tp.season=p1.season and tp.teamid=p1.teamid
             JOIN paid p2 on tp.season-1=p2.season and tp.teamid=p2.teamid
             where tp.teamid = :teamId and tp.season = :season",
            ['teamId' => $teamId, 'season' => $season]
        );
        if (!$row) {
            return null;
        }

        return [
            'totalPts' => (int) $row['TotalPts'],
            'protectionPts' => (int) $row['ProtectionPts'],
            'paid' => (bool) $row['paid'],
        ];
    }

    /**
     * Replace the team's protections for a season with the given players.
     * Costs are recomputed server-side; over-budget selections change
     * nothing (saveprotections.php).
     *
     * @param int[] $playerIds
     * @return array{ok: bool, totalCost: int, allowed: int}
     */
    public function saveProtections(int $teamId, int $season, array $playerIds): array
    {
        // Empty IN() is invalid SQL; legacy used a (0) sentinel
        $playerIds = $playerIds === [] ? [0] : array_map('intval', $playerIds);

        $costs = $this->connection->fetchFirstColumn(
            "select max(pos.cost) as cost
             from players p
             join roster r on p.playerid=r.playerid and r.dateoff is null
             join positioncost pos on pos.position=p.pos
             left join protectioncost pc on p.playerid=pc.playerid and pc.season = :season
             left join protections pro on pro.playerid=p.playerid and pro.teamid=r.teamid and pro.season = :season
             where r.teamid = :teamId and (pos.years<=pc.years or pos.years=0)
             and p.playerid in (:players)
             GROUP BY p.playerid",
            ['teamId' => $teamId, 'season' => $season, 'players' => $playerIds],
            ['players' => ArrayParameterType::INTEGER]
        );
        $totalCost = (int) array_sum($costs);

        $allowed = (int) $this->connection->fetchOne(
            'SELECT totalpts FROM transpoints WHERE teamid = :teamId and season = :season',
            ['teamId' => $teamId, 'season' => $season]
        );

        if ($totalCost > $allowed) {
            return ['ok' => false, 'totalCost' => $totalCost, 'allowed' => $allowed];
        }

        $this->connection->transactional(function (Connection $conn) use ($teamId, $season, $playerIds, $totalCost) {
            $conn->executeStatement(
                'DELETE FROM protections WHERE season = :season AND teamid = :teamId',
                ['season' => $season, 'teamId' => $teamId]
            );
            $conn->executeStatement(
                "INSERT INTO protections (teamid, playerid, season, cost)
                 select r.teamid, p.playerid, :season, max(pos.cost) as cost
                 from players p
                 join roster r on p.playerid=r.playerid and r.dateoff is null
                 join positioncost pos on pos.position=p.pos
                 left join protectioncost pc on p.playerid=pc.playerid and pc.season = :season
                 where r.teamid = :teamId and (pos.years<=pc.years or pos.years=0)
                 and p.playerid in (:players)
                 GROUP BY p.playerid",
                ['teamId' => $teamId, 'season' => $season, 'players' => $playerIds],
                ['players' => ArrayParameterType::INTEGER]
            );
            $conn->executeStatement(
                'UPDATE transpoints SET protectionpts = :cost WHERE teamid = :teamId and season = :season',
                ['cost' => $totalCost, 'teamId' => $teamId, 'season' => $season]
            );
        });

        return ['ok' => true, 'totalCost' => $totalCost, 'allowed' => $allowed];
    }

    /** The team's saved protections for the confirmation view */
    public function getSavedProtections(int $teamId, int $season): array
    {
        return $this->connection->fetchAllAssociative(
            "select CONCAT(p.firstname, ' ', p.lastname) as player, p.pos, p.team, pro.cost
             from players p, protections pro
             where p.playerid=pro.playerid
             and pro.season = :season and pro.teamid = :teamId
             order by p.pos, p.lastname",
            ['teamId' => $teamId, 'season' => $season]
        );
    }
}
