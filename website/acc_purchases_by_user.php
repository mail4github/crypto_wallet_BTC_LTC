<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'new_acc_purchases_by_user.php') ) {
	require(DIR_WS_TEMP_CUSTOM_CODE.'new_acc_purchases_by_user.php');
	exit;
}

$page_header = 'Purchases';
$page_title = $page_header;
$page_desc = $page_header;

require(DIR_WS_INCLUDES.'header.php');

if ( is_file_variable_expired('list_of_services', 30) || defined('DEBUG_MODE') ) {
	$services = get_api_value('get_list_of_services', '', ['condition' => 'account upgrades'], '', null, false, 1);
	if ( $services !== false && !empty($services) )
		update_file_variable('list_of_services', json_encode($services));
}
$services = json_decode(get_file_variable('list_of_services'), 1);
echo '<div id="account_upgrades">';
foreach ($services as $service) {
	echo '
	<div class="box_type3 row" style="background-color:'.($service['servicetype'] == 'GA' ? '#fff5b0' : '#e9e9e9').'; margin-bottom:15px;">
		<div class="col-xs-6"><h3 style="font-size:150%;">'.$service['name'].'</h3></div>
		<div class="col-xs-6"><h3 style="text-align:right; font-size:130%;"><span class=notranslate>'.currency_format($service['price'], '', '', '', false, false, DOLLAR_SIGN, DOLLAR_DECIMALS).'</span> / month</h3></div>
		<div class="col-xs-12">
			'.$service['units_name'].'
			<button class="btn btn-success" style="padding:6px 30px; display:block; margin:auto;" onclick="buy_subscription(\''.$service['servicetype'].'\', \''.$service['name'].'\', \''.currency_format($service['price'], '', '', '', false, false, DOLLAR_SIGN, DOLLAR_DECIMALS).'\');">Purchase '.$service['name'].'...</button>
		</div>
	</div>
	';
}
echo '</div>';
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
<script type="text/javascript">
var html_code_sources_to_pay = "";
var selected_subscription_id = "";
var selected_subscription_description = "";

function buy_subscription(subscription_id, subscription_description, price)
{
	if ( html_code_sources_to_pay.length == 0 ) {
		alert("Insufficient funds!!!");
		return false;
	}
	selected_subscription_id = subscription_id;
	selected_subscription_description = subscription_description;
	show_box_yesno_box("<h3 style='padding-top:0px;'>Confirm purchase:</h3><p>You agree to pay <b class=notranslate>" + price + "</b> every month for " + subscription_description + ".</p>" + html_code_sources_to_pay, "purchase_confirmed", "Purchasing Subscription", "Confirm", "Cancel", null);
}

function purchase_confirmed()
{
	$("#wait_sign").show();

	var currency = $("input[name=from_balance_pay_currency]").filter(":checked").val();
	var crypto_cl = new Crypto( currency );
	if (crypto_cl)
		currency = crypto_cl.crypto_symbol;
	$.ajax({
		method: "POST",
		url: "/api/user_get_request_to_pay_cart",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", add_funds: 1, payment_method:"balance", make_post_request: false, pay_email:"none", cross_payoutid:0, total:0, transaction_prefix: "<?php echo UPGRADE_PREFIX; ?>", note:"", currency:currency, currency_symbol:"", invoice_suffix:selected_subscription_id, /*return_post_request_as_text:1, force_to_use_pay_email:1,*/ ip:"<?php echo $_SERVER['REMOTE_ADDR']; ?>"},
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			$("#wait_sign").hide();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				show_message_box_box("Success", "The " + selected_subscription_description + " has been activated", 1, "", "redirect_on_success");
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Cannot make purchase", 2, "", "redirect_on_success");
		}
	});
}

function redirect_on_success()
{
	window.location.reload();
	return false;
}

function on_balance_received(balances_arr)
{
	html_code_sources_to_pay = "";
	for (i = 0; i < balances_arr.length; i++) { 
		if (balances_arr[i]["currency"] != "<?php echo strtolower(DOLLAR_NAME); ?>" && balances_arr[i]["amount"] > 0){
			html_code_sources_to_pay = html_code_sources_to_pay + "<p><input type=radio name=from_balance_pay_currency value=" + balances_arr[i]["currency"] + (html_code_sources_to_pay.length == 0?" checked":"") + ">&nbsp;&nbsp;" + balances_arr[i]["description"] + "&nbsp;&nbsp; <span class=notranslate>" + currency_format(balances_arr[i]["available_funds"], balances_arr[i]["symbol"], "color:#008800", "color:#FF0000", undefined, balances_arr[i]["digits"]) + "</span></p>";
		}
	}
	if (html_code_sources_to_pay.length > 0)
		html_code_sources_to_pay = "<p>Select the source of payment:</p>" + html_code_sources_to_pay;
}

function cancel_upgrade()
{
	show_box_yesno_box("Do you really want to cancel subscription?", "cancel_upgrade_confirmed", "Canceling Subscription", "Confirm");
}

function cancel_upgrade_confirmed()
{
	$("#wait_sign").show();

	$.ajax({
		method: "POST",
		url: "/api/user_cancel_upgrade",
		data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>"},
		cache: false
	})
	.done(function( ajax__result ) {
		try
		{
			$("#wait_sign").hide();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				show_message_box_box("Success", "The subscription has been cancelled", 1, "", "redirect_on_success");
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Cannot cancell subscription", 2, "", "redirect_on_success");
		}
	});
}

$( document ).ready(function() {
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
				if (arr_ajax__result["values"]["account_type"] != "B") {
					var text = "<div class='box_type3 row' style='background-color:" + (arr_ajax__result["values"]['account_type'] == 'G' ? '#fff5b0' : '#e9e9e9') + "; margin-bottom:15px;'><div class='col-xs-6'><h3 style='font-size:150%;'>" + arr_ajax__result["values"]['name'] + "</h3></div><div class='col-xs-6'><h3 style='text-align:right; font-size:130%;'><span class=notranslate>" + currency_format(arr_ajax__result["values"]['price'], "<?php echo DOLLAR_SIGN; ?>", "", "", undefined, <?php echo DOLLAR_DECIMALS; ?>) + "</span> / month</h3></div><div class='col-xs-12'>" + arr_ajax__result["values"]['units_name'];
					if ( Number(arr_ajax__result["values"]['done']) == 0 )
						text = text + "<button class='btn btn-warning' style='padding:6px 30px; display:block; margin:auto;' onclick='cancel_upgrade();'>Cancel Subscription...</button>"
					else
						text = text + "<h4>Expires on " + get_text_between_tags(arr_ajax__result["values"]['expires'], "", " ") + "</h4>";
					text = text + "</div></div>";
					$("#account_upgrades").html(text);
				}
			}
			
		}
		catch(error){}
	});
});

</script>
</body>
</html>
