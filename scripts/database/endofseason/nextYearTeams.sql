set @season := 2020; -- The season that just ended
set @transpoints := 55; -- Number of transaction points a team gets each year

insert into teamnames
select teamid, season+1, name, abbrev, divisionId
from teamnames where season=@season;

insert into owners
select teamid, userid, season+1, `primary`
from owners where season=@season;

insert into weekmap
select season+1, week, DATE_ADD(startDate, INTERVAL 364 DAY), 
DATE_ADD(endDate, INTERVAL 364 DAY), DATE_ADD(ActivationDue, INTERVAL 364 DAY), 
DATE_ADD(DisplayDate, INTERVAL 364 DAY), weekname
from weekmap 
where (season=@season and week<>0) or (season=@season+1 and week=0);

insert into transpoints
(teamid, season, totalpts)
select teamid, @season+4, @transpoints from team where active=1;

insert into draftpicks
(season, round, teamid, orgTeam, pickTime)
select Season+4, round, orgTeam, orgTeam, null
from draftpicks where season=@season;

insert into waiverorder
select season+1, 0, ordernumber, teamid
from waiverorder
where season=@season and week=16;

INSERT into draftdate
SELECT o.UserID, d.Date, 'Y'
FROM owners o
JOIN ( select distinct DATE_ADD(Date, INTERVAL 52 WEEK) as 'Date'
       from draftdate d
       where Date>concat(@season,'-01-01') ) d
where o.season=@season;

insert into draftvote
select userid, @season+1, null
from user
where active=1;
