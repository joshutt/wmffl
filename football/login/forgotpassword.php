<?
function generate_password($length) {
	$newPass = "";
	srand((double) microtime() * 1000000);
	while (strlen($newPass) < 6) {
		$sizeTake = rand(0,strlen($Username));
		$newPass = $newPass . "" . substr($Username, $sizeTake, rand(0, strlen($Username)-$sizeTake+1));
		$newPass = $newPass . "" . rand(0,99);
		$newPass = $newPass . "" . chr(rand(65,91));
	}
	return $newPass;
}

function make_password($length, $strength=0) {
$vowels = 'aeiouy';
  $consonants = 'bdghjlmnpqrstvwxz';
  if ($strength & 1) {
    $consonants .= 'BDGHJLMNPQRSTVWXZ';
  }
  if ($strength & 2) {
    $vowels .= "AEIOUY";
  }
  if ($strength & 4) {
    $consonants .= '0123456789';
  }
  if ($strength & 8) {
    $consonants .= '@#$%^';
  }
  $password = '';
  $alt = time() % 2;
  srand(time());
  for ($i = 0; $i < $length; $i++) {
    if ($alt == 1) {
      $password .= $consonants[(rand() % strlen($consonants))];
      $alt = 0;
    } else {
        $password .= $vowels[(rand() % strlen($vowels))];
      $alt = 1;
    }
  }
  return $password;
}

require_once "utils/start.php";
	if (isset($Username)) {
			
			// Make sure that old password matches password passed in
			$thequery = "select name, email from user where username='".$Username."'";
			$result = mysql_query($thequery, $conn);
			$email = mysql_fetch_row($result);
			$numrow = mysql_num_rows($result);
			if ($numrow == 0) {
				$ErrorMessage = "Invalid Account";
			} else {
			
				$newPass = generate_password(6);
				$newPass = make_password(6, 7);

				// Save password in db
				// Sent confirmation mail
				$thequery = "UPDATE user SET password=md5('".$newPass."') WHERE username='".$Username."'";
				$result = mysql_query($thequery, $conn);

				$body = $email[0].",\n\nYour request for a new password has been completed.  Your new password is ";
				$body = $body."".$newPass."\n\nThis password must be entered exactly as it appears, the login is case sensitive.\n\n";
				$body = $body."Once you are logged in, please change your password to something you will remember.  Thank you.\n\n\n";
				$body = $body."Webmaster WMFFL";

				mail ($email[1], "WMFFL New Password", $body, "From: webmaster@$SERVER_NAME");
				mysql_close($conn);
				header("Location: thanksnew.php");
			}
			
			mysql_close($conn);
	}
	
?>

<HTML>
<HEAD>
<TITLE>Generate New Password</TITLE>
</HEAD>

<? include "base/menu.php"; ?>

<H1 ALIGN=Center>Generate New WMFFL Password</H1>
<HR size = "1">
<P>
<CENTER>

<P><FONT COLOR="Red" SIZE="+1"><? print $ErrorMessage; ?></FONT></P>

<FORM METHOD="POST">

<TABLE>
<TR><TD>Username:</TD><TD><INPUT TYPE="Text" NAME="Username" VALUE="<? print $HTTP_COOKIE_VARS["user"]; ?>"></TD></TR>
<TR><TD></TD><TD><INPUT TYPE="Submit" VALUE="Get New Password"></TD></TR>
</TABLE>

</FORM>

</CENTER>
<P>

    <? include "base/footer.html"; ?>

