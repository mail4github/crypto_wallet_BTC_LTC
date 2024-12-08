<?php
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

if ( !empty($_POST['form_submitted']) ) {
	$box_message = $user_account->update_security_question($_POST['sec_question'], $_POST['sec_answer_hash'], $_POST['password_hash'], $_POST['face_descriptor'], $_POST['old_sec_answer_hash']);
	if ( empty($box_message) )
		echo generate_json_answer(1, 'Your Security Question has been changed. ');
	else
		echo generate_json_answer(0, 'Error: '.$box_message.'.');
	exit;
}

$page_header = 'Security Question';
$page_title = $page_header;
require(DIR_WS_INCLUDES.'header.php');
/*
if ( !empty($user_account->security_answer) && $user_account->get_calculated_balance() > VERIFICATION_PIN_NEED_IF_BALANCE && (empty($_POST['verification_pin']) || !$user_account->check_verification_pin($_POST['verification_pin'])) ) {
	echo '
	<form name="user_frm" method="post">
	<input type="hidden" name="verification_pin" value="">
	</form>
	<SCRIPT LANGUAGE="JavaScript">
	function verification_pin_checked()
	{
		document.user_frm.submit();
	}
	$(document).ready(function(){
		show_verification_pin(document.user_frm, document.user_frm.verification_pin, undefined, undefined, verification_pin_checked, true);
	});
	</SCRIPT>
	';
	require(DIR_WS_INCLUDES.'box_verification_code.php');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}
*/
$user_account->clear_verification_pin();

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'sec_question.png', 'This security question works as an additional layer of your data protection. It helps verify your identity in case when the password is not enough. For example, it will help reset your password if you ever forget it.', 'alert-info');

global $seq_questions_arr;
$seq_questions_options = '';
$question_found = false;
foreach ($seq_questions_arr as $seq_question) {
	$seq_questions_options = $seq_questions_options.'<option value="'.$seq_question.'"';
	if ($user_account->security_question == $seq_question) {
		$seq_questions_options = $seq_questions_options.' selected';
		$question_found = true;
	}
	$seq_questions_options = $seq_questions_options.'>'.$seq_question.'</option>';
}
if (!$question_found && !empty($user_account->security_question)) 
	$seq_questions_options = $seq_questions_options.'<option value="'.$user_account->security_question.'" selected>'.$user_account->security_question.'</option>';

?>
<div class="container small_center_cantainer" style="">
	<div class="form-horizontal">
		<input type="hidden" name="form_submitted" value="1">
		<input type="hidden" name="cur_password" id="cur_password" value="">
		<input type="hidden" name="face_descriptor" id="face_descriptor" value="">
		<input type="hidden" name="entered_verification_pin" id="entered_verification_pin" value="">
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-3">Security question:</label>
				<div class="col-md-9 inputGroupContainer">
					<select name="sec_question" id="sec_question" class="form-control" style="" required="required">
						<option value="">- Select one -</option>
						<?php echo $seq_questions_options; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-3">Answer:</label>
				<div class="col-md-9 inputGroupContainer <?php if ( is_integer(strpos($box_message, 'Error:')) ) echo 'has-error'; ?>" style="" >
					<input type="text" pattern="[a-z 0-9]{1,64}$" name="sec_answer" id="sec_answer" value="<?php echo $_POST['sec_answer']; ?>" class="form-control" placeholder="<?php echo (empty($user_account->security_answer)?'enter your security answer':'*** hidden ***'); ?>" aria-describedby="at-addon" required="required" onkeyup="validate_obj('sec_answer', 'sec_answer');" autocomplete="off"> 
					
					<span class="glyphicon form-control-feedback"></span>
					<?php echo show_help('Minimum length 1 symbols, maximum length 64 symbols. Please avoid using capital letters in your answer. Also it is most preferable to use Latin letters. Avoid common words or an information that can be easily found online. Avoid using special characters, such as: !@#$%\...'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" style="text-align:center;" >
				<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg" onClick="save_data(); return false;">Save...</button>
			</div>
		</div>
	</div>
</div>
<SCRIPT language="JavaScript">
function validate_obj(obj_name, error_message)
{	
	var tmp_obj = document.getElementById(obj_name);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_name).parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if (tmp_obj.checkValidity() && tmp_obj.value.length > 0 ) {
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
	if ( !validate_obj("sec_question", "sec_question") || !validate_obj("sec_answer", "sec_answer") ) {
		show_message_box_box("Error", "Please select question and enter answer. Minimum length 2 symbols, maximum length 64 symbols. No capital letters, latin letters only, no special characters, such as: !@#$%\ etc.", 2);
		return false;
	}
	<?php 
		if (!empty($user_account->face_descriptor) && $user_account->face_recog_when & 8)
			echo 'show_face_scanner_box("approve", on_face_found, on_no_camera);';
		else
			echo 'show_password("cur_password", "check_old_password();", "");';
	?>
}

function on_face_found(descriptor)
{
	hide_face_scanner_box();
	$("#face_descriptor").val( descriptor );
	show_password("cur_password", "check_old_password();", "");
}

function on_no_camera()
{
	hide_face_scanner_box();
	show_password("cur_password", "check_old_password();", "old_password_hash", "Old Password");
}

function check_old_password()
{
	hide_wait_box_box();
	box_check_password($("#cur_password").val(), "<?php echo (empty($user_account->security_answer) || !$user_account->common_param_is_strict_security ? 'change_seq_question();' : 'check_ver_pin();'); ?>", "wrong_password();");
	return 0;
}

function check_ver_pin()
{
	show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "change_seq_question", undefined, "Answer on current security question");
}

function wrong_password()
{
	show_message_box_box("Error", "Please enter correct password.", 2);
}

function change_seq_question()
{
	$.ajax({
		method: "POST",
		url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>",
		data: { form_submitted: "1", sec_question: $("#sec_question").val(), sec_answer_hash: md5($("#sec_answer").val()), password_hash: md5($("#cur_password").val()), face_descriptor:$("#face_descriptor").val(), old_sec_answer_hash: md5($("#box_sec_answer").val()) }
	})
	.done(function( ajax__result ) {
		hide_wait_box_box();
		try{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				show_message_box_box("Success", arr_ajax__result["message"], 1, undefined, "reload_page"); 
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2, undefined, "reload_page"); 
			}
		}
		catch(error){
			show_message_box_box("Error", "Cannot change security question", 2, undefined, "reload_page"); 
		}
	});
}

function reload_page() { 
	location.assign("<?php echo $_SERVER['SCRIPT_NAME']; ?>?alert_info=Your security question has been saved.");
}

</script>
<?php
require(DIR_WS_INCLUDES.'box_verification_code.php');
require(DIR_WS_INCLUDES.'box_password.php');
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_COMMON_PHP.'box_wait.php');
if ( !empty($user_account->face_descriptor) && $user_account->face_recog_when & 8 )
	require_once(DIR_COMMON_PHP.'box_face_recog.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>