<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'p2p_loan.class.php');

$crypto = new Bitcoin();

$display_in_short = !empty($_GET['userid']);
if ( !empty($_GET['userid']) ) {
	$tmp_user = new User();
	$tmp_user->userid = (int)$_GET['userid'];
	$tmp_user->read_data(false);
}
else
	$tmp_user = $user_account;

$page_header = 'My Lendings';
$page_title = $page_header;
$page_desc = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

echo '
<style type="text/css">
.description{padding:0px;}
.label_at_left{font-weight:normal;}
.form-horizontal .label_at_right{text-align:left;}
.rating_table tr td{padding:3px 10px 3px 3px; text-align:left;}
@media (min-width: 1200px) {
	#loan_info_box .modal-dialog {width: 60%;}
}
.loan_info_help_table{margin: 0 auto 0 auto;}
</style>
';
if ( !empty($_POST['data_period']) )
	$data_period = $_POST['data_period'];
else
	$data_period = '6month';

$toolbar = array(
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
<div class="" style="padding:20px;">
	<form method="post" name="graph_frm" style="max-width:500px;">
	<input type="hidden" name="data_period" value="'.$data_period.'">
	'.show_data_period_toolbar($toolbar).'
	</form>	
</div>';

$rows_per_page = 0;
$current_page_number = 1;
$total_rows = 0;
$whole_header_html = '
<tr>
	<td>
		<div class="row">
			<div class="col-md-3" style="text-align:right;"></div>
			<div class="col-md-3" style="text-align:center;"><a class="sorted_table_link" href="javascript: RedrawWithSort(0);"><b>Progress</b></a>#sort_label0#</div>
			<div class="col-md-1" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(1);"><b>Rate</b></a>#sort_label1#</div>
			<div class="col-md-2" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(2);"><b>Collateral</b></a>#sort_label2#</div>
			<div class="col-md-2" style="text-align:right;"><a class="sorted_table_link" href="javascript: RedrawWithSort(3);"><b>Term</b></a>#sort_label3#</div>
			<div class="col-md-1" style="text-align:left;"><a class="sorted_table_link" href="javascript: RedrawWithSort(4);"><b>Created</b></a>#sort_label4#</div>
			
		</div>
	</td>
</tr>
';
$whole_row_html = '
<tr style="#c_row_background_color#" id="row_#c_transactionid#" term_in_days=#c_term# credit_score=#c_score# total_yield="#c_yield#%" lender_total_receives="#c_yield_as_money#" lender_receives="#c_income#" lender_receives_in_dollars="#c_income_in_dollars#" earliest_loan="#c_earliest_loan#" used_to_have_loans="#c_used_to_have_loans#" has_delinquencies="#c_has_delinquencies#" loan_assets="#c_assets_data#" >
	<td>
		<div class="row">
			<div class="col-md-3">
				<table style="width:200px;"><tr>
					<td style="text-align:center;">
						<span id="requested_in_crypto_#c_transactionid#">#c_amount_in_crypto#</span><br><span class="description">(<span id="requested_in_dollars_#c_transactionid#">#c_amount_in_dollars#</span>)</span><br>
						<span class="glyphicon glyphicon-arrow-right" aria-hidden="true" style="color:#54d654; font-size: 20px;"></span>
					</td>
					<td style="vertical-align:top;">
						<a href="#" onclick="show_loan_info(\'#c_transactionid#\'); return false;"><img src="#c_photo_borrower#" class="first_page_image user_thumbnail" style="width:40px; height:40px; margin:0 auto 0 auto;" id="image_borrower_#c_transactionid#"></a>
						<span class="description" style="display:block; text-align:center;" id="name_borrower_#c_transactionid#">#c_first_name_borrower#</span>
					</td>
				</tr></table>
			</div>
			<div class="col-md-3" style="text-align:center; #c_display_active#">
				<div class="progress" style="margin:6px 0 0 0;">
					<div class="progress-bar" role="progressbar" style="width:#c_progress_in_percents#%; background-color:#79da79;"></div>
				</div>
				<span style="#c_show_progress_in_percents#"><a href="#" onclick="show_amortization_schedule(#c_amount#, #c_term#, #c_rate#, #c_created_unix_timestamp#, #c_transactionid#); return false;">#c_progress_in_percents#% paid</a></span>
				<a href="#" onclick="show_amortization_schedule(#c_amount#, #c_term#, #c_rate#, #c_created_unix_timestamp#, #c_transactionid#); return false;"><span style="color:#ff0000; #c_show_due_days#">past due days: #c_due_days#</span></a>
			</div>
			<div class="col-md-3" style="text-align:center; #c_display_cancelled#">
				<span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:#ff0000; font-size:20px;"></span><br>
				<b>cancelled</b>
			</div>
			<div class="col-md-3" style="text-align:center; #c_display_completted#">
				<span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:#54d654; font-size:20px;"></span><br>
				<b>completed</b>
			</div>
			<div class="col-md-1" style="text-align:right; cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;">
				<span style="color:#c_rate_color#;">#c_rate#%</span><br><span class="description">per week</span>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<span id="shares_worth_#c_transactionid#" style="color:#c_assets_color#;">#c_assets#</span><br>
				<a href="#" onclick="show_loan_assets(\'#c_assets_data#\'); return false;" id="number_of_shares_#c_transactionid#">#c_number_of_shares# #c_number_of_shares_plural#</a>
			</div>
			<div class="col-md-2" style="text-align:right;">
				<a href="#" onclick="show_amortization_schedule(#c_amount#, #c_term#, #c_rate#, #c_created_unix_timestamp#, #c_transactionid#); return false;"><span id="term_in_payments_#c_transactionid#">#c_term_in_weeks#</span> weeks<br></a>
				<span class="description">#c_payments_left#</span>
			</div>
			<div class="col-md-1" style="text-align:left; cursor:pointer;" onclick="show_loan_info(\'#c_transactionid#\'); return false;">
				#c_created#
			</div>
		</div>
	</td>
</tr>	
';

$total_lended = 0;
$total_received = 0;
$total_loss = 0;
$row_eval = '
	global $user_account;
	global $crypto;
	global $total_lended;
	global $total_received;
	global $total_loss;
	$total_lended = $total_lended + $row["c_amount"];
	$total_received = $total_received + $row["c_received"];
	if ($row["c_status"] == "F")
		$total_loss = $total_loss + $row["c_amount"];
	$period_in_days = '.LOAN_PP_INTERVAL.';
	$income = $user_account->get_part_of_loan_payment_which_goes_to_lender($row["c_amount"], $row["c_term"], $row["c_rate"], '.LOAN_PP_INTERVAL.');
	if (empty($row["c_photo_lender"])) 
		$row["c_photo_lender"] = "/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60boy.png";
	if ( $row["c_borrower_from_this_site"] ) {
		if ( file_exists("'.DIR_WS_WEBSITE_PHOTOS.'".$row["c_borrower_userid"].".jpg") ) 
			$row["c_photo_borrower"] = "/'.DIR_WS_WEBSITE_PHOTOS_DIR.'".$row["c_borrower_userid"].".jpg";
	}
	if (empty($row["c_photo_borrower"])) 
		$row["c_photo_borrower"] = "/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60boy.png";
	$row["c_assets_color"] = $row["c_assets"] > $row["c_amount"] * '.$crypto->get_exchange_rate(DOLLAR_NAME).'?"#008800":"#000000";
	$row["c_amount_in_dollars"] = currency_format($row["c_amount"] * '.$crypto->get_exchange_rate(DOLLAR_NAME).');
	$row["c_amount_in_crypto"] = currency_format($row["c_amount"], "", "", "", false, false, "'.$crypto->symbol.'", '.$crypto->digits.');
	$row["c_rate_color"] = $row["c_rate"] > 0.01?"#008800":"#000000";
	$row["c_rate"] = round($row["c_rate"] * 100 * '.LOAN_PP_INTERVAL.', 1);
	$row["c_assets"] = currency_format($row["c_assets"]);
	$row["c_term_in_weeks"] = round($row["c_term"] / '.LOAN_PP_INTERVAL.');
	$row["c_assets_data"] = bin2hex(get_text_between_tags($row["c_data"], "<shares>", "</shares>"));
	$row["c_number_of_shares_plural"] = show_plural($row["c_number_of_shares"], "'.WORD_MEANING_SHARE.'");
	$row["c_first_name_borrower"] = '.($user_account->is_manager()?'$row["c_borrower_from_this_site"]?"<a href=/acc_viewuser.php?userid=".$row["c_borrower_userid"]." target=_blank>".$row["c_first_name_borrower"]."</a>":$row["c_first_name_borrower"]."<br>(".$row["c_borrower_websiteid"].")"':'$row["c_first_name_borrower"]').';
	$row["c_payments_left"] = ($row["c_term_in_weeks"] - floor($row["c_days_past"] / $period_in_days))." ".show_plural($row["c_term_in_weeks"] - floor($row["c_days_past"] / $period_in_days), "week")." left";
	$row["c_display_active"] = "display:none;";
	$row["c_display_cancelled"] = "display:none;";
	$row["c_display_completted"] = "display:none;";
	$row["c_row_background_color"] = "";
	$row["c_show_progress_in_percents"] = "";
	$row["c_show_due_days"] = "display:none;";
	switch ($row["c_status"]) {
		case "T":
		case "A":
			$row["c_display_active"] = "";
		break;
		case "F":
			$row["c_display_cancelled"] = "";
			$row["c_row_background_color"] = "background-color:#f2dede;";
		break;
		case "C":
			$row["c_display_completted"] = "";
			$row["c_row_background_color"] = "background-color:#dff0d8;";
		break;
	}
	if ($row["c_due_days"] > 0) {
		$row["c_show_progress_in_percents"] = "display:none;";
		$row["c_show_due_days"] = "";
		$row["c_row_background_color"] = "background-color:#fcf8e3;";
	}
	$row["c_income"] = currency_format($income, "", "", "", false, false, "'.$crypto->symbol.'", '.$crypto->digits.');
	$row["c_income_in_dollars"] = currency_format($income * '.$crypto->get_exchange_rate(DOLLAR_NAME).');
	$row["c_yield"] = round(($income * $row["c_term"] / '.LOAN_PP_INTERVAL.' / $row["c_amount"] - 1) * 100);
	$row["c_yield_as_money"] = currency_format($income * $row["c_term"] / '.LOAN_PP_INTERVAL.', "", "", "", false, false, "'.$crypto->symbol.'", 5);
	$row["c_earliest_loan"] =  get_text_between_tags($row["c_data"], "<unix_time_of_earliest_p2p_loan>", "</unix_time_of_earliest_p2p_loan>");
	if (!empty($row["c_earliest_loan"]))
		$row["c_earliest_loan"] =  date(\'j M Y\', $row["c_earliest_loan"]);
	else
		$row["c_earliest_loan"] = "did not have loans yet";
	$row["c_used_to_have_loans"] = get_text_between_tags($row["c_data"], "<number_of_previous_p2p_loans>", "</number_of_previous_p2p_loans>");
	$row["c_has_delinquencies"] = get_text_between_tags($row["c_data"], "<number_of_loan_delinquencies>", "</number_of_loan_delinquencies>");
	
';
$table_tag = '<table class="table table-striped table-hover" cellspacing="0" cellpadding="0" border="0" style="">';
$output_str = '';
if ( !$user_account->disabled && print_sorted_table(
		'lendings', 
		$header, 
		array('for_userid' => $tmp_user->userid, 'data_period' => $data_period), 
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
		4, 
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
	echo '<h3>No lendings yet.</h3><br><br>';
}

echo '
<div class="row box_type1">
	<div class="col-md-3" style="text-align:right;">
		Total Lended: '.currency_format($total_lended, "", "", "", false, false, $crypto->symbol, $crypto->digits).'<br>
		<span class="description">'.currency_format($total_lended * $crypto->get_exchange_rate(DOLLAR_NAME)).'</span>
	</div>
	<div class="col-md-3" style="text-align:right;">
		Total Received: '.currency_format($total_received, "", "", "", false, false, $crypto->symbol, $crypto->digits).'<br>
		<span class="description">'.currency_format($total_received* $crypto->get_exchange_rate(DOLLAR_NAME)).'</span>
	</div>
	<div class="col-md-3" style="text-align:right;">
		Total Loss: '.currency_format(-$total_loss, "", "color:#ff0000;", "", false, false, $crypto->symbol, $crypto->digits).'<br>
		<span class="description">'.currency_format(-$total_loss * $crypto->get_exchange_rate(DOLLAR_NAME), "", "color:#ff0000;").'</span>
	</div>
	<div class="col-md-3" style="text-align:right;">
		Balance: '.currency_format($total_received - $total_lended, "", "color:#ff0000;", "", false, false, $crypto->symbol, $crypto->digits).'<br>
		<span class="description">'.currency_format(($total_received - $total_lended) * $crypto->get_exchange_rate(DOLLAR_NAME), "", "color:#ff0000;").'</span>
	</div>
</div>
';
include(DIR_WS_INCLUDES.'box_wait.php');
require(DIR_WS_INCLUDES.'box_message.php');
require(DIR_WS_INCLUDES.'box_yes_no.php');
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');

echo generate_popup_code('loan_assets', '<div id="loan_assets_table_body"></div>'.'<div class="box_type1">'.show_help('These '.WORD_MEANING_SHARE.'s transfers to the lender&rsquo;s ownership on the very next day in case of failure of payment').'</div>', '', 'Loan Assets/Securities', '', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Close', '', '', '', 'loan_assets_setup()');

echo generate_popup_code('loan_info', '
<div class="row">
	<div class="col-md-3">
		<img src="" class="first_page_image" style="width:100%; max-width:200px; _height:100px; margin:0 auto 0 auto;" id="loan_info_image">
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
			<label class="control-label col-md-5 label_at_left">Borrower&rsquo;s earliest loan:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_earliest_loan"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower used to have loans:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_used_to_have_loans"></label>
		</div>
		<div class="row">
			<label class="control-label col-md-5 label_at_left">Borrower has Delinquencies:</label>
			<label class="control-label col-md-7 label_at_right" id="loan_info_has_delinquencies"></label>
		</div>
	</div>
</div>
<form method=post name="lend_money_from_loan_info">
	<input type=hidden name=form_submitted value=1>
	<input type=hidden name=payment_method value='.$crypto->crypto_name.'>
	<input type=hidden name=transactionid id="loan_info_form_transactionid" value=""> 
</form>
', '', '', '', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>', '', '', '', 'loan_info_setup(box_title)');

?>
<script type="text/javascript">
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

function show_amortization_schedule(amount, term, rate, created_unix_timestamp, transactionid)
{
	var message = "";
	var balance = amount;
	var n = term / <?php echo LOAN_PP_INTERVAL; ?>; // number of weeks
	var i = rate / 100; // per week
	var installment = amount * (i / (1 - Math.pow(1 + i, - n)));
	var total_tnterest = 0;
	//var week = 1;
	var date_of_loan = new Date(created_unix_timestamp * 1000);
	for (var i = 0; i < term / <?php echo LOAN_PP_INTERVAL; ?>; i++) {
		var interest = balance * parseFloat(rate) / 100 * <?php echo LOAN_PP_INTERVAL; ?>;
		var principal = installment - interest;
		total_tnterest = total_tnterest + interest;
		balance = balance - principal;
		if (balance < 0)
			balance = 0;
		date_of_loan.setDate(date_of_loan.getDate() + <?php echo LOAN_PP_INTERVAL; ?>);
		message = message + "<tr id=amortization_schedule_tr_" + i + "><td><div class=row><div class=col-md-2 id=amortization_schedule_label_" + i + "></div><div class=col-md-4>" + leading_zero(date_of_loan.getDate(), 2) + "/" + leading_zero((date_of_loan.getMonth()+1), 2) + "/" + date_of_loan.getFullYear() + "</div><div class=col-md-3>" + currency_format(installment, "<?php echo $crypto->symbol; ?>") + "</div><div class=col-md-3>" + currency_format(balance, "<?php echo $crypto->symbol; ?>") + "</div></div></td></tr>";
	}
	message = "<div style='height:400px; overflow:auto; padding:0 10px 0 10px;'><table class=table style='text-align:right;'><tr><td><div class=row><div class=col-md-2></div><div class=col-md-4>Due Date</div><div class=col-md-3>Payment</div><div class=col-md-3>Balance</div></div></td></tr>" + message + "</table></div>";
	show_message_box_box("Amortization Schedule", message, 0);

	try {
		$.ajax({
			method: "POST",
			url: "/api/get_sorted_table",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", table_name: "loans", sort_column: "8", sort_order: "ASC", row_number: "0", current_page_number: "0", max_ros: "500", installments_from_user: "<?php echo $tmp_user->userid; ?>", transactions_global_transactionid: transactionid }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					var table_body = "";
					for (var i = 0; i < arr_ajax__result["values"]["table"].length; i++) {
						$("#amortization_schedule_tr_" + i).css("background-color", "#ccf7cc");
						$("#amortization_schedule_label_" + i).html("<span class='label label-success'>Paid &nbsp;&nbsp;&nbsp;&nbsp;<span class='glyphicon glyphicon-ok' aria-hidden=true></span></span>");
					}
				}
			}
			catch(error){console.error(ajax__result + " amortization_schedule_table_body " + error);}
		});
	}
	catch(error){console_log("amortization_schedule_table_body: " + error);}
}

function loan_info_setup(transactionid)
{
	current_transactionid = transactionid;
	current_loan_assets = $("#row_" + transactionid).attr("loan_assets");
	$("#loan_info_form_transactionid").val(current_transactionid);

	$("#loan_info_title").html("");
	$("#loan_info_image").attr("src", $("#image_borrower_" + transactionid).attr("src") );

	$("#loan_info_name").html( $("#name_borrower_" + transactionid).html() );
	$("#loan_info_requested_in_crypto").html( $("#requested_in_crypto_" + transactionid).html() );
	$("#loan_info_requested_in_dollars").html( $("#requested_in_dollars_" + transactionid).html() );
	$("#loan_info_term_in_days").html( $("#row_" + transactionid).attr("term_in_days") );
	$("#loan_info_term_in_payments").html( $("#term_in_payments_" + transactionid).html() );
	$("#loan_info_credit_score").html( $("#row_" + transactionid).attr("credit_score") );
	$("#loan_info_total_yield").html( $("#row_" + transactionid).attr("total_yield"));
	$("#loan_info_lender_receives").html( $("#row_" + transactionid).attr("lender_receives") );
	$("#loan_info_lender_receives_in_dollars").html( $("#row_" + transactionid).attr("lender_receives_in_dollars") );

	$("#loan_info_lender_receives_during").html( $("#term_in_payments_" + transactionid).html() );
	$("#loan_info_lender_total_receives").html( $("#row_" + transactionid).attr("lender_total_receives") );

	$("#loan_info_number_of_shares").html( $("#number_of_shares_" + transactionid).html() );
	$("#loan_info_shares_worth").html( $("#shares_worth_" + transactionid).html() );
	$("#loan_info_earliest_loan").html( $("#row_" + transactionid).attr("earliest_loan") );
	$("#loan_info_used_to_have_loans").html( $("#row_" + transactionid).attr("used_to_have_loans") );
	$("#loan_info_has_delinquencies").html( parseInt($("#row_" + transactionid).attr("has_delinquencies")) > 0?"<span style='color:#ff0000;'>" + $("#row_" + transactionid).attr("has_delinquencies") + "</span>":"none" );
}

</script>
</body>
</html>
