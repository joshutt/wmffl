<?
// This is temporary, but maybe it's not such a bad idea
require_once "utils/start.php";
?>

<html>
<head>
<title><? print $title; ?></title>
<link rel="icon" href="/images/logo3.png" type="image/png" />
<link rel="SHORTCUT ICON" href="/images/logo3.png" />

<?

if (isset($javascriptList)) {
    foreach ($javascriptList as $sheet) {
        print "<script src=\"$sheet\"></script>";
    }
}

print "<link rel=\"stylesheet\" type=\"text/css\" href=\"/base/css/core.css\"></script>";
if (isset($cssList)) {
    foreach ($cssList as $sheet) {
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\"></script>";
    }
}
?>

<SCRIPT LANGUAGE="JavaScript">
<!--- Hide from non-javascript browsers
	function changenews ()
	{
		var uid = document.getElementById("news").value;
		location.href = 'index.php?uid=' + uid;
	}


	function changeyear ()
	{
		var uid = document.getElementById("artSeason").value;
		location.href = 'index.php?artSeason=' + uid;
	}
// --->
</SCRIPT>
</head>

<!-- Begin Menu.html -->


<body bgcolor="#f5efef">

<TABLE bgcolor="#ffffff" align="center" width="100%" border="0" class="mainTable">
<TR><TD WIDTH=180 VALIGN=Top>

<IMG SRC="/images/blank.gif" HEIGHT=11><BR>
      <IMG SRC="/images/logo3.png" ALT="WMFFL" width="145"><BR>
<IMG SRC="/images/blank.gif" HEIGHT=20><BR>
    <div class="sideButton"><a class="sideButton" href="/index.php">Front Page</a></div>
    <div class="sideButton"><a class="sideButton" href="/activate/activations.php">Activations</a></div>
    <div class="sideButton"><a class="sideButton" href="/teams/">Teams</a></div>
    <div class="sideButton"><a class="sideButton" href="/history/2016Season/schedule.php">Schedule</a></div>
    <div class="sideButton"><a class="sideButton" href="/history/2016Season/standings.php">Standings</a></div>
    <div class="sideButton"><a class="sideButton" href="/transactions/transactions.php">Transactions</a></div>
    <div class="sideButton"><a class="sideButton" href="/rules/index.php">Rules</a></div>
    <div class="sideButton"><a class="sideButton" href="/history.php">History</a></div>

<? include "login/logininc.php"; ?>

<!--<IMG SRC="/images/blank.gif" WIDTH=180>-->

</TD><TD WIDTH=* VALIGN=Top ALIGN=Left>
<!-- End Menu.html -->
