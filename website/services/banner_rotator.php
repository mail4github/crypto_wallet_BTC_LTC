<?php

include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
?>
//<script type="text/javascript">
function inc(filename)
{
	document.write('<script type="text/javascript" src="' + filename + '"></scr' + 'ipt>'); 
}
//</script>

<?php
$banner_id = '';
$user_id = '';

$banner_id = trim(@$_GET['a_bid'], '"');
$bannerId = preg_replace("/ \+/", '', trim($bannerId));
$bannerId = preg_replace('/\'/', '', $bannerId);
$bannerId = preg_replace('/"/', '', $bannerId);
$banner_id = preg_replace("/[<>]/", '', $banner_id);
$banner_id = substr($banner_id, 0, 8);

$user_id = trim($_GET['a_aid'], '"><');
$user_id = preg_replace("/ \+/", '', trim($user_id));
$user_id = preg_replace('/\'/', '', $user_id);
$user_id = preg_replace('/"/', '', $user_id);
$user_id = preg_replace("/[<>]/", '', $user_id);
$user_id = substr($user_id, 0, 40);

if ( empty($user_id) ) {
	$s = $_SERVER['QUERY_STRING'];
	$_SERVER['QUERY_STRING'] = '';
	foreach($_GET as $key => $value){
		$value = '';
	}
	
	foreach($_REQUEST as $key => $value){
		$value = '';
	}
	
	$i = stripos($s, '/aft/');
	if ( is_integer($i) ) {
		$s = substr($s, $i + 5);
		$i = stripos($s, '/');
		$user_id = trim( substr($s, 0, $i), '"><' );
		$s = substr($s, $i + 1);
		$i = stripos($s, '.');
		$banner_id = trim( substr($s, 0, $i), '"><' );
	}
}

echo '
var banner_id="'.$banner_id.'";
var user_id="'.$user_id.'";
var country_code = "";
';
//if ( get_number_of_sql_connections() < MIN_NUMB_OF_SQL_CONNECTIONS * 1 ) {
	//if ( tep_db_connect() ) {
		//$country_code = getCountryCodefromIP($_SERVER['REMOTE_ADDR']);
		//echo 'country_code = "'.$country_code.'";'."\r\n";
	//}
//}
if ( file_exists(DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_NAME.'banner_data.js') ) {
	echo '
	inc("'.SITE_DOMAIN.DIR_WS_TEMP_NAME.'banner_data.js");
	inc("'.SITE_DOMAIN.'javascript/banner.js");
	';
}
?>
