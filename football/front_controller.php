<?php
require __DIR__ . '/../vendor/autoload.php';
$page = $_SERVER['REDIRECT_URL'];
if (array_key_exists('SCRIPT_URL', $_SERVER)) {
    $page = $_SERVER['SCRIPT_URL'];
}
$docRoot = $_SERVER['DOCUMENT_ROOT'];
//print "<pre>";
//print_r($_SERVER);
//print "</pre>";

//print $page;
//exit;
$curDir = getcwd();
$parent = dirname($curDir);
$includePath = '/home/joshutt/php';
$includePath .= PATH_SEPARATOR.$curDir;
$includePath .= PATH_SEPARATOR.$parent;
$includePath .= PATH_SEPARATOR.$parent.'/src';
$includePath .= PATH_SEPARATOR.$parent.'/lib';
$includePath .= PATH_SEPARATOR.$parent. '/conf';
set_include_path(get_include_path().PATH_SEPARATOR. $includePath);
#set_include_path(".:/usr/lib/php;/usr/local/lib/php;/usr/share/php:/home/joshutt/php:/home/joshutt/git/football:/home/joshutt/git/lib:/home/joshutt/git/conf");
ini_set('error_log', "$parent/logs/wmffl.log");
ini_set('log_errors', 1);
//print "*** ".get_include_path()." **";

$orgPage = $page;
error_log("PAGE: $page");

if (str_starts_with($page, '/img/')) {
    $orgPage = $page;
    $page = '/img.php';
//    $_REQUEST['url']='d19d9ad38fa48172373146a4a3136425';
    $_REQUEST['url']=substr($orgPage, 7);
    $_REQUEST['size']=substr($orgPage, 5, 1);
}

if (is_dir($docRoot.$page) && !str_ends_with($page, '/')) {
    $page .= '/';
}

if (str_ends_with($page, '/')) {
    $page .= 'index.php';
}

if (!str_ends_with($page, 'php')) {
    $page .= '.php';
}

$path = $docRoot.$page;
#print "** $docRoot$orgPage **";
//print "<pre>";
////print_r($_SERVER);
//print $path;
//print "\n";
//print dirname($path);
//print "</pre>";
#
#if (is_dir($docRoot.$orgPage)) {
#    $p = "TRUE";
#} else {
#    $p = "FALSE";
#}

if (is_dir($path) || is_file($path)) {
    chdir(dirname($path));
    include $path;
} else {
    error_log('Path: ' .getcwd());
    include '404.php';
}

