<?php
require('../includes/application_top.php');

$page_header = 'Account Information Reminder';
$page_title = $page_header;
$no_prototype_js_in_header = true;
require(DIR_WS_INCLUDES.'header.php');
echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'remember.svg', 'To locate your account we need your login name. Then you will be asked a security question; if the answer if correct you will obtain access your account.', 'alert-info');

$box_message = '';
if ( $_POST['form_submitted'] == '1' ) {
	$tmp_user = new User();
	$tmp_user->email = $_POST['email'];
	$sec_question = $tmp_user->get_seq_question();
	$face_recog_when = $tmp_user->get_face_recog_when();
	if (!empty($sec_question)) {
		echo '
		<h3>Answer security question:</h3>
		<div class="form-horizontal" style="margin:20px 15px 0 15px;">
			<input type="hidden" name="form_submitted" value="3">
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4"></label>
					<label class="control-label col-md-8" style="text-align:left;"><div style="padding:0 10px 0 20px;">'.$sec_question.'</div></label>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Your Answer:</label>
					<div class="col-md-8 inputGroupContainer">
						<div style="padding:0 10px 0 20px;">
							<input type="text" pattern="[a-z 0-9]{1,64}$" name="sec_answer" id="sec_answer" value="'.$_POST['sec_answer'].'" class="form-control" placeholder="enter your answer" aria-describedby="at-addon" required="required" onkeyup="validate_obj(\'sec_answer\', \'sec_answer\');"> 
							'.show_help('No capital letters, Latin letters only, no special characters, such as: !@#$%\...').'
							<span class="glyphicon form-control-feedback"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" style="text-align:center;" >
					<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg" onClick="validate_seq_answer(); return false;">Continue...</button>
				</div>
			</div>
		</div>
		<script type="text/javascript">
		function validate_seq_answer()
		{
			show_wait_box_box();
			s_answer = md5($("#sec_answer").val());
			$.ajax({
				method: "POST",
				url: "/api/user_validate_seq_answer/",
				data: { sec_answer: s_answer, entered_email: "'.$tmp_user->email.'", token: "'.get_api_token_seed().'" }
			})
			.done(function( ajax__result ) {
				hide_wait_box_box();
				try {
					var arr_ajax__result = JSON.parse(ajax__result);
					if ( arr_ajax__result["success"] ) {
						document.location.href = "/login.php?email='.$_POST['email'].'&redirect=acc_change_psw.php&verification_pin=" + arr_ajax__result["values"]["verification_pin"];
					}
					else {
						show_message_box_box("Error", "Wrong answer!!!", 2);
					}
				}
				catch(error){'.(defined('DEBUG_MODE')?'write_console_log(ajax__result + " --- login --- " + error);':'').'}
			});
		}
		</script>
		';
	}
	else {
		echo '<div class="alert alert-danger" role="alert">You never set up your security question.</div>';
	}
	if ($face_recog_when & 16) {
		echo '
		<button class="btn btn-info btn-lg" style="margin:40px auto; display:block; white-space:normal;" onClick="face_approval(); return false;">
			<span class="visible_on_big_screen"><img src="/images/face_approval.png" style="width:30px; height:30px; margin:6px; filter:grayscale(100%);"></span>
			Obtain access using the Face Recognition...
		</button>';
	}
	else
	if ( empty($sec_question) ) 
		echo '<div class="alert alert-danger" role="alert">And you never set up Face Recognition. Password cannot be restored.</div>';

	echo '
	<script type="text/javascript">
	$( document ).ready(function() {
		$("#intro_top_alert").hide();
		$("#page_header").html("");
	});
	</script>
	';
	if ( $face_recog_when & 16 ) {
		require_once(DIR_COMMON_PHP.'box_face_recog.php');
		?>
		<script type="text/javascript">
		function face_approval()
		{
			show_face_scanner_box("approve", on_face_found, undefined, "<?php echo get_api_token_seed(); ?>", "<?php echo $tmp_user->email; ?>");
		}

		function on_face_found(descriptor, result_from_server)
		{
			if (result_from_server["success"]) {
				document.location.href = "/login.php?email=<?php echo $tmp_user->email; ?>&redirect=acc_change_psw.php&verification_pin=" + result_from_server["values"]["verification_pin"];
			}
		}
		</script>
		<?php
	}
}
if ( empty($_POST['form_submitted']) ) {
	echo '
	<div class="container small_center_cantainer" style="">
		<form class="form-horizontal" method="post" name="user_frm">
			<input type="hidden" name="form_submitted" value="1">
			<div class="row" style="margin-bottom:10px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">User Name:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<div class="input-group">
							<span class="input-group-addon" ><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
							<input type="text" name="email" id="email" class="form-control" placeholder="your login name" required="required" autocomplete="off">
							'.show_more_info_popover('Please enter your login name.').'
						</div>
					</div>
				</div>
			</div>		
			<div class="row" style="margin-bottom:10px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">
						Verification Code:
						<p class="description visible_on_big_screen" style="text-align:right;">To avoid spam and automatic registration we ask you to repeat the code you see in the picture.</p>
					</label>
					<div class="col-md-8" style="" >
						<img src="/'.DIR_WS_SERVICES_DIR.'captcha.php?width=650&height=240&char_min_size=100&char_max_size=110" border="0" style="max-width:270px; width:100%; min-width:180px;" alt="Enable images to see number" name="captcha_image" id="captcha_image">
						<div class="btn btn-default" data-toggle="tooltip" title="Click to change image."><span class="glyphicon glyphicon-refresh" aria-hidden="true" onclick="reloadImg(\'captcha_image\'); return false;"></span></div>
					</div>
				</div>
			</div>	
			<div class="row" style="margin-bottom:10px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Enter Code:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="number" name="captcha" id="captcha" maxlength="5" placeholder="verification code" class="form-control" required="required" autocomplete="off">
						<span class="glyphicon form-control-feedback"></span>
						<div class="error_message" style="'.(is_integer(strpos($box_message, 'verification code'))?'':'display:none;').'">Please enter valid Verification Code.</div>
					</div>
				</div>
			</div>		
			<div class="row">
				<div class="col-md-12" style="text-align:center;" >
					<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg">Continue...</button>
				</div>
			</div>
		</form>
	</div>
	';
}
?>

<SCRIPT language="JavaScript"> 
function validate_obj(obj_name, error_message)
{	
	var tmp_obj = document.getElementById(obj_name);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_name).parents(".form-group");
		var glyphicon = formGroup.find(".form-control-feedback");
		if (tmp_obj.checkValidity() && tmp_obj.value.length > 0 ) {
			formGroup.addClass("has-success").removeClass("has-error");
			glyphicon.addClass("glyphicon-ok").removeClass("glyphicon-remove");
			return true;
		} 
		else {
			formGroup.addClass("has-error").removeClass("has-success");
			glyphicon.addClass("glyphicon-remove").removeClass("glyphicon-ok");
			return false;
		}
	}
}

function validate_email()
{	
	var email_obj = document.getElementById("email");
	if ( email_obj ) {
		var formGroup = $("#email").parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if (email_obj.checkValidity()) {
			formGroup.addClass('has-success').removeClass('has-error');
			glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
		} 
		else {
			formGroup.addClass('has-error').removeClass('has-success');
			glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
		}
	}
}
function validate_captcha()
{	
	var obj = document.getElementById("captcha");
	if ( obj ) {
		var formGroup = $("#captcha").parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if (obj.checkValidity()) {
			formGroup.addClass('has-success').removeClass('has-error');
			glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
		} 
		else {
			formGroup.addClass('has-error').removeClass('has-success');
			glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
		}
	}
}

$(function() {
    $("#captcha").click(function() {
		validate_email();
	});
});
$(function() {
    $("#submit_btn").click(function() {
		validate_email();
		validate_captcha();
	});
});
</script>
<?php 
echo (is_integer(strpos($box_message, 'verification code'))?'<script language="JavaScript">validate_captcha();</script>':'');

require(DIR_COMMON_PHP.'box_message.php');
require(DIR_COMMON_PHP.'box_wait.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>
