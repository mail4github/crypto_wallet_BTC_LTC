<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$box_message = '';
$page_header = 'Referrals';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = $page_header;
global $ranks;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');
if ( $user_account->disabled ) {
}
else {
	echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'referrals_32x32.png', 'Your referrals is your extra income. For all investors that join the site through your referral URL you will receive <b>'.number_format(AFFILIATE_COMMISSION * 100, 0).'%</b>  based on their purchases.<br><a href="/acc_banners.php">Invite referrals</a> and get revenue for the rest of your life.<br>Your URL to invite referrals:<br><strong style="cursor:pointer; border-bottom: 1px dashed;" id="ref_link" onclick="select_text_by_click(\'ref_link\');">'.$user_account->get_general_aff_link().'</strong>', 'alert-info');
	
	$rows_per_page = 8;
	$current_page_number = 1;
	$total_rows = 0;
	
	$row_eval = '
		if ( $row["c_purchases"] > 0 )
			$row["c_purchases"] = currency_format($row["c_purchases"]);
		else
			$row["c_purchases"] = "";
	';

	$whole_header_html = '
	<thead>
		<tr>
			<td style="width:50%;">
				<div class="row">
					<div class="col-md-3"><a class="description" href="javascript: RedrawWithSort(0);"><b>Member since</b></a>#sort_label0#</div>
					<div class="col-md-3"><a class="description" href="javascript: RedrawWithSort(1);"><b>Name</b></a>#sort_label1#</div>
					<div class="col-md-3"><a class="description" href="javascript: RedrawWithSort(2);"><b>Country</b></a>#sort_label2#</div>
					<div class="col-md-3"><a class="description" href="javascript: RedrawWithSort(3);"><b>Source</b></a>#sort_label3#</div>
				</div>
			</td>
			<td style="width:50%;">
				<div class="row">
					<div class="col-md-4"><a class="description" href="javascript: RedrawWithSort(4);"><b>Last Login</b></a>#sort_label4#</div>
					<div class="col-md-4"><a class="description" href="javascript: RedrawWithSort(5);"><b>Purchases</b></a>#sort_label5#</div>
					<div class="col-md-4"><a class="description" href="javascript: RedrawWithSort(6);"><b>Referrals</b></a>#sort_label6#</div>
				</div>
			</td>
		</tr>
	</thead>
	<tbody>
	';
	$whole_row_html = '
		<tr>
			<td>
				<div class="row">
					<div class="col-md-3">#c_created#</div>
					<div class="col-md-3">#c_first_name#<br></div>
					<div class="col-md-3"><img src="/images/flags_big/#c_country#.gif" data-toggle="tooltip" data-placement="bottom" title="From #c_country_name#" style="width:35px; height20px; border:1px solid #f2f2f2;"></div>
					<div class="col-md-3"><a href="http://#c_born_domain#" target="_blank" data-toggle="tooltip" data-placement="bottom" title="#c_born_domain#">#c_born_domain_text#</a></div>
				</div>
			</td>
			<td>
				<div class="row">
					<div class="col-md-4">#c_last_login#</div>
					<div class="col-md-4">#c_purchases#</div>
					<div class="col-md-4">#c_referrals#</div>
				</div>
			</td>
		</tr>
	';
	$row_html = array();

	$table_tag = '<table class="table">';
	$output_str = '';
	if ( print_sorted_table(
			'referrals', 
			$header, 
			NULL, 
			$row_html, 
			$rows_per_page, 
			$current_page_number, 
			$row_number, 
			$total_rows, 
			$output_str,
			'', 
			$table_tag, 
			'class=sorted_table_cell',
			'', 
			0, 
			'DESC', 
			$whole_row_html, 
			$whole_header_html,
			'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
			'', //$not_sorted_column_label_mask
			true, // $read_page_nmb_from_cookie
			$row_eval
			)
		)
	{
		echo '<div class="box_type1">'.$output_str.'</div>';
		paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	}
	else {
		echo '<h3>You have no referrals.</h3><br><br>';
	}
}
include(DIR_COMMON_PHP.'box_wait.php');
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<script language="JavaScript">show_message_box_box("Error", "'.$box_message.'", 2);</script>'."\r\n";
	else
		echo '<script language="JavaScript">show_message_box_box("Success", "'.$box_message.'", 1);</script>'."\r\n";
}
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>