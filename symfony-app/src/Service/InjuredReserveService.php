<?php

namespace App\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Injured-reserve rules and moves, ported from
 * football/transactions/IRResource.php.
 *
 * A player is IR-eligible when he is on the team's active roster, has a
 * current-week NFL injury with one of the qualifying statuses, and is not
 * already on the WMFFL IR. Both moves log a transactions row.
 */
class InjuredReserveService
{
    public const IR_STATUSES = ['IR', 'IR-PUP', 'IR-NFI', 'IR-R'];

    public function __construct(
        private Connection $connection
    ) {
    }

    public function getEligiblePlayers(int $teamId): array
    {
        return $this->connection->fetchAllAssociative(
            "select p.playerid, p.firstname, p.lastname, p.pos, inj.status, inj.details,
                    DATE_FORMAT(inj.expectedReturn, '%m-%d-%Y') as expreturn
             from players p
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             join roster r on p.playerid=r.PlayerID and r.dateoff is null
             join newinjuries inj on p.playerid = inj.playerid and inj.season=wm.Season and inj.week=wm.Week
             left join ir on p.playerid = ir.playerid and ir.dateoff is null
             where r.teamid = :teamId and ir.id is null and inj.status in (:statuses)",
            ['teamId' => $teamId, 'statuses' => self::IR_STATUSES],
            ['statuses' => ArrayParameterType::STRING]
        );
    }

    public function getCurrentIrPlayers(int $teamId): array
    {
        return $this->connection->fetchAllAssociative(
            "select p.playerid, p.firstname, p.lastname, p.pos,
                    DATE_FORMAT(ir.dateon, '%m-%d-%Y') as dateon, n.details,
                    DATE_FORMAT(n.expectedReturn, '%m-%d-%Y') as expreturn
             from players p
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             join roster r on p.playerid=r.PlayerID and r.DateOff is null
             join ir on ir.playerid=p.playerid and ir.dateoff is null
             left join newinjuries n on p.playerid = n.playerid and wm.Season=n.season and wm.week=n.week
             where r.TeamID = :teamId",
            ['teamId' => $teamId]
        );
    }

    /**
     * Add a player to IR. The eligibility rules live in the WHERE clause,
     * so an ineligible player is a no-op returning false.
     */
    public function addPlayerToIr(int $teamId, int $playerId): bool
    {
        $rows = $this->connection->executeStatement(
            'insert into ir (playerid, current, dateon)
             select p.playerid, 1, now()
             from players p
             join roster r on p.playerid=r.PlayerID and r.DateOff is null
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             left join ir on p.playerid=ir.playerid and ir.dateoff is null
             left join newinjuries inj on p.playerid=inj.playerid and wm.Season=inj.season and wm.week=inj.week
             where p.playerid = :playerId and r.teamid = :teamId and ir.id is null
             and inj.status in (:statuses)',
            ['playerId' => $playerId, 'teamId' => $teamId, 'statuses' => self::IR_STATUSES],
            ['statuses' => ArrayParameterType::STRING]
        );

        if ($rows > 0) {
            $this->logTransaction($teamId, $playerId, 'To IR');
            return true;
        }

        return false;
    }

    public function removePlayerFromIr(int $teamId, int $playerId): bool
    {
        $rows = $this->connection->executeStatement(
            'update players p
             join roster r on p.playerid=r.PlayerID and r.DateOff is null
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             left join ir on p.playerid=ir.playerid and ir.dateoff is null
             set ir.dateoff=now()
             where p.playerid = :playerId and r.teamid = :teamId and ir.id is not null',
            ['playerId' => $playerId, 'teamId' => $teamId]
        );

        if ($rows > 0) {
            $this->logTransaction($teamId, $playerId, 'From IR');
            return true;
        }

        return false;
    }

    /**
     * Legacy logged the transaction even when the IR write matched no
     * rows, polluting history with phantom moves; here failed moves log
     * nothing (documented behavior fix in the phase requirements).
     */
    private function logTransaction(int $teamId, int $playerId, string $method): void
    {
        $this->connection->executeStatement(
            'INSERT INTO transactions (TeamID, PlayerID, Method, Date) VALUES (:teamId, :playerId, :method, now())',
            ['teamId' => $teamId, 'playerId' => $playerId, 'method' => $method]
        );
    }
}
