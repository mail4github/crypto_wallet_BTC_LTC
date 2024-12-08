<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Purchases';
$page_title = $page_header;
$page_desc = $page_header;
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
	$data_type = 'admin_money';
	$data_period = '90days';
	$group_by = 'day';
	$day_date = '';
}
$past_years = array();
$this_year = (int)date("Y");
for ($i = $this_year - 2; $i > $this_year - 6; $i--)
	$past_years[] = $i;

$toolbar = array(
	array('name' => '', 'selected' => $data_type, 'style' => 'text-align:left;',
		'buttons' => array(
			array('name' => 'Money', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_money\'); document.graph_frm.submit();', 'value' => 'admin_money'),
			array('name' => 'Orders by Country', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_purchases_by_country\'); document.graph_frm.submit();', 'value' => 'admin_purchases_by_country'),
			array('name' => 'Customers by Country', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_customers_by_country\'); document.graph_frm.submit();', 'value' => 'admin_customers_by_country'),
			array('name' => 'Amount by Country', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_amount_by_country\'); document.graph_frm.submit();', 'value' => 'admin_amount_by_country'),
			array('name' => 'Average Amount per User by Country', 'onclick'=> '$(\'input[name=data_type]\').val(\'admin_average_amount_per_user_by_country\'); document.graph_frm.submit();', 'value' => 'admin_average_amount_per_user_by_country'),
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
foreach ($past_years as $year) 
	$toolbar[2]['buttons'][] = array('value' => 'year_'.$year, 'onclick'=> '$(\'input[name=data_period]\').val(\'year_'.$year.'\'); document.graph_frm.submit();', 'name' => 'Year '.$year);
	
$prefix = '';
switch ( $data_type ) {
	case 'admin_money':
		$prefix = DOLLAR_SIGN;
		$caption = 'Money';
	break;
	case 'admin_purchases_by_country':
		$prefix = 'number of orders: ';
	break;
	case 'admin_customers_by_country':
		$prefix = 'number of customers: ';
	break;
	case 'admin_amount_by_country':
		$prefix = DOLLAR_SIGN;
	break;
	case 'admin_average_amount_per_user_by_country':
		$prefix = DOLLAR_SIGN;
	break;
}
echo '
<div class="box_type1" style="padding:20px;">
	<form method="post" name="graph_frm">
	<input type="hidden" name="data_type" value="'.$data_type.'">
	<input type="hidden" name="group_by" value="'.$group_by.'">
	<input type="hidden" name="data_period" value="'.$data_period.'">
	'.show_data_period_toolbar($toolbar).'
	</form>	
	<div class="row" id="graph_container" style="height:350px;"></div>
</div>'.
draw_graph($caption, $data_type, $data_period, $group_by, $date, $prefix, $q_pie);

include_once(DIR_COMMON_PHP.'print_sorted_table.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>