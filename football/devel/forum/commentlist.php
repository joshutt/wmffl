<?
require_once "$DOCUMENT_ROOT/utils/start.php";


require "DataObjects/Forum.php";

$post = new DataObjects_Forum;
$post->orderBy('createTime DESC');
$post->limit(6);
$post->find();

?>

<style>
  .NFLHeaderText {color:Red; text-decoration:bold; font-size:14pt}
  //.Headline  {color:Brown} 
  A.NFLHeadline:link {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:visited {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:hover {font-size:10pt; color:Red}
  .NFLNewsText  {font-size:10pt; color:Orange}
  A.NFLNewsText:link {text-decoration:none}
  A.NFLNewsText:visited {text-decoration:none}
  .NFLNewsDate {font-size:8pt}
  A.Comment:link {font-size:10pt; text-decoration:none; color:e2a500}
  A.Comment:visited {font-size:10pt; text-decoration:none; color:e2a500}
  A.Comment:hover {font-size:10pt; color:Brown}
</STYLE>

<TABLE ALIGN="left" BORDER='0' WIDTH='244' CELLPADDING='1' CELLSPACING='0'>
<TR><TD><TABLE BGCOLOR='#eeeeee' CELLPADDING='6' CELLSPACING='0'  border='0' WIDTH='244'>
<TR><TD height='24' align='center' bgcolor="#660000">
<FONT class='SectionHeader'>LATEST TRASH TALK</FONT></TD></TR>



<?
while ($post->fetch()) {
    print "<TR><TD><A HREF=\"/forum/comments.php\" class=\"NFLHeadline\">";
	print "<FONT class=\"NFLHeadline\">".$post->gettitle()."</FONT></A></TD></TR>";
}

?>

<tr><td><a href="/forum/blogentry.php" class="Comment">
Leave Commentary
</a></td></tr>

</TABLE>
</table>
