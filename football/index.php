<?
$title = "The WMFFL Fantasy Football League";

$cssList = array("base/css/index.css");
$javascriptList = array("base/js/front.js");
include "base/menu.php";

?>


<table width="100%" align="center" valign="top" bgcolor="#660000">
    <tr>
        <td align="center"><font color="#e2a500"><b>WASHINGTON METROPOLITAN FANTASY FOOTBALL LEAGUE</b></td>
    </tr>
</table>

<table width="100%" border="0">
    <TR>
        <TD VALIGN="top" width="*">

            <div class="card m-1">
                <?php include "article.php" ?>
            </div>
            <div class="card m-1">
                <?php include "quicklinks.php"; ?>
            </div>

        </TD>

        <td align="right" valign="top" width="260">
            <div class="card m-1">
                <?php include "scores.php" ?>
            </div>
            <div class="card m-1">
                <?php include "standings.php" ?>
            </div>
            <div class="card m-1">
                <?php include "forum/commentlist.php" ?>
            </div>
        </td>

    </TR>



</TABLE>

<?
include "base/footer.html";
?>
