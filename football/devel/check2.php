<?php
$mbox = imap_open("{mail.wmffl.com:143}", "josh@wmffl.com", "swWR$2bo") or die("can't connect: " . imap_last_error());
      
$numMsg = imap_num_msg($mbox);
$unseenCount = 0;
for ($i=1; $i<=$numMsg; $i++) {
    $mailHead = imap_headerinfo($mbox, $i);
    if ($mailHead->Unseen == 'U' || $mailHead->Recent=='N') {
        $unseenCount++;
    }
}
print "There are $unseenCount unread messages<br/>";


$list = imap_getmailboxes($mbox, "{mail.wmffl.com:143}", "*");
if (is_array($list)) {
    //print len($list);
    reset($list);
    while (list($key, $val) = each($list)) {
        print "($key) ";
        print imap_utf7_decode($val->name) . ",";
        print "'" . $val->delimiter . "',";
        print $val->attributes . "<br />\n";
    }
} else {
    print "imap_getmailboxes failed: " . imap_last_error() . "\n";
}

/*
$headers = imap_headers($mbox);
while (list($key, $val) = each($headers)) {
    print "$val<br/>\n";
}
    
$overview = imap_fetch_overview($mbox, "2,4:6", 0);
       
if (is_array($overview)) {
    reset($overview);
    while (list($key, $val) = each($overview)) {
        print      $val->msgno." - ".$val->date." - ".$val->subject."<br/>";
    }
}
*/
imap_close($mbox);
?>
