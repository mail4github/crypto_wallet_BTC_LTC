<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'p2p_loan.class.php');

$crypto = new Bitcoin();

if ( !empty($_POST['form_submitted']) ) {
	if ( empty($_POST['crypto_address']) && (empty($user_account->paypalemail) || strtolower($user_account->payoutoption) != strtolower($crypto->crypto_name)) ) {
		$box_message = 'Error: To receive installments from the lended money you have to set <b>payout method</b> as <b>'.$crypto->crypto_name.'</b> and enter valid '.$crypto->crypto_name.' address. Please <a href="/acc_payment_details.php">change payout method</a>.';
	}
	else {
		$pay_email = '';
		$make_post_request = false;
		$p2p_loan = new P2p_loan();
		if ( $p2p_loan->read_data($_POST['transactionid']) ) {
			if ($_POST['lend_from_balance'] == '1') {
				$box_message = $user_account->lend_from_balance($_POST['transactionid'], $_POST['crypto_address']);
				if (empty($box_message)) {
					header('Location: /acc_transactions.php');
					exit;
				}
			}
			else {
				$req = $user_account->get_request_to_add_funds($p2p_loan->amount_of_loan, $_POST['payment_method'], P2P_LEND_PREFIX.'_txid='.$_POST['transactionid'].'&crypto='.$crypto->crypto_name.'&address='.tep_sanitize_string($_POST['crypto_address']), $make_post_request, P2P_LEND_PREFIX, '', $crypto->crypto_symbol, $crypto->symbol);
				if ( is_integer(strpos($req, 'Error: ')) ) {
					$box_message = $req;
				}
				else {
					if ( !$make_post_request )
						header('Location: '.$req);
					else
						echo $req;
					exit;
				}
			}
		}
		else
			$box_message = 'Error: no such loan: '.$_POST['transactionid'];
	}
}

$page_header = 'Person to person loans';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

if ( ($user_account->stat_purchases == 0 && is_integer(strpos(HACKER_COUNTRIES, $user_account->country)) && is_integer(strpos(HACKER_COUNTRIES, $user_account->stat_country_last_login))) || !$user_account->get_rank_value('can_buy_shares') || ($user_account->account_type == 'G' && $user_account->rank == 0 && $user_account->purchases_disabled) ) {
	echo show_intro('', 'Not available.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}

echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'loans.png', 'We offer borrowing and lending between individuals without a traditional financial institution being involved. Choose a person to whom you are willining to lend money. All loans are secured by certain amount of '.WORD_MEANING_SHARE.'s. If, for any reason, your borrower fails to pay an installment these '.WORD_MEANING_SHARE.'s are going to the ownership of yours.<br>If you want to borrow money, click the following button: <button class="btn btn-success btn-xs" onclick="location.replace(\'/acc_loan_add.php\');">Get Loan...</button>', 'alert-info');

echo '
<style type="text/css">
.description{padding-right:0px;}
.div_btn_1{position:relative; width:100%; height:0px; z-index:10000;}
.div_btn_2{position:absolute; width:100%; height:0px; text-align:center;}
.wait_img{width:14px; height:14px; display:none; border:none;}
.label_at_left{font-weight:normal;}
.form-horizontal .label_at_right{text-align:left;}
.rating_table tr td{padding:3px 10px 3px 3px; text-align:left;}
@media (min-width: 1200px) {
	#loan_info_box .modal-dialog {width: 60%;}
}
.loan_info_help_table{margin: 0 auto 0 auto;}
</style>
';

include_once(DIR_COMMON_PHP.'print_sorted_table.php');
$ratings_table = '';
$probability = 14;
for ($i = 0; $i <= 1000; $i = $i + 200) {
	$credit_rating = get_credit_rating(1000 - $i, $rating_color, $rating_text_color);
	$credit_rating = $credit_rating[0];
	$ratings_table = $ratings_table."<tr><td><span class='label label-$rating_color' style='color:#$rating_text_color;'>$credit_rating</span></td><td>".round($i / $probability)."% - ".(round($i / $probability) + round(200 / $probability) - 0.01 )."%</td>";
	if ( $i <= 1000 ) {
		$i = $i + 200;
		$credit_rating = get_credit_rating(1000 - $i, $rating_color, $rating_text_color);
		$credit_rating = $credit_rating[0];
		$ratings_table = $ratings_table."<td><span class='label label-$rating_color' style='color:#$rating_text_color;'>$credit_rating</span></td><td>".($i >= 1000?"> &nbsp;".(round(($i - 200) / $probability) + round(200 / $probability) ):round($i / $probability)."% - ".(round($i / $probability) + round(200 / $probability) - 0.01))."%</td></tr>";
	}
}
$rows_per_page = 13;
$current_page_number = 1;
$total_rows = 0;
$whole_header_html = '
	<tr>
		<td style="width:10%; text-align:center;">
			<a class="sorted_table_link" href="javascript: RedrawWithSort(0);"><b>Borrower</b></a>#sort_label0#
		</td>
		<td style="width:40%;">
			<div class="row">
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(1);"><b>Amount</b></a>#sort_label1#</div>
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(2);"><b>Rate</b></a>#sort_label2#</div>
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(3);"><b>Score</b></a>#sort_label3#</div>
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(4);"><b>Term</b></a>#sort_label4#</div>
			</div>
		</td>
		<td style="width:50%;">
			<div class="row">
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(5);"><b>Collateral</b></a>#sort_label5#</div>
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(6);"><b>Income</b></a>#sort_label6#</div>
				<div class="col-md-2" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(7);"><b>Yield</b></a>#sort_label7#</div>
				<div class="col-md-3" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(8);"><b>Expires</b></a>#sort_label8#</div>
				<div class="col-md-1" style="text-align:right;"></div>
			</div>
		</td>
	</tr>
';
$whole_row_html = '
	<tr id="row_#c_transactionid#" loan_assets="#c_assets_data#" earliest_loan="#c_earliest_loan#" used_to_have_loans="#c_used_to_have_loans#" has_delinquencies="#c_has_delinquencies#">
		<td style="text-align:center; '.($user_account->is_manager()?'"':'cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;"').'>
			<img src="#c_photo#" class="first_page_image user_thumbnail" style="width:40px; height:40px; margin:0 auto 0 auto;" id="image_#c_transactionid#">
			<span class="description" style="display:block; text-align:center;" id="name_#c_transactionid#">#c_first_name#</span>
		</td>
		<td style="cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;">
			<div class="row">
				<div class="col-md-3" style="text-align:right;"><span id="requested_in_crypto_#c_transactionid#">#c_amount#</span><br><span class="description">(<span id="requested_in_dollars_#c_transactionid#">#c_amount_in_dollars#</span>)</span></div>
				<div class="col-md-3" style="text-align:right;"><span style="color:#c_rate_color#;">#c_rate#%</span><br><span class="description">per week</span></div>
				<div class="col-md-3" style="text-align:right;">
					<span class="label label-#c_rating_color#" style="color:#c_rating_text_color#;" id="loan_info_credit_rating_#c_transactionid#">#c_rating#</span>
					<br>
					<span class="description" id="credit_score_#c_transactionid#">#c_score#</span>
				</div>
				<div class="col-md-3" style="text-align:right;"><span id="term_in_days_#c_transactionid#">#c_term#</span> days<br><span class="description"><span id="term_in_payments_#c_transactionid#">#c_term_in_weeks#</span> weeks</span></div>
			</div>
		</td>
		<td>
			<div class="row">
				<div class="col-md-3" style="text-align:right;">
					<span id="shares_worth_#c_transactionid#" style="color:#c_assets_color#;">#c_assets#</span><br>
					<a href="#" onclick="show_loan_assets(\'#c_assets_data#\'); return false;" id="number_of_shares_#c_transactionid#">#c_number_of_shares# #c_number_of_shares_plural#</a>
				</div>
				<div class="col-md-3" style="text-align:right; cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;"><span id="lender_receives_#c_transactionid#">#c_income#</span><br><span class="description">(<span id="lender_receives_in_dollars_#c_transactionid#">#c_income_in_dollars#</span>) weekly</span></div>
				<div class="col-md-2" style="text-align:right; cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;"><span id="total_yield_#c_transactionid#">#c_yield#%</span><br><span class="description" id="lender_total_receives_#c_transactionid#">#c_yield_as_money#</span></div>
				<div class="col-md-3" style="text-align:right; cursor:pointer;" id="expires_#c_transactionid#" onclick="show_loan_info(\'#c_transactionid#\'); return false;">in #c_expires#</div>
				<div class="col-md-1" style="text-align:right;">#c_cancel_btn#</div>
			</div>
		</td>
	</tr>
';

$row_eval = '
	global $user_account;
	global $crypto;
	$period_in_days = '.LOAN_PP_INTERVAL.';
	$loan_payment = $user_account->calculate_loan_payment($row["c_amount"], $row["c_term"], $row["c_rate"], $period_in_days, $transactions_global_transactionid, $current_payment);
	$income = $user_account->get_part_of_loan_payment_which_goes_to_lender($row["c_amount"], $row["c_term"], $row["c_rate"], '.LOAN_PP_INTERVAL.');

	if ( $row["c_cancel_btn"] ) 
		$row["c_cancel_btn"] = "<button class=\'btn btn-danger btn-xs\' transactionid=\'".$row["c_transactionid"]."\' userid=\'".$row["c_userid"]."\' onclick=\'cancel_pressed(this); return false;\' >
		<div class=div_btn_1>
			<div class=div_btn_2>
				<img src=/images/wait_big3.gif class=wait_img id=wait_".$row["c_transactionid"].">
			</div>
		</div>
		<span id=btn_".$row["c_transactionid"].">Cancel...</span>
		</button>";
	else {
		$row["c_cancel_btn"] = "<form method=post name=lend_form_".$row["c_transactionid"]."><input type=hidden name=form_submitted value=1><input type=hidden name=payment_method value=".$crypto->crypto_name."><input type=hidden name=transactionid value=".$row["c_transactionid"]."> <input type=hidden name=crypto_address value=\'\'> <input type=hidden name=lend_from_balance value=\'0\'> <button class=\'btn btn-success btn-xs\' transactionid=\'".$row["c_transactionid"]."\' amount=\'".$row["c_amount"]."\' onclick=\'return lend_button_clicked(this); return false;\'>Lend</button></form>";
		'.($user_account->is_manager()?'
		$row["c_cancel_btn"] = $row["c_cancel_btn"]."<br><button class=\'btn btn-danger btn-xs\' transactionid=\'".$row["c_transactionid"]."\' userid=\'".$row["c_userid"]."\' onclick=\'cancel_pressed(this); return false;\' >
		<div class=div_btn_1>
			<div class=div_btn_2>
				<img src=/images/wait_big3.gif class=wait_img id=wait_".$row["c_transactionid"].">
			</div>
		</div>
		<span id=btn_".$row["c_transactionid"].">Cancel...</span>
		</button>";
		':'').'
	}
	$row["c_income"] = currency_format($income, "", "", "", false, false, "'.$crypto->symbol.'", '.$crypto->digits.');
	$row["c_income_in_dollars"] = currency_format($income * '.$crypto->get_exchange_rate(DOLLAR_NAME).');
	$row["c_yield"] = round(($income * $row["c_term"] / '.LOAN_PP_INTERVAL.' / $row["c_amount"] - 1) * 100);
	$row["c_yield_as_money"] = currency_format($income * $row["c_term"] / '.LOAN_PP_INTERVAL.', "", "", "", false, false, "'.$crypto->symbol.'", 5);
	
	$row["c_assets_color"] = $row["c_assets"] > $row["c_amount"] * '.$crypto->get_exchange_rate(DOLLAR_NAME).'?"#008800":"#000000";
	$row["c_amount_in_dollars"] = currency_format($row["c_amount"] * '.$crypto->get_exchange_rate(DOLLAR_NAME).');
	$row["c_amount"] = currency_format($row["c_amount"], "", "", "", false, false, "'.$crypto->symbol.'", '.$crypto->digits.');
	$row["c_rate_color"] = $row["c_rate"] > 0.01?"#008800":"#000000";
	$row["c_rate"] = round($row["c_rate"] * 100 * '.LOAN_PP_INTERVAL.', 1);
	
	$row["c_assets"] = currency_format($row["c_assets"]);
	$row["c_photo"] = get_text_between_tags($row["c_data"], "<photo>", "</photo>");
	if ( $row["c_user_from_this_site"] && !empty($row["c_photo"]) ) {
		$foto_file = get_text_between_tags($row["c_photo"], "'.DIR_WS_WEBSITE_PHOTOS_DIR.'", "");
		if ( !file_exists("'.DIR_WS_WEBSITE_PHOTOS.'".$foto_file) ) {
			$tmp_user = new User();
			$tmp_user->userid = $row["c_userid"];
			if ( $tmp_user->read_data(false) )
				$tmp_user->get_photo();
		}
	}
	if (empty($row["c_photo"])) 
		$row["c_photo"] = "/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60boy.png";
	$row["c_rating"] = get_credit_rating($row["c_score"], $row["c_rating_color"], $row["c_rating_text_color"]);
	$row["c_term_in_weeks"] = round($row["c_term"] / '.LOAN_PP_INTERVAL.');
	$row["c_assets_data"] = bin2hex(get_text_between_tags($row["c_data"], "<shares>", "</shares>"));
	$row["c_number_of_shares_plural"] = show_plural($row["c_number_of_shares"], "'.WORD_MEANING_SHARE.'");
	$row["c_first_name"] = '.($user_account->is_manager()?'$row["c_user_from_this_site"]?"<a href=/acc_viewuser.php?userid=".$row["c_userid"]." target=_blank>".$row["c_first_name"]."</a>":$row["c_first_name"]."<br>(".$row["c_user_websiteid"].")"':'$row["c_first_name"]').';
	$row["c_earliest_loan"] =  get_text_between_tags($row["c_data"], "<unix_time_of_earliest_p2p_loan>", "</unix_time_of_earliest_p2p_loan>");
	if (!empty($row["c_earliest_loan"]))
		$row["c_earliest_loan"] =  date(\'j M Y\', $row["c_earliest_loan"]);
	else
		$row["c_earliest_loan"] = "did not have loans yet";
	$row["c_used_to_have_loans"] = get_text_between_tags($row["c_data"], "<number_of_previous_p2p_loans>", "</number_of_previous_p2p_loans>");
	$row["c_has_delinquencies"] = get_text_between_tags($row["c_data"], "<number_of_loan_delinquencies>", "</number_of_loan_delinquencies>");
	if ($row["c_has_delinquencies"] > 0 && $row["c_used_to_have_loans"] == 0)
		$row["c_used_to_have_loans"] = $row["c_has_delinquencies"];
';
$table_tag = '<table class="table table-striped table-hover" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( !$user_account->disabled && print_sorted_table(
		'loans', 
		$header, 
		array('by_userid' => $user_account->userid), 
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
		3, 
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
	echo '<h3>No loans yet.</h3><br><br>';
}
echo generate_popup_code('loan_assets', '<div id="loan_assets_table_body"></div>'.'<div class="box_type1">'.show_help('These '.WORD_MEANING_SHARE.'s transfers to the lender&rsquo;s ownership on the very next day in case of failure of payment').'</div>', '', 'Loan Assets/Securities', '', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Close', '', '', '', 'loan_assets_setup()');

echo generate_popup_code('loan_info', '
<div class="row">
	<div class="col-md-3">
		<img src="" class="first_page_image user_thumbnail" style="width:100%; max-width:200px; _height:100px; margin:0 auto 0 auto;" id="loan_info_image">
		<div style="text-align:center; margin-top:10px;" id="loan_info_name"></div>
	</div>
	<div class="col-md-9 form-horizontal">
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Amount Requested:</label>
			<label class="control-label col-md-7 label_at_right"><span id="loan_info_requested_in_crypto"></span> <span class="description">(<span id="loan_info_requested_in_dollars"></span>)</span></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Loan Length:</label>
			<label class="control-label col-md-7 label_at_right"><span id="loan_info_term_in_days"></span> days <span class="description">(<span id="loan_info_term_in_payments"></span> payments)</span></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower&rsquo;s credit score:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_credit_score"></label>
		</div>
		<div class="row">
			<div class="col-md-12">
			'.show_help('This risk score is built using historical data. The score ranges from 1 to 1000, with 1000 being the best.', '', 'text-success', 'loan_info_help_table').'
			</div>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower&rsquo;s credit rating:</label>
			<label class="control-label col-md-7 label_at_right"><span class="label label-danger" style="color:#ffffff;" id="loan_info_credit_rating">F-</span></label>
		</div>
		<div class="row">
			<div class="col-md-12">
			'.show_help('
			The borrower&rsquo;s credit rating is allowing you to analyze a level of risk of default (estimated probability of loss):
			<table class="rating_table" style="width:100%;">
			<tr><td></td><td style="width:50%;"></td><td></td><td style="width:50%;"></td></tr>
			'.$ratings_table.'
			</table>
			', '', 'text-success', 'loan_info_help_table').'
			</div>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Lender&rsquo;s Total Yield:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_total_yield"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Lender receives:</label>
			<label class="control-label col-md-7 label_at_right"><span id="loan_info_lender_receives"></span> <span class="description">(<span id="loan_info_lender_receives_in_dollars"></span>)</span> every week, during <span id="loan_info_lender_receives_during"></span> weeks</label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Lender total receives:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_lender_total_receives"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Asset-based lending:</label>
			<label class="control-label col-md-7 label_at_right">yes</label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Loan Assets/Securities:</label>
			<label class="control-label col-md-7 label_at_right">
				<a href="#" onclick="$(\'#loan_info_box\').modal(\'hide\'); show_loan_assets(current_loan_assets); return false;" id="loan_info_number_of_shares"></a>
				<span class="description">cumulative worth</span> <span id="loan_info_shares_worth">$13.00</span></label>
		</div>
		<div class="row">
			<div class="col-md-12">
			'.show_help('these assets transfers to the lender&rsquo;s ownership on the very next day in case of failure of payment', '', 'text-success', 'loan_info_help_table').'
			</div>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Listing Expires:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_expires"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower&rsquo;s earliest loan:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_earliest_loan"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower used to have loans:</label>
			<div class="form-group" style="display:table-row;" id="loan_info_used_to_have_loans_div">
				<label class="control-label col-md-7 label_at_right" id="loan_info_used_to_have_loans"></label>
			</div>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower has Delinquencies:</label>
			<div class="form-group" style="display:table-row;" id="loan_info_has_delinquencies_div">
				<label class="control-label col-md-7 label_at_right" id="loan_info_has_delinquencies"></label>
			</div>
		</div>
	</div>
</div>
<form method=post name="lend_money_from_loan_info">
	<input type=hidden name=form_submitted value=1>
	<input type=hidden name=payment_method value='.$crypto->crypto_name.'>
	<input type=hidden name=transactionid id="loan_info_form_transactionid" value=""> 
</form>
', 'loan_info_lend();', '', '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Lend Money...', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>', '', '', '', 'loan_info_setup(box_title)');

include(DIR_WS_INCLUDES.'box_wait.php');
require(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_edit_item.php');
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_WS_INCLUDES.'box_yes_no.php');
echo $crypto->get_java_code_to_check_address();
?>
<script type="text/javascript">

var current_obj = 0;
var current_transactionid = "";
var current_loan_assets = "";
var crypto_address_last_value = "";
var receivers_crypto_currency = "<?php echo $crypto->crypto_name; ?>";
var crypto_address_description = "";

function loan_assets_setup()
{
	var assets_data = hex_to_string($("#loan_assets_title").html());
	assets_data = JSON.parse(assets_data);

	var table_body = "";
	for (var i = 0; i < assets_data.length; i++) {
		table_body = table_body + "<tr><td><div class=row><div class=col-sm-6><a href=/stocks/" + assets_data[i][0] + "><img src='/tmp/" + assets_data[i][0] + "_cover_img.jpeg' style='width:57px; height:32px;'></a></div><div class=col-sm-6><a href=/stocks/" + assets_data[i][0] + "><span class=notranslate>" + assets_data[i][0] + "</span></a></div></div></td><td>" + assets_data[i][1] + "</td></tr>";
	}
	table_body = "<table class='table table-striped'>" + table_body + "</table>";
	$("#loan_assets_table_body").html(table_body);

	$("#loan_assets_title").html("Loan Assets");
	
}

function lend_button_clicked(obj)
{
	current_obj = obj;
	var validate_pattern = "<?php echo $crypto->pattern; ?>";

	var length = get_text_between_tags(validate_pattern, "{", "}");
	var length_arr = length.split(",");
	length = (parseInt(length_arr[0]) + 1) + " - " + (parseInt(length_arr[1]) + 1);
	var start_let = get_text_between_tags(validate_pattern, "^[", "]");
	var start_letters = "";
	for (var j = 0; j < start_let.length; j++) {
		if ( start_letters.length > 0 )
			start_letters = start_letters + " or ";
		start_letters = start_letters + "'" + start_let[j] + "'";
	}
	crypto_address_description = "The " + receivers_crypto_currency + " address must be " + length + " alphanumeric characters, beginning with the " + start_letters + ". The address must be generated by your "+receivers_crypto_currency+" wallet";
	show_edit_item_box("crypto_address", string_to_hex(receivers_crypto_currency + " address:"), string_to_hex(crypto_address_last_value), string_to_hex("<p>To receive installments from the lended money you have to enter "+receivers_crypto_currency+" address.</p><div class=description>"+crypto_address_description+"</div>"), undefined, crypto_address_entered, "on_error", undefined, undefined, string_to_hex("enter " + receivers_crypto_currency + " address to receive installments"), undefined, undefined, validate_pattern);
	return false;
}

function crypto_address_entered(value, now_edited)
{
	crypto_address_last_value = value;
	var r = edit_item_value.checkValidity();
	if ( r && value.length > 0 ) {
		if ( !check_ctypto_address(value) && !confirm("This address: " + value + " looks like wrong address. Do you want to continue?")) 
			return false;
		document.forms["lend_form_" + current_obj.getAttribute("transactionid")].crypto_address.value = value;
		if (typeof currency_balances != 'undefined' && currency_balances["btc"] >= current_obj.getAttribute("amount") ) {
			if ( confirm("Do you want to use balance?") )
				document.forms["lend_form_" + current_obj.getAttribute("transactionid")].lend_from_balance.value = "1";
		}
		document.forms["lend_form_" + current_obj.getAttribute("transactionid")].submit();
	}
	else {
		alert("Error: " + crypto_address_description);
	}
}

function cancel_pressed(obj)
{
	current_obj = obj;
	show_box_yesno_box("Do you really want to cancell this offer?", "cancel_loan", "Confirm", "Yes", "No");
}

function cancel_loan()
{
	obj = current_obj;
	$("#btn_" + obj.getAttribute("transactionid")).css("opacity", 0);
	$("#wait_" + obj.getAttribute("transactionid")).show();
	
	try {
		$.ajax({
			method: "POST",
			url: "/api/user_cancel_loan",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", transactionid: obj.getAttribute("transactionid"), owner_userid: obj.getAttribute("userid") }
		})
		.done(function( ajax__result ) {
			try
			{
				$("#btn_" + obj.getAttribute("transactionid")).css("opacity", 255);
				$("#wait_" + obj.getAttribute("transactionid")).hide();
				
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					location.reload();
				}
				else {
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				}
			}
			catch(error){console.error(ajax__result + " cancel_loan " + error);}
		});
	}
	catch(error){console.error("cancel_loan: " + error);}
}

function loan_info_setup(transactionid)
{
	current_transactionid = transactionid;
	current_loan_assets = $("#row_" + transactionid).attr("loan_assets");
	$("#loan_info_form_transactionid").val(current_transactionid);

	$("#loan_info_title").html("");
	$("#loan_info_image").attr("src", $("#image_" + transactionid).attr("src") );

	$("#loan_info_name").html( $("#name_" + transactionid).html() );
	$("#loan_info_requested_in_crypto").html( $("#requested_in_crypto_" + transactionid).html() );
	$("#loan_info_requested_in_dollars").html( $("#requested_in_dollars_" + transactionid).html() );
	$("#loan_info_term_in_days").html( $("#term_in_days_" + transactionid).html() );
	$("#loan_info_term_in_payments").html( $("#term_in_payments_" + transactionid).html() );
	
	$("#loan_info_credit_score").html( $("#credit_score_" + transactionid).html() );

	$("#loan_info_credit_rating").html( $("#loan_info_credit_rating_" + transactionid).html() );
	$("#loan_info_credit_rating").css("color", $("#loan_info_credit_rating_" + transactionid).css("color") );
	$("#loan_info_credit_rating").removeClass("label-danger").removeClass("label-warning").removeClass("label-success");
	if ( $("#loan_info_credit_rating_" + transactionid).hasClass( "label-danger" ) )
		$("#loan_info_credit_rating").addClass( "label-danger" );
	else
	if ( $("#loan_info_credit_rating_" + transactionid).hasClass( "label-warning" ) )
		$("#loan_info_credit_rating").addClass( "label-warning" );
	else
	if ( $("#loan_info_credit_rating_" + transactionid).hasClass( "label-success" ) )
		$("#loan_info_credit_rating").addClass( "label-success" );
	$("#loan_info_total_yield").html( $("#total_yield_" + transactionid).html() );
	$("#loan_info_lender_receives").html( $("#lender_receives_" + transactionid).html() );
	$("#loan_info_lender_receives_in_dollars").html( $("#lender_receives_in_dollars_" + transactionid).html() );
	$("#loan_info_lender_receives_during").html( $("#term_in_payments_" + transactionid).html() );
	$("#loan_info_lender_total_receives").html( $("#lender_total_receives_" + transactionid).html() );

	$("#loan_info_number_of_shares").html( $("#number_of_shares_" + transactionid).html() );
	$("#loan_info_shares_worth").html( $("#shares_worth_" + transactionid).html() );
	$("#loan_info_expires").html( $("#expires_" + transactionid).html() );
	$("#loan_info_earliest_loan").html( $("#row_" + transactionid).attr("earliest_loan") );
	$("#loan_info_used_to_have_loans").html( $("#row_" + transactionid).attr("used_to_have_loans") );
	
	//$("#loan_info_has_delinquencies").html( parseInt($("#row_" + transactionid).attr("has_delinquencies")) > 0?"<span style='color:#ff0000;'>" + $("#row_" + transactionid).attr("has_delinquencies") + "</span>":"none" );
	$("#loan_info_has_delinquencies").html( parseInt($("#row_" + transactionid).attr("has_delinquencies")) > 0?$("#row_" + transactionid).attr("has_delinquencies"):"none" );
	
	$("#loan_info_used_to_have_loans_div").removeClass("has-error").removeClass("has-success");
	$("#loan_info_has_delinquencies_div").removeClass("has-error").removeClass("has-success");

	if ( parseInt($("#row_" + transactionid).attr("has_delinquencies")) > 0 ) {
		$("#loan_info_has_delinquencies_div").addClass("has-error").removeClass("has-success");
		$("#loan_info_used_to_have_loans_div").addClass("has-error").removeClass("has-success");
	}
	else {
		if ( parseInt($("#row_" + transactionid).attr("used_to_have_loans")) > 0) {
			$("#loan_info_used_to_have_loans_div").addClass("has-success").removeClass("has-error");
			$("#loan_info_has_delinquencies_div").addClass("has-success").removeClass("has-error");
		}
	}
	if ( $("#row_" + transactionid).attr("has_delinquencies") > 0 )
		$("#loan_info_has_delinquencies").effect("pulsate", { times:40 }, 20000);
}

function loan_info_lend()
{
	lend_money_from_loan_info.submit();
}

</script>
</body>
</html>
