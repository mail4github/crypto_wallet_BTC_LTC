<?php
require('../includes/application_top.php');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'new_signup.php') ) {
	require(DIR_WS_TEMP_CUSTOM_CODE.'new_signup.php');
	exit;
}

$showDebugMessages = defined('DEBUG_MODE');
if ( $showDebugMessages )
	error_reporting(E_ALL);

if ( $user_account->is_loggedin() ) {
	header('Location: /acc_main.php');
	exit;
}
$show_signup = true;

$redirect_signup = defined('REDIRECT_SIGNUP') && REDIRECT_SIGNUP != '' && ( !defined('REDIRECT_SIGNUP_COUNTRIES') || REDIRECT_SIGNUP_COUNTRIES == "" || is_integer(strpos(REDIRECT_SIGNUP_COUNTRIES, getCountryCodefromIP())) );

if ( !empty($_POST['form_submitted']) ) {
	$box_message = '';
	if ( empty($_POST['skip_captcha']) && ( strtolower($_POST['captcha']) != xor_decrypt('captcha psw', $_COOKIE['cpc']) || empty($_COOKIE['cpc']) ) ) {
		$box_message = 'verification code';
	}
	else {
		if ( $redirect_signup ) {
			$born_domain = $_COOKIE[TRACK_COOKIE_DOMAIN];
			if ( empty($born_domain) || empty($_POST['parentid']) ) {
				$parentid_rec = $user_account->get_parentid_by_ip($_SERVER['REMOTE_ADDR']);
				if ( $parentid_rec ) {
					if ( empty($born_domain) ) 
						$born_domain = base64_decode($parentid_rec['domain']);
					if ( empty($_POST['parentid']) )
						$_POST['parentid'] = $parentid_rec['userid'];
				}
				if (empty($born_domain)) 
					$born_domain = SITE_SHORTDOMAIN;
			}
			$post_data = '';
			foreach ($_POST as $key => $value)
				$post_data = $post_data.$key.'='.urlencode($value).'&';
			
			$post_data = 'form_submitted=1&popup_form=1&skip_captcha=1&parent_website='.urlencode(SITE_SHORTDOMAIN).'&signup_ip='.urlencode($_SERVER['REMOTE_ADDR']).'&user_domain='.urlencode($born_domain).'&'.$post_data;
			//if ($showDebugMessages) {echo generate_json_answer(0, "post_data: $post_data"); exit;}
			$res = do_post_request(REDIRECT_SIGNUP, $post_data);
			$res_json = json_decode($res, true);
			if ( !$res_json["success"] )
				$box_message = $res_json["message"];
		}
		else {
			$tmp_user = new User();
			$box_message = $tmp_user->signup($_POST['email'], $_POST['hashed_password'], $_POST['firstname'], $_POST['lastname'], $_POST['country'], $_POST['parentid'], '', true, true, '', $_POST['signup_ip'], $_POST['user_domain'], $_POST['parent_websiteid'], $_POST['parent_website']);
			
			if ( empty($box_message) )
				$show_signup = false;
		}
	}
	if ( !empty($_POST['popup_form']) ) {
		if ( empty($box_message) )
			echo generate_json_answer(1, 'User <b>'.$_POST['email'].'</b> has been created');
		else
			echo generate_json_answer(0, 'Error: please fill in the '.$box_message.'.');
		exit;
	}
	else {
		if ( !empty($box_message) )
			$box_message = 'Error: Please enter correct '.$box_message;
	}
}

foreach ($_POST as $key => $value )
	$_POST[$key] = tep_sanitize_string($value);

$page_header = 'The registration';
$page_title = $page_header;
require(DIR_WS_INCLUDES.'header.php');
echo translate_str('
<div class="container small_center_cantainer" style="max-width:800px;">
	<div class="form-horizontal">
		<input type="hidden" name="form_submitted" value="1">
		<input type="hidden" name="come_from_site" value="'.$_COOKIE['come_from_site'].'">
		<input type="hidden" name="parentid" id="parentid" value=""> 
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">Name:</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<input type="text" name="firstname" id="firstname" value="'.$_POST['firstname'].'" class="form-control string_to_translate" placeholder="Type in your name" required="required" > 
					<span class="glyphicon form-control-feedback"></span>	
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">Surname:</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<div class="input-group">
						<input type="text" name="lastname" id="lastname" value="'.$_POST['lastname'].'" class="form-control string_to_translate" placeholder="Type in your surname" required="required">
						'.show_more_info_popover('Your personal information will not be shown to other members or shared with outside parties.').'
					</div>
				</div>
			</div>
		</div>
		
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">'.ACCOUNT_EMAIL_SYNONYM.':</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<div class="input-group" style="position:relative;">
						<span class="input-group-addon" >'.str_replace('&lt;', '<', str_replace('&quot;', '"', str_replace('&gt;', '>', EMAIL_SYMBOL))).'</span>
						<input type="text" name="email" id="email" value="'.$_POST['email'].'" class="form-control string_to_translate" placeholder="'.ACCOUNT_EMAIL_SYNONYM.' (example: carolsmith)" required="required" pattern="'.(defined('ACCOUNT_EMAIL_TEMPLATE') ? ACCOUNT_EMAIL_TEMPLATE : '').'" onblur="validate_obj(\'email\', \'User Name, which '.(defined('ACCOUNT_EMAIL_TEMPLATE') ? convert_regex_to_description(ACCOUNT_EMAIL_TEMPLATE) : '').' \', 1);">
						<img id="sand_glass_user_name" src="/images/wait64x64.gif" style="position:absolute; width:24px; opacity:0; right:4px; top:4px; z-index:3;">
					</div>
					<span class="glyphicon form-control-feedback"></span>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">Password:</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
						<input type="password" name="password" id="password" value="'.$_POST['password'].'" class="form-control string_to_translate" placeholder="Your password" aria-describedby="at-addon" required="required" pattern=".{6,}" onkeyup="validate_obj(\'password\', \'first password\');"> 
						'.show_more_info_popover('Try to select a password that is easy to remember, but hard to guess for others.').'
					</div>
					'.show_help('<span class="string_to_translate">At least 6 characters, no spaces.</span>').'
					<div class="" id="password_weakness_div" style="display:none;">
						<div class="description text-danger" style="margin-top:10px;" id="password_weakness_description"><span id="password_weakness_name">Weak</span> Password</div>
						<div class="progress" style="width:50%; height:10px; margin:0;">
							<div class="progress-bar progress-bar-danger" role="progressbar" style="width:80%" id="password_weakness_progress"></div>
						</div>
					</div>	
				</div>
			</div>
		</div>
		<div class="row" id="password2_row" style="margin-bottom:0px; display:none;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">Repeat Password:</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<div class="input-group">
						<span class="input-group-addon"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span></span>
						<input type="password" name="password2" id="password2" value="'.$_POST['password2'].'" class="form-control string_to_translate" placeholder="Repeat password one more time" aria-describedby="at-addon" required="required" onkeyup="validate_password2();"> 
					</div>
					<span class="glyphicon form-control-feedback"></span>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom:0px;">
			<div class="form-group has-feedback">
				<label class="control-label col-md-4 visible_on_big_screen string_to_translate">Verification Code:</label>
				<div class="col-md-8 inputGroupContainer" style="" >
					<table style="width:100%; border:none;">
					<tr>
						<td style="vertical-align:top;"><img src="/'.DIR_WS_SERVICES_DIR.'captcha.php?width=650&height=240&char_min_size=100&char_max_size=110" border="0" style="max-width:300px; width:100%; min-width:180px;" alt="Enable images to see number" name="captcha_image" id="captcha_image"></td>
						<td style="vertical-align:top; padding:0 0 0 10px; width:50%;">
							<input type="text" name="captcha" id="captcha" style="width:100%; padding-right:0;" value="" class="form-control string_to_translate" placeholder="Enter code" required="required" autocomplete="off">
							<div class="btn btn-default" data-toggle="tooltip" title="Click to change image." style="margin-top:4px;"><span class="glyphicon glyphicon-refresh" aria-hidden="true" onclick="reloadImg(\'captcha_image\'); return false;"></span></div>
						</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" style="text-align:center;" >
				<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg string_to_translate">Sign up...</button>
				<br><span class="description"><span class="string_to_translate">Sign up is free of charge. By clicking the "Sign up" button you agree that you have read, understand, and accept our:</span> <a href="/tos.php" class="string_to_translate">Terms of Service</a>.</span><br>
				<span class="label label-info" id="parentid_label" style="display:none;"><span class="string_to_translate">Referrer ID:</span> <b id="parentid_text" style="color:#f7ff98;"></b> <span class="string_to_translate">(this is id of user who invited you)</span></span>
			</div>
		</div>
	</div>
</div>');
?>
<script language="JavaScript" src="/<?php echo DIR_WS_SERVICES_DIR; ?>af_track.php" type="text/javascript"></script>
<SCRIPT language="JavaScript"> 
var id_to_pulsate = "";
var last_login_name = "";

if ( typeof ref_affiliateid != "undefined" && ref_affiliateid.length > 0) {
	$("#parentid").val(ref_affiliateid);
	$("#parentid_text").html(ref_affiliateid);
	$("#parentid_label").show();
}

function pulsate_item()
{
	$("#" + id_to_pulsate).effect("pulsate", { times:12 }, 15);
}

function validate_obj(obj_name, error_message, show_error)
{	
	if (obj_name == "password") {
		scorePassword( $("#password").val() );
		$("#password2_row").show();
	}
	if (obj_name == "email") {
		if ( last_login_name !== $("#email").val() )
			$("#email").attr("not_valid", "0");
	}
	var tmp_obj = document.getElementById(obj_name);
	if ( tmp_obj ) {
		var formGroup = $("#" + obj_name).parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if (tmp_obj.checkValidity() && tmp_obj.value.length > 0 && "<?php echo $box_message; ?>".indexOf(error_message) < 0 && $("#" + obj_name).attr("not_valid") !== "1" ) {
			formGroup.addClass("has-success").removeClass("has-error");
			glyphicon.addClass("glyphicon-ok").removeClass("glyphicon-remove");
			if (obj_name == "email")
				validate_login_name();
			return true;
		} 
		else {
			formGroup.addClass("has-error").removeClass("has-success");
			glyphicon.addClass("glyphicon-remove").removeClass("glyphicon-ok");
			if (typeof show_error != "undefined" && show_error ) {
				id_to_pulsate = obj_name;
				show_message_box_box("Error", "Please enter <b>" + error_message + "</b>", 2, "", "pulsate_item");
			}
			return false;
		}
	}
}

function validate_password2(show_error)
{
	var password_obj = document.getElementById("password2");
	if ( password_obj ) {
		var valid = $("#password").val() == $("#password2").val();
		var formGroup = $("#password2").parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if ( valid && validate_obj("password", "first password") ) {
			formGroup.addClass('has-success').removeClass('has-error');
			glyphicon.addClass('glyphicon-ok').removeClass('glyphicon-remove');
			return true;
		} 
		else {
			formGroup.addClass('has-error').removeClass('has-success');
			glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
			if (typeof show_error != "undefined" && show_error ) {
				id_to_pulsate = "password2";
				show_message_box_box("Error", "Please enter the same password one more time", 2, "", "pulsate_item");
			}
			
			return false;
		}
	}
}

function validate_captcha()
{
	var password_obj = document.getElementById("captcha");
	if ( password_obj ) {
		var formGroup = $("#captcha").parents('.form-group');
		var glyphicon = formGroup.find('.form-control-feedback');
		if ( <?php echo (is_integer(strpos($box_message, 'verification code'))?'1':'0'); ?> ) {
			formGroup.addClass('has-error').removeClass('has-success');
			glyphicon.addClass('glyphicon-remove').removeClass('glyphicon-ok');
			return false;
		}
	}
}
function validate_all()
{
	validate_obj("firstname", "first name");
	validate_obj("lastname", "last name");
	validate_obj("email", "User Name");
	validate_obj("password", "first password");
	validate_password2();
	validate_captcha();
}

function reload_page_on_login() 
{
	<?php 
	if ( $redirect_signup ) 
		echo 'document.location.href = "http://'.get_domain(REDIRECT_SIGNUP).'/login.php?email=" + $("#email").val();';
	else
		echo 'document.location.href = "/login.php?email=" + $("#email").val();';
	?>
}

function validate_login_name()
{
	if ( last_login_name == $("#email").val() )
		return false;
	last_login_name = $("#email").val();
	$("#sand_glass_user_name").css("opacity", 1);
	$.ajax({
		method: "POST",
		url: "/api/user_is_user_exist/",
		data: { entered_email: $("#email").val(), token: "<?php echo get_api_token_seed(); ?>", firstname: $("#firstname").val(), lastname: $("#lastname").val() }
	})
	.done(function( ajax__result ) {
		$("#sand_glass_user_name").css("opacity", 0);
		try {
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				if ( arr_ajax__result["values"]["user_exist"] ) {
					$("#email").attr("not_valid", "1");
					id_to_pulsate = "email";
					var suggest_code = "";
					if ( arr_ajax__result["values"]["suggestions"] && arr_ajax__result["values"]["suggestions"]["name0"] ) {
						var j = 0;
						for (var i = 0; i < 50; i++) {
							if ( typeof arr_ajax__result["values"]["suggestions"]["exist_" + i] !== 'undefined' && arr_ajax__result["values"]["suggestions"]["exist_" + i] == "0" && arr_ajax__result["values"]["suggestions"]["name" + i].length > 0 ) {
								suggest_code = suggest_code + "<p><input type=checkbox onclick='select_other_name(\"" + arr_ajax__result["values"]["suggestions"]["name" + i] + "\");'>&nbsp;&nbsp;" + arr_ajax__result["values"]["suggestions"]["name" + i] + "</p>";
								j++;
								if (j > 4)
									break;
							}
						}
					}
					show_message_box_box("Error", "", 2, string_to_hex("<p>An account <b>" + $("#email").val() + "</b> has already been assigned.</p>" + (suggest_code.length > 0 ? "<p>You might like to select one of suggested:</p>" + suggest_code : "<p>Please enter different one.</p>")), "pulsate_item");
					var formGroup = $("#email").parents('.form-group');
					var glyphicon = formGroup.find('.form-control-feedback');
					formGroup.addClass("has-error").removeClass("has-success");
					glyphicon.addClass("glyphicon-remove").removeClass("glyphicon-ok");
				}
				else {
					$("#email").attr("not_valid", "0");
				}
			}
		}
		catch(error){write_console_log(ajax__result + " --- validate_login_name --- " + error);}
	});
}

function select_other_name(other_name)
{
	$("#email").val(other_name);
	$("#email").attr("not_valid", "0");
	$('#message_box_box').modal('hide');
	var formGroup = $("#email").parents('.form-group');
	var glyphicon = formGroup.find('.form-control-feedback');
	formGroup.addClass("has-success").removeClass("has-error");
	glyphicon.addClass("glyphicon-ok").removeClass("glyphicon-remove");
}

$(function() {
	$("#firstname").click(function() {
	});
	$("#lastname").click(function() {
		validate_obj("firstname", "first name");
	});
	$("#email").click(function() {
		validate_obj("firstname", "first name");
		validate_obj("lastname", "last name");
	});
	$("#password").click(function() {
		validate_obj("firstname", "first name");
		validate_obj("lastname", "last name");
		validate_obj("email", "User Name");
	});
	$("#password2").click(function() {
		validate_obj("firstname", "first name");
		validate_obj("lastname", "last name");
		validate_obj("email", "User Name");
		validate_obj("password", "first password");
	});
	$("#captcha").click(function() {
		validate_obj("firstname", "first name");
		validate_obj("lastname", "last name");
		validate_obj("email", "User Name");
		validate_obj("password", "first password");
		validate_password2();
	});
	$("#submit_btn").click(function() {
		if ( 
			validate_obj("firstname", "first name")
			&& validate_obj("lastname", "last name")
			&& validate_obj("email", "User Name", 1)
			&& validate_obj("password", "first password", 1)
			&& validate_password2(1)
		) {
			show_wait_box_box("Registering. Please wait...");
			$.ajax({
				method: "POST",
				url: "/signup.php",
				data: { form_submitted: "1", popup_form: "1", captcha: $("#captcha").val(), email: $("#email").val(), hashed_password: md5($("#password").val()), firstname: $("#firstname").val(), lastname: $("#lastname").val(), country: "", parentid: $("#parentid").val() }
			})
			.done(function( ajax__result ) {
				hide_wait_box_box();
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					on_message_box_hide_func = "reload_page_on_login";
					show_message_box_box("Success", arr_ajax__result["message"], 1); 
				}
				else {
					on_message_box_hide_func = "";
					show_message_box_box("Error", arr_ajax__result["message"], 2); 
				}
			});
		}
		else {
			show_message_box_box("Error", "Please fill in all fields", 2); 
		}
	});
});
</script>
<?php
require(DIR_COMMON_PHP.'box_message.php');
if ( !$show_signup ) {
	$success_message = 'User '.$_POST['firstname'].' '.$_POST['lastname'].' has been created';
	echo '
	<SCRIPT language="JavaScript"> 
	on_message_box_hide_func = "reload_page_on_login";
	</SCRIPT> ';
}

if ( !empty($box_message) )
	echo '<script language="JavaScript">validate_all();</script>'."\r\n";
if ( !empty($success_message) )
	echo '<script language="JavaScript">show_message_box_box("Success", "'.$success_message.'", 1);</script>'."\r\n";

require(DIR_WS_INCLUDES.'footer.php');
require_once(DIR_COMMON_PHP.'box_wait.php');

?>
</body>
</html>