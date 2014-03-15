<?php
if (! isset ( $_REQUEST ['i'] ) || ! isset ( $_REQUEST ['w'] ) || ! isset ( $_REQUEST ['h'] )) {
	header ( "HTTP/1.0 404 Not Found" );
	die ( "Invalid parameters" );
}
$source_path = $_REQUEST ['i'];
$width = $_REQUEST ['w'];
$height = $_REQUEST ['h'];
if (! file_exists ( $source_path )) {
	header ( "HTTP/1.0 404 Not Found" );
	die ( "Image not found" );
}
try {
	list ( $source_width, $source_height, $source_type ) = getimagesize ( $source_path );
	switch ($source_type) {
		case IMAGETYPE_GIF :
			$source_gdim = imagecreatefromgif ( $source_path );
			break;
		case IMAGETYPE_JPEG :
			$source_gdim = imagecreatefromjpeg ( $source_path );
			break;
		case IMAGETYPE_PNG :
			$source_gdim = imagecreatefrompng ( $source_path );
			break;
		default :
			header ( "HTTP/1.0 404 Not Found" );
			die ( "Unsupported image" );
			break;
	}
} catch ( Exception $e ) {
	header ( "HTTP/1.0 404 Not Found" );
	die ( "File not image? " . $e );
}
$source_aspect_ratio = $source_width / $source_height;
$desired_aspect_ratio = $width / $height;

if ($source_aspect_ratio > $desired_aspect_ratio) {
	/*
	 * Triggered when source image is wider
	 */
	$temp_height = $height;
	$temp_width = ( int ) ($height * $source_aspect_ratio);
} else {
	/*
	 * Triggered otherwise (i.e. source image is similar or taller)
	 */
	$temp_width = $width;
	$temp_height = ( int ) ($width / $source_aspect_ratio);
}

/*
 * Resize the image into a temporary GD image
 */

$temp_gdim = imagecreatetruecolor ( $temp_width, $temp_height );
imagecopyresampled ( $temp_gdim, $source_gdim, 0, 0, 0, 0, $temp_width, $temp_height, $source_width, $source_height );

/*
 * Copy cropped region from temporary image into the desired GD image
 */

$x0 = ($temp_width - $width) / 2;
$y0 = ($temp_height - $height) / 2;
$desired_gdim = imagecreatetruecolor ( $width, $height );
imagecopy ( $desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $width, $height );

/*
 * Render the image and save it
 */
switch ($source_type) {
	case IMAGETYPE_GIF :
		header ( 'Content-type: image/gif' );
		imagegif ( $desired_gdim );
		imagegif ( $desired_gdim, substr ( $source_path, 0, strlen ( $source_path ) - 4 ) . "_thumb_" . $width . "x" . $height . ".gif" );
		break;
	case IMAGETYPE_JPEG :
		header ( 'Content-type: image/jpeg' );
		imagejpeg ( $desired_gdim );
		imagegif ( $desired_gdim, substr ( $source_path, 0, strlen ( $source_path ) - 4 ) . "_thumb_" . $width . "x" . $height . ".jpg" );
		break;
	case IMAGETYPE_PNG :
		header ( 'Content-type: image/png' );
		imagepng ( $desired_gdim );
		imagegif ( $desired_gdim, substr ( $source_path, 0, strlen ( $source_path ) - 4 ) . "_thumb_" . $width . "x" . $height . ".png" );
		break;
}

imagedestroy ( $desired_gdim );