-- Set to the season just finished
SET @season := 2019;

-- Players that were protected last year and had costs
insert into protectioncost (playerid, season, years)
select c.playerid, @season+1, c.years+1
from protectioncost c, protections p
where c.playerid=p.playerid and c.season=p.season
and c.season=@season;

-- Players that were protected last year w/ year 1
insert into protectioncost (playerid, season, years)
select p1.playerid, @season+1, 1 
from protections p1
left join protectioncost p2 on p1.playerid=p2.playerid and p1.season+1=p2.season
where p1.season=@season and p2.playerid is null;

-- Player snot protected last year, but still have costs
insert into protectioncost (playerid, season, years)
select pc1.playerid, @season+1, max(posc2.years)-1
from protectioncost pc1
left join protectioncost pc2 on pc1.playerid=pc2.playerid and pc1.season=pc2.season-1
join newplayers p on pc1.playerid=p.playerid
join positioncost posc on p.pos=posc.position and posc.years<=pc1.years and posc.endSeason is null
join positioncost posc2 on p.pos=posc2.position and posc.cost-1 = posc2.cost and posc2.endSeason is null
where pc1.season=@season and pc2.playerid is null
group by pc1.playerid
having max(posc2.years)-1 > 0;


-- Get rid of head coaches
delete pc from protectioncost pc, newplayers p
where pc.playerid=p.playerid and p.pos='HC';

