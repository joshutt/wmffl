<?php
$page = $_SERVER['SCRIPT_URL'];
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$curDir = getcwd();
$parent = dirname($curDir, 1);
set_include_path(get_include_path().":/home/joshutt/php:$curDir:$parent/lib:$parent/conf");
#set_include_path(".:/usr/lib/php;/usr/local/lib/php;/usr/share/php:/home/joshutt/php:$curDir:$parent/lib:$parent/conf");
#set_include_path(".:/usr/lib/php;/usr/local/lib/php;/usr/share/php:/home/joshutt/php:/home/joshutt/git/football:/home/joshutt/git/lib:/home/joshutt/git/conf");
ini_set('error_log', "$parent/logs/wmffl.log");
ini_set('log_errors', 1);

$orgPage = $page;
error_log($page);
if (is_dir($docRoot.$page) && substr($page, -1) !== '/') {
    $page .= "/";
}

if (substr($page, -1) === "/") {
    $page .= "index.php";
}

if (substr($page, -3) !== 'php') {
    $page .= ".php";
}

$path = $docRoot.$page;
#print "** $docRoot$orgPage **";
#print "<pre>";
#print_r($_SERVER);
#print $path;
#print "</pre>";
#
#if (is_dir($docRoot.$orgPage)) {
#    $p = "TRUE";
#} else {
#    $p = "FALSE";
#}
#print "** $p **";
chdir(dirname($path));
#print getcwd();

#phpinfo();
include $path;
