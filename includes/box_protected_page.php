<?php

$protected_page_unlocked = false;
if ( $_POST['protected_page_form_submitted'] == '1' ) {
	$_COOKIE['tmp_psw_hash'] = $_POST['hashed_password'];
	setcookie('tmp_psw_hash', $_COOKIE['tmp_psw_hash'], time() + 60 * 60 * 24);
	$_COOKIE['tmp_password_sign'] = md5( substr($_POST['password'], strlen($_POST['password']) -3 , 3).date('Y-m-d') );
	setcookie('tmp_password_sign', $_COOKIE['tmp_password_sign']);
}
if ( !empty($_COOKIE['tmp_psw_hash']) ) {
	$_POST['hashed_password'] = $_COOKIE['tmp_psw_hash'];

	if ( !$user_account->verify_password_from_box_password('hashed_password', '', $_COOKIE['tmp_password_sign']) ) {
		$box_message = 'Error: Please enter correct password.';
		setcookie('tmp_psw_hash', '');
		setcookie('tmp_password_sign', '');
	}
	else {
		$protected_page_unlocked = true;
	}
}

function get_protected_page_java_code()
{
	return '
	<form name="user_frm" method="post">
	<input type="hidden" name="protected_page_form_submitted" value="1">
	<input type="hidden" name="password" id="password" value="">
	<input type="hidden" name="hashed_password" id="hashed_password" value="">
	</form>
	<script language="JavaScript">

	function password_entered()
	{
		document.user_frm.submit();
	}
	$(document).ready(function(){
		show_password("password", "password_entered();", "hashed_password"); 
	});
	</script>
	';
}

?>