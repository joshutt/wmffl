<?

require_once "base/conn.php";
require_once "login/loginglob.php";
if (!$isin) {
    header("Location: /history/2004Season/draftdate.php");
}

$userID = 3;
foreach ($HTTP_POST_VARS as $key => $value) {
    //print "$key - $value <BR>";
    $thequery = "UPDATE draftdate SET attend='$value' ";
    $thequery.= "WHERE date='2004-".substr($key,0,2)."-".substr($key,2,2)."' ";
    $thequery .= "AND userid = $usernum"; 
    
    #print $thequery."<BR>";
    mysql_query($thequery);
    

}
?>

<HTML>
<HEAD>
<TITLE>WMFFL Draft Dates</TITLE>
</HEAD>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>Draft Date</H1>
<HR/>

<P>Thank you for letting us know when you can make the draft.  If your situation
changes you may update your availablity at any time until the draft date is 
announced.  After that time you will need to contact Josh.</P>

<? include "base/footer.html"; ?>
