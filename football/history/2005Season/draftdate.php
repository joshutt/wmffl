<HTML>
<HEAD>
<TITLE>Determine Draft Date</TITLE>
</HEAD>

<?

require_once "base/conn.php";
require_once "login/loginglob.php";
include "base/menu.php";
?>

<H1 ALIGN=Center>Draft Date Open</H1>
<HR size = "1"/>

<?
if ($isin) {

    $thequery = "SELECT DATE_FORMAT(date, '%m%e'), DATE_FORMAT(date, '%W, %M %D'), attend ";
    $thequery .= "FROM draftdate WHERE userid=$usernum AND date BETWEEN '2005-07-01' AND '2005-10-01' ORDER BY date";
    $results = mysqli_query($conn, $thequery);

?>

<P>The default date for the draft is August 27th.  As always we check to see if
a better date is available.  Please fill out the below form, letting us know 
when you can NOT make the draft.  The date will be announced on or about June 24.</P>
<P>NOTE: Monday, September 5th is Labor Day, all other days under consideration are
weekend days.
</P>

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
include "base/footer.php";
?>

