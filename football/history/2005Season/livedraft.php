<?
require_once "$DOCUMENT_ROOT/utils/start.php";

$title = "2005 Draft Picks";
include "$DOCUMENT_ROOT/base/menu.php";
?>

<H1 Align=Center><? print $title; ?></H1>
<HR size = "1">
This is the complete list of up-to-the-minute draft picks.  

<PRE>
<? include "draftsummary.txt"; ?>
</PRE>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
