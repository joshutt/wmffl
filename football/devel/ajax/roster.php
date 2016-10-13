<?
require_once "../utils/start.php";

if (!isset($checkDate) || $checkDate=="") {
    $checkDate = "now()";
}

$query =<<<EOD
select p.lastname, p.pos, p.team, 
IF(p.firstname <> '', concat(', ',p.firstname), '') 
from newplayers p, roster r, team t 
where p.playerid=r.playerid and r.teamid=t.teamid 
and r.dateon <= '$checkDate' and (r.dateoff is null or r.dateoff >= '$checkDate')
and t.teamid=$teamid 
order by p.pos, p.lastname
EOD;

$result = mysql_query($query) or die("Dead: ".mysql_error());

while ($player = mysql_fetch_row($result)) {
    print "<TR><TD>".$player[1]."</TD><TD>".$player[0].$player[3]."</TD><TD>".$player[2]."</TD></TR>";
}

?>
