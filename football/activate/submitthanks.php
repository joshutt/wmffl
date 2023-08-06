<HTML>
<HEAD>
<TITLE>Activations</TITLE>
</HEAD>

<?php include "base/menu.php"; ?>

<H1 ALIGN=Center>Activations</H1>
<HR size = "1">
<TABLE ALIGN=Center WIDTH=100% BORDER=0>
<TD WIDTH=33%><A HREF="#Current"><IMG SRC="/images/football.jpg" BORDER=0>Current Activations</A></TD>
<TD WIDTH=34%></TD>
<TD WIDTH=33%><A HREF="submitactivations.php"><IMG SRC="/images/football.jpg" BORDER=0>Submit Activations</A></TD>
</TR></TABLE>

<HR size = "1">

<CENTER>
<P><B>Your activations have been submitted for Week <? print $Week; ?></B></P>

<A NAME="Current">
<?
$_POST["week"] = $Week;
	include "currentactivations.php";
?>
</CENTER>

<?php include "base/footer.php"; ?>