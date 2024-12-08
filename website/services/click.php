<?php
include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');

$showDebugInfo = defined('DEBUG_MODE');
if ( $showDebugInfo )
	error_reporting(E_ALL);

function end_this_script($bannerId, $user_id = '', $unique_click = true)
{
	global $showDebugInfo;
	save_click($bannerId, $user_id);
	exit();
}
$destUrl = '';
$bannerId = trim(@$_GET['a_bid'], '"');
$bannerId = preg_replace("/ \+/", '', trim($bannerId));
$bannerId = preg_replace('/\'/', '', $bannerId);
$bannerId = preg_replace('/"/', '', $bannerId);

$bannerId = preg_replace("/[<>]/", '', $bannerId);
$bannerId = substr($bannerId, 0, 40);
if ( empty($bannerId) || !isInteger($bannerId) )
	$bannerId = 0;//AFFILIATES_BANNERID;
if ( $showDebugInfo ) echo '$bannerId: '.$bannerId.'<br>';

$user_id = @$_GET['a_aid'];
$user_id = preg_replace("/ \+/", '', trim($user_id));
$user_id = preg_replace('/\'/', '', $user_id);
$user_id = preg_replace('/"/', '', $user_id);
$user_id = preg_replace("/[<>]/", '', $user_id);
$user_id = intval($user_id);

$main_page_html = SITE_DOMAIN;
if ( defined('AFFILIATE_URL') )
	$main_page_html = AFFILIATE_URL;
if ( $main_page_html[strlen($main_page_html) - 1] != '/' && $main_page_html[strlen($main_page_html) - 1] != '\\' )
	$main_page_html = $main_page_html.'/';
$main_page_for_deleted = $main_page_html.'index_signup_d.html';

if ( !empty($_GET['signup']) )
	$main_page_html = $main_page_html.'signup.php';
if ( !empty($user_id) )
	$main_page_html = $main_page_html.'?a_aid='.$user_id;

$main_page_html_to_top_frame = $main_page_html;

if ( (empty($user_id) ) && empty($_GET['tst_cl']) ) {
	if ( $showDebugInfo ) {
		echo 'empty userid<br>';
	}
	else {
		header('Location: '.$main_page_html);
		end_this_script($bannerId, $user_id, false);
	}
}

if ( !empty($_GET['signup']) )
	$destUrl = $main_page_html_to_top_frame;

$countrycode = '';
$banner_destinationurl = '';

if ( $showDebugInfo ) echo 'banner destinationurl: "'.$banner_destinationurl.'"<br>';

if ( empty($destUrl) ) {
	$destUrl = $banner_destinationurl;
	if ( empty($destUrl) )
		$destUrl = $main_page_html;
}

if ( empty($destUrl) )
	$destUrl = $main_page_html;

if ( !$showDebugInfo )
	@header('Location: '.$destUrl, true, 301);

end_this_script($bannerId, $user_id);
?>
