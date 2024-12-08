<?php
require('../includes/application_top.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');

$tmp_crypto = new Pay_processor();
$bitcoin = $tmp_crypto->get_crypto_currency_by_name($_POST['crypto_currency']);
if (!$bitcoin)
	$bitcoin = new Bitcoin();
$currency_code = $bitcoin->crypto_symbol;
$crypto_currency = $bitcoin->crypto_name;

$total_BTC = 0.001;
$bitcoin_address = '';
$miners_fee = $bitcoin->get_miners_fee() * 3;

if ( !empty($_POST['invoice']) ) {
	$invoice = tep_sanitize_string(xor_decrypt('bt2cn', $_POST['invoice']), 128);
	$item_name = tep_sanitize_string(xor_decrypt('bt2cn', $_POST['item_name']), 128);
	$total = (float)xor_decrypt('bt2cn', $_POST['total']);
	$total_as_string = tep_sanitize_string(xor_decrypt('bt2cn', $_POST['total_as_string']));
	$currency = tep_sanitize_string(xor_decrypt('bt2cn', $_POST['currency']), 5);
	$total_BTC = (float)xor_decrypt('bt2cn', $_POST['total_BTC']);
	$return = tep_sanitize_string(xor_decrypt('bt2cn', $_POST['return']), 256);

	if ( empty($currency) )
		$currency = DOLLAR_NAME;
	if ( empty($total_BTC) && $total > 0 ) {
		if ( strtolower($currency) == 'btc' || strtolower($currency) == 'ltc' ) {
			$total_BTC = $total;
		}
		else {
			$exchange_rate = $bitcoin->get_exchange_rate($currency);
			$total_BTC = round_to_nearest_digits($total / $exchange_rate * 1 + $miners_fee);
		}
	}
	$total_BTC = round($total_BTC, $bitcoin->digits);

	$pay_attempt = get_api_value('user_check_crypto_order', '', array('invoice' => $invoice));
	if ( $pay_attempt && $pay_attempt['address'] != $user_account->get_payout_option_value('email', false, $crypto_currency) )
		$bitcoin_address = $pay_attempt['address'];

}
else {
	$not_paid_or_not_fully_paid_order = $user_account->get_not_paid_or_not_fully_paid_order($crypto_currency);
	if ( $not_paid_or_not_fully_paid_order ) {
		$bitcoin = $tmp_crypto->get_crypto_currency_by_name($not_paid_or_not_fully_paid_order['payment_method']);
		$miners_fee = $bitcoin->get_miners_fee() * 1;
		$currency_code = $bitcoin->crypto_symbol;
		$crypto_currency = $bitcoin->crypto_name;

		$invoice = $not_paid_or_not_fully_paid_order['invoice'];
		$item_name = 'Account replenish';
		$total = (float)$not_paid_or_not_fully_paid_order['total'];
		$total_as_string = currency_format($total).' '.DOLLAR_NAME;
		$currency = DOLLAR_NAME;
		$total_BTC = $not_paid_or_not_fully_paid_order['total_in_currency'];
		if ( empty($total_BTC) && $total > 0 ) {
			if ( strtolower($currency) == 'btc' || strtolower($currency) == 'ltc' ) {
				$total_BTC = $total;
			}
			else {
				$exchange_rate = $bitcoin->get_exchange_rate($currency);
				$total_BTC = round_to_nearest_digits($total / $exchange_rate * 1 + $miners_fee);
			}
		}
		$return = SITE_DOMAIN.'thank_you.php?inv='.$invoice;
		$bitcoin_address = $not_paid_or_not_fully_paid_order['pay_email'];
	}
}

$page_header = '';
$page_title = ucfirst(strtolower($crypto_currency)).' Payment';
require(DIR_WS_INCLUDES.'header.php');

$exchange_website = '';
$payment_methods = $user_account->get_payment_methods();
foreach ( $payment_methods as $value ) {
	if ($value['id'] == $bitcoin->crypto_name) {
		$exchange_website = $value['exchange_website'];
		break;
	}
}
echo '
	<div class="alert alert-success" style="margin:0px;">
		<img src="'.$user_account->get_payout_option_value('logo', false, $crypto_currency).'" width="100" height="100" border="0" alt="" style="float:left; margin-left:20px; margin-right:40px;">
		<span style="text-transform:none; text-align:left; color:#585858; 
		font-family:arial; font-size:48px; line-height:80px; font-weight:bold; font-style:italic; 
		padding:0px; margin:0px;
		text-shadow: -1px -1px 0px #000000;
		">'.ucfirst(strtolower($crypto_currency)).' Payment</span><br>
		'.(!empty($exchange_website)?'<p style="margin-bottom:10px;">If you do not have '.ucfirst(strtolower($bitcoin->crypto_name)).'s you can buy them at: <a href="'.$exchange_website.'">'.get_domain($exchange_website).'</a></p>':'').'
		<table class="table" style="margin:0px;">
		<tr>
			<td style="border-top: 1px solid #aaaaaa; text-left:right; padding-left:20px;">'.($item_name).'</td>
			<td class="deposit_cell" style="border-top: 1px solid #aaaaaa;"><span class="notranslate">'.($total_as_string).'</span></td>
			<td class="deposit_cell" style="border-top: 1px solid #aaaaaa;">&nbsp;</td>
		</tr>
		<tr>
			<td style="border-top: 1px solid #aaaaaa; text-align:right; padding-bottom:0px; padding-right:0px; font-size:18px; "><b>Total to Pay:</b></td>
			<td class="deposit_cell" style="border-top: 1px solid #aaaaaa; padding-bottom:0px; font-size:18px;"><b><span class="notranslate" id="no_translate">'.($total_as_string).'</span></b></td>
			<td class="deposit_cell" style="border-top: 1px solid #aaaaaa;" id="key_str">&nbsp;</td>
		</tr>
		<tr>
			<td style="border-top:none; text-align:right; padding-top:0px; padding-right:0px;"></td>
			<td class="deposit_cell" style="border:none; "><span class="notranslate" id="no_translate">('.($total_BTC.' '.$currency_code).')</span></td>
			<td class="deposit_cell" style="border:none; ">&nbsp;</td>
		</tr>
		</table>
	</div>
	<div class="alert alert-warning" style="width:100%; height:100px; text-align:center; margin-top:20px;" id="add_purchase_wait">
		<h2>Obtaining the payment information. Please wait...</h2>
		<img src="/images/wait64x64.gif" width="30" height="30" border="0" alt="">
	</div>
	<div class="box_type1" id="address_table" style="display:none; margin-top:10px;">
		<div class="row" style="">
			<div class="col-md-2" style="text-align:center;">
				<a class="launch_crypto_app" href="" style="" id="qrcode_public" title="Click to launch the '.(ucfirst(strtolower($crypto_currency))).' wallet"></a>
			</div>
			<div class="col-md-10" style="padding-left:40px; padding-top:10px;">
				Send exactly:<br><br>
				<span class="notranslate"><span style="line-height:20px;"><b style="font-size:30px; cursor:pointer; border-bottom: 1px dashed #999;" id="selectable_sum" onclick="select_text_by_click(\'selectable_sum\')">'.$total_BTC.'</b> '.$currency_code.'</span></span><br>
				'.($miners_fee > 0?'<span style="font-size:10px;">(includes the '.$miners_fee.' '.$currency_code.' of miner&#39;s fee)</span>':'').'<br><br>
				to the '.(ucfirst(strtolower($crypto_currency))).' address:<br>
				<span class="notranslate"><b style="font-size:12px; line-height:40px; cursor:pointer; border-bottom: 1px dashed #999;" id="selectable_adr" onclick="select_text_by_click(\'selectable_adr\')"></b></span>
				<br>
				<span style="font-size:10px;">This is a single use '.(ucfirst(strtolower($crypto_currency))).' address. Do not store this address for future payments.<br>
				After sending funds to the above address click the &rsquo;Confirm Payment&rsquo; button below:<br>
				</span>
			</div>
		</div>
		<div class="row" style="border:none; text-align:center;">
			<br>
			<button class="btn btn-primary btn-lg" id="confirm_btn" style="margin-left:0px;" onclick="show_check_payment_box();">Confirm Payment</button>
			<br><br>
			<a class="launch_crypto_app btn-info btn-sm" style="" href="">Launch the '.(ucfirst(strtolower($crypto_currency))).' Wallet Software <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span><span class="glyphicon glyphicon-bitcoin" aria-hidden="true"></span></a>
		</div>
	</div>
';

require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>

<!-- Check payment -->
<div class="modal fade" id="check_payment_box" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body" style="text-align:center;">
				<div class="row" style="">
					<div class="col-md-2" style="text-align:center;" >
						<a class="launch_crypto_app" href="" style="" id="check_payment_qrcode" title="Click to launch the <?php echo (ucfirst(strtolower($crypto_currency))); ?> wallet"></a>
					</div>
					<div class="col-md-10" style="">
						<span id="check_payment_box_text">We are waiting for receiving:<br>
						<span style="line-height:40px;"><b style="font-size:24px;"><?php echo $total_BTC; ?></b> <span id="no_translate"><?php echo $currency_code; ?></span></span><br>
						to <?php echo ucfirst(strtolower($crypto_currency)); ?> address:<br>
						<b style="font-size:14px; line-height:40px;" id="check_payment_box_address"></b><br>
						</span>
						
					</div>
				</div>
				<img src="/images/wait_big3.gif" width="30" height="30" border="0" alt="" id="check_payment_box_wait"><br>
				<div style="display:none;" id="check_payment_box_ok">
					<h2>Congratulations!!!</h2> Your payment has been received.<br><br>In a short time you will receive an email with confirmation.<br>
					<img src="/images/icon_good2.png" width="64" height="64" border="0" alt="" ><br>
				</div>
				<p id="check_payment_box_received" style="font-size:20px; margin-top:20px;"></p>
				<p id="check_payment_box_remains" style="font-size:20px;"></p>
			</div>
			<div class="modal-footer">
				<button type="button" id="cancel_btn" class="btn btn-warning" data-dismiss="modal" style="margin-bottom:0px;" onclick="hide_check_payment_box();">Close</button>
			</div>
		</div>
	</div>
</div>

<script src="/javascript/QRCode.js" type="text/javascript"></script>
<script language="JavaScript">
var stop_check = 0;
var payment_made = 0;
var crypto_address = "<?php echo $bitcoin_address; ?>";
var privWif = "";
var qr_code_image = "";
var crypto_currency = "<?php echo $crypto_currency; ?>";
var check_payment_timer = 0;

function hide_check_payment_box()
{
	if (payment_made)
		window.location.assign("<?php echo $return; ?>");
	else 
		$('#check_payment_box').modal('hide');
	return false;
}

function show_check_payment_box()
{
	var cripto_ref = "<?php echo $crypto_currency; ?>:" + crypto_address + "?amount=<?php echo $total_BTC; ?>";
	var keyValuePair = {
		"check_payment_qrcode": cripto_ref
	};
	ninja.qrCode.showQrCode(keyValuePair, 3);
	$(".launch_crypto_app").attr("href", cripto_ref);
	$("#check_payment_box_address").html(crypto_address);
	$('#check_payment_box').modal('show'); 
	return false;
}

function check_payment()
{
	if ( stop_check )
		return false;
	$.ajax({
		method: "POST",
		url: "/api/user_check_crypto_order/",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", invoice: "<?php echo $invoice; ?>" }
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				if (arr_ajax__result["message"].length > 0) {
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				}
				else {
					var remains = (Math.round( (arr_ajax__result["values"]["total_in_currency"] - arr_ajax__result["values"]["received"]) * 100000) / 100000);
					if (arr_ajax__result["values"]["received"] > 0)
						show_check_payment_box();
					if ( !Number(arr_ajax__result["values"]["process_status"]) ) {
						$("#check_payment_box_received").html("Received: <span style='color:#008800;'>" + arr_ajax__result["values"]["received"] + " <?php echo $currency_code; ?></span><br>Confirmations: <span style='color:#"+(Number(arr_ajax__result["values"]["confirmations"]) > 1?"008800":"880000")+";'>" + Number(arr_ajax__result["values"]["confirmations"]) + "</span> (must be at least " + <?php echo bitcoin_WAIT_FOR_CONFIRMATIONS; ?> + ")");
						$("#cancel_btn").hide();
						if (remains > 0) {
							$("#check_payment_box_remains").html("Remains: <span style='color:#440000;'>" + remains + " <?php echo $currency_code; ?></span>");
							var cripto_ref = "<?php echo $crypto_currency; ?>:" + crypto_address + "?amount=" + remains;
							$(".launch_crypto_app").attr("href", cripto_ref); 
							var keyValuePair = {
								"check_payment_qrcode": cripto_ref
							};
							ninja.qrCode.showQrCode(keyValuePair, 3);
						}
						else {
							$("#check_payment_box_remains").html("");
						}
					}
					else {
						stop_check = 1;
						payment_made = 1;
						clearInterval(check_payment_timer);
						$("#check_payment_box_text").html("");
						$("#check_payment_qrcode").html("");
						$("#check_payment_box_wait").hide();
						$("#check_payment_box_received").hide();
						$("#check_payment_box_remains").hide();
						$("#check_payment_box_ok").show();
						$("#cancel_btn").show();
						$("#cancel_btn").html("Continue...");
					}
				}
			}
		}
		catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
	});
	return false;
}

function address_updated()
{
	var cripto_ref = "<?php echo $crypto_currency; ?>:" + crypto_address + "?amount=<?php echo $total_BTC; ?>";
	$(".launch_crypto_app").attr("href", cripto_ref); 
	var keyValuePair = {
		"qrcode_public": cripto_ref
	};
	ninja.qrCode.showQrCode(keyValuePair, 4);

	$("#add_purchase_wait").hide();
	$("#address_table").show();
	$("#selectable_adr").html(crypto_address);
	
	check_payment();
	check_payment_timer = setInterval("check_payment();", 10000);
}

function finish_get_address()
{
	if ( crypto_address.length == 0 ) {
		if (crypto_currency == "bitcoin")
			crypto_address = generate_bitcoin_address(key_random_seed);
		else
			crypto_address = generate_litecoin_address(key_random_seed);
		if ( crypto_address.length == 0 ) {
			check_payment_timer = setTimeout("finish_get_address();", 5000);
			return false;
		}
	}
	if ( crypto_address.length > 0 ) {
		if (crypto_currency == "bitcoin")
			privWif = get_bitcoin_privWif();
		else
			privWif = get_litecoin_privWif();
		$.ajax({
			method: "POST",
			url: "/api/user_update_crypto_addr/",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", crypto_currency: "<?php echo $crypto_currency; ?>", address: crypto_address, private_address: privWif, total_BTC: "<?php echo urlencode($total_BTC); ?>", invoice: "<?php echo urlencode($invoice); ?>" }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] && arr_ajax__result["values"] ) {
					crypto_address = arr_ajax__result["values"]["pay_email"];
					address_updated();
				}
			}
			catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
		});
		crypto_address = "";
	}
	else {
		address_updated();
	}
}

var key_random_seed = "<?php echo md5($user_account->psw_hash.date('Y-m-d-h-s').(rand(1, 1000000))); ?>";

function random_seed(event) 
{
	if ( crypto_address.length > 0 || key_random_seed.length > 256)
		return false;
	try
	{
		if (!evt) var evt = window.event;
		var timeStamp = new Date().getTime();
		if (evt.clientX && evt.clientY) {
			var tmp = evt.clientX * evt.clientY;
			tmp_k = tmp.toString(16);
			key_random_seed = key_random_seed + tmp_k;
		}
	}
	catch(error){}
}

$(document).ready(function(){
	if ( crypto_address.length > 0 ) {
		address_updated();
	}
	else {
		setTimeout(finish_get_address, 5000);
	}
});

$( document ).on( "mousemove", function( event ) {
  random_seed(event);
});
</script>

<?php
echo $bitcoin->get_javascript_to_generate_address();
?>
</body>
</html>
