<?php
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Face Recognition';
$page_title = $page_header;
$no_prototype_js_in_header = true;
require(DIR_WS_INCLUDES.'header.php');

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'face_approval.png', "Face recognition provides secure authentication delivered by neural network software. This feature adds new level of security to your wallet. You can use the face recognition to authorize access to your account or reset forgotten password.", 'alert-info');

echo '<p style="text-align:center; margin:30px 0 0 0;"><button class="btn btn-success btn-lg" onclick="setup_face();">'.(empty($user_account->face_descriptor)?'Setup the recognition of your face...':'Change Face...').'</button></p>'.show_help('<b>How to setup the face approval feature?</b><br>Position your face inside the circle, do not wear glasses, do not laugh. Wait for message that your face has been saved.');

?>
<input type="hidden" id="entered_password" value="">
<input type="hidden" id="entered_verification_pin" value="">
<style type="text/css" media="screen">
p input[type=checkbox]{margin-right:20px;}
</style>
<h2 style="margin-top:30px;">Select when software will ask for the face recognition:</h2>
<!--p><input type="checkbox" id="face_recog_at_login" <?php echo ($user_account->face_recog_when & 1 ? 'checked' : ''); ?>>At login to account</p>
<p><input type="checkbox" id="face_recog_at_send" <?php echo ($user_account->face_recog_when & 2 ? 'checked' : ''); ?>>At money transfer</p-->
<p><label class="checkbox-inline"><input type="checkbox" id="face_recog_at_chng_password" <?php echo ($user_account->face_recog_when & 4 ? 'checked' : ''); ?>>At changing password</label></p>
<p><label class="checkbox-inline"><input type="checkbox" id="face_recog_at_sec_question" <?php echo ($user_account->face_recog_when & 8 ? 'checked' : ''); ?>>At changing security question</label></p>
<p><label class="checkbox-inline"><input type="checkbox" id="face_recog_at_forgot_password" <?php echo ($user_account->face_recog_when & 16 ? 'checked' : ''); ?>>At restoring forgotten password</label></p>
<p style="text-align:center; margin:30px 0 0 0;"><button class="btn btn-success btn-lg" onclick="save_face_recog_when();">Save</button></p>
<?php
require_once(DIR_WS_INCLUDES.'box_verification_code.php');
require_once(DIR_WS_INCLUDES.'box_password.php');
require_once(DIR_COMMON_PHP.'box_message.php');
require_once(DIR_COMMON_PHP.'box_wait.php');
require_once(DIR_COMMON_PHP.'box_face_recog.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
<script type="text/javascript">

function setup_face()
{
	<?php echo (empty($user_account->face_descriptor)?'show_face_scanner_box("setup", on_face_found);':'show_password("entered_password", "check_entered_password();", "");'); ?>
}

function wrong_password()
{
	show_message_box_box("Error", "Please enter correct password.", 2);
}

function check_entered_password()
{
	hide_wait_box_box();
	box_check_password($("#entered_password").val(), "<?php echo (empty($user_account->security_answer) || !$user_account->common_param_is_strict_security ? 'activate_face_scanner();' : 'check_ver_pin1();'); ?>", "wrong_password();");
	return 0;
}

function activate_face_scanner()
{
	show_face_scanner_box("setup", on_face_found);
}

function check_ver_pin1()
{
	show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "activate_face_scanner", undefined, "Answer on current security question");
}

function on_face_found(descriptor)
{
	descriptor = string_to_hex(JSON.stringify(descriptor));
	sec_quest_answer_md5 = $("#box_sec_answer").val();
	sec_quest_answer_md5 = md5(sec_quest_answer_md5);
			
	$.ajax({
		method: "POST",
		url: "/api/user_save_face_descriptor",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", face_descriptor:descriptor, password_hash: md5($("#entered_password").val()), sec_answer_hash:sec_quest_answer_md5 },
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			hide_face_scanner_box();
			hide_verification_pin();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) 
				show_message_box_box("Success", "A signature of your face has been saved", 1); 
			else {
				if (arr_ajax__result["message"].length > 0)
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				else
					show_message_box_box("Error", "A signature of your face could not been saved", 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "A signature of your face could not been saved. Please try again.", 2); 
		}
	});
}

function save_face_recog_when()
{
	show_password("entered_password", "check_entered_password2();", "");
}

function check_entered_password2()
{
	hide_wait_box_box();
	box_check_password($("#entered_password").val(), "<?php echo (empty($user_account->security_answer) || !$user_account->common_param_is_strict_security ? 'save_face_recog_when2();' : 'check_ver_pin2();'); ?>", "wrong_password();");
	return 0;
}

function check_ver_pin2()
{
	show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "save_face_recog_when2", undefined, "Answer on current security question");
}

function save_face_recog_when2()
{
	var face_recog_when = ($("#face_recog_at_login").is(':checked') ? 1 : 0) + ($("#face_recog_at_send").is(':checked') ? 2 : 0) + ($("#face_recog_at_chng_password").is(':checked') ? 4 : 0) + ($("#face_recog_at_sec_question").is(':checked') ? 8 : 0) + ($("#face_recog_at_forgot_password").is(':checked') ? 16 : 0);
	$.ajax({
		method: "POST",
		url: "/api/user_save_face_recog_when",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", face_recog_when:face_recog_when, password_hash: md5($("#entered_password").val()), sec_answer_hash:md5($("#box_sec_answer").val()) },
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			hide_face_scanner_box();
			hide_verification_pin();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) 
				show_message_box_box("Success", "Changes have been saved", 1); 
			else {
				if (arr_ajax__result["message"].length > 0)
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				else
					show_message_box_box("Error", "Changes could not been saved", 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Changes could not been saved. Please try again.", 2); 
		}
	});
}

</script>
</body>
</html>