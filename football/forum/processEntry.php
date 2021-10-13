<?php
require_once 'utils/start.php';

if (!$isin) {
    header('Location: blogentry.php');
    exit;
}

require 'DataObjects/Forum.php';

$post = new DataObjects_Forum;

$subject = stripslashes(mysqli_real_escape_string($conn, $_POST['subject']));
$body = stripslashes(mysqli_real_escape_string($conn, str_replace("\r\n", '', $_POST['body'])));

print "$subject <br/>";
print_r($_REQUEST);

$post->settitle($subject);
$post->setbody($body);
$post->setuserid($usernum);
$post->setcreateTime(date('Y-n-d G:i:s'));
$id = $post->insert();
print $id;

//print_r($post);

header('Location: comments.php');
exit;
