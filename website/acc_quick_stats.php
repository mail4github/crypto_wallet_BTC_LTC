<?php
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$display_in_short = !empty($_GET['userid']);
$page_header = 'Statistics';
$page_title = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
if ( !empty($_GET['userid']) ) {
	$tmp_user = new User();
	$tmp_user->userid = (int)$_GET['userid'];
	$tmp_user->read_data(false, true);
}
else
	$tmp_user = $user_account;

if ( !$tmp_user->disabled || $display_in_short ) {
	if ( !$user_account->is_manager() ) {
		global $currencies_arr;
		foreach ($currencies_arr as $currency) {
			if ( strtolower($currency['currency']) != strtolower(DOLLAR_NAME) && intval($currency['instant_withdraw']) ) {
				$exchange_rate = 1;
				$crypto_crnc = Pay_processor::get_crypto_currency_by_name($currency['currency']);
				if ($crypto_crnc)
					$exchange_rate = $crypto_crnc->get_exchange_rate(DOLLAR_NAME);

				echo '
				<div style="margin-bottom:0px;" class="row _form-horizontal">
					<div class="col-md-4"><span style="font-size:150%;">'.$currency['description'].' Balance:</span><span style="font-size:250%;">&nbsp;</span></div>
					<div class="col-md-8" style="_text-align:right;"><span class="'.strtolower($currency['currency']).'_balance" style="font-size:250%;"><span style="color:#ddd; text-shadow:none;">&#x258b;.&#x258b;&#x258b;&#x258b;</span></span></div>
				</div>
				<div style="margin-bottom:30px;" class="row _form-horizontal">
					<div class="col-md-4"><p class="description visible_on_big_screen" style="text-align:right;">Available:</p></div>
					<div class="col-md-8" style="_text-align:right;"><p class="description"><span class="invisible_on_big_screen">Available: </span><span class="'.strtolower($currency['currency']).'_available_funds"><span style="color:#ddd; text-shadow:none;">&#x258b;.&#x258b;&#x258b;&#x258b;</span></span>  (<span class="available_in_usd_'.strtolower($currency['currency']).' notranslate"></span>)</p></div>
				</div>
				';
			}
		}
	}
	else {
		$activity_points = $tmp_user->get_activity_points('activity');
		echo '
		<h2 title="'.$activity_points['purchases_std_dev_plus_avg'].'">Activity:</h2>
		';
		$table_arr = array(
			array('Before last '.ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS.' '.show_plural(ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS, 'day').' purchases:', currency_format($activity_points['total_purchases']), $activity_points['total_purchases_points']),
			
		);
		if ( defined('PAYPAL_DISABLED') && PAYPAL_DISABLED == 'true' ) {
			$table_arr[] = array('Last '.ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS.' days purchases:', currency_format($activity_points['purchases']), $activity_points['purchases_points'] + $activity_points['solid_purchases_points']);
		}
		else {
			$table_arr[] = array('Last '.ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS.' days purchases:', currency_format($activity_points['purchases']), $activity_points['purchases_points']);
			$table_arr[] = array('Last '.ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_FOR_DAYS.' days purchases by '.$tmp_user->common_param_solid_payment_providers.':', currency_format($activity_points['solid_purchases']), $activity_points['solid_purchases_points']);
		}
		$table_arr[] = array('<a href="/acc_referrals.php">Last 30 days referrals</a>:', $activity_points['refetrals'], $activity_points['refetrals_points']);
		$table_arr[] = array('Last 30 days purchases made by referrals:', $activity_points['refetrals_purchases'], $activity_points['refetrals_purchases_points']);
		$table_arr[] = array('<a href="/issue">Last 15 days issued new '.WORD_MEANING_SHARE.'s</a>:', $activity_points['issued_shares'], $activity_points['issued_shares_points']);
		$table_arr[] = array('Last 7 days <a href="/acc_job_perform.php">jobs done</a>:', $activity_points['jobs_done'], $activity_points['jobs_done_points']);

		echo '
		<table class="table table-condensed table-striped" cellspacing="0" cellpadding="0" border="0" >
		';
		foreach ($table_arr as $row) {
			echo '<tr><td style="width:3%;" class="visible_on_big_screen"></td><td>'.$row[0].'</td><td style="text-align:right;">'.$row[1].'</td><td style="text-align:right;">'.$row[2].' bonus points</td><td style="width:10%;" class="visible_on_big_screen"></td></tr>';
		}
		echo '
			<tr><td style="width:3%; border-top: solid 1px #'.COLOR1DARK.';" class="visible_on_big_screen"></td><td colspan="2" style="font-weight:bold; border-top: solid 1px #'.COLOR1DARK.';"><h2>Total of Bonus Points:</h2></td><td style="text-align:right; font-weight:bold; border-top: solid 1px #'.COLOR1DARK.';"><h2>'.$activity_points['total_points'].' bonus '.show_plural($activity_points['total_points'], 'point').'</h2></td><td style="width:10%; border-top: solid 1px #'.COLOR1DARK.';" class="visible_on_big_screen"></td></tr>
		</table>';
		
		echo '
		<table class="table table-condensed table-borderless" cellspacing="0" cellpadding="0" border="0" >
		<tr>
			<td style="" class="visible_on_big_screen"></td>
			<td style=""></td>
			<td colspan="2" style="text-align:right;">Max. cash-out per month:</td>
			<td style="" class="visible_on_big_screen"></td>
		</tr>
		';
		global $ranks;
		$number_of_ranks = count($ranks) - 2;
		$max_cashout = 30 / (int)$ranks[$number_of_ranks]['cashout'] * $ranks[$number_of_ranks]['max_cashout'];
		for ($i = $number_of_ranks; $i >= 0; $i--) {
			$rank_cashout = 30 / (int)$ranks[$i]['cashout'] * $ranks[$i]['max_cashout'];
			$rank_cashout_value = ($i == $number_of_ranks?'unlimited':currency_format($rank_cashout));
			$individual_withdraw_period = $tmp_user->get_individual_withdraw_period();
			echo '<tr>
				'.( $activity_points['total_points'] >= $i * 100 && $activity_points['total_points'] < ($i + 1) * 100 || ( $activity_points['total_points'] >= 500 && $i == $number_of_ranks ) ?
					'
					<td style="width:3%; border-top: solid 1px #'.COLOR1LIGHT.'; background-color:#'.COLOR1DARK.'; color:#ffffff; font-weight:bold;" class="visible_on_big_screen"></td>
					<td style="border-top: solid 1px #'.COLOR1LIGHT.'; background-color:#'.COLOR1DARK.'; color:#ffffff; font-weight:bold; padding-right: 20px;">You have '.$activity_points['total_points'].' bonus points, cash-out <!--once in '.$individual_withdraw_period.' '.show_plural($individual_withdraw_period, 'day').'--> by '.currency_format($ranks[$i]['max_cashout']).', cash-out fee: '.round($ranks[$i]['withdrawal_fee'] * 100).'%'.'</td>
					<td style="width:20%; border-top: solid 1px #'.COLOR1LIGHT.'; background-color:#'.COLOR1DARK.'; color:#ffffff; font-weight:bold;" title="Maximum cash-out per month: '.$rank_cashout_value.'"><div style="background-color:#'.COLOR1BASE.'; width:'.round($rank_cashout / $max_cashout * 100).'%; height:20px;"></td>
					<td style="text-align:right; border-top: solid 1px #'.COLOR1LIGHT.'; background-color:#'.COLOR1DARK.'; color:#ffffff; font-weight:bold;" title="Maximum cash-out per month: '.$rank_cashout_value.'">'.currency_format(30 / $individual_withdraw_period * $ranks[$i]['max_cashout']).'</td>
					<td style="width:3%; border-top: solid 1px #'.COLOR1LIGHT.'; background-color:#'.COLOR1DARK.'; color:#ffffff; font-weight:bold; " class="visible_on_big_screen"></td>
					'
					:'
					<td style="width:3%; border-top: solid 1px #'.COLOR1LIGHT.';" class="visible_on_big_screen"></td>
					<td style="border-top: solid 1px #'.COLOR1LIGHT.'; '.($activity_points['total_points'] >= $i * 100?'opacity:0.3':'opacity:0.3').'">from '.($i * 100).($i == $number_of_ranks?'':' to '.($i * 100 + 99)).' points, cash-out <!--once in '.$ranks[$i]['cashout'].' '.show_plural($ranks[$i]['cashout'], 'day').',--> by '.currency_format($ranks[$i]['max_cashout']).', cash-out fee: '.round($ranks[$i]['withdrawal_fee'] * 100).'%'.'</td>
					<td style="width:20%; border-top: solid 1px #'.COLOR1LIGHT.';" title="Maximum cash-out per month: '.$rank_cashout_value.'"><div style="background-color:#'.COLOR1BASE.'; width:'.($i == $number_of_ranks?100:round($rank_cashout / $max_cashout * 100)).'%; height:20px; '.($activity_points['total_points'] >= $i * 100?'opacity:0.3;':'').'" ></td>
					<td style="text-align:right; border-top: solid 1px #'.COLOR1LIGHT.'; '.($activity_points['total_points'] >= $i * 100?'opacity:0.3':'').'">'.$rank_cashout_value.'</td>
					<td style="width:3%; border-top: solid 1px #'.COLOR1LIGHT.';" class="visible_on_big_screen"></td>
					'
				).'
			</tr>';
		}
		echo '</table>
		';
	}
	if ( !empty($_POST['data_period']) )
		$data_period = $_POST['data_period'];
	else
		$data_period = '7days';
	
	if ( !empty($_POST['group_by']) )
		$group_by = $_POST['group_by'];
	else
		$group_by = 'day';

	$toolbar = array(
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
				array('name' => 'Period: This Month', 'onclick'=> '$(\'input[name=data_period]\').val(\'this_month\'); document.graph_frm.submit();', 'value' => 'this_month'),
				array('name' => 'Period: Last Month', 'onclick'=> '$(\'input[name=data_period]\').val(\'last_month\'); document.graph_frm.submit();', 'value' => 'last_month'),
				array('name' => 'Period: This Year', 'onclick'=> '$(\'input[name=data_period]\').val(\'this_year\'); document.graph_frm.submit();', 'value' => 'this_year'),
				array('name' => 'Period: Last Year', 'onclick'=> '$(\'input[name=data_period]\').val(\'last_year\'); document.graph_frm.submit();', 'value' => 'last_year'),
			)
		),
	);
	echo '
	<div class="box_type2" style="padding:20px;">
		<form method="post" name="graph_frm" style="max-width:500px;">
		<input type="hidden" name="group_by" value="'.$group_by.'">
		<input type="hidden" name="data_period" value="'.$data_period.'">
		'.show_data_period_toolbar($toolbar).'
		</form>	
	</div>
	<div class="box_type1" style="padding:20px;"><div class="row" id="graph_container1" style="height:300px;"></div></div>
	'.
	draw_graph('Money Flow', 'money_flow',	$data_period, $group_by, 1, DOLLAR_SIGN, '', 'graph_container1', 'transparent', '', false, false, false, array('for_userid' => $tmp_user->userid, 'remove_graphs' => 'sales,dividends'), true).
	'<div class="box_type1" style="padding:20px;"><div class="row" id="graph_container2" style="height:300px;"></div></div>'.
	draw_graph('Visitors', 'visitors', $data_period, $group_by, 2, '', '', 'graph_container2', 'transparent', '', false, false, false, array('for_userid' => $tmp_user->userid), true).
	'<div class="box_type1" style="padding:20px;"><div class="row" id="graph_container3" style="height:300px;"></div></div>'.
	draw_graph('Referrals', 'admin_users', $data_period, $group_by, '', '', '', 'graph_container3', 'transparent', '', false, false, false, array('parentid' => $tmp_user->userid, 'display_in_short' => 1), true).
	($user_account->is_manager()
		?'<div class="box_type1" style="padding:20px;"><div class="row" id="graph_container4" style="height:300px;"></div></div>'.
		draw_graph('', 'purchases_by_refferrals', $data_period, $group_by, '', DOLLAR_SIGN, '', 'graph_container4', 'transparent', '', false, false, false, array('for_userid' => $tmp_user->userid, 'display_in_short' => 1), true)
		:''
	).
	'
	<br><br>
	';
}
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>
