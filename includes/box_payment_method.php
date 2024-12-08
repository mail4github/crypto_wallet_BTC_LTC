<?php
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

function get_table_of_payment_methods($total_to_buy, $user_account, $solid_cur_only = 0, $no_discount = 0, $can_get_loan = 1, $additioanal_options = '', $show_additional_description = 1, $show_exchange_website = 1, $include_balance = false)
{
	$res = '
	<table class="payment_method_table">
	<tr>
		<td>
	';
	$i = 0;
	$payment_methods = $user_account->get_payment_methods($total_to_buy, $solid_cur_only, $can_get_loan, $additioanal_options, $include_balance);
	if (empty($_POST['pay_method'])) {
		foreach ( $payment_methods as $value ) {
			if ($value['id'] == 'balance') {
				$_POST['pay_method'] = 'balance';
				break;
			}
			if ($value['id'] == 'bitcoin') {
				$_POST['pay_method'] = 'bitcoin';
				break;
			}
		}
	}
	foreach ( $payment_methods as $value ) {
		{
			if ( $no_discount || !isset($user_account) )
				$value['discount'] = 0;
			else
				$value['discount'] = $user_account->calculate_discount($value['id']);

			$crypto = Pay_processor::get_crypto_currency_by_name($value['currency']);
			
			$normal_fee = (isset($crypto) && $crypto?$crypto->from_satoshis($crypto->get_priority_fee_in_satoshi(0, 'normal')):0.0001);
			$fast_fee = (isset($crypto) && $crypto?$crypto->from_satoshis($crypto->get_priority_fee_in_satoshi(0, 'fast')):0.0001);
			$res = $res.'
			<table class="payment_option_table" cellspacing="0" cellpadding="0" border="0" id="pay_met_'.$i.'_bkg" _symbol="'.bin2hex($value['symbol']).'" _currency="'.$value['currency'].'" _currency_precision="'.(isset($crypto) && $crypto?$crypto->digits:DOLLAR_DECIMALS).'" _description="'.$value['description'].'" _withdraw_amounts="'.implode("|", $value['withdraw_amounts']).'" _min="'.floatval($value['add_funds_min']).'" _rate="'.(isset($crypto) && $crypto?$crypto->get_exchange_rate(DOLLAR_NAME):'1').'" _name="'.$value['name'].'" _account_title="'.$value['account_title'].'" _pattern="'.(isset($crypto) && $crypto?$crypto->pattern:'').'" _priority_fee_normal="'.$normal_fee.'" _priority_fee_fast="'.$fast_fee.'" _max="'.$value['max_cashout'].'" >
			<tr>
				<td style="vertical-align:top; padding-top:13px; height:100px;">
					<input type="radio" name="payment_method" id="pay_met_'.$i.'" class="payment_method_radio" discount="'.$value['discount'].'" value="'.$value['id'].'" '.( ( empty($_POST['pay_method']) && $i == 0 ) || $_POST['pay_method'] == $value['id']?'checked':'').' onclick="select_method(\'pay_met_'.$i.'\'); return true;"> 
				</td>
				<td style="vertical-align:top; width:100%">
					<div class="tranparent_name" id="pay_met_'.$i.'_tranparent_name" style="width:100%; height:0; position:relative; top:0; left:0; display:none;">
						<div style="width:100px; height:0; position:absolute; top:0px; right:0px; text-align:right; opacity:0.2; ">
							<b class="notranslate">'.$value['name'].'</b>
						</div>
					</div>

					<img src="'.(!empty($value['logo'])?$value['logo']:'/'.DIR_WS_WEBSITE_IMAGES_DIR.$value['id'].'.png').'" border="0" alt="'.$value['name'].'" title="'.$value['name'].'" 
					style="width:64px; height:64px; margin-left:10px; cursor:pointer;"
					onclick="return select_method(\'pay_met_'.$i.'\');">
					'.($value['discount'] > 0?'<br><span class="description"><span style="color:#ff0000; font-weight:bold;" title="Get '.number_format($value['discount']*100, 0).'% discount if purchase through '.$value['name'].'">'.number_format($value['discount']*100, 0).'% Discount</span><input type="hidden" name="discount_'.$value['id'].'" value="'.$value['discount'].'" >':'').'
					'.(!empty($value['additional_description']) && $show_additional_description?'<br>'.$value['additional_description']:'').'</span>
					'.(!empty($value['exchange_website']) && $show_exchange_website?'<p class="description" style="margin-top:10px;">Buy '.ucfirst(strtolower($value['id'])).'s at: <a href="'.$value['exchange_website'].'">'.get_domain($value['exchange_website']).'</a></p>':'').'
				</td>
			</tr>
			</table>
			'."\r\n";
			$i++;
		}
	}
	$res = $res.'
		</td>
	</tr>
	</table>
	<script type="text/javascript">
	function select_method(method_id, method_name)
	{
		if ( typeof method_name != "undefined" && method_name.length > 0 ) {
			var select_payment = 0;
			var payment_number = 0;
			$( ".payment_method_radio" ).each(function() {
				if ( $( this ).val().toLowerCase() == method_name.toLowerCase() ) {
					select_payment = payment_number;
				}
				payment_number++;
			});
			method_id = "pay_met_" + select_payment;
		}
		$("#" + method_id).prop("checked", true);
		$(".payment_option_table").removeClass("payment_option_table_selected");
		$("#" + method_id + "_bkg").addClass("payment_option_table_selected");
		$(".tranparent_name").hide();
		$("#" + method_id + "_tranparent_name").show();
		
		if (typeof calculateTotal === "function")
			calculateTotal();
		
		if (typeof select_method_function !== "undefined" && select_method_function.length > 0) 
			window[select_method_function](method_id);

		return false;
	}
	function on_balance_received(balances_arr)
	{
		var select_from_balance_pay_currency_body = "";
		for (i = 0; i < balances_arr.length; i++) { 
			if (balances_arr[i]["currency"] != "'.strtolower(DOLLAR_NAME).'" && balances_arr[i]["amount"] > 0){
				select_from_balance_pay_currency_body = select_from_balance_pay_currency_body + "<input type=radio name=from_balance_pay_currency value=" + balances_arr[i]["currency"] + (select_from_balance_pay_currency_body.length == 0?" checked":"") + ">&nbsp;&nbsp;" + balances_arr[i]["description"] + "&nbsp;&nbsp; " + currency_format(balances_arr[i]["available_funds"], balances_arr[i]["symbol"], "color:#008800", "color:#FF0000", undefined, balances_arr[i]["digits"]) + "<br>";
			}
		}
		$("#select_from_balance_pay_currency_body").html(select_from_balance_pay_currency_body);
	}
	function from_balance_pay_currency_selected()
	{
		if (typeof on_from_balance_pay_currency_selected === "function")
			on_from_balance_pay_currency_selected();
	}

	</script>
	'.generate_popup_code('select_from_balance_pay_currency', '<div id="select_from_balance_pay_currency_body"></div>', 'from_balance_pay_currency_selected()', 'Please select currency to pay', '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Purchase', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel', '', '', '', '', true);
	return $res;
}
?>
	