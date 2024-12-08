<?php
$get_list_of_common_params = false;
require('../includes/application_top.php');
if ( !$user_account->is_loggedin() ) {
	header('Location: /');
	exit;
}

if ( $user_account->logged_using_pin) {
	header('Location: /acc_change_psw.php?alert_info=You made request to change password because password has been forgotten. Please fill in with new password the fields located below:');
	exit;
}

require(DIR_WS_INCLUDES.'header.php');

if ( $_POST['job_done'] == '1' ) {
	$user_account->done_current_job(0);
	$box_message = 'Congratulations! You did one more job!';
}
else
if ( $_POST['job_skipped'] == '1' ) {
	$user_account->done_current_job(1);
}
require(DIR_WS_INCLUDES.'acc_main_user.php');
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>

