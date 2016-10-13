<?
require_once "$DOCUMENT_ROOT/base/conn.php";

$gameScores = "SELECT t1.name, s.scorea, t2.name, s.scoreb ";
$gameScores .= "FROM schedule s, team t1, team t2, weekmap w ";
$gameScores .= "WHERE s.teama = t1.teamid ";
$gameScores .= "AND s.teamb = t2.teamid ";
$gameScores .= "AND s.season=w.season AND s.week=w.week-1 ";
$gameScores .= "AND current_date() BETWEEN w.startdate and w.enddate ";
$gameScores .= "ORDER BY MD5(CONCAT(t1.name, t2.name))";

$standingSQL = "select t1.name as name, t1.divisionid as division, ";
$standingSQL .= "if (t1.teamid=s.teama, s.scorea, s.scoreb) as ptsfor, ";
$standingSQL .= "if (t1.teamid=s.teamb, s.scorea, s.scoreb) as ptsag, ";
$standingSQL .= "t2.name as opp, t2.divisionid as oppdiv, s.week  as week ";
$standingSQL .= "from schedule s, team t1, team t2, weekmap w ";
$standingSQL .= "where s.season=w.season and s.week<w.week ";
$standingSQL .= "AND current_date() BETWEEN w.startdate and w.enddate ";
$standingSQL .= "and t1.teamid in (s.teama, s.teamb) ";
$standingSQL .= "and t2.teamid in (s.teama, s.teamb) ";
$standingSQL .= "and t1.teamid<>t2.teamid";

$nextGame = "SELECT t1.name, t2.name ";
$nextGame .= "FROM schedule s, team t1, team t2, weekmap w ";
$nextGame .= "WHERE s.teama=t1.teamid AND s.teamb=t2.teamid ";
$nextGame .= "AND s.season=w.season AND s.week=w.week ";
$nextGame .= "AND current_date() BETWEEN w.startdate and w.enddate ";
$nextGame .= "ORDER BY MD5(CONCAT(t1.name, t2.name))";

$nflBye = "SELECT t.name FROM nflstatus s, nflteams t, weekmap w ";
$nflBye .= "WHERE s.nflteam=t.nflteam AND s.status='B' ";
$nflBye .= "AND s.season=w.season AND s.week=w.week ";
$nflBye .= "AND current_date() BETWEEN w.startdate AND w.enddate ";
$nflBye .= "ORDER BY t.name";

select t.name, p.position, p.lastname, p.firstname, p.NFLteam, ps.active
from players p, activations a, weekmap w, team t
LEFT JOIN playerscores ps ON p.playerid=ps.playerid AND w.season=ps.season
AND w.week=ps.week 
where a.season=w.season and a.week=w.week-1
and current_date() BETWEEN w.startdate and w.enddate
and p.playerid in 
(a.HC, a.QB, a.RB1, a.RB2, a.WR1, a.WR2, a.TE, a.K, a.OL, a.DL1,
a.DL2, a.LB1, a.LB2, a.DB1, a.DB2) 
and p.playerid=ps.playerid
and t.teamid=a.teamid
order by a.teamid, p.position, p.lastname, p.firstname

?>
