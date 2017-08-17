<?
// This is temporary, but maybe it's not such a bad idea
require_once "utils/start.php";

if (isset($cssList)) {
    array_unshift($cssList, "https://www.w3schools.com/w3css/4/w3.css");
//     array_unshift($cssList, "/base/css/core.css");
     array_unshift($cssList, "/base/css/theme.css");
} else {
    //$cssList = array("https://www.w3schools.com/w3css/4/w3.css");
    $cssList = array("https://www.w3schools.com/w3css/4/w3.css", "/base/css/theme.css");
}
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

if (isset($cssList)) {
    foreach ($cssList as $sheet) {
        print "<link rel=\"stylesheet\" type=\"text/css\" href=\"$sheet\"></script>";
    }
}
?>

</head>

<!-- Begin Menu.html -->


<body class="w3-theme-l5">

<!-- Navbar -->
<div class="w3-top">
<div class="w3-bar w3-theme-d2 w3-left-align w3-large">

<img id="logo" class="w3-left" style="height: 48px; " src="/images/revisedLogo.png"/>

<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/">Front Page</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/activate/activations.php">Activations</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/teams">Teams</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/history/2016Season/schedule.php">Schedule</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/history/2016Season/standings.php">Standings</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/transactions/transactions.php">Transactions</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/rules/index.php">Rules</a>
<a class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" href="/history">History</a>
</div>
</div>

<!-- Navbar on small screens -->
<div id="navDemo" class="w3-bar-block w3-theme-d2 w3-hide w3-hide-large w3-hide-medium w3-large">
  <a href="#" class="w3-bar-item w3-button w3-padding-large">Link 1</a>
    <a href="#" class="w3-bar-item w3-button w3-padding-large">Link 2</a>
      <a href="#" class="w3-bar-item w3-button w3-padding-large">Link 3</a>
        <a href="#" class="w3-bar-item w3-button w3-padding-large">My Profile</a>
        </div>


<!-- Page Container -->
<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">    
    <!-- The Grid -->
    <div class="w3-row">

<!-- End Menu.html -->
