<?php

namespace App\Service;

use App\Entity\Paid;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The public team-finances ledger, ported from legacy
 * football/history/teammoney.php + common/moneyUtil.php. Reads the
 * same Paid/season_flags data the admin money pages maintain and
 * computes each team's season balance: previous balance, entry fee,
 * payments, late fees, illegal-lineup and bye-week activation charges,
 * transaction-point overage, per-win payouts and playoff winnings.
 */
class TeamMoneyService
{
    // Legacy magic numbers (teammoney.php:44-54)
    private const ILLEGAL_ACTIVATION_FINE = 5;
    private const BYE_WEEK_ACTIVATION_FINE = 1;
    private const EXTRA_TRANSACTION_FINE = 1;
    private const NUM_OF_GAMES = 84;
    private const ENTRY_FEE = 75;
    private const WIN_PERCENT = 0.25;
    private const POST_PERCENT = 0.5;
    private const DIV_PERCENT = 0.05;
    private const PLAYOFF_PERCENT = 0.05;
    private const FINAL_PERCENT = 0.25;
    private const CHAMP_PERCENT = 0.50;

    public function __construct(
        private Connection $connection,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * The full ledger for a season: per-team rows (legacy $teamRow with
     * balance/stillOwe computed), the payout table, the last-update
     * date and the logged-in-team amounts owed (PayPal button).
     */
    public function getLedger(int $season, bool $showNextSeasonFee): array
    {
        $paidArr = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Paid::class, 'p')
            ->join('p.team', 't')
            ->where('p.season = :season')
            ->orderBy('t.name', 'ASC')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult();

        $paidRows = array_map(fn(Paid $p) => [
            'teamid'   => $p->getTeam()->getId(),
            'teamName' => $p->getTeam()->getName(),
            'paid'     => (bool) $p->isPaid(),
            'previous' => $p->getPrevious(),
            'entry'    => $p->getEntryFee(),
            'amtPaid'  => $p->getAmtPaid(),
            'lateFee'  => $p->getLateFee(),
        ], $paidArr);

        return $this->computeLedger(
            $paidRows,
            $this->getExtraCharges($season),
            $this->getWins($season),
            $this->getSeasonFlags($season),
            $showNextSeasonFee
        ) + ['lastUpdate' => $this->getLastUpdate()];
    }

    /**
     * The pure ledger math (legacy teammoney.php:57-152), separated
     * from data access for testability.
     */
    public function computeLedger(
        array $paidRows,
        array $fines,
        array $wins,
        array $flags,
        bool $showNextSeasonFee
    ): array {
        $teams = [];
        $fullNeg = 0;
        foreach ($paidRows as $p) {
            $id = $p['teamid'];
            $f = $fines[$id] ?? ['name' => $p['teamName'], 'illegal' => 0, 'byeWeek' => 0, 'Remaining' => 0];
            $overage = $f['Remaining'] < 0 ? -$f['Remaining'] * self::EXTRA_TRANSACTION_FINE : 0;

            $neg = $p['lateFee'] + $f['illegal'] * self::ILLEGAL_ACTIVATION_FINE
                + $f['byeWeek'] * self::BYE_WEEK_ACTIVATION_FINE + $overage;
            $fullNeg += $neg;

            $teamFlags = $flags[$id] ?? [];
            $playoffs = [];
            foreach (['division_winner' => 'd', 'playoff_team' => 'p', 'finalist' => 'f', 'champion' => 'c'] as $flag => $code) {
                if (!empty($teamFlags[$flag])) {
                    $playoffs[] = $code;
                }
            }

            $teams[$id] = [
                'name'      => $f['name'],
                'deliquent' => !$p['paid'],
                'overage'   => $overage,
                'negative'  => $neg,
                'previous'  => $p['previous'],
                'entry'     => $p['entry'],
                'paid'      => $p['amtPaid'],
                'lateFee'   => $p['lateFee'],
                'illegal'   => $f['illegal'],
                'byeWeek'   => $f['byeWeek'],
                'wins'      => isset($wins[$id]) ? $wins[$id]['wins'] + $wins[$id]['ties'] / 2 : 0,
                'playoffs'  => $playoffs,
            ];
        }

        // Pot and payout rates (legacy teammoney.php:102-109)
        $totalPot = self::ENTRY_FEE * count($teams) + $fullNeg;
        $perWin = round($totalPot * self::WIN_PERCENT / self::NUM_OF_GAMES, 2);
        $playoffPot = $totalPot * self::POST_PERCENT;
        $payouts = [
            'totalPot'    => $totalPot,
            'perWin'      => $perWin,
            'divisionWin' => round($playoffPot * self::DIV_PERCENT, 2),
            'playoffApp'  => round($playoffPot * self::PLAYOFF_PERCENT, 2),
            'champApp'    => round($playoffPot * (self::FINAL_PERCENT - self::PLAYOFF_PERCENT), 2),
            'champWin'    => round($playoffPot * (self::CHAMP_PERCENT - self::FINAL_PERCENT), 2),
        ];

        $amtOwed = [];
        foreach ($teams as $id => &$t) {
            $winnings = 0;
            $playoffLines = [];
            if (in_array('d', $t['playoffs'])) {
                $winnings += $payouts['divisionWin'];
                $playoffLines[] = ['label' => 'Division Title', 'amount' => $payouts['divisionWin']];
            }
            if (in_array('p', $t['playoffs'])) {
                $winnings += $payouts['playoffApp'];
                $playoffLines[] = ['label' => 'Playoff Team', 'amount' => $payouts['playoffApp']];
            }
            if (in_array('f', $t['playoffs'])) {
                $winnings += $payouts['champApp'];
                $playoffLines[] = ['label' => 'First Round Win', 'amount' => $payouts['champApp']];
            }
            if (in_array('c', $t['playoffs'])) {
                $winnings += $payouts['champWin'];
                $playoffLines[] = ['label' => 'Championship', 'amount' => $payouts['champWin']];
            }

            $t['balance'] = $t['previous'] - $t['entry'] + $t['paid'] - $t['negative']
                + $t['wins'] * $perWin + $winnings;
            if ($t['balance'] >= 0) {
                $t['deliquent'] = false;
            }
            $t['playoffLines'] = $playoffLines;

            if ($showNextSeasonFee) {
                $owe = $t['balance'] - self::ENTRY_FEE;
                $t['stillOwe'] = $owe < 0 ? -$owe : 0;
            } else {
                $t['stillOwe'] = $t['deliquent'] ? -$t['balance'] : 0;
            }

            if ($t['stillOwe'] > 0) {
                $amtOwed[$id] = $t['stillOwe'];
            }
        }
        unset($t);

        return [
            'teams'   => $teams,
            'payouts' => $payouts,
            'amtOwed' => $amtOwed,
        ];
    }

    /**
     * Per-team illegal-activation counts (player not on the WMFFL
     * roster, not on an NFL team, or on IR at activation time),
     * bye-week activations and remaining transaction points — the
     * three-way UNION from moneyUtil.php getExtraCharges().
     */
    private function getExtraCharges(int $season): array
    {
        $sql = <<<SQL
select tn.teamid, tn.name, count(illegal.playerid) as 'illegal', coalesce(bye.players, 0) as 'byeWeek',
       tp.TotalPts - (tp.ProtectionPts+tp.TransPts) as 'Remaining'
from teamnames tn
left join ((select tn.teamid, wm.Season, wm.week, ra.playerid
            from teamnames tn
                     JOIN activations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     join weekmap wm on ra.season = wm.season and ra.week = wm.week
                     LEFT JOIN roster r on ra.teamid = r.teamid and ra.playerid = r.playerid and r.dateon < wm.ActivationDue
                and (r.dateoff is null or r.dateoff > wm.ActivationDue)
            where wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC'
              and r.playerid is null)
           UNION
           (select tn.teamid, wm.Season, wm.week, ra.playerid
            from teamnames tn
                     JOIN activations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     join weekmap wm on ra.season = wm.season and ra.week = wm.week
                     LEFT JOIN nflrosters nr on nr.playerid = ra.playerid and nr.dateon < wm.ActivationDue
                and (nr.dateoff is null or nr.dateoff > wm.ActivationDue)
            where wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC'
              and nr.nflteamid is null)
           UNION
           (select tn.teamid, wm.Season, wm.week, ra.playerid
            from teamnames tn
                     JOIN activations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     JOIN weekmap wm on ra.season = wm.season and ra.week = wm.week
                     JOIN ir on ir.playerid = ra.playerid and ir.dateon <= wm.ActivationDue and
                                (ir.dateoff is null or ir.dateoff > wm.ActivationDue)
            WHERE wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC')) as illegal on tn.teamid=illegal.teamid
LEFT JOIN (select tn.teamid, count(*) as players
           from teamnames tn
                    JOIN activations ra on tn.season=ra.season and tn.teamid=ra.teamid
                    join weekmap wm on ra.season=wm.season and ra.week=wm.week
                    LEFT JOIN nflrosters nr on nr.playerid=ra.playerid and nr.dateon < wm.ActivationDue
               and (nr.dateoff is null or nr.dateoff > wm.ActivationDue)
                    LEFT JOIN nflbyes nb on nr.nflteamid=nb.nflteam and nb.season=wm.season and nb.week=wm.week
           where wm.season=:season and wm.ActivationDue<now() and ra.pos != 'HC'
             and (nb.nflteam is not null or nr.nflteamid is null)
           group by tn.teamid) as bye ON tn.teamid=bye.teamid
JOIN transpoints tp on tn.teamid=tp.teamid and tn.season=tp.season
where tn.season=:season
group by tn.teamid
SQL;

        return $this->byTeamId($sql, $season);
    }

    /** Regular-season W/L/T per team (moneyUtil.php getWins). */
    private function getWins(int $season): array
    {
        return $this->byTeamId(
            "select t.teamid, sum(if(tw.Result='W', 1, 0)) as 'wins',
                    sum(if(tw.Result='L', 1, 0)) as 'losses',
                    sum(if(tw.Result='T', 1, 0)) as 'ties'
             from team t
             join team_wins tw on t.teamid=tw.Team
             where tw.season=:season and tw.week <= 14
             group by t.teamid",
            $season
        );
    }

    /** season_flags rows keyed by teamid (moneyUtil.php getSeasonFlags). */
    private function getSeasonFlags(int $season): array
    {
        return $this->byTeamId('SELECT * FROM season_flags sf where sf.season=:season', $season);
    }

    /**
     * Latest of: last transaction, last completed week, the
     * config money.update stamp (moneyUtil.php getLastUpdate).
     */
    public function getLastUpdate(): ?string
    {
        $value = $this->connection->fetchOne(
            "select DATE_FORMAT(greatest(t.Date, max(wm.EndDate), (c.value)), '%b %e, %Y')
             from transactions t
             join weekmap wm
             join config c on c.`key`='money.update'
             where wm.EndDate < now()"
        );

        return $value === false ? null : $value;
    }

    private function byTeamId(string $sql, int $season): array
    {
        $rows = $this->connection->fetchAllAssociative($sql, ['season' => $season]);

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['teamid']] = $row;
        }

        return $byId;
    }
}
