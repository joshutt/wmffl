<?php
require_once "utils/start.php";

$title = "2005 Draft Picks";
include "base/menu.php";
?>

<H1 Align=Center><?php print $title; ?></H1>
<HR size = "1">
This is the complete list of up-to-the-minute draft picks.  

<PRE>
<?php include "draftsummary.txt"; ?>
</PRE>

<?php include "base/footer.php"; ?>
