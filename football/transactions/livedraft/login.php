<?
print "Hi";
exit();
require_once "utils/start.php";

print_r($_POST);

if (!isset($logArr)) {
    $_SESSION['logArr'] = array( 0=>0);
    $logArr = &$_SESSION['logArr'];
    
}

if (!isset($_POST)) {
    print "ERROR: Please provide username and password";
    exit();
}
print "AAA";

$commish = 0;
if (isset($team)) {
    $sql = "SELECT count(*), sum(u.commish), max(u.userid) FROM user u, team t where u.teamid=t.teamid and t.teamid=$team and u.password=MD5('$pass')";
    $resultA = mysql_query($sql) or die("ERROR Can't verify password: ".mysql_error());
    $count = mysql_fetch_row($resultA);
    if ($count[0] == 0) {
        print "ERROR Username and password did not match";
        exit();
    }

    $commish = $count[1];
    array_push($logArr, $_REQUEST['team']);
)
    $_SESSION['userid'] = $count[2];
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
