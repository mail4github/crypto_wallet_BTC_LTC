<?php

error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

require_once('../includes/application_top.php');

if (defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true' && empty(@$_COOKIE['googtrans'])) {
	// translate page according the coutry code
	$country_code = getCountryCodefromIP();
	$_COOKIE['googtrans'] = '/auto/'.strtolower($country_code); 
	setcookie('googtrans', $_COOKIE['googtrans']);
}

function search_images($in_str, &$images_arr)
{
	if ( preg_match_all("/(\"\/".str_replace('/', '\/', DIR_WS_TEMP_IMAGES_NAME)."(.*?)\")/i", $in_str, $arr ) ) {  
		foreach ($arr[2] as $key => $image) {
			if ( is_array($images_arr) && !array_search($image, $images_arr) )
				$images_arr[] = $image;
		}
	}
	if ( preg_match_all("/(\(\/".str_replace('/', '\/', DIR_WS_TEMP_IMAGES_NAME)."(.*?)\))/i", $in_str, $arr ) ) {  
		foreach ($arr[2] as $key => $image) {
			if ( is_array($images_arr) && !array_search($image, $images_arr) )
				$images_arr[] = $image;
		}
	}
}

if ( (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) && isset($_GET['a_aid']) ) {
	$disabled_userids_from_file = get_file_variable('disabled_userids');
	$disabled_userid_arr = explode('|', $disabled_userids_from_file);
	
	foreach ($disabled_userid_arr as $userid) {
		if ( is_integer(strpos($userid, '_'.$_GET['a_aid'].':')) ) {
			header('Location: /index.html');
			exit;
		}
	}
}

if ( $user_account->is_loggedin() ) {
	header('Location: /acc_main.php');
	exit;
}

if ( (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) && (!defined('SET_LANGUAGE_AUTOMATICALLY') || SET_LANGUAGE_AUTOMATICALLY != 'true') && !is_file_variable_expired('index_page') && !defined('DEBUG_MODE') ) {
	echo get_file_variable('index_page');
	exit;
}

ob_start();
require(DIR_WS_INCLUDES.'header.php');
?>
<script src="/javascript/landing_page_common.js" type="text/javascript"></script>
<SCRIPT LANGUAGE="JavaScript">
<?php 
require_once(DIR_WS_INCLUDES.'$$$widget_track_user_id.php');
?>
</script>
<?php 
include(DIR_WS_INCLUDES.'first_page_body.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

<script type="text/javascript">

var global_language = "";
var language_already_changed = false;
var number_of_attempts = 0;
var max_number_of_attempts = 20;

function change_language_automatically() {
	try	{
		var selectField = document.querySelector("#google_translate_element select");
		if (!selectField || !selectField.children || selectField.children.length < 1 || selectField.offsetParent == null) {
			throw "empty list";
		}
		var google_language = selectField.value;
		var already_selected_language = get_cookie("googtrans");
		var language_found = false;
		if ((already_selected_language.length == 0 || number_of_attempts > max_number_of_attempts - 2) && google_language.length == 0) {
			for (var i = 0; i < selectField.children.length; i++) {
				var option = selectField.children[i];
				// find desired langauge and change the former language of the hidden selection-field 
				if ( option.value == global_language ) {
					selectField.selectedIndex = i;
					// trigger change event afterwards to make google-lib translate this side
					selectField.dispatchEvent(new Event('change'));
					language_found = true;
					break;
				}
			}
		}
		if (!language_found && number_of_attempts < max_number_of_attempts && google_language.length == 0) {
			number_of_attempts++;
			throw "not found";
		}
	}
	catch(error){
		setTimeout(function() { 
			change_language_automatically();
		}, 200);
	}
}

$( document ).ready(function() {
	<?php if ( defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY  == 'true' ) { ?>
		
		try	{
			var language = window.navigator.userLanguage || window.navigator.language;
			if (typeof language == "string" && language.length > 0) {
				global_language = language.substring(0, language.indexOf("-") >= 0 ? language.indexOf("-") : language.length );
				if ( global_language.length > 0 && global_language != "en" )
					change_language_automatically();
			}
		}
		catch(error){}
	<?php } ?>
});

</script>

</body>
</html>
<?php
$page_body = '';
if ( (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) && (!defined('SET_LANGUAGE_AUTOMATICALLY') || SET_LANGUAGE_AUTOMATICALLY != 'true') ) {
	$page_body = ob_get_contents();
	if ( $page_body && !defined('DEBUG_MODE') ) 
		update_file_variable('index_page', $page_body);
}
if ($page_body && !empty($page_body)) {
	// restore the default page files from DB
	search_images($page_body, $images_arr);
	foreach ($images_arr as $key => $image) {
		if ( !file_exists(DIR_WS_TEMP_IMAGES.$image) ) {
			$data = get_api_value('restore_file_from_db', '', array('file_name' => $image));
			$data['file_body'] = base64_decode($data['file_body']);
			if ( $data && $data['file_name'] == $image && !empty($data['file_body']) ) {
				file_put_contents(DIR_WS_TEMP_IMAGES.$image, $data['file_body']);
				break;
			}
		}
	}
}
echo "<!-- country_code: $country_code, cookie googtrans: ".$_COOKIE['googtrans'].", cookie language_from_ip: ".$_COOKIE['language_from_ip']." -->";
?>