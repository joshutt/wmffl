<?
if (count($badTransArray) > 0) {
?>
<font color="red"><b>Invalid Transaction Points</b></font><br/>

<?
foreach ($badTransArray as $invalidTran) {
    $teamName = $mapping[$invalidTran->team];
    $ptsGiven = $invalidTran->pts;
    $seasonUp = $invalidTran->year;
    print "The $teamName don't have $ptsGiven remaing transactions in ";
    print "$seasonUp.<br/>";
}
}
?>

<?
if (count($badDraftArray) > 0) {
?>
<font color="red"><b>Invalid Draft Picks</b></font><br/>

<?
foreach ($badDraftArray as $invalidTran) {
    $teamName = $mapping[$invalidTran->team];
    $round = $invalidTran->round;
    $seasonUp = $invalidTran->year;
    $num = $invalidTran->num;
    if ($num == 0) {
        print "The $teamName don't have a $round".OrdinalEnding($round)." round pick in $seasonUp.";
    } else {
        print "The $teamName have $num picks in the $round".OrdinalEnding($round)." round of $seasonUp. ";
        print "Trading picks when a team has multiples in a round is not currently supported.  Please contact the commissioner to complete this trade.";
    }
    print "<br/>";
}
}
?>
