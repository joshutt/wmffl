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
    $thequery .= "FROM draftdate WHERE userid=$usernum ORDER BY date";
    $results = mysqli_query($conn, $thequery);

?>

<P>In an effort to avoid some of the problems we have had in recent years
with the setting of the draft date, I am trying to get it out of the way early.  So I ask that everyone fill out the below form indicating which days they
can NOT attend.  The ballot for this will close on June 1st and the draft 
date will be announced shortly thereafter.  Any date that you don't specify as
not being able to attend, I will assume that you can.</P>
<P>NOTE: Monday, September 2nd is Labor Day, all other days under consideration are
weekend days.
</P>

<P><FORM ACTION="processdraftdate.php" METHOD="POST">

<TABLE BORDER=1 WIDTH=50%>
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

