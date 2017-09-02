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

array_unshift($cssList, "/base/css/core.css");
if (isset($cssList)) {
    foreach ($cssList as $sheet) {
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\"></script>";
    }
}
?>

<style>
A.headline:link {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:active {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:visited {color:e2a500; text-decoration:None; font-size:12pt; font-weight:bold}
A.headline:hover {color:660000; text-decoration:None; font-size:12pt; font-weight:bold}

.stats {background-color:f5efef}
A.stats:link {font-size:10pt; text-decoration:none; color:660000;}
A.stats:visited {font-size:10pt; text-decoration:none; color:660000;}
A.stats:hover {font-size:10pt; text-decoration:none; color:e2a500;}

A:link {color:660000;}
A:active {color:e2a500;}
A:visited {color:660000;}
A:hover {color:e2a500;}

.row{white-space:nowrap;padding:1px 3px 1px 3px;}
.c1{background-color:#EFEFEF;}
/*.c1{background-color:#F8FCCC;}*/
.C{text-align:center;}

.titleLine1{font-size:24px;color:#660000; font-weight: bold;}
/*.titleLine1{font-size:24px;color:#004080;}*/
.titleLine2{font-size:16px;color:#CC3300;}

.PQDO {
        float: left;
        color: #FF3300;
        font-style: italic;
        width: 20px;
        text-align: center;
        padding: 0px 1px 0px 2px;
}

.headline_photo {
	margin: 0px;
	padding: 0px;
	border: 1px solid black;
}
.caption {
	font-size: 8pt;
	color: #e2a500;
	font-weight: 700;
	margin-bottom: 5px;
}

.rap{white-space:normal;}

.newsdate {
	font-size: 8pt;
	font-weight: 700;
	color: #660000;
}

.inelig {
	color: #8888BB;
	font-style: italic;
}

.mainStory {
	margin-bottom: 5px;
	font-family:Arial,Helvetica,sans-serif;
	font-size:11px;
}
.cat, .catfoot {
/*	background-image:url('../Local%20Settings/Temporary%20Internet%20Files/Content.IE5/QBCJWFGN/skin/default/images/specific/cellpic1.gif'); */
	background-color:#660000;
	color:#e2a500;
	height:25px;
	font-weight:700;
	white-space:nowrap;
	padding-left:8px;
	padding-right:3px;
	padding-top:0px;
	padding-bottom:0px
}

.mainTable {
    background-image: url(/images/bluestrip2.gif);
    background-repeat: repeat-y;
}

.sideButton {
    font-size: 18px;
    height: 24px;
    border: 0px solid;
    text-align: center;
    margin-right: 20px;
    color: #660000;
}

.loginText {
    font-weight: bold;
    color: #660000;
}

.footer {
    float: left;
}


A.sideButton {text-decoration: none; font-size:18px;}

A.sideButton:link {color:#660000; text-decoration: none; font-weight: bold}
A.sideButton:active {color:#e2a500; text-decoration: none; font-weight: bold}
A.sideButton:visited {color:#660000; text-decoration: none; font-weight: bold}
A.sideButton:hover {color:#e2a500; text-decoration: none; font-weight: bold}

</style>

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
    <div class="sideButton"><a class="sideButton" href="/">Front Page</a></div>
    <div class="sideButton"><a class="sideButton" href="/activate/activations.php">Activations</a></div>
    <div class="sideButton"><a class="sideButton" href="/teams">Teams</a></div>
    <div class="sideButton"><a class="sideButton" href="/history/2016Season/schedule.php">Schedule</a></div>
    <div class="sideButton"><a class="sideButton" href="/history/2016Season/standings.php">Standings</a></div>
    <div class="sideButton"><a class="sideButton" href="/transactions/transactions.php">Transactions</a></div>
    <div class="sideButton"><a class="sideButton" href="/rules/index.php">Rules</a></div>
    <div class="sideButton"><a class="sideButton" href="/history">History</a></div>

<? include "login/logininc.php"; ?>

<!--<IMG SRC="/images/blank.gif" WIDTH=180>-->

</TD><TD WIDTH=* VALIGN=Top ALIGN=Left>
<!-- End Menu.html -->
