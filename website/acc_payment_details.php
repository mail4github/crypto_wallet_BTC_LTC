<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');

if ( !empty($_GET['send_pin_again']) ) {
	$user_account->clear_verification_pin();
	$user_account->set_verification_pin();
	exit;
}
$box_message = '';
$answer = '';
if ( !empty($_POST['form_submitted']) && !$user_account->disabled ) {
	if ( $_POST['payoutoption'] != 'paypal' )
		$_POST['paypal_email'] = $_POST[$_POST['payoutoption'].'_email'];
	if ( empty($answer) && !$user_account->verify_password_from_box_password('old_password') )
		$answer = 'Error: Please enter your current password.';

	if ( empty($answer) && $user_account->get_calculated_balance() > VERIFICATION_PIN_NEED_IF_BALANCE && !$user_account->check_verification_pin($_POST['verification_pin']) )
		$answer = 'verification PIN is not correct.';
	
	if ( empty($answer) ) {
		$old_email = $user_account->paypalemail;
		$answer = $user_account->update_payment_details($_POST['paypal_email'], $_POST['payoutoption']);
	}
	if ( empty($answer) ) {
		$user_account->clear_verification_pin();
		$box_message = 'Your Payment Details have been updated.';
		if ( $old_email != $_POST['paypal_email'] ) {
			$user_account->send_email('notify_paypal_email_changed', '&paypalemail='.$_POST['paypal_email'], '', '', $user_account->order_emails);
		}
		if ( !empty($_COOKIE['continue_withdraw']) ) {
			header('Location: /acc_withdraw.php');
			exit;
		}
	}
}
if ( empty($_POST['payoutoption']) )
	$_POST['payoutoption'] = $user_account->payoutoption;

if ( empty($_POST[$_POST['payoutoption'].'_email']) )
	$_POST[$_POST['payoutoption'].'_email'] = $user_account->paypalemail;

$page_header = 'Payment Details';
$page_title = $page_header.'. '.SITE_NAME;
$parent_page = 'acc_account.php';
require(DIR_WS_INCLUDES.'header.php');

global $payout_options;

if ( $user_account->disabled ) {
	echo '<p><strong>No Payment Details</strong></p>';
}
else {
	if ( !empty($answer) ) {
		if ( is_integer(strpos($answer, 'Error:')) )
			echo '<script>show_top_alert("", "alert-danger", "'.bin2hex($answer).'");</script>';
		else {
			$answer = 'Error: '.$answer;
			echo '<script>show_top_alert("", "alert-danger", "'.bin2hex($answer).'");</script>';
		}
		$box_message = $answer;
	}

	if ( empty($box_message) ) {
		$common_params = $user_account->get_list_of_common_params();
		echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'cryptocurrencies.png', 'To get paid, you need to choose the method of payment. You can make withdrawals using a variety of methods including: '.$common_params['payout_providers'].'. Withdrawal fees are vary by payment method.', 'alert-info');
	}
	$select_options = '';
	$select_options_values = '';
	$payment_selected = false;
	$hours_since_last_payout = $user_account->get_hours_since_last_payout();
	$hours_to_prevent_change = 3 * 24;
	$pending_payouts = $user_account->get_numb_of_pending_payouts();
	$numb_of_pending_payouts = $pending_payouts || $hours_since_last_payout < $hours_to_prevent_change;
	
	$i = 0;
	$current_selected_index = 0;
	//if ( defined('DEBUG_MODE') ) {var_dump($payout_options); exit;}
	foreach ( $payout_options as $value ) {
		if ( $user_account->can_use_this_payout_option($value['can_payout'], $value['banned_countries'], $value['west_users_only'], $value['id']) ) 
		{
			$select_options = $select_options.'<option value="'.$value['id'].'" ';
			if ( $_POST['payoutoption'] == $value['id'] ) {
				$select_options = $select_options.' selected ';
				$payment_selected = true;
			}
			$select_options = $select_options.'>'.$value['name'].'</option>'."/r/n";
			$input_type = 'text';
			if (!empty($value['input_pattern']))
				$input_pattern = $value['input_pattern'];
			else
				$input_pattern = '.+';
			$description = '';
			if ( $value['id'] == 'paypal' ) {
				$input_type = 'email';
				$description = 'Enter your email address on Paypal';
			}
			else {
				switch ( $value['id'] ) {
					case 'bitcoin' : 
						$crypto = new Bitcoin();
						$input_pattern = $crypto->pattern;
					break;
					case 'litecoin' : 
						$crypto = new Litecoin();
						$input_pattern = $crypto->pattern;
						
					break;
				}
				$length = get_text_between_tags($input_pattern, '{', '}');
				$length_arr = explode(',', $length);
				$length = ($length_arr[0] + 1).' - '.($length_arr[1] + 1);
				$start_let = get_text_between_tags($input_pattern, '^[', ']');
				$start_letters = '';
				for ($j = 0; $j < strlen($start_let); $j++) {
					if ( !empty($start_letters) )
						$start_letters = $start_letters.' or ';
					$start_letters = $start_letters.'&quot;'.$start_let[$j].'&quot;';
				}
				$description = $description.'The '.$value['account_title'].' must be '.$length.' '.(is_integer(strpos($input_pattern, '[0-9]'))?'digits':'alphanumeric characters').(!empty($start_letters)?', beginning with the '.$start_letters:'').'. '.(!empty($value['name_of_issuer'])?'The '.$value['account_title'].' must be generated by your '.$value['name_of_issuer'].'.':'');
			}
			$its_selected_item = $_POST['payoutoption'] == $value['id'] || ( empty($user_account->payoutoption) && $i == 0 );
			if ( $its_selected_item )
				$current_selected_index = $i;
			$select_options_values = $select_options_values.'
				<div class="row payment_data_row" id="data'.$i.'" style="'.($its_selected_item?'':'display:none;').'">
					<label class="control-label col-md-4" style="text-transform:capitalize;">'.$value['account_title'].': <font color="#FF0000">*</font>
						<table class="visible_on_big_screen"><tr>
							<td style="vertical-align:top;">
								<img src="'.(!empty($value['logo'])?$value['logo']:'/images/'.$value['id'].'.gif').'" alt="'.$value['name'].'" title="'.$value['name'].'" style="width:60px !important; height:60px !important; margin:0px; padding-left:0px; padding-top:0px;">
							</td>
							<td>
								<span class="description">'.$value['account_title_description'].'</span>
							</td>
						</tr></table>
					</label>
					<div class="col-md-8" style="" >
						<div class="input-group">
							<input type="'.$input_type.'" pattern="'.$input_pattern.'" name="'.$value['id'].'_email" id="payout_'.$i.'" payment_method="'.$value['id'].'" value="'.$_POST[$_POST['payoutoption'].'_email'].'" class="form-control" style="" placeholder="'.$value['account_title'].'" required="required" '.($numb_of_pending_payouts?'disabled title="You cannot alter payment options while a withdrawal is active"':'').'>
							'.($numb_of_pending_payouts?show_more_info_popover('You cannot alter payment options while a withdrawal is active'):'<span title="" class="input-group-addon" style=""></span>').'
						</div>
						'.( is_integer(strpos($answer, 'account number')) || is_integer(strpos($answer, 'email address'))?'<span class="error_message">Please enter correct '.$value['account_title'].'.</span>':'' ).'
						'.($numb_of_pending_payouts?show_help('You cannot alter payment options while a withdrawal is active.'.(!$pending_payouts?'You will be able to change in '.($hours_to_prevent_change - $hours_since_last_payout).' '.show_plural($hours_to_prevent_change - $hours_since_last_payout, 'hour').'.':'').'<br><a href="/acc_transactions.php?transaction_type=PO">See Withdrawals...</a>', 'glyphicon glyphicon-exclamation-sign', 'text-danger'):show_help($description)).'
					</div>
				</div>
			'."\r\n";
			$i++;
		}
	}
	if ( !$payment_selected )
		$select_options = $select_options.'<option value="" selected>Not Selected</option>'."/r/n";
	echo '
	<div class="container small_center_cantainer">
		<form method="post" name="user_frm" id="payment_meth_form" enctype="multipart/form-data" class="form-horizontal">
			<input type="hidden" name="form_submitted" value="1">
			<input type="hidden" name="password" id="password" value="">
			<input type="hidden" name="old_password" id="old_password" value="">
			<input type="hidden" name="hash" id="hash" value="">
			<input type="hidden" name="verification_pin" value="">
			<div class="row" style="margin-bottom:6px;">
				<label class="control-label col-md-4" for="name" style="text-transform:capitalize;">Payout method: <font color="#FF0000">*</font></label>
				<div class="col-md-8" style="" >
					<div class="input-group">
						<select name="payoutoption" onChange="show_payment_data(this.selectedIndex);" class="form-control" style="" '.($numb_of_pending_payouts?'disabled title="You cannot alter payment options while a withdrawal is active"':'').'>
							'.$select_options.'
						</select>
						'.($numb_of_pending_payouts?show_more_info_popover('You cannot alter payment options while a withdrawal is active'):'<span title="" class="input-group-addon" style=""></span>').'
					</div>
				</div>
			</div>
			'.$select_options_values.'
			<div class="row" style="margin-top: 20px;">
				<div class="col-md-12" style="text-align:center;" >
					<button name="submit_btn" id="submit_btn" class="btn btn-primary btn-lg" style="'.($numb_of_pending_payouts?'display:none;':'').'" onClick="return payment_meth_form_submitted();">Save</button>
				</div>
			</div>
		</form>
	</div>

	';
	$crypto_obj = new Pay_processor();
	echo $crypto_obj->get_java_code_to_check_address();
	?>
	<SCRIPT LANGUAGE="JavaScript">
	var current_selectedIndex = <?php echo $current_selected_index; ?>;
	var need_verification_pin = <?php echo ($user_account->get_calculated_balance() > VERIFICATION_PIN_NEED_IF_BALANCE?'1':'0'); ?>;
	function payment_meth_form_submitted()
	{
		var r = document.getElementById("payout_" + current_selectedIndex).checkValidity();
		if ( !r )
			return true;
		else {
			var cr_address = $("#payout_" + current_selectedIndex).val();

			if ( ($("#payout_" + current_selectedIndex).attr("name") == "bitcoin_email" && cr_address[0] != "3" ) || $("#payout_" + current_selectedIndex).attr("name") == "litecoin_email" ) {
				try
				{
					if ( !check_ctypto_address(cr_address) ) {
						if ( !confirm("This address: " + cr_address + " looks like wrong address. Do you want to continue?")) {
							var formGroup = $("#payout_" + current_selectedIndex).parents('.input-group');
							if ( formGroup )
								formGroup.addClass("has-error").removeClass("has-success");
							return false;
						}
					}
					else {
						var formGroup = $("#payout_" + current_selectedIndex).parents('.input-group');
						if ( formGroup )
							formGroup.removeClass("has-error").addClass("has-success");
					}
				}
				catch(error){}
			}
			show_password("password", "send_data();", "old_password", "hash"); 
		}
		return false;
	}

	function show_payment_data(selectedIndex)
	{
		current_selectedIndex = selectedIndex;
		$(".payment_data_row").hide();
		$("#data" + selectedIndex).show();
	}

	function send_data()
	{
		if (need_verification_pin) 
			show_verification_pin(document.user_frm, document.user_frm.verification_pin);
		else
			document.user_frm.submit();
	}
	</script>
	<?php
}
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_WS_INCLUDES.'box_verification_code.php');
require(DIR_WS_INCLUDES.'box_password.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>