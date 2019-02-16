<?
require_once "utils/start.php";


if ($currentWeek < 1) {
    $thisSeason = $currentSeason - 1;
} else  {
    $thisSeason = $currentSeason;
}

$check = "SELECT MAX(wm.week) FROM weekmap wm 
WHERE (DATE_SUB(wm.DisplayDate, INTERVAL 12 HOUR) < now() or wm.week=1) and wm.season=$thisSeason ";
$checRe = mysql_query($check) or die("Dead: $check<br/>".mysql_error());
list($useWeek) = mysql_fetch_row($checRe);


$sql = "SELECT wm.weekname, wm.week, s.teama, if(s.scorea>=s.scoreb,t1.name, t2.name) as 'leadname', if(s.scorea>=s.scoreb,s.scorea,s.scoreb) as 'leadscore', if(s.scorea>=s.scoreb,t2.name, t1.name) as 'trailname', if(s.scorea>=s.scoreb,s.scoreb,s.scorea) as 'trailscore', s.label, s.overtime  FROM weekmap wm, schedule s, team t1, team t2
where (DATE_SUB(wm.displaydate, INTERVAL 12 HOUR) < now() OR wm.week=1)
and wm.season=$thisSeason and s.season=wm.season and s.week=wm.week
and s.teama=t1.teamid and s.teamb=t2.teamid
and wm.week=$useWeek
order by wm.week DESC, s.label, MD5(CONCAT(t1.name, t2.name)) ";
//limit 5";

$results = mysql_query($sql) or die("Dead: $sql <br/>".mysql_error());

?>

<style>
  .NFLHeaderText {color:Red; text-decoration:bold; font-size:14pt}
  //.Headline  {color:Brown} 
  A.Score:link {font-size:10pt; text-decoration:none; color:Brown;}
  A.Score:visited {font-size:10pt; text-decoration:none; color:Brown;}
  A.Score:hover {font-size:10pt; color:Gold;}
  .NFLNewsText  {font-size:10pt; color:Orange}
  A.NFLNewsText:link {text-decoration:none}
  A.NFLNewsText:visited {text-decoration:none}
  .NFLNewsDate {font-size:8pt}
</STYLE>

<TABLE ALIGN=Right BORDER='0' WIDTH='244' CELLPADDING='2' CELLSPACING='0'>
<TR><TD><TABLE BGCOLOR='#eeeeee' CELLPADDING='0' CELLSPACING='0'  border='0' WIDTH='244'>
<TR><TD height='32' align='center' bgcolor="#660000" colspan="4">
<?
$first = true;
while (list($weekname, $week, $team1, $leader, $leadscore, $trail, $trailscore, $label, $ot) = mysql_fetch_row($results)) {
if ($first) {

?>
<FONT class='SectionHeader'><? print strtoupper($weekname); ?> SCORES</FONT></TD></TR>

<tr><td height="2" colspan="4"><font class="NFLNewsDate"><hr height="1"/></font></td></tr>

<?
    $first = false;
}

if ($label != "") {
?>
<tr><td height="10" colspan="4" align="center"><a href="" class="NFLHeadline">
<? print $label; ?></a></td></tr>
<?
}
?>

<tr height="10"><td height="10"><a href="" class="NFLHeadline"><? print $leader; ?></a></td>
<td height="10"><a href="" class="NFLHeadline"><? print $leadscore; ?></a></td>
<? if ($ot > 0) { ?>
<td rowspan="2" valign="center" align="center" height="20"><a href="" class="NFLHeadline">OT</a></td>
<? } else { ?>
<td rowspan="2" valign="center" align="center" height="20"></td>
<? } ?>
<td rowspan="2" valign="center" align="center" height="20"><a href="/activate/currentscore.php?teamid=<? print $team1;?>&week=<? print $week;?>" class="Score">Box Score</a>
</td></tr>
<tr height="10">
<td height="10"><a href="" class="NFLHeadline"><? print $trail; ?></a></td>
<td height="10"><a href="" class="NFLHeadline"><? print $trailscore; ?></a></td></tr>

<tr><td height="2" colspan="4"><font class="NFLNewsDate"><hr height="1"/></font></td></tr>

<?
}
?>

</TABLE>
</table>
