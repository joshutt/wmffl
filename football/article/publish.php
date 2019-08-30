<?
$title = "Publish Article";

include "base/menu.php";

if (!$isin) {
    print "You Shouldn't Be here";
    exit();
}
?>
    <script type="text/javascript" src="javascript/tiny_mce/tiny_mce.js"></script>

    <script type="text/javascript">
tinyMCE.init({
        mode : "textareas",
        theme : "advanced",
        plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
    
        // Theme options
        theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        remove_linebreaks: true
});
    </script>

<?
if (isset($errors)) {
    foreach ($errors as $name) {
        print "<span style=\"color: red; weight: bold\">$name</span><br/>";
    }
}

if (!isset($artTitle)) {
    $artTitle = $_POST["title"];
}
if (!isset($url)) {
    $url = $_POST["url"];
}
if (!isset($caption)) {
    $caption = $_POST["caption"];
}
if (!isset($article)) {
    $article = $_POST["article"];
}

?>


<table>
<form method="POST" action="process.php">
<tr><th>Title:</th><td><input type="text" name="title" size="75" value="<?= $artTitle ?>"/></td></tr>
<tr><th>Image URL:</th><td><input type="text" name="url" size="75" value="<?= $url ?>"/></td></tr>
<tr><th>Caption:</th><td><input type="text" name="caption" size="75" value="<?= $caption ?>"/></td></tr>
<tr><th>Article: </th><td><textarea name="article" cols="80" rows="30"><?= $article ?></textarea></td></tr>
<tr><th><input type="submit" name="submit" value="Preview"/></th></tr>
</form>
</table>


<?
include "base/footer.html";
?>
