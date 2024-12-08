<?php
/*
include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');

$url = 'http://api1.'.SITE_SHORTDOMAIN.'/api/synchro_db/renew_table_row/'.md5($_GET['table_name']).'/'.$_GET['row_number'].'/'.$_GET['number_of_rows'].'/'.urlencode($_GET['modified']).'/'.urlencode($_GET['websiteid']).'/'.urlencode($_GET['table_counter']).'/'.urlencode($_GET['counter']).'/'.base64_encode($_GET['website_url']);
$post_params = '';
$s = do_post_request($url, $post_params);
if ( !empty($s) ) {
	$res_arr = json_decode($s, true);
	if ( $res_arr['success'] && is_integer(strpos($res_arr['values']['data'], '<end_of_row>')) ) {
		echo $res_arr['values']['data'];
		exit;
	}
}
*/
echo 'Error: no data';
?>