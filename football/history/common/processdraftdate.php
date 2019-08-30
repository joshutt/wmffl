<?
require_once "utils/start.php";

if (!$isin) {
    header("Location: /history/{$currentSeason}Season/draftdate.php");
}

$numNo = 0;
foreach ($_POST as $key => $value) {
    if ($value == "N") {
        $numNo++;
    }
}

if ($numNo > 4) {

$draftMessage = <<<EOD

<div class="container">
<div class="alert alert-danger col-4" role="alert">Your draft request has NOT been recorded!!!</div>

<div class="panel">You may select at most 4 dates you can not attend.  If there are truly more than four dates
you can't make it, then pick the four that you are least likely to be able to attend (in person
or remotly).</div>
</div>

EOD;

} else {
    $season = $currentSeason;

    foreach ($_POST as $key => $value) {
        //print "$key - $value <BR>";
        $thequery = "UPDATE draftdate SET attend='$value' ";
        $thequery.= "WHERE date='$season-".substr($key,0,2)."-".substr($key,2,2)."' ";
        $thequery .= "AND userid = $usernum"; 
        
        #print $thequery."<BR>";
        mysqli_query($conn, $thequery) or die("Error: " . mysqli_error($conn));
        

    }

    $newQuery = "UPDATE draftvote SET lastUpdate=now() where season=$season and userid=$usernum";
    mysqli_query($conn, $newQuery) or die("Error: " . mysqli_error($conn));

    $draftMessage = <<<EOD
    <div class="container">
    <div class="alert alert-success col-4" role="alert">Your draft request has been recorded.</div>

    <div class="panel">If your situation
    changes you may update your availablity at any time until the draft date is 
    announced.  After that time you will need to contact Josh.</div>
</div>
EOD;
}
    
include "../{$currentSeason}Season/draftdate.php";
?>
