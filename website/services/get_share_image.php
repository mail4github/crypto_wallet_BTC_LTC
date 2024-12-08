<?php
$showDebugInfo = false;
if ( !empty($_GET['debug']) )
	$showDebugInfo = true;

if ( ! $showDebugInfo ) 
   error_reporting(0);

include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
require_once(DIR_WS_CLASSES.'stock.class.php');

$_GET['stockid'] = tep_sanitize_string($_GET['stockid']);
$_GET['stockid'] = substr($_GET['stockid'], 0, 5);
if ( empty($_GET['stockid']) )
	$_GET['stockid'] = 'GPA';

$image_url = '/';
$stock = new Stock();
if ( $stock->read_data($_GET['stockid']) ) {
	$image_url = $stock->get_share_image();
}
header('Location: '.$image_url);
?>
