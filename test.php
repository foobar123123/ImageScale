<?php
require_once("classes/ImageScale.php");

// dispatcher params
$source_dir = "original/";					// Source Directory
$source_filename = "image_original.jpeg";	// Sourcer Filename
$destination_dir = "scaled/";				// Destination Directory
$destination_height = 100;					// Destination Height
$destination_width = 200;					// Destination Width

$handler = new ImageScale();				// create ImageScale obejectinstance

var_dump($handler);
// start the dispatcher
$ret = $handler->startDispatcher($source_dir, $source_filename, $destination_dir, $destination_height, $destination_width);
var_dump($ret);
var_dump($handler);

?>
