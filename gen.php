<?php
$log = date("Y-m-d-H-i-s") . ": ";

// get the
$cid = "";
if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];
}

$log .= "vid=${cid}, ";
$images = explode("_", substr($_GET['img'], 1));

$random = mt_rand(0, sizeof($images) - 1);

$log .= "rand=${random}, ";

require_once 'db_conn.php';

if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Attempt select query execution
$imageid = mysqli_real_escape_string($link, $images[$random]);
$sql = "SELECT `cid_required`, `uri` FROM image where id = $imageid and active = 1";
$images_result = mysqli_query($link, $sql);

// Close connection
mysqli_close($link);
$row = false;
$uri = false;
if (mysqli_num_rows($images_result) > 0) {
    $row = mysqli_fetch_array($images_result);
    if ($row['cid_required'] != 0 and $cid == "") {
        $uri = "assets/img/error_cid.png";
        $log .= "ERROR->cid_required, ";
    } else {
        $uri = str_replace("\$cid", urlencode($cid), $row['uri']);
        if (intval(date("I")) == 1) {
            $uri = str_replace("\$time", "sommer", $uri);
        }

        if (intval(date("I")) == 0) {
            $uri = str_replace("\$time", "winter", $uri);
        }

        $log .= "uri=${uri}, ";
    }
} else {
    $uri = "assets/img/error_code.png";
    $log .= "ERROR->SQL_no_result, ";
}

$size = false;
try {
    error_reporting(0);
    $size = getimagesize($uri);

} catch (\Throwable $th) {
    $size = false;
}

if ($size) {
    if (strpos($size['mime'], "gif") !== false) {
        $mime = $size['mime'];
        header("Content-type: " . $mime);
        readfile($uri);
    } else {
        $image = imagecreatefromstring(file_get_contents($uri));
        // Set a maximum height and width
        $width = 400;
        $height = 80;
        // Get new dimensions
        $width_orig = intval($size[0]);
        $height_orig = intval($size[1]);
        $ratio_orig = $width_orig / $height_orig;
        if ($width / $height > $ratio_orig) {
            $width = $height * $ratio_orig;
        } else {
            $height = $width / $ratio_orig;
        }
        // Resample
        $image_p = imagecreatetruecolor($width, $height);
        imagealphablending($image_p, false);
        imagesavealpha($image_p, true);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        // Content type
        header('Content-Type: image/png');
        imagepng($image_p);
    }
} else {
    header("Content-type: image/png");
    readfile("assets/img/error_external.png");
    $log .= "ERROR->external_not_reachable, ";
}

mysqli_free_result($images_result);

//write the log
$log .= "\n";
file_put_contents('./logs/gen' . date("Y-m") . '.log', $log, FILE_APPEND);
