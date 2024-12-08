<?php
$showDebugMessages = false;

if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);
	
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_INCLUDES.'box_payment_method.php');

$box_message = '';
global $payout_options;
$this_payout = '';
$page_header = 'Receive';

$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

if ( ($user_account->account_type == 'H' && $user_account->rank == 0 && $user_account->purchases_disabled) || $user_account->disabled || $user_account->deleted ) {
	echo '<br><br><br><h3>This option is not available.</h3><br><br><br><br>';
	$_GET['alert_info'] = '';
}
else {
	echo show_intro('/'.DIR_WS_IMAGES_DIR.'add_funds.png', '<p>Select the currency that you want to receive. You will see crypto-address and QR code. Share crypto-address or scan QR code to receive funds.</p>
	<p>Always copy and paste crypto-address. This lowers the risk of making a mistake.</p>', 'alert-info');
	$withdraw_amounts = '';
	foreach ( $payout_options as $value ) {
		if ( count($value['withdraw_amounts']) > 0 ) {
			foreach ($value['withdraw_amounts'] as $withdraw_amount)
				$withdraw_amounts = $withdraw_amounts.'<option class="withdraw_amount_value" value="'.$withdraw_amount.'" id="withdraw_amount_'.$withdraw_amount.'">'.$withdraw_amount.'</option>'."\r\n";
			break;
		}
	}
	?>
	<style type="text/css">
	#address{width:100%;}
	@media (max-width: 579px) {
		#address{height:auto;}
	}
	@media (min-width: 580px) {
		#address{height:;}
	}
	</style>
	<div class="row">
		<div class="col-sm-3" style="min-width:210px;">
			<?php 
			echo get_table_of_payment_methods($total_to_buy, $user_account, 1, 1, 0, ['add_funds'], 0, 0, false);
			?>
		</div>
		<div class="col-sm-6">
			<h3>Address:</h3>
			<div class="input-group input-group-sm" style="width:100%">
				<textarea wrap="soft" class="form-control notranslate" style="" readonly id="address">&#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b;</textarea>
				<span class="input-group-btn" style="vertical-align:top;">
					<button class="btn btn-info btn-sm" style="max-width:160px;" id="copy_address_btn" onclick="copy_address();"><span class="glyphicon glyphicon-duplicate" aria-hidden="true" id="copy_address_btn_icon"></span><span class="visible_on_big_screen" style="padding-left:10px;">Copy to Clipboard</span></button>
				</span>
			</div>
			<?php echo show_help('Forward the address above to the sender and use that address to send funds to your wallet', 0); ?>
			<button class="btn btn-info" style="min-width:160px; margin-top:10px;" onclick="get_address(1);"><span class="glyphicon glyphicon-repeat" style="margin-right:20px;"></span>Change Address</button>

			<h3>Amount:</h3>
			<div class="row">
				<div class="col-xs-9" style="padding-right:0;">
					<div class="inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon dollar_sign notranslate"><?php echo DOLLAR_SIGN; ?></span>
							<input type="number" step="any" autocomplete="off" name="amount" id="amount" class="form-control notranslate" value="" required="required" placeholder="0.00" onkeyup="amount_changed();" style="min-width:110px;">
						</div>
					</div>
				</div>
				<div class="col-xs-3" style="padding-top:4px;">
					<span id="amount_in_usd" class="notranslate"><?php echo DOLLAR_SIGN; ?>0.00</span>
				</div>
			</div>
			<?php echo show_help('Enter amount, which will be shown in QR code');//'Minimum: <a href="#" class="min_amount notranslate" onclick=\'$("#amount").val( $("#amount").attr("min") ); return false;\' style="text-decoration:underline;">0</a>', 0); ?>
			
		</div>
		<div class="col-sm-3" style="text-align:center; padding-top:20px;">
			<a class="launch_crypto_app" href="" style="" id="qrcode_public" title="Click to launch the crypto-wallet"></a>
			<?php echo show_help('A sender can scan the QR code above from mobile device to get correct address and amount', 0); ?>
		</div>
	</div>
	
	<!-- Check payment -->
	<div class="modal fade" id="check_payment_box" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" style="text-align:center;">
					<span id="check_payment_box_text">We are waiting for receiving funds<br>
					to address:<br>
					<b style="" class="description notranslate" id="check_payment_box_address"></b><br>
					</span>
					<img src="/images/wait_big3.gif" width="30" height="30" border="0" alt="" id="check_payment_box_wait"><br>
					<div style="display:none;" id="check_payment_box_ok">
						<h2>Congratulations!!!</h2> 
						Your payment has been received.<br><br>
						<svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:100px;"><circle fill="#ffffff" stroke="#76ce76" stroke-width="6" cx="50" cy="50" r="44" style="opacity:1;"></circle><line fill="none" stroke-linecap="round" stroke="#76ce76" stroke-width="15" stroke-miterlimit="10" x1="25" y1="50" x2="47" y2="70"></line> <line fill="none" stroke-linecap="round" stroke="#76ce76" stroke-width="15" stroke-miterlimit="10" x1="47" y1="70" x2="74" y2="40"></line></svg>
					</div>
					<p id="check_payment_box_received" style="margin-top:20px;"></p>
				</div>
				<div class="modal-footer">
					<button type="button" id="cancel_btn" class="btn btn-success" data-dismiss="modal" style="margin-bottom:0px;" onclick="hide_check_payment_box();"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button>
				</div>
			</div>
		</div>
	</div>

	<script src="/javascript/QRCode.js" type="text/javascript"></script>

	<script type="text/javascript">
	var select_method_function = "set_vars_when_select_method_changed";
	var dollar_sign = "$";
	var currency_code = "USD";
	var currency_name = "";
	var currency_rate = 1;
	var currency_description = "";
	var currency_precision = 2;
	var current_currency_symbol = "$";
	var current_method_id = "";
	var invoice = "";
	var crypto_address = "";

	var stop_check = 0;
	var payment_made = 0;
	var check_payment_timer = 0;
	var min_amount = 0;

	function set_vars_when_select_method_changed(method_id)
	{
		current_method_id = method_id;
		var parent_table = $("#" + method_id).parent().parent().parent().parent();
		dollar_sign = parent_table.attr("_symbol");
		currency_code = parent_table.attr("_currency");
		currency_name = parent_table.attr("_name").toLowerCase();
		currency_rate = parent_table.attr("_rate");
		currency_description = parent_table.attr("_description");
		currency_precision = parent_table.attr("_currency_precision");
		
		current_currency_symbol = hex_to_string(dollar_sign);
		$(".dollar_sign").html(current_currency_symbol);
		var withdraw_amounts = parent_table.attr("_withdraw_amounts");
		min_amount = parent_table.attr("_min");
		var max_amount = parent_table.attr("_max");
		$(".min_amount").html( currency_format(min_amount / currency_rate, current_currency_symbol, undefined, undefined, undefined, currency_precision) );
		$("#amount").attr("step", Math.pow(10, - (currency_precision - 1)));
		$("#amount").attr("min", Math.round(min_amount / currency_rate * Math.pow(10, currency_precision)) / Math.pow(10, currency_precision));
		$("#amount").attr("max", max_amount / currency_rate);
		$("#address").val("\u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b");
		get_address();
	}
	
	function amount_changed()
	{
		$("#amount_in_usd").html( currency_format( $("#amount").val() * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) );
		show_qrCode();
	}
	
	function show_qrCode()
	{
		var cripto_ref = currency_name + ":" + $("#address").val() + ($("#amount").val() > 0 ? "?amount=" + $("#amount").val() : "");
		var keyValuePair = {
			"qrcode_public": cripto_ref
		};
		ninja.qrCode.showQrCode(keyValuePair, 3, "qr_canvas");
		$("#qr_canvas").css("width", "100%");
		$("#qr_canvas").css("height", "100%");
		$("#qr_canvas").css("max-width", "200px");
		$("#qr_canvas").css("max-height", "200px");
		$(".launch_crypto_app").attr("href", cripto_ref);
		return false;
	}
	
	function address_updated()
	{
		crypto_address = $("#address").val();
		show_qrCode();
	}

	function get_address(generate_new_address)
	{
		if (typeof generate_new_address == "undefined" )
			generate_new_address = 0;
		$("#wait_sign").show();
		$.ajax({
			method: "POST",
			url: "/api/user_get_request_to_pay_cart",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", add_funds: 1, payment_method:currency_name, make_post_request: false, pay_email:"none", cross_payoutid:0, total:$("#amount").val(), transaction_prefix: "<?php echo ADD_FUNDS_PREFIX; ?>", note:"", currency:currency_code, currency_symbol:dollar_sign, invoice_suffix:"", return_post_request_as_text:1, force_to_use_pay_email:1, ip:"<?php echo $_SERVER['REMOTE_ADDR']; ?>", return_data_as_array:1, generate_new_address:generate_new_address },
			cache: false
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] && arr_ajax__result["values"]["invoice"].length > 0 ) {
					invoice = arr_ajax__result["values"]["invoice"];
					if ( typeof arr_ajax__result["values"]["pay_email"] == "undefined" || arr_ajax__result["values"]["pay_email"].length == 0 ) {
						$.ajax({
							method: "POST",
							url: "/api/user_update_crypto_addr/",
							data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", crypto_currency:currency_name, address:"none", private_address:"none", total_BTC:"", invoice: invoice }
						})
						.done(function( ajax__result ) {
							try
							{
								var arr_ajax__result = JSON.parse(ajax__result);
								if ( arr_ajax__result["success"] && arr_ajax__result["values"] ) {
									$("#address").val( arr_ajax__result["values"]["pay_email"] );
									address_updated();
									$("#wait_sign").hide();
								}
							}
							catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
						});
					}
					else {
						$("#address").val( arr_ajax__result["values"]["pay_email"] );
						address_updated();
						$("#wait_sign").hide();
					}
				}
				else {
					setTimeout(function() { 
						get_address();
					}, 2000);
				}
			}
			catch(error){}
		});
	}

	function check_payment()
	{
		if ( stop_check || invoice.length == 0 )
			return false;
		$.ajax({
			method: "POST",
			url: "/api/user_check_crypto_order/",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", invoice: invoice }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					if (arr_ajax__result["message"].length > 0) {
						//show_message_box_box("Error", arr_ajax__result["message"], 2);
					}
					else {
						var remains = (Math.round( (arr_ajax__result["values"]["total_in_currency"] - arr_ajax__result["values"]["received"]) * 100000) / 100000);
						//if (arr_ajax__result["values"]["received"] > 0)
							//show_check_payment_box();
						if ( !Number(arr_ajax__result["values"]["process_status"]) ) {
							//$("#check_payment_box_received").html("Received: <span class=notranslate style='color:#008800;'>" + current_currency_symbol + arr_ajax__result["values"]["received"] + " " + currency_code + " </span><br>Confirmations: <span class=notranslate style='color:#"+(Number(arr_ajax__result["values"]["confirmations"]) > 1?"008800":"880000")+";'>" + Number(arr_ajax__result["values"]["confirmations"]) + "</span><br>(must be at least " + <?php echo bitcoin_WAIT_FOR_CONFIRMATIONS; ?> + ")");
							//$("#cancel_btn").hide();
						}
						else {
							stop_check = 1;
							payment_made = 1;
							clearInterval(check_payment_timer);
							//$("#check_payment_box_text").html("");
							//$("#check_payment_box_wait").hide();
							//$("#check_payment_box_received").hide();
							//$("#check_payment_box_ok").show();
							//$("#cancel_btn").show();
							//$("#cancel_btn").html("Continue...");
						}
					}
				}
			}
			catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
		});
		return false;
	}
	
	function show_check_payment_box()
	{
		$("#check_payment_box_address").html(crypto_address);
		$('#check_payment_box').modal('show'); 
		return false;
	}

	function hide_check_payment_box()
	{
		if (payment_made)
			window.location.assign("/transactions");
		else 
			$('#check_payment_box').modal('hide');
		return false;
	}

	function copy_address()
	{
		if (copy_text_from_input("address")) {
			$("#copy_address_btn_icon").removeClass("glyphicon-duplicate").addClass("glyphicon-ok");
			$("#copy_address_btn").removeClass("btn-info").addClass("btn-success");
			setTimeout(function() { 
				$("#copy_address_btn_icon").removeClass("glyphicon-ok").addClass("glyphicon-duplicate");
				$("#copy_address_btn").removeClass("btn-success").addClass("btn-info");
			}, 2000);
		}
	}

	$( document ).ready(function() {
		setInterval(function() { amount_changed(); }, 500);
		select_method("pay_met_0", get_param_value("currency"));
		check_payment_timer = setInterval("check_payment();", 10000);
	});
	</script>
	<?php
}
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
