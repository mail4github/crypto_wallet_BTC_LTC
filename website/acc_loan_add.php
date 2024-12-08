<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Make request for Loan';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
$crypto = new Bitcoin();

if ( empty($user_account->paypalemail) || strtolower($user_account->payoutoption) != strtolower($crypto->crypto_name) ) {
	echo show_intro('', 'To receive a loan you have to set <b>payout method</b> as <b>'.$crypto->crypto_name.'</b> and enter valid '.$crypto->crypto_name.' address. Please <a href="/acc_payment_details.php">change payout method</a>.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}

if ( $user_account->stat_real_funds < SHARE_PRICE_MINIMUM || $user_account->is_user_punished() ) {
	echo show_intro('', 'To apply for loan you have to have a positive available funds.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}

$min_max_term_deposit = $user_account->get_min_max_term_deposit();
if ( $min_max_term_deposit['bitcoin']['12_months_rate'] < 1 ) {
	echo show_intro('', 'No loans available.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}
//if (defined('DEBUG_MODE')) echo "stat_real_funds: ".$user_account->stat_real_funds.'<br>';

$terms_list = '';
for($i = 2; $i <= 52; $i++)
	$terms_list = $terms_list.'<option value="'.($i * LOAN_PP_INTERVAL).'" '.($i == 2?'selected':'').'>'.($i * LOAN_PP_INTERVAL).'</option>';

echo '
<style type="text/css">
@media (max-width: 991px) {
	.col-md-6{padding: 20px 0 10px 0;}
	.row{margin: 0px auto 0px auto;}
	.loan_glyphicon{min-width:70px;}
}
@media (min-width: 992px) {
	.row{margin: 20px auto 10px auto;}
	.loan_glyphicon{min-width:100px;}
}
.loan_item_row{width:80%; min-width: 300px;}
.input-group{width: 80%;}
.loan_glyphicon{font-weight:bold; font-family:arial; text-align:left;}
</style>
'.show_intro('/images/ask_for_loan.png', 'You can borrow money without using a traditional bank or credit union. People with extra money will lend money to you when you in need of cash.<br>Your '.WORD_MEANING_SHARE.'s are assets to secure the loan. To make a request for loan you have to own, at least, one '.WORD_MEANING_SHARE.'.<br>After you receive a loan you have to make weekly payments until all the loan and interest are paid out completelly.', 'alert-info').'
<div class="row loan_item_row">
	<div class="col-md-6">
		<label class="control-label" style="">Borrow Amount:</label>
		<div class="inputGroupContainer" style="">
			<div class="input-group">
				<span class="input-group-addon"><span class="glyphicon" aria-hidden="true" style="font-weight:bold; font-family:arial; min-width:10px; text-align:left;">'.$crypto->symbol.'</span></span>
				<input type="number" step="any" autocomplete="off" id="amount" value="" class="form-control control_to_validate loan_min_no_cur" placeholder="amount in '.$crypto->crypto_symbol.'" min="0" max="0" _validate_message="enter Amount of Loan between 0 '.$crypto->crypto_symbol.' and 0 '.$crypto->crypto_symbol.'">
				<span class="input-group-addon"><span class="glyphicon loan_glyphicon" aria-hidden="true" style="">'.$crypto->crypto_name.'s</span></span>
			</div>
		</div>
		'.show_help('Min. <span class="loan_min_cur">0</span> '.$crypto->crypto_symbol.', max. <span class="loan_max_cur">0</span> '.$crypto->crypto_symbol.'. This amount of '.$crypto->crypto_name.'s <span id="amount_in_dollars"></span> will be transferred to your '.$crypto->crypto_name.' account').'
	</div>
	<div class="col-md-6">
		<label class="control-label" style="">Interest Rate:</label>
		<div class="inputGroupContainer" style="">
			<div class="input-group">
				<input type="number" step="any" autocomplete="off" id="rate" value="" class="form-control control_to_validate" placeholder="percents" _validate_message="enter Interest Rate between 0% and 0%">
				<span class="input-group-addon"><span class="glyphicon loan_glyphicon" aria-hidden="true" style="">% per day</span></span>
			</div>
		</div>
		'.show_help('Min. <span class="rate_min">0</span>%, max. <span class="rate_max">0</span>%. This percent of the loan will be charged for the use of the borrowed '.$crypto->crypto_name.'s').'
	</div>
</div>
<div class="row loan_item_row">
	<div class="col-md-6">
		<label class="control-label" style="">Loan Term:</label>
		<div class="inputGroupContainer" style="">
			<div class="input-group">
				<select name="term" id="term" class="form-control" onchange="term_changed();">
					'.$terms_list.'
				</select>
				<span class="input-group-addon"><span class="glyphicon loan_glyphicon" aria-hidden="true" style="">days</span></span>
			</div>
		</div>
		'.show_help('<span class="term_in_weeks">2</span> weeks. The loan agreement will be in force during this period, and before this period the loan should be repaid').'
	</div>
	<div class="col-md-6">
		<label class="control-label" style="">Loan Assets:</label><br>
		<label class="control-label" style="margin-top:8px;"><a href="#" onclick="show_loan_assets(); return false;"><h2 style="display:inline;"><span id="total_shares">0</span> '.WORD_MEANING_SHARE.'s</a></h2>, cumulative worth <span id="total_shares_worth">'.DOLLAR_SIGN.'0.00</span></label>
		'.show_help('In case of failure of payment these '.WORD_MEANING_SHARE.'s will be transferred to the lender&apos;s ownership').'
	</div>
</div>
<div class="row loan_item_row">
	<label class="control-label" style="">Payment:</label><br>
	<h1 style="text-transform:none; display: inline;"><span id="installment">'.$crypto->symbol.'0.01</span> per week</h1><h1 style="text-transform:none; display: inline;">, for <span class="term_in_weeks">2</span> weeks</h1>
	<a href="#" onclick="show_amortization_schedule(); return false;">(amortization schedule)</a><br>
	(<span id="installment_in_dollars"></span> per week)
	'.show_help('Every week you have to make this payment. A failure of payment will force to transfer your '.WORD_MEANING_SHARE.'s to the ownership of lender').'
</div>
<p style="text-align:center;">
	<button class="btn btn-success btn-lg" style="" onclick="form_submitted(); return false;">
		<div style="position:relative; width:100%; height:0px; z-index:10000;">
			<div style="position:absolute; width:100%; height:0px; text-align:center;">
				<img src="/images/wait_big3.gif" border="0" style="width:20px; height:20px; display:none;" id="form_submitted_btn_wait">
			</div>
		</div>
		<span id="form_submitted_btn_text">Apply for the Loan...</span>
	</button>
</p>
'.generate_popup_code('loan_assets', '<div id="loan_assets_table_body" style="height:300px; overflow:auto;"></div>', 'loan_assets_save()', 'Loan Assets', '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Save', 'btn-link', '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Close', '', '', '', 'loan_assets_setup()').'
';

include(DIR_WS_INCLUDES.'box_wait.php');
require(DIR_WS_INCLUDES.'box_message.php');
require(DIR_WS_INCLUDES.'box_yes_no.php');
require(DIR_WS_INCLUDES.'footer.php');

?>
<script type="text/javascript">

var id_to_pulsate = "";
var installment = 0;
var loan_assets = [];
var total_shares = 0;
var total_shares_worth = 0;
var assets_min = 1;
var all_user_shares_worth = 0;

function term_changed()
{
	$(".term_in_weeks").html( $("#term").val() / <?php echo LOAN_PP_INTERVAL; ?> );
	calculate_payment();
}

function pulsate_item()
{
	$("#" + id_to_pulsate).effect("pulsate", { times:12 }, 15);
	setTimeout(function() { $("#" + id_to_pulsate).focus(); }, 100);
}
	
function form_submitted()
{
	var is_error = false;
	$(".control_to_validate").each(function () {
		if ( !is_error ) {
			var tmp_obj = document.getElementById( $(this).attr("id") );
			if ( !tmp_obj.checkValidity() ) {
				var validate_message = $(this).attr("_validate_message");
				if ( typeof validate_message == "undefined" || validate_message.length <= 0 )
					validate_message = "enter " + $(this).attr("id");
				id_to_pulsate = $(this).attr("id");
				show_message_box_box("Error", "Please " + validate_message, 2, "", "pulsate_item");
				is_error = true;
			}
		}
    });
	if ( !is_error ) {
		if ( total_shares_worth < assets_min * parseFloat($("#amount").val()) * <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?> ) {
			if (total_shares_worth == 0)
				show_message_box_box("Error", "Please select Loan Assets", 2, "", "show_loan_assets");
			else
				show_message_box_box("Error", "The worth of selected <?php echo WORD_MEANING_SHARE; ?>s is not enough to cover the loan amount. Please select more <?php echo WORD_MEANING_SHARE; ?>s or lower the amount, which you want to borrow.", 2, "", "show_loan_assets");
			is_error = true;
		}
	}
	if ( !is_error ) {
		show_box_yesno_box("Please confirm:<ol><li>You agree to pay the loan payouts.</li><li>You understand that all your payments must be made in <b><?php echo $crypto->crypto_name; ?>s</b>.</li><li>You understand that if you do not pay the loan payouts on time you <b>lose your <?php echo WORD_MEANING_SHARE; ?>s</b>.</li></ol>Click on <b>I agree</b> to continue:", "create_loan", "Confirm", "I agree", "No");
	}
	return false;
}

function goto_loans_list()
{
	location.assign("/acc_loans.php");
}

function create_loan()
{
	$("#form_submitted_btn_text").css("opacity", 0);
	$("#form_submitted_btn_wait").show();

	tmp_loan_assets = [];
	for (var i = 0; i < loan_assets.length; i++){
		tmp_loan_assets.push([loan_assets[i][0].substring(8), loan_assets[i][1]]);
	}
	var loan_assets_str = JSON.stringify(tmp_loan_assets);
	try {
		$.ajax({
			method: "POST",
			url: "/api/user_create_loan",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", amount_of_loan: $("#amount").val(), term_in_days: $("#term").val(), rate_per_day: $("#rate").val() / 100, loan_assets: string_to_hex32(loan_assets_str) }
		})
		.done(function( ajax__result ) {
			try
			{
				$("#form_submitted_btn_text").css("opacity", 255);
				$("#form_submitted_btn_wait").hide();

				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					show_message_box_box("Success", "Your enquiry to borrow money has been accepted", 1, "", "goto_loans_list");
				}
				else {
					show_message_box_box("Error", arr_ajax__result["message"], 2);
				}
			}
			catch(error){console.error(ajax__result + " create_loan " + error);}
		});
	}
	catch(error){console.error("create_loan: " + error);}
}

function calculate_payment()
{
	var n = $("#term").val() / <?php echo LOAN_PP_INTERVAL; ?>; // number of weeks
	var i = $("#rate").val() / 100 * <?php echo LOAN_PP_INTERVAL; ?>; // per week
	//var P = $("#amount").val() / (Math.pow(1 + i, n) - 1 ) * (i * Math.pow(1 + i, n));
	installment = $("#amount").val() * (i / (1 - Math.pow(1 + i, - n)));
	$("#installment").html( currency_format(installment, "<?php echo $crypto->symbol; ?>") );
	$("#amount_in_dollars").html( "(about " + currency_format($("#amount").val() * <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?>, "<?php echo DOLLAR_SIGN; ?>") + " <?php echo DOLLAR_NAME; ?>)" );
	$("#installment_in_dollars").html( "about " + currency_format(installment * <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?>, "<?php echo DOLLAR_SIGN; ?>") + " <?php echo DOLLAR_NAME; ?>" );
}

function show_amortization_schedule()
{
	var message = "";
	var balance = $("#amount").val();
	var total_tnterest = 0;
	var week = 1;
	for (var i = 0; i < $("#term").val() / <?php echo LOAN_PP_INTERVAL; ?>; i++) {
		var interest = balance * parseFloat($("#rate").val()) / 100 * <?php echo LOAN_PP_INTERVAL; ?>;
		var principal = installment - interest;
		total_tnterest = total_tnterest + interest;
		balance = balance - principal;
		if (balance < 0)
			balance = 0;
		message = message + "<tr><td><div class=row><div class=col-md-4>" + week + "</div><div class=col-md-4>" + $("#installment").html() + "</div><div class=col-md-4>" + currency_format(principal, "<?php echo $crypto->symbol; ?>") + "</div></div></td><td><div class=row><div class=col-md-4>" + currency_format(interest, "<?php echo $crypto->symbol; ?>") + "</div><div class=col-md-4>" + currency_format(total_tnterest, "<?php echo $crypto->symbol; ?>") + "</div><div class=col-md-4>" + currency_format(balance, "<?php echo $crypto->symbol; ?>") + "</div></div></td></tr>";
		week++;
	}
	message = "<table class=table style='text-align:right;'><tr><td><div class=row><div class=col-md-4>Week</div><div class=col-md-4>Payment</div><div class=col-md-4>Principal</div></div></td><td><div class=row><div class=col-md-4>Interest</div><div class=col-md-4>Total Interest</div><div class=col-md-4>Balance</div></div></td></tr>" + message + "</table>";
	show_message_box_box("Amortization Schedule", message);
}

function loan_assets_setup()
{
	for (var i = 0; i < loan_assets.length; i++) {
		$("#" + loan_assets[i][0]).val( loan_assets[i][1] );
	}
}

function loan_assets_save()
{
	loan_assets = [];
	total_shares = 0;
	total_shares_worth = 0;
	$(".asset_share").each(function () {
		if ($(this).val() > $(this).attr("max"))
			$(this).val( $(this).attr("max") );
		loan_assets.push( [$(this).attr("id"), $(this).val()] );
		total_shares = total_shares + parseInt($(this).val());
		$("#total_shares").html(total_shares);
		total_shares_worth = total_shares_worth + parseFloat($(this).attr("price")) * parseInt($(this).val());
		$("#total_shares_worth").html( currency_format(total_shares_worth) );
    });
}

$( document ).ready(function() {
	try {
		$.ajax({
			method: "POST",
			url: "/api/user_get_loan_vars",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>" }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					assets_min = arr_ajax__result["values"]["assets_min"];
					$(".loan_min_no_cur").val( currency_format(arr_ajax__result["values"]["loan_min"], "~") );
					$(".loan_min_cur").html( currency_format(arr_ajax__result["values"]["loan_min"], "<?php echo $crypto->symbol; ?>") );
					$(".loan_max_cur").html( currency_format(arr_ajax__result["values"]["loan_max"], "<?php echo $crypto->symbol; ?>") );

					$("#amount").attr( "min", arr_ajax__result["values"]["loan_min"] );
					$("#amount").attr( "max", arr_ajax__result["values"]["loan_max"] );
					$("#amount").attr("_validate_message", "enter Amount of Loan between "+currency_format(arr_ajax__result["values"]["loan_min"], "<?php echo $crypto->symbol; ?>")+" <?php echo $crypto->crypto_symbol; ?> and "+currency_format(arr_ajax__result["values"]["loan_max"], "<?php echo $crypto->symbol; ?>")+" <?php echo $crypto->crypto_symbol; ?>");

					
					arr_ajax__result["values"]["rate_min"] = arr_ajax__result["values"]["rate_min"] * 100;
					arr_ajax__result["values"]["rate_max"] = arr_ajax__result["values"]["rate_max"] * 100;

					$("#rate").val( Math.round(arr_ajax__result["values"]["rate_min"] * 2) / 1 );
					$(".rate_min").html( Math.round(arr_ajax__result["values"]["rate_min"] * 10) / 10 );
					$(".rate_max").html( Math.round(arr_ajax__result["values"]["rate_max"] * 10) / 10 );
					$("#rate").attr( "min", arr_ajax__result["values"]["rate_min"] );
					$("#rate").attr( "max", arr_ajax__result["values"]["rate_max"] );
					$("#rate").attr("_validate_message", "enter Interest Rate between "+Math.round(arr_ajax__result["values"]["rate_min"] * 10) / 10+"% and "+Math.round(arr_ajax__result["values"]["rate_max"] * 10) / 10+"%");

					$(".control_to_validate").on("click mouseenter mouseleave mousedown mouseover active keyup ", function (e) {
						calculate_payment();	
					});
					calculate_payment();
				}
			}
			catch(error){console.error(ajax__result + " user_get_loan_vars " + error);}
		});
	}
	catch(error){console_log("user_get_loan_vars: " + error);}

	try {
		$.ajax({
			method: "POST",
			url: "/api/get_sorted_table",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", table_name: "user_my_shares", sort_column: "1", sort_order: "ASC", row_number: "0", current_page_number: "0", max_ros: "20" }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					var table_body = "";
					all_user_shares_worth = 0;
					for (var i = 0; i < arr_ajax__result["values"]["table"].length; i++) {
						table_body = table_body + "<tr><td><div class=row><div class=col-sm-6><a href=/stocks/" + arr_ajax__result["values"]["table"][i]["c_stockid"] + "><span class=notranslate>" + arr_ajax__result["values"]["table"][i]["c_name"] + " (" + arr_ajax__result["values"]["table"][i]["c_stockid"] + ")</span></a></div><div class=col-sm-3>" + currency_format(arr_ajax__result["values"]["table"][i]["c_price"]) + "</div><div class=col-sm-3><input type=number step=any autocomplete=off id=to_lean_" + arr_ajax__result["values"]["table"][i]["c_stockid"] + " value=" + arr_ajax__result["values"]["table"][i]["c_quantity"] + " class='form-control asset_share' min=0 max=" + arr_ajax__result["values"]["table"][i]["c_quantity"] + " price=" + arr_ajax__result["values"]["table"][i]["c_price"] + "></div></div></td></tr>";
						all_user_shares_worth = all_user_shares_worth + arr_ajax__result["values"]["table"][i]["c_quantity"] * arr_ajax__result["values"]["table"][i]["c_price"];
					}
					var borrow_amount = all_user_shares_worth / <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?> * 0.5;
					if ( borrow_amount > 100 / <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?> )
						borrow_amount = 100 / <?php echo $crypto->get_exchange_rate(DOLLAR_NAME); ?>;
					borrow_amount = Math.round(borrow_amount * <?php echo pow(10, $crypto->digits - 1); ?>) / <?php echo pow(10, $crypto->digits - 1); ?>;
					if ( parseFloat($("#amount").attr( "min" )) > 0 && borrow_amount < parseFloat($("#amount").attr( "min" )) )
						borrow_amount = parseFloat($("#amount").attr( "min" ));
					if ( parseFloat($("#amount").attr( "max" )) > 0 && borrow_amount > parseFloat($("#amount").attr( "max" )) )
						borrow_amount = parseFloat($("#amount").attr( "max" ));
					$("#amount").val( borrow_amount );
				
					table_body = "<table class='table table-striped'>" + table_body + "</table>";
					$("#loan_assets_table_body").html(table_body);
				}
			}
			catch(error){console.error(ajax__result + " user_get_loan_vars " + error);}
		});
	}
	catch(error){console_log("user_get_loan_vars: " + error);}
});
</script>
</body>
</html>


