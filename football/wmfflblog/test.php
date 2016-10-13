<style>
.post-title {
    color:#6A0000;
    font-family:Georgia,"Times New Roman",Times,serif;
    font-size:18px;
    font-weight:bolder;
}
.post-time {
    font-size:13px;
    font-weight:bold;
}
.post-body {
    border-bottom:1px dashed #990000;
    margin-bottom:12px;
    padding-left:36px;
    padding-right:36px;
}
</style>


<?php
/*
$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
$gdata = new Zend_Gdata($client);
$query = new Zend_Gdata_Query('http://www.blogger.com/feeds/blogID/posts/default');
$query->setMaxResults(10);
$feed = $gdata->getFeed($query);
foreach ($feed as $entry) {
    // using the magic accessor
    echo 'Title: ' . $entry->title->text;
    // using the defined accessors
    print  'Content: ' . $entry->getContent()->getText();
}
*/

$xmlString = file_get_contents("http://www.blogger.com/feeds/8508375/posts/default");
$newString = utf8_encode($xmlString);
$domDocument = domxml_open_mem($newString);

$root = $domDocument->document_element();
$node_array = $root->get_elements_by_tagname('*');

/*
print "<pre>";
foreach ($node_array as $node) {
        echo $node->node_name() . ' - ' . $node->get_content() . "\n";
}
print "</pre>";
*/

$entryArray = $root->get_elements_by_tagname("entry");

foreach ($entryArray as $entry) {
    $atitle = $entry->get_elements_by_tagname("title");
    $abody =  $entry->get_elements_by_tagname("content");
    $aauthor =  $entry->get_elements_by_tagname("author");
    $atime =  $entry->get_elements_by_tagname("updated");

    $title = $atitle[0]->get_content();
    $body = $abody[0]->get_content();
    $author = $aauthor[0]->get_content();
   // $time = $atime[0]->get_content();
    $frmtime = $atime[0]->get_content();
    $time = strtotime("YYYY-mm-DD", $frmtime);

print <<<EOD

<div class="post">
    <div class="post-title">$title</div>
    <div class="post-time">Posted by $author at $time</div>
    <div class="post-body">$body</div>
</div>

EOD;
/*
    print "<p><b>".$title[0]->get_content()."</b><br/>";
    print "<b>By ".$author[0]->get_content()."</b> - ".$time[0]->get_content()."<br/>";
    print $body[0]->get_content();
    print "</p>";
*/
}



/*
$parser = xml_parser_create();
xml_parse_into_struct($parser, $xmlString, $data);
xml_parser_free($parser);

print_r($data);
*/
?>


