<?php
// Paths to config files should be set in .user.ini file
$ini = parse_ini_file('wmffl.conf');

$config = parse_ini_file('wmffl.conf', TRUE);

error_reporting(E_ALL & ~E_DEPRECATED);

// TODO: This is a very bad thing to do, but currently I'm dependant on it because of old php behavior
// Identity globals are never taken from the request - they come only from
// the session Symfony writes (see start.php/connect.php) - so a request
// parameter can't spoof a login (e.g. ?isin=1&teamnum=2).
foreach ($_REQUEST as $key => $val) {
    if (in_array($key, ['isin', 'teamnum', 'usernum', 'user', 'fullname', 'commish', 'impersonating'], true)) {
        continue;
    }
    $$key = $val;
}

if (isset($week)) {
    $week = (int) $week;
}

if (isset($season)) {
    $season = (int) $season;
}
