<?php
include '../check.inc.php';
//$path = "http://static.nfl.com/static/content/public/image/getty/2007/75557821TL014_NEW_ORLEANS_S_20070906174925_gallery_600.jpg";
//$path = "http://static.nfl.com/static/content/public/image/getty/2007/75557828DP015_ATLANTA_FALCO_20070909141521_gallery_600.jpg";

$path = $_REQUEST["path"];
$newName = $_REQUEST["newName"];
$newDir = $_REQUEST["newDir"];
$maxSize = $_REQUEST["maxSize"];

$rootLoc = "/home/wmffl/public_html";

print "$path<br/>";
//$path = $rootLoc.$path;
$image = imagecreatefromjpeg($path);
print "$image<br/>";
$width = imagesx($image);
$height = imagesy($image);

print "Width: $width  Height: $height <br/>";

$percent = 1.0;
    
if ($width >= $height && $width > $maxSize) {
    $percent = $maxSize / $width;
} elseif ($height > $maxSize) {
    $percent = $maxSize / $height;
}

print "$percent</br>";

$newwidth = $width * $percent;
$newheight = $height * $percent;

// Load
$thumb = imagecreatetruecolor($newwidth, $newheight);
imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

$fullName = "$rootLoc/$newDir/$newName";
imagejpeg($thumb, $fullName);
?>

<img src="http://wmffl.com/<? print "$newDir/$newName"; ?>">
