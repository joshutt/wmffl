<?
$NFL = "http://myrss.com/f/n/f/nflAdsrct1.rss91";
$NFL2 = "http://myrss.com/f/n/f/nflAdsrct1.rss";
$DRUDGE = "http://myrss.com/f/d/r/drudgereport78oxhc1.rss91";
$DRUDGE2 = "http://myrss.com/f/d/r/drudgereport78oxhc1.rss";
$SLASHDOT = "http://slashdot.org/slashdot.rdf";
$MAGGIE = "http://magpie.sf.net/samples/imc.1-0.rdf";
$ABOUT = "http://z.about.com/6/g/football/b/index.xml";
$MOREOVER = "http://p.moreover.com/cgi-local/page?c=Sports%3A%20American%20football%20news&o=rss1";
$NEWSISFREE = "http://xml.newsisfree.com/feeds/52/1852.xml";
//$source = "http://myrss.com/f/t/h/theinsidersRaym9p2.rss";
$source = $NEWSISFREE;
//$source = "http://myrss.com/f/g/o/goIndexF9kg930.rss";

include_once ("rssparse/class_rdf_parser.php");
include_once ("rssparse/class_rss_parser.php");

$rss=new RSS_parser();
//$rss->rss_parse("http://myrss.com/f/n/f/nflAdsrct1.rss");
$rss->rss_parse($SOURCE);
//$rss->rss_parse("http://p.moreover.com/cgi-local/page?c=Sports%3A%20American%20football%20news&o=rss1");
$items = $rss->get_items_data();

foreach (array_keys($items) as $key) {
	print $key."<BR>";
}

foreach ($items as $item) {
foreach (array_keys($item) as $key) {
	print $key."<BR>";
}
        echo "<A HREF=\"".$item["link"]."\">".$item["title"]."</A><BR>";
	foreach ($item as $key=>$val) {
//		print "$key<BR>";
		print "$key: $val<BR>";
	}
	print "<P>";
}

?>
