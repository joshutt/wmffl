<?php

namespace App\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * League-wide injury report queries, ported from
 * football/stats/InjuryReportResource.php.
 */
class InjuryReportService
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Players currently on the WMFFL IR, split into the regular and
     * COVID lists.
     *
     * @return array{ir: array, covid: array}
     */
    public function getCurrentIrLists(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'select p.playerid, p.firstname, p.lastname, p.pos, nr.nflteamid, t.name, t.abbrev,
                    i.details, i.expectedReturn, ir.dateon, ir.covid
             from ir
             join players p on ir.playerid = p.playerid
             join roster r on p.playerid=r.PlayerID and r.DateOff is null
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             join teamnames t on wm.Season = t.season and r.TeamID=t.teamid
             left join injuries i on p.playerid = i.playerid and wm.season=i.season and wm.week=i.week
             left join nflrosters nr on nr.playerid=p.playerid and nr.dateoff is null
             where ir.dateoff is null
             order by t.name, p.pos, p.lastname'
        );

        $lists = ['ir' => [], 'covid' => []];
        foreach ($rows as $row) {
            $lists[$row['covid'] == 1 ? 'covid' : 'ir'][] = $row;
        }

        return $lists;
    }

    /** Rostered players whose current NFL injury status allows an IR move */
    public function getEligible(): array
    {
        return $this->connection->fetchAllAssociative(
            'select p.playerid, p.firstname, p.lastname, p.pos, nr.nflteamid, tn.abbrev, inj.status,
                    inj.details, inj.expectedReturn
             from players p
             join weekmap wm on now() between wm.StartDate and wm.EndDate
             join injuries inj on p.playerid=inj.playerid and inj.season=wm.Season and inj.week=wm.Week
             join roster r on p.playerid=r.playerid and r.dateoff is null
             left join ir on ir.playerid=p.playerid and ir.dateoff is null
             left join nflrosters nr on p.playerid=nr.playerid and nr.dateoff is null
             join teamnames tn on r.TeamID=tn.teamid and tn.season=wm.Season
             where ir.id is null and inj.status in (:statuses)',
            ['statuses' => InjuredReserveService::IR_STATUSES],
            ['statuses' => ArrayParameterType::STRING]
        );
    }

    /**
     * Every rostered player with a current-week injury, grouped by team.
     *
     * @return array<string, array> team name => players
     */
    public function getFullReport(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            "select p.firstname, p.lastname, p.pos, n.nflteamid, t.name,
                    if(ir.id, if(ir.covid=1, 'COVID', 'WMFFL IR'), i.status) as status,
                    i.details, wm.season, wm.week, ir.covid
             from injuries i
             join weekmap wm on now() between wm.StartDate and wm.EndDate and i.season=wm.season and i.week=wm.Week
             join players p on i.playerid = p.playerid
             join roster r on p.playerid=r.PlayerID and r.DateOff is null
             left join ir on ir.playerid=p.playerid and ir.dateoff is null
             join teamnames t on t.season=wm.season and t.teamid=r.TeamID
             left join nflrosters n on n.playerid=p.playerid and n.dateoff is null
             order by t.name, p.pos, n.nflteamid"
        );

        $byTeam = [];
        foreach ($rows as $row) {
            $byTeam[$row['name']][] = $row;
        }

        return $byTeam;
    }
}
