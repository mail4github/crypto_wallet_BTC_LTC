<?php

if ($_POST['show_all_tickets_changed'] == '1') {
	//echo 'all_tickets: "'.$_POST['all_tickets'].'"'; exit;
	setcookie('all_tickets', $_POST['all_tickets'], time() + 60 * 60 * 24 * 10, '/'); // 10 days
	$_COOKIE['all_tickets'] = $_POST['all_tickets'];
}
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
include_once(DIR_WS_CLASSES.'ticket.inc.php');

$display_in_short = !empty($_GET['userid']);

$page_header = '';
$page_title = $page_header;
$page_desc = 'Track and answer questions come from your customers.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

echo '
<div class="row">
	<div class="col-md-6">
		<h1>'.make_str_translateable('Feedbacks from users').'</h1>
	</div>
	<div class="col-md-6" style="padding-top:16px;">
		<form method="post" name="show_all_tickets_frm">
			<input type="hidden" name="show_all_tickets_changed" value="1">
			<input type="checkbox" name="all_tickets" id="all_tickets" value="'.($_COOKIE['all_tickets'] == 'all'?'not_all':'all').'" '.($_COOKIE['all_tickets'] == 'all'?'checked':'').' onclick="$(\'#all_tickets\').attr(\'checked\',\'checked\'); document.show_all_tickets_frm.submit();">&nbsp;'.make_str_translateable('All Feedbacks').'
		</form>
	</div>
</div>
';
if ( $_POST['page'] == 'delete_post' ) {
	$ticket = new ticket();
	$ticket->get_info($_POST['ticketid']);
	$ticket->delete();
}

if ( $_POST['page'] == 'close_post' ) {
	$ticket = new ticket();
	$ticket->get_info($_POST['ticketid']);
	$ticket->close();
}
if ( !$display_in_short )
	$rows_per_page = 5;
else
	$rows_per_page = 3;

$current_page_number = 1;
$total_rows = 0;
	
$header = array(
	'<strong>'.make_str_translateable('Condition').'</strong>#sort_label#',
	'<strong>'.make_str_translateable('Name').'</strong>#sort_label#',
	'<strong>'.make_str_translateable('Date').'</strong>#sort_label#',
	'',
	''
);
$whole_row_html = '
	<tr valign="top">
		<td style="border:none;">
			<img src="/images/icon-#c_status#-middle.png" border="0" alt="#c_status_desc#" style="position:relative; top:5px; left:5px;">
		</td>
		<td style="border:none;" valign="baseline" >from: <strong class="notranslate">'.($user_account->is_manager()?'<a href="/acc_viewuser.php?userid=#c_fromuserid#" target="_blank">#c_name#</a>':'#c_name#').'</strong><span style="font-size:60%; margin-left:0.5em;">(purchases: #c_stat_purchases#)</span></td>
		<td style="border:none;" valign="baseline" ><span class=local_pline_date_and_hour unix_time=#c_unix_created#>#c_created#</span></td>
		<form action="/acc_ticket_view.php" method="get" '.($display_in_short?'target="_blank"':'').'>
			<input type="hidden" name="ticketid" value="#c_ticketid#">
			<td style="border:none;" valign="middle"><button class="btn btn-primary btn-xs notranslate" name="btn1">#c_first_btn_capt#</button></td>
		</form>
		<td style="border:none;" valign="middle">
			<form action="" method="post">
				<input type="hidden" name="page" value="delete_post">
				<input type="hidden" name="ticketid" value="#c_ticketid#">
				<button class="btn btn-danger btn-xs" style="#c_display_del#" name="delete_post" onClick="return ( confirm(\'Do you really want to delete this ticket?\') );">'.make_str_translateable('Delete').'</button>
			</form>
			<form action="" method="post">
				<input type="hidden" name="page" value="close_post">
				<input type="hidden" name="ticketid" value="#c_ticketid#">
				<button class="btn btn-success btn-xs" style="#c_display_close#" name="close_post" onClick="return ( confirm(\'Do you really want to close this ticket?\') );">'.make_str_translateable('Close').'</button>
			</form>
		</td>
	</tr>
	<tr>
		<td style="border:none;"></td>
		<td style="border:none;" colspan="4">'.make_str_translateable('Subject').': <strong class="notranslate">#c_subject#</strong></td>
	</tr>
	<tr>
		<td style="border:none;"></td>
		<td style="border:none;" colspan="4"><p class="account_small_desc notranslate">#c_message#</p>
			<b>'.make_str_translateable('Answer').':</b><br><span class="description notranslate">#c_answer#</span>
		</td>
	</tr>
	<tr><td colspan="5" style="padding:0px;"></td></tr>
';
$row_eval = '
$row["c_stat_purchases"] = currency_format($row["c_stat_purchases"]);
';
$row_html = array();
$table_tag = '<table class="table box_type1">';
$output_str = '';
if ( print_sorted_table(
		'admin_tickets', 
		$header, 
		array('display_in_short' => $display_in_short, 'from_this_user' => $_GET['from_this_user'], 'by_userid' => $_GET['userid'], 'show_all_tickets' => $_COOKIE['all_tickets'] == 'all'), 
		$row_html, 
		$rows_per_page, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', // $row_html_odd
		$table_tag, 
		'class=sorted_table_cell', // $td_html
		'', // $td_html_odd
		0, // $default_sort_column
		'DESC', // $default_sort_order
		$whole_row_html, 
		//$count_query,
		'', // $whole_header_html
		
		'', // $sorted_column_label_mask
		'', // $not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval
		)
	)
{
	echo $output_str;
	echo '<p>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	echo '</p>';
}
else {
	echo '<p class=account_paragraph><strong>'.make_str_translateable('No trouble tickets').'.</strong></p>';
}
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
