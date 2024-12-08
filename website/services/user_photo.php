<?php
$showDebugInfo = false;
if ( !empty($_GET['debug']) )
	$showDebugInfo = true;

if ( ! $showDebugInfo ) 
   error_reporting(0);

include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$photo = get_file_variable($_GET['photoid']);
if (empty($photo)) {
	$photo_arr = get_api_value('user_get_photo_by_photoid', '', array('photoid' => $_GET['photoid']), '', $user_account);
	if (!empty($photo_arr['photo_data'])) {
		$photo = base64_decode($photo_arr['photo_data']);
		update_file_variable($_GET['photoid'], $photo);
	}
	else { 
		$photo = file_get_contents(DIR_WS_IMAGES.'no_photo.jpg');
	}
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type:image/jpeg");
echo $photo;

/*
if ( empty($_GET['userid']) ) {
	header('Location: '.'/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60boy.png');
	exit;
}

$_GET['userid'] = tep_sanitize_string($_GET['userid']);

$image_url = '/'.DIR_WS_WEBSITE_PHOTOS_DIR.$_GET['userid'].'.jpg';
if ( file_exists($image_url) ) {
	header('Location: '.$image_url);
	exit;
}
$user = new User();
$user->userid = $_GET['userid'];
if ( $user->read_data(false) )
	$image_url = $user->get_photo();

header('Location: '.$image_url);
*/
?>
