<?php
$cookie_active_deposits_only = $_COOKIE['active_deposits_only'];
if (!empty($_POST['active_deposits_only'])) {
	setcookie('active_deposits_only', $_POST['active_deposits_only'], time() + 60 * 60 * 24 * 365, '/'); // 365 days
	$cookie_active_deposits_only = $_POST['active_deposits_only'];
}

require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$bitcoin = new Bitcoin();
$litecoin = new Litecoin();

$display_in_short = !empty($_GET['userid']);

$page_header = '';
$page_title = $page_header;
$page_desc = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !$display_in_short )
	$rows_per_page = 14;
else
	$rows_per_page = 10;
$current_page_number = 1;
$total_rows = 0;
$row_eval = '';
$managers = array();

$currencies = [ 
	['id' => 'usd', 'name' => 'USD', 'logo' => '/images/invest_70x70_h.png', 'symbol' => '$', 'precision' => 2, 'currency_code' => 'USD'],
	['id' => $bitcoin->crypto_name, 'name' => 'Bitcoin', 'logo' => '/images/bitcoin.png', 'symbol' => $bitcoin->symbol, 'precision' => 5, 'currency_code' => $bitcoin->crypto_symbol],
	['id' => $litecoin->crypto_name, 'name' => 'Litecoin', 'logo' => '/images/litecoin_100x100.png', 'symbol' => $litecoin->symbol, 'precision' => 3, 'currency_code' => $litecoin->crypto_symbol]
];
?>

<div class="row">
	<div class="col-md-6">
		<h1 style="<?php echo ($display_in_short ? 'display:none;' : ''); ?>">Term Deposits</h1>
	</div>
	<div class="col-md-6" style="text-align:right;">
		<label class="checkbox-inline">
			<form method="post" name="active_deposits_only_frm">
			<input type="hidden" name="active_deposits_only" value="<?php echo ($cookie_active_deposits_only == 'YES' ? 'NO' : 'YES'); ?>">
			<input type="checkbox" name="" id="" <?php echo ($cookie_active_deposits_only == 'YES' ? 'checked' : ''); ?> onclick="active_on_off();">Active Only
			</form>
		</label>
	</div>
</div>


<?php
$whole_header_html = '
	<tr>
		<td style="width:50%;">
			<div class="row">
				<div class="col-md-3" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>Created</strong></a>#sort_label0#</div>
				<div class="col-md-2" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Status</strong></a>#sort_label1#</div>
				<div class="col-md-2" style="text-align:center;"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>Currency</strong></a>#sort_label2#</div>
				<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>Amount</strong></a>#sort_label3#</div>
				<div class="col-md-3" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(4);"><strong>Term</strong></a>#sort_label4#</div>
			</div>
		</td>
		<td style="width:50%;">
			<div class="row">
				<div class="col-md-3" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(5);"><strong>Rate</strong></a>#sort_label5#</div>
				<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(6);"><strong>Paid</strong></a>#sort_label6#</div>
				<div class="col-md-3" style="text-align:right;"></div>
				<div class="col-md-4" style="text-align:center;"><span style="'.($display_in_short ? 'display:none;' : '').'"><a class=sorted_table_link href="javascript: RedrawWithSort(7);"><strong>User</strong></a>#sort_label7#</span></div>
			</div>
		</td>
	</tr>
';
$header = '';

$whole_row_html = '
	<tr>
		<td style="">
			<div class="row">
				<div class="col-md-3 description" style="text-align:left;"><span class="local_time_date_next_hour" unix_time="#c_created_unix#">#c_created_date#<br> <span style="color:#'.COLOR1BASE.';">#c_created_time#</span></span></div>
				<div class="col-md-2" style="text-align:left;">#c_status#</div>
				<div class="col-md-2" style="text-align:center;"><img src="#c_currency_logo#" border="0" alt="#c_payment_method#" title="#c_payment_method#" style="width:20px; height:20px;"></div>
				<div class="col-md-2 description" style="text-align:right;">#c_amount_in_currency#</div>
				<div class="col-md-3 description" style="text-align:right;">#c_term#</div>
			</div>
		</td>
		<td style="">
			<div class="row">
			
				<div class="col-md-3" style="text-align:right;">#c_rate#</div>
				<div class="col-md-2 description" style="text-align:right;">#c_paid#</div>
				<div class="col-md-3" style="text-align:center;">
					<button class="btn btn-link btn-xs" onclick="info_btn_click(\'#c_created#\', #c_created_unix#, \'#c_ends#\', #c_ends_unix#, #c_done#, \'#c_servingid#\', #c_units_served#, #c_weeks#, \'#c_payment_method#\', #c_amount#, \'#c_currency_symbol#\', \'#c_currency_logo_hex#\', #c_precision#, #c_receive_weekly#, #c_interest_rate#, \'#c_wallet#\', \'#c_seed#\', \'#c_question#\', #c_total_receive#, \'#c_receiver#\', \'#c_pay_currency_symbol#\', \'#c_pay_precision#\', \'#c_temp_wallet_version#\', \'#c_insurance#\', \'#c_pay_attempts_total#\', \'#c_pay_attempts_currency#\'); return false;" style="border: solid 1px #00aa00;">Info...</button>
				</div>
				<div class="col-md-4" style="text-align:center;"><span style="'.($display_in_short ? 'display:none;' : '').'">#c_userid#</span></div>
			</div>
		</td>
	</tr>
';

$row_eval = '
	global $currencies;
	parse_str($row["c_note"], $term_deposit_data);
	//var_dump($term_deposit_data); echo "<br><br>";
	$row["c_receive_payment_method"] = $term_deposit_data["currency"];
	$tmp_currency = null;
	foreach ($currencies as $currency) {
		if ( strtolower($currency["name"]) == strtolower($row["c_payment_method"]) ) {
			$tmp_currency = $currency;
			break;
		}
	}
	if (!isset($tmp_currency)) {
		foreach ($currencies as $currency) {
			if ($currency["id"] == $row["c_receive_payment_method"] || strtolower($currency["currency_code"]) == strtolower($row["c_pay_attempts_currency"]) || strtolower($currency["currency_code"]) == strtolower($row["c_currency"]) ) {
				$tmp_currency = $currency;
				break;
			}
		}
	}
	if (isset($tmp_currency)) {
		$row["c_pay_currency_symbol"] = $tmp_currency["symbol"];
		$row["c_pay_currency_logo"] = $tmp_currency["logo"];
		$row["c_pay_currency_logo_hex"] = bin2hex($tmp_currency["logo"]);
		$row["c_pay_precision"] = $tmp_currency["precision"];
		$row["c_amount_in_currency"] = currency_format(doubleval($row["c_amount"]), "", "", "", false, false, $tmp_currency["symbol"], $tmp_currency["precision"]);
		$row["c_currency_logo"] = $tmp_currency["logo"];
		$row["c_currency_logo_hex"] = bin2hex($tmp_currency["logo"]);
	}
	$tmp_currency = null;
	foreach ($currencies as $currency) {
		if ( $currency["id"] == $row["c_currency"] ) {
			$tmp_currency = $currency;
			break;
		}
	}
	if (!isset($tmp_currency)) {
		foreach ($currencies as $currency) {
			if ( strtolower($currency["currency_code"]) == strtolower($row["c_currency"]) ) {
				$tmp_currency = $currency;
				break;
			}
		}
	}
	if (!isset($tmp_currency)) {
		foreach ($currencies as $currency) {
			if ($currency["id"] == $row["c_payment_method"] || $currency["id"] == $row["c_receive_payment_method"] || strtolower($currency["currency_code"]) == strtolower($row["c_pay_attempts_currency"]) || strtolower($currency["currency_code"]) == strtolower($row["c_currency"]) ) {
				$tmp_currency = $currency;
				break;
			}
		}
	}
	if (isset($tmp_currency)) {
		$row["c_currency_symbol"] = $tmp_currency["symbol"];
		$row["c_currency_symbol"] = $tmp_currency["symbol"];
		$row["c_precision"] = $tmp_currency["precision"];
		$row["c_paid"] = currency_format($row["c_units_served"] * $term_deposit_data["receive_weekly"], "", "", "", false, false, $tmp_currency["symbol"], $tmp_currency["precision"]);
	}
	
	$row["c_receive_weekly"] = $term_deposit_data["receive_weekly"]?$term_deposit_data["receive_weekly"]:0;
	$row["c_interest_rate"] = $term_deposit_data["interest_rate"]?$term_deposit_data["interest_rate"]:0;
	$row["c_wallet"] = $term_deposit_data["addr"];
	$row["c_seed"] = $term_deposit_data["seed"];
	$row["c_question"] = bin2hex($term_deposit_data["qstn"]);
	$row["c_total_receive"] = $term_deposit_data["total_receive"]?$term_deposit_data["total_receive"]:0;
	$row["c_temp_wallet_version"] = $term_deposit_data["vrs"]?$term_deposit_data["vrs"]:0;
	$row["c_receiver"] = $term_deposit_data["rcvr"];
	$row["c_close_show"] = $row["c_done"]?"display:none;":"";
	
	$row["c_description"] = currency_format($row["c_amount"], "", "", "", false, false, $row["c_pay_currency_symbol"], $row["c_pay_precision"])." for ".round($row["c_weeks"] / 4.34524)." ".show_plural(round($row["c_weeks"] / 4.34524), "month")." with ".round($term_deposit_data["interest_rate"] * 100 - 100)."% per annum, ".currency_format($row["c_units_served"] * $term_deposit_data["receive_weekly"], "", "", "", false, false, $row["c_currency_symbol"], $row["c_precision"]).($row["c_done"]?" total paid":" already paid");
	$deposit_completion = round($row["c_units_served"] / round($row["c_weeks"]) * 100);
	$row["c_status"] = $row["c_done"]?(round($row["c_units_served"]) >= round($row["c_weeks"])?"<span class=\'glyphicon glyphicon-ok\' aria-hidden=\'true\' style=\'color:#00aa00;\'></span>":"<span class=\'glyphicon glyphicon-ok\' aria-hidden=\'true\' style=\'color:#aa0000;\'></span>"):"
	<div style=\'width:100%; background-color:transparent; padding:4px 0 0 0;\'><div style=\'width:100%; background-color:#ffffff; padding:0px; border:1px solid #C0C0C0;\'><div style=\'width:".$deposit_completion."%; background-color:#00aa00; padding:0; border:none; height:2px;\'></div></div></div><p style=\'font-size:9px; margin:0; padding:0;\'>".$deposit_completion."%</p>";

	$row["c_term"] = round($row["c_weeks"])." ".show_plural(round($row["c_weeks"]), "week");// < 52 ? (round($row["c_weeks"] / 4.34524)." ".show_plural(round($row["c_weeks"] / 4.34524), "month")) : (round($row["c_weeks"] / 52.1429)." ".show_plural(round($row["c_weeks"] / 52.1429), "year"));
	$row["c_rate"] = round($term_deposit_data["interest_rate"] * 100 - 100)."%";
	
	
	$row["c_userid"] = "<a href=\'/acc_viewuser.php?userid=".$row["c_userid"]."\' target=\'_blank\' class=description style=\'margin:0;padding:0;\'><image src=/".(!empty($row["c_photo"])?DIR_WS_WEBSITE_PHOTOS_DIR.$row["c_photo"]:DIR_WS_IMAGES_DIR."no_photo_60x60".( $row["c_gender"] == "F"?"girl":"boy").".png")." class=\'first_page_image user_thumbnail_hidden\' style=\'width:26px; height:26px; margin: 0 auto 0 auto;\'>".$row["c_name"]."</a>";
	$row["c_insurance"] = round($term_deposit_data["insurance"] * 100)."%";
';


$row_html = '';

$table_tag = '<table class="table" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( print_sorted_table(
		'term_deposits_admin', 
		$header, 
		array('for_userid' => !empty($_GET['userid']) ? $_GET['userid'] : '', 'active_deposits_only' => $cookie_active_deposits_only), 
		$row_html,
		$rows_per_page, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', 
		$table_tag, 
		'',
		'', 
		0, 
		'DESC', 
		$whole_row_html, 
		$whole_header_html,
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
		'', // $not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval,
		NULL, // $data_array
		'', // $whole_footer_html
		false, // $always_use_default_sort
		0, // $number_of_grid_columns
		'<div class="row">', // $grid_row_prefix
		'</div>', // $grid_row_suffix
		$sort_column,
		$sort_order,
		null // $user
		)
	)
{
	echo '<div>'.$output_str.'</div>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
}
else {
	echo '<h3>No deposits yet.</h3><br><br>';
}

require_once(DIR_WS_INCLUDES.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require_once(DIR_WS_INCLUDES.'box_edit_item.php');
?>

<script type="text/javascript">
function info_btn_click(created, created_unix, ends, ends_unix, done, servingid, weeks_paid, duration_in_weeks, payment_method, amount_of_deposit, currency_symbol, currency_logo, currency_precision, receive_weekly, interest_rate, wallet, seed, question, total_receive, receiver, pay_currency_symbol, pay_precision, temp_wallet_version, insurance, total_in_usd, usd_currency)
{
	cur_temp_wallet_version = temp_wallet_version;
	cur_servingid = servingid;
	duration_in_weeks = Math.round(duration_in_weeks);
	if ( receiver == "balance" )
		var receive_method = "your account balance.";
	else
	if ( receiver == "paper_wallet" ) {
		var receive_method = "the temporary wallet:<p style='text-align:center;'><b class='visible_on_big_screen select_to_copy' id='private_seed' style='word-wrap:break-word;'>" + wallet + "</b><b class='invisible_on_big_screen' style='word-wrap:break-word;'>" + wallet + "</b><br><button class='btn btn-link btn-xs' style='border: solid 1px #337ab7; margin-left:0px;' id='wallet_qr_code_button' onclick='show_qr_code(" + '"' + payment_method + '"' + ", " + '"' + wallet + '"' + ", " + '"wallet_qr_code"' + ", " + '"wallet_qr_code_button"' + ");'>Show QR Code</button></p><p style='text-align:center;' id='wallet_qr_code' onclick='$(" + '"#wallet_qr_code"' + ").hide(); $(" + '"#wallet_qr_code_button"' + ").show();'></p>";
		if (done)
			receive_method = receive_method + "To withdraw funds use private key:<p style='text-align:center;'><button class='btn btn-link btn-xs' style='border: solid 1px #337ab7; margin-left:0px;' id='wallet_priv_key_button' onclick='show_private_key(" + '"' + payment_method + '"' + ", " + '"' + wallet + '"' + ", " + '"'+question+'"' + ", " + '"'+seed+'"' + ");'>Show private key</button></p><div id='wallet_priv_key_div' style='display:none;'><p style='text-align:center;'><b id='wallet_priv_key_val' style='word-wrap:break-word;'></b><br><button class='btn btn-link btn-xs' style='border: solid 1px #337ab7; margin-left:0px;' id='wallet_priv_key_qr_code_button' onclick='show_qr_code(" + '""' + ", private_key, " + '"wallet_priv_key_qr_code"' + ", " + '"wallet_priv_key_qr_code_button"' + ");'>Show QR Code</button></p><p style='text-align:center;' id='wallet_priv_key_qr_code' onclick='$(" + '"#wallet_priv_key_qr_code"' + ").hide(); $(" + '"#wallet_priv_key_qr_code_button"' + ").show();'></p></div><div class='alert alert-warning' style='margin-bottom:0px;'><p>Use this private key to transfer money to your wallet. Keep it in secure place, do not show it to anybody.</p><p>To transfer funds from this temporary wallet to a wallet on your gadget scan the QR code of private key with your gadget&#39;s camera.</p></div>";
	}
	else
	if ( receiver == "crypto_address" )
		var receive_method = "your wallet:<p style='margin-bottom:4px; text-align:center;'><b>" + wallet + "</b></p>";
	
	var exchange_rate = 1;
	if (typeof balance_data != "undefined" && balance_data != null )
		exchange_rate = Number(balance_data[usd_currency.toLowerCase()]["exchange_rate"]);
	var paid_in_usd = total_in_usd * exchange_rate;
	
	show_message_box_box(
		"<img src='"+hex_to_string(currency_logo)+"' border='0' style='width:20px; height:20px; margin-right:10px;'>Deposit", 
		"<div style='margin:0 0 0 10px;'><h1 style='margin:0 0 10px 0; padding:0;'>" + currency_format(amount_of_deposit, pay_currency_symbol, undefined, undefined, undefined, pay_precision) + " " + payment_method + " (" + currency_format(paid_in_usd) + ")" + "</h1><p>from: <b>" + created+"</b>&nbsp;&nbsp;&nbsp;&nbsp;till: <b>" + ends + "</b>"+(done?(Math.round(weeks_paid) >= Math.round(duration_in_weeks) ? "<span style='font-weight:bold; color:#00aa00; margin-left:20px;'>Paid in full</span>":"<span style='font-weight:bold; color:#aa0000; margin-left:20px;'>Cancelled</span>"):"")+"</p><p>rate: <b>" + Math.round(interest_rate * 100 - 100)+"%</b> per annum, <b>" + duration_in_weeks + "</b> weekly payments by <b>"+currency_format(receive_weekly, currency_symbol, undefined, undefined, undefined, currency_precision)+"</b>, total receive: <b>"+currency_format( (done?weeks_paid:duration_in_weeks) * receive_weekly, currency_symbol, undefined, undefined, undefined, currency_precision)+"</b>, insurance: <b>" + insurance + "</b></p><p>Payments are sending to " + receive_method + "</p>",
		0);
}

function active_on_off()
{
	document.active_deposits_only_frm.submit();
}

$( document ).ready(function() {
	
});

</script>
<?php
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
