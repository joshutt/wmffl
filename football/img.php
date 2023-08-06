<?php
require_once 'utils/start.php';

error_log('In process');
$url = $_REQUEST['url'];
$size = $_REQUEST['size'];
error_log("Url: $url");
error_log("Size: $size");

$query = 'SELECT fullImage FROM images WHERE url = ?';
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $url);
$stmt->execute();
$result = $stmt->get_result();
//$result = $conn->execute_query($query, [$id]);


$row = $result->fetch_array();
//$row = $result[0];
$data = $row['fullImage'];
$data = utf8_decode($data);
$image = imagecreatefromstring($data);

header('Content-Type: image/jpeg');
imagejpeg($image);
