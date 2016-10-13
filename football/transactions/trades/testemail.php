<?
$address = "josh.utterback@gmail.com, josh@joshutterback.com, jutterback@getwellnetwork.com, josh@wmffl.com";
$address = "josh@wmffl.com";
$subject =" A Test again";
$mailmessage = " Some text can go here ";


mail($address, $subject, $mailmessage, "From: webmaster@wmffl.com\r\n") or die("Death: ");
print "Success";
?>
