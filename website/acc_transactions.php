<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');
$display_in_short = !empty($_GET['userid']);

if ( !$display_in_short && !$user_account->is_manager() ) {
	$page_header = 'Transactions';
	$page_title = $page_header;
	$page_desc = $page_header;
	require(DIR_WS_INCLUDES.'header.php');
	
	echo '
		<script type="text/javascript">
		'.file_get_contents(DIR_ROOT.'mobi_website/javascript/universal.constants.js').'
		'.file_get_contents(DIR_ROOT.'mobi_website/javascript/universal.functions.js').'
		</script>';
	require_once(DIR_WS_INCLUDES.'mobi_header.php');

	echo '
	<style type="text/css">
	@media (min-width: 992px) {
		.modal-dialog {
			width: 800px;
		}
	}
	</style>
	';

	$file_path = DIR_ROOT.'mobi_website/transactions.html';
	$file_data = file_get_contents($file_path);
	if ( $file_data && filesize($file_path) == strlen($file_data) ) {
		$file_data = get_text_between_tags($file_data, '<!-- for_site>> -->', '<!-- <<for_site -->');
		echo $file_data;
	}

	require_once(DIR_WS_INCLUDES.'box_wait.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_yes_no.php');
	require_once(DIR_WS_INCLUDES.'box_edit_item.php');

}
else {
	$page_header = '';
	$page_title = $page_header;
	$page_desc = 'Transactions';
	if ( $display_in_short )
		$_GET['noheader'] = 1;
	require(DIR_WS_INCLUDES.'header.php');
		
	global $currencies_arr;
	$currencies_menu = '<li><a href="#" onclick="$(\'input[name=currency]\').val(\'\'); document.set_currency_frm.submit();">All currencies</a></li>';
	$current_currency = '';
	foreach ($currencies_arr as $currency) {
		if (empty($current_currency)) {
			if (!empty($_GET['currency'])) {
				if ( strtolower($currency['description']) == $_GET['currency'] )
					$current_currency = $currency['description'];
			}
		}
		$currencies_menu = $currencies_menu.'<li class="notranslate"><a href="#" onclick="$(\'input[name=currency]\').val(\''.strtolower($currency['description']).'\'); document.set_currency_frm.submit();">'.$currency['description'].'</a></li>'; 
	}

	$confirmed_icon = '<svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:100%; height:100%; margin:0;"><circle fill="#ffffff" stroke="#9affa2" stroke-width="6" cx="50" cy="50" r="44" style="opacity:0.7;"></circle><line fill="none" stroke-linecap="round" stroke="#9affa2" stroke-width="15" stroke-miterlimit="10" x1="30" y1="50" x2="50" y2="70"></line> <line fill="none" stroke-linecap="round" stroke="#9affa2" stroke-width="15" stroke-miterlimit="10" x1="50" y1="70" x2="78" y2="40"></line></svg>';

	echo '
	<style type="text/css">
		.confirmations_indicator_st{position:absolute; width:14px; min-height:14px; max-height:14px; right:3px; top:4px;}
	</style>
	<form name="set_currency_frm" method="get">
		<input type="hidden" name="currency" value="'.$_GET['currency'].'">
		<input type="hidden" name="transaction_type" value="'.$_GET['transaction_type'].'">
		'.($user_account->is_manager()?'<input type="hidden" name="userid" value="'.$_GET['userid'].'">':'').'
	</form>
	<div class="row">
		<div class="col-sm-6" style="vertical-align:bottom;">
			<h1>Transactions</h1>
		</div>
		<div class="col-sm-6" style="vertical-align:bottom; text-align:right;">
			<div class="btn-group" role="group">
				<a href="#" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="text-align:left;">'.(empty($current_currency)?'All currencies':$current_currency).'&nbsp;&nbsp;<span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					'.$currencies_menu.'
				</ul>
			</div>
			<h1 style="display:inline-block;">&nbsp;</h1>
		</div>
	</div>
	';
	$transaction_type = tep_sanitize_string($_GET['transaction_type'], 2);

	include_once(DIR_COMMON_PHP.'print_sorted_table.php');

	if ( !$display_in_short )
		$rows_per_page = 13;
	else
		$rows_per_page = 6;
	$current_page_number = 1;
	$total_rows = 0;

	$whole_header_html = '
		'.($user_account->is_manager()?'
		<tr>
			<td style="width:30%;">
				<div class="row">
					<div class="col-md-2" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>Date</strong></a>#sort_label0#</div>
					<div class="col-md-1" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Type</strong></a>#sort_label1#</div>
						<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>Amount</strong></a>#sort_label2#</div>
						<div class="col-md-6" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>Description</strong></a>#sort_label3#</div>
						<div class="col-md-1" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(4);"><strong>'.($display_in_short?'Balance':'User').'</strong></a>#sort_label4#</div>
				</div>
			</td>
		</tr>
		':'
		').'
	';
	$whole_row_html = '
		<tr '.($user_account->is_manager()?'':'onclick="location.assign(\'/transaction?transactionid=#c_transactionid#\')" style="cursor: pointer;"').'>
			<td style="margin:0; padding:4px;">
				<div class=row style="margin:0; padding:0;">
					<div class=col-xs-3 style="padding-left:0; position:relative;">
						<div class="confirmations_indicator confirmations_indicator_st notranslate" id="#c_blockch_tr_hash#" _currency="#c_currency#">#c_confirmed_icon#</div>
						<span class="local_time_date_next_hour" unix_time="#c_unix_created#">#c_created_date#<br><span style="color:#'.COLOR1BASE.';">#c_created_time#</span></span><br>
						<span class="description">#c_type#</span>
					</div>
					<div class=col-xs-4 style="overflow:hidden;">
						<span class=description>'.($user_account->is_manager()?'<a href="/transaction?transactionid=#c_transactionid#">#c_description#</a>':'#c_description#').'</span><br>
						<span class="description notranslate">#c_address_to_show#</span><br>
						<span class="description notranslate">#c_note#</span>
					</div>
					<div class=col-xs-1 style="padding:0;">
						<span class="notranslate" style="text-transform:uppercase;">#c_currency#</span>
						'.($user_account->is_manager()?'#c_photo#':'').'
					</div>
					<div class=col-xs-4 style="padding:0; text-align:right;">
						<div class="notranslate">#c_commission#</div>
						<span class="description notranslate">#c_amount_in_usd#</span><br>
						<span class="description" style="color:#0000af;">#c_status#</span><br>
						'.($user_account->is_manager()?'
							'.($display_in_short?'#c_balance#':'<a href="/acc_viewuser.php?userid=#c_userid#" target="_blank">#c_firstname#</a>').'
						':''
						).'
					</div>
				</div>
			</td>
		</tr>
	';
	$row_eval = '
		global $user_account;
		global $confirmed_icon;
		$row["c_photo"] = (file_exists(DIR_WS_WEBSITE_PHOTOS.$row["c_userid"].".jpg")?"<image src=\'/".DIR_WS_WEBSITE_PHOTOS_DIR.$row["c_userid"].".jpg\' class=\'first_page_image\' style=\'width:40px; height:40px; display:inline; margin:0 10px 0 0; border:none;     border-radius:0; box-shadow:none; padding:0;\'>":"");
		$row["c_firstname"] = ucfirst(strtolower($row["c_firstname"]));
		$row["c_note"] = "";
		$crypto = Pay_processor::get_crypto_currency_by_name($row["c_currency"]);
		if ( isset($crypto) && $crypto ) {
			$exchange_rate = $crypto->get_exchange_rate(DOLLAR_NAME);
			$row["c_amount_in_usd"] = ($exchange_rate > 0 ? currency_format($row["c_commission_as_number"] * $exchange_rate, "", "", "", false, false, DOLLAR_SIGN, DOLLAR_DECIMALS) : "");
		}
		else
			$row["c_amount_in_usd"] = "";

		if ( $row["c_status"] == "P" )
			$row["c_status"] = "<span style=\'color:#'.COLOR1BASE.';\' class=\'description\'>pending</span>";
		else
		if ( $row["c_status"] == "D" ) {
			$row["c_status"] = "<span style=\'color:#ff0000;\' class=\'description\'>canceled</span>";
			$row["c_commission"] = "<span style=\'text-decoration:line-through;\'>".$row["c_commission"]."</span>";
			$row["c_note"] = "<span style=\'color:#ff0000;\'>".$row["c_payout_note"]."</span>";
		}
		else
		if ( $row["c_payout_status"] == "W" || $row["c_payout_status"] == "P" )
			$row["c_status"] = "<span style=\'color:#00AA00;\' class=\'description\'>processing</span>";
		else
			$row["c_status"] = "";

		$row["c_address_to_show"] = $row["c_address_to_receive"];
			
		$row["c_confirmed_icon"] = "";

		if ( !empty($row["c_servingid"]) ) {
			$row["c_blockch_tr_hash"] = "";
			$row["c_confirmed_icon"] = $confirmed_icon;
		}
		
		switch ($row["c_type"]) {
			case "AF": $row["c_type"] = "added funds"; break; 
			case "AJ": $row["c_type"] = "adjust balance"; break; 
			case "AI": $row["c_type"] = "admin info"; break; 
			case "BN": $row["c_type"] = "bonus"; break; 
			case "BR": $row["c_type"] = "borrow"; break; 
			case "CL": $row["c_type"] = "reward"; break; 
			case "CO": $row["c_type"] = "add funds"; break;
			case "CB": 
			case "CS": $row["c_type"] = "exchange"; break;
			case "DI": $row["c_type"] = "earning"; break;  
			case "DV": $row["c_type"] = "dividend"; break; 
			case "FE": $row["c_type"] = "fee"; break; 
			case "I":  $row["c_type"] = "reason"; break; 
			case "IN": $row["c_type"] = "installment"; break; 
			case "JB": $row["c_type"] = "reward"; break; 
			case "LA": $row["c_type"] = "set-off"; break; 
			case "LI": $row["c_type"] = "interest"; break; 
			case "LL": $row["c_type"] = "lend"; break; 
			case "LN": $row["c_type"] = "loan"; break; 
			case "LP": $row["c_type"] = "installment"; break; 
			case "PO": 
				$row["c_type"] = "payout"; 
				$row["c_address_to_show"] = $row["c_address_to_send"];
			break; 
			case "PP": $row["c_type"] = "withdrawal"; break; 
			case "PT": $row["c_type"] = "surfing"; break; 
			case "RE": $row["c_type"] = "reversal"; break; 
			case "RI": $row["c_type"] = "earn"; break; 
			case "SA": $row["c_type"] = "sale"; break; 
			case "SC": $row["c_type"] = "issue"; break; 
			case "SH": $row["c_type"] = "purchase"; break; 
			case "SM": $row["c_type"] = "purchase"; break; 
			case "SR": $row["c_type"] = "commission"; break; 
			case "SS": $row["c_type"] = "sale"; break; 
			case "SV": $row["c_type"] = "service"; break; 
			case "RE": $row["c_type"] = "reversal"; break; 
			case "TD": $row["c_type"] = "deposit"; break; 
			case "UP": $row["c_type"] = "upgrade"; break;
			case "MR": $row["c_type"] = "merchant&#39;s sale"; break;
			
			case "RF": 
			case "HA": 
			case "TB": $row["c_type"] = ""; break; 
		}

		if ($user_account->is_manager())
			$row["c_address_to_show"] = "<a href=".replaceCustomConstantInText("address", $row["c_address_to_show"], constant(strtoupper(Pay_processor::get_crypto_currency_by_address($row["c_address_to_show"])."_BLOCKS_EXPLORER"))).">".$row["c_address_to_show"]."</a>";
		
	';
	$table_tag = '<table class="table table-striped table-hover" cellspacing="0" cellpadding="0" border="0" style="">';
	$output_str = '';
	echo '
		<div class="box_type1" >
			<ul id="myTab" class="nav nav-tabs" role="tablist" >
				<li role="presentation" class="'.(empty($transaction_type)?'active':'').'"><a role="tab" href="?transaction_type=&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency="'.$_GET['currency'].'"" class="description">All</a></li>
				<li role="presentation" class="'.($transaction_type == 'AF'?'active':'').'"><a role="tab" href="?transaction_type=AF&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Credit</a></li>
				'.(defined('COST_OF_VISITOR') && COST_OF_VISITOR > 0?'<li role="presentation" class="'.($transaction_type == 'CL'?'active':'').'"><a role="tab" href="?transaction_type=CL&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Clicks</a></li>':'').'
				<li role="presentation" class="'.($transaction_type == 'PO'?'active':'').'"><a role="tab" href="?transaction_type=PO&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Debit</a></li>
				<li role="presentation" class="'.($transaction_type == 'SR'?'active':'').'"><a role="tab" href="?transaction_type=SR&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Commissions</a></li>
				<li role="presentation" class="'.($transaction_type == 'FE'?'active':'').'"><a role="tab" href="?transaction_type=FE&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Fees</a></li>
				<li role="presentation" class="'.($transaction_type == 'CS'?'active':'').'"><a role="tab" href="?transaction_type=CS&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Exchange</a></li>
				<li role="presentation" class="'.($transaction_type == 'DI'?'active':'').'"><a role="tab" href="?transaction_type=DI&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Interests</a></li>
				<li role="presentation" class="'.($transaction_type == 'TD'?'active':'').'"><a role="tab" href="?transaction_type=TD&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Deposits</a></li>
				<li role="presentation" class="'.($transaction_type == 'RA'?'active':'').'"><a role="tab" href="?transaction_type=RA&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Rewards</a></li>
				<li role="presentation" class="'.($transaction_type == 'BN'?'active':'').'"><a role="tab" href="?transaction_type=BN&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Bonus</a></li>
				'.($user_account->is_manager()?'
				<li role="presentation" class="'.($transaction_type == 'UP'?'active':'').'"><a role="tab" href="?transaction_type=UP&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Upgrades</a></li>
				
				<li role="presentation" class="'.($transaction_type == 'AI'?'active':'').'"><a role="tab" href="?transaction_type=AI&'.($user_account->is_manager()?'userid='.$_GET['userid'].'&':'').'currency='.$_GET['currency'].'" class="description">Admin</a></li>
				':'').'
			</ul>
			<div id="myTabContent" class="tab-content">
				<div role="tabpanel" class="tab-pane fade in active">
					';
	//if (defined('DEBUG_MODE')) echo "start 8: ".(time() - SCRIPT_STARTED_SEC)."<br>";
	if ( !$user_account->disabled && print_sorted_table(
			'transactions', 
			$header, 
			array('transaction_type' => $transaction_type, 'for_userid' => !empty($_GET['userid'])?$_GET['userid']:$user_account->userid, 'display_in_short' => $display_in_short, 'currency' => $_GET['currency']), 
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
		//if (defined('DEBUG_MODE')) echo "start 9: ".(time() - SCRIPT_STARTED_SEC)."<br>";
		if ( !empty($output_str) ) {
			echo '<div id=table_div>'.$output_str.'</div>';
			echo '<p>';
			paging($current_page_number, $total_rows, $rows_per_page, $row_number);
			echo '</p>';
		}
		else
			echo '<h2 style="margin-bottom:100px;">There are no transactions yet.</h2>';
	}
	else {
		echo '<h2>No transactions yet.</h2><br><br>';
	}
	echo '
				</div>
			</div>
		</div>';
	require_once(DIR_COMMON_PHP.'box_message.php');
}
//if (defined('DEBUG_MODE')) echo "start 10: ".(time() - SCRIPT_STARTED_SEC)."<br>";
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
