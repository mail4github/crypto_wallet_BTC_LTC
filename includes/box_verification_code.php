<div class="modal fade" id="show_verification" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" id="btn_close_box">&times;</button>
				<h2 class="modal-title" style="display:inline-block;"><span id="verification_title"><?php echo (!empty($user_account->security_answer)?'Answer Security Question':'Verification Code'); ?></span></h2>
				<div style="display:inline-block; padding-left:20px;"><button id="sec_question_btn" type="button" class="btn btn-info btn-xs" onClick="return box_verification_answer_sec_question();" style="<?php echo (!empty($user_account->security_answer)?'display:none;':''); ?>">Answer Security Question</button></div>
				<div style="display:inline-block; padding-left:20px;"><button id="verification_code_btn" type="button" class="btn btn-info btn-xs" onClick="return box_verification_enter_verification_code();" style="<?php echo (!empty($user_account->security_answer) && (!defined('DONT_USE_EMAIL') || !DONT_USE_EMAIL)?'':'display:none;'); ?>">Enter Verification Code</button></div>
			</div>
			<div class="modal-body">
				<div id="verification_code" style="<?php echo (!empty($user_account->security_answer)?'display:none;':''); ?>">
					<div class="alert alert-warning" style="">For security reason, in order to change your account information we sent verification PIN to your email (<b id="recipient_email_show"></b>) to make sure you are the owner of this account. If you have a problem receiving email please <a href="#" onClick="return box_verification_answer_sec_question();" >answer security question</a> instead.</div>
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></span>
						<input type="text" name="verification_pin" id="verification_pin" value="" class="form-control" placeholder="Verification PIN" required="required" > 
						<span class="glyphicon form-control-feedback"></span>
					</div>
					<div style="display: inline-block;">
						<button type="button" id="send_again_btn" class="btn btn-info" onClick="return send_verification_pin();"><img src="/images/wait64x64.gif" style="margin-left:0px; display:none;" id="wait" width="16" height="16" border="0" alt="">Send Again <span id="send_again_timer"></span></button>
					</div>	
					
				</div>
				<div id="security_question" style="<?php echo (!empty($user_account->security_answer)?'':'display:none;'); ?>">
					<label class="control-label" style="text-align:left;"><?php echo $user_account->security_question; ?></label>
					<div class="input-group">
						<input type="text" pattern="[a-z 0-9]{2,64}$" name="box_sec_answer" id="box_sec_answer" value="" class="form-control" placeholder="enter your answer" aria-describedby="at-addon" required="required" onkeyup="validate_PIN_obj('box_sec_answer');" autocomplete="off"> 
						<?php echo show_help('No capital letters, Latin letters only, no special characters, such as: !@#$%\...'); ?>
						<span class="glyphicon form-control-feedback"></span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" onClick="submit_verification_pin(); return false;" style="margin-bottom:0px; min-width:100px;"><img id="wait_image" src="/images/wait64x64.gif" width="16" height="16" border="0" alt="" style="display:none;"><span id="box_verification_submit_btn_title">Continue</span></button> <button type="button" class="btn btn-warning" data-dismiss="modal" style="margin-bottom:0px;">Cancel</button>
			</div>
		</div>
	</div>
</div>

<SCRIPT language="JavaScript">
var seconds_left = "<?php echo (VERIFICATION_PIN_SENDING_IN_MINUTES * 60); ?>";
var send_again_timer_id = 0;
var form_to_submit;
var verification_pin_input;
var current_recipient_email = "<?php echo (isset($verification_pin_recipient_email)?$verification_pin_recipient_email:''); ?>";
var verification_pin_sent = 0;
var verification_code_title = "";

function box_verification_answer_sec_question()
{
	$("#verification_code").hide();
	$("#security_question").show();
	if (verification_code_title.length == 0)
		verification_code_title = "Answer Security Question";
	$("#verification_title").html(verification_code_title);
	$("#sec_question_btn").hide();
	$("#verification_code_btn").show();
}

function box_verification_enter_verification_code()
{
	$("#verification_code").show();
	$("#security_question").hide();
	if (verification_code_title.length == 0)
		verification_code_title = "Verification Code";
	$("#verification_title").html(verification_code_title);
	$("#sec_question_btn").show();
	$("#verification_code_btn").hide();
}

function validate_PIN_obj(obj_id, error_message)
{	
	var tmp_obj = document.getElementById(obj_id);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_id).parents(".input-group");
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

function send_verification_pin(SMS)
{
	if ( send_again_timer_id != 0 )
		return false;

	if (typeof SMS == "undefined")
		SMS = "0";
	verification_pin_sent++;
	$.ajax({
		method: "POST",
		url: "/api/user_verification_pin_refresh/",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", send_sms: SMS }
	})
	.done(function( ajax__result ) {
		var sec_left = 0;
		var message = "";
		
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				message = arr_ajax__result["message"];
				sec_left = arr_ajax__result["values"]["sec_left"];
			}
		}
		catch(error){<?php echo (defined("DEBUG_MODE")?'alert(error + ", send_verification_pin: " + ajax__result);':''); ?>}

		seconds_left = <?php echo (VERIFICATION_PIN_SENDING_IN_MINUTES * 60); ?> - sec_left;
		if ( message.length > 0 ) {
			alert(message);
			seconds_left = 0;
		}
		if (seconds_left > 0 ) {
			$("#send_again_btn").addClass("disabled");
			$("#send_SMS").addClass("disabled");
			clearInterval(send_again_timer_id);
			send_again_timer_id = setInterval(function() {
				$("#send_again_timer").html("in " + seconds_left + " seconds"); 
				$("#send_SMS_timer").html("in " + seconds_left + " seconds"); 
				seconds_left--;
				if ( seconds_left <= 0 ) {
					clearInterval(send_again_timer_id);
					send_again_timer_id = 0;
					$("#send_again_btn").removeClass("disabled");
					$("#send_SMS").removeClass("disabled");
					$("#send_again_timer").html(""); 
					$("#send_SMS_timer").html(""); 
				}
			}, 1000);
		}
	});
	return false;
}

function show_verification_pin(form, input_with_verification_pin, recipient_email, dont_send_SMS, run_after_verification_pin_checked, hide_verification_code_btn, title)
{
	form_to_submit = form;
	verification_pin_input = input_with_verification_pin;
	if (typeof recipient_email != "undefined")
		current_recipient_email = recipient_email;
	else
		current_recipient_email = "<?php echo $user_account->email; ?>";
	
	if (typeof run_after_verification_pin_checked != "undefined")
		after_submit_verification_pin = run_after_verification_pin_checked;
	
	if (typeof hide_verification_code_btn != "undefined" && hide_verification_code_btn)
		$("#verification_code_btn").hide();
	
	if (typeof title !== 'undefined' ) {
		verification_code_title = title;
		$("#verification_title").html(verification_code_title);
	}

	if ( !verification_pin_sent && <?php echo ( empty($user_account->verification_pin)?'1':'0'); ?> )
		send_verification_pin();
	
	$("#recipient_email_show").html(current_recipient_email);
	$("#show_verification").modal("show"); 
	if (typeof dont_send_SMS != "undefined" && dont_send_SMS) {
		$("#send_SMS").hide(); 
		$("#send_SMS_reminder").hide();
	}
	else {
		<?php echo (!empty($user_account->phone)?'$("#send_SMS").show();':''); ?>
	}
}

function hide_verification_pin()
{
	$("#show_verification").modal("hide"); 
}

function submit_verification_pin()
{
	if ( $("#security_question").is(":visible") ) {
		if ( !validate_PIN_obj("box_sec_answer") ) {
			alert("Please enter Security Answer.");
			return false;
		}
		$("#wait_image").show();
		$("#box_verification_submit_btn_title").hide();

		$.ajax({
			method: "POST",
			url: "/api/user_validate_seq_answer/",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", sec_answer: md5($("#box_sec_answer").val()) }
		})
		.done(function( ajax__result ) {
			$("#wait_image").hide();
			$("#box_verification_submit_btn_title").show();
			try {
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					$("#verification_pin").val( arr_ajax__result["values"]["verification_pin"] );
					try {
						if (typeof verification_pin_input !== "undefined")
							verification_pin_input.value = $("#verification_pin").val();
					}
					catch(error){}
					if (typeof form_to_submit != "undefined")
						form_to_submit.submit();
					if (typeof after_submit_verification_pin != "undefined" && after_submit_verification_pin.length > 0 )
						window[after_submit_verification_pin]();
				}
				else {
					alert("Wrong answer!!!");
				}
			}
			catch(error){<?php echo (defined("DEBUG_MODE")?'alert(error + ", submit_verification_pin: " + ajax__result);':''); ?>}
		});
	}
	else {
		if ( !validate_PIN_obj("verification_pin") ) {
			alert("Please enter PIN.");
			return false;
		}
		$("#wait_image").show();
		$("#box_verification_submit_btn_title").hide();
		$.ajax({
			method: "POST",
			url: "/api/user_check_verification_pin/",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", verification_pin: $("#verification_pin").val() }
		})
		.done(function( ajax__result ) {
			$("#wait_image").hide();
			$("#box_verification_submit_btn_title").show();
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					try {
						if (typeof verification_pin_input !== "undefined")
							verification_pin_input.value = $("#verification_pin").val();
					}
					catch(error){}
					if (typeof form_to_submit != "undefined")
						form_to_submit.submit();
					if (typeof after_submit_verification_pin != "undefined" && after_submit_verification_pin.length > 0 )
						window[after_submit_verification_pin]();
				}
				else {
					alert("Wrong PIN, please try again " + arr_ajax__result["message"]);
				}
			}
			catch(error){<?php echo (defined("DEBUG_MODE")?'alert(error + ", submit_verification_pin: " + ajax__result);':''); ?>}
		});
	}
	return false;
}

</SCRIPT>