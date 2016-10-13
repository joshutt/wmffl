<? 
mt_srand((double)microtime()*1000000);
$Werewolves += mt_rand(1,10); ?>

<FORM ACTION=season.php METHOD=POST>
<? print($Werewolves); ?>
<INPUT TYPE="hidden" NAME="Werewolves" VALUE="<? print($Werewolves); ?>">
<INPUT TYPE="submit">
</FORM>

<? echo "Last modified: ".date( "F j, Y", getlastmod() ); ?>