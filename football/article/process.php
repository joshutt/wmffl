<?php
require_once 'utils/start.php';
require_once 'utils/ImageProcessor.php';


function logerror($errno, $errstr, $errfile, $errline): void
{
    global $fail;
    global $errors;
    error_log("Error [$errno]: $errstr in file $errfile on line $errline");
    $fail = true;
    $errors[] = 'Provide a full URL to a JPEG, GIF or PNG image';
}

function compressImage($conn, string $url)
{
    global $config;
    $paths = $config['Paths'];
    $maxSize = 600;

    set_error_handler('logerror');
    $processor = new \utils\ImageProcessor($paths);
    $processor->createImageFromURL($url, $maxSize);
    $processor->saveImage($conn);

    restore_error_handler();
    return $processor->getImageFileName();
}


//print_r($_FILES);
//exit;

$title = $_REQUEST['title'];
$url = $_REQUEST['url'];
$upload = $_FILES['upload'];
$uploadFile = $upload['name'];
$caption = $_REQUEST['caption'];
$articleList = $_REQUEST['article'];
$article = '';

foreach ($articleList as $subArt) {
    if (empty($subArt)) {
        continue;
    }
    $article .= $subArt;
}

global $fail;
$fail = false;
global $errors;
$errors = array();
// Validate input
if (empty($title)) {
    $errors[] = 'Must include a title';
    $fail = true;
} else if (strlen($title) >= 75) {
    $errors[] = 'Title can\'t be longer than 75 characters';
    $fail = true;
}
if (empty($url) && empty($uploadFile)) {
    $errors[] = 'Must include either an image URL or upload file';
    $fail = true;
} else if (!empty($url) && !empty($uploadFile)) {
    $errors[] = 'Only include one of image URL and image upload file';
    $fail = true;
}

if (empty($article)) {
    $errors[] = 'Come on!  Put something in the message';
    $fail = true;
}

if (!$fail) {
    $file = $url;
    if (empty($file)) {
        $file = $upload['tmp_name'];
    }
    error_log("File Name [$file]" );
    $fullName = compressImage($conn, $file);
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

$sql = <<<EOD
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
