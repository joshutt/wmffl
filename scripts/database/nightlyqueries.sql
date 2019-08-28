-- Expire pending trades over 7 days old
UPDATE offer SET status='Expired'
WHERE date < DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
AND status='Pending';

-- Inactivate players not on rosters for over a year
UPDATE newplayers p
JOIN ( SELECT p.playerid, max(r2.dateon) as 'laston', max(r2.dateoff) as 'lastoff'
    FROM newplayers p
    LEFT JOIN nflrosters r on p.playerid=r.playerid and r.dateoff is null
    JOIN nflrosters r2 ON p.playerid=r2.playerid and r2.dateoff is not null
    WHERE r.playerid is null and p.active=1
    GROUP BY p.playerid ) noa on p.playerid=noa.playerid
SET p.active=0
WHERE p.active=1 and noa.lastoff < DATE_SUB(now(), INTERVAL 1 YEAR) AND
noa.lastoff < CONCAT(YEAR(DATE_SUB(now(), INTERVAL 1 YEAR)),'-09-01');


