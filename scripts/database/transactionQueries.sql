
CURRENTLY ON A TEAM
------------------
SELECT s.name, p.lastname, p.firstname, p.playerid
FROM players p, roster r, team s
WHERE p.playerid=r.playerid and r.teamid=s.teamid
and r.dateoff is null


NEVER BEEN ON A TEAM
--------------------
SELECT  'Available', p.lastname, p.firstname, p.playerid
FROM players p
LEFT  JOIN roster r ON p.playerid = r.playerid
WHERE r.dateon IS  NULL


BEEN ON A TEAM IN THE PAST, NOT NOW
----------------------------------
SELECT  'Available', p.lastname, p.firstname, p.playerid
FROM players p, roster r
WHERE p.playerid = r.playerid
GROUP BY p.playerid
HAVING COUNT(r.dateon) = COUNT(r.dateoff)

