<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$season = $currentSeason;
if ($commish) {
    
    $sql = "select * from draftpicks where season = $season and playerid is not null order by Round desc, Pick desc limit 1";
    $results = mysql_query($sql);
    $picks = mysql_fetch_array($results);

    $playerid = $picks['playerid'];
    $update = "update draftpicks set playerid=null where season=$season and playerid=$playerid";
    $delete = "delete from roster where dateoff is null and playerid=$playerid";

    mysql_query($update) or die("Dead on update: ".mysql_error());
    mysql_query($delete) or die("Dead on delete: ".mysql_error());
}

?>
