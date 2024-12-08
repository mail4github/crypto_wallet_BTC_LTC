<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Delete Account';
$page_title = $page_header.'. '.SITE_NAME;
require(DIR_WS_INCLUDES.'header.php');

if ( !empty($_POST['delete_acc']) && $_POST['delete_acc'] == 'yes' ) {
	if ( !$user_account->verify_password_from_box_password('old_password') )
		//$box_message = make_str_translateable('Error: Please enter correct password');
		$box_message = 'Error: Please enter correct password';

	if ( empty($box_message) ) {
		if ( $user_account->parentid != DEFAULT_USERID ) {
			$box_message = $user_account->delete('deleted by user.', 1);
		}
		else
			$box_message = $user_account->delete('deleted by user.');
		if ( empty($box_message) )
			//$box_message = make_str_translateable('Your account has been deleted.');
			$box_message = 'Your account has been deleted.';
	}
}

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'delete_account.png', translate_str('<p class="string_to_translate">You can delete your account at any time. If you change your mind, you will not be able to recover it. The deleting your account means that you will lose all the data in that account and you will lose access to your funds.</p>'), 'alert-warning');

?>
<form name="user_frm" method="post">
<input type="hidden" name="delete_acc" value="yes">
<input type="hidden" name="password" id="password" value="">
<input type="hidden" name="old_password" id="old_password" value="">
<input type="hidden" name="hash" id="hash" value="">
<input type="hidden" name="verification_pin" value="">
</form>
<span class="description"><?php echo make_str_translateable('Click on the button below to delete your account:'); ?></span><br><br><br>
<button name="submit_btn" id="submit_btn" class="btn btn-danger btn-lg" onClick="delete_acc(); return false;"><?php echo make_str_translateable('Delete Account'); ?></button>

<br><br><br><br>

<SCRIPT language="JavaScript">
function delete_acc()
{
	//if ( !confirm("<?php echo make_str_translateable('Are you sure you want to delete your account? All content, including user information and logs, will be permanently removed?'); ?>") )
		//return false;
	show_box_yesno_box(`<?php echo make_str_translateable('Are you sure you want to delete your account? All content, including user information and logs, will be permanently removed?'); ?>`, `delete_acc_confirmed`, `<?php echo make_str_translateable('Confirm delete account'); ?>`/*, ``, ``, icon, yes_class, no_class, hex_message*/);
}

function delete_acc_confirmed()
{
	show_password("password", "send_data();", "old_password", "hash");
}

function send_data()
{
	document.user_frm.submit();
}

function go_to_logout()
{
	window.location = "/logout.php";
}
</script>
<?php
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_WS_INCLUDES.'box_password.php');
require(DIR_WS_INCLUDES.'box_yes_no.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>