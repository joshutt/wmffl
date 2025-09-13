<?
include 'check.inc.php';
//	require "../base/conn.php";
    require "/home/wmffl/public_html/base/conn.php";
	
	// Determine time range
    $cronEnter = true;
if (!isset($_GET["StartDate"]) || $_GET["StartDate"] == "") {
		$StartDate = "now()";
	} else {
		$StartDate = "'$StartDate'";
        $cronEnter = false;
	}
if (!isset($_GET["EndDate"]) || $_GET["EndDate"] == "") {
		$EndDate = "now()";
	} else {
		$EndDate = "'$EndDate'";
        $cronEnter = false;
	}
	
	$checkQuery = "SELECT * FROM transactions WHERE Date BETWEEN $StartDate AND $EndDate";

	// Check if any exist
$result = mysqli_query($conn, $checkQuery);
if (mysqli_num_rows($result) <= 0 && !$cronEnter) {
		print "No Transactions to send";
		exit();
	} 
	
	// Email if they do
	
	// Create the query
	$thequery="SELECT DATE_FORMAT(t.date, '%m/%e/%Y'), m.statid, p.statid, t.method ";
	$thequery .= "FROM transactions t, team m, players p ";
    if ($cronEnter) {
        $thequery .= ", weekmap w1, weekmap w2 ";
    }
	$thequery .= "WHERE t.teamid=m.teamid AND t.playerid=p.playerid ";
    if ($cronEnter) {
        $thequery .= "AND t.date BETWEEN w1.activationdue AND w2.activationdue ";
        $thequery .= "AND w1.season=w2.season AND w1.week=w2.week-1 ";
        $thequery .= "AND now() BETWEEN w2.startdate AND w2.enddate ";
    } else {
        $thequery .= "AND t.date BETWEEN $StartDate AND $EndDate ";
    }
	$thequery .= "ORDER BY t.date, m.name, t.method, p.lastname";
	
	$body = "";
$results = mysqli_query($conn, $thequery);
while (list($date, $teamcode, $playercode, $method) = mysqli_fetch_row($results)) {
		switch($method) {
			case 'Cut':  
			case 'Fire': $methodCode = 2; break;
			case 'Sign': 
			case 'Hire': $methodCode = 1; break;
            default:     $methodCode = 0; break;
		}
		$body .= "$date,$teamcode,$playercode,$methodCode\n";
	}
//	print $body;
	
	mail ("transactions@wmffl.com", "Transactions for $StartDate through $EndDate", $body, "From: webmaster@wmffl.com") or print "Mail Not Sent<BR>";
	print "A mail has been sent";
?>


