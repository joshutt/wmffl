<?
require_once "$DOCUMENT_ROOT/utils/start.php";


if ($commish) {
    if (isset($_REQUEST["start"])) {
        $sql = "UPDATE config SET value='true' WHERE `key`='draft.clock.run'";
        print "Start";
    } else if (isset($_REQUEST["stop"])) {
        $sql = "UPDATE config SET value='false' WHERE `key`='draft.clock.run'";
        print "Stop";
    }

    mysql_query($sql) or die("Dead: ".mysql_error());
}

?>
