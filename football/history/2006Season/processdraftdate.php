<?
require_once "utils/start.php";

if (!$isin) {
    header("Location: /history/2006Season/draftdate.php");
}

$userID = 3;
foreach ($_POST as $key => $value) {
    //print "$key - $value <BR>";
    $thequery = "UPDATE draftdate SET attend='$value' ";
    $thequery.= "WHERE date='2006-".substr($key,0,2)."-".substr($key,2,2)."' ";
    $thequery .= "AND userid = $usernum"; 
    
    #print $thequery."<BR>";
    mysqli_query($conn, $thequery);
    

}

$title="WMFFL Draft Dates";
?>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>Draft Date</H1>
<HR/>

<P>Thank you for letting us know when you can make the draft.  If your situation
changes you may update your availablity at any time until the draft date is 
announced.  After that time you will need to contact Josh.</P>

<? include "base/footer.php"; ?>
