<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$display_in_short = !empty($_GET['userid']);

$page_header = 'Visits';
$page_title = $page_header;
$page_desc = 'See stats about visits.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if ( !empty($_POST['data_type']) ) {
	$data_type = $_POST['data_type'];
	$data_period = $_POST['data_period'];
	$group_by = $_POST['group_by'];
	$day_date = $_POST['day_date'];
}
else {
	$data_type = 'admin_visits';
	$data_period = '90days';
	$group_by = 'day';
	$day_date = '';
}
if ( empty($group_by) )
	$group_by = 'day';

$show_daydate = 'display:none;';

$toolbar = array(
	array('name' => 'Users:', 'selected' => $data_type, 'style' => 'text-align:left;',
		'buttons' => array(
			array('name' => 'Visits', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_visits\'); document.graph_frm.submit();', 'value' => 'admin_visits'),
			array('name' => 'Visitors by Countries', 'onclick'=> '$(\'input[name=data_type]\').val(\'visitors_by_countries\'); document.graph_frm.submit();', 'value' => 'visitors_by_countries'),
			array('name' => 'Visitors by Domains', 'onclick'=> '$(\'input[name=data_type]\').val(\'visitors_by_domains\'); document.graph_frm.submit();', 'value' => 'visitors_by_domains'),
			array('name' => 'Visitors from Google', 'onclick'=> '$(\'input[name=data_type]\').val(\'visitors_from_google\'); document.graph_frm.submit();', 'value' => 'visitors_from_google'),
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
		)
	),
);
switch ( $data_type ) {
	case 'admin_visits':
		$caption = 'visits';
	break;
	case 'visitors_from_google':
		$caption = 'Visitors from Google';
	break;
}
echo '
<div class="box_type1" style="padding:20px;">
	<form method="post" name="graph_frm" style="">
	<input type="hidden" name="data_type" value="'.$data_type.'">
	<input type="hidden" name="group_by" value="'.$group_by.'">
	<input type="hidden" name="data_period" value="'.$data_period.'">
	'.show_data_period_toolbar($toolbar).'
	</form>	
	<div class="row" id="graph_container" style="height:350px;"></div>
</div>'.
draw_graph($caption, $data_type, $data_period, $group_by, $date, '', $q_pie, 'graph_container', 'transparent',
	'', false, false, false, array('for_userid' => $_GET['userid'], 'display_in_short' => $display_in_short));
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>