<?php
define('DO_NOT_REDIRECT_APPLICATION', 1);
require('../includes/application_top.php');

if (defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true' && empty(@$_COOKIE['googtrans'])) {
	// translate page according the coutry code
	$country_code = getCountryCodefromIP();
	$_COOKIE['googtrans'] = '/auto/'.strtolower($country_code); 
	setcookie('googtrans', $_COOKIE['googtrans']);
}

require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$showDebugMessages = defined('DEBUG_MODE');
if ( $showDebugMessages )
	error_reporting(E_ALL);
require(DIR_WS_INCLUDES.'account_common_top.php');

//if ( !$showDebugMessages )
//	sleep(3);

$_GET['inv'] = easy_descramble($_GET['inv']);

$page_title = 'Thank you for your payment';
$page_header = $page_title;
$show_top_images = false;
require(DIR_WS_INCLUDES.'header.php');

//define('DEBUG_MODE', '1');
//$crypto = Pay_processor::get_crypto_currency_by_name('BTC');
//if ($crypto) {
//	$exchange_rate = $crypto->get_exchange_rate(DOLLAR_NAME, false, 2);
//}
?>
<svg version="1.1"
	 xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"  width="800px" height="800px"
	 viewBox="0 0 1200 1200" enable-background="new 0 0 1200 1200" xml:space="preserve"
	 style="width: 5em; height: 5em; margin: 1em auto; display: block;"
	 >
<path style="stroke: rgb(66, 208, 122); fill: rgb(46, 216, 122);" inkscape:connector-curvature="0" d="M1004.237,99.152l-611.44,611.441L194.492,512.288L0,706.779
	l198.305,198.306l195.762,195.763L588.56,906.355L1200,294.916L1004.237,99.152z"/>
</svg>
<?php
$row_pay_attempt = get_api_value('user_get_last_purchase', '', array('invoice' => $_GET['inv']));
if ( is_integer(strpos($_GET['inv'], P2P_LEND_PREFIX)) ) {
	$crypto = new Bitcoin();
	if ( $row_pay_attempt ) {
		parse_str($row_pay_attempt['note'], $arr1);
		$transactions_global_transactionid = $arr1[P2P_LEND_PREFIX.'_txid'];
		$loan_payment = $user_account->calculate_loan_payment($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days, $transactions_global_transactionid, $current_payment);
		$part_of_loan_payment_which_goes_to_lender = $user_account->get_part_of_loan_payment_which_goes_to_lender($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days);
		echo '
			<p style="margin-top:20px;">Your money have been transferred to borrower. Every <b>'.$period_in_days.'</b> days the borrower should pay you interest and part of principal in amount of <b>'.currency_format($part_of_loan_payment_which_goes_to_lender, '', '', '', false, false, $crypto->symbol, $crypto->digits).'</b>.</p>
			<p>You should receive <b>'.round($term_in_days / $period_in_days).'</b> payments total worth <b>'.currency_format($part_of_loan_payment_which_goes_to_lender * $term_in_days / $period_in_days, '', '', '', false, false, $crypto->symbol, $crypto->digits).'</b>.</p>
			<p>In case of default (the borrower does not pay installment) all secured '.WORD_MEANING_SHARE.'s will be automatically transferred to the ownership of yours.</p>
			';
	}
	else
		echo '
			<p>Every '.LOAN_PP_INTERVAL.' days you will be paid interest and part of principal.</p>
			<p>You should receive all the installments from borrower</p>
			<p>In case of default (the borrower does not pay installment) all secured '.WORD_MEANING_SHARE.'s will be automatically transferred to the ownership of yours.</p>
			';
}
else
if ( is_integer(strpos($_GET['inv'], P2P_INSTALLMENT_PREFIX)) ) {
	echo '
	<p>Your lender received your installment.</p>
	';
}
else
if ( is_integer(strpos($_GET['inv'], DEPOSIT_CREATE_PREFIX)) ) {
	echo '
	<p>You made a term deposit. Interest and part of principal are paid on weekly basis. Payments are made automatically, no need for the withdrawal requests.</p>
	<p>You can see all your deposits at the <a href="/acc_deposits.php">Deposits webpage</a>.</p>
	';
}
else
if ( is_integer(strpos($_GET['inv'], CURRENCY_EXCHANGE_SELL_PREFIX)) ) {
	parse_str($row_pay_attempt['note'], $term_deposit_data);
	$crypto = Pay_processor::get_crypto_currency_by_name($row_pay_attempt['currency']);
	if ( !$crypto )
		$crypto = new Bitcoin();
	
	echo '
	<p>You made exchange of <strong>'.currency_format($row_pay_attempt['received'], '', '', '', false, false, $crypto->symbol, $crypto->digits).'</strong> with exchange rate <strong>'.currency_format($term_deposit_data['exc_rate']).'</strong> per '.$crypto->crypto_name.'. Your account has been credited for <strong>'.currency_format($term_deposit_data['exc_rate'] * $row_pay_attempt['received']).'</strong>.</p>
	<p>Please withdraw these funds using your <b>Paypal</b> account.</p>
	';
}
else {
	echo translate_str('
	<p class="string_to_translate">We have received your payment and during next short time you will see the purchased item onto your account.</p>
	<p class="string_to_translate">Please be patient because the procedure of processing your order takes time.</p>
	');
}
?>
<br>
<?php 
require(DIR_WS_INCLUDES.'footer.php');
echo '
<script language="JavaScript">
$(document).ready(function(){	
	$.ajax({
		method: "POST",
		url: "/api/user_change_rank_according_to_activity_points",
		data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'" }
	})
	.done(function( ajax__result ) {});
});
</script>
';
?>

<script type="text/javascript">

var global_language = "";
var language_already_changed = false;
var number_of_attempts = 0;
var max_number_of_attempts = 20;

function change_language_automatically() {
	try	{
		var selectField = document.querySelector("#google_translate_element select");
		if (!selectField || !selectField.children || selectField.children.length < 1 || selectField.offsetParent == null) {
			throw "empty list";
		}
		var google_language = selectField.value;
		var already_selected_language = get_cookie("googtrans");
		var language_found = false;
		if ((already_selected_language.length == 0 || number_of_attempts > max_number_of_attempts - 2) && google_language.length == 0) {
			for (var i = 0; i < selectField.children.length; i++) {
				var option = selectField.children[i];
				// find desired langauge and change the former language of the hidden selection-field 
				if ( option.value == global_language ) {
					selectField.selectedIndex = i;
					// trigger change event afterwards to make google-lib translate this side
					selectField.dispatchEvent(new Event('change'));
					language_found = true;
					break;
				}
			}
		}
		if (!language_found && number_of_attempts < max_number_of_attempts && google_language.length == 0) {
			number_of_attempts++;
			throw "not found";
		}
	}
	catch(error){
		setTimeout(function() { 
			change_language_automatically();
		}, 200);
	}
}

$( document ).ready(function() {
	<?php if ( defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY  == 'true' ) { ?>
		
		try	{
			var language = window.navigator.userLanguage || window.navigator.language;
			if (typeof language == "string" && language.length > 0) {
				global_language = language.substring(0, language.indexOf("-") >= 0 ? language.indexOf("-") : language.length );
				if ( global_language.length > 0 && global_language != "en" )
					change_language_automatically();
			}
		}
		catch(error){}
	<?php } ?>
});

</script>

</body>
</html>