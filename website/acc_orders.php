<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');

$display_in_short = !empty($_GET['userid']);

if ( !empty($_GET['userid']) )
	$userid = tep_sanitize_string($_GET['userid']);
else
	$userid = $user_account->userid;

$page_header = 'Orders';
$page_title = $page_header;
$page_desc = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if (!$display_in_short)
	echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'job_waiting100x100.png', 'Here you can see a list of your recent purchases which are not completed yet. The process of transferring the '.WORD_MEANING_SHARE.' ownership from old to the new owner takes time. Sometimes this process takes hours and even days. You can see the status of your orders in the list below.', 'alert-info');

include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !$display_in_short )
	$rows_per_page = 13;
else
	$rows_per_page = 6;
$current_page_number = 1;
$total_rows = 0;
$whole_header_html = '
	<tr>
		<td style="width:40%;">
			<div class="row">
				<div class="col-md-6" style="text-align:left; padding-left:8px; padding-right:4px;"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>Date</strong></a>#sort_label0#</div>
				<div class="col-md-6" style="text-align:right; padding-left:4px; padding-right:40px;"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>Amount</strong></a>#sort_label2#</div>
			</div>
		</td>
		<td style="width:60%;">
			<div class="row">
				<div class="col-md-9" style="text-align:left; padding-left:4px; padding-right:4px;"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>Transaction</strong></a>#sort_label3#</div>
				<div class="col-md-3" style="text-align:left; padding-left:4px; padding-right:4px;"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Status</strong></a>#sort_label1#</div>
			</div>
		</td>
	</tr>
';
$whole_row_html = '
	<tr>
		<td>
			<div class="row">
				<div class="col-md-6" style="text-align:left; padding-left:8px; padding-right:4px;">#c_created#</div>
				<div class="col-md-6" style="text-align:right; padding-left:4px; padding-right:40px;">#c_paid#</div>
			</div>
		</td>
		<td>
			<div class="row">
				<div class="col-md-9" style="text-align:left; padding-left:4px; padding-right:4px;">#c_description#<br>#c_soldout#</div>
				<div class="col-md-3" style="text-align:left; padding-left:4px; padding-right:4px;">#c_status#</div>
			</div>
		</td>
	</tr>
';

$row_eval = '
	$row["c_description"] = "Purchase";
';
$table_tag = '<table class="table table-striped" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( !$user_account->disabled && print_sorted_table(
		'orders', 
		$header, 
		array('by_userid' => $userid), 
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
}
else {
	echo '<h3>No orders yet.</h3><br><br>';
}

if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');

?>

</body>
</html>
