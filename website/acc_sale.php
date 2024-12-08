<?php
if ( !empty($_GET['transactionid']) ) 
	$transactionid = (int)$_GET['transactionid'];

if ( !empty($_POST['transactionid']) ) 
	$transactionid = (int)$_POST['transactionid'];

require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');
require_once(DIR_WS_CLASSES.'payout.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$transaction = new Transaction();
if ( !empty($_GET['payoutid']) ) {
	$payout = new Payout();
	if ( $payout->read_data($_GET['payoutid']) ) {
		if ( $payout->userid !== $user_account->userid && !$user_account->is_manager() )
			$box_message = 'Error: payout not found';
		else
		if ( !$transaction->read_data($payout->transactionid, '', 0, 1, 1) ) {
			$transaction->type = 'PO';
			$transaction->created = $payout->created;
		}
	}
	else
		$box_message = 'Error: payout not found';
}
else {
	if ( !$transaction->read_data($transactionid, '', 0, 1, 1) ) {
		header('Location: /', true, 301);
		exit;
	}
}

if ( $user_account->is_manager() ) {
	if ( !empty($_GET['cancel_payout']) ) {
		$tmp_payout = new Payout();
		if ( $tmp_payout->read_data($_GET['cancel_payout']) )
			$tmp_payout->decline(true, false);
	}

	if ( !empty($_GET['complete_payout']) ) {
		$tmp_payout = new Payout();
		if ( $tmp_payout->read_data($_GET['payoutid']) )
			$tmp_payout->finish_payment('A', '', 0, false, '1');
	}

	if ( !empty($_GET['resend_payout']) ) {
		$tmp_payout = new Payout();
		if ( $tmp_payout->read_data($_GET['payoutid']) ) {
			$tmp_payout->resend_payout('', '', true);
		}
	}
	if ( !empty($_POST['confirm_payout']) )
		get_api_value('user_confirm_funds_received', '', array('payoutid' => $_POST['payoutid']));

	if ( !empty($_POST['payout_not_received']) )
		get_api_value('user_payout_not_received', '', array('payoutid' => $_POST['payoutid']));


	if ( !empty($_POST['date_will_active']) ) 
		$transaction->change_date_will_active($_POST['date_will_active']);

	if ( !empty($_GET['activate_transaction']) )
		$transaction->update_status('A');
		
	if ( !empty($_GET['change_transaction_status']) ) 
		$transaction->update_status($_GET['transaction_status'], false);
		
	if ( !empty($_GET['cancel_transaction']) ) 
		$transaction->update_status('D', $_GET['make_reversal']);
		
	if ( !empty($_GET['delete_transaction']) ) 
		$transaction->delete();
		
	if ( !empty($_GET['date_will_active']) ) 
		$transaction->change_date_will_active($_GET['date_will_active']);
		
	if ( !empty($_GET['change_tr_commission']) ) 
		$transaction->update_commission($_GET['commission']);
		
}

$currency_symbol = DOLLAR_SIGN;
$currency_digits = DOLLAR_DECIMALS;
$crypto = Pay_processor::get_crypto_currency_by_name($transaction->currency);
if ($crypto) {
	$currency_symbol = $crypto->symbol;
	$currency_digits = $crypto->digits;
}

$page_header = 'Transaction';
$page_title = $page_header;
$page_desc = 'Information about sale.';
require(DIR_WS_INCLUDES.'header.php');
echo '

';
switch ( $transaction->type ) {
	case 'PO': 
		if ( !isset($payout) ) {
			$payout = new Payout();
			$payout->read_data('', $transaction->transactionid);
		}
	break;
	case 'PP': 
		if ( !isset($payout) ) {
			$payout = new Payout();
			$payout->read_data($transaction->var_int1);
		}
	break;
}
if ( isset($payout) ) {
	parse_str($payout->adminnote, $payout_option);
	$payout_option = $payout_option["payopt"];
	if (!empty($payout_option))
		$cancelation_blocked = $user_account->get_payout_option_value("block_withdrawal_cancellation", false, $payout_option);
	else 
		$cancelation_blocked = false;
}
?>
<style type="text/css">
.address_div{width:100%; margin:0 0 20px 0; max-width:520px;}
.address_text,
#address_text{border:none; background-color:transparent; padding:0;}
@media (max-width: 579px) {
	.address_text,
	#address_text{height:auto;}
}
@media (min-width: 580px) {
	.address_text,
	#address{height:;}
}
</style>
<div class="row">
	<div class="col-xs-4 notranslate">
		<?php 
		echo '<h3 style="'.($transaction->status == "D" ? 'text-decoration: line-through;"':'').'">'.currency_format($transaction->commission, 'color:#008800', 'color:#ff0000', '', false, false, $currency_symbol, $currency_digits).'</h3>'; 
		if ( isset($crypto) && $crypto ) {
			$exchange_rate = $crypto->get_exchange_rate(DOLLAR_NAME);
			echo '<p class="description" style="margin-bottom:20px; max-width:84px;">'.($exchange_rate > 0 ? currency_format($transaction->commission * $exchange_rate, "", "", "", false, false, DOLLAR_SIGN, DOLLAR_DECIMALS) : "").'</p>';
		}
		?>
	</div>
	<div class="col-xs-8">
		<?php 
		if ( ($transaction->type == 'PP' || $transaction->type == 'PO') && !empty($transaction->purchaseid) ) {
			echo '<h3>Send</h3>
			';
		}
		else
		if ( $transaction->type == 'AF' ) {
			echo '<h3>Received Funds</h3>
			';
		}
		else
			echo "<h3>Sent Funds</h3>";
		if ( $user_account->is_manager() ) {
			if ( $transaction->type == 'IT' ) {
				$sender_tr = new Transaction();
				$sender_tr->read_data($transaction->parent_transactionid);
				echo 'from user: <a href="/acc_viewuser.php?userid='.$sender_tr->userid.'" target="_blank">'.$sender_tr->userid.'</a>';
			}
		}
		?>
	</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<h3>Initiated:</h3>
		<p class="local_pline_date_and_hour" unix_time="<?php echo $transaction->created_since_unix; ?>"><?php echo $transaction->created; ?></p>
	</div>
	<div class="col-xs-8">
		<?php 
		if ( ($transaction->type == 'PP' || $transaction->type == 'PO') && !empty($transaction->purchaseid) ) {
			$pay_processor = new Pay_processor();
			$payout_option = '';
			if ( isset($payout) )
				$payout_option = $payout->get_payout_option();
			$addr = '';
			$href = '';
			if ( $payout_option != 'bankcard' ) {
				switch ( $pay_processor->get_crypto_currency_by_address($transaction->pay_processor_email) ) {
					case 'bitcoin' :
					case 'litecoin' :
						if ( isset($payout) && isset($payout->note) && is_integer(strpos($payout->note, 'hash:')) ) {
							$addr = substr($payout->note, 5);
							$href = replaceCustomConstantInText("hash", $addr, constant(strtoupper($pay_processor->get_crypto_currency_by_address($transaction->pay_processor_email).'_TRANSACTIONS_EXPLORER')));
						}
						else {
							$addr = $transaction->pay_processor_email;
							$href = replaceCustomConstantInText("address", $addr, constant(strtoupper($pay_processor->get_crypto_currency_by_address($transaction->pay_processor_email).'_BLOCKS_EXPLORER')));
						}
					break;
					default: 
						$addr = $transaction->purchase_user_email;
						if ( $user_account->is_manager() )
							$href = '/acc_viewuser.php?paypalemail='.$addr;
				}
			}
			echo 
			(
				!empty($addr) && (!empty($transaction->paid) && $transaction->status == 'A' || $user_account->is_manager())?
				( '<h3 style="overflow:hidden;">Source:</h3>'.
					(
						!empty($href)?'
						<div class="input-group input-group-sm address_div notranslate">
							<textarea wrap="soft" class="form-control" readonly id="address_text">'.$addr.'</textarea>
							<span class="input-group-btn" style="vertical-align:top;"><button class="btn btn-link btn-sm" onclick="window.location.href=\''.$href.'\';"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></button></span>
						</div>'
						:"<textarea wrap=soft class='form-control notranslate' readonly id=address_text>$addr</textarea>"
					)
				).''
				:''
			);
			if ( $user_account->is_manager() ) {
				$user = new User();
				$user->userid = $transaction->purchase_userid;
				if ( $user->read_data(false) )
					echo 'from user: <a href="/acc_viewuser.php?userid='.$transaction->purchase_userid.'" target="_blank">'.$user->full_name().'</a>';
			}
			if ( isset($payout) ) {
				$href = '';
				$to_addr = $transaction->init_owner()->paypalemail;
				if ( isset($payout) && !empty($payout->address_to_send))
					$to_addr = $payout->address_to_send;
				switch ( $pay_processor->get_crypto_currency_by_address($transaction->pay_processor_email) ) {
					case 'bitcoin' :
					case 'litecoin' :
						$href = replaceCustomConstantInText("address", $to_addr, constant(strtoupper($pay_processor->get_crypto_currency_by_address($transaction->pay_processor_email).'_BLOCKS_EXPLORER')));
					break;
				}
				echo '<h3 style="overflow:hidden;">Destination:</h3>'.
					(!empty($href)?'
						<div class="input-group input-group-sm address_div notranslate">
							<textarea wrap="soft" class="form-control" readonly id="address_text">'.$to_addr.'</textarea>
							<span class="input-group-btn" style="vertical-align:top;"><button class="btn btn-link btn-sm" onclick="window.location.href=\''.$href.'\';"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></button></span>
						</div>'
						:"<textarea wrap=soft class='form-control notranslate' readonly  id=address_text>$to_addr</textarea>"
							
					).'';
			}
		}
		else
		if ( $transaction->type == 'MR' ) {
			parse_str($transaction->pay_attempt_note, $pay_attempt_arr);
			if (isset($pay_attempt_arr['ref']) && !empty($pay_attempt_arr['ref']))
				echo "Referrer:<br><a href=".$pay_attempt_arr['ref']." target=_blank>".$pay_attempt_arr['ref']."</a>";
		}
		else 
		if ( $transaction->type == 'AF' ) {
			//if (defined('DEBUG_MODE')) var_dump($transaction);
			if (!empty($transaction->pay_attempt_pay_email))
				echo '
				<h3 style="overflow:hidden;">Destination:</h3>
				<div class="input-group input-group-sm address_div notranslate">
					<textarea wrap="soft" class="form-control" readonly id="address_text">'.$transaction->pay_attempt_pay_email.'</textarea>
					<span class="input-group-btn" style="vertical-align:top;"><button class="btn btn-link btn-sm" onclick="window.location.href=\''.replaceCustomConstantInText("address", $transaction->pay_attempt_pay_email, constant(strtoupper(Pay_processor::get_crypto_currency_by_address($transaction->pay_attempt_pay_email).'_BLOCKS_EXPLORER'))).'\';"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></button></span>
				</div>';
			else
				echo '<h3>'.$transaction->description.'</h3>';
		}
		else
		if ( isset($payout) && !empty($payout->address_to_send)) {
			echo '
			<p>To:</p>
			<div class="input-group input-group-sm address_div notranslate">
				<textarea wrap="soft" class="form-control address_text" style="background-color:transparent;" readonly>'.$payout->address_to_send.'</textarea>
				<span class="input-group-btn" style="vertical-align:top;">
					<a style="margin-left:6px;" class="btn btn-default btn-sm" href="'.replaceCustomConstantInText("address", $payout->address_to_send, constant(strtoupper(Pay_processor::get_crypto_currency_by_name($payout->currency)->crypto_name.'_BLOCKS_EXPLORER'))).'" title="Open in blockchain"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>
				</span>
			</div>';
			if ( isset($payout->note) && is_integer(strpos($payout->note, 'hash:')) ) {
				$hash = substr($payout->note, 5);
				echo '
				<p>Hash:</p>
				<div class="input-group input-group-sm address_div notranslate">
					<textarea wrap="soft" class="form-control address_text" style="background-color:transparent;" readonly>'.$hash.'</textarea>
					<span class="input-group-btn" style="vertical-align:top;">
						<a style="margin-left:6px;" class="btn btn-default btn-sm" href="'.replaceCustomConstantInText("hash", $hash, constant(strtoupper(Pay_processor::get_crypto_currency_by_name($payout->currency)->crypto_name.'_TRANSACTIONS_EXPLORER'))).'" title="Open in blockchain"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a>
					</span>
				</div>';
			}
		}
		
		if ( $user_account->is_manager() ) {
			if ( $transaction->type == 'AF' && !empty($transaction->purchaseid) ) {
				echo '
				To <b>'.$transaction->pay_attempt_payment_method.' '.$transaction->pay_attempt_pay_email.'</b>, to user: <a href="/acc_viewuser.php?userid='.$transaction->sales_manager_id.'" target="_blank">'.$transaction->sales_manager_id.'</a><br>
				';
			}
			if ( $transaction->type == 'SR' ) {
				$user = new User();
				$user->userid = $transaction->var_int1;
				$user->read_data(false);
				echo '
				Purchase by <a href="/acc_viewuser.php?userid='.$transaction->var_int1.'" target="_blank">'.$user->full_name().'</a>, transaction: <a href="/transaction?transactionid='.$transaction->parent_transactionid.'" target="_blank">'.$transaction->parent_transactionid.'</a><br>
				';
			}
			echo '
			'.($transaction->status != 'D'
				?'<form method="get" style="_display:inline;">
				<input type="hidden" name="transactionid" value="'.$transaction->transactionid.'">
				<input type="hidden" name="cancel_transaction" value="'.$transaction->transactionid.'">
				<button style="margin-left:0px;" class="btn btn-danger btn-xs" onClick="return ( confirm(\'Do you really want to cancel this transaction?\') );">Cancel Transaction</button>
				<input type="checkbox" name="make_reversal" value="1" style="margin-left:20px;"> Make Reversal
				</form>
				'
				:'<form method="get" style="_display:inline;">
				<input type="hidden" name="transactionid" value="'.$transaction->transactionid.'">
				<input type="hidden" name="activate_transaction" value="'.$transaction->transactionid.'">
				<button style="margin-left:20px;" class="btn btn-success btn-xs" onClick="return ( confirm(\'Do you really want to activate this transaction?\') );">Activate Transaction</button>
				</form>
			').'
			';
		}
		if ( $transaction->status == "D" ) 
			echo "<p style='color:#ff0000;'>{$payout->note}</p>";
		?>
	</div>
</div>
<div class="row">
	<div class="col-xs-4">
		<?php 
		if ( isset($payout) ) {
			if ( $user_account->is_manager() ) {
				if ( $transaction->type == 'PO' )
					echo "
					<br>Payout Status: <b>{$payout->get_status_name()}</b>
					<br>Attempts to pay: {$payout->number_of_attempts_to_pay}<br>
					Sent from account: <strong><a href='/acc_viewuser.php?paypalemail={$payout->pay_processor_email}'>{$payout->pay_processor_email}</a></strong><br>
					User: <a href='/acc_viewuser.php?userid={$transaction->userid}' target=_blank>{$transaction->userid}</a><br>
					";
			}
		}
		?>
	</div>
	<div class="col-xs-8">
		<h3>Status:</h3>
		<?php
		if ( isset($payout) && ($payout->status == 'W' || $payout->status == 'P') )
			echo '
			<p style="color:#00AA00;">
				processing
				<svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:14px; height:14px; margin:-3px 0px -3px 10px; display:inline-block;">
				<circle fill="none" stroke="#00FF00" stroke-width="2" cx="50" cy="50" r="44" style="opacity:0.9;"></circle>
				  <circle fill="#00aa00" stroke="#ffffff" stroke-width="3" cx="92" cy="40" r="8" transform="rotate(246.654 50 50.7406)">
					<animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 48" to="360 50 52" repeatCount="indefinite"></animateTransform>
				  </circle>
				</svg>
			</p>
			<h3>Transaction Speed:</h3>
			<div class="input-group input-group-sm address_div">
				<select id="transaction_speed" class="form-control">
					<option '.($payout->priority == 'slow' ? 'selected' : '').' value="slow">Slow Delivery</option>
					<option '.($payout->priority == 'normal' ? 'selected' : '').' value="normal">Fast Delivery</option>
					<option '.($payout->priority == 'fast' ? 'selected' : '').' value="fast">Urgent Delivery</option>
				</select>
				<span class="input-group-btn" style="vertical-align:top;"><button class="btn btn-info btn-sm" onclick="change_transaction_speed();"><span class="glyphicon glyphicon-save" aria-hidden="true"></span><span class="visible_on_big_screen" style="margin-left:6px;">Change Speed</span></button></span>
			</div>
			
			';
		else
			echo '<p style="'.($transaction->status == 'A' && !empty($transaction->paid)?'color:#00AA00;':($transaction->status == 'D'?'color:#FF0000;':'')).'">'.$transaction->get_status_name().($transaction->status == 'P'?'<svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:14px; height:14px; margin:-3px 0px -3px 10px; display:inline-block;">
				<circle fill="none" stroke="#00FF00" stroke-width="2" cx="50" cy="50" r="44" style="opacity:0.9;"></circle>
				  <circle fill="#00aa00" stroke="#ffffff" stroke-width="3" cx="92" cy="40" r="8" transform="rotate(246.654 50 50.7406)">
					<animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 48" to="360 50 52" repeatCount="indefinite"></animateTransform>
				  </circle>
				</svg>':'').'</p>';
		if ( $user_account->is_manager() && $transaction->status == "P" ) {
			echo 'Will be available: 
				<form method="post" style="display:inline;">
					<input type="hidden" name="transactionid" value="'.$transaction->transactionid.'">
					<div class="input-group input-group-sm" style="width:100%; ">
						<input type="text" class="form-control input-sm" name="date_will_active" id="date_will_active" value="'.$transaction->date_will_active.'" placeholder="Date when will be activated">
						<span class="input-group-btn">
							<button class="btn btn-success btn-sm">Change</button>
						</span>
					</div>
					<span class="description" style="padding-left:0px;">(yyyy-mm-dd)</span>
				</form>';
		}
		if ( $transaction->type == 'PO' || $payout->transactionid == $transaction->transactionid ) {
			if ( $payout->status == "A" || $payout->status == "D" ) {
				echo (!empty($payout->processed_unix) ? '<p class="local_pline_date_and_hour" unix_time="'.$payout->processed_unix.'">'.$payout->processed.'</p>':'');
			}
			else {
				if ( $user_account->is_manager() ) {
					echo '
					<form method="get" style="display:inline;">
					<input type="hidden" name="payoutid" value="'.$payout->payoutid.'">
					<input type="hidden" name="transactionid" value="'.$transaction->transactionid.'">
					<input type="hidden" name="cancel_payout" value="'.$payout->payoutid.'">
					<button style="margin-left:0px;" class="btn btn-danger btn-xs" onClick="return ( confirm(\'Do you really want to cancel this payout?\') );">Cancel Payout</button>
					</form>
					<form method="get" style="display:inline;">
					<input type="hidden" name="payoutid" value="'.$payout->payoutid.'">
					<input type="hidden" name="complete_payout" value="1">
					<button style="margin-left:0px;" class="btn btn-info btn-xs" onClick="return ( confirm(\'Do you really want to make this payout completed?\') );">Make "Completed"</button>
					</form>';
				}
			}
			if ( $user_account->is_manager() && ($payout->status == 'A' || $payout->status == 'D') )
				echo '
				<form method="get" style="display:inline;">
				<input type="hidden" name="payoutid" value="'.$payout->payoutid.'">
				<input type="hidden" name="resend_payout" value="'.$payout->payoutid.'">
				<button style="margin-left:0px;" class="btn btn-success btn-xs" onClick="return ( confirm(\'Do you really want to re-send this payout?\') );">Re-send Payout</button>
				</form>';

		}
		if ( ($transaction->type == 'PO' || $transaction->type == 'PP') && $payout->status == "A" && $payout->adminnote == "pm" )
			echo '<p>PM batch number: <b>'.$payout->pay_processor_email.'</b></p>';
		echo '
		<h3>Description:</h3>
		<div class="input-group input-group-sm address_div notranslate">
			<textarea wrap="soft" class="form-control" id="description_text">'.$transaction->description.'</textarea>
			<span class="input-group-btn" style="vertical-align:top;"><button class="btn btn-info btn-sm" onclick="save_description();"><span class="glyphicon glyphicon-save" aria-hidden="true"></span><span class="visible_on_big_screen" style="margin-left:6px;">Change Description</span></button></span>
		</div>
		';
		if ( $user_account->is_manager() && $transaction->type == "RR" ) 
			echo "<p>Reward for new referral: <a href='/acc_viewuser.php?userid={$transaction->var_int1}' target=_blank>{$transaction->var_int1}</a></p>";
		?>
	</div>
</div>
<?php
if ( isset($payout) && $payout->status == "W" ) {
	echo '
		<form method=post style="width:100%; text-align:center;">
		<input type=hidden name=payoutid value='.$payout->payoutid.'>
		<input type=hidden name=confirm_payout value=1>
		<button style="margin-left:0px;" class="btn btn-success" onClick="return confirm(\'Do you really received these funds?\');"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp;&nbsp; I Received Funds</button>
		</form>';
	if ( $payout->processed_hours_ago > $user_account->get_payout_option_value('send_payment_during_minutes', false, $payout->payout_option) / 60 * 2 ) {
		echo '
		<form method=post style="width:100%; text-align:center; margin-top:20px;">
		<input type=hidden name=payoutid value='.$payout->payoutid.'>
		<input type=hidden name=payout_not_received value=1>
		<button style="margin-left:0px;" class="btn btn-warning" onClick="return confirm(\'Do you really did not receive these funds?\');"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>&nbsp;&nbsp; I did NOT Received Funds</button>
		</form>
		<div class="alert alert-danger" style="text-align:center;">Please click on one of the buttons above. In '.($user_account->get_payout_option_value('add_funds_auto_confirm_after_days', false, $payout->payout_option) * 24 - $payout->processed_hours_ago).' hours this payout will be marked as <b>RECEIVED</b> automatically!!!</div>
		';
	}
}
?>
<br>
<a class="btn btn-primary" href="/transactions"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span>&nbsp;&nbsp;Back to Transactions</a>
<br>
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

<link rel="stylesheet" type="text/css" media="all" href="/javascript/jsdatepick-calendar/jsDatePick_ltr.min.css" />
<script type="text/javascript" src="/javascript/jsdatepick-calendar/jsDatePick.min.1.3.js"></script>
<script language="JavaScript">
function save_description()
{
	var description_text = $("#description_text").val();
	if ( description_text.length == 0 ) {
		show_message_box_box("Error", "Please enter your description", 2);
		return false;
	}
	description_text = string_to_hex32(description_text.replace(/\n/g, "[#br#]"));
	$.ajax({
		method: "POST",
		url: "/api/user_save_transaction_description",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", transactionid: "<?php echo $transaction->transactionid; ?>", transaction_description: description_text },
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) 
				show_message_box_box("Success", "Description has been saved", 1); 
			else {
				if (arr_ajax__result["message"].length > 0)
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				else
					show_message_box_box("Error", "Description could not been saved", 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Description could not been saved. Please try again.", 2); 
		}
	});
}

function change_transaction_speed()
{
	$.ajax({
		method: "POST",
		url: "/api/user_change_payout_priority",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", payoutid: "<?php echo $payout->payoutid; ?>", priority: $("#transaction_speed").val() },
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) 
				show_message_box_box("Success", "Transaction Speed has been changed", 1); 
			else {
				if (arr_ajax__result["message"].length > 0)
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				else
					show_message_box_box("Error", "Transaction Speed could not been changed", 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Transaction Speed could not been changed. Please try again.", 2); 
		}
	});
}

if ( document.getElementById("date_will_active") ) {
	window.onload = function(){
		JsPick = new JsDatePick({
			useMode:2,
			target:"date_will_active",
			dateFormat:"%Y-%m-%d"
		});
		JsPick.oConfiguration.imgPath = "/javascript/jsdatepick-calendar/img/";
	};
}
</script>

</body>
</html>
