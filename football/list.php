<?
//$source = "http://myrss.com/f/n/f/nflAdsrct1.rss";
//$source = "http://sports.yahoo.com/nfl/rss.xml";
$source = "http://www.nfl.com/rss/rsslanding?searchString=home";
//$source = "http://xml.newsisfree.com/feeds/52/1852.xml";
//$source = "http://p.moreover.com/cgi-local/page?c=Sports%3A%20American%20football%20news&o=rss";//
//$RSSVersion = 0.91;
//$RSSVersion = 1.0;
/*
if ($RSSVersion == 1.0) {
	include_once ("lib/rss/rssparse/class_rdf_parser.php");
	include_once ("lib/rss/rssparse/class_rss_parser.php");

	$rss=new RSS_parser();
	$rss->rss_parse($source);
	//$rss->rss_parse("http://myrss.com/f/n/f/nflAdsrct1.rss");
	//$rss->rss_parse("http://p.moreover.com/cgi-local/page?c=Sports%3A%20American%20football%20news&o=rss1");
	$items = $rss->get_items_data();
} else if ($RSSVersion == 0.91) {
	include_once("lib/rss/magpier/rss_fetch.inc");

	$output = fetch_rss($source);
	$items = $output->items;
}
*/

/*
require_once("lib/xmlParser.php");
require_once("lib/rss/feedParser.php");
$p = new feedParser();
//print "Feed: ";
//print_r($p);
//print "***<br/>";
$data = @implode("",@file($source));

//print "Data: <pre>";
//print_r($data);
//print "</pre>***<br/>";

$info = $p->parseFeed($data);

$newdata =& $p->buildStruct(&$data);
print "Arrayed Data: <pre>";
print_r($newdata);
print "</pre>***<br/>";

print "Info: ";
print_r($info);
print "***<br/>";

$items = $info["item"];
print "**";
print_r($items);
print "**";
*/

$feed = implode(file($source));
$xml = simplexml_load_string($feed);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
$items = $array['entry'];

//print "Items: <pre>";
//print_r($items);
//print "</pre>***<br/>";

if (sizeof($items) > 6) {
    $items = array_slice($items, 0, 6);
}
//$items = array_slice($items, 1, 5);

?>

<style>
  .NFLHeaderText {color:Red; text-decoration:bold; font-size:14pt}
  //.Headline  {color:Brown} 
  .NFLSorry {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:link {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:visited {font-size:10pt; text-decoration:none; color:Brown}
  A.NFLHeadline:hover {font-size:10pt; color:Red}
  .NFLNewsText  {font-size:10pt; color:Orange}
  A.NFLNewsText:link {text-decoration:none}
  A.NFLNewsText:visited {text-decoration:none}
  .NFLNewsDate {font-size:8pt}
</STYLE>

<TABLE ALIGN=Right BORDER='0' WIDTH='244' CELLPADDING='1' CELLSPACING='0'>
<TR><TD><TABLE BGCOLOR='#eeeeee' CELLPADDING='6' CELLSPACING='0'  border='0' WIDTH='244'>
<TR><TD height='24' align='center' bgcolor="#660000">
<FONT class='SectionHeader'>NFL NEWS</FONT></TD></TR>



<?
    if ($items != null) {
		foreach ($items as $item) {
		//print "<TR><TD><A HREF=\"".$item["link"]."\" class=\"NFLHeadline\">";
		print "<TR><TD><A HREF=\"".$item["link"]["@attributes"]["href"]."\" class=\"NFLHeadline\">";
		//print "<TR><TD><A HREF=\"".$item["id"]."\" class=\"NFLHeadline\">";
	print "<FONT class=\"NFLHeadline\">".$item["title"]."</FONT></A></TD></TR>";
//        echo "<A HREF=\"".$item["link"]."\">".$item["title"]."</A><BR>";
        }
    } else {
        print "<TR><TD><FONT class=\"NFLSorry\">Sorry, but NFL News is not currently available at this time</FONT></TD></TR>";
    }
?>

</TABLE>
</table>