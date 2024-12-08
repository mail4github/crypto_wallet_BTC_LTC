<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$display_in_short = !empty($_GET['userid']);

$page_header = 'Messages';
$page_title = $page_header;
$page_desc = 'Last messages sent to you';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

//if (!$display_in_short)
	//echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'emails.png', 'This is the list of last email messages which have been sent to your mailbox. Please click on subject to see the email body.', 'alert-info');

if ( !$display_in_short )
	$rows_per_page = 7;
else
	$rows_per_page = 4;

$current_page_number = 1;
$total_rows = 0;

$whole_header_html = '
	<thead>
		<tr>
			<th><a href="javascript: RedrawWithSort(2);"><b>Date</b></a>#sort_label2#</th>
			<th><a href="javascript: RedrawWithSort(3);"><b>Subject</b></a>#sort_label3#</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
';

$whole_row_html = '
	<tr valign="top" style="cursor:pointer;" onclick="location.assign(\'/message?mailid=#c_mailid#\');">
		<td>
			<span class="local_time_date_next_hour" unix_time="#c_created_time#">#c_date#<br><span style="color:#'.COLOR1BASE.';">#c_time#</span></span><br>
		</td>
		<td>
			#c_subject#
		</td>
		<td>
			<img src="'.DIR_WS_WEBSITE_IMAGES_DIR.'email_read.png" class="" style="display:#c_email_read#; width:30px; height:30px; filter:grayscale(100%); margin:0; border:none; padding:0; -webkit-box-shadow:none; box-shadow:none; -moz-border-radius:0; border-radius:0;" title="This message has been read">
			<img src="'.DIR_WS_WEBSITE_IMAGES_DIR.'email_new.png" class="" style="display:#c_email_new#; width:30px; height:30px; filter:grayscale(100%); margin:0; border:none; padding:0; -webkit-box-shadow:none; box-shadow:none; -moz-border-radius:0; border-radius:0;" title="This is new message">
		</td>
	</tr>
';

$row_html = array();
$table_tag = '<table class="table table-striped table-hover" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( !$user_account->disabled && print_sorted_table(
		'user_emails', 
		$header, 
		array('for_userid' => !empty($_GET['userid'])?$_GET['userid']:$user_account->userid), // $additional_params
		$row_html, 
		$rows_per_page, 
		//$section_from, 
		//$section_where, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', 
		//$section_group, 
		$table_tag, 
		'class=sorted_table_cell',
		'', 
		2, 
		'DESC', 
		$whole_row_html, 
		//$count_query,
		$whole_header_html,
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">' // $sorted_column_label_mask
		)
	)
{
	echo '<div class="box_type1">'.$output_str.'</div>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
}
else
	echo '<p><strong>No Messages.</strong></p>';

if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>
