<?php
$showDebugMessages = false;

if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);
	
require('../includes/application_top.php');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'new_login.php') ) {
	require(DIR_WS_TEMP_CUSTOM_CODE.'new_login.php');
	exit;
}

$it_is_flat_login = 1;
require(DIR_WS_INCLUDES.'box_login.php');

if ( defined('REDIRECT_LOGIN') && REDIRECT_LOGIN != '' ) {
	header('Location: '.REDIRECT_LOGIN);
	exit;
}

if ( !empty($_POST['its_login']) && !$user_account->is_loggedin() && $_SESSION['numb_of_logins'] > 10 ) {
	header('Location: /');
}

if ( $user_account->is_loggedin() ) {
	header('Location: /');
	exit;
}
$page_header = 'Sign In';
$page_title = $page_header;
$show_top_images = false;
require(DIR_WS_INCLUDES.'header.php');

if ( defined('DEBUG_MODE') ) echo 'ip: '.$_SERVER['SERVER_ADDR'].', password: "'.$_COOKIE['password'].'", "'.$_COOKIE['password2'].'", session_id: "'.session_id().'", stat_new_interface: "'.$_COOKIE['stat_new_interface'].'"<br>';

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'login.png', translate_str('<p class="string_to_translate">Registered user of - please sign in here.</p><p><span class="string_to_translate">Not registered?</span> <a href="/signup.php" class="string_to_translate">Sign up now</a>!</p><p class="text-warning"><span class="string_to_translate">Please make sure that this page is</span>: <b>'.SITE_DOMAIN.substr($_SERVER['SCRIPT_NAME'], 1).'</b>. <span class="string_to_translate">Sometimes hackers send phishing emails with a link to a fake webpage</span>.</p>'), 'alert-info visible_on_big_screen');
echo show_help(translate_str('<p><span class="string_to_translate">Please make sure that this page is:</span> <b>'.SITE_DOMAIN.substr($_SERVER['SCRIPT_NAME'], 1).'</b>. <span class="string_to_translate">Sometimes hackers send phishing emails with a link to a fake webpage</span>.</p>'), 'glyphicon-exclamation-sign', 'text-warning', 'invisible_on_big_screen');
?>
<need_to_login>
<?php 
echo get_dialog_body($it_is_flat_login, 0);
echo get_login_script($it_is_flat_login);
echo translate_str('
<div style="text-align:center;">
	<a href="/forgot_psw" class="btn btn-link string_to_translate">Forgot Your Password?</a>
	<a href="/signup" class="btn btn-link string_to_translate">Don&#39;t have an account? Sign up...</a>
</div>
');
require(DIR_COMMON_PHP.'box_message.php');

if ( !empty($_POST['its_login']) && !$user_account->is_loggedin() ) {
	$_SESSION['numb_of_logins'] = $_SESSION['numb_of_logins'] + 1;
	?>
	<script type="text/javascript">
	show_message_box_box("Error", translate_str("<span class=string_to_translate>That email address and password combination is incorrect.<br>Please try again.</span><br><br>"), 2);
	</script>
	<?php
}
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
