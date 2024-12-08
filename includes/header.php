<?php
include_once(DIR_WS_INCLUDES.'detect_mobile_browser.php');
global $submenu_max_depth;
$submenu_max_depth = 0;

function print_Menu_subitem($sub_menu_name, $menu_page, $curpage, $highlited, $onclick = '', $user_account)
{
	$res = '';
	if (empty($onclick) && !is_integer(strpos($menu_page, '#')))
		$onclick = '$(\'#wait_sign\').show();';
	
	if ( !empty($menu_page) && !empty($sub_menu_name) && ( !is_menu_item_hidden($menu_page) ) ) {
		if ( $menu_page[0] != '/' && $menu_page[0] != ' ' )
			$menu_page = '/'.$menu_page;
		if ($sub_menu_name == '-')
			$res = $res.'
					<li role="separator" class="divider"></li>';
		else
			$res = $res.'
					<li><a href="'.$menu_page.'" onclick="'.$onclick.'" class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$sub_menu_name.'</a></li>';
	}
	return $res;
}

function count_submenu_max_depth($submenu)
{
	global $submenu_max_depth;
	$numb_of_submenu = 0;
	foreach ( $submenu as $menu )
		if ( !empty($menu['title']) )
			$numb_of_submenu++;

	if ( $numb_of_submenu > $submenu_max_depth )
		$submenu_max_depth = $numb_of_submenu;
}

function print_Menu_item($menu_name, $curpage, $menu_page, $submenu, $onclick = '', $user_account)
{
	$res = '';
	global $mobile_device;
	if ( !$mobile_device && defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION )
		$mobile_device = 1;
	if ( empty($menu_name) )
		return false;
	if ( is_menu_item_hidden($menu_page) )
		return false;
	if ( $menu_page[0] != '/' && $menu_page[0] != ' ' )
		$menu_page = '/'.$menu_page;
	if ( count($submenu) > 1 ) {
		if ( (int)MENU_STYLE == 1 || $mobile_device ) {
			$res = $res.'
			<li class="dropdown" style="">
				<a href="'.($mobile_device?'':$menu_page).'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="height:'.MENU_HEIGHT.'px; padding-top:'.round((MENU_HEIGHT - MENU_FONT_SIZE) / 2.1 ).'px; background-image:'.(check_image_path(MENU_BTN_BACKGROUND_IMAGE)).';" '.($mobile_device?'':'onclick="'.$onclick.'"').'>
					<span class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</span> <span class="caret"></span>
				</a>
				<ul class="dropdown-menu" role="menu">
			';
			foreach( $submenu as $value ) {
				$res = $res.print_Menu_subitem($value['title'], $value['page'], $curpage, $value['highlited'], @$value['onclick'], $user_account);
			}
			$res = $res.'
				</ul>
			</li>';
		}
		else {
			$res = $res.'
			<div class="btn-group">
				<a href="'.($mobile_device?'':$menu_page).'" type="button" class="btn btn-menu" onclick="'.$onclick.'">
					'.$menu_name.'
				</a>
				<button type="button" class="btn btn-menu dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<span class="caret"></span>
					<span class="sr-only"></span>
				</button>
				<ul class="dropdown-menu" role="menu">';
			foreach( $submenu as $value )
				$res = $res.print_Menu_subitem($value['title'], $value['page'], $curpage, $value['highlited'], isset($value['onclick'])?$value['onclick']:'', $user_account);		
			$res = $res.'
				</ul>
			</div>
			';
		}
	}
	else {
		if ( count($submenu) == 1 )
			$menu_page = $submenu[0]['page'];
		if ( $menu_page[0] != '/' && $menu_page[0] != ' ' )
			$menu_page = '/'.$menu_page;
		if (empty($onclick) && !is_integer(strpos($menu_page, '#')))
			$onclick = '$(\'#wait_sign\').show();';
		if ( (int)MENU_STYLE == 1 || $mobile_device) {
			$res = $res.'<li style=""><a href="'.$menu_page.'" style="height:'.MENU_HEIGHT.'px; padding-top:'.round((MENU_HEIGHT - MENU_FONT_SIZE) / 2.1 ).'px; background-image:'.(check_image_path(MENU_BTN_BACKGROUND_IMAGE)).';" onclick="'.$onclick.'" class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</a></li>';
		}
		else 
			$res = $res.'<a href="'.$menu_page.'" type="button" class="btn btn-menu" onclick="'.$onclick.'">'.$menu_name.'</a>';
	}
	return translate_str($res);
}

function print_Menu($user_account, $menu_items, $curpage = '')
{
	global $user_account;
	global $balance1;
	
	if ( defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ) {
		$res = '
		<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" id="mobi_top_menu_navbar" style="padding: 10px 10px 0 10px;">
			<div class="row" style="margin:0;padding: 0;">
				<div class="col-xs-2" style="margin:0;padding: 0;">
					<button class="btn btn-sm btn-default"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></button>
				</div>
				<div class="col-xs-8" style="margin:0;padding: 0;">
					<div class="input-group input-group-sm">
						<input type="text" class="form-control" style="padding:0 2px 0 2px; height:auto; margin:4px 0 0 0; border-radius:0; background:transparent; border:none;"></input>
						<div class="input-group-btn">
							<button class="btn btn-sm btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
						</div>
					</div>
				</div>
				<div class="col-xs-2" style="margin:0;padding:0; text-align:right;">
					<button class="btn btn-sm btn-default"><span class="glyphicon glyphicon-bell" aria-hidden="true"></span></button>
				</div>
			</div>
		</nav>
		';
	}
	else {
		if ( isset($_COOKIE['print_Menu_righttx_logged1']) )
			$print_Menu_righttx_logged1 = json_decode($_COOKIE['print_Menu_righttx_logged1'], true);
		
		if ( isset($_COOKIE['print_Menu_righttx_logged2']) )
			$print_Menu_righttx_logged2 = json_decode($_COOKIE['print_Menu_righttx_logged2'], true);
		
		$res = '
		<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" id="top_menu_navbar">
			'.(defined('LOGO_ADDITIONAL_CODE') ? (LOGO_ADDITIONAL_CODE != 'LOGO_ADDITIONAL_CODE' ? $user_account->parse_common_params(LOGO_ADDITIONAL_CODE) : '') : '').'
			<div class="container-fixed" id="logo_container" style="">
				<div class="container" style="height:'.LOGO_HEIGHT.'px;">
					<table><tr>';
		$logo_text = '
						<td style="vertical-align:top;">
							<a class="navbar-brand" href="/" style="'.((int)LOGO_ON_LEFT == 1?'float:left;':'float:right;').'"><div style="position:relative; width:0px; height:0px; left:0px; right:0px;"><div style="position:absolute; width:'.LOGO_IMG_WIDTH.'px; height:'.LOGO_IMG_HEIGHT.'px; left:0px; right:0px; overflow:hidden; background-image:'.check_image_path(LOGO_BACKGROUND_IMAGE).'; "></div></div></a>
						</td>
						';
		$bal_cell = '
						<td style="width:100%; vertical-align:top; cursor: pointer;'.((int)LOGO_ON_LEFT == 0?'text-align:right;':'').'" onclick="location.assign(\'/\');">
							<p class="logo_text_last'.(defined('TRANSLATE_BUSINESS_NAME') && TRANSLATE_BUSINESS_NAME == '0' ? ' notranslate' : '').'">'.(is_integer(strpos(BUSINESS_NAME, ' ')) ? '<span class="logo_text_first">'.substr(BUSINESS_NAME, 0, strpos(BUSINESS_NAME, ' ')).'</span>&nbsp;':'').'<span class="logo_text_last'.(defined('TRANSLATE_BUSINESS_NAME') && TRANSLATE_BUSINESS_NAME == '0' ? ' notranslate' : '').'">'.trim(substr(BUSINESS_NAME, strpos(BUSINESS_NAME, ' '))).'</span></p>
							<p class="top_slogan">'.translate_str(SITE_SLOGAN).'</p>	
						</td>';
		$contacts = '
						<td style="vertical-align:top; text-align:'.((int)LOGO_ON_LEFT == 0?'left;':'right;').'">
							<table width="'.RIGHTTX_WIDTH.'"></table>
							<p class="top_slogan">'.($user_account->is_loggedin() ? $print_Menu_righttx_logged1 : $user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", translate_str(RIGHTTX1)))).'</p>
							<p class="top_slogan">'.($user_account->is_loggedin() ? $print_Menu_righttx_logged2 : $user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", translate_str(RIGHTTX2)))).'</p>
						</td>
						<td style="">
							'.(LOGO_ON_LEFT?'<table width="80"><!-- this is space to show Google language selector --></table>':'').'
						</td>
						';
		if ( LOGO_ON_LEFT )
			$res = $res.$logo_text.$bal_cell.$contacts;
		else
			$res = $res.$contacts.$bal_cell.$logo_text;
		
		$menu_html = '';
		if (isset($menu_items)) {
			for ($i = 0; $i < count($menu_items); $i++) {
				$value = $menu_items[$i];
				$menu_html = $menu_html.print_Menu_item((isset($value['title']) ? $value['title'] : ''), (isset($curpage) ? $curpage : ''), (isset($value['page']) ? $value['page'] : ''), (isset($value['submenu']) ? $value['submenu'] : ''), isset($value['onclick'])?$value['onclick']:'', $user_account);
			}
		}
		$res = $res.'		</tr></table>
				</div>
			</div>
			<div class="container" id="navbar_container">
				<div class="navbar-header">
					'.($user_account->is_loggedin()?'<span class="label label-primary collapsed_balance" style="position:relative; top:16px; left:0px; cursor:pointer; padding-right:3px;" onclick="show_all_currencies_balance();">Balance: <strong class="balance1"><span class="balance_in_usd notranslate" style="background-color:#ffffff; padding:0 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></strong></span>':'').'
					<a class="navbar-brand collapsed_logo" href="/"></a>
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" style="'.( empty($menu_html) ? 'display:none;' : '' ).'">
						<span class="sr-only"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<div id="navbar" class="collapse navbar-collapse">
					<a class="navbar-brand small_scroll_logo" href="/" style=""></a>
					<ul class="nav navbar-nav menu_button" style="">
					
					';
		
		$res = $res.$menu_html;
		$res = $res.'
					</ul>
				</div>
			</div>
		</nav>
		<div id="blank_div_for_fixed_menu" style=""></div>';
	}
	return $res;
}

function print_footer_menu_item($menu_name, $menu_page, $submenu, $left_position = true, $table = true, $onclick = '', $user_account)
{
	if ( empty($menu_name) )
		return false;
	if ( is_menu_item_hidden($menu_page) )
		return false;
	if ( count($submenu) > 1 ) {
		if ( $table ) {
			echo translate_str('
			<td style="vertical-align:top; border:none;">
				<span class="footer_text"><b class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</b></span>
			');
			foreach( $submenu as $value )
				if ( !empty($value['page']) && !empty($value['title']) && ( !is_menu_item_hidden($value['page']) ) ) {
					if ( $value['page'][0] != '/' && $value['page'][0] != ' ' )
						$value['page'] = '/'.$value['page'];
					if ($value['title'] != '-')
						echo translate_str('<p class="footer_text"><a href="'.$value['page'].'" onclick="'.@$value['onclick'].'" class="'.(!strpos($value['title'], 'notranslate') ? 'string_to_translate' : '').'">'.$value['title'].'</a></p>');
				}
			echo '
			</td>';
		}
		else {
			echo translate_str('
			<div class="col-md-1">
				<span class="footer_text"><b class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</b></span>
			');
			foreach( $submenu as $value )
				if ( !empty($value['page']) && !empty($value['title']) && ( !is_menu_item_hidden($value['page']) ) ) {
					if ( $value['page'][0] != '/' && $value['page'][0] != ' ' )
						$value['page'] = '/'.$value['page'];
					if ($value['title'] != '-')
						echo translate_str('<p class="footer_text"><a href="'.$value['page'].'" onclick="'.@$value['onclick'].'" class="'.(!strpos($value['title'], 'notranslate') ? 'string_to_translate' : '').'">'.$value['title'].'</a></p>');
				}
			echo '
			</div>';
		}
	}
	else {
		if ( count($submenu) == 1 )
			$menu_page = $submenu[0]['page'];

		if ( $menu_page[0] != '/' && $menu_page[0] != ' ' )
			$menu_page = '/'.$menu_page;
		if ( $table )
			echo translate_str('
			<td style="vertical-align:top; border:none;">
				<a href="'.$menu_page.'" class="footer_text" onclick="'.$onclick.'"><b class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</b></a>
			</td>');
		else
			echo translate_str('
			<div class="col-md-1">
				<a href="'.$menu_page.'" class="footer_text" onclick="'.$onclick.'"><b class="'.(!strpos($menu_name, 'notranslate') ? 'string_to_translate' : '').'">'.$menu_name.'</b></a>
			</div>');
	}
}

function include_html_from_file($out_str)
{
	if ( is_integer(strpos($out_str, '{$include_html')) ) {
		while ( is_integer(strpos($out_str, '{$include_html')) ) {
			$html_text = '';
			$html_tag = get_text_between_tags($out_str, '{$include_html', '}');
			$html_file = get_text_between_tags($html_tag, '$file=\'', '\'');
			if (file_exists(DIR_WS_TEMP_CUSTOM_CODE.$html_file)) {
				$html_text = file_get_contents(DIR_WS_TEMP_CUSTOM_CODE.$html_file);
				if ( !is_integer(strpos($html_tag, '$notranslate=\'yes\'')) ) {
					$html_text = translate_str($html_text);
				}
			}
			else {
				$html_text = "<p style='color:#a00000'>file: $html_file does not exists</p>";
			}
			delete_text_between_tags($out_str, '{$include_html', '}', false, $html_text);
		}
	}
	return $out_str;
}

function print_footer_menu($user_account, $menu_items, $left_position = true, $table = true)
{
	global $submenu_max_depth;
	echo ($table ? '
	<footer class="footer" style="height:'.(isset($submenu_max_depth) && $submenu_max_depth > 0 ? $submenu_max_depth : FOOTER_HEIGHT).'px;">
		<div class="container" id="footer_container_table">
			<table class="table footer_table"><tr>'
			:'
		<div class="container" id="footer_container">
			<div class="row">');
	
	try {
		if ( isset($menu_items) && $menu_items !== false && is_array($menu_items)) {
			for ($i = 0; $i < count($menu_items); $i++) {
				$value = $menu_items[$i];
				print_footer_menu_item((isset($value['title']) ? $value['title'] : ''), (isset($value['page']) ? $value['page'] : ''), (isset($value['submenu']) ? $value['submenu'] : ''), $left_position, $table, (isset($value['onclick']) ? $value['onclick'] : ''), $user_account);
			}
		}
	}
	catch (Exception $e) {}	
	if (defined('FOOTER_TEXT1'))
		$footer_text1 = include_html_from_file(FOOTER_TEXT1);
	else
		$footer_text1 = '';
	
	if (defined('FOOTER_TEXT2'))
		$footer_text2 = include_html_from_file(FOOTER_TEXT2);
	else
		$footer_text2 = '';

	$copyr_str = '
				<table style="width:100%;">
					<tr>
						<td class="footer_copyright">'.translate_str($user_account->parse_common_params($footer_text1)).'</td>
						<td class="footer_copyright">'.translate_str($user_account->parse_common_params($footer_text2)).'</td>
					</tr>
				</table>';
	echo ($table?'
				</tr>
			</table>'.$copyr_str.'
		</div>
	</footer>'
		:'</div>'.$copyr_str.'
	</div>');
}

function read_menu_from_db($user_account)
{
	if ( $user_account->is_loggedin() )
		$menu_permission_file = 'menu_'.str_replace(',' ,'-', $user_account->permissions);
	else
		$menu_permission_file = 'menu_';
	
	$s = $user_account->parse_common_params(get_file_variable($menu_permission_file), false);
	$menu_items = json_decode($s, 1);
	return $menu_items;
}

if (file_exists(DIR_WS_TEMP_CUSTOM_CODE.'header.php')) {
	include(DIR_WS_TEMP_CUSTOM_CODE.'header.php');
}
else {
	if ( isset($_GET['noheader']) && $_GET['noheader'] ) {
		echo '
		<!DOCTYPE html>
		<html lang="en">
		<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<link href="/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/translateelement.css" rel="stylesheet">
		<style type="text/css">
		';
		include(DIR_WS_CSS.'site_design.php'); 
		echo '
		body{margin:0; background-image:'.(is_integer(strpos(BACKGROUND_IMAGE, ':repeat;'))?check_image_path(BACKGROUND_IMAGE):'none;').' margin-bottom:0px;}
		</style>
		<script src="/javascript/pycommon.js" type="text/javascript"></script>
		'.(defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
			<script src="/javascript/Crypto.java.class.php" type="text/javascript"></script>'
			: ''
		).'
		<script src="/javascript/scriptaculous/lib/prototype.js" type="text/javascript"></script>
		<script src="/javascript/jquery.min.js"></script>
		<script src="/javascript/jquery-ui.min.js"></script>
		<script src="/javascript/bootstrap/js/bootstrap.min.js"></script>
		</head>
		<body>
		';
	}
	else {
		$uagent_info = new uagent_info();
		$mobile_device = $uagent_info->DetectMobileQuick();
		$menu_items = [];
		if (!isset($site_image))
			$site_image = '';
		if ( !defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION ) {
			if ( $user_account->is_loggedin() ) {
				global $balance1;
				$balance1 = '';
				$menu_items = read_menu_from_db($user_account);
			}
			else {
				$menu_items = read_menu_from_db($user_account);
			}
			
			if (isset($menu_items)) {
				for ($i = 0; $i < count($menu_items); $i++) {
					$value = $menu_items[$i];
					if (isset($value['submenu'])) 
						count_submenu_max_depth($value['submenu']);
				}
			}
			$submenu_max_depth = ($submenu_max_depth + 4) * (FOOTER_FONT_SIZE + 15);
			if (empty($site_image) && defined('SITE_IMAGE') && SITE_IMAGE !== '' ) {
				$site_image = SITE_IMAGE;
				if ( $site_image[0] == '/' )
					$site_image = substr($site_image, 1);
				$site_image = SITE_DOMAIN.$site_image;
			}
		}
		$page_title = get_text_from_html($page_title, false);
		$navigator_string = '';
		
		if ( $user_account->is_loggedin() ) {
			if (!isset($_COOKIE['print_Menu_righttx_logged1']) || empty($_COOKIE['print_Menu_righttx_logged1']) || time() - $_COOKIE['cookie_vars_time'] > 300 ) {
				$_COOKIE['print_Menu_righttx_logged1'] = json_encode($user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", RIGHTTX_LOGGED1), true, true));
				setcookie('print_Menu_righttx_logged1', $_COOKIE['print_Menu_righttx_logged1']);
			}
			if (!isset($_COOKIE['print_Menu_righttx_logged2']) || empty($_COOKIE['print_Menu_righttx_logged2']) || time() - $_COOKIE['cookie_vars_time'] > 300 ) {
				$_COOKIE['print_Menu_righttx_logged2'] = json_encode($user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", RIGHTTX_LOGGED2), true, true));
				setcookie('print_Menu_righttx_logged2', $_COOKIE['print_Menu_righttx_logged2']);
			}
			if ( time() - $_COOKIE['cookie_vars_time'] > 300 )
				setcookie('cookie_vars_time', time());
		}
		$language = get_selected_language();
		if (empty($language) || !file_exists(DIR_WS_TEMP_ON_WEBSITE.$language.'/'))
			$language = 'en';
		echo '<!DOCTYPE html>
		<html lang="'.$language.'">
		<head>';
		if ( !isset($header_textout) || empty($header_textout) ) {
			$header_textout = '
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>'.$page_title.'</title>
			<meta http-equiv="content-type" content="text/html;charset=utf-8" />
			<meta name="description" content="'.$page_description.'">
			<meta name="keywords" content="'.$page_keywords.'">
			<meta name="viewport" content="width=device-width, user-scalable=no">
			<meta property="og:type" content="website" />
			<meta property="og:title" content="'.$page_title.'" />
			<meta property="og:site_name" content="'.SITE_TITLE.'" />
			'.(!empty($site_image) && !is_integer(strpos($additionsl_header_meta, 'og:image'))?'<meta property="og:image" content="'.$site_image.'" />':'').'
			<meta property="og:description" content="'.$page_description.'" />
			<meta property="twitter:title" content="'.$page_title.'"/>
			<meta property="twitter:description" content="'.$page_description.'"/>
			'.(!empty($site_image) && !is_integer(strpos($additionsl_header_meta, 'twitter:image'))?'<meta property="twitter:image" content="'.$site_image.'" />':'').'
			'.$additionsl_header_meta.'
			<link rel="shortcut icon" href="'.SITE_ICON.'">
			'.(defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ? '<link href="/javascript/bootstrap/css/bootstrap.css" rel="stylesheet">' : '<link href="/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet">').'
			<link href="/css/translateelement.css" rel="stylesheet">
			<link rel="preload" href="/javascript/bootstrap/fonts/glyphicons-halflings-regular.woff" as="font" type="font/woff">
			';
		}
		
		echo $header_textout;
		echo '
		<style type="text/css">
		';
		include(DIR_WS_CSS.'site_design.php');
		$no_prototype_js_in_header = true;
		echo '
		</style>
		<script src="/javascript/pycommon.js" type="text/javascript"></script>
		'.(defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
			<script src="/javascript/Crypto.java.class.php" type="text/javascript"></script>'
			: ''
		).'
		'.(!isset($no_prototype_js_in_header) || !$no_prototype_js_in_header ? '
			<script src="/javascript/scriptaculous/lib/prototype.js" type="text/javascript"></script>	
			'
			: '').'
		<script src="/javascript/jquery.min.js"></script>
		<script src="/javascript/jquery-ui.min.js"></script>
		<script src="/javascript/bootstrap/js/bootstrap.min.js"></script>
		';
		if ( !$user_account->is_loggedin() ) {
			echo '<script type="text/javascript">';
			require_once(DIR_WS_INCLUDES.'$$$widget_track_user_id.php');
			echo '</script>';
		}
		echo '
		</head>
		<body '.($user_account->is_loggedin() && $user_account->is_manager() ? 'class="notranslate"' : '').'>
		';
		if ( !isset($hide_top_menu) || !$hide_top_menu )
			echo print_Menu($user_account, $menu_items, $curpage); 
		echo '
		<div class="google_translate google_translate__panel" id="google_translate_element"></div>
		<script>
		var it_is_mobile_device = '.(empty($mobile_device)?'0':'1').';
		'.(defined('SHOW_GOOGLE_TRANSLATE') && SHOW_GOOGLE_TRANSLATE == 'true' ? '
			function googleTranslateElementInit() {
			new google.translate.TranslateElement({}, "google_translate_element");
			$("#google_translate_element img").eq(0).remove();
			$("#google_translate_element span").eq(3).remove();
			}'
			: ''
		).'
		'.(defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ?
			'
			var userid = get_cookie("userid");
			var password = get_cookie("password");
			var is_loggedin = userid.length > 0 && password.length > 0;
			var this_is_mobi_version = 1;
			'
			: '
			var this_is_mobi_version = 0;
			'
		).'
		</script>
		';
		echo '<div class="container central_container">
			';
			echo $navigator_string;
			if ( !empty($page_header) )
				echo translate_str('<h1 id="page_header" class="first string_to_translate">'.$page_header.'</h1>');
			$notifications_str = '';
			$limits = '';
			echo '
			<div class="alert" id="top_alert" style="display:none;">
				<span id="top_alert_text"></span>
			</div>

			<script type="text/javascript">
			var show_top_alert_timerId = 0;
			function show_top_alert(message, alert_type, hex_message)
			{
				show_top_alert_timerId = setTimeout(
					function() 
					{ 
						clearTimeout(show_top_alert_timerId);
						if (typeof alert_type == "undefined")
							alert_type = "alert-success";
						$("#top_alert").removeClass("alert-success").removeClass("alert-info").removeClass("alert-warning").removeClass("alert-danger");
						$("#top_alert").addClass(alert_type);
						if ( hex_message && hex_message.length > 0 )
							$("#top_alert_text").html(hex_to_string(hex_message));
						else
							$("#top_alert_text").html(message);
						$("#top_alert").fadeIn( "slow" );
					}, 
				100);
			}
			</script>
			<div class="alert alert-danger" id="javascript_warning">
				Please enable JavaScript in your browser!!! This site doesn&#39;t work if JavaScript is disabled!!!
			</div>
			<div align="center" id="wait_sign" style="position:fixed; top:50%; left:50%; _display:none; z-index:10000; width:0px;"><img src="/images/wait64x64.gif" style="width:42px; height:42px; border:none; position:relative; left:-21px; box-shadow:2px 2px 14px #414141;"></div>
			<SCRIPT LANGUAGE="JavaScript">
				$("#javascript_warning").hide();
				$("#wait_sign").show();
			</SCRIPT>
			'.generate_popup_code('all_currencies_balance', '<div id="all_currencies_balance_body"></div>', '', 'Balance', '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>', 'btn-link', '', '', '', '', '', true).'
			<div class="row" style="width:100%; '.(defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ? 'margin:60px 0 0 0; padding:0;' : 'margin:0px; padding:0 0 40px 0;').'">
			';
	}
	?>

	<SCRIPT LANGUAGE="JavaScript">

	$( document ).ready(function() {
		$(".local_time_date_next_hour").each(function( index ) {
			var d = new Date();
			d.setTime($(this).attr("unix_time") * 1000);
			$(this).html(d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear() + "<br> <span style='color:#<?php echo COLOR1BASE; ?>;'>" + leading_zero(d.getHours(), 2) + ":" + leading_zero(d.getMinutes(), 2) + "</span>");
		});
		$(".local_pline_date_and_hour").each(function( index ) {
			var d = new Date();
			d.setTime($(this).attr("unix_time") * 1000);
			$(this).html(d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear() + " " + leading_zero(d.getHours(), 2) + ":" + leading_zero(d.getMinutes(), 2));
		});
	});

	window.onbeforeunload = confirmExit;
	function confirmExit() {
		$("#wait_sign").show();
	}
	</SCRIPT>
	<?php
	if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) {
		echo '
		<script type="text/javascript">
		'.file_get_contents(DIR_ROOT.'mobi_website/javascript/universal.constants.js').'
		'.file_get_contents(DIR_ROOT.'mobi_website/javascript/universal.functions.js').'
		</script>';
		require_once(DIR_WS_INCLUDES.'mobi_header.php');
	}

	if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'header_footer.php') )
		require_once(DIR_WS_TEMP_CUSTOM_CODE.'header_footer.php');
}
?>