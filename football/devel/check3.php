<?
$mbox = imap_open("{mail.wmffl.com:143}", "josh@wmffl.com", "swWR$2bo");

$numMsg = imap_num_msg($mbox);
$unseenCount = 0;
for ($i=1; $i<=$numMsg; $i++) {
    $mailHead = imap_headerinfo($mbox, $i);
 //   if ($mailHead->Unseen == 'U' || $mailHead->Recent=='N') {
//        $unseenCount++;
 //   }
}
print "There are $unseenCount unread messages<br/>";

imap_close($mbox);
?>
