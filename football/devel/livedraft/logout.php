<?
require_once "$DOCUMENT_ROOT/utils/start.php";

if (!isset($logArr)) {
    $_SESSION['logArr'] = array();
    $logArr = &$_SESSION['logArr'];
}

if (isset($team)) {
    $spot = array_search($team, $logArr);
    if (is_numeric($spot)) {
        $logArr[$spot] = 0;
    }
}

unset($team);
include "login.php";
?>
