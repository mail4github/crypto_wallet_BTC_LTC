<?php

function get_dialog_body($it_is_flat_login = false, $use_userid = false)
{
	return translate_str('
	<div id="login_frm" class="form-horizontal">
	<input type="hidden" name="its_login" value="1">
	'.(!$it_is_flat_login?'
	<div class="modal fade" id="show_box_login" role="dialog">
		<div class="modal-dialog" style="">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close close_dlg_btn" data-dismiss="modal" id="btn_close_box"></button>
					<p class="modal-title"><span id="box_login_title">Login</span></p>
				</div>
				<div class="modal-body" id="box_login_body" style="padding-right:40px;">
					':'
	<div class="container small_center_cantainer" style="max-width:600px;">
					').'
					<div class="row" style="">
						<div class="form-group has-feedback" style="margin-left:0px;">
							'.($use_userid?'
							<label for="userid" class="control-label col-md-4 string_to_translate">User ID:</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<span class="input-group-addon" ><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
									<input type="number" name="login_userid" id="login_userid" placeholder="Your ID" required="required" class="form-control">
								</div>
								<span class="glyphicon form-control-feedback"></span>
							</div>
							':'
							<label for="email" style="text-transform:capitalize;" class="control-label col-md-4 string_to_translate">'.ACCOUNT_EMAIL_SYNONYM.':</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<span class="input-group-addon">'.(defined('EMAIL_SYMBOL') ? str_replace('&lt;', '<', str_replace('&quot;', '"', str_replace('&gt;', '>', EMAIL_SYMBOL))) : '<b>@</b>').'</span>
									<input type="text" name="email" id="login_email" value="'.(isset($_GET['email'])?$_GET['email']:(isset($_POST['email'])?$_POST['email']:'')).'" class="form-control" required="required" autocomplete="off"> 
								</div>
								<span class="glyphicon form-control-feedback"></span>
							</div>
							').'
						</div>
					</div>		
					<div class="row" style="">
						<div class="form-group has-feedback" style="margin-left:0px;">	
							<label for="password" style="text-transform:capitalize;" class="control-label col-md-4 string_to_translate">Password:</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<span class="input-group-addon" id="lock-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
									<input type="password" name="password" id="login_password" class="form-control" required="required" autocomplete="off" onkeydown="check_lgn_keyboard_layout(event);">
								</div>	
								<span class="glyphicon form-control-feedback"></span>
								<span id="lgn_non_latin_keyboard" style="display:none;" class="label label-danger string_to_translate">Non Latin keyboard layout!!!</span>
								<span id="lgn_capslock_detected" style="display:none;" class="label label-danger string_to_translate">Caps Lock is pressed!!!</span>
							</div>
						</div>
					</div>
					'.(!$it_is_flat_login?'
				</div>
				<div class="modal-footer" style="text-align:center;">':'
				<div class="row">
					<div class="col-md-12" style="text-align:center;" >
						').'
						<button class="btn btn-primary btn-lg" id="login_btn" onclick="" style="min-width:100px;"><span id="login_caption" class="string_to_translate">Login...</span><img src="/images/wait64x64.gif" width="16" height="16" border="0" id="box_login_wait_image" style="display:none;">
						</button>
				'.(!$it_is_flat_login?'
				</div>
				'.(defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ? 
					'' 
					: '
					<div class="row" style="margin-bottom:20px; ">
						<div class="col-md-6" style="text-align:center;" >
							<a href="/forgot_psw.php" class="btn btn-link string_to_translate">Forgot Your Password?</a>
						</div>
						<div class="col-md-6" style="text-align:center;" >
							<a href="#" onclick="signup_from_login(); return false;" class="btn btn-link string_to_translate">Don&#39;t have an account? Sign up...</a>
						</div>
					</div>'
				).'
			</div>
		</div>
	</div>':'
					</div>
				</div>
	</div>
	').'
	</div>
	');
}

function get_login_script($it_is_flat_login = false)
{
	global $user_account;
	return '
<form name="redirect_frm" method="post" action="">
<input type="hidden" name="email" value="">
<input type="hidden" name="psw" value="">
</form>
<script type="text/javascript">

var login_in_process = 0;
var number_of_fail_logins = 0;
var java_on_login_performed = "";
var is_loggedin = '.((int)$user_account->is_loggedin()).';
var redirect_on_login = "/acc_main.php";

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
	return true;
}

function reload_page_on_login(get_param)
{
	
	if ( is_loggedin )
		document.location.href = redirect_on_login + "?" + get_param;
	else
		document.location.reload();

	if (!is_loggedin)
		do_login();
}

function login(email_addr, user_id, password, password2, validate_form, password_hash, verification_pin)
{
	if ( (typeof validate_form == "undefined" || validate_form) && !( validate_obj("login_email", "login_email") '.($use_userid?'&& validate_obj("login_userid", "login_userid")':'').' && validate_obj("login_password", "login_password") ) ) {
		$("#show_box_login").modal("hide");
		show_message_box_box("'.make_str_translateable('Error').'", "'.make_str_translateable('Please fill in all the fileds.').'", 2, "", "do_login");
		return false;
	}
	login_in_process = 1;
	var login_data = "";
	if ( typeof password == "undefined" ) 
		var password = $("#login_password").val();
	
	if ( typeof password_hash == "undefined" )
		password_hash = md5( decodeURIComponent(password.replace(/\+/g, "%20")) );
	
	var password_sign = decodeURIComponent(password.replace(/\+/g, "%20"));
	password_sign = password_sign.substr(password_sign.length - 3, 3);
	password_sign = md5(password_sign + "'.date('Y-m-d').'");

	if ( typeof email_addr == "undefined" ) {
		if ( document.getElementById("login_email") )
			var email_addr = $("#login_email").val();
		else
			var email_addr = "";
	}
	'.($use_userid?'
	if ( typeof user_id == "undefined" ) {
		if ( document.getElementById("login_userid") )
			var user_id = $("#login_userid").val();
		else
			var user_id = "";
	}
	':'').'
	if ( typeof verification_pin == "undefined" )
		verification_pin = "";
	var fingerprint = "";
	try {
		if ( typeof fp != "undefined" )
			fingerprint = fp.get();
	}
	catch(error){write_console_log(" --- fingerprint eror --- " + error);}

	login_data = encodeURIComponent(email_addr + "<div>" + '.($use_userid?'user_id':'""').' + "<div><div><div>" + password_hash + "<div><div>" + verification_pin + "<div>" + password_sign + "<div>" + fingerprint + "<div>");
	login_data = string_to_hex(login_data);

	$.ajax({
		method: "POST",
		url: "/api/user_login/",
		data: { data: login_data }
	})
	.done(function( ajax__result ) {
		try {
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				is_loggedin = 1;
				$("#show_box_login").modal("hide");
				login_in_process = 0;
				$("#login_caption").show();
				$("#box_login_wait_image").hide();
				if ( java_on_login_performed.length > 0 )
					window[java_on_login_performed](); 
				reload_page_on_login("logged_userid="+arr_ajax__result["values"]["userid"] + "&logged_password=" + arr_ajax__result["values"]["hash"]);
			}
			else {
				if ( arr_ajax__result["error_code"] == "2" ) {
					// redirect
					document.redirect_frm.action = arr_ajax__result["message"];
					document.redirect_frm.email.value = $("#login_email").val();
					document.redirect_frm.psw.value = md5(decodeURIComponent($("#login_password").val().replace(/\+/g, "%20")));
					document.redirect_frm.submit();
				}
				else
				if ( arr_ajax__result["error_code"] == "3" ) {
					setTimeout(function(){ login(); }, 5000);
				}
				else {
					$("#show_box_login").modal("hide");
					show_message_box_box("'.make_str_translateable('Error').'", "'.make_str_translateable('Invalid email or password.').' " + arr_ajax__result["message"], 2, "", "reload_page_on_login");
					login_in_process = 0;
					$("#login_caption").show();
					$("#box_login_wait_image").hide();
					$("#login_password").val("");
					number_of_fail_logins++;
					if (number_of_fail_logins > 3)
						document.location.reload();
				}
			}
		}
		catch(error){'.(!empty($_COOKIE['debug'])?'write_console_log(ajax__result + " --- login --- " + error);':'').'}
	});
	$("#box_login_wait_image").show();
	$("#login_caption").hide();
	return false;
}

function do_login()
{
	$("#show_box_login").on("shown.bs.modal", function () {
		setTimeout(function() { $("#'.($use_userid?'login_userid':'login_email').'").focus(); }, 100);
	});
	$("#show_box_login").modal("show");
	
}

function signup_from_login()
{
	if ( ! (typeof(show_signup_dialog) == "undefined") ) {
		$("#show_box_login").modal("hide");
		show_signup_dialog();
	}
	else
		document.location.assign("/signup.php");
}

function check_lgn_keyboard_layout(event)
{
	re = /\d|\w|[\.\$@\*\\\/\+\-\^\!\(\)\[\]\~\%\&\=\?\>\<\{\}\"\'\,\:\;\_]/g;
	a = event.key.match(re);
	if ( a == null )
		$("#lgn_non_latin_keyboard").show();
	else
		$("#lgn_non_latin_keyboard").hide();
	
	if (event.getModifierState("CapsLock")) 
		$("#lgn_capslock_detected").show();
	else
		$("#lgn_capslock_detected").hide();
}

$(function() {
    $("#login_password").click(function() {
		validate_obj("login_email", "login_email");
		'.($use_userid?'validate_obj("login_userid", "login_userid");':'').'
	});
});

$("#login_password").bind("keypress", function(e){
	if ( e.keyCode == 13 )
		login();
});

$( document ).ready(function() {
	setTimeout(function() { $("#login_password").val(""); $("#login_btn").attr("onclick", "login();") }, 1000);
	var user_id = get_param_value("user");
	var email = decodeURIComponent(get_param_value("email"));
	var hash = get_param_value("psw");
	var verification_pin = get_param_value("verification_pin");
	if ( (user_id.length > 0 || email.length > 0 ) && ( hash.length > 0 || verification_pin.length > 0 ) ) {
		$("#login_frm").hide();
		$("#intro_top_alert").html("<h2 style=\'text-align:center;\'>'.make_str_translateable('Please Wait...').'<br><img src=/images/wait64x64.gif width=80 height=80 border=0></h2>");
		var redirect_url = get_param_value("redirect");
		if ( redirect_url.length > 0 )
			redirect_on_login = "/" + redirect_url;
		login(email, user_id, "", "", false, hash, verification_pin);
	}
	'.(!empty($_POST['email']) && !empty($_POST['psw'])?'login("'.$_POST['email'].'", "", "", "", false, "'.$_POST['psw'].'");':'').'
});
</script>
';
}
?>