<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (!$isin) {
    header("Location: /history/2017Season/draftdate.php");
}

$numNo = 0;
foreach ($HTTP_POST_VARS as $key => $value) {
    if ($value == "N") {
        $numNo++;
    }
}

if ($numNo > 4) {

$draftMessage = <<<EOD

<font color="red"><b>Your draft request has NOT been recorded!!!</b></font></p>

<p>You may select at most 4 dates you can not attend.  If there are truly more than four dates
you can't make it, then pick the four that you are least likely to be able to attend (in person
or remotly).

EOD;

} else {
    $season = 2017;

    foreach ($HTTP_POST_VARS as $key => $value) {
        //print "$key - $value <BR>";
        $thequery = "UPDATE draftdate SET attend='$value' ";
        $thequery.= "WHERE date='$season-".substr($key,0,2)."-".substr($key,2,2)."' ";
        $thequery .= "AND userid = $usernum"; 
        
        #print $thequery."<BR>";
        mysql_query($thequery) or die("Error: ".mysql_error());
        

    }

    $newQuery = "UPDATE draftvote SET lastUpdate=now() where season=$season and userid=$usernum";
    mysql_query($newQuery) or die("Error: ".mysql_error());

    $draftMessage = <<<EOD
    Your draft request has been recorded.</p>

    <p>If your situation
    changes you may update your availablity at any time until the draft date is 
    announced.  After that time you will need to contact Josh.</p>
EOD;
}
    
include "draftdate.php";
?>