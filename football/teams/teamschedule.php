<?php
require_once "utils/start.php";

$page = "Schedule";
include "teamheader.php";

if ($vsTeam != null) {
    include "h2h.php";
} else {
    include "indschedule.php";
}

include "base/footer.html";
?>
