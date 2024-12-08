<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

// save a language id in cookie
if (!empty(@$_POST['force_language']) && strlen($_POST['force_language']) < 4) {
	$_COOKIE['googtrans'] = '/auto/'.$_POST['force_language'];
	setcookie('googtrans', $_COOKIE['googtrans']);
}

include_once('config.inc.php');

if ( (!defined('DO_NOT_REDIRECT_APPLICATION') || !DO_NOT_REDIRECT_APPLICATION) && is_integer(strpos($_SERVER['HTTP_HOST'], 'www.')) ) {
	header('Location: '.SITE_DOMAIN.(!empty($_SERVER['REQUEST_URI']) ? substr($_SERVER['REQUEST_URI'], 1) : ''));
	exit;
}

if ( (!defined('DO_NOT_REDIRECT_APPLICATION') || !DO_NOT_REDIRECT_APPLICATION) && HTTP_PREFIX == 'https://' && $_SERVER['REQUEST_SCHEME'] != 'https' && @$_SERVER['HTTPS'] != 'on' ) {
	// reload page using https
	if (count($_POST) > 0) {
		echo '<form action="'.SITE_DOMAIN.(!empty($_SERVER['REQUEST_URI'])?substr($_SERVER['REQUEST_URI'], 1):'').'" method="post" name="frm">';
		foreach ($_POST as $a => $b) {
			echo '<input type="hidden" name="'.$a.'" value="'.$b.'">';
		}
		echo '</form><script language="JavaScript">document.frm.submit();</script>
		';
	}
	else {
		$language = strtolower(end(explode(".", $_SERVER['HTTP_HOST'])));
		if ( $language == 'com' || !defined('SET_LANGUAGE_AUTOMATICALLY') || SET_LANGUAGE_AUTOMATICALLY != 'true' ) {
			header('Location: '.SITE_DOMAIN.(!empty($_SERVER['REQUEST_URI'])?substr($_SERVER['REQUEST_URI'], 1):''));
		}
		else {
			// force to translate to the domain language 
			echo '<form action="'.SITE_DOMAIN.(!empty($_SERVER['REQUEST_URI'])?substr($_SERVER['REQUEST_URI'], 1):'').'" method="post" name="frm"><input type="hidden" name="force_language" value="'.$language.'"></form><script language="JavaScript">document.frm.submit();</script>';
		}
	}
	exit;
}

if ( !function_exists('tep_sanitize_string') ) include_once(DIR_COMMON_PHP.'general.php');

session_start();
$time_start = microtime(true);
$additionsl_header_meta = '';

protect_from_DDOS();

include_once(DIR_WS_CLASSES.'user.class.php');

$curpage = substr($_SERVER['SCRIPT_NAME'], strpos($_SERVER['SCRIPT_NAME'], '/') + 1);
$parent_page = $curpage;
if ( !empty($_GET['site']) )
	setcookie('come_from_site', $_GET['site'], time() + 3600*24*30, '/', '.'.SITE_SHORTDOMAIN);

if ( !empty($_GET['logged_userid']) ) {
	setcookie('userid', $_GET['logged_userid']);
	setcookie('cookie_vars_time', 0);
	$_COOKIE['cookie_vars_time'] = 0;
}
if ( !empty($_GET['logged_password']) ) {
	setcookie('password', $_GET['logged_password']);
}
global $user_account;

$dont_show_menu = false;
$user_account = new User();

if ( defined('SITE_TITLE') )
	$page_title = SITE_TITLE;
else
	$page_title = SITE_NAME;

if (defined('SITE_DESCRIPTION'))
	$page_description = SITE_DESCRIPTION;
else
	$page_description = '';

if ( is_integer(strpos($page_description, '{$read_from_file')) ) {
	$code = get_text_between_tags($page_description, '{$read_from_file', '}');
	$file_name = get_text_between_tags($code, '$file="', '"');
	$text = translate_str(file_get_contents(DIR_WS_TEMP_CUSTOM_CODE.$file_name));
	$id = get_text_between_tags($code, '$id="', '"');
	$text = get_text_between_tags($text, $id, '<');
	$text = get_text_between_tags($text, '>');
	if ($text[0] == '"' || $text[0] == "'")
		$text = substr($text, 1);

	$page_name = get_text_between_tags($code, '$page_name="', '"');
	
	$language = get_selected_language();
	if (!empty($language)) {
		$script_name = preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $page_name)));
		$res = replace_with_translated_text($text, $language, $script_name, '', '');
		if ($res !== false)
			$text = $res;
	}
	delete_text_between_tags($page_description, '{$read_from_file', '}', false, $text);
}

if (defined('SITE_KEYWORDS'))
	$page_keywords = SITE_KEYWORDS;
else
	$page_keywords = '';

$local_phone = '';
if (defined('SUPPORT_PHONE')) {
	$local_phone = parse_value_by_locale(SUPPORT_PHONE);
}
define('LOCAL_PHONE_HEX', bin2hex($local_phone));

$local_address = '';
if (defined('SITE_ADDRESS')) {
	$local_address = parse_value_by_locale(SITE_ADDRESS);
}
define('LOCAL_SITE_ADDRESS', $local_address);

if ( $user_account->is_loggedin() ) {
	$user_account->userid = $_COOKIE['userid'];

	$request_uri = tep_sanitize_string($_SERVER['REQUEST_URI'], 128);
	if ($request_uri[0] == '/')
		$request_uri = substr($request_uri, 1);
	if ( is_integer(strpos($request_uri, '?')) ) $request_uri = get_text_between_tags($request_uri, '', '?');
	if ( is_integer(strpos($request_uri, '&')) ) $request_uri = get_text_between_tags($request_uri, '', '&');
	if ( is_integer(strpos($request_uri, '/')) ) $request_uri = get_text_between_tags($request_uri, '', '/');
	$script_name_arr = get_page_name();
	$user_account->read_data(true, (isset($get_list_of_common_params) ? $get_list_of_common_params : false), $script_name_arr[0]);
	if ( !$user_account->session_active ) {
		$user_account->logout();
		header('Location: /login');
		exit;
	}
}

if ( is_file_variable_expired('ranks', 30) /*|| defined('DEBUG_MODE')*/ ) {
	$s = get_api_value('config_ranks', '', '', '', null, false, 1);
	if ( $s !== false && !empty($s) ) {
		update_file_variable('ranks', json_encode($s));
	}
}
if ( is_file_variable_expired('payout_options', 30) /*|| defined('DEBUG_MODE')*/ ) {
	$s = get_api_value('config_payout_options', '', '', '', null, false, 1);
	if ( $s !== false && !empty($s) ) {
		update_file_variable('payout_options', json_encode($s));
	}
}
if ( is_file_variable_expired('currencies', 30) /*|| defined('DEBUG_MODE')*/ ) {
	$s = get_api_value('config_currencies', '', '', '', null, false, 1);
	if ( $s !== false && !empty($s) ) {
		update_file_variable('currencies', json_encode($s));
	}
}
if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'add_application_top.php') )
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'add_application_top.php');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && defined('CUSTOM_CODE_PREFIX') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.$curpage) ) {
	header('Location: /'.CUSTOM_CODE_PREFIX.$curpage.(!empty($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:''));
	exit;
}
?>