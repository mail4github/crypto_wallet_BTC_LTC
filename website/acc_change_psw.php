<?php
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$dont_ask_password = $user_account->logged_using_pin;

if ( !empty($_POST['form_submitted']) ) {
	$box_message = '';
	$face_approved = false;
	if ( empty($box_message) && !empty($user_account->face_descriptor) && $user_account->face_recog_when & 4 && !empty($_POST['face_descriptor']) ) {
		if ( $user_account->is_face_correct($_POST['face_descriptor']) )
			$face_approved = true;
		else
			$box_message = 'Error: Your face not found.'.strlen($_POST['face_descriptor']);
	}
	if ( empty($box_message) ) {
		$box_message = $user_account->update_password( $_POST['new_password_hash'], $_POST['old_password_hash'], true, true, '', $_POST['face_descriptor'], $_POST['sec_answer_hash']);
	}
	if ( empty($box_message) )
		echo generate_json_answer(1, 'Your password has been changed.');
	else
		echo generate_json_answer(0, 'Error: '.$box_message.'.');
	exit;
}

$page_header = 'Change Password';
$page_title = $page_header.'. '.SITE_NAME;
$no_prototype_js_in_header = true;
require(DIR_WS_INCLUDES.'header.php');

echo '<div class="visible_on_big_screen">'.show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'password_512x512.png', translate_str('<p class="string_to_translate">Your password gives you access to all services you use. It is always a good idea to update your password regularly and to make sure it is unique from other passwords you use. Select a password that is at least 8 characters in length, that does not contain your name, username, phone number, birthday, or other personal information. It is best to include a mix of numbers, symbols and/or capital and lowercase letters in your new password.</p>'), 'alert-info').'</div>';

?>
<div class="container small_center_cantainer" style="">
	<div class="form-horizontal">
		<input type="hidden" name="form_submitted" value="1">
		<input type="hidden" name="old_password_hash" id="old_password_hash" value="">
		<input type="hidden" name="new_password_hash" id="new_password_hash" value="">
		<input type="hidden" name="entered_verification_pin" id="entered_verification_pin" value="">
		<input type="hidden" name="cur_password" id="cur_password" value="">
		<input type="hidden" name="face_descriptor" id="face_descriptor" value="">

		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label for="password" class="control-label col-md-4 "><?php echo translate_str('<span class="string_to_translate">New Password:</span>'); ?></label>
				<div class="col-md-8 inputGroupContainer <?php if ( is_integer(strpos($box_message, 'Error:')) ) echo 'has-error'; ?>" style="" >
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
						<input type="password" name="password" id="password" value="<?php echo $_POST['password']; ?>" class="form-control" _placeholder="New Password" aria-describedby="at-addon" required="required" pattern=".{6,}" onkeyup="validate_obj('password', 'password');"> 
					</div>
					<?php echo show_help(translate_str('<span class="string_to_translate">At least 6 characters, no spaces. Try to select a password that is easy to remember, but hard to guess for others.</span>')); ?>
					<div class="" id="password_weakness_div" style="display:none;">
						<div class="description text-danger" style="margin-top:10px;" id="password_weakness_description"><?php echo translate_str('<span class="string_to_translate">The level of toughness of your password:</span>'); ?> <span id="password_weakness_name"></span></div>
						<div class="progress" style="width:50%; height:10px; margin:0;">
							<div class="progress-bar progress-bar-danger" role="progressbar" style="width:80%" id="password_weakness_progress"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label for="password2" class="control-label col-md-4"><?php echo translate_str('<span class="string_to_translate">Re-type new password:</span>'); ?></label>
				<div class="col-md-8 inputGroupContainer <?php if ( is_integer(strpos($box_message, 'Error:')) ) echo 'has-error'; ?>" style="" >
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
						<input type="password" name="password2" id="password2" value="<?php echo $_POST['password2']; ?>" class="form-control" _placeholder="Password again" aria-describedby="at-addon" required="required" onkeyup="validate_obj('password2', 'password2');"> 
					</div>
					<span class="glyphicon form-control-feedback"></span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" style="text-align:center;" >
				<button name="submit_btn" id="submit_btn"  onClick="save_data(); return false;" class="btn btn-primary btn-lg"><?php echo translate_str('<span class="string_to_translate">Change Password...</span>'); ?></button>
			</div>
		</div>
	</div>
</div>
<SCRIPT language="JavaScript">

function validate_obj(obj_name, error_message)
{	
	if (obj_name == "password")
		scorePassword( $("#password").val(), "&#128076;", "&#128077;", "&#128078;" );
	var tmp_obj = document.getElementById(obj_name);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_name).parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if (tmp_obj.checkValidity() && tmp_obj.value.length > 0 && (obj_name != "password2" || $("#password").val() == $("#password2").val() ) ) {
			formGroup.addClass('has-success').removeClass('has-error');
			glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
			return true;
		} 
		else {
			formGroup.addClass('has-error').removeClass('has-success');
			glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
			return false;
		}
	}
}

function save_data()
{
	if ( !validate_obj("password", "password") || !validate_obj("password2", "password2") ) {
		show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Please fill in all the required fileds.'); ?>", 2);
		return false;
	}
	<?php
	if ($dont_ask_password)
		echo '
			$("#old_password_hash").val("'.$_SESSION['password'].'");
			$("#entered_verification_pin").val("'.$user_account->verification_pin.'");
		'.(!empty($user_account->face_descriptor) && $user_account->face_recog_when & 4 ? 'show_face_scanner_box("approve", on_face_found, on_no_camera);' : 'change_psw();');
	else
		echo (!empty($user_account->face_descriptor) && $user_account->face_recog_when & 4 ? 'show_face_scanner_box("approve", on_face_found, on_no_camera);' : 'show_password("cur_password", "check_old_password();", "old_password_hash", "'.make_str_translateable('Old Password').'");');
	?>
	
}

function on_face_found(descriptor)
{
	hide_face_scanner_box();
	$("#face_descriptor").val( descriptor );
	<?php
	echo ($dont_ask_password ? 'change_psw();' : 'show_password("cur_password", "check_old_password();", "old_password_hash", "'.make_str_translateable('Old Password').'");');
	?>
}

function on_no_camera()
{
	hide_face_scanner_box();
	show_password("cur_password", "check_old_password();", "old_password_hash", "<?php echo make_str_translateable('Old Password'); ?>");
}

function check_old_password()
{
	hide_password();
	box_check_password($("#cur_password").val(), $("#face_descriptor").val().length > 0 ? "change_psw();" : "<?php echo (empty($user_account->security_answer) || !$user_account->common_param_is_strict_security ? 'change_psw();' : 'check_ver_pin();'); ?>", "wrong_password();");
	return 0;
}

function check_ver_pin()
{
	<?php
	if ( !$dont_ask_password )
		echo 'show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "change_psw");';
	?>
	
}
function wrong_password()
{
	show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Please enter correct password.'); ?>", 2);
}

function change_psw()
{
	hide_verification_pin();
	hide_password();
	$("#cur_password").val( "" );
	show_wait_box_box("Changing password. Please wait...");
	$.ajax({
		method: "POST",
		url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>",
		data: { form_submitted: "1", old_password_hash: $("#old_password_hash").val(), new_password_hash: md5($("#password").val()), verification_pin: $("#entered_verification_pin").val(), face_descriptor:$("#face_descriptor").val(), sec_answer_hash: md5($("#box_sec_answer").val()) }
	})
	.done(function( ajax__result ) {
		hide_wait_box_box();
		try{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				show_message_box_box("Success", arr_ajax__result["message"], 1, "", "reload_ch_psw"); 
			}
			else {
				if (arr_ajax__result["message"].indexOf("<need_security_answer>") >= 0)
					show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "change_psw");
				else
					show_message_box_box("Error", arr_ajax__result["message"], 2); 
			}
		}
		catch(error){
			show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Cannot change password'); ?>", 2); 
		}
	});
}

function reload_ch_psw() { 
	location.assign("/acc_main.php?alert_info=Your password has been changed.");
}

</script>
<?php
require(DIR_WS_INCLUDES.'box_verification_code.php');
require(DIR_WS_INCLUDES.'box_password.php');
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_COMMON_PHP.'box_wait.php');
if ( !empty($user_account->face_descriptor) && $user_account->face_recog_when & 4 )
	require_once(DIR_COMMON_PHP.'box_face_recog.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>