-- Create temp table of eligible ir players
CREATE TEMPORARY TABLE countedIR
select ir.playerid, ir.dateon
from ir
         join weekmap wm on now() BETWEEN wm.StartDate and wm.EndDate
         LEFT JOIN revisedactivations a on a.playerid = ir.playerid and a.season = wm.season and a.week = wm.week
where (ir.dateoff is null
    or ir.dateoff > DATE(DATE_SUB(wm.ActivationDue, INTERVAL 1 DAY)))
  and a.playerid is null;

-- Create temp tables for teams with too many players
CREATE TEMPORARY TABLE overlimit
select t.TeamID
from team t
         join weekmap wm on now() between wm.StartDate and wm.EndDate
         join roster r on t.teamid = r.teamid and r.dateon <= wm.ActivationDue and r.dateoff is null
         join newplayers p on r.PlayerID = p.playerid and p.pos != 'HC'
         left join countedIR ir on p.playerid = ir.playerid
group by t.TeamID
having count(p.playerid) > 26
    or (count(p.playerid) - count(ir.playerid)) > 25;

-- Create Transaction records for cutting of excess players
insert into transactions
(TeamID, PlayerID, Method, Date)
select r.teamid, r.PlayerID, 'Cut', wm.ActivationDue
from newplayers p
join weekmap wm
join roster r on p.playerid=r.playerid and r.dateoff is null and r.dateon > wm.StartDate
JOIN overlimit ov ON r.teamid = ov.teamid
WHERE now() BETWEEN wm.StartDate and wm.EndDate and p.pos != 'HC';

-- Remove excess players from roster
update newplayers p
join weekmap wm
join roster r on p.playerid=r.playerid and r.dateoff is null and r.dateon > wm.StartDate
    JOIN overlimit ov ON r.teamid = ov.teamid
SET r.dateoff=wm.ActivationDue
WHERE now() BETWEEN wm.StartDate and wm.EndDate and p.pos != 'HC';

-- Delete tempoary tables
DROP TABLE overlimit;
DROP TABLE countedIR;