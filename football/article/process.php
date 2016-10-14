<?
require_once "utils/start.php";

function imageCreateFromAny($filepath) {
    $type = exif_imagetype($filepath);
    $allowedTypes = array (
        1, // gif
        2, // jpg
        3, // png
        6 // bmp
    );
    if (!in_array($type, $allowedTypes)) {
        return false;
    }
    switch ($type) {
        case 1:
            $im = imagecreatefromgif($filepath);
            break;
        case 2:
            $im = imagecreatefromjpeg($filepath);
            break;
        case 3:
            $im = imagecreatefrompng($filepath);
            break;
        case 6:
            $im = imagecreatefromwbmp($filepath);
            break;
    }
    return $im;
}


function getExtension($type) {
    switch ($type) {
        case 1:  return "gif"; break;
        case 2:  return "jpg"; break;
        case 3:  return "png"; break;
        case 6:  return "bmp"; break;
        default: return false;
    }
}

function createImageFile($image, $fileName, $type) {
    switch ($type) {
        case 1:  // GIF
            return imagegif($image, $fileName);
            break;
        case 2:  // JPG
            return imagejpeg($image, $fileName);
            break;
        case 3:  // PNG
            return imagepng($image, $fileName);
            break;
        case 6:  // BMP
            return imagewbmp($image, $fileName);
            break;
    }
}


function compressImage($url, $config) {
    // Set-up the newName
    $maxSize = 450;
    $rootLoc = $config->getValue("Paths.wwwPath");
    $newDir = $config->getValue("Paths.imagesPath");
    $type = exif_imagetype($url);
    $newName = hash_file('md5', $url).'.'.getExtension($type);
    global $fail;

    // Establish image
    set_error_handler(logerror);
    $image = imageCreateFromAny($url);

    // Set-up new size
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

    // Define new image
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    if ($fail) { return null; }
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    if ($fail) { return null; }

    // Save new image
    $shortname = "$newDir/$newName";
    $fullName = "$rootLoc/$shortname";
    createImageFile($thumb, $fullName, $type);
    if ($fail) { return null; }
    restore_error_handler();

    // Return path name
    return $shortname;
}

function logerror($errno, $errstr) {
    global $fail;
    global $errors;
    $fail = true;
    array_push($errors, "Provide a full URL to a GIF, JPG or PNG image");
}


$title = $_POST["title"];
$url = $_POST["url"];
$caption = $_POST["caption"];
$article = $_POST["article"];

global $fail;
$fail = false;
global $errors;
$errors = array();
if (!isset($title) || empty($title)) {
    array_push($errors, "Must include a title");
    $fail = true;
}
if (!isset($url) || empty($url)) {
    array_push($errors, "Must include an image URL");
    $fail = true;
}
if (!isset($article) || empty($article)) {
    array_push($errors, "Come on!  Put something in the message");
    $fail = true;
}

if (!$fail) {
    $fullName = compressImage($url, $config);
}


if ($fail) {
    include "publish.php";
    exit();
}



$useTitle = mysql_real_escape_string($title);
$useURL = mysql_real_escape_string($fullName);
$useCaption = mysql_real_escape_string($caption);
$useArticle = mysql_real_escape_string($article);

$sql =<<<EOD
INSERT INTO articles
(title, link, caption, articleText, displayDate, active, author)
VALUES
('$useTitle', '$useURL', '$useCaption', '$useArticle', now(), 0, $usernum)
EOD;

//print $sql;
$result = mysql_query($sql) or die("Failed: ".mysql_error());
$uid = mysql_insert_id();
$_REQUEST["uid"] = $uid;

include "preview.php";
?>
