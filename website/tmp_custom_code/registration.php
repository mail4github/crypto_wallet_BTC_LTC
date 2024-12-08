<?php
require('../../includes/application_top.php');
$page_header = 'Sign up an account';
require('auth_header.php');

$box_message = '';

if ( !empty($_POST['form_submitted']) ) {
	$box_message = '';
	if ( empty($_POST['skip_captcha']) && ( strtolower($_POST['captcha']) != xor_decrypt('captcha psw', $_COOKIE['cpc']) || empty($_COOKIE['cpc']) ) ) {
		$box_message = 'verification code';
	}
	else {
        $tmp_user = new User();
        $box_message = $tmp_user->signup($_POST['email'], $_POST['hashed_password'], $_POST['firstname'], $_POST['lastname'], $_POST['country'], $_POST['parentid'], '', true, true, '', $_POST['signup_ip'], $_POST['user_domain'], $_POST['parent_websiteid'], $_POST['parent_website']);
        
        if ( empty($box_message) )
            $show_signup = false;
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

?>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="firstname"><?php echo make_str_translateable('First name'); ?><span style="color: #FF002B;">*</span></label>
        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="Joe">
    </div>
    <div class="form-group col-md-6">
        <label for="lastname"><?php echo make_str_translateable('Last name'); ?><span style="color: #FF002B;">*</span></label>
        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Doe">
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="phone"><?php echo make_str_translateable('Password'); ?><span style="color: #FF002B;">*</span></label>
        <input type="password" class="form-control" name="password" id="password">
    </div>
    <div class="form-group col-md-6">
        <label for="email"><?php echo make_str_translateable('Email'); ?><span style="color: #FF002B;">*</span></label>
        <input type="email" class="form-control" name="email" id="email" placeholder="example@yourmail.com">
    </div>
</div>
<div class="form-group text-center">
    <p class="text-white"><?php echo make_str_translateable('By registering, you agree to the Privacy Policy.'); ?></p>
</div>
<div class="form-group d-flex flex-column align-items-center">
    <div class="d-flex align-items-center">
        <img src="/sr_/captcha.php?width=650&height=240&char_min_size=100&char_max_size=110" alt="Captcha" class="mr-2" name="captcha_image" id="captcha_image" style="width:auto; height:47px;">
        <img src="/tmp_custom_code/images/arrows.png" class="mr-2" onclick="reloadImg(`captcha_image`); return false;" style="cursor:pointer;"></img>
        <input type="text" class="form-control captcha" placeholder="" name="captcha" id="captcha">
    </div>
    <button type="submit" class="btn btn-success btn-block d-flex justify-content-center align-items-center mt-3" name="submit_btn" id="submit_btn"><?php echo make_str_translateable('Register'); ?></button>
</div>

<script>
var id_to_pulsate = "";
var last_login_name = "";

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
					show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "", 2, string_to_hex("<p>An account <b>" + $("#email").val() + "</b> has already been assigned.</p>"), "pulsate_item");
				}
				else {
					$("#email").attr("not_valid", "0");
				}
			}
		}
		catch(error){write_console_log(ajax__result + " --- validate_login_name --- " + error);}
	});
}

function reload_page_on_login() 
{
	<?php 
	echo 'document.location.href = "/login.php?email=" + $("#email").val();';
	?>
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
			//if (obj_name == "email")
				//validate_login_name();
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

$( document ).ready(function() {
    $("#submit_btn").click(function() {
		if ( 
			validate_obj("firstname", "first name")
			&& validate_obj("lastname", "last name")
			&& validate_obj("email", "User Name", 1)
			&& validate_obj("password", "first password", 1)
			) {
			$.ajax({
				method: "POST",
				url: "/signup.php",
				data: { form_submitted: "1", popup_form: "1", captcha: $("#captcha").val(), email: $("#email").val(), hashed_password: md5($("#password").val()), firstname: $("#firstname").val(), lastname: $("#lastname").val(), country: "", parentid: $("#parentid").val() }
			})
			.done(function( ajax__result ) {
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
			show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Please fill in all fields?'); ?>", 2); 
		}
	});
});

</script>

<?php
require('auth_footer.php');
?>
</body>
</html>

