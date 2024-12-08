<?php
require('../includes/application_top.php');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'add_contactus.inc.php') ) {
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'add_contactus.inc.php');
	exit;
}

require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'Contact Us';
$page_title = $page_header;
$show_top_images = false;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_WS_CLASSES.'ticket.inc.php');

$show_form = !$user_account->disabled;
//if ( defined('DEBUG_MODE') ) echo "<br>takes time: ".(time() - $start_time)." secs.<br>";
if ( $_POST['next_step'] == 'submit_frm' ) {
	if ( !empty($_POST['subject']) && !empty($_POST['message']) && !empty($_POST['email']) && !empty($_POST['name']) ) {
		$error_message = '';
		$_POST['subject'] = tep_sanitize_string($_POST['subject'], 128);
		$_POST['message'] = tep_sanitize_string($_POST['message'], 1024);
		$_POST['subject'] = mb_convert_encoding($_POST['subject'], 'HTML-ENTITIES', 'UTF-8');
		$_POST['message'] = mb_convert_encoding($_POST['message'], 'HTML-ENTITIES', 'UTF-8');
		
		if ( strlen($_POST['subject']) < 5 )
			$error_message = 'Subject too short';
		if ( strlen($_POST['message']) < 10 )
			$error_message = 'Message too short';
		if ( empty($error_message) )
			$error_message = get_api_value('open_trouble_ticket', '', array('subject' => base64_encode($_POST['subject']), 'message' => base64_encode($_POST['message'])));
		if ( empty($error_message) ) {
			echo '<br><br><h2>Thank you for your interest in contacting us.</h2>
				<p>We welcome your ideas, comments, questions and requests.</p>
				<br>
				<p>If this was a question needing a reply, we will carefully review your request and someone will be getting back with you shortly.</p>
				<p>If it was feedback or a general comment, we have received your message, and thank you for taking the time to contact us.</p>
				<br>
				<p>Please visit our webpage with <a href="/acc_faqs.php">Frequently Asked Questions</a> to quickly find answers to common questions.</p><br><br>';
			$show_form = false;
			$_SESSION['captcha'] = '';
		}
	}
	else {
		$error_message = 'Please fill-in all mandatory fields.!!!';
	}
}

if ( $show_form ) {
	echo show_intro('/'.DIR_WS_IMAGES_DIR.'lifebuoy.png', 'The following form helps us to process your submission in a timely 
	manner. Please note that we are unable to accept attachments.', 'alert-info');
	//if ( defined('DEBUG_MODE') ) echo "<br>takes time: ".(time() - $start_time)." secs.<br>";
	?>
	<div class="container small_center_cantainer">
		<form method="post" name="tech_sup_frm">
		<input type="hidden" name="next_step" value="submit_frm">
		<?php
		echo '
			<input type="hidden" name="name" value="'.$user_account->full_name().'">
			<input type="hidden" name="email" value="'.$user_account->email.'">
			';
		?>
		<div class="row" style="margin-bottom:10px;">
			<div class="form-group has-feedback">
				<div class="col-md-3" style="" >
					<b>Subject: <font color="#FF0000">*</font></b>
				</div>
				<div class="col-md-9" style="" >
					<input type="text" name="subject" id="subject" value="<?php echo (!empty($_POST['subject'])?$_POST['subject']:''); ?>" class="form-control" placeholder="Subject" minlength="5" required="required">
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:10px;">
			<div class="form-group has-feedback">
				<div class="col-md-3" style="" >
					<b>Describe the Problem: <font color="#FF0000">*</font></b>
					<p class="description" style="padding-top:0px;">(please English only)</p>
				</div>
				<div class="col-md-9" style="" >
					<textarea name="message" wrap="soft" class="form-control" rows="5" minlength="10" required="required"><?php echo (!empty($_POST['message'])?$_POST['message']:''); ?></textarea>
					<br>
					<?php echo show_help('Enter as much information about the issue as possible, including the results of all troubleshooting that took place before.'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" style="text-align:center;" >
				<button name="submit_btn" class="btn btn-primary btn-lg">Submit...</button>
			</div>
		</div>
		</form>
	</div>
	</form>
	<?php
}
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($error_message) )
	echo '<script language="JavaScript">show_message_box_box("Error", "'.$error_message.'", 2);</script>';
require(DIR_WS_INCLUDES.'footer.php');

?>
</body>
</html>