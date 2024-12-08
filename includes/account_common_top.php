<?php
$script_name_arr = get_page_name();
if ( !isset($dont_check_login) || !$dont_check_login ) {
	if ( !$user_account->is_loggedin() ) {
		if ( !is_integer(strpos(GUEST_PERMISSIONS, "`".$script_name_arr[0]."`")) && !is_integer(strpos(GUEST_PERMISSIONS, "`".$script_name_arr[1]."`")) ) {
			header('Location: /login.php');
			exit;
		}
	}
	else {
		if ( empty($user_account->has_permission_to_page) ) {
			header('Location: /');
			exit;
		}
	}
}

// if such page exists in the folder "tmp_custom_code" then perform PHP code from this folder
if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.$script_name_arr[0]) ) {
	require(DIR_WS_TEMP_CUSTOM_CODE.$script_name_arr[0]);
	exit;
}

?>