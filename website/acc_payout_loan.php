<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_INCLUDES.'box_payment_method.php');

$show_first_page = true;
$box_message = '';
$crypto = new Bitcoin();

$display_in_short = !empty($_GET['userid']);
if ( !empty($_GET['userid']) ) {
	$tmp_user = new User();
	$tmp_user->userid = (int)$_GET['userid'];
	$tmp_user->read_data(false);
}
else
	$tmp_user = $user_account;

if ( !empty($_POST['form_submitted']) ) {
	$make_post_request = true;
	//if ( defined('DEBUG_MODE') ) { var_dump($_POST); exit; }
	$currency = DOLLAR_NAME;
	if ( $_POST['payment_method'] == 'balance' && (strtolower($_POST['from_balance_pay_currency']) == 'btc' || strtolower($_POST['from_balance_pay_currency']) == 'ltc') ) {
		$crypto = Pay_processor::get_crypto_currency_by_name($_POST['from_balance_pay_currency']);
		$_POST['amount'] = $_POST['amount'] / $crypto->get_exchange_rate(DOLLAR_NAME);
		$currency = $_POST['from_balance_pay_currency'];
	}
	else {
		if ($_POST['amount'] < 1)
			$_POST['amount'] = 1;
	}
	$req = $user_account->get_request_to_add_funds($_POST['amount'], $_POST['payment_method'], 'Pay out loan', $make_post_request, PAY_LOAN_PREFIX, '', $currency, '');
	if ( is_integer(strpos($req, 'Error: ')) ) {
		$box_message = $req;
	}
	else {
		if ( !$make_post_request )
			header('Location: '.$req);
		else {
			//if ( defined('DEBUG_MODE') ) { echo str_replace('<', '[', $req); exit; }
			echo $req;
		}
		exit();
	}
}

if ( !empty($_POST['form_p2p_submitted']) ) {
	$pay_email = '';
	$make_post_request = true;
	$p2p_loan_payment = $user_account->calculate_loan_payment($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days, $transactions_global_transactionid, $current_payment);
	if ($_POST['amount'] < $current_payment) {
		$box_message = 'Error: please enter amount of installment between '.$current_payment.' '.$crypto->crypto_symbol.' and '.($current_payment < $amount_of_loan?$amount_of_loan:$current_payment * 2).' '.$crypto->crypto_symbol;
	}
	if (empty($box_message)) {
		$req = $user_account->get_request_to_add_funds($_POST['amount'], $_POST['payment_method'], P2P_INSTALLMENT_PREFIX.'_txid='.$_POST['transactionid'], $make_post_request, P2P_INSTALLMENT_PREFIX, '', $crypto->crypto_symbol, $crypto->symbol);
		if ( is_integer(strpos($req, 'Error: ')) ) {
			$box_message = $req;
		}
		else {
			if ( !$make_post_request )
				header('Location: '.$req);
			else
				echo $req;
			exit();
		}
	}
}

$page_header = 'Pay out loan';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = $page_header;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');


if ( $tmp_user->disabled || $tmp_user->deleted ) {
	echo '<br><br><br><h3>This option is not available.</h3><br><br><br><br>';
}
else {
	$p2p_loan_payment = $tmp_user->calculate_loan_payment($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days, $transactions_global_transactionid, $current_payment);
	if ( $p2p_loan_payment ) {
		$has_p2p_loan_past_due_days = $tmp_user->has_p2p_loan_past_due_days($next_unix_time_to_pay, $transactions_global_transactionid, $created_unix_timestamp);
		echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'loans.png', '
			You have loan '.currency_format($amount_of_loan, '', '', '', false, false, $crypto->symbol, $crypto->digits).' from '.date("M j Y", $created_unix_timestamp).', which is must be payed out by '.round($term_in_days / $period_in_days).' installments, '.currency_format($p2p_loan_payment, '', '', '', false, false, $crypto->symbol, $crypto->digits).' each.<br>
			Your ongoing installment <b>'.currency_format($current_payment, '', '', '', false, false, $crypto->symbol, $crypto->digits).'</b> is due on '.date("M j Y", $next_unix_time_to_pay).'.<br>
			If this loan installment is not made by the due date <b title="due days: '.$has_p2p_loan_past_due_days.'">'.date("M j Y", $next_unix_time_to_pay).'</b>, all your shares, secured by this loan, are going to the ownership of lender.<br>
			When you sign up for a loan, you are obligated to pay all installments when due. Also please notice, failure of loan payments may lower your credit rating.<br>'.($has_p2p_loan_past_due_days?'<b id="debt_alert">You have '.$has_p2p_loan_past_due_days.' past due '.show_plural($has_p2p_loan_past_due_days, 'day').'. Please pay out your debt immediately!!!</b>':''), $has_p2p_loan_past_due_days?'alert-danger':'alert-warning').'
		<style type="text/css">
		@media (max-width: 991px) {
			.loan_glyphicon{min-width:70px;}
			.input-group{min-width:250px;}
		}
		@media (min-width: 992px) {
			.loan_glyphicon{min-width:100px;}
			.input-group{min-width:300px;}
		}
		.input-group{width: 80%; max-width:320px;}
		.loan_glyphicon{font-weight:bold; font-family:arial; text-align:left;}
		</style>
		<script type="text/javascript">
		var id_to_pulsate = "";
		function pulsate_item()
		{
			$("#" + id_to_pulsate).effect("pulsate", { times:12 }, 15);
			setTimeout(function() { $("#" + id_to_pulsate).focus(); }, 100);
		}

		function pay_installment_submitted()
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
			return !is_error;
		}
		'.($has_p2p_loan_past_due_days?'id_to_pulsate = "debt_alert"; pulsate_item();':'').'
		</script>
		'.(!$display_in_short?
			'<form method="post" name="user_frm" onsubmit="return pay_installment_submitted();">
			<input type="hidden" name="form_p2p_submitted" value="1">
			<input type="hidden" name="payment_method" value="'.$crypto->crypto_name.'">
			<input type="hidden" name="transactionid" value="'.$transactions_global_transactionid.'">
			<div class="row" style="margin-top:30px;">
				<label class="control-label col-md-4" style="text-align:right; margin-top:8px;">Minimum payment:</label>
				<div class="col-md-8">
					<div class="inputGroupContainer" style="">
						<div class="input-group">
							<span class="input-group-addon"><span class="glyphicon loan_glyphicon" aria-hidden="true" style="min-width:10px; text-align:left;">'.$crypto->symbol.'</span></span>
							<input type="number" step="any" autocomplete="off" id="amount" name="amount" value="'.number_format($current_payment, $crypto->digits, '.', '').'" class="form-control control_to_validate loan_min_no_cur" placeholder="amount in '.$crypto->crypto_symbol.'" min="'.$current_payment.'" max="'.($current_payment < $amount_of_loan?$amount_of_loan:$current_payment * 2).'" _validate_message="enter amount of installment between '.$current_payment.' '.$crypto->crypto_symbol.' and '.($current_payment < $amount_of_loan?$amount_of_loan:$current_payment * 2).' '.$crypto->crypto_symbol.'">
							<span class="input-group-addon"><span class="glyphicon loan_glyphicon" aria-hidden="true" style="">'.$crypto->crypto_name.'s</span></span>
						</div>
					</div>
				</div>
			</div>
			<div style="text-align:center; margin-top:30px;"><button name="submit_btn" class="btn-lg btn-primary" style="margin-bottom:20px;">Continue...</button></div>
			</form>
			':''
		).'
		<h2>Amortization Schedule</h2>
		<div id="amortization_schedule_table_body"></div>
		';
		?>
		<script type="text/javascript">
		$( document ).ready(function() {
			var message = "";
			var balance = <?php echo $amount_of_loan ?>;
			var total_tnterest = 0;
			var week = 1;
			var installment = <?php echo $p2p_loan_payment; ?>;
			var date_of_loan = new Date(<?php echo $created_unix_timestamp; ?> * 1000);
			
			for (var i = 0; i < <?php echo ($term_in_days / $period_in_days); ?>; i++) {
				var interest = balance * <?php echo $rate_per_day; ?> * <?php echo $period_in_days; ?>;
				var principal = installment - interest;
				total_tnterest = total_tnterest + interest;
				balance = balance - principal;
				if (balance < 0)
					balance = 0;
				date_of_loan.setDate(date_of_loan.getDate() + <?php echo LOAN_PP_INTERVAL; ?>);
				message = message + "<tr id=amortization_schedule_tr_" + i + "><td><div class=row><div class=col-md-2 id=amortization_schedule_label_" + i + "></div><div class=col-md-2>" + leading_zero(date_of_loan.getDate(), 2) + "/" + leading_zero((date_of_loan.getMonth()+1), 2) + "/" + date_of_loan.getFullYear() + "</div><div class=col-md-4>" + currency_format(installment, "<?php echo $crypto->symbol; ?>", undefined, undefined, undefined, <?php echo $crypto->digits; ?>) + "</div><div class=col-md-4>" + currency_format(principal, "<?php echo $crypto->symbol; ?>", undefined, undefined, undefined, <?php echo $crypto->digits; ?>) + "</div></div></td><td><div class=row><div class=col-md-4>" + currency_format(interest, "<?php echo $crypto->symbol; ?>", undefined, undefined, undefined, <?php echo $crypto->digits; ?>) + "</div><div class=col-md-4>" + currency_format(total_tnterest, "<?php echo $crypto->symbol; ?>", undefined, undefined, undefined, <?php echo $crypto->digits; ?>) + "</div><div class=col-md-4>" + currency_format(balance, "<?php echo $crypto->symbol; ?>", undefined, undefined, undefined, <?php echo $crypto->digits; ?>) + "</div></div></td></tr>";
				week++;
			}
			message = "<table class='table table-striped' style='text-align:right;'><tr><td><div class=row><div class=col-md-2></div><div class=col-md-2>Due Date</div><div class=col-md-4>Payment</div><div class=col-md-4>Principal</div></div></td><td><div class=row><div class=col-md-4>Interest</div><div class=col-md-4>Total Interest</div><div class=col-md-4>Balance</div></div></td></tr>" + message + "</table>";
			$("#amortization_schedule_table_body").html(message);

			try {
				$.ajax({
					method: "POST",
					url: "/api/get_sorted_table",
					data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", table_name: "loans", sort_column: "8", sort_order: "ASC", row_number: "0", current_page_number: "0", max_ros: "500", installments_from_user: "<?php echo $tmp_user->userid; ?>", transactions_global_transactionid: "<?php echo $transactions_global_transactionid; ?>" }
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
		});
		</script>

		<?php
	}
	else {
		$unpaid_loan = $tmp_user->have_unpaid_loan();
		if ( $unpaid_loan > 0 ) {
			echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'sign_error.png', 'You have to pay out the loan in full because if you have unpaid debts you are not able to withdraw your balance.', 'alert-danger').'
			<form method="post" name="user_frm">
			<input type="hidden" name="form_submitted" value="1">
			<input type="hidden" name="amount" value="'.$unpaid_loan.'">
			<p style="font-size:16px; margin-top:30px; margin-bottom:30px; font-weight:bold;">Pay out your debt: <span style="font-size:26px">'.currency_format($unpaid_loan).'</span></p>';
			?>
			<h3>Payment Method:</h3>
			<?php 
			$box_payment_method_no_discount = true;
			$box_payment_solid_cur_only = true;
			echo get_table_of_payment_methods($unpaid_loan, $tmp_user, true, true, false, '', 1, 1, true).'
			<hr>
			<div align="center"><button name="submit_btn" class="btn-lg btn-primary" style="margin-bottom:20px;" onclick="return purchase_pressed();">Continue...</button></div>
			</form>
			<SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
			<SCRIPT language="JavaScript"> 
			var frmvalidator = new Validator("user_frm", "submit_btn");
			
			function purchase_pressed()
			{
				var payment_method = "";
				$(".payment_method_radio").each(function( index ) {
					if ($(this).is(":checked"))
						payment_method = $(this).val();
				});
				if (payment_method == "balance") {
					show_select_from_balance_pay_currency();
					return false;
				}
				return true;
			}

			function on_from_balance_pay_currency_selected()
			{
				document.user_frm.submit();
				return true;
			}

			</script>
			';
		}
		else
			echo '<p style="font-size:16px; margin-top:30px; margin-bottom:30px; font-weight:bold;">You have no loan.</p>';
	}
}
include(DIR_COMMON_PHP.'box_wait.php');
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<script language="JavaScript">show_message_box_box("Error", "'.$box_message.'", 2);</script>'."\r\n";
	else
		echo '<script language="JavaScript">show_message_box_box("Success", "'.$box_message.'", 1);</script>'."\r\n";
}
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>


</body>
</html>
