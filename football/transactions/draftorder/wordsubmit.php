<?
require_once "utils/start.php";

$word = $_POST["word"];
$teamid = $_POST["teamid"];


if (!$isin) {
    header("Location: index.php");
    exit();
}



$query = "select `key` from config where `key` like 'draft.order.word.%' and value=''";
$results = mysqli_query($conn, $query) or die("Ugg " . mysqli_error($conn));

$query2 = "select `key`, value from config where `key` like 'draft.order.team.%' and value='$teamid'";
$result2 = mysqli_query($conn, $query2) or die("Ugg " . mysqli_error($conn));
$count = mysqli_num_rows($result2);

if ($count > 0) {
    $message2 = "You have already submitted a word.";
}

$min = 9;
while ($row = mysqli_fetch_assoc($results)) {
    $key = $row["key"]; 
    if (substr($key, -1) < $min) {
        $min = substr($key, -1);
    }
}

if ($count == 0) {
    $query = "update config set value = '$word' where `key`='draft.order.word.$min'";
    mysqli_query($conn, $query) or die("Unable to set word " . mysqli_error($conn));
    $query = "update config set value = '$teamid' where `key`='draft.order.team.$min'";
    mysqli_query($conn, $query) or die("Unable to set team " . mysqli_error($conn));
    $message2 = "Your word has been submited as '$word' and will be the $min word in the identifier";

    if ($min == 4) {
        mail("josh@wmffl.com", "Draft Words Done", "The Draft words are done being selects");
    }
}


$title = "Word Submitted";
include "base/menu.php";
?>

<h1 align="center">Draft Order Determination</h1>
<hr size = "1" />

<p><? print $message2; ?></p>

<p>Return to <a href="index.php">word submit</a> page</p>

<? include "base/footer.php"; ?>
