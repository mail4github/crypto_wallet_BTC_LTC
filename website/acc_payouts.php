<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

if ( $user_account->permission_rank < PERMISSION_MANAGER_MIN_RANK ) {
	if ( !$user_account->has_permission(PERMISSION_MANAGER) ) {
		header('Location: /');
		exit;
	}
}

$display_in_short = !empty($_GET['userid']);

$page_header = 'Payouts';
$page_title = $page_header;
$page_desc = 'List of purchases.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !$display_in_short )
	$rows_per_page = 17;
else
	$rows_per_page = 6;
$current_page_number = 1;
$total_rows = 0;
$header = '';
$whole_header_html = '
	<tr>
		<td style="width:25%;">
			<a href="javascript: RedrawWithSort(0);"><b>Date</b></a>#sort_label0#
		</td>
		<td style="width:25%; text-align:right;">
			<a href="javascript: RedrawWithSort(1);" style="text-align:right;"><b>Amount</b></a>#sort_label1#
		</td>
		<td style="width:25%;"></td>
		<td style="width:25%;">
			<a href="javascript: RedrawWithSort(2);"><b>Status</b></a>#sort_label2#
		</td>
	</tr>
';
$row_html = '';
$whole_row_html = '
	<tr>
		<td>
			<a href="/acc_sale.php?payoutid=#c_payoutid#" target="_blank"><span class="local_pline_date_and_hour description" unix_time="#c_unix_time#">#c_created#</span></a>
		</td>
		<td style="text-align:right; padding-right:6px;">
			#c_amount#
		</td>
		<td></td>
		<td>
			#c_status#
		</td>
	</tr>
';

//$currency = new Currency();
//$list_of_currencyis = $currency->get_list();
$row_eval = '
	//global $currency;
	//global $list_of_currencyis;
	//$row["c_amount"] = currency_format($row["c_amount"], "color:#008800;", "color:#ff0000;", "", false, false, $currency->get_symbol($row["c_currency"], $list_of_currencyis));
	$row["c_amount"] = currency_format($row["c_amount"], "color:#008800;", "color:#ff0000;", "", false, false, DOLLAR_SIGN);
	switch ($row["c_status"]) {
		case "A": $row["c_status"] = "<font color=#008800>Ok</font>"; break; 
		case "P": $row["c_status"] = "pending"; break; 
		case "D": $row["c_status"] = "<font color=#FF0000>declined</font>"; break; 
		case "C": $row["c_status"] = "ok"; break; 
		case "W": $row["c_status"] = "waiting"; break; 
		default: $row["c_status"] = ""; break; 
	}
';
$table_tag = '<table class="table box_type1">';
$output_str = '';
if ( print_sorted_table(
		'admin_payouts', 
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
		'',
		'class=account_webpages_cell', 
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
	echo '<p class=account_paragraph><strong>No payouts</strong></p>';
}

if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');

?>

</body>
</html>
