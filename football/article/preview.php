<?
include "base/menu.php";

?>
<table width="100%" border="0">
<TR><TD VALIGN="top" width="100%">
        <? include "article.php"; ?>
</TD>

<td align="right" valign="top" width="244">
<table id="rightbar" width="244">
<tr><td>
</td></tr>
</table>
</td>

</TR>
</table>

<form action="confirm.php" method="post">
<input type="hidden" name="uid" value="<?= $uid ?>" />
<input type="submit" name="Edit" value="Edit"/>
<input type="submit" name="Publish" value="Publish"/>
</form>

<?
include "base/footer.php";
?>
