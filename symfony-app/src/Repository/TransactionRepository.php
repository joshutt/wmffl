<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

/**
 * Queries for the member-facing transaction pages.
 * Ports football/transactions/{transactions,displayWaiverOrder,
 * listwaiverpicks,showprotections}.php with bound parameters.
 */
class TransactionRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * Date of the most recent transaction, split for the month/year
     * navigation default (transactions.php line 51).
     *
     * @return array{lastupdate: ?string, month: int, year: int}
     */
    public function getLastTransactionDate(): array
    {
        $row = $this->connection->fetchAssociative(
            "SELECT DATE_FORMAT(max(date), '%m/%e/%Y') as lastupdate,
                    DATE_FORMAT(max(date), '%m') as month,
                    DATE_FORMAT(max(date), '%Y') as year
             FROM transactions"
        );

        return [
            'lastupdate' => $row['lastupdate'] ?? null,
            'month' => (int) ($row['month'] ?? date('m')),
            'year' => (int) ($row['year'] ?? date('Y')),
        ];
    }

    /**
     * Transactions for one display period (transactions.php lines 83-99).
     * Months 9-12 are shown individually; months 1-8 are lumped into a
     * single January-August offseason view keyed by year.
     */
    public function getTransactions(int $year, int $month): array
    {
        if ($month > 8) {
            $start = sprintf('%d-%02d-01', $year, $month);
            $end = sprintf('%d-%02d-31 23:59:59.99999', $year, $month);
        } else {
            $start = "$year-01-01";
            $end = "$year-08-31 23:59:59.99999";
        }

        return $this->connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(t.date, '%M %e, %Y') as displaydate, m.name as teamname, t.method,
                    concat(p.firstname, ' ', p.lastname) as player, p.pos,
                    COALESCE(r.nflteamid, '') as nflteam, m.teamid, DATE_FORMAT(t.date, '%Y-%m-%d') as rawdate, p.playerid
             FROM transactions t
             JOIN teamnames m on t.teamid=m.teamid
             JOIN players p on p.playerid=t.playerid
             LEFT JOIN nflrosters r on p.playerid=r.playerid AND r.dateon<=t.Date and (r.dateoff >= t.date or r.dateoff is null)
             WHERE m.season = :season
             AND t.date BETWEEN :start AND :end
             ORDER BY DATE_FORMAT(t.date, '%Y/%m/%d') DESC, m.name, t.method, p.lastname",
            ['season' => $year, 'start' => $start, 'end' => $end]
        );
    }

    /**
     * All legs of the trades a team completed on one day, grouped/ordered
     * the way the trade sentence is built (transactions.php trade()).
     */
    public function getTradeDetails(int $teamId, string $date): array
    {
        return $this->connection->fetchAllAssociative(
            "select t1.tradegroup, t1.date, tm1.name as teamfrom,
                    p.lastname, p.firstname, p.pos, p.team, t1.other, p.playerid
             from trade t1
             left join trade t2 on t1.tradegroup=t2.tradegroup and t1.teamfromid<>t2.teamfromid
             join teamnames tm1 on t1.teamfromid=tm1.teamid
             left join team tm2 on t2.teamfromid=tm2.teamid
             join weekmap wm on tm1.season=wm.season
             left join players p on p.playerid=t1.playerid
             where (t1.TeamFromid = :teamId or t1.TeamToid = :teamId)
             and t1.date = :date
             and :date between wm.startDate and wm.enddate
             group by t1.tradegroup, abs(tm1.teamid - :teamId), p.lastname",
            ['teamId' => $teamId, 'date' => $date]
        );
    }

    /** Waiver selection order for a week (displayWaiverOrder.php line 36) */
    public function getWaiverOrder(int $season, int $week): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT t.name FROM team t, waiverorder w
             WHERE t.teamid=w.teamid AND w.season = :season AND w.week = :week
             ORDER BY w.ordernumber',
            ['season' => $season, 'week' => $week]
        );
    }

    /** A member's current waiver priorities (displayWaiverOrder.php line 53) */
    public function getMemberWaiverPicks(int $season, int $week, int $teamId): array
    {
        return $this->connection->fetchAllAssociative(
            'select p.firstname, p.lastname, p.pos, p.team
             from waiverpicks wp join players p on wp.playerid=p.playerid
             where wp.season = :season and wp.week = :week and wp.teamid = :teamId
             order by wp.priority',
            ['season' => $season, 'week' => $week, 'teamId' => $teamId]
        );
    }

    /** Awarded waiver picks for a week (listwaiverpicks.php) */
    public function getWaiverAwards(int $season, int $week): array
    {
        return $this->connection->fetchAllAssociative(
            'select wa.pick, tn.name, p.firstname, p.lastname, p.pos, p.team
             from waiveraward wa, teamnames tn, players p
             where wa.season = :season and wa.week = :week and wa.teamid=tn.teamid
             and wa.playerid=p.playerid and tn.season=wa.season
             order by wa.pick',
            ['season' => $season, 'week' => $week]
        );
    }

    /**
     * Protected players for a season (showprotections.php). The NFL team
     * shown is the one the player was on at that season's mid-August.
     */
    public function getProtections(int $season, bool $byTeam): array
    {
        $orderBy = $byTeam ? 'ORDER BY t.name, p.pos, p.lastname' : 'ORDER BY p.pos, p.lastname';

        return $this->connection->fetchAllAssociative(
            "select t.name, t.abbrev, CONCAT(p.firstname, ' ', p.lastname) as player,
                    p.pos, r.nflteamid, pro.cost
             FROM players p
             LEFT JOIN nflrosters r on p.playerid=r.playerid and r.dateon <= concat(:season, '-08-15')
                  and (r.dateoff is null or r.dateoff >= concat(:season, '-08-15'))
             JOIN protections pro on p.playerid=pro.playerid
             JOIN teamnames t on t.teamid=pro.teamid and t.season=pro.season
             WHERE pro.season = :season
             $orderBy",
            ['season' => $season]
        );
    }
}
