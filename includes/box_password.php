<div class="modal fade" id="show_password" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" id="btn_close_box">&times;</button>
				<h2 class="modal-title" id="password_box_title"><?php echo make_str_translateable('Password'); ?></h2>
			</div>
			<div class="modal-body">
				<div class="alert alert-warning" id="password_box_description"><?php echo make_str_translateable('Please enter your current password, we want to be sure that you are the owner of this account on site:').'&nbsp;'.SITE_SHORTDOMAIN; ?></div>
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
					<input type="password" name="password_pin" id="password_pin" value="" class="form-control" placeholder="Password" required="required" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" onkeydown="check_psw_keyboard_layout(event);"> 
					<span class="glyphicon form-control-feedback"></span>
				</div>
				<span class="label label-danger" id="pws_non_latin_keyboard" style="display:none;"><?php echo make_str_translateable('Non Latin keyboard layout!!!'); ?></span>
				<span class="label label-danger" id="psw_capslock_detected" style="display:none;"><?php echo make_str_translateable('Caps Lock is pressed!!!'); ?></span>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" onClick="submit_password_pin(); return false;" style="margin-bottom:0px;"><?php echo make_str_translateable('Continue'); ?></button> <button type="button" class="btn btn-warning" data-dismiss="modal" style="margin-bottom:0px;"><?php echo make_str_translateable('Cancel'); ?></button>
			</div>
		</div>
	</div>
</div>

<SCRIPT language="JavaScript">
var password_form_to_submit;
var password_pin_input;
var function_to_run_on_exit;
var hashed_password_input = "";

function validate_password_obj(obj_id, error_message)
{	
	var tmp_obj = document.getElementById(obj_id);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_id).parents('.input-group');
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

function show_password(input_with_password, function_to_run, hashed_password, title, description)
{
	password_pin_input = input_with_password;
	function_to_run_on_exit = function_to_run;
	if (typeof hashed_password !== 'undefined' )
		hashed_password_input = hashed_password;
	if (typeof title !== 'undefined' )
		$("#password_box_title").html(title);
	if (typeof description !== 'undefined' )
		$("#password_box_description").html(description);
	$('#show_password').modal('show'); 
	$("#show_password").on("shown.bs.modal", function () {
		setTimeout(function() { $("#password_pin").focus(); }, 100);
	});

	$("#password_pin").bind("keypress", function(e){
		if ( e.keyCode == 13 && $("#show_password").hasClass("in") ) {
			submit_password_pin();
		}
	});
}

function hide_password()
{
	$('#show_password').modal('hide');
}

function submit_password_pin()
{
	if ( !validate_password_obj("password_pin") ) {
		alert("Please enter password.");
		return false;
	}
	$("#" + password_pin_input).val( $("#password_pin").val() ); 
	if ( hashed_password_input.length > 0 ) {
		var hash_suffix = "";
		var psw_v = md5(decodeURIComponent($("#password_pin").val().replace(/\+/g, "%20")));
		$("#" + hashed_password_input).val( psw_v ); 
	}
	$('#show_password').modal('hide'); 
	setTimeout(function_to_run_on_exit, 100);
	return false;
}

function box_check_password(password_to_check, function_if_right, function_if_wrong)
{
	try {
		if (typeof show_wait_box_box === "function")
			show_wait_box_box();
		$.ajax({
			method: "POST",
			url: "/api/user_is_password_correct/",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", hash_password: md5(password_to_check) }
		})
		.done(function( ajax__result ) {
			if (typeof hide_wait_box_box === "function")
				hide_wait_box_box();
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					if (typeof function_if_right !== "undefined")
						setTimeout(function_if_right, 100);
					return 0;
				}
			}
			catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error);':''); ?>}
			if (typeof function_if_wrong !== "undefined")
				setTimeout(function_if_wrong, 100);
		});
	}
	catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error);':''); ?>}
	return 0;
}

function check_psw_keyboard_layout(event)
{
	re = /\d|\w|[\.\$@\*\\\/\+\-\^\!\(\)\[\]\~\%\&\=\?\>\<\{\}\"\'\,\:\;\_]/g;
	a = event.key.match(re);
	if ( a == null )
		$("#pws_non_latin_keyboard").show();
	else
		$("#pws_non_latin_keyboard").hide();
	
	if (event.getModifierState("CapsLock")) 
		$("#psw_capslock_detected").show();
	else
		$("#psw_capslock_detected").hide();
}

</SCRIPT>