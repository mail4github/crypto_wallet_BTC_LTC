<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$box_message = '';
if ( !empty($_POST['form_submitted']) ) {
	$answer = $user_account->update_notifications($_POST['job_emails'], $_POST['order_emails'], $_POST['ticket_emails']);
	if ( empty($answer) ) {
		$box_message = 'Your account has been updated.';
	}
}

$page_header = 'Notification Emails';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = 'Set up which notification emails you want to receive.';
require(DIR_WS_INCLUDES.'header.php');
echo show_intro('/'.DIR_WS_IMAGES_DIR.'notify.png', 'Select which notification emails you want to receive.', 'alert-info');
?>
<div class="container small_center_cantainer" style="margin-top:20px;">
	<form method="post" name="user_frm" id="user_frm" class="form-horizontal">
		<input type="hidden" name="form_submitted" value="1">
		
		<div class="checkbox">
			<label>
				<input type="checkbox" name="job_emails" value="1" <?php if ( $user_account->job_emails == 1 ) echo 'CHECKED'; ?>><strong style="padding-left:10px;">Receive emails about job</strong>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="order_emails" value="1" <?php if ( $user_account->order_emails ) echo 'CHECKED'; ?>><strong style="padding-left:10px;">Receive emails about transactions and events</strong>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="ticket_emails" value="1" <?php if ( $user_account->ticket_emails ) echo 'CHECKED'; ?>><strong style="padding-left:10px;">Receive emails about new messages</strong>
			</label>
		</div>
		<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg" style="margin-top:20px;">Save</button>
	</form>
</div>
<?php
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
