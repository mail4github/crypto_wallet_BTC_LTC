<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

require_once(DIR_WS_INCLUDES.'box_protected_page.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$purchase_type = tep_sanitize_string($_GET['purchase_type']);
$display_in_short = !empty($_GET['userid']);

$page_header = 'Purchases';
$page_title = $page_header;
$page_desc = 'List of purchases.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if ( !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}

include_once(DIR_COMMON_PHP.'print_sorted_table.php');
if ( !$display_in_short )
	$rows_per_page = 17;
else
	$rows_per_page = 6;
$current_page_number = 1;
$total_rows = 0;
$whole_header_html = '
	<tr>
		<td style="width:30%;">
			<div class="row">
				<div class="col-md-6"><a href="javascript: RedrawWithSort(0);"><b>Date</b></a>#sort_label0#</div>
				<div class="col-md-3"><a href="javascript: RedrawWithSort(1);" style="text-align:right;"><b>Amount</b></a>#sort_label1#</div>
				<div class="col-md-3"><a href="javascript: RedrawWithSort(2);"><b>Status</b></a>#sort_label2#</div>
			</div>
		</td>
		<td style="width:70%;">
			<div class="row">
				<div class="col-md-5"><a href="javascript: RedrawWithSort(3);"><b>Invoice</b></a>#sort_label3#</div>
				<div class="col-md-5"><a href="javascript: RedrawWithSort(4);"><b>Sent to Email</b></a>#sort_label4#</div>
				<div class="col-md-2"><a href="javascript: RedrawWithSort(5);"><b></b></a>#sort_label5#</div>
			</div>
		</td>
	</tr>
';
$whole_row_html = '
	<tr>
		<td>
			<div class="row">
				<div class="col-md-6"><a href="/acc_purchase.php?purchaseid=#c_purchaseid#" target="_blank"><span class="local_pline_date_and_hour description" unix_time="#c_unix_time#">#c_created#</span></a></div>
				<div class="col-md-3" style="text-align:right; padding:0;">#c_amount#</div>
				<div class="col-md-3">#c_status#</div>
			</div>
		</td>
		<td>
			<div class="row">
				<div class="col-md-5 description">#c_invoice#</div>
				<div class="col-md-5 description" id="email_div_#c_sent_to_email#">#c_sent_to_email#</div>
				<div class="col-md-2 description">'.(!$display_in_short?'#c_photo#<img src="/images/flags/#c_country#.jpeg" title="from #c_country#" style="border:none; width:16px; height:10px; position:relative; top:-2px; left:-4px;"><a href="/acc_viewuser.php?userid=#c_userid#" target="_blank">#c_firstname#</a>':'').'</div>
			</div>
		</td>
	</tr>
';

$header = '';
$row_html = '';
$row_eval = '
	$row["c_photo"] = (file_exists(DIR_WS_WEBSITE_PHOTOS.$row["c_userid"].".jpg")?"<image src=\'/".DIR_WS_WEBSITE_PHOTOS_DIR.$row["c_userid"].".jpg\' class=\'first_page_image\' style=\'width:26px; height:26px; display:inline; margin:0 10px 0 0;\'>":"");
	$row["c_firstname"] = ucfirst(strtolower($row["c_firstname"]));
	$crypto = Pay_processor::get_crypto_currency_by_name($row["c_currency"]);
	if (!$crypto) {
		$crypto = new stdClass;
		$crypto->symbol = DOLLAR_SIGN;
		$crypto->digits = DOLLAR_DECIMALS;
		$crypto->crypto_symbol = DOLLAR_NAME;
	}
	$row["c_amount"] = currency_format($row["c_amount"], "color:#008800;", "color:#ff0000;", "", false, false, $crypto->symbol, $crypto->digits);
	if ( is_integer(strpos($row["c_sent_to_email"], "@")) ) 
		$row["c_sent_to_email"] = "<a href=\'/acc_viewuser.php?paypalemail=".$row["c_sent_to_email"]."\' target=\'_blank\'>".$row["c_sent_to_email"]."</a>";
	else
	if ( strtolower($row["c_sent_to_email"][0]) != "u" ) {
		$currency = pay_processor::get_crypto_currency_by_address($row["c_sent_to_email"][0]);
		if ( $currency == "bitcoin") 
			$row["c_sent_to_email"] = "<a href=\'".replaceCustomConstantInText("address", $row["c_sent_to_email"], BITCOIN_BLOCKS_EXPLORER)."\' target=\'_blank\'>".$row["c_sent_to_email"]."</a>";
		else
		if ( $currency == "litecoin") 
			$row["c_sent_to_email"] = "<a href=\'".replaceCustomConstantInText("address", $row["c_sent_to_email"], LITECOIN_BLOCKS_EXPLORER)."\' target=\'_blank\'>".$row["c_sent_to_email"]."</a>";
	}
	
';
$output_str = '';
$table_tag = '<table class="table table-striped table-hover">';
if ( print_sorted_table(
		'admin_purchases', 
		$header, 
		array('by_userid' => $_GET['userid'], 'display_in_short' => $display_in_short, 'purchase_type' => $_GET['purchase_type']), 
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
		'',
		true,
		$row_eval
		)
	)
{
	echo '<div id=table_div>'.$output_str.'</div>';
	echo '<p>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	echo '</p>';
}
else {
	echo '<p class=account_paragraph><b>No purchases</b></p>';
}
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</script>
</body>
</html>
