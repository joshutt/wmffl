<?
require_once "utils/start.php";

$title = "2006 Draft Picks";
include "base/menu.php";
?>
<!--<meta http-equiv="refresh" content="60;">-->

<H1 Align=Center><? print $title; ?></H1>
<HR size = "1">
This is the complete list of up-to-the-minute draft picks.  

<PRE>
<? include "draftsummary.txt"; ?>
</PRE>

<? include "base/footer.php"; ?>
