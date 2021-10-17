<?php
require_once 'utils/start.php';

function compressImage($url, $currentSeason, $currentWeek) {
    global $config;
    $paths = $config['Paths'];
    $maxSize = 600;
    $rootLoc = $paths['wwwPath'];
    error_log(print_r($config, true));
    $newDir = $paths['imagesPath'];
    $newName = hash_file('md5', $url).'.jpg';
    global $fail;

    set_error_handler(logerror);
    $image = imagecreatefromjpeg($url);
    if ($fail) { return null; }
    $width = imagesx($image);
    if ($fail) { return null; }
    $height = imagesy($image);
    if ($fail) { return null; }
    $percent = 1.0;
    if ($width >= $height && $width > $maxSize) {
        $percent = $maxSize / $width;
    } elseif ($height > $maxSize) {
        $percent = $maxSize / $height;
    }
    $newwidth = $width * $percent;
    $newheight = $height * $percent;
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    if ($fail) { return null; }
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    if ($fail) { return null; }
    $shortname = "$newDir/$newName";
    $fullName = "$rootLoc/$shortname";
    imagejpeg($thumb, $fullName);
    if ($fail) { return null; }
    restore_error_handler();
    return $shortname;
}

function logerror($errno, $errstr, $errfile, $errline) {
    global $fail;
    global $errors;
    error_log("Error [$errno]: $errstr in file $errfile on line $errline");
    $fail = true;
    array_push($errors, 'Provide a full URL to a JPG image');
}


$title = $_REQUEST['title'];
$url = $_REQUEST['url'];
$caption = $_REQUEST['caption'];
$article = $_REQUEST['article'];

global $fail;
$fail = false;
global $errors;
$errors = array();
if (empty($title)) {
    array_push($errors, 'Must include a title');
    $fail = true;
}
if (empty($url)) {
    array_push($errors, 'Must include an image URL');
    $fail = true;
}
if (empty($article)) {
    array_push($errors, 'Come on!  Put something in the message');
    $fail = true;
}

if (!$fail) {
    $fullName = compressImage($url, $currentSeason, $currentWeek);
    //$fullName = compressImage($url, $currentSeason, $currentWeek-1);
}


if ($fail) {
    include 'publish.php';
    exit();
}


$useTitle = mysqli_real_escape_string($conn, $title);
$useURL = mysqli_real_escape_string($conn, $fullName);
$useCaption = mysqli_real_escape_string($conn, $caption);
$useArticle = mysqli_real_escape_string($conn, $article);

$sql =<<<EOD
INSERT INTO articles
(title, link, caption, articleText, displayDate, active, author)
VALUES
('$useTitle', '$useURL', '$useCaption', '$useArticle', now(), 0, $usernum)
EOD;

//print $sql;
$result = mysqli_query($conn, $sql) or die('Failed: ' . mysqli_error($conn));
$uid = mysqli_insert_id($conn);
$_REQUEST['uid'] = $uid;

include 'preview.php';
