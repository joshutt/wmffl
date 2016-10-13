<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (!isset($logArr)) {
    $_SESSION['logArr'] = array();
    $logArr = &$_SESSION['logArr'];
}

$commish = 0;
if (isset($team)) {
    $sql = "SELECT count(*), sum(u.commish) FROM user u, team t where u.teamid=t.teamid and t.teamid=$team and t.name='$pass'";
    //$sql = "SELECT count(*) FROM user u, team t where u.teamid=t.teamid and t.teamid=$team and u.password=PASSWORD('$pass')";
    $resultA = mysql_query($sql) or die("ERROR Can't verify password: ".mysql_error());
    $count = mysql_fetch_row($resultA);
    if ($count[0] == 0) {
        print "ERROR Team and password did not match";
        exit();
    }

    $commish = $count[1];
    array_push($logArr, $_REQUEST['team']);
}

$queryArr = "(";
foreach ($logArr as $aTeam) {
    $queryArr .= "$aTeam, ";
}
$queryArr .= "100)";

if ($commish) {
    $_SESSION['commish'] = true;
    $results = mysql_query("SELECT teamid, name FROM team where active=1") or die("ERROR Unable to get Teams in query: ".mysql_error());
} else {
    $results = mysql_query("SELECT teamid, name FROM team where teamid in $queryArr") or die("ERROR Unable to get Teams in query: ".mysql_error());
}
while ($teamList = mysql_fetch_array($results)) {
    print "<option value=\"{$teamList['teamid']}\">{$teamList['name']}</option>";
}

?>
