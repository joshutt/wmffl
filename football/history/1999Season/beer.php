<HTML>
<HEAD>
<TITLE>WMFFL Offical Beer Vote</TITLE>
</HEAD>

<?php include 'base/menu.php'; ?>

<H1 ALIGN=Center>Offical Beer Voting</H1>
<HR size = "1">

<P><IMG SRC="/images/group_beer.jpg" ALIGN=Right>
The nominations are in and we are ready to vote on the Offical League Beer.
To vote fill out the form below.  Select which beer is your favorite and then
for each beer select whether you would drink it or not.  The Offical League
Beer will be the one that most people say is acceptable.  To break ties
favorties will be considered.  The results will be announced once all the
votes are in or on December 12. </P>

<P>Remember, you <B>MUST</B> include your name and team,
failure to do so will disqualify your vote.  You may only vote once and all
votes will be confirmed.</P>

<P><B>Voting for League Beer</B></P>

<FORM ACTION="/cgi-bin/FormMail.cgi" METHOD="POST">
<INPUT TYPE="Hidden" NAME="subject" VALUE="Beer Voting">
<INPUT TYPE="Hidden" NAME="redirect" VALUE="/beerthanks.php">

<B>Team:</B>
<SELECT NAME="Team">
	<OPTION VALUE="None">Select Your Team</OPTION>
	<OPTION VALUE="Archers">Archers Who Say Ni</OPTION>
	<OPTION VALUE="Barbarians">Barbarians</OPTION>
	<OPTION VALUE="Crusaders">Crusaders</OPTION>
	<OPTION VALUE="GreenWave">Green Wave</OPTION>
	<OPTION VALUE="Hempaholics">Hempaholics</OPTION>
	<OPTION VALUE="Norsemen">Norsemen</OPTION>
	<OPTION VALUE="Werewolves">Werewolves</OPTION>
	<OPTION VALUE="ZEN">ZEN</OPTION>
</SELECT><BR>

<B>Name:</B><INPUT TYPE="Text" NAME="name"><BR>
<B>Favorite:</B>
<SELECT NAME="Favorite">
	<OPTION VALUE="None">Select Your Favorite Beer</OPTION>
	<OPTION VALUE="AnchorSteam">Anchor Steam</OPTION>
	<OPTION VALUE="Bass">Bass Ale</OPTION>
	<OPTION VALUE="Budweiser">Budweiser</OPTION>
	<OPTION VALUE="CementMixers">Cement Mixers</OPTION>
	<OPTION VALUE="DevonShandy">Devon's Shandy</OPTION>
	<OPTION VALUE="Fosters">Foster's</OPTION>
	<OPTION VALUE="Guiness">Guiness Stout</OPTION>
	<OPTION VALUE="HempinAle">Hempin' Ale</OPTION>
	<OPTION VALUE="HoneyBrown">Honey Brown</OPTION>
	<OPTION VALUE="Killians">Killian's Red</OPTION>
	<OPTION VALUE="MadDog">Mad Dog 20/20</OPTION>
	<OPTION VALUE="MillerLite">Miller Lite</OPTION>
	<OPTION VALUE="RedStripe">Red Stripe</OPTION>
	<OPTION VALUE="SamAdams">Sam Adams</OPTION>
	<OPTION VALUE="StIdes:Lime">St. Ides Special Brew: Lime</OPTION>
	<OPTION VALUE="StIdes:Original">St. Ides Special Brew: Original</OPTION>
	<OPTION VALUE="StIdes:PassionFruit">St. Ides Special Brew: Passion Fruit</OPTION>
	<OPTION VALUE="StIdes:PinaColada">St. Ides Special Brew: Pina Colada</OPTION>
	<OPTION VALUE="StIdes:Raspberry">St. Ides Special Brew: Raspberry</OPTION>
	<OPTION VALUE="WineSpritzers">Wine Spritzers</OPTION>
	<OPTION VALUE="YuengLing">Yueng Ling</OPTION>
</SELECT><BR>

<TABLE>
<TR><TD><B>Beer Name</B></TD><TD><B>Acceptable</B></TD><TD><B>Unacceptable</B></TD>
<TD><B>Beer Name</B></TD><TD><B>Acceptable</B></TD><TD><B>Unacceptable</B></TD></TR>

<TR><TD>Anchor Steam</TD><TD><INPUT TYPE="radio" NAME="AnchorSteam" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="AnchorSteam" VALUE="no"></TD>
<TD>Miller Lite</TD><TD><INPUT TYPE="radio" NAME="MillerLite" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="MillerLite" VALUE="no"></TD></TR>

<TR><TD>Bass Ale</TD><TD><INPUT TYPE="radio" NAME="Bass" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="Bass" VALUE="no"></TD>
<TD>Red Stripe</TD><TD><INPUT TYPE="radio" NAME="RedStripe" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="RedStripe" VALUE="no"></TD></TR>

<TR><TD>Budweiser</TD><TD><INPUT TYPE="radio" NAME="Budweiser" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="Budweiser" VALUE="no"></TD>
<TD>Sam Adams</TD><TD><INPUT TYPE="radio" NAME="SamAdams" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="SamAdams" VALUE="no"></TD></TR>

<TR><TD>Cement Mixers</TD><TD><INPUT TYPE="radio" NAME="CementMixers" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="CementMixers" VALUE="no"></TD>
<TD>St. Ides Special Brew: Lime</TD><TD><INPUT TYPE="radio" NAME="StIdes:Lime" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="StIdes:Lime" VALUE="no"></TD></TR>

<TR><TD>Devon's Shandy</TD><TD><INPUT TYPE="radio" NAME="DevonShandy" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="DevonShandy" VALUE="no"></TD>
<TD>St. Ides Special Brew: Original</TD><TD><INPUT TYPE="radio" NAME="StIdes:Original" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="StIdes:Original" VALUE="no"></TD></TR>

<TR><TD>Foster's</TD><TD><INPUT TYPE="radio" NAME="Fosters" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="Fosters" VALUE="no"></TD>
<TD>St. Ides Special Brew: Passion Fruit</TD><TD><INPUT TYPE="radio" NAME="StIdes:Passion" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="StIdes:Passion" VALUE="no"></TD></TR>

<TR><TD>Guiness Stout</TD><TD><INPUT TYPE="radio" NAME="Guiness" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="Guiness" VALUE="no"></TD>
<TD>St. Ides Special Brew: Pina Colada</TD><TD><INPUT TYPE="radio" NAME="StIdes:PinaColada" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="StIdes:PinaColada" VALUE="no"></TD></TR>

<TR><TD>Hempin' Ale</TD><TD><INPUT TYPE="radio" NAME="HempinAle" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="HempinAle" VALUE="no"></TD>
<TD>St. Ides Special Brew: Raspberry</TD><TD><INPUT TYPE="radio" NAME="StIdes:Raspberry" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="StIdes:Raspberry" VALUE="no"></TD></TR>

<TR><TD>Honey Brown</TD><TD><INPUT TYPE="radio" NAME="HoneyBrown" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="HoneyBrown" VALUE="no"></TD>
<TD>Wine Spritzers</TD><TD><INPUT TYPE="radio" NAME="WineSpritzers" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="WineSpritzers" VALUE="no"></TD></TR>

<TR><TD>Killian's Red</TD><TD><INPUT TYPE="radio" NAME="Killians" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="Killians" VALUE="no"></TD>
<TD>Yueng Ling</TD><TD><INPUT TYPE="radio" NAME="YuengLing" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="YuengLing" VALUE="no"></TD></TR>

<TR><TD>Mad Dog 20/20</TD><TD><INPUT TYPE="radio" NAME="MadDog" VALUE="yes" CHECKED></TD>
<TD><INPUT TYPE="radio" NAME="MadDog" VALUE="no"></TD></TR>
</TABLE>

<INPUT TYPE="Submit" VALUE="Drink Up">
</FORM>

<?php include 'base/footer.php'; ?>
