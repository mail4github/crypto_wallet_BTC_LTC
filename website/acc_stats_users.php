<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$display_in_short = !empty($_GET['parentid']);

$page_header = 'User Statatistics';
$page_title = $page_header;
$page_desc = 'See stats about users.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if ( !empty($_POST['data_type']) ) {
	$data_type = $_POST['data_type'];
	$data_period = $_POST['data_period'];
	$group_by = $_POST['group_by'];
	$day_date = $_POST['day_date'];
	$custom_term_bigin = $_POST['custom_term_bigin'];
	$custom_term_end = $_POST['custom_term_end'];
}
else {
	$data_type = 'admin_users';
	$data_period = '90days';
	$group_by = 'day';
	$day_date = '';
	$custom_term_bigin = '';
	$custom_term_end = '';
}
if ( empty($group_by) )
	$group_by = 'day';

$show_daydate = 'display:none;';

$past_years = array();
$this_year = (int)date("Y");
for ($i = $this_year - 2; $i > $this_year - 6; $i--)
	$past_years[] = $i;

$toolbar = array(
	array('name' => 'Users:', 'selected' => $data_type, 'style' => 'text-align:left;',
		'buttons' => array(
			array('name' => 'New Users', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_users\'); document.graph_frm.submit();', 'value' => 'admin_users'),
			array('name' => 'Users by Countries', 'onclick'=> '$(\'input[name=data_type]\').val(\'users_by_countries\'); document.graph_frm.submit();', 'value' => 'users_by_countries'),
			array('name' => 'Users by Domains', 'onclick'=> '$(\'input[name=data_type]\').val(\'users_by_domains\'); document.graph_frm.submit();', 'value' => 'users_by_domains'),
			array('name' => 'Users by Recruiters', 'onclick'=> '$(\'input[name=data_type]\').val(\'users_by_recruiter\'); document.graph_frm.submit();', 'value' => 'users_by_recruiter'),
			array('name' => 'Buyers by Recruiters', 'onclick'=> '$(\'input[name=data_type]\').val(\'buyers_by_recruiter\'); document.graph_frm.submit();', 'value' => 'buyers_by_recruiter'),
			array('name' => 'Purchases by Recruiters', 'onclick'=> '$(\'input[name=data_type]\').val(\'purchases_by_recruiter\'); document.graph_frm.submit();', 'value' => 'purchases_by_recruiter'),
		)
	),
	
	array('name' => 'Group by:', 'selected' => $group_by, 'style' => 'text-align:left;',
		'buttons' => array(
			array('name' => 'Group by Day', 'onclick'=> '$(\'input[name=group_by]\').val(\'day\'); document.graph_frm.submit();', 'value' => 'day'),
			array('name' => 'Group by Week', 'onclick'=> '$(\'input[name=group_by]\').val(\'week\'); document.graph_frm.submit();', 'value' => 'week'),
			array('name' => 'Group by Month', 'onclick'=> '$(\'input[name=group_by]\').val(\'month\'); document.graph_frm.submit();', 'value' => 'month'),
		)
	),
	array('name' => 'Period:', 'selected' => $data_period, 'style' => 'text-align:left;',
		'buttons' => array(
			array('name' => 'Period: today', 'onclick'=> '$(\'input[name=data_period]\').val(\'today\'); document.graph_frm.submit();', 'value' => 'today'),
			array('name' => 'Period: last 7 days', 'onclick'=> '$(\'input[name=data_period]\').val(\'7days\'); document.graph_frm.submit();', 'value' => '7days'),
			array('name' => 'Period: last 30 days', 'onclick'=> '$(\'input[name=data_period]\').val(\'30days\'); document.graph_frm.submit();', 'value' => '30days'),
			array('name' => 'Period: last 90 days', 'onclick'=> '$(\'input[name=data_period]\').val(\'90days\'); document.graph_frm.submit();', 'value' => '90days'),

			array('name' => 'Period: last 6 months', 'onclick'=> '$(\'input[name=data_period]\').val(\'6month\'); document.graph_frm.submit();', 'value' => '6month'),
			array('name' => 'Period: last 12 months', 'onclick'=> '$(\'input[name=data_period]\').val(\'12month\'); document.graph_frm.submit();', 'value' => '12month'),

			array('value' => 'this_month', 'onclick'=> '$(\'input[name=data_period]\').val(\'this_month\'); document.graph_frm.submit();', 'name' => 'This Month'),
			array('value' => 'last_month', 'onclick'=> '$(\'input[name=data_period]\').val(\'last_month\'); document.graph_frm.submit();', 'name' => 'Last Month'),
			array('value' => 'this_year', 'onclick'=> '$(\'input[name=data_period]\').val(\'this_year\'); document.graph_frm.submit();', 'name' => 'This Year'),
			array('value' => 'last_year', 'onclick'=> '$(\'input[name=data_period]\').val(\'last_year\'); document.graph_frm.submit();', 'name' => 'Last Year'),
			array('value' => 'custom_term', 'onclick'=> '$(\'#data_period_toolbar_cat_2\').html(\'Custom Period\'); $(\'#custom_period\').show();', 'name' => 'Custom Period'),
		)
	),
);

foreach ($past_years as $year) 
	$toolbar[2]['buttons'][] = array('value' => 'year_'.$year, 'onclick'=> '$(\'input[name=data_period]\').val(\'year_'.$year.'\'); document.graph_frm.submit();', 'name' => 'Year '.$year);

switch ( $data_type ) {
	case 'admin_users':
		$caption = 'New Users';
	break;
}

global $mobile_device;

echo '
<div class="box_type1" style="padding:20px;">
	<form method="post" name="graph_frm" style="">
	<input type="hidden" name="data_type" value="'.$data_type.'">
	<input type="hidden" name="group_by" value="'.$group_by.'">
	<input type="hidden" name="data_period" value="'.$data_period.'">
	'.show_data_period_toolbar($toolbar).'
	<div style="position:relative; width:100%; height:0;">
		<div class="popover fade bottom in" id="custom_period" style="display:'.($data_period == 'custom_term' && !$mobile_device?'block':'none').'; position:absolute; right:0; margin:0 0 0 auto;">
			<div class="arrow" style="left: 50%;"></div>
			<h3 class="popover-title" style="display: none;">Custom Date:</h3>
			<div class="popover-content">
				<div class="row">
					<div class="col-md-2">From:</div><div class="col-md-10"><input type="date" name="custom_term_bigin" class="form-control input-sm" value="'.$_POST['custom_term_bigin'].'" style=""></div>
					<div class="col-md-2">To:</div><div class="col-md-10"><input type="date" name="custom_term_end" class="form-control input-sm" value="'.$_POST['custom_term_end'].'" style=""></div>
				</div>
				<button class="btn btn-info btn-xs" style="margin:10px auto; display:block;" onclick="custom_date_selected(); return false;">Show...</button>
			</div>
		</div>
	</div>
	</form>	
	<div class="row" id="graph_container" style="height:350px;"></div>
</div>'.

draw_graph($caption, $data_type, $data_period, $group_by, $date, '', $q_pie, 'graph_container', 'transparent',
	'', false, false, false, array('parentid' => $_GET['parentid'], 'display_in_short' => $display_in_short), false, $custom_term_bigin, $custom_term_end);

if ( !$display_in_short && $data_type == 'admin_users' && $data_period != 'today' && $data_period != 'custom_term' ) {
	include_once(DIR_COMMON_PHP.'print_sorted_table.php');
	$rows_per_page = 8;
	$current_page_number = 1;
	$total_rows = 0;

	$whole_header_html = '
		<tr>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-6"><a class="description" href="javascript: RedrawWithSort(1);"><strong>Date</strong></a>#sort_label1#</div>
					<div class="col-md-3" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(2);"><strong>New</strong></a>#sort_label2#</div>
					<div class="col-md-3" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(3);"><strong>Active</strong></a>#sort_label3#</div>
				</div>
			</td>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(4);"><strong>Western</strong></a>#sort_label4#</div>
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(5);"><strong>Logged</strong></a>#sort_label5#</div>
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(6);"><strong>Referred</strong></a>#sort_label6#</div>
				</div>
			</td>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(8);"><strong>Recruiters</strong></a>#sort_label8#</div>
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(9);"><strong>Reproductivity</strong></a>#sort_label9#</div>
					<div class="col-md-4" style="text-align:right;"><a class="description" style="padding:0px;" href="javascript: RedrawWithSort(10);"><strong>TTL</strong></a>#sort_label10#</div>
				</div>
			</td>
		</tr>
	';

	$whole_row_html = '
		<tr>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-6"><span class="description">#c_date#</span></div>
					<div class="col-md-3" style="text-align:right;">#c_users#</div>
					<div class="col-md-3" style="text-align:right;">#c_active_users#</div>
				</div>
			</td>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-4" style="text-align:right;">#c_west_users#</div>
					<div class="col-md-4" style="text-align:right;">#c_loggedin_users#%</div>
					<div class="col-md-4" style="text-align:right;">#c_referred#</div>
				</div>
			</td>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-4" style="text-align:right;">#c_hirers#</div>
					<div class="col-md-4" style="text-align:right;">#c_reproductivity#</div>
					<div class="col-md-4" style="text-align:right;">#c_days_online#</div>
				</div>
			</td>
		</tr>
	';
	$table_tag = '<table class="table table-striped">';
	$output_str = '';
	if ( print_sorted_table(
			'admin_stats_users', 
			'', 
			array('group_by' => $group_by, 'data_period' => $data_period), 
			'', 
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
			'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">' // $sorted_column_label_mask
			)
		)
	{
		echo $output_str;
		paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	}
	else {
		echo '<p class=account_paragraph><strong></strong></p>';
	}
}	
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
<script type="text/javascript">
function custom_date_selected()
{
	$("input[name=data_period]").val("custom_term");
	document.graph_frm.submit();
	return false;
}
</script>
</body>
</html>