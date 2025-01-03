<?php

function format($value, $type='currency'): false|string
{
    static $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    if ($value == 0) {
        return '-';
    } else if ($type === 'int') {
        return $value;
    } else if ($value < 0) {
        return '<span class="debt">(' . $formatter->formatCurrency(-$value, 'USD') . ')</span>';
    } else {
        return $formatter->formatCurrency($value, 'USD');
    }
}


function getExtraCharges(\Doctrine\ORM\EntityManager $em, int $currentSeason): ?array
{
    $query = <<< EOD
select tn.teamid, tn.name, count(illegal.playerid) as 'illegal', coalesce(bye.players, 0) as 'byeWeek',
       tp.TotalPts - (tp.ProtectionPts+tp.TransPts) as 'Remaining'
from teamnames tn
left join ((select tn.teamid, tn.name, wm.Season, wm.week, ra.playerid, 'Not on WMFFL team' as reason
            from teamnames tn
                     JOIN revisedactivations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     join weekmap wm on ra.season = wm.season and ra.week = wm.week
                     LEFT JOIN roster r on ra.teamid = r.teamid and ra.playerid = r.playerid and r.dateon < wm.ActivationDue
                and (r.dateoff is null or r.dateoff > wm.ActivationDue)
            where wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC'
              and r.playerid is null)
           UNION
           (select tn.teamid, tn.name, wm.Season, wm.week, ra.playerid, 'Not on NFL team' as reason
            from teamnames tn
                     JOIN revisedactivations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     join weekmap wm on ra.season = wm.season and ra.week = wm.week
                     LEFT JOIN nflrosters nr on nr.playerid = ra.playerid and nr.dateon < wm.ActivationDue
                and (nr.dateoff is null or nr.dateoff > wm.ActivationDue)
            where wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC' # and wm.week=11
              and nr.nflteamid is null)
           UNION
           (select tn.teamid, tn.name, wm.Season, wm.week, ra.playerid, 'On IR' as reason
            from teamnames tn
                     JOIN revisedactivations ra on tn.season = ra.season and tn.teamid = ra.teamid
                     JOIN weekmap wm on ra.season = wm.season and ra.week = wm.week
                     JOIN ir on ir.playerid = ra.playerid and ir.dateon <= wm.ActivationDue and
                                (ir.dateoff is null or ir.dateoff > wm.ActivationDue)
            WHERE wm.season = :season
              and wm.ActivationDue < now()
              and ra.pos != 'HC')) as illegal on tn.teamid=illegal.teamid
LEFT JOIN (select tn.teamid, tn.name, count(*) as players
           from teamnames tn
                    JOIN revisedactivations ra on tn.season=ra.season and tn.teamid=ra.teamid
                    join weekmap wm on ra.season=wm.season and ra.week=wm.week
                    LEFT JOIN nflrosters nr on nr.playerid=ra.playerid and nr.dateon < wm.ActivationDue
               and (nr.dateoff is null or nr.dateoff > wm.ActivationDue)
                    LEFT JOIN nflbyes nb on nr.nflteamid=nb.nflteam and nb.season=wm.season and nb.week=wm.week
           where wm.season=:season and wm.ActivationDue<now() and ra.pos != 'HC' # and wm.week=11
             and (nb.nflteam is not null or nr.nflteamid is null)
           group by tn.teamid) as bye ON tn.teamid=bye.teamid
JOIN transpoints tp on tn.teamid=tp.teamid and tn.season=tp.season
where tn.season=:season
group by tn.teamid
EOD;

    return performQuery($em, $query, $currentSeason);
}

function getWins(\Doctrine\ORM\EntityManager $em, int $currentSeason): ?array {
    $query = <<<EOD
select t.teamid, sum(if(tw.Result='W', 1, 0)) as 'wins',
       sum(if(tw.Result='L', 1, 0)) as 'losses',
       sum(if(tw.Result='T', 1, 0)) as 'ties'
from team t
join team_wins tw on t.teamid=tw.Team
where tw.season=:season
group by t.teamid
EOD;

    return performQuery($em, $query, $currentSeason);
}


function getSeasonFlags(\Doctrine\ORM\EntityManager $em, int $currentSeason): ?array {
    $query = <<<EOD
SELECT *
FROM season_flags  sf
where sf.season=:season
EOD;

    return performQuery($em, $query, $currentSeason);
}


/**
 * @param \Doctrine\ORM\EntityManager $em
 * @param string $query
 * @param int $currentSeason
 * @return array|null
 */
function performQuery(\Doctrine\ORM\EntityManager $em, string $query, int $currentSeason): ?array
{
    $returnArr = array();
    try {
        $conn = $em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->bindValue('season', $currentSeason);
        $result = $stmt->executeQuery()->fetchAllAssociative();
        foreach ($result as $r) {
            $returnArr[$r['teamid']] = $r;
        }
    } catch (\Doctrine\DBAL\Exception $e) {
        error_log('Exception getting Extra charges'. $e);
        return null;
    } finally {
//        $stmt->closeCursor();
        $conn->close();
    }

    return $returnArr;
}
