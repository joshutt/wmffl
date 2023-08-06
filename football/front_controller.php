<?php
$page = $_SERVER['REDIRECT_URL'];
if (array_key_exists('SCRIPT_URL')) {
    $page = $_SERVER['SCRIPT_URL'];
}
$docRoot = $_SERVER['DOCUMENT_ROOT'];
//print "<pre>";
//print_r($_SERVER);
//print "</pre>";

//print $page;
//exit;
$curDir = getcwd();
$parent = dirname($curDir, 1);
set_include_path(get_include_path().PATH_SEPARATOR. '/home/joshutt/php' .PATH_SEPARATOR.$curDir.PATH_SEPARATOR.$parent. '/lib' .PATH_SEPARATOR.$parent. '/conf');
#set_include_path(".:/usr/lib/php;/usr/local/lib/php;/usr/share/php:/home/joshutt/php:/home/joshutt/git/football:/home/joshutt/git/lib:/home/joshutt/git/conf");
ini_set('error_log', "$parent/logs/wmffl.log");
ini_set('log_errors', 1);
//print "*** ".get_include_path()." **";

$orgPage = $page;
error_log("PAGE: $page");

if (substr($page, 0, 5) == '/img/') {
    $orgPage = $page;
    $page = '/img.php';
//    $_REQUEST['url']='d19d9ad38fa48172373146a4a3136425';
    $_REQUEST['url']=substr($orgPage, 7);
    $_REQUEST['size']=substr($orgPage, 5, 1);
}


if (is_dir($docRoot.$page) && substr($page, -1) !== '/') {
    $page .= '/';
}

if (substr($page, -1) === '/') {
    $page .= 'index.php';
}

if (substr($page, -3) !== 'php') {
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

