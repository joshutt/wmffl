<?
$PATTERN = "*";
//$PATTERN = "/Spam/";
$MAINBOX = "{mail.wmffl.com:143}";

$mbox = imap_open($MAINBOX, "josh@wmffl.com", "swWR$2bo", OP_READONLY) or die("can't connect: " . imap_last_error());
$message = "";
     
$list = imap_getmailboxes($mbox, "{mail.wmffl.com:143}", $PATTERN);
if (is_array($list)) {
    while(list($key, $value) = each($list)) {
        $newBox = imap_utf7_decode($value->name);
        $foldOnly = strrchr($newBox, "}");
        //$foldOnly = strrchr(strrchr($newBox, "}"), "/");
        if (preg_match("/spam/i", $foldOnly) ||
            preg_match("/trash/i", $foldOnly) ||
            preg_match("/\./i", $foldOnly) ||
            preg_match("/draft/i", $foldOnly)) {
                continue;
        }
        $status = imap_status($mbox, $newBox, SA_UNSEEN);
        if ($status && $status->unseen > 0) {
            $message .= "$newBox -> ".$status->unseen."\n";
        }
    }
}
imap_close($mbox);


print $message;
mail("josh@wmffl.com", "Unread Mail", $message) or die("Unable to send mail");
?>
