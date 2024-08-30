<?php
require_once 'utils/start.php';

$page = 'Schedule';
include 'teamheader.php';

if (array_key_exists('vsTeam', $_REQUEST)) {
    include 'h2h.php';
} else {
    include 'indschedule.php';
}

include 'base/footer.php';
