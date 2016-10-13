<?
require_once "$DOCUMENT_ROOT/utils/start.php";
?>

<html>
<head>
    <title>Leave Commentary</title>
    <script type="text/javascript" src="javascript/tiny_mce/tiny_mce.js"></script>

    <script type="text/javascript">
tinyMCE.init({
        mode : "textareas"
});
    </script>

</head>

<? include "$DOCUMENT_ROOT/base/menu.php"; ?>

<h1 align="center">Enter Commentary</h1>
<hr/>

<?
if (!$isin) {
?>
<b>You must be logged in to submit a commentary entry</b>
<?
} else {
?>

<form action="processEntry.php" method="post">
    <b>Subject:</b><br/>
<input type="text" size="60" name="subject"/><br/>
<b>Body:</b><br/>
<textarea name="body" cols="60" rows="20">
</textarea><br/>
<center>
<input type="submit" value="Submit Entry"/>
</center>

</form>

<?
}
?>

<? include "$DOCUMENT_ROOT/base/footer.html"; ?>
