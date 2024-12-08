<?php
$country_list = '';
if ( empty($_POST['country']) ) {
	$_POST['country'] = getCountryCodefromIP();
	if ( empty($_POST['country']) ) 
		$country_list = $country_list.'<option value="" SELECTED>Select</option>'."\r\n";
}
make_countries_file();
if ( file_exists(DIR_WS_TEMP.'countries.txt') ) {
	$countries_text = file_get_contents(DIR_WS_TEMP.'countries.txt');
	$countries_tmp = preg_split('/$\R?^/m', $countries_text);
	foreach ($countries_tmp as $value) {
		if ( !empty($value) ) {
			$cntry_arr = explode("=", $value);
			$country_list = $country_list.'<option value="'.$cntry_arr[0].'" ';
			if ( $_POST['country'] == $cntry_arr[0] ) 
				$country_list = $country_list.' SELECTED ';
			$country_list = $country_list.'>'.$cntry_arr[1].'</option>'."\r\n";
		}
	}
}
echo generate_popup_code(
'signup_dialog', //$popup_name
'
<form method="post" name="user_frm" id="user_frm" class="form-horizontal" style="margin:0 20px 0 20px;">
	<input type="hidden" name="form_submitted" value="1">
	<input type="hidden" name="popup_form" value="1">
	<input type="hidden" name="come_from_site" value="'.($_COOKIE['come_from_site']).'">
	<input type="hidden" name="parentid" id="parentid" value="'.$_POST['parentid'].'">
	
	<span class="description" style="display:none;" id="parentid_span" >Referrer ID: <b><a id="parentid_text" href="">'.$_POST['parentid'].'</a></b></span>
	
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="email">Email:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<div class="input-group">
					<span class="input-group-addon" ><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></span>
					<input type="email" name="email" id="email" value="'.$_POST['email'].'" class="form-control" placeholder="Your Email" required="required"> 
				</div>
				<span class="glyphicon form-control-feedback"></span>	
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="password">Password:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
					<input type="password" name="password" id="password" value="'.$_POST['password'].'" class="form-control" placeholder="Your password" aria-describedby="at-addon" required="required" pattern=".{6,}"> 
					'.(show_more_info_popover('Try to select a password that is easy to remember, but hard to guess for others.')).'
				</div>
				<span class="description">At least 6 characters, no spaces, no "&lt;" symbols.</span>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="password2">Re-type password:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<div class="input-group">
					<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
					<input type="password" name="password2" id="password2" value="'.$_POST['password2'].'" class="form-control" placeholder="Password again" aria-describedby="at-addon" required="required"> 
				</div>
				<span class="glyphicon form-control-feedback"></span>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="firstname">Name:</label>
			<div class="col-md-8" style="" >
				<div class="col-md-6" style="padding:0px 4px 4px 0px;">
					<input type="text" name="firstname" id="firstname" value="'.$_POST['firstname'].'" class="form-control" placeholder="First Name" required="required" > 
					<span class="glyphicon form-control-feedback"></span>	
				</div>
				<div class="col-md-6" style="padding:0px 0px 0px 0px;">
					<div class="input-group">
						<input type="text" name="lastname" id="lastname" value="'.$_POST['lastname'].'" class="form-control" placeholder="Last Name" required="required">
						'.(show_more_info_popover('Your personal information will not be shown to other members or shared with outside parties.')).'
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="country">Country:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<div class="input-group">
					<select name="country" id="country" class="form-control" required="required">
					'.$country_list.'
					</select>
					'.(show_more_info_popover('If your country not in the list, please select country which is nearest to you.')).'
				</div>
				
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" >Verification Code:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<img src="/'.(DIR_WS_SERVICES_DIR).'captcha.php" border="0" class="_first_page_image" style="width:161px; height:66px;" alt="Enable images to see number" name="captcha_image" id="captcha_image">
				<div class="btn btn-default" data-toggle="tooltip" title="Click to change image." style="margin-top:20px;"><span class="glyphicon glyphicon-refresh" aria-hidden="true" onclick="reloadImg(\'captcha_image\'); return false;"></span></div>
			</div>
		</div>
	</div>
	<div class="row" style="margin-bottom:0px;">
		<div class="form-group has-feedback">
			<label class="control-label col-md-4 visible_on_big_screen" for="captcha">Enter code:</label>
			<div class="col-md-8 inputGroupContainer" style="" >
				<input type="text" name="captcha" id="captcha" value="'.$_POST['captcha'].'" class="form-control" placeholder="Enter verification code above" required="required" > 
				<span class="glyphicon form-control-feedback"></span>	
				<span class="description">To avoid spam and automatic registration we ask you to repeat the code you see in the picture.</span>
			</div>
		</div>
	</div>
</form>


<script language="JavaScript" src="/'.(DIR_WS_SERVICES_DIR).'af_track.php" type="text/javascript"></script>
<SCRIPT language="JavaScript"> 

var ref_affiliateid = "";
var redirect_after_login = "/";
signup_function_on_login_performed = "login_after_signup_performed";

function validate_signup_obj(obj_name, error_message)
{	
	var tmp_obj = document.getElementById(obj_name);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_name).parents(".form-group");
		var glyphicon = formGroup.find(".form-control-feedback");
		if (tmp_obj.checkValidity() && tmp_obj.value.length > 0 && "'.($box_message).'".indexOf(error_message) < 0 ) {
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

function validate_signup_password2()
{
	var password_obj = document.getElementById("password2");
	if ( password_obj ) {
		var valid = $("#password").val() == $("#password2").val();
		var formGroup = $("#password2").parents(".form-group");
		var glyphicon = formGroup.find(".form-control-feedback");
		if ( valid && validate_signup_obj("password", "first password") ) {
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

function validate_signup_captcha()
{
	var password_obj = document.getElementById("captcha");
	if ( password_obj ) {
		var formGroup = $("#captcha").parents(".form-group");
		var glyphicon = formGroup.find(".form-control-feedback");
		if ( '.(is_integer(strpos($box_message, 'verification code'))?'1':'0').' ) {
			formGroup.addClass("has-error").removeClass("has-success");
			glyphicon.addClass("glyphicon-remove").removeClass("glyphicon-ok");
			return false;
		}
	}
}

$( document ).ready(function() {
	$("#password").click(function() {
		validate_signup_obj("email", "email");
	});
	$("#password2").click(function() {
		validate_signup_obj("email", "email");
		validate_signup_obj("password", "first password");

	});
	$("#firstname").click(function() {
		validate_signup_obj("email", "email");
		validate_signup_obj("password", "first password");
		validate_signup_password2();
	});
	$("#lastname").click(function() {
		validate_signup_obj("email", "email");
		validate_signup_obj("password", "first password");
		validate_signup_password2();
		validate_signup_obj("firstname", "first name");
	});
	$("#country").click(function() {
		validate_signup_obj("email", "email");
		validate_signup_obj("password", "first password");
		validate_signup_password2();
		validate_signup_obj("firstname", "first name");
		validate_signup_obj("lastname", "last name");
	});
	$("#captcha").click(function() {
		validate_signup_obj("email", "email");
		validate_signup_obj("password", "first password");
		validate_signup_password2();
		validate_signup_obj("firstname", "first name");
		validate_signup_obj("lastname", "last name");
		validate_signup_obj("country", "country");
	});
});

function login_after_signup_performed()
{
	document.location.assign(redirect_after_login);
}

function login_after_success_signup()
{
	if ( java_on_login_performed.length == 0 )
		java_on_login_performed = signup_function_on_login_performed;
	login($("#email").val(), "", $("#password").val(), $("#password2").val(), false);
}

function signup_btn_clicked()
{
	var res = validate_signup_obj("email", "email") && validate_signup_obj("password", "first password") && validate_signup_password2() && validate_signup_obj("firstname", "first name") && validate_signup_obj("lastname", "last name") && validate_signup_obj("country", "country");
	if (!res) {
		on_message_box_hide_func = "show_signup_dialog";
		show_message_box_box("Error", "Please fill in all requered fields", 2); 
	}
	else {
		show_wait_box_box("Registering. Please wait...");
		$.ajax({
			method: "POST",
			url: "/signup.php",
			data: { form_submitted: "1", popup_form: "1", captcha: $("#captcha").val(), email: $("#email").val(), password: $("#password").val(), password2: $("#password2").val(), firstname: $("#firstname").val(), lastname: $("#lastname").val(), country: $("#country").val(), parentid: $("#parentid").val() }
		})
		.done(function( ajax__result ) {
			hide_wait_box_box();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				on_message_box_hide_func = "login_after_success_signup";
				show_message_box_box("Success", arr_ajax__result["message"], 1); 
			}
			else {
				on_message_box_hide_func = "show_signup_dialog";
				show_message_box_box("Error", arr_ajax__result["message"], 2); 
			}
		});
	}
}

function login_btn_clicked()
{
	do_login();
}

</script>

', //$popup_body
'signup_btn_clicked()', //$yes_js
'New Member Registration', //$title
'<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Signup', //$button_yes_caption
'btn-link', //$button_cancel_class
'Have an account? Log in...', //$button_cancel_caption
'login_btn_clicked();', //$cancel_js
'email', // $focused_id
'', // $modal_dialog_style
( defined('REDIRECT_SIGNUP') && REDIRECT_SIGNUP != '' ? 'location.assign("'.REDIRECT_SIGNUP.'"); return false;':'' ) // $on_show_js
).'
';

require_once(DIR_WS_INCLUDES.'box_login.php');
echo get_login_script(false);
echo get_dialog_body(false);

require_once(DIR_COMMON_PHP.'box_message.php');
require_once(DIR_COMMON_PHP.'box_wait.php');

?>