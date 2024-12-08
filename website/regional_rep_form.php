<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
if ( !$user_account->can_apply_for_regional_rep() ) {
	header('Location: /careers/');
	exit;	
}

$box_message = '';

if ( $_POST['cancel_reg_rep'] == '1' ) {
	if ( $user_account->is_loggedin() && $_POST['userid'] == $user_account->userid ) {
		$user_account->cancel_regional_rep();
		$box_message = 'Position of Regional Representative has been cancelled.';		
	}
}
if ( empty($_POST['country']) ) 
	$_POST['country'] = $user_account->country;
if ( empty($_POST['city']) ) 
	$_POST['city'] = $user_account->city;
if ( empty($_POST['languages']) ) 
	$_POST['languages'] = $user_account->languages;
if ( empty($_POST['website']) ) 
	$_POST['website'] = $user_account->website;
if ( empty($_POST['phone']) ) 
	$_POST['phone'] = $user_account->phone;

$page_to_show = 0;
if ( !empty($_POST['form_submitted']) ) {
	if ( empty($user_account->photo) )
		$box_message = 'Error: you have to upload your photo';
	if ( empty($box_message) )
		$box_message = $user_account->convert_to_regional_rep($_POST['country'], $_POST['city'], $_POST['languages'], $_POST['website'], $_POST['phone']);
	if ( empty($box_message) )
		$page_to_show = 1;
}

$page_header = 'Regional Representative';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

switch ($page_to_show) {
    case 1:
		echo show_intro('/images/regional_reps_512x512.png', '
		<h2>Congratulations!</h2><br>
		Now your contact information is included in the list of our local representatives.<br>
		In the very short time you will start receiving calls from referals who need help in your area.<br>

		<b>The tipical questions are:</b><br>
		<br>
		<b>Referals will ask you what are benefits from purchasing '.WORD_MEANING_SHARE.'s and how to pay for them.</b><br>
		You can easily answer this question using our <a href="acc_faqs.php" target="_blank">FAQs</a> web page.<br>
		<br>
		If referals do not have account you will create account for them. Do not forget that you will receive <b>'.number_format(AFFILIATE_COMMISSION * 100, 0).'%</b> commission from every purchase they made.<br>
		<br>
		<b>Also they will ask you about purchasing '.WORD_MEANING_SHARE.'s for cash.</b><br>
		When you are receiving cash from your referals you must proceed with the procedure described below:<br>
		<b>1.</b> You exchange the received cash to electronic funds like Perfect Money or Bitcoin, and then buy '.WORD_MEANING_SHARE.'s on your name.<br>
		<b>2.</b> Then you open account for your referal (if he/she does not have one).<br>
		<b>3.</b> After that, you will transfer these '.WORD_MEANING_SHARE.'s to the account of your referal.<br>
		<br>
		If you have any questions please use "<a href="/contactus.php" target="_blank">Contact Us</a>" form.<br>
		<br>
		');
	break;
	default:
		if( !$user_account->is_regional_rep() ) 
			echo show_intro('/images/regional_reps_512x512.png', 'We are looking for regional representatives to promote our service in languages around the world. The Regional Representative status is an opportunity for experienced partners who are able to promote our services in their regions.<br>
		<br>
		<b>Regional Representative benefits:</b>
		<ul class="ul">
		<li>Your contact information will be listed in the list of our regional representatives and distributed to persons inquiring about our services in your area.</li>
		<li>Increased partnership revenue.</li>
		<li>Increased number of referals.</li>
		</ul>
		<b>Earnings of the Regional Representative:</b><br>
		The Regional Representative income comes as <b>'.number_format(AFFILIATE_COMMISSION * 100, 0).'%</b> commission from every purchase made by his/her referal'.(COST_OF_VISITOR > 0?' plus <b>'.currency_format(COST_OF_VISITOR).'</b> for every unique visitor that comes from the Representative web site':'').'.<br>
		<br>
		In order to become a Regional Representative, you need to fill in the form below:', 'alert-info');
		?>
		<SCRIPT LANGUAGE="JavaScript">
		function upload_image(upload_form)
		{
			show_hide_obj("wait_image_upload", 1);
			show_hide_obj("upload_photo", 0);
			upload_form.submit();
		}
		</SCRIPT>
		<div class="container row" style="width:90%; margin:0 auto 0 auto; /*border: solid 1px #ff0000;*/">
			<div class="col-md-9" style="">
				<form method="post" name="user_frm" enctype="multipart/form-data" class="form-horizontal">
					<input type="hidden" name="form_submitted" value="1">
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
							<label class="control-label col-md-4" for="email">City:</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<input type="text" name="city" id="city" value="<?php echo !empty($_POST['city'])?$_POST['city']:$user_account->city; ?>" class="form-control" placeholder="Your City" > 
								<span class="glyphicon form-control-feedback"></span>	
							</div>
						</div>
					</div>
					
					<div class="row" style="margin-bottom:0px;">
						<div class="form-group has-feedback">
							<label class="control-label col-md-4" for="url" style="text-transform:capitalize;">Spoken languages: <font color="#FF0000">*</font><br>
							<span class="description"></span>
							</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<input type="text" name="languages" id="languages" class="form-control" readonly value="<?php echo $_POST['languages']; ?>" style="cursor:pointer;" onclick="show_languages(this);" placeholder="Languages that you speak" required="required">
									<span class="input-group-btn">
										<button class="btn btn-success" onclick="return show_languages(this);">Select Language...</button>
									</span>
								</div>
								
							</div>
						</div>
					</div>

					<div class="row" style="margin-bottom:0px;">
						<div class="form-group has-feedback">
							<label class="control-label col-md-4" for="url" style="text-transform:capitalize;">Your Website: <font color="#FF0000">*</font><br>
							<span class="description">Example: http://www.mywebsite.com</span>
							</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<span class="input-group-addon" ><span class="glyphicon glyphicon-globe" aria-hidden="true"></span></span>
									<input type="text" name="website" id="website" maxlength="128" class="form-control" value="<?php echo $_POST['website']; ?>" style="" onBlur="website_changed(this);" placeholder="Address of your Website" >
								</div>
								<img src="/images/wait64x64.gif" style="display:none;" id="wait_url_check" width="16" height="16" border="0" alt="">
								<p class="description" style="padding-left:5px; display:none; color:#ff0000" id="message_about_bad_url">Error reading this website. Is this address correct?</p>
							</div>
						</div>
					</div>
					<div class="row" style="margin-bottom:0px;">
						<div class="form-group has-feedback">
							<label class="control-label col-md-4" for="email">Phone:</label>
							<div class="col-md-8 inputGroupContainer" style="" >
								<div class="input-group">
									<span class="input-group-addon" ><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span></span>
									<input type="text" name="phone" id="phone" value="<?php echo !empty($_POST['phone'])?$_POST['phone']:$user_account->phone; ?>" class="form-control" placeholder="Your Phone" > 
								</div>
								<span class="glyphicon form-control-feedback"></span>	
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-8" style="text-align:left;" >
							<button id="continue_btn" name="continue_btn" class="btn btn-primary btn-lg"><?php echo ( $user_account->is_regional_rep()?'Save':'Apply...'); ?></button>
						</div>
					</div>
				</form>
				<?php
				if ( $user_account->is_regional_rep() ) {
					echo '
					<div class="row">
						<div class="col-md-4"></div>
						<div class="col-md-8" style="text-align:left;" >
							<form method="post" style="display:inline;">
								<input type="hidden" name="userid" value="'.$user_account->userid.'">
								<input type="hidden" name="cancel_reg_rep" value="1">
								<button class="btn btn-danger btn-sm" style="" onClick="return ( confirm(\'Do you really want to cancel your representation?\') );">Cancel Regional Representation</button>
							</form>
						</div>
					</div>
					';
				}
				?>
			</div>
			<div class="col-md-3" style="/*border: solid 1px #00ff00;*/">
				<iframe width="100%" height="230" frameborder="0" scrolling="no" SRC="/acc_personal_details.php?show_avatar=1"></iframe>
			</div>
		</div>
		
		<!--SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
		<SCRIPT language="JavaScript"> 
		var frmvalidator = new Validator("user_frm", "continue_btn");
		frmvalidator.addValidation("country","req","Please enter your Country");
		frmvalidator.addValidation("city","req","Please enter your City");
		frmvalidator.addValidation("languages","req","Please enter the language you speak");
		</SCRIPT-->
		<?php
		$max_name_lenght = 12;
		$number_of_cols = 5;

		$popup_body = '<table class="_table" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';
		for ($i = 1; $i <= $number_of_cols; $i++)
			$popup_body = $popup_body.'<td width="100"></td>';
		$popup_body = $popup_body.'</tr>';
		
		$languages = get_api_value('get_languages');
		$row = 0;
		$column = 0;
		foreach ( $languages as $language ) {
			if ( $column == 0 )
				$popup_body = $popup_body."<tr>\r\n";
			$language_name = ucfirst($language['language']);
			if ( strlen($language_name) > $max_name_lenght  )
				$language_name = substr($language_name, 0, $max_name_lenght).'...';
			
			$popup_body = $popup_body.'<td><p class="description"><input type="checkbox" name="banner_language[]" value="'.$language['language'].'" title="'.ucfirst($language['language']).'"';
			if ( is_integer(strpos(strtolower($_POST['languages']), $language['language'])) )
				$popup_body = $popup_body.' checked ';
			$popup_body = $popup_body.' style="margin-right:4px;">'.$language_name.'</p></td>'."\r\n";
			
			if ( $column >= $number_of_cols - 1 ) {
				$popup_body = $popup_body."</tr>\r\n";
				$column = 0;
			}
			else
				$column = $column + 1;
		}
		$popup_body = $popup_body.'</table>';
		echo generate_popup_code('languages_box', $popup_body, 'select_languages_ok()', 'Select Language / Languages', '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>');
	break;
}
?>

<SCRIPT language="JavaScript"> 
function show_languages(element)
{
	show_languages_box();
	return false;
}

function select_languages_ok()
{
	var new_countries = "";
	var lists = document.getElementsByTagName("input");
	for (var i = 0; i < lists.length; i++) {
		if ( lists[i].name.indexOf("banner_language") >= 0 && lists[i].checked ) {
			if ( new_countries.length > 0 )
				new_countries = new_countries + ", ";
			s = lists[i].value.charAt(0).toUpperCase() + lists[i].value.slice(1);
			new_countries = new_countries + s;
		}
	}
	$("#languages").val(new_countries);
	return true;
}

var URL_ok = true;
var name_ok = true;
var website_url = "";

function website_changed(website_obj)
{
	if ( website_url != website_obj.value ) {
		website_url = website_obj.value;
		if ( website_url.length > 0 ) {
			if ( website_url.indexOf("://") < 0 )
				website_url = "http://" + website_url;
			website_obj.value = website_url;
			check_webpage(website_url);
		}
	}
}

function check_webpage(webpage_url)
{
	$("#wait_website_check").show();
	$.ajax({
		method: "POST",
		url: "/api/check_webpage/" + string_to_hex(webpage_url) + "/1",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>"}
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			URL_ok = arr_ajax__result["success"];
			if ( URL_ok ) {
				$("#message_about_bad_url").hide();
			}
			else {
				$("#message_about_bad_url").show();
				$("#bad_url_message").html(arr_ajax__result["message"]);
			}
			$("#wait_website_check").hide();
		}
		catch(error){
			$("#wait_website_check").hide();
			<?php echo (defined('DEBUG_MODE')?'alert(error);':''); ?>
		}
	});
}

</script>

<?php
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<script language="JavaScript">show_message_box_box("Error", "'.$box_message.'", 2);</script>'."\r\n";
	else
		echo '<script language="JavaScript">show_message_box_box("Success", "'.$box_message.'", 1);</script>'."\r\n";
}
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>