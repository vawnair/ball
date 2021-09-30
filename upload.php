<?php
require 'protected/connection.php';
require 'protected/functions.php';

$conn = get_connection();

$allowedTypes = array('image/png', 'image/jpeg', 'image/gif', 'video/webm', 'video/mp4', 'video/mov');


if (!isset($_POST['upload_key'])) {
    $array = array();

    $array['ImageUrl'] = "";
    $array['error'] = "401 Unauthorized";
    echo json_encode($array, JSON_PRETTY_PRINT);

    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if (!(filesize($_FILES['file']['tmp_name']) > 0 && in_array($_FILES['file']['type'], $allowedTypes))) {
    $array = array();

    $array['ImageUrl'] = "";
    $array['error'] = "415 File type is not Supported";
    echo json_encode($array, JSON_PRETTY_PRINT);

    header("HTTP/1.1 415 Unsupported Media Type");
    exit;
}

if ($_FILES['file']['error'] > 0) {
    $array = array();

    $array['ImageUrl'] = "";
    $array['error'] = "500 Internal Server Error";
    echo json_encode($array, JSON_PRETTY_PRINT);

    header("HTTP/1.1 500 Internal Server Error");
    exit;
}

$account_query = $conn->query('SELECT * FROM users WHERE uploadKey=?', [$_POST['upload_key']]);
$account_data = $account_query->fetch_assoc();

error_reporting(0);

mkdir("uploads/" . $account_data['username']);

$dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $account_data['username'] . '/';


addUpload($_POST['upload_key'], $_FILES['file']['type'], $_FILES['file']['tmp_name']);

function generateNewHash($type)
{
    $an = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $str = '';

    for ($i = 0; $i < 5; $i++) {
        $str .= substr($an, rand(0, strlen($an) - 1), 1);
    }

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/uploads/$str.$type")) {
        return $str;
    } else {
        return generateNewHash($type);
    }
}

function addUpload($upload_key, $mimeType, $tempName)
{

    global $conn;
    static $tmpName;

    if (!empty($upload_key)) {
        $key_check = $conn->query("SELECT * FROM users WHERE uploadKey='" . sqlthing($upload_key) . "'");
        if ($key_check->num_rows != 0) {
            while ($row = $key_check->fetch_assoc()) {
                $get_domain = $conn->query("SELECT * FROM domainSelector WHERE username='" . sqlthing($row['username']) . "'");
                if ($get_domain->num_rows != 0) {
                    while ($domain_row = $get_domain->fetch_assoc()) {
                        global $dir;
                        $mimeTypeArray = explode('/', $mimeType);
                        $type = $mimeTypeArray[1];
                        $hash = generateNewHash($type);
                        move_uploaded_file($tempName, $dir . "$hash.$type");

                        $tmpName = $hash;

                        $conn->query("INSERT INTO `uploads`(`uploadedBy`, `uploadName`, `fromUploadKey`, `fromIP`, `mimeType`) VALUES ('" . sqlthing($row['username']) . "','" . $tmpName . "','" . $upload_key . "','" . sqlthing(getip()) . "', '" . $type . "')");

                        if ($domain_row['subdomain'] == null) {
                            $array = array();

                            $array['ImageUrl'] = "https://" . $domain_row['domain'] . "/" . $tmpName;
                            $array['error'] = "";

                            echo json_encode($array, JSON_PRETTY_PRINT);

                            die();
                        } elseif ($domain_row['subdomain'] != null) {
                            $array = array();

                            $array['ImageUrl'] = "https://" . $domain_row['subdomain'] . "." . $domain_row['domain'] . "/" . $tmpName;
                            $array['error'] = "";

                            echo json_encode($array, JSON_PRETTY_PRINT);
                        }
                        return true;
                    }
                } elseif ($get_domain->num_rows == 0) {
                    global $dir;
                    $mimeTypeArray = explode('/', $mimeType);
                    $type = $mimeTypeArray[1];
                    $hash = generateNewHash($type);
                    move_uploaded_file($tempName, $dir . "$hash.$type");

                    $tmpName = $hash;

                    $conn->query("INSERT INTO `uploads`(`uploadedBy`, `uploadName`, `fromUploadKey`, `fromIP`, `mimeType`) VALUES ('" . sqlthing($row['username']) . "','" . $tmpName . "','" . $upload_key . "','" . sqlthing(getip()) . "', '" . $type . "')");

                    $array = array();

                    $array['ImageUrl'] = "https://" . $_SERVER['SERVER_NAME'] . "/" . $tmpName;
                    $array['error'] = "";

                    echo json_encode($array, JSON_PRETTY_PRINT);

                    return true;
                }
            }
        } else {
            $array = array();

            $array['ImageUrl'] = "";
            $array['error'] = "Invalid upload_key, or it doesn't exist";
            return false;
        }
    } else {
        $array = array();

        $array['ImageUrl'] = "";
        $array['error'] = "upload_key is empty";
        return false;
    }
}
