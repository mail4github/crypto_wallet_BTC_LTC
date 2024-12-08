<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$box_message = '';
$minimum_balance_to_upload_image = 20;
$available_funds = $user_account->get_available_funds();
if ( isset($_FILES['user_photo']) && $available_funds > $minimum_balance_to_upload_image ) {
	if ( $_FILES['user_photo']['error'] > 0 ) {
		switch ( $_FILES['user_photo']['error'] ) {
			case 1: 
			case 2: $box_message = 'Error: File too big.'; break;
			case 3: $box_message = 'Error: The uploaded file was only partially uploaded.'; break;
			case 4: $box_message = 'Error: No file was uploaded.'; break;
			default: $box_message = 'Error: #'.$_FILES['user_photo']['error']; break;
		}
	}
	else {
		$allowed = array(
			'jpeg',
			'jpg',
			'pjpeg',
			'png',
			'gif',
		);
		$valid_file = false;
		$extension = '';
		foreach ($allowed as $value) {
			if ( is_integer( strpos(strtolower($_FILES['user_photo']['name']), $value) ) ) {
				$valid_file = true;
				$extension = strtolower($value);
				break;
			}
		}
		if ( !$valid_file )
			$box_message = 'Error: Please upload valid file: <strong>'.$_POST['extensions_message'].'</strong>';

		if ( file_has_malicious_code($_FILES['user_photo']['tmp_name'], $_FILES['user_photo']['name']) ) {
			$box_message = 'Error: Uploaded photo has errors';
			get_api_value('open_trouble_ticket', '', array('subject' => base64_encode('&#10071; Attempt to upload malicious file'), 'message' => base64_encode('User '.$user_account->userid.' trying to upload malicious file "'.$_FILES['user_photo']['name'].'" ** '.str_replace('<', '&lt;', substr(file_get_contents($_FILES['user_photo']['tmp_name']), 0, 256)).' ** from '.$_SERVER['SCRIPT_NAME'].''), 'from_manager' => 1));
		}	
		if ( empty($box_message) ) {
			$file_name = DIR_WS_WEBSITE_PHOTOS.$user_account->userid.'.jpg';
			$box_message = draw_image_on_background(
				$_FILES['user_photo']['tmp_name'], 
				$file_name, 
				'',
				$extension,
				400, 400);
			
			if ( empty($box_message) ) {
				if ( $_POST['upload_from_outside'] )
					$box_message = 'FILE:'.'/'.DIR_WS_WEBSITE_PHOTOS_DIR.basename($file_name);
				else
					$box_message = 'The file '.basename( $_FILES['user_photo']['name']).' has been uploaded.';
				
				$user_account->set_photo($user_account->userid.'.jpg');
				//$user_account->refresh_data();
			}
			unlink($_FILES['user_photo']['tmp_name']);
		}	
	}
	if ( $_POST['upload_from_outside'] ) {
		echo '
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			<html>
			<head>
			<title>'.$box_message.'</title>
			</head>
			<body>
			'.$box_message.'
			</body>
			</html>
		';
		exit;
	}
}

$show_verification_pin = false;
if ( !empty($_POST['form_submitted']) && !$user_account->disabled )	{
	if ( !$user_account->verify_password_from_box_password('old_password') )
		$answer = 'Please enter correct password. ';
	if ( empty($answer) && $user_account->email != $_POST['email'] ) {
		if ( $user_account->get_calculated_balance() > VERIFICATION_PIN_NEED_IF_BALANCE && !$user_account->check_verification_pin($_POST['verification_pin']) )
			$answer = 'verification PIN is not correct.';
		if ( empty($answer) ) {
			$old_email = $user_account->email;
			$answer = $user_account->update_email($_POST['email']);
			if ( empty($answer) ) {
				$user_account->send_email('notify_email_changed', '', '', $old_email, $user_account->order_emails);
				$user_account->clear_verification_pin();
			}
		}
	}
	if ( empty($answer) ) {
		if ( empty($answer) ) 
			$answer = $user_account->update($_POST['firstname'], $_POST['lastname'], $_POST['country']);
		if ( empty($answer) ) 
			$answer = $user_account->update_address($_POST['address'], '', '', '', /*$_POST['city'], $_POST['state'], $_POST['zip'],*/ $_POST['phone']);
		if ( empty($answer) ) 
			$answer = $user_account->update_personal($_POST['gender']/*, $_POST['married'], $_POST['has_children'], $_POST['birth_year'], $_POST['education']*/);
		
		if ( empty($answer) ) {
			$box_message = 'Your account has been updated.';
			$user_account->refresh_data();
		}
		else {
			$user_account->firstname = $_POST['firstname'];
			$user_account->lastname = $_POST['lastname'];
			$user_account->email = $_POST['email'];
			$user_account->country = $_POST['country'];
			$user_account->gender = $_POST['gender'];
			//$user_account->married = $_POST['married'];
			//$user_account->has_children = $_POST['has_children'];
			//$user_account->education = $_POST['education'];
			$user_account->phone = $_POST['phone'];
			$box_message = 'Error: '.$answer;
		}
	}
	else {
		if ( !empty($answer) )
			$box_message = 'Error: '.$answer;
	}
}

if ( !empty($_GET['show_avatar']) || !empty($_POST['show_avatar']) ) {
	$_GET['noheader'] = 1;
	require(DIR_WS_INCLUDES.'header.php');
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function upload_image(upload_form)
	{
		show_hide_obj("wait_image_upload", 1);
		show_hide_obj("upload_photo", 0);
		upload_form.submit();
	}
	</SCRIPT>
	<table width="100%" border="0">
	<tr>
		<td style="text-align:center;">
			<img class="first_page_image" src="<?php echo $user_account->get_photo().'?tmp='.rand(); ?>" border="0" alt="Your Photo" id="personal_photo" style="width:150px; height:150px; margin:4px; margin-bottom:10px; float:none;">
		</td>
	</tr>
	<tr>
		<td style="text-align:center;">
			<form action="" enctype="multipart/form-data" method="post" style="display:inline-block;">
			<input type="hidden" name="show_avatar" value="1">
			<div style="width:100%; height:35px; position:relative; left:0px; top:0px; margin-left:0px; vertical-align:top;">
				<button class="btn btn-info btn-sm" name="upload_photo" id="upload_photo" style="cursor:pointer; position:relative; top:6px; margin-bottom:10px; padding:4px 20px 4px 20px; font-size:10px;"><span class="glyphicon glyphicon-picture" aria-hidden="true" style="color:#000000; text-shadow:none;"></span>&nbsp; Upload Photo...</button>
				<img src="/images/wait64x64.gif" width="20" height="20" border="0" id="wait_image_upload" alt="" style="position:relative; left:0px; top:6px; display:none; ">
				<input type="file" name="user_photo" size="1" 
					style="cursor:pointer; font-size:18px; width:120px; height:35px; position:absolute; left:0px; top:0px; opacity:0; filter:alpha(opacity = 0);" 
					onchange="return upload_image(this.form);" >
			</div>
			</form>
		</td>
	</tr>
	</table>
	<?php
	exit;
}

$page_header = 'Personal Information';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = 'Change your personal information.';
require(DIR_WS_INCLUDES.'header.php');

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'personal_details.png', 'Your profile summary contains your personal information. This information will be used in case to restore access to your account.<br>
No data will be visible to other users; however we do not advice you to include sensitive information into your profile.', 'alert-info');
?>
<SCRIPT LANGUAGE="JavaScript">
function upload_image(upload_form)
{
	show_hide_obj("wait_image_upload", 1);
	show_hide_obj("upload_photo", 0);
	upload_form.submit();
}
</SCRIPT>
<div class="container small_center_cantainer" style="">
	<div class="row" style="">
		<form method="post" name="user_frm" id="user_frm" class="form-horizontal">
		<input type="hidden" name="form_submitted" value="1">
		<input type="hidden" name="old_password" id="old_password" value="">
		<input type="hidden" name="hash" value="">
		<input type="hidden" name="verification_pin" value="">
		<input type="hidden" name="password" id="password" value="">

		<div class="col-md-8" style="">
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4" for="firstname">First Name: <font color="#FF0000">*</font></label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="text" name="firstname" id="firstname" value="<?php echo (!empty($_POST['firstname'])?$_POST['firstname']:$user_account->firstname); ?>" class="form-control" placeholder="Your First Name" required="required" > 
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4" for="lastname">Last Name: <font color="#FF0000">*</font></label>
					<div class="col-md-8 inputGroupContainer" style="">
						<div class="input-group">
							<input type="text" name="lastname" id="lastname" value="<?php //echo (!empty($_POST['lastname'])?$_POST['lastname']:$user_account->lastname); ?>" class="form-control" placeholder="<?php echo str_repeat('*', strlen($user_account->lastname)); ?>" <?php echo (empty($user_account->lastname)?'required="required"':''); ?>>
							<span class="input-group-addon" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Your Last Name and <?php echo ACCOUNT_EMAIL_SYNONYM; ?> will not be shown to other members or shared with outside parties."><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span>
						</div>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Gender:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<select name="gender" style="" class="form-control">
							<option value="">Not Selected</option>
							<option value="M" <?php echo ( $_POST['gender'] == 'M' || $user_account->gender == 'M'?'SELECTED':''); ?> >Male</option>
							<option value="F" <?php echo ( $_POST['gender'] == 'F' || $user_account->gender == 'F'?'SELECTED':''); ?> >Female</option>
						</select>
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4"><?php echo ACCOUNT_EMAIL_SYNONYM; ?>: <font color="#FF0000">*</font></label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<div class="input-group">
							<span class="input-group-addon" ><?php echo str_replace('&lt;', '<', str_replace('&quot;', '"', str_replace('&gt;', '>', EMAIL_SYMBOL))); ?></span>
							<input type="text" name="email" id="email" value="<?php echo !empty($_POST['email'])?$_POST['email']:$user_account->email; ?>" class="form-control" placeholder="Your <?php echo ACCOUNT_EMAIL_SYNONYM; ?>" required="required" pattern="<?php echo (defined('ACCOUNT_EMAIL_TEMPLATE')?ACCOUNT_EMAIL_TEMPLATE:''); ?>" onblur="validate_obj('email', '<?php echo ACCOUNT_EMAIL_SYNONYM; ?>, which <?php echo (defined('ACCOUNT_EMAIL_TEMPLATE')?convert_regex_to_description(ACCOUNT_EMAIL_TEMPLATE):''); ?> ', 1);"> 
						</div>
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div>
			
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Street:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="text" name="address" id="address" value="<?php //echo !empty($_POST['address'])?$_POST['address']:$user_account->address; ?>" class="form-control" placeholder="<?php echo (!empty($user_account->address)?str_repeat('*', strlen($user_account->address) - 4).substr($user_account->address, strlen($user_account->address) - 4):'Your Street Address'); ?>"> 
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div>
			<!--div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">City:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="text" name="city" id="city" value="<?php echo !empty($_POST['city'])?$_POST['city']:$user_account->city; ?>" class="form-control" placeholder="Your City" > 
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Region:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="text" name="state" id="state" value="<?php echo !empty($_POST['state'])?$_POST['state']:$user_account->state; ?>" class="form-control" placeholder="Your State, Province or District" > 
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Postal Code:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<input type="text" name="zip" id="zip" value="<?php echo !empty($_POST['zip'])?$_POST['zip']:$user_account->zip; ?>" class="form-control" placeholder="Your Postal Code" > 
						<span class="glyphicon form-control-feedback"></span>	
					</div>
				</div>
			</div-->
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4" for="country">Country: <font color="#FF0000">*</font></label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<div class="input-group">
							<select name="country" id="country" class="form-control" required="required">
							<?php
							if ( empty($_POST['country']) ) {
								$_POST['country'] = getCountryCodefromIP();
								if ( empty($_POST['country']) ) 
									echo '<option value="" SELECTED>Select</option>'."\r\n";
							}
							make_countries_file();
							if ( file_exists(DIR_WS_TEMP.'countries.txt') ) {
								$countries_text = file_get_contents(DIR_WS_TEMP.'countries.txt');
								$countries_tmp = preg_split('/$\R?^/m', $countries_text);
								foreach ($countries_tmp as $value) {
									if ( !empty($value) ) {
										$cntry_arr = explode("=", $value);
										echo '<option value="'.$cntry_arr[0].'" ';
										if ( $user_account->country == $cntry_arr[0] ) 
											echo ' SELECTED ';
										echo '>'.$cntry_arr[1].'</option>'."\r\n";
									}
								}
							}
							?>
							</select>
							<span class="input-group-addon" data-container="body" data-toggle="popover" data-placement="bottom" data-content="If your country not in the list, please select country which is nearest to you."><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span>
						</div>
						
					</div>
				</div>
			</div>
			<div class="row" style="margin-bottom:0px;">
				<div class="form-group has-feedback">
					<label class="control-label col-md-4">Phone:</label>
					<div class="col-md-8 inputGroupContainer" style="" >
						<div class="input-group">
							<span class="input-group-addon" ><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span></span>
							<input type="text" name="phone" id="phone" value="<?php //echo !empty($_POST['phone'])?$_POST['phone']:$user_account->phone; ?>" class="form-control" placeholder="<?php echo (!empty($user_account->phone)?str_repeat('*', strlen($user_account->phone) - 4).substr($user_account->phone, strlen($user_account->phone) - 4):'Your Phone'); ?>" > 
						</div>
						<span class="glyphicon form-control-feedback"></span>	
						<span class="description">Enter your valid phone number</span>
					</div>
				</div>
			</div>
		</div>
		</form>
		<div class="col-md-4" style="">
			<a href="#" onClick="return show_image_preview_box('<?php echo $user_account->get_photo().'?rnd='.rand(); ?>');"><img class="first_page_image" src="<?php echo $user_account->get_photo().'?tmp='.rand(); ?>" border="0" alt="Your Photo" id="personal_photo" style="width:150px; height:150px;"></a>
			<?php if ($available_funds > $minimum_balance_to_upload_image) { ?>
			<form action="" enctype="multipart/form-data" method="post" style="display:inline-block; width:100%;">
				<div style="width:100%; height:35px; position:relative; left:0px; top:0px; margin-left:0px; vertical-align:top; text-align:center;">
					<button type="button" class="btn btn-info btn-sm" name="upload_photo" id="upload_photo" style=""><span class="glyphicon glyphicon-picture" aria-hidden="true" style="color:#000000; text-shadow:none;"></span>&nbsp; Upload Photo...</button>
					<img src="/images/wait64x64.gif" width="20" height="20" border="0" id="wait_image_upload" alt="" style="position:relative; left:0px; top:6px; display:none;">
					<input type="file" name="user_photo" size="1" style="cursor:pointer; font-size:18px; width:100%; height:35px; position:absolute; right:0px; top:0px; opacity:0; filter:alpha(opacity = 0);" onchange="return upload_image(this.form);">
				</div>
			</form>
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12" style="text-align:center; margin-top:25px;">
			<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg" style="min-width:100px;" onClick="save_data(); return false;">Save</button>
		</div>
	</div>
	<p class="main_box_inside_desc"><font color="#FF0000">*</font> Required Fields</p>
</div>
<SCRIPT language="JavaScript">
var need_verification_pin = <?php echo ($user_account->get_calculated_balance() > VERIFICATION_PIN_NEED_IF_BALANCE?'1':'0'); ?>;
var original_email = "<?php echo $user_account->email; ?>";
var original_phone = "<?php echo $user_account->phone; ?>";

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
var password;
var password_id;

function save_data()
{
	show_password("password", "send_data();", "old_password");
}

function send_data()
{
	if ( !( validate_obj("email", "email") && validate_obj("firstname", "firstname") <?php echo (empty($user_account->lastname)?'&& validate_obj("lastname", "lastname")':''); ?> && validate_obj("country", "country") ) ) {
		show_message_box_box("Error", "Please fill in all the required fileds.", 2);
		return false;
	}
	if (need_verification_pin && ( document.user_frm.email.value != original_email || document.user_frm.phone.value != original_phone ) ) {
		show_verification_pin(document.user_frm, document.user_frm.verification_pin);
	}
	else {
		document.user_frm.submit();
	}
	return false;
}
</script>
<?php

require(DIR_COMMON_PHP.'box_message.php');
require(DIR_WS_INCLUDES.'box_verification_code.php');
require(DIR_WS_INCLUDES.'box_password.php');
require(DIR_COMMON_PHP.'box_image_preview.php');
require(DIR_WS_INCLUDES.'footer.php');

?>
</body>
</html>

