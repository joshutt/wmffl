
<TABLE WIDTH=100%>
<TR><TD WIDTH=33%>
<TABLE ALIGN=Left>
<TR><TD WIDTH=5%></TD><TD><A HREF="#History"><IMG SRC="../images/football.jpg" BORDER=0></A></TD>
<TD><A HREF="#History">History</A></TD></TR></TABLE>

</TD><TD>

<TABLE ALIGN=Center>
<TR><TD>
<?
for ($yearloop=0; $yearloop<count($champyear); $yearloop++) {
	print "<IMG SRC=\"/images/greystone15-2a.jpg\" HEIGHT=100>";
	print "</TD><TD>";
}
?>
</TR><TR><TD ALIGN=Center>
<?
for ($yearloop=0; $yearloop<count($champyear); $yearloop++) {
	print $champyear[$yearloop]."</TD><TD ALIGN=Center>";
}
?>
</TD></TR></TABLE>

</TD><TD WIDTH=33%>

<TABLE ALIGN=Right>
<TR><TD><A HREF="#Roster"><IMG SRC="../images/football.jpg" BORDER=0></A></TD>
<TD><A HREF="#Roster">Roster</A></TD><TD WIDTH=5%></TD></TR></TABLE>

</TD></TR>
</TABLE>
<HR size="1">

