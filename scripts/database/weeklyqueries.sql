-- Create Transaction records for cutting of excess players
insert into transactions
(TeamID, PlayerID, Method, Date)
select r.teamid, r.PlayerID, 'Cut', wm.ActivationDue
from newplayers p
join weekmap wm
join roster r on p.playerid=r.playerid and r.dateoff is null and r.dateon > wm.StartDate
JOIN (
    select t.TeamID
    from team t
             join weekmap wm
             join roster r on t.teamid = r.teamid and r.dateon <= wm.ActivationDue and r.dateoff is null
             join newplayers p on r.PlayerID = p.playerid
             left join ir on p.playerid = ir.playerid
    WHERE now() between wm.StartDate and wm.EndDate
      and p.pos != 'HC'
      and ir.id is null
    group by t.TeamID
    having count(p.playerid) > 25
) ov ON r.teamid=ov.teamid
WHERE now() BETWEEN wm.StartDate and wm.EndDate and p.pos != 'HC';

-- Remove excess players from roster
update newplayers p
join weekmap wm
join roster r on p.playerid=r.playerid and r.dateoff is null and r.dateon > wm.StartDate
JOIN (
    select t.TeamID
    from team t
             join weekmap wm
             join roster r on t.teamid = r.teamid and r.dateon <= wm.ActivationDue and r.dateoff is null
             join newplayers p on r.PlayerID = p.playerid
             left join ir on p.playerid = ir.playerid
    WHERE now() between wm.StartDate and wm.EndDate
      and p.pos != 'HC'
      and ir.id is null
    group by t.TeamID
    having count(p.playerid) > 25
) ov ON r.teamid=ov.teamid
SET r.dateoff=wm.ActivationDue
WHERE now() BETWEEN wm.StartDate and wm.EndDate and p.pos != 'HC';
