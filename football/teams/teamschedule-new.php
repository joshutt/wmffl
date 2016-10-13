<?
$title = "Head To Head Results";
include "teamheader.php";
?>

<style>
.headTable table {float: center; border: 0; margin-left: auto; margin-right: auto}
.headTable td {padding-right: 5px; padding-left: 5px}
table.headTable {margin-left: auto; margin-right: auto;}
</style>

<?

if ($vsTeam != null) {
    include "h2h.php";
} else {
    include "indschedule.php";
}

include "$DOCUMENT_ROOT/base/footer.html";
?>
