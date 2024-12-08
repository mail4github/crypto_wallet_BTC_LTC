<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_INCLUDES.'box_protected_page.php');

function get_global_constants()
{
	$data = make_api_request('admin_get_global_constants', '', $_POST);
	if ( $data ) {
		$s = '';
		foreach($data['values'] as $key => $value)
			$s = $s."$key=$value\r\n";
		if (!empty($s)) {
			$res = file_put_contents(DIR_DATA.'$$$settings.ini', $s);
			if ($res === false) {
				return 'Error: Cannot write to file';
			}
		}
		else {
			return 'Error: Data is empty';
		}
	}
	else {
		return 'Error: no answer from server';
	}
	return '';
}

if ( $_POST['form_submitted'] == '1' ) {
	$box_message = '';
	if ( !$user_account->verify_password_from_box_password('hashed_password') )
		$box_message = 'Error: Please enter correct password.';

	if (empty($box_message)) {
		foreach($_POST as $key => $value) {
			$_POST[$key] = encrypt_decrypt("encrypt", $value, 'jhjfg87465jhfb1`.-'.date('Y-m-d'));
		}

		$res = make_api_request('admin_save_consts', '', $_POST);
		
		$res = get_global_constants();
		if (empty($res)) {
			echo '
			<script language="JavaScript">
				document.addEventListener("DOMContentLoaded", function(event) { 
					show_wait_box_box("", "Please wait...");
					setTimeout(function() { document.location.replace("'.$_SERVER['REQUEST_URI'].'"); }, 1000);
				});
			</script>';
		}
		else {
			$box_message = $res;
		}
	}
}
else {
	get_global_constants();
}
$page_header = 'Website Variables';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

if ( !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}

$vars = '
'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
MANAGERS_BITCOIN_ACCOUNT|Bitcoin cold wallet|text|To this account extra bitcoins will be sent|
MANAGERS_LITECOIN_ACCOUNT|Litecoin cold wallet|text|To this account extra Litecoins will be sent|
BANKCARD_DISABLED|Bankcard Disabled|list|||false=NO~true=YES
PAYPAL_DISABLED|Paypal Disabled|list|||false=NO~true=YES
ORDER_SITE|Get Paypal email for exchange from||
CAN_BUY_FROM_BALANCE|Purchases from balance are allowed|list|||false=NO~true=YES
PART_OF_TRANSACTION_GOES_TO_BROCKER|Admin&#39;s margin|percent|Used as: penalty for early closure of deposit, as margin in exchange
' : '').'
FREEKASSA_MERCHANT_ID|
FREEKASSA_SECRET_WORD_1|
FREEKASSA_SECRET_WORD_2|||(not used)
FREEKASSA_COMMISSION|||(not used)
'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
:Withdraw
WITHDRAWAL_DISABLED|All widthrawals are disabled|list|||false=NO~true=YES
DISABLED_WITHDRAWAL_ON_A_MENACE||list|||true=YES~false=NO
COLLECT_FUNDS_ON_WEBSITE_FOR_DAYS|Collect reserves for|number||days||2
HACKER_COUNTRIES|No widthrawals for users from countries|textarea|
#REMAINDER_FOR_NEWBIES|Pay to manager after remainder exceeds|currency|This amount reserved for newbies&#39; payments. Payments to manager&#39;s account will start when minimum remainder plus this amount exceeds accumulated amount|||50
#MINIMUM_WITHDRAW=2.00
#MAXIMUM_WITHDRAW|PM max. withdraw|currency|Effects to the PM withdrawals only|
BITCOIN_MAXIMUM_WITHDRAW||currency||||600
LITECOIN_MAXIMUM_WITHDRAW||currency||||600
MAKE_AUTO_PAYOUT_AFTER_DAYS|Cancel not processed payouts after|number||days
#MAXIMUM_PAYOUT=12.00
WITHDRAWAL_FEE||percent
WITHDRAWAL_FEE_CONST|Constant fee for Newbies|currency||
PAYOUTS_VS_PURCHASES|Block withdrawals when payouts higher than purchases in|number||times
ALLOW_OVERLAPPING_WITHDRAWAL|Allow send funds simultaneously|list|||false=NO~true=YES
MAX_CASHOUT_SCALE|Multiply max. withdrawal for every rank to|number||||1
MIN_CASHOUT_SCALE|Multiply min. withdrawal for every rank to|number||||1
RESEND_CRYPTO_PAYOUTS_AFTER_MINUTES|Re-send payout after|number|If a payout has been not processed during this time it will be sent again. After double of this time, it will be declined|minutes||20
PRIORITY_FEE_FAST|Urgent Delivery expensive than Fast Delivery in|number||times||1.5
PROCESS_FREE_CRYPTO_PAYOUTS_AFTER_MINUTES|Process &quot;Slow Delivery&quot; after|number||minutes||120
WITHDRAW_INTERVAL_IN_MINUTES|Multiply &#39;cashout&#39; interval for each rank by|number||times||10
' : '').'
'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
:Loans and Deposits
#LOAN_INTEREST|Loan fee per day|percent||
#LOAN_PP_INTERVAL|Interval between installments for P2P loan|number||days
#LOAN_PP_STAY_LISTED|A request for P2P loan is active for|number||days
#DEPOSIT_INTEREST_IS_CORRELATED_WITH_SALES|If no history of deposits, interest is correlated with sales trend on|percent
DEPOSIT_MIN_INTEREST|Minimum Term Deposit interest|percent|If 0 that means no deposits if negative interest.|||0.03
DEPOSIT_MAX_INTEREST|Maximum Term Deposit interest|percent|If 0 that means no maximum interest.|||2.01
DEPOSIT_BALANCE_MULTIPLIC|Increase rate if receiving to balance|number||times||1
' : '').'
'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
:Exchange
HOLD_NOT_SOLID_PARCHASES_DAYS|Not instant exchanges will be pending for|number||days
CURRENCY_EXCHANGE_SELL_MIN|Minimum sell|currency||||40
CURRENCY_EXCHANGE_SELL_MAX|Maximum sell|currency||||1000
CURRENCY_EXCHANGE_BUY_MIN|Minimum buy|currency||||20
CURRENCY_EXCHANGE_BUY_MAX|Maximum buy|currency||||100
EXCHANGE_RESERVE_MULTIPLICATOR|Reserve funds for|number|If the reserves of a currency, divided by the max.exchange amount, are less than this number the exchange rate will be increased to stimulate users to sell this currency|exchanges||10
EXCHANGE_SCALES_THRESHOLD|Exchange margin leverage|number|Exchange margin could not be higher than this value multiplied by <b>Admin&#39;s margin</b>|times||4
CURRENCY_EXCHANGE_MUST_BE_DONE_IN_MINS|Exchange must be completed in|number||minutes||10
' : '').'

:Affilates
AFFILIATE_COMMISSION||percent|
AFFILIATE_REDUCE_COMMISSION_AFTER_DAYS|Reduce commission from old referrals. Devide it by 2 each|number|If this value is blank or 0 the commission not changes|days
AFFILIATE_ALLOW_COMMISSION_FROM_RELATIVES|Allow commission from relatives|list|||true=YES~false=NO
COST_OF_VISITOR|Reward for visitor|currency||
MAXIMUM_GAIN_FOR_CLICKS|Maximum gain for visitors|currency||
HOLD_REWARD_FOR_CLICKS_DAYS|Hold reward for visitors|number||days
CONTEST_1PLACE|Revard for 1st place in the Referrals contest|currency||
REWARD_AFFILIATE_AMOUNT|Revard new affiliate who enter&#39;s owners bitcoin address|currency|Recruiter also receives the same amount|||0.5
REWARD_RECRUITER_WITH_REWARD_AFFILIATE_AMOUNT|Reward recruiter for new affiliate|list|The reward amount is the same as the revard for new affiliate. It will be on hold for the same time like reward for visitors||true=YES~false=NO
ON_SIGNUP_REWARD|Revard new user for signup|currency||

'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
:Bonus Points
ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS|count only purchases for last|number||days
ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_IF_MORE|count crypto-purchases if more|currency|
ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_FOR_DAYS|count only crypto-purchases for last|number||days
MAX_RANK_CALCULATED_BY_ACTIVITY_POINTS|Maximum rank based on points|||||3
' : '').'
:Currency
DOLLAR_NAME|Primary currency|
DOLLAR_SIGN|Currency symbol|unicode
DOLLAR_SIGN_POSITION|Symbol position|list|||left=at left side (like: &pound;0.00)~right=at right side (like: 0.00&#8381;)
DOLLAR_DECIMALS|Decimals in primary currency|number
DECIMALS_IN_ALL_FLOAT|Decimals in all other currencies|number

:Feedback
ENGLISH_LANGUAGE_ONLY|Force users to use English|list|||true=YES~false=NO
AUTO_ANSWER_TICKET_AFTER_HOURS|Auto answer ticket after|number||hours

:Interface
GOOGLE_ANALYTICS_CODE|Analytics code|textarea|
HIDDEN_PAGES||textarea|Name of page must be included in quotes: &quot;
SHOW_GOOGLE_TRANSLATE||list|||false=NO~true=YES
SET_LANGUAGE_AUTOMATICALLY|Switch to user language automatically|list|||false=NO~true=YES
ACCOUNT_EMAIL_TEMPLATE||textarea|Used to validate the email, which user enters on signup
ACCOUNT_EMAIL_SYNONYM||textarea|Used to call the email column in data base
EMAIL_SYMBOL||textarea|Used to display the email column

'.( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? '
:Mobile App
MOBILE_APP_STORE_URL_ANDROID|URL of Google Play Store with App|textarea|
MOBILE_APP_STORE_URL_IOS|URL of Apple Store with App|textarea|
' : '').'
:DDOS protection
MIN_SECONDS_TO_CONSIDER_DDOS|Check out DDOS attack every|number||seconds
DDOS_REQUESTS_PER_SEC|recognize DDOS attack if more than|number||requests/sec
MAX_CLICKS_FROM_SPAMMER|block spammer if sends more than|number||clicks||2000

:Load balancing
REDIRECT_SIGNUP|Redirect new users to website|
REDIRECT_SIGNUP_COUNTRIES|Only redirect users from countries|

:Security
DENY_ACCESS_TO_API_NOT_LISTED_IN_DNS|If access to API from IP not listed in DNS|list|||true=Deny~false=Accept
VERIFICATION_PIN_SENDING_IN_MINUTES|Send verification pin in|number||minutes
VERIFICATION_PIN_NEED_IF_BALANCE|Ask sec. question on changing wallet name if balance more|currency
USE_API_SERVER_NOT_COLD_MIRROR||list|||true=Use API server~false=Use Cold Mirror
MAXIMUM_REGISTRATIONS_FROM_SAME_IP||number||||3
COUNT_REGISTRATIONS_DURING_DAYS|Count registrations during last|number||days||3
HOT_COLD_SWAP_SUSPENSE_PERIOD_IN_SEC|Switch to cold mirror if primary server does not respond during|number||seconds||300
SWITCH_TO_COLD_MIRROR_ON_DISASTER||list|||true=Yes~false=Never

:Data
DELETE_PASSIVE_USER_AFTER_DAYS|Delete passive users after|number||days||60
DELETE_DB_DATA_AFTER_DAYS|Delete useless data from DB after|number||days||60
DELETE_IMPORTANT_DB_DATA_AFTER_DAYS|Delete important data from DB after|number||days||365

';

$vars_arr = preg_split('/$\R?^/m', $vars);
echo '
<form name="user_frm" class="form-horizontal" method="post">
<input type="hidden" name="form_submitted" value="1">
<input type="hidden" name="password" id="password" value="">
<input type="hidden" name="hashed_password" id="hashed_password" value="">
';
foreach ($vars_arr as $var) {
	if ( $var[0] != '#' && $var[0] != ':' && !empty($var) && is_integer(strpos($var, '|')) ) {
		$var_params = explode("|", $var);
		$constant_name = $var_params[0];
		$constant_description = $var_params[1];
		$constant_tipe = $var_params[2];
		$constant_help = $var_params[3];
		$suffix = $var_params[4];
		$list_items = $var_params[5];
		$default_value = isset($var_params[6]) ? $var_params[6] : '';

		if ( !empty($constant_name) ) {
			
			if ( defined($constant_name) )
				$value = constant($constant_name);
			else
				$value = $default_value;

			if (!empty($list_items)) {
				$list_items_arr = explode("~", $list_items);
				$list_items_code = '';
				foreach ($list_items_arr as $list_item) {
					$list_item_arr = explode('=', $list_item);
					$list_items_code = $list_items_code.'<option value="'.$list_item_arr[0].'" '.($value == $list_item_arr[0] ? 'SELECTED' : '').'>'.$list_item_arr[1].'</option>';
				}
			}
			if ( empty($constant_description) )
				$constant_description = ucfirst(strtolower(str_replace('_', ' ', $constant_name)));
			if ( empty($constant_tipe) )
				$constant_tipe = 'text';
			
			switch ($constant_tipe) {
				case 'list_of_items':
					$input_code = '
						<div class="" style="padding:0 2px 0 2px; height:150px; overflow-y:auto; overflow-x:auto;">
							<div style="width:100%; height:0; position:relative; top:0; left:0;">
								<div style="width:0; height:0; position:absolute; top:4px; right:30px;">
									<button title="Add Risk" class="btn btn-success btn-xs" onclick="show_edit_item_box(this.id, \''.bin2hex('Task:').'\', undefined, \''.bin2hex('Minimum 10 symbols, maximum '.$task_max_char.' symbols. Please use English language only.').'\', \'min_len=3&max_len='.$task_max_char.'&no_chars=\', add_hacker_country); return false;"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></button>
								</div>
							</div>
						</div>
					';
				break;
				case 'percent':
					$input_code = '
						<input type="hidden" name="const_'.$constant_name.'" id="const_'.$constant_name.'" value="'.$value.'">
						<div class="input-group">
							<input type="number" step="1" autocomplete="off" id="'.$constant_name.'" value="'.round(floatval($value) * 100, 2).'" class="form-control input_percent" placeholder="" autocomplete="off"><span class="input-group-addon"><span class="glyphicon" aria-hidden="true" style="font-weight:bold; font-family:arial; min-width:100px; text-align:left;">%</span></span>
						</div>
					';
				break;
				case 'textarea':
					$input_code = '<textarea name="const_'.$constant_name.'" id="'.$constant_name.'" wrap="soft" class="form-control" rows="2">'.$value.'</textarea>';
				break;
				case 'unicode':
					$input_code = '
						<input type="text" step="any" autocomplete="off" name="'.$constant_name.'" id="'.$constant_name.'" onkeypress="unicode_entered(\''.$constant_name.'\');" onpaste="setTimeout(function(){ unicode_entered(\''.$constant_name.'\'); }, 300);" class="form-control" placeholder="'.$value.'" autocomplete="off">
						';
				break;
				case 'list':
					$input_code = '<select name="const_'.$constant_name.'" id="'.$constant_name.'" class="form-control">'.$list_items_code.'</select>';
				break;
				default:
					$input_code = 
						($constant_tipe == 'currency' || !empty($suffix) ? '<div class="input-group">' : '').'
						'.($constant_tipe == 'currency'?'<span class="input-group-addon"><span class="glyphicon" aria-hidden="true" style="font-weight:bold; font-family:arial;">'.DOLLAR_SIGN.'</span></span>':'').
						'<input type="'.($constant_tipe == 'currency' ?'number':$constant_tipe).'" step="any" autocomplete="off" name="'.
						($constant_tipe == 'password' || $constant_tipe == 'unicode' 
							? '' 
							:'const_'.$constant_name
						).'" id="'.$constant_name.'" '.
						($constant_tipe == 'password' 
							? 'onkeypress="psw_entered(\''.$constant_name.'\');"'
							:''
						).' value="'.
						(
							$constant_tipe == 'currency'?
							number_format($value, DOLLAR_DECIMALS, '.', '')
							:($constant_tipe == 'password' || $constant_tipe == 'unicode' ? '' : $value)
						
						).'" class="form-control '.'" placeholder="'.(
							$constant_tipe == 'password' ? 
								str_repeat('*', strlen($value)) 
								: ($constant_tipe == 'unicode' ? $value : '')
						).'" autocomplete="off" '.
						($constant_tipe == 'text' ? ' readonly onfocus="this.removeAttribute(\'readonly\');"' : '').
						'>'
						.(!empty($suffix)
							?'<span class="input-group-addon"><span class="glyphicon" aria-hidden="true" style="font-weight:bold; font-family:arial; min-width:100px; text-align:left;">'.$suffix.'</span></span>'
							:''
						).
						($constant_tipe == 'currency' || !empty($suffix)?'</div>':'');
			}

			echo '
			<div class="row" style="margin-bottom:4px;">
				<label class="control-label col-md-5" style="padding-left:20px;">'.ucfirst($constant_description).':</label>
				<div class="col-md-3 inputGroupContainer" style="padding-left:20px;">
					'.$input_code.'
				</div>
				<div class="col-md-4 inputGroupContainer" style="padding:0;">
					<table><tr>
						<td><div class="invisible_on_big_screen" style="width:20px;height:1px;"></div></td>
						<td>'.(!empty($constant_help)?show_help($constant_help):'').'</td>
					</tr></table>
				</div>
			</div>
			';

		}
	}
	if ( $var[0] == ':' ) {
		echo '<h2>'.substr($var, 1).'</h2>';
	}
}
echo '
<div style="text-align:center;"><button class="btn btn-success btn-lg" onclick="return save_btn_clicked();">Save</button></div>
</form>';
require(DIR_WS_INCLUDES.'footer.php');
require_once(DIR_WS_INCLUDES.'box_edit_item.php');
require_once(DIR_WS_INCLUDES.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_password.php');
?>
<script language="JavaScript">
function add_hacker_country()
{
	
}

function save_btn_clicked()
{
	$(".input_percent").each(function( index ) {
		var id = $(this).attr("id");
		$("#const_" + id ).val( $(this).val() / 100 );
		var val = $("#const_" + id ).val();
		val = 0;
	});
	return true;
}

function psw_entered(item_id)
{
	$("#" + item_id).attr("name", "const_" + item_id);
}

function unicode_entered(item_id)
{
	if ( $("#" + item_id).val().length > 0 ) {
		$("#" + item_id).attr("name", "const_" + item_id);
	}
}

$( document ).ready(function() {
	if ( $("#WITHDRAWAL_DISABLED").val() == "true" ) {
		$("#WITHDRAWAL_DISABLED").css("color", "#ff0000");
	}
});

</script>
</body>
</html>

