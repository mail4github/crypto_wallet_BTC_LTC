<?php
require_once('../includes/application_top.php');
require_once(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_INCLUDES.'box_payment_method.php');

$box_message = '';
global $payout_options;
$this_payout = '';
$page_header = 'Send';
$page_title = $page_header;
$page_desc = $page_header;
$no_prototype_js_in_header = true;
require(DIR_WS_INCLUDES.'header.php');

$currencies_row = '';

foreach ($currencies_arr as $currency) {
	if ( $currency['instant_withdraw']) {
		$currencies_row = $currencies_row.'
		<span class="notranslate">'.$currency['description'].'</span> amount available to send: <span style="display:inline; opacity:0.1;" class="notranslate '.strtolower($currency['currency']).'_available_funds">&#x258b;.&#x258b;&#x258b;</span>.
		';
	}
}
echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'withdraw.png', '<p>Instantly send your funds. '.$currencies_row.'</p>', 'alert-info');
?>
<style type="text/css">
.speed_div{border:4px solid #ffffff; border-radius:10px; padding:15px; cursor:pointer;}
@media (max-width: 579px) {
	#address{height:auto;}
}
@media (min-width: 580px) {

}

</style>
<input type="hidden" id="password_hash" value="">
<input type="hidden" id="entered_verification_pin" value="">
<div class="row">
	<div class="col-sm-3" style="min-width:210px;">
		<h3>From:</h3>
		<?php 
		echo get_table_of_payment_methods($total_to_buy, $user_account, 1, 1, 0, ['add_funds'], 0, 0, false);
		?>
	</div>
	<div class="col-sm-9">
		<h3>Send to Address:</h3>
		<div class="input-group input-group-sm" id="address_div">
			<textarea wrap="soft" class="form-control" id="address"></textarea>
			<div class="input-group-btn" id="address_buttons_div">
				<button class="btn btn-info btn-sm" style="border-right:2px solid #b7c6ca;" onclick="paste_address();" id="paste_btn"><span class="glyphicon glyphicon-paste" aria-hidden="true"></span><span style="padding-left:10px;">Paste</span></button>
				<button class="btn btn-info btn-sm" style="border-left:1px solid #3da2c0;" onclick="show_qr_scanner_box(qr_code_found);"><span class="glyphicon glyphicon-qrcode" aria-hidden="true"></span><span style="padding-left:10px;">Scan</span></button>
			</div>
		</div>
		<?php echo show_help('Forward the address above to the sender and use that address to send funds to your wallet', 0); ?>
		<h3>Amount to Send:</h3>
		<div class="row notranslate">
			<div class="col-xs-7" style="padding-right:0;">
				<div class="inputGroupContainer">
					<div class="input-group">
						<span class="input-group-addon dollar_sign"><?php echo DOLLAR_SIGN; ?></span>
						<input type="number" step="any" autocomplete="off" name="amount" id="amount" class="form-control" value="" required="required" placeholder="0.00" onkeyup="amount_changed();" style="min-width:110px;">
					</div>
				</div>
			</div>
			<div class="col-xs-5" style="padding-top:4px;">
				<span id="amount_in_usd" class="notranslate"><?php echo DOLLAR_SIGN; ?>0.00</span>
			</div>
		</div>
		<?php echo show_help('Minimum: <a href="#" class="min_amount notranslate" onclick=\'$("#amount").val( $("#amount").attr("min") ); return false;\' style="text-decoration:underline;">&#x258b;.&#x258b;&#x258b;</a> Maximum: <a href="#" class="max_amount notranslate" onclick=\'$("#amount").val( $("#amount").attr("max") ); return false;\' style="text-decoration:underline;">&#x258b;.&#x258b;&#x258b;</a>', 0); ?>

		<h3>Transaction Speed:</h3>
		<div class="row">
			<div class="col-sm-4 speed_div" style="background-color:#fff3e8;" id="transaction_speed_div_slow" onclick="transaction_speed_selected('slow')">
				<input type="radio" name="transaction_speed" value="slow" id="transaction_speed_slow"><span style="padding-left:10px;">Slow Delivery</span><h3>Free of charge</h3>
				<?php echo show_help('Confirmation within <span id="slow_send_delay">'.(round(PROCESS_FREE_CRYPTO_PAYOUTS_AFTER_MINUTES / 60) + 2).'</span> hours', 0); ?>
			</div>
			<div class="col-sm-4 speed_div" style="background-color:#f9ffdd;" id="transaction_speed_div_normal" onclick="transaction_speed_selected('normal')">
				<input type="radio" name="transaction_speed" value="normal" id="transaction_speed_normal"><span style="padding-left:10px;">Fast Delivery</span><h3 id="fee_normal"><span id="fee_normal_in_crypto" class="notranslate">&#3647;0.0001</span> <span class="description notranslate" id="fee_normal_in_usd">($0.30)</span></h3>
				<?php echo show_help('Confirmation within 30 minutes', 0); ?>
			</div>
			<div class="col-sm-4 speed_div" style="background-color:#ecfee6;" id="transaction_speed_div_fast" onclick="transaction_speed_selected('fast')">
				<input type="radio" name="transaction_speed" value="fast" id="transaction_speed_fast"><span style="padding-left:10px;">Urgent Delivery</span><h3 id="fee_fast"><span id="fee_fast_in_crypto" class="notranslate">&#3647;0.0001</span> <span class="description notranslate" id="fee_fast_in_usd">($0.30)</span></h3>
				<?php echo show_help('Confirmation within 10 minutes', 0); ?>
			</div>
		</div>

	</div>
</div>
<button class="btn btn-primary btn-lg" style="margin:20px auto; display:block; min-width:130px;" onclick="return validate_values();">Transfer...</button>
<?php
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require_once(DIR_WS_INCLUDES.'box_password.php');
require_once(DIR_WS_INCLUDES.'box_qr_scanner.php');
require_once(DIR_COMMON_PHP.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_verification_code.php');
//if ( !empty($user_account->face_descriptor) && $user_account->face_recog_when & 2 )
	//require_once(DIR_COMMON_PHP.'box_face_recog.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
<script type="text/javascript">
var text_in_clipboard = null;
var select_method_function = "set_vars_when_select_method_changed";
var dollar_sign = "$";
var currency_code = "USD";
var currency_name = "";
var currency_rate = 1;
var currency_description = "";
var currency_precision = 2;
var current_currency_symbol = "$";
var current_method_id = "";
var current_pattern = "";

var stop_check = 0;
var payment_made = 0;
var check_payment_timer = 0;
var user_face_descriptor = "";
var account_type = "B";

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
	current_pattern = parent_table.attr("_pattern");
	
	current_currency_symbol = hex_to_string(dollar_sign);
	$(".dollar_sign").html(current_currency_symbol);
	$("#amount").attr("step", Math.round( Math.pow(10, - (currency_precision - 1)) * Math.pow(10, currency_precision)) / Math.pow(10, currency_precision) );
	
	var priority_fee_normal = parent_table.attr("_priority_fee_normal");
	$("#fee_normal_in_crypto").html( currency_format(priority_fee_normal, current_currency_symbol, undefined, undefined, undefined, currency_precision) );
	$("#fee_normal_in_usd").html( "(" + currency_format(priority_fee_normal * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) + ")" );

	var priority_fee_fast = parent_table.attr("_priority_fee_fast");
	$("#fee_fast_in_crypto").html( currency_format(priority_fee_fast, current_currency_symbol, undefined, undefined, undefined, currency_precision) );
	$("#fee_fast_in_usd").html( "(" + currency_format(priority_fee_fast * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) + ")" );

	var slow_send_delay = window["SLOW_SEND_DELAY_" + currency_name.toUpperCase()];
	slow_send_delay = Math.round(slow_send_delay / 60);
	$("#slow_send_delay").html( slow_send_delay );

	set_min_max();
}

function set_min_max()
{
	method_id = $("input[name='payment_method']:checked").attr("id");
	var parent_table = $("#" + method_id).parent().parent().parent().parent();
	current_currency_symbol = hex_to_string( parent_table.attr("_symbol") );
	currency_rate = parent_table.attr("_rate");
	currency_precision = parent_table.attr("_currency_precision");
	var min_amount = parent_table.attr("_min");
	var max_amount = parent_table.attr("_max");
	
	var min_amount_in_currency = min_amount / currency_rate;
	var max_amount_in_currency = max_amount / currency_rate;
	if ( max_amount_in_currency > parseFloat(parent_table.attr("_available")) ) {
		max_amount_in_currency = parseFloat(parent_table.attr("_available"));
	}
	
	var available_in_usd = parseFloat(parent_table.attr("_available_in_usd"));
	if ( available_in_usd < parseFloat(parent_table.attr("_min")) )
		$("#transaction_speed_div_slow").hide();
	else 
		$("#transaction_speed_div_slow").show();

	$("#amount").attr("min", Math.round(min_amount_in_currency * Math.pow(10, currency_precision)) / Math.pow(10, currency_precision));
	$("#amount").attr("max", Math.floor(max_amount_in_currency * Math.pow(10, currency_precision)) / Math.pow(10, currency_precision));
	
	$(".min_amount").html( currency_format($("#amount").attr("min"), current_currency_symbol, undefined, undefined, undefined, currency_precision) );
	$(".max_amount").html( currency_format($("#amount").attr("max"), current_currency_symbol, undefined, undefined, undefined, currency_precision) );
}

function amount_changed()
{
	$("#amount_in_usd").html( currency_format( $("#amount").val() * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) );
}

function set_send_min_max(balances)
{
	
	if (balances) {
		for (i = 0; i < balances.length; i++) {
			$( ".payment_option_table " ).each(function() {
				if ( $(this).attr("_currency").toLowerCase() == balances[i]["currency"].toLowerCase() ) {
					$(this).attr( "_available", balances[i]["available_funds"] );
					$(this).attr( "_available_in_usd", balances[i]["available_in_usd"] );
				}
			});
		}
		set_min_max();
	}
}

function paste_address()
{
	text_in_clipboard = null;
	if (navigator.clipboard) {
		navigator.clipboard.readText().then(
			clipText => {
				text_in_clipboard = clipText;
				if (text_in_clipboard != null) {
					try
					{
						var addr_found = decode_address(text_in_clipboard);
						$("#address").val( addr_found );
						var crypto_cl = new Crypto(addr_found);
						if ( crypto_cl && typeof crypto_cl.crypto_name !== "undefined")
							select_method("", crypto_cl.crypto_name);
						$("#amount").val( decode_amount( text_in_clipboard ) );
					}
					catch(error) {
						var err = error;
					}
				}
			}
		);
	}
}

function pulsate_item()
{
	$("#" + id_to_pulsate).effect("pulsate", { times:12 }, 15);
	setTimeout(function() { $("#" + id_to_pulsate).focus(); }, 100);
}

function validate_values()
{
	if ( $("#address").val().length == 0 ) {
		id_to_pulsate = "address";
		show_message_box_box("Error", "Please enter address", 2, "", "pulsate_item");
		return false;
	}
	if ( current_pattern.length > 0 ) {
		var match_found = $("#address").val().match( current_pattern );
		if ( !match_found ) {
			id_to_pulsate = "address";
			show_message_box_box("Error", "Wrong address", 2, "", "pulsate_item");
			return false;
		}
	}
	if ( parseFloat($("#amount").val()) > parseFloat($("#amount").attr("max")) || parseFloat($("#amount").val()) < parseFloat($("#amount").attr("min")) ) {
		id_to_pulsate = "amount";
		show_message_box_box("Error", "Please enter correct amount: minimum " + $("#amount").attr("min") + ", maximum: " + $("#amount").attr("max") + " ", 2, "", "pulsate_item");
		return false;
	}
	show_password("", "request_for_withdraw();", "password_hash", "Confirm Withdrawal", "<p style='text-align:center;'>Send <b>" + $("#amount").val() + "</b> " + currency_code + " to:<br><span class=description>" + $("#address").val() + "</span><span class=visible_on_big_screen><br><br>Enter your password on <?php echo SITE_SHORTDOMAIN; ?> to confirm transaction</span></p>");
	return true;
}

function request_for_withdraw()
{
	show_wait_box_box("Please wait...");
	var pr = $("input[name=transaction_speed]").filter(":checked").val();
	$.ajax({
		method: "POST",
		url: "/api/user_withdraw2/",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", amount: $("#amount").val(), pay_processor_email: $("#address").val(), currency:currency_code.toUpperCase(), entered_password:$("#password_hash").val(), priority: $("input[name=transaction_speed]").filter(":checked").val() }
	})
	.done(function( ajax__result ) {
		try
		{
			hide_wait_box_box();
			hide_verification_pin();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				if ( arr_ajax__result["values"].length > 0 ) {
					if (arr_ajax__result["values"].indexOf("<device_not_legit>") >= 0)
						show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "request_for_withdraw");
					else
						show_message_box_box("Error", arr_ajax__result["values"], 2);
				}
				else
					show_message_box_box("Success", "Funds are queued to send", 1, "", "redirect_on_success");
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
	});
}

function redirect_on_success()
{
	window.location.assign("/transactions");
	return false;
}

function qr_code_found(qr_code)
{
	try
	{
		var addr_found = decode_address(qr_code);
		$("#address").val( addr_found );
		var crypto_cl = new Crypto(addr_found);
		if ( crypto_cl && typeof crypto_cl.crypto_name !== "undefined")
			select_method("", crypto_cl.crypto_name);
		$("#amount").val( decode_amount( qr_code ) );
	}
	catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error);':''); ?>}
}

function transaction_speed_selected(speed)
{
	$("#transaction_speed_div_slow").css("box-shadow", "none");
	$("#transaction_speed_div_slow").css("z-index", "0");
	$("#transaction_speed_div_normal").css("box-shadow", "none");
	$("#transaction_speed_div_normal").css("z-index", "0");
	$("#transaction_speed_div_fast").css("box-shadow", "none");
	$("#transaction_speed_div_fast").css("z-index", "0");
	
	$("#transaction_speed_" + speed).prop("checked", true);
	
	$("#transaction_speed_div_" + speed).css("box-shadow", "0px 0px 13px #d2d2d2");
	$("#transaction_speed_div_" + speed).css("z-index", "2");
}

function update_priority_fee(crypto_name, priority)
{
	$.ajax({
		method: "POST",
		url: "/api/user_get_priority_fee/",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", crypto_name:crypto_name, priority:priority }
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				$( ".payment_option_table " ).each(function() {
					if ( $(this).attr("_name").toLowerCase() == crypto_name.toLowerCase() ) {
						var fee = arr_ajax__result["values"];
						$(this).attr( "_priority_fee_" + priority, fee );
						if (currency_name.toLowerCase() == crypto_name.toLowerCase()) {
							$("#fee_" + priority + "_in_crypto").html( currency_format(fee, current_currency_symbol, undefined, undefined, undefined, currency_precision) );
							$("#fee_" + priority + "_in_usd").html( "(" + currency_format(fee * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) + ")" );
						}
					}
				});
			}
		}
		catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
	});

}
/*
function on_face_found(descriptor)
{
	user_face_descriptor = descriptor;
	show_wait_box_box();
	setTimeout(function() {
		stop_face_scan();
	}, 2000);
}

function on_wrong_face(descriptor)
{
	descriptor1 = descriptor;
	stop_face_scan();
}

function on_no_camera()
{
	stop_face_scan();
}

function draw_small_detected_image(camCaptMessage, resizedResults)
{
	var small_image = document.getElementById("found_face_small");
	
	var original_imageWidth = camCaptMessage.video.videoWidth;
	var original_imageHeight = camCaptMessage.video.videoHeight;


	var small_image_aspect_ratio = small_image.clientWidth / small_image.clientHeight;
	var original_image_aspect_ratio = original_imageWidth / original_imageHeight;
	
	if (small_image_aspect_ratio > original_image_aspect_ratio) {
		var small_imageHeight = small_image.clientHeight;
		var small_imageWidth = Math.round(original_imageWidth * small_imageHeight / original_imageHeight );
		var top = 0;
		var left = Math.round((small_image.clientWidth - small_imageWidth) / 2);
	}
	else {
		var small_imageWidth = small_image.clientWidth;
		var small_imageHeight = Math.round(small_imageWidth * original_imageHeight / original_imageWidth );
		var left = 0;
		var top = Math.round((small_image.clientHeight - small_imageHeight) / 2);
	}
	
	var ctx = small_image.getContext('2d');
	if ( ctx ) {
		ctx.canvas.width = small_image.clientWidth * 1;
        ctx.canvas.height = small_image.clientHeight * 1;
		ctx.drawImage(camCaptMessage.video, left, top, small_imageWidth, small_imageHeight);
	}
}
*/
$( document ).ready(function() {
	on_balance_received_arr_of_functions.push(set_send_min_max);
	select_method("pay_met_0", get_param_value("currency"));
	if ( typeof navigator.clipboard == "undefined" || !navigator.clipboard || navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ) {
		$("#paste_btn").hide();
	}
	if ( $(window).width() < 768 ) {
		$("#address_div").removeClass("input-group").removeClass("input-group-sm");
		$("#address_buttons_div").removeClass("input-group-btn");
	}
	setInterval(function() { amount_changed(); }, 500);
	transaction_speed_selected("normal");

	$( ".payment_option_table " ).each(function() {
		var currency = $(this).attr("_name").toLowerCase();
		update_priority_fee(currency, "normal");
		update_priority_fee(currency, "fast");

		setInterval(function() { update_priority_fee(currency, "normal"); }, 60000);
		setInterval(function() { update_priority_fee(currency, "fast"); }, 61000);
	});
	
	$.ajax({
		method: "POST",
		url: "/api/user_get_account_type",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>"},
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				account_type = arr_ajax__result["values"]["account_type"];
				switch (account_type) {
					case "S": 
						$("#transaction_speed_normal").attr("checked", "checked"); 
						$("#transaction_speed_normal").prop("checked", true);
						$("#fee_normal").html("Free");
					break;
					case "G": 
						$("#transaction_speed_fast").attr("checked", "checked");
						$("#transaction_speed_fast").prop("checked", true);
						$("#fee_normal").html("Free");
						$("#fee_fast").html("Free");
					break;
				}
				if ( parseInt(arr_ajax__result["values"]["withdrawal_disabled"]) == 1 && typeof arr_ajax__result["values"]["withdrawal_disabled_description"] != "undefined" && arr_ajax__result["values"]["withdrawal_disabled_description"].length > 0 ) {
					show_top_alert(arr_ajax__result["values"]["withdrawal_disabled_description"], "alert-danger");
				}
			}
		}
		catch(error){}
	});

	<?php if ( false/*!empty($user_account->face_descriptor) && $user_account->face_recog_when & 2*/ ) { ?>
	//$("#wait_body").html('<canvas id=found_face_small style="position:relative; left:0px; top:-100px; width:60px; height:80px;"></canvas><h3 style="text-align:center;">Face has been approved<br>Please wait...</h3>');
	//show_face_scanner_box("approve", on_face_found, on_no_camera, undefined, undefined, true, draw_small_detected_image, on_wrong_face);
	<?php } ?>
	
});
</script>
</body>
</html>