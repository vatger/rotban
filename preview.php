<?php
require_once('db_conn.php');
if ($link == false)
    die("ERROR: Could not connect. " . mysqli_connect_error());
if(!isset($_GET["id"]))
    die("ERROR: No id set.");


// Attempt select query execution
$imageid = mysqli_real_escape_string($link, $_GET["id"]);
$sql = "SELECT * FROM `image` where id = $imageid and active = 1";
$images_result = mysqli_query($link, $sql);
$image = mysqli_fetch_assoc($images_result);

// Close connection
mysqli_close($link);

// get the fitting url
if ($image['uri_preview'] == NULL or $image['uri_preview'] == "") 
    $uri = $image['uri'];
else 
    $uri = $image['uri_preview'];
if ($image['cid_required'] != 0){
    $uri = str_replace("\$cid", "", $uri) ;
    if(intval(date("I")) == 1)
        $uri = str_replace("\$time", "sommer", $uri);
    if(intval(date("I")) == 0)
        $uri = str_replace("\$time", "winter", $uri);
}
$size = false;
try {
    error_reporting(0);
    $size = getimagesize($uri);
} catch (\Throwable $th) {
    $size = false;
}
if($size){
    $mime = $size['mime'];
    header("Content-type: " . $mime);
    readfile($uri);
}else{
    header("Content-type: image/png");
    readfile("assets/img/error_external.png");
}
mysqli_free_result($images_result);