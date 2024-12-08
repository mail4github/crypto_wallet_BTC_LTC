<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$display_in_short = !empty($_GET['userid']);
$page_header = 'Clicks';
$page_title = $page_header;
$page_desc = 'See stats about visits.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

include_once(DIR_COMMON_PHP.'print_sorted_table.php');
	
$rows_per_page = 8;
$current_page_number = 1;
$total_rows = 0;
	
$header = array(
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;"><strong>Date</strong>#sort_label#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;"><strong>Domain</strong>#sort_label#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;"><strong>Unique</strong>#sort_label#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;"><strong>URL</strong>#sort_label#</p>',
);

$whole_row_html = '';

$row_html = array(
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; padding-top:10px; padding-bottom:10px;">#value#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;">#value#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;">#value#</p>',
	'<p class="account_smail_button" style="padding:0px; margin:0px; padding-left:2px; text-align:left;">#value#</p>',
);
$table_tag = '<table class="table box_type1">';
$output_str = '';
if ( print_sorted_table(
		'admin_stats_clicks', 
		$header, 
		array('by_userid' => $_GET['userid']), 
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
		'',
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">' // $sorted_column_label_mask
		)
	)
{
	echo $output_str;
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
}
else {
	echo '<p><strong>No clicks</strong></p>';
}
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>