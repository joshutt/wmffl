<?php
// Paths to config files should be set in .user.ini file
$ini = parse_ini_file('wmffl.conf');

$config = parse_ini_file('wmffl.conf', TRUE);

error_reporting(E_ALL & ~E_DEPRECATED);

// TODO: This is a very bad thing to do, but currently I'm dependant on it because of old php behavior
foreach ($_REQUEST as $key => $val) {
    $$key = $val;
}

if (isset($week)) {
    $week = (int) $week;
}

if (isset($season)) {
    $season = (int) $season;
}
