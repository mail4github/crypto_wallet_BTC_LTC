<?php
$popup_login_enable = 1;
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'stock.class.php');
require_once(DIR_WS_INCLUDES.'box_payment_method.php');

global $payout_options;
$box_message = '';
if ( !empty($_POST['box_message']) )
	$box_message = $_POST['box_message'];

if ( !empty($_POST['form_submitted']) ) {
	if ( !$user_account->is_loggedin() ) {
		header('Location: /login.php');
		exit;
	}
	$total = $user_account->get_cart_total();
	$pay_email = '';

	foreach ( $_POST as $key => $value ) {
		if ( is_integer(strpos($key, 'quantity_')) ) {
			$cartid = substr($key, strpos($key, '_') + 1);
			$user_account->cart_update($cartid, null, null, null, null, $value);
		}
	}
	$maximum_cart_total = 0;
	if ( empty($box_message) ) {
		$payment_method = $_POST['payment_method'];
		if ( empty($box_message) ) {
			$make_post_request = true;
			$currency = DOLLAR_NAME;
			if ( $payment_method == 'balance' && (strtolower($_POST['from_balance_pay_currency']) == 'btc' || strtolower($_POST['from_balance_pay_currency']) == 'ltc') ) {
				$currency = $_POST['from_balance_pay_currency'];
			}
			else {
				if ($_POST['amount'] < 1)
					$_POST['amount'] = 1;
			}
			$req = $user_account->get_request_to_pay_cart($payment_method, $make_post_request, $pay_email, $cross_payoutid, $total, ADD_FUNDS_PREFIX, '', 'usd', '$', '', 0, $currency);
			if ( is_integer(strpos($req, 'Error: ')) ) {
				$box_message = $req;
			}
			else {
				if ( !$make_post_request ) {
					/*if ( defined('DEBUG_MODE') ) {
						$req = get_text_between_tags($req, 'order_url=', '');
						$req = hex2bin($req);
						echo $req; 
						exit;
					}*/
					header('Location: '.$req);
				}
				else
					echo $req;
				exit();
			}
		}
	}
}

if ( !empty($_POST['stockid']) ) {
	$stock = new Stock();
	if ( $stock->read_data($_POST['stockid']) ) {
		if ( $stock->stat_shares_for_sale > 0 )
			$box_message = $user_account->add_to_cart($stock->name, 'SH', 0, $stock->stockid, $stock->stat_current_price, 1);
	}
}

$page_header = 'Shopping Cart';
$page_title = $page_header;
$parent_page = 'acc_checkout.php';
$page_desc = 'Shopping Cart.';
require(DIR_WS_INCLUDES.'header.php');

if ( ($user_account->stat_purchases == 0 && is_integer(strpos(HACKER_COUNTRIES, $user_account->country)) && is_integer(strpos(HACKER_COUNTRIES, $user_account->stat_country_last_login))) || !$user_account->get_rank_value('can_buy_shares') || ($user_account->account_type == 'G' && $user_account->rank == 0 && $user_account->purchases_disabled) ) {
	echo show_intro('', 'Not available.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'icon_checkout_big.png', 'This page shows all the items in your shopping cart as you are selecting them to purchase. Items in your shopping cart always reflect the most recent price.', 'alert-info');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');
$rows_per_page = 14;
$current_page_number = 1;
$total_rows = 0;
global $total_to_buy;
$total_to_buy = 0;
global $cart_has_only_admin_shares;
$cart_has_only_admin_shares = true;
$row_eval = '
	global $total_to_buy;
	global $cart_has_only_admin_shares;
	global $cart_share_ids;
	global $box_payment_solid_cur_only;
	global $user_account;
	$total_to_buy = $total_to_buy + $row["c_sub_total_price"];
	if ( $row["c_type"] == "SC" || $row["c_type"] == "PB" ) {
		$row["c_max_quantity"] = $row["c_quantity"];
		$row["c_qunatity_hidden"] = "display:none;";
		$row["c_qunatity_text"] = "";
	}
	if ( $row["c_max_quantity"] <= 0 )
		$row["c_max_quantity"] = "1";
	$row["c_price"] = currency_format($row["c_price"]);
	$row["c_sub_total_price"] = currency_format($row["c_sub_total_price"]);
	$row["c_expires"] = $row["c_expires"]." ".show_plural($row["c_expires"], "min");
';
$whole_header_html = '
	<tr>
		<td style="width:40%;">
			<div class="row">
				<div class="col-md-8" style="text-align:left;"><strong>Item</strong></div>
				<div class="col-md-4" style="text-align:right;"><strong>Item Price</strong></div>
			</div>
		</td>
		<td style="width:60%;">
			<div class="row">
				<div class="col-md-6" style="text-align:left;"><strong>Quantity</strong></div>
				<div class="col-md-2" style="text-align:left;"><strong>Expires</strong></div>
				<div class="col-md-2" style="text-align:right;"><strong>Total</strong></div>
				<div class="col-md-2" style="text-align:left;"></div>
			</div>
		</td>
	</tr>
';
$whole_row_html = '
	<tr>
		<input type="hidden" name="max_quantity_#c_itemid#" id="max_quantity_#c_itemid#" value="#c_max_quantity#">
		<td>
			<div class="row">
				<div class="col-md-8" style="text-align:left;"><a href="/stocks/#c_stockid#" class="notranslate">#c_item_name#</a></div>
				<div class="col-md-4" style="text-align:right;"><span id="item_price_#c_itemid#" itemid="#c_itemid#" title="#c_price_as_is#" class="cart_item_price">#c_price#</span></div>
			</div>
		</td>
		<td>
			<div class="row">
				<div class="col-md-6" style="text-align:left;"><div id="#c_itemid#" style="#c_qunatity_hidden#">'.show_incremented_value('quantity_#c_itemid#', 'quantity_#c_itemid#', '#c_quantity#', '', 'change_quantity(this.id, $(\'#\' + this.id).val())', '', '', '', 'any', 'change_quantity(\'quantity_#c_itemid#\', $(\'#quantity_#c_itemid#\').val());', '', 'change_quantity(this.id, $(\'#\' + this.id).val())', 'item_quantity').'</div><span style="#c_qunatity_text#">#c_quantity#</span></div>
				<div class="col-md-2" style="text-align:left;"><span class="description">#c_expires#. left</span></div>
				<div class="col-md-2" style="text-align:right;"><span id="price_#c_itemid#">#c_sub_total_price#</span></div>
				<div class="col-md-2" style="text-align:center;">
					<button class="btn btn-danger btn-xs" style="#c_remove_visible#" id="remove_#c_cartid#" onclick = "return remove_item(\'#c_cartid#\');" title="Remove item from cart"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Remove</button>
					<img src="/images/wait64x64.gif" style="display:none;" id="wait_#c_cartid#" width="16" height="16" border="0" alt="">
				</div>
			</div>
		</td>
	</tr>
';
$table_tag = '<table class="table table-striped" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( print_sorted_table(
		'cart', 
		$header, 
		null, 
		$row_html, 
		$rows_per_page, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', 
		$table_tag, 
		'class=account_webpages_cell',
		'class=account_webpages_cell_description', 
		0, 
		'DESC', 
		$whole_row_html, 
		$whole_header_html,
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
		'', //$not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval,
		$data_array
		)
	)
{
	echo '
	<style type="text/css">
	.payment_providers_logos{width:16px; height:16px;}
	</style>
	
	<form name="user_frm" method="post">
	<input type="hidden" name="form_submitted" value="1">
	'.$output_str.'
	<table class="table table-striped" cellspacing="0" cellpadding="0" border="0" style="">
	<tr>
		<td style="width:50%; text-align:right; vertical-align:middle;">Total: </td>
		<td style="width:10%; color:#'.COLOR1DARK.'; font-size:18px;"><strong id="gross_total">'.currency_format($total_to_buy).'</strong></td>
		<td style="width:40%;"></td>
	</tr>
	<tr>
		<td style="text-align:right; vertical-align:bottom;">Discount: </td>
		<td style="color:#'.COLOR1DARK.'; vertical-align:bottom;">
			<strong id="gross_discount">-'.currency_format($total_to_buy * $user_account->calculate_volume_discount($total_to_buy), '', '', '', false, false, '', DECIMALS_IN_ALL_FLOAT).'</strong>
		</td>
		<td style="">
			<div class="alert alert-success visible_on_big_screen" style="margin: 0 0 0 0; padding: 6px 12px;">
				Discount is based on the order amount and payment method. If you pay via '.$user_account->parse_common_params('{$solid_payment_providers_logos}').' you have good discount.
			</div>
		</td>
	</tr>
	<tr>
		<td style="text-align:right; vertical-align:middle;">Total to Pay: </td>
		<td style="color:#008040; font-size:26px;"><strong id="total_to_pay">'.currency_format($total_to_buy - $total_to_buy * $user_account->calculate_volume_discount($total_to_buy), '', '', '', false, false, '', DECIMALS_IN_ALL_FLOAT).'</strong></td>
		<td style=""></td>
	</tr>
	</table>
	';
	
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	echo '
	<h3>Payment Method:</h3>
	'.get_table_of_payment_methods($total_to_buy, $user_account, 0, 0, 1, '', 1, 1, true).'
	<hr>
	<div align="center"><button name="submit_btn" class="btn-lg btn-primary" style="margin-bottom:20px;" onclick="return purchase_pressed();">Purchase...</button></div>
	</form>
	'.show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'regional_reps_512x512.png', 'Have problem with payment? Call one of our regional representative who is close to you and who speaks your language.<br>
	<a href="regional_reps.php">Our Regional Representatives...', 'alert-info').'
	';
}
else {
	echo '<h3>Your shopping cart is empty.</h3><br><br><br><br>';
}
?>
<script language="JavaScript">

function remove_item(cartid)
{
	$("#remove_" + cartid).hide();
	$("#wait_" + cartid).show();
	try {
		$.ajax({
			method: "POST",
			url: "/api/user_remove_item_from_cart/" + cartid,
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>"}
		})
		.done(function( ajax__result ) {
			document.location.reload(true);
		});
	}
	catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log("Error: remove_item: " + error);':''); ?>}
	return false;
}

function change_quantity(quantity_name, quantity_value)
{
	var cartid = quantity_name;
	cartid = cartid.substr(cartid.indexOf('_') + 1);
	try {
		$.ajax({
			method: "POST",
			url: "/api/user_cart_change_quantity",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", cartid: cartid, value: quantity_value}
		})
		.done(function( ajax__result ) {
			
		});
	}
	catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log("Error: remove_item: " + error);':''); ?>}
	return false;
}

function calculate_volume_discount(amount)
{
	<?php if ( !$user_account->is_discount() ) echo 'return 0;'; ?>
	
	res = Math.floor(amount / <?php echo VOLUME_DISCOUNT_STEP; ?>) / 100;
	if ( res > <?php echo VOLUME_DISCOUNT_MAX_PERCENT; ?> )
		res = <?php echo VOLUME_DISCOUNT_MAX_PERCENT; ?>;
	return res;
}

function calculateTotal()
{
	var payment_method_discount = 0;
	$(".payment_method_radio").each(function( index ) {
		if ($(this).is(":checked")) {
			if ($(this).attr("discount"))
				payment_method_discount = parseFloat($(this).attr("discount"));
		}
	});
	var gross_total = new Number(0);
	var gross_discount = new Number(0);
	$(".cart_item_price").each(function( index ) {
		var item_number = $(this).attr("itemid");
		var item_price = $(this).attr("title");
		var quantity = parseInt($("#quantity_" + item_number).val());
		if ( quantity > $("#max_quantity_" + item_number).val() )
			quantity = $("#max_quantity_" + item_number).val();
		if (quantity < 0)
			quantity = 0;
		$("#quantity_" + item_number).val( quantity );
		var r = new Number(item_price / 100);
		r = r * quantity;
		var ii = Math.round(r * 100);
		var total_price = ii / 100;
		$("#price_" + item_number).html( currency_format(total_price, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>") );
		gross_total = gross_total + parseFloat(total_price);
		
	});
	$("#gross_total").html( currency_format(gross_total, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>"));
	gross_discount = payment_method_discount + calculate_volume_discount(gross_total);
	$("#gross_discount").html( "-" + currency_format(gross_total * gross_discount, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", <?php echo DECIMALS_IN_ALL_FLOAT; ?>) );
	$("#total_to_pay").html( currency_format(gross_total * (1 - gross_discount), "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", <?php echo DECIMALS_IN_ALL_FLOAT; ?>) );
}

function purchase_pressed()
{
	$(".item_quantity").each(function() {
		change_quantity($( this ).attr("id"), $( this ).val());
	});
	
	var payment_method = "";
	$(".payment_method_radio").each(function( index ) {
		if ($(this).is(":checked"))
			payment_method = $(this).val();
	});
	if (payment_method == "balance") {
		show_select_from_balance_pay_currency();
		return false;
	}
	return true;
}

function on_from_balance_pay_currency_selected()
{
	document.user_frm.submit();
	return true;
}

window.setInterval("calculateTotal();", 1000);
</script>
<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>
