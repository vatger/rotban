<?php
$log = date("Y-m-d-H-i-s") . ": ";

// get the 
$cid = "";
if(isset($_GET['cid']))
    $cid = $_GET['cid'];
$log.= "vid=${cid}, ";
$images = explode("_", substr($_GET['img'], 1));

$random = mt_rand(0, sizeof($images) - 1);

$log.= "rand=${random}, ";


require_once('db_conn.php');

if ($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Attempt select query execution
$imageid = mysqli_real_escape_string($link, $images[$random]);
$sql = "SELECT `cid_required`, `uri` FROM image where id = $imageid and active = 1";
$images_result = mysqli_query($link, $sql);

// Close connection
mysqli_close($link);

if (mysqli_num_rows($images_result) > 0) {
    while ($row = mysqli_fetch_array($images_result)) {
        if ($row['cid_required'] != 0 AND $cid == "") {
            $uri = "assets/img/error_cid.png";
            $log.= "ERROR->cid_required, ";
        } else {
            $uri = str_replace("\$cid", urlencode($cid), $row['uri']);
            $log.= "uri=${uri}, ";
        }
        break;
    }
} else {
    $uri = "assets/img/error_code.png";
    $log.= "ERROR->SQL_no_result, ";
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
    $log.= "ERROR->external_not_reachable, ";
}

mysqli_free_result($images_result);

//write the log
$log .= "\n";
file_put_contents('./logs/gen'. date("Y-m").'.log', $log, FILE_APPEND);