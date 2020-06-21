<?php
require_once 'db_conn.php';
if ($link == false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

if (!isset($_GET["id"])) {
    die("ERROR: No id set.");
}

// Attempt select query execution
$imageid = mysqli_real_escape_string($link, $_GET["id"]);
$sql = "SELECT * FROM `image` where id = $imageid and active = 1";
$images_result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($images_result);

// Close connection
mysqli_close($link);

// get the fitting url
$uri = false;
if ($row['uri_preview'] == null || $row['uri_preview'] == "") {
    $uri = $row['uri'];
} else {
    $uri = $row['uri_preview'];
}

if ($row['cid_required'] != 0) {
    $uri = str_replace("\$cid", "945223", $uri);
    if (intval(date("I")) == 1) {
        $uri = str_replace("\$time", "sommer", $uri);
    }

    if (intval(date("I")) == 0) {
        $uri = str_replace("\$time", "winter", $uri);
    }

}

$size = false;
try {
    //error_reporting(0);
    $size = getimagesize($uri);

} catch (\Throwable $th) {
    $size = false;
}

if ($size) {
    $mime = $size['mime'];
    if (strpos($mime, "gif") !== false) {
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
}
mysqli_free_result($images_result);
