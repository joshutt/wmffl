<?php
include 'base/menu.php';

?>
<table width="100%" border="0">
<TR><TD VALIGN="top" width="100%">
        <?php include 'article.php'; ?>
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
    <div class="text-center">
<input type="submit" class="btn btn-wmffl" name="Edit" value="Edit"/>
<input type="submit" class="btn btn-wmffl" name="Publish" value="Publish"/>
    </div>
</form>

<?php
include 'base/footer.php';
