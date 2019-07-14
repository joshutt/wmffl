<?
require_once "utils/start.php";

$title = "Determine Draft Date";

if ($draftMessage == "") {

$draftMessage = <<<EOD

The default date for the draft is August 23th.  As always we check to see if
a better date is available.  Please fill out the below form, letting us know 
when you can NOT make the draft.  You are limited to selected 4 dates you can not attend.
The date will be announced on or about July 22th.</p>

EOD;

}


include "base/menu.php";
?>

<H1 ALIGN=Center>Draft Date Open</H1>
<HR size = "1"/>

<?
if ($isin) {
    $thequery = "SELECT DATE_FORMAT(d.date, '%m%e'), DATE_FORMAT(d.date, '%W, %M %D'), d.attend ";
    $thequery .= "FROM draftdate d, user u WHERE d.userid=u.userid and u.username='$user' AND d.date BETWEEN '2014-07-01' AND '2014-10-01' ORDER BY d.date";
    $results = mysqli_query($conn, $thequery);

?>

<p><? print $draftMessage; ?></p>

<P><FORM ACTION="processdraftdate.php" METHOD="POST">

<TABLE BORDER=1>
<TR><TH WIDTH=30%>Can Attend?</TH><TH WIDTH=70%>Date</TH></TR>

<?
while (list($date, $fulldate, $attend) = mysqli_fetch_row($results)) {
        print "<TR><TD><INPUT TYPE=\"radio\" NAME=\"$date\" VALUE=\"Y\" ";
        if ($attend == 'Y') print "CHECKED ";
        print "/>Yes<INPUT TYPE=\"radio\" NAME=\"$date\" VALUE=\"N\" ";
        if ($attend == 'N') print "CHECKED ";
        print "/>No</TD><TD>$fulldate</TD></TR>";
    }
?>

<TR><TD COLSPAN=2 ALIGN=Center><INPUT TYPE="Submit" VALUE="Submit"></TD></TR>

</TABLE>
</FORM>
</P>
<?
} else {
?>

<CENTER><B>You must be logged in to use this feature</B></CENTER>

<? }
include "base/footer.html";
?>

