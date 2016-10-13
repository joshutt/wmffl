<?
session_start();

print $weekName;

print "<h1>SESSION</h1>";
print_r($_SESSION);
foreach ($_SESSION as $key => $value) {
    print "$key -> $value<br/>";
}


print "<h1>REQUEST</h1>";
foreach ($_REQUEST as $key => $value) {
    print "$key -> $value<br/>";
}
?>
