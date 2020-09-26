<?php
// This is temporary, but maybe it's not such a bad idea
require_once "utils/start.php";
if (!isset($title)) {
    $title = "WMFFL";
}
?>

<html>
<head>
    <title><?= $title; ?></title>
    <link rel="icon" href="/images/logo3.png" type="image/png"/>
    <link rel="SHORTCUT ICON" href="/images/logo3.png"/>

    <?
    // Include any Javascript
    if (isset($javascriptList)) {
        foreach ($javascriptList as $sheet) {
            print "<script src=\"$sheet\"></script>";
        }
    }

    // If no cssList then add it, otherwise add core.css
    if (isset($cssList)) {
        array_unshift($cssList, "/base/css/core.css?v12");
        array_unshift($cssList, "/transactions/livedraft/bootstrap.min.css");
    } else {
        $cssList = array("/transactions/livedraft/bootstrap.min.css", "/base/css/core.css");
    }

    // Print out the css
    foreach ($cssList as $sheet) {
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\"></script>";
    }
    ?>

</head>

<!-- Begin Menu.html -->


<body>

<table bgcolor="#ffffff" align="center" width="100%" border="0" class="mainTable">
    <tr>
        <td width=180 valign=Top>

            <img src="/images/blank.gif" height=11><BR>
            <img src="/images/logo3.png" alt="wmffl" width="145"><br>
            <IMG SRC="/images/blank.gif" HEIGHT=20><BR>
            <div class="sideButton"><a class="sideButton" href="/">Front Page</a></div>
            <div class="sideButton"><a class="sideButton" href="/activate/activations">Activations</a></div>
            <div class="sideButton"><a class="sideButton" href="/teams">Teams</a></div>
            <div class="sideButton"><a class="sideButton" href="/stats/leaders">Stats</a></div>
            <div class="sideButton"><a class="sideButton" href="/history/2020Season/schedule">Schedule</a></div>
            <div class="sideButton"><a class="sideButton" href="/history/2020Season/standings">Standings</a></div>
            <div class="sideButton"><a class="sideButton" href="/transactions/transactions">Transactions</a></div>
            <div class="sideButton"><a class="sideButton" href="/rules">Rules</a></div>
            <div class="sideButton"><a class="sideButton" href="/history">History</a></div>

            <? include "login/logininc.php"; ?>

            <!--<IMG SRC="/images/blank.gif" WIDTH=180>-->

        </TD>
        <TD WIDTH=* VALIGN=Top ALIGN=Left>
            <!-- End Menu.html -->
