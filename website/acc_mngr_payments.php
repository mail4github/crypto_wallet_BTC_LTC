<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'payout.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$SCRIPT_STARTED_SEC = time();
//echo "start: ".(time() - $SCRIPT_STARTED_SEC)."<br>";
$display_in_short = !empty($_GET['userid']);

$_SESSION['payment_made'] = '';

if ( !empty($_GET['delete_payout']) ) {
	$payout = new Payout();
	if ( $payout->read_data($_GET['delete_payout']) )
		$payout->decline();
}

if ( !empty($_GET['complete_payout']) ) {
	$payout = new Payout();
	if ( $payout->read_data($_GET['complete_payout']) )
		$payout->finish_payment();
}

$page_header = 'Withdrawals that are waiting for processing';
$page_title = $page_header;
$page_desc = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
echo '
<style type="text/css">
.qr_code_div{text-align:center; padding:6px;}
</style>
';
//echo "after header : ".(time() - $SCRIPT_STARTED_SEC)."<br>";
$show_form = true;
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

include_once(DIR_COMMON_PHP.'bitcoin.pay_processor.class.php');
$bitcoin = new Bitcoin();
$bitcoin_rate = $bitcoin->get_exchange_rate();
//echo "after bitcoin: ".(time() - $SCRIPT_STARTED_SEC)."<br>";
if ( $display_in_short )
	$rows_per_page = 10;
else
	$rows_per_page = 1000;
$current_page_number = 1;
$total_rows = 0;
//echo "before table: ".(time() - $SCRIPT_STARTED_SEC)."<br>";
$whole_header_html = '
	<tr>
		<td style="width:60%;">
			<div class="row">
				<div class="col-md-1"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong></strong>Method</a>#sort_label0#</div>
				<div class="col-md-11"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Name</strong></a>#sort_label1#</div>
			</div>
		</td>
		<td style="width:40%;">
			<div class="row">
				<div class="col-md-3" style="text-align:right; padding-right:10px;"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>Total Paid</strong></a>#sort_label2#</div>
				<div class="col-md-3" style="text-align:right; padding-right:10px;"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>'.($display_in_short?'Paid':'To Pay').'</strong></a>#sort_label3#</div>
				<div class="col-md-4"><a class=sorted_table_link href="javascript: RedrawWithSort(4);"><strong>Initiated</strong></a>#sort_label4#</div>
				<div class="col-md-2"></div>
			</div>
		</td>
	</tr>
';

$whole_row_html = '
	<tr>
		<td>
			<div class="row">
				<div class="col-md-1 description" style="text-align:center;">#c_comment#</div>
				<div class="col-md-11">
					<a target=_blank href="/acc_viewuser.php?userid=#c_userid#">#c_fullname#</a> #c_payout_more_purchase#<br>
					<b class="description">#c_paypalemail#</b><br>
					<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'rank#c_rank#.png" border="0" width="20" height="10">
				</div>
			</div>
		</td>
		<td>
			<div class="row">
				<div class="col-md-3 description" style="text-align:right; padding-right:10px;">#c_total_paid#</div>
				<div class="col-md-3 amount_to_pay" style="text-align:right; padding-right:10px;" _amount_to_pay="#c_payout#" >
					#c_payout_formatted#
				</div>
				<div class="col-md-4 description">#c_created#</div>
				<div class="col-md-2">'.($display_in_short?'':'
				<a style="padding-right:4px;" class="btn btn-danger btn-xs" href="?delete_payout=#c_payoutid#" onClick="return ( confirm(\'Do you really want to cancell this payout?\') );"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
				<a style="padding-right:4px;" class="btn btn-success btn-xs" href="?complete_payout=#c_payoutid#" onClick="return ( confirm(\'Do you really want to close this payout?\') );"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>
				').'</div>
			</div>
		</td>
	</tr>
';

$miners_fee = 0;
if ( defined('bitcoin_MINERS_FEE') )
	$miners_fee = bitcoin_MINERS_FEE;
$row_eval = '
	global $user_account;
	if ($row["c_total_paid"] > $row["c_stat_purchases"] )
		$row["c_payout_more_purchase"] = "<span class=\'glyphicon glyphicon-signal\' aria-hidden=true style=\'color:#ffc3c3;\'></span>";
	else
		$row["c_payout_more_purchase"] = "";
	if ( empty($row["c_total_paid"]) )
		$row["c_total_paid"] = "";
	else
		$row["c_total_paid"] = currency_format($row["c_total_paid"]);
	
	$crypto = Pay_processor::get_crypto_currency_by_name($row["c_currency"]);
	if ( !$crypto ) {
		$crypto = new stdClass;
		$crypto->symbol = DOLLAR_SIGN;
		$crypto->digits = DOLLAR_DECIMALS;
		$crypto->crypto_symbol = DOLLAR_NAME;
	}
	$row["c_payout_formatted"] = currency_format($row["c_payout"], "", "", "", false, false, $crypto->symbol, $crypto->digits);
	
	$s = $user_account->get_payout_option_value("logo", true, $row["c_comment"]);
	if ( !empty($s) )
		$row["c_comment"] = "<img src=$s style=\'width:20px;\'>";
	
';

$table_tag = '<table class="table box_type1">';

if ( print_sorted_table(
		'admin_waiting for processing_payments', 
		$header, 
		array('by_userid' => $_GET['userid'], 'display_in_short' => $display_in_short), 
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
		4, 
		'ASC', 
		$whole_row_html, 
		$whole_header_html,
		'<br><span class="description">sorted: #sort_order_text_name#</span>',
		'', // $not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval
		)
	)
{
	echo '<div id=table_div>'.$output_str.'</div>';
	echo '<p>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	echo '</p>';
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function caclculate_total()
	{
		var total = 0.0;
		
		$(".amount_to_pay").each(function( index ) {
			total = total + Number( $(this).attr("_amount_to_pay") );
		});
		$("#total_inner").html("$" + total.toFixed(2));
		return false;
	}
	
	</SCRIPT>
	<?php 
	if ( !$display_in_short ) {
		echo '<!--p><a class=commissions_table_text href="javascript:void(null);" onclick="checkboxes_select(1);">select all</a>&nbsp;&nbsp;&nbsp;<a class=commissions_table_text href="javascript:void(null);" onclick="checkboxes_select(0);">unselect all</a></p-->
		';
		echo '<p><!--Total Rows: <strong>'.$total_rows.'</strong>. Selected: <strong id=selected_rows>'.$total_rows.'</strong>. -->Total to pay: <strong id=total_inner>$0.00</strong></p>'; 
		echo '
		<SCRIPT LANGUAGE="JavaScript">
			caclculate_total();
		</SCRIPT>
		';
	}
}
else {
	echo '<p class=account_paragraph><strong>No payouts yet</strong></p>';
}
//echo "after table: ".(time() - $SCRIPT_STARTED_SEC)."<br>";
include(DIR_COMMON_PHP.'box_wait.php');
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<script language="JavaScript">show_message_box_box("Error", "'.$box_message.'", 2);</script>'."\r\n";
	else
		echo '<script language="JavaScript">show_message_box_box("Success", "'.$box_message.'", 1);</script>'."\r\n";
}
if ( !$display_in_short ) {
	require(DIR_WS_INCLUDES.'footer.php');
}
?>

<script src="/javascript/QRCode.js" type="text/javascript"></script>
<script type="text/javascript">

var need_to_continue_check = 20;
var miners_fee = <?php echo (defined("bitcoin_MINERS_FEE")?bitcoin_MINERS_FEE:'0'); ?>;
	
function checkuot_bitcoins()
{
	try{
		$(".qr_code_div").each(function( index ) {
			if ( $(this).css("display") == "none" )
				return true;
			var address = $(this).attr("id");
			var amount = $(this).attr("amount");
			var hours_from_created = Number($(this).attr("hours_to_search"));
			if (hours_from_created <= 0)
				return true;
			
			currentdate = new Date(); 
			
			write_console_log("check addr: " + address);

			$.ajax({
				method: "POST",
				url: "/api/get_crypto_addr_info/" + address + "/",
				data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", hours_to_search: hours_from_created, amount_to_search: amount }
			})
			.done(function( ajax__result ) {
				try
				{
					var arr_ajax__result = JSON.parse(ajax__result);
					if ( arr_ajax__result["success"] ) {
						if ( arr_ajax__result["values"][0].amount > 0 ) {
							$("#" + arr_ajax__result["values"][0].address).css("display", "none");
							write_console_log("hide: " + arr_ajax__result["values"][0].address + ", " + need_to_continue_check);
							need_to_continue_check--;
							
							var payoutid = $("#" + arr_ajax__result["values"][0].address).attr("payoutid");
							write_console_log("completed payout: " + payoutid);
							
							$.ajax({
								method: "POST",
								url: "/api/complete_payout_sign/" + payoutid,
								data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", pay_processor_email: arr_ajax__result["values"][0].hash }
							})
							.done(function( ajax__result ) {
								try
								{
									write_console_log(ajax__result);
								}
								catch(error){<?php echo (!empty($_COOKIE['debug'])?'write_console_log(error);':''); ?>}
							});
						}
						else {
							need_to_continue_check = 20;
							write_console_log("Not completed payout: " + $("#" + arr_ajax__result["values"][0].address).attr("payoutid"));
						}
					}
					else {
						
					}
				}
				catch(error){<?php echo (!empty($_COOKIE['debug'])?'write_console_log(error);':''); ?>}
			});	
		});
	}
	catch(error){<?php echo (!empty($_COOKIE['debug'])?'write_console_log(error);':''); ?>}
	
	if (need_to_continue_check > 0) {
		setTimeout(function(){ 
			checkuot_bitcoins();
		}, 5000);
	}
}

function amount_changed(payout_address, payoutid, currency)
{
	if (typeof currency == 'undefined' ) 
		currency = "usd";
	var bitcoin_rate = <?php echo $bitcoin_rate; ?>;
	if ( currency.toLowerCase() == "<?php echo strtolower(DOLLAR_NAME); ?>" )
		var btc_amount = $("#us_amount_" + payoutid).val() / bitcoin_rate;
	else 
	    var btc_amount = $("#us_amount_" + payoutid).val();
	btc_amount = Number(btc_amount).toFixed(5);
	$("#btc_amount_" + payout_address).html( btc_amount );
	$("#" + payout_address).attr("amount", $("#btc_amount_" + payout_address).html());
	refresh_qr_codes();
}

function refresh_qr_codes() 
{
	$(".qr_code_div").each(function( index ) {
		var address = $(this).attr("id");
		var amount = $(this).attr("amount");
		var keyValuePair = {};
		keyValuePair[address] = "bitcoin:" + address + "?amount=" + amount;
		ninja.qrCode.showQrCode(keyValuePair, 2);
	});
}

</script>
</body>
</html>