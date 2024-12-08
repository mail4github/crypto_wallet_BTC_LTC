<?php
$showDebugMessages = false;
if ( !empty($_GET['debug']) )
	$showDebugMessages = true;
if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);
require('../includes/config.inc.php');

$curpage = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '/') + 1);
if ( strpos($curpage, '?') > 0 )
	$curpage = substr($curpage, 0, strpos($curpage, '?'));

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && defined('CUSTOM_CODE_PREFIX') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.$curpage) ) {
	header('Location: /'.CUSTOM_CODE_PREFIX.$curpage.(!empty($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''));
}
else
	header('Location: /');
?>
