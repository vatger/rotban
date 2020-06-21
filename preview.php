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
$image = mysqli_fetch_assoc($images_result);

// Close connection
mysqli_close($link);

// get the fitting url
if ($image['uri_preview'] == null or $image['uri_preview'] == "") {
    $uri = $image['uri'];
} else {
    $uri = $image['uri_preview'];
}

if ($image['cid_required'] != 0) {
    $uri = str_replace("\$cid", "", $uri);
    if (intval(date("I")) == 1) {
        $uri = str_replace("\$time", "sommer", $uri);
    }

    if (intval(date("I")) == 0) {
        $uri = str_replace("\$time", "winter", $uri);
    }

}
$image = false;
try {
    error_reporting(0);
    $image = imagecreatefromstring(file_get_contents($uri));
} catch (\Throwable $th) {
    $image = false;
}
if ($image) {
    // Set a maximum height and width
    $width = 400;
    $height = 80;
    // Get new dimensions
    $width_orig = imagesx($image);
    $height_orig = imagesy($image);
    $ratio_orig = $width_orig / $height_orig;
    if ($width / $height > $ratio_orig) {
        $width = $height * $ratio_orig;
    } else {
        $height = $width / $ratio_orig;
    }
    // Resample
    $image_p = imagecreatetruecolor($width, $height);
    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    // Content type
    header('Content-Type: image/png');
    imagepng($image_p);
} else {
    header("Content-type: image/png");
    readfile("assets/img/error_external.png");
}
mysqli_free_result($images_result);
