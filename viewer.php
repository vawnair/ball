<?php
if (!isset($_GET['file'])) {
    include_once __DIR__ . '/index.php';
    die();
}

require 'protected/connection.php';
require 'protected/functions.php';


$conn = get_connection();

$types = array('png' => 'image/png', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'mp4' => 'video/mp4');

$file = $_GET['file'];

$result = $conn->query("SELECT * FROM `uploads` WHERE `uploadName`='$file'");
$row = $result->fetch_assoc();

if (!isset($_GET['file'])) {
    include_once __DIR__ . '/index.php';
    die();
}


$type = $row['mimeType'];


$filelocation = __DIR__ . "/uploads/" . $row['uploadedBy'] . "." . $row['mimeType'];


require_once __DIR__ . '/protected/viewer.php';
