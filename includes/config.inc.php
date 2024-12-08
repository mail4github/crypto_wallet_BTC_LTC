<?php

if ( !function_exists('protect_from_DDOS') ) {
	function protect_from_DDOS()
	{
		//if ( empty($_COOKIE['userid']) ) {
			$min_seconds_to_consider_DDOS = MIN_SECONDS_TO_CONSIDER_DDOS;
			$last_ips_from_file = get_file_variable('last_ips');
			$last_ip_arr = explode('|', $last_ips_from_file);
			$requests_from_this_ip = 0;
			foreach ($last_ip_arr as $ip) {
				if ( $ip == $_SERVER['REMOTE_ADDR'] )
					$requests_from_this_ip++;
			}
			if ( is_file_variable_expired('last_ips_created', 0, MIN_SECONDS_TO_CONSIDER_DDOS) ) {
				update_file_variable('last_ips_created', '1');
				$ip_arr = array();
				foreach ($last_ip_arr as $ip) {
					for ($i = 0; $i < count($ip_arr); $i++) {
						$found_ip = false;
						if ( $ip == $ip_arr[$i]['ip'] ) {
							$ip_arr[$i]['times']++;
							$found_ip = true;
							break;
						}
						if ( !$found_ip )
							$ip_arr[] = array( 'ip' => $ip, 'times' => 1 );
					}
				}
				$last_ips = '';
				foreach ( $ip_arr as $ip ) {
					for ($i = 1; $i <= $ip['times'] - 1; $i++)
						$last_ips = $last_ips.$ip['ip'].'|';
				}
				update_file_variable('last_ips', $last_ips);
			}
			else
				update_file_variable('last_ips', $_SERVER['REMOTE_ADDR'].'|'.$last_ips_from_file);
			//if ( defined('DEBUG_MODE') ) {echo "requests_from_this_ip: $requests_from_this_ip ".MIN_SECONDS_TO_CONSIDER_DDOS."  * ".DDOS_REQUESTS_PER_SEC."<br>"; exit;}
			if ( $requests_from_this_ip > MIN_SECONDS_TO_CONSIDER_DDOS * DDOS_REQUESTS_PER_SEC ) {
				if ( $requests_from_this_ip > MIN_SECONDS_TO_CONSIDER_DDOS * DDOS_REQUESTS_PER_SEC * 1.5 )
					header("HTTP/1.1 500 Internal Server Error", true, 500);
				else
					echo '
					<style>.loader {display: inline-block; border: 2px solid #f3f3f3;border-radius: 50%;border-top: 2px solid #3498db;width: 10px;height: 10px;-webkit-animation: spin 2s linear infinite;animation: spin 2s linear infinite;}@-webkit-keyframes spin{0% { -webkit-transform: rotate(0deg); }100% { -webkit-transform: rotate(360deg); }}@keyframes spin{0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); }}</style>
					Please Wait. <a href="/">Reload...</a> &nbsp;<div class="loader"></div>
					<script>setTimeout("location.reload();", 3000);</script>';				
				exit;
			}
		//}
	}
}

define('SCRIPT_STARTED_SEC', time());

global $received_API_server_IP;
$received_API_server_IP = '';
global $received_cold_mirror_IP;
$received_cold_mirror_IP = '';

define('WEBSITE_FRONT_DIR', 'website');
$s = getcwd();

$s1 = substr($s, 0, strpos($s, WEBSITE_FRONT_DIR) );

if (empty($s1))
	$s1 = $argv[1];

$website_folder_name = '';
for ($i = strlen($s1) - 2; $i >= 0; $i--) {
	if ( $s1[$i] == '/' )
		break;
	$website_folder_name = $s1[$i].$website_folder_name;
}
define('WEBSITE_FOLDER_NAME', $website_folder_name);

if ( $s1[strlen($s1) - 1] != '/' )
	$s1 = $s1.'/';
$s1 = str_replace('\\', '/', $s1);
define('DIR_ROOT', $s1);
define('WEBSITE_FOLDER_FULL_PATH', $s1);
define('DIR_WEBSITE_FOLDER', '');

define('DIR_DATA', DIR_ROOT.'ini_data/');

/////////////////////////////////
if ( file_exists(DIR_DATA.'$$$core_settings.ini') ) {
	$s = file_get_contents(DIR_DATA.'$$$core_settings.ini');
	$strings = preg_split('/$\R?^/m', $s);
	foreach( $strings as $string ) {
		$item_name = substr($string, 0, strpos($string, '='));
		if ( !empty($item_name) ) {
			$item_value = substr($string, strpos($string, '=') + 1);
			$item_value = trim($item_value);
			define($item_name, $item_value);
		}
	}
}
////////////////////////////////

if ( defined('COREDIR_INCLUDES') )
	define('DIR_WS_INCLUDES', COREDIR_INCLUDES);
else
	define('DIR_WS_INCLUDES', DIR_ROOT.'includes/');

define('DIR_COMMON_PHP', DIR_WS_INCLUDES);

include_once(DIR_WS_INCLUDES.'general.php');

define('INVOICE_PREFIX', 'xc-');
define('DONT_ROUND_ORDER_AMOUNT', true);

define('AFFILIATES_BANNERID', '0');

define('PAYPAL_PAGE_STYLE', '');
define('PAYMENT_PAGE_LOGO', '');

define('DEFAULT_REFBACK', 0.0); 

// Banners
define('BANNER_BID_MINIMUM', 0.01);
define('BANNER_BID_MAXIMUM', 10.00);

define('MANAGER_ID', '1');
define('GENERAL_AFF_MANAGER_USERID', MANAGER_ID); 
define('DEFAULT_USERID', MANAGER_ID);

define('TRACK_COOKIE_NAME', 'track_referrer');
define('TRACK_COOKIE_DOMAIN', 'came_from_domain');

global $result_file_extensions;
$result_file_extensions = array('jpeg', 'jpg', 'png', 'gif');

define('ALWAYS_CALCULATE_BALANCE', 1);

define('INITIAL_RANK', 1);

define('DIR_WS_CLASSES', DIR_WS_INCLUDES.'classes/');
define('DIR_WS_LOG', DIR_ROOT.'tmp_log/');

define('DIR_WS_IMAGES_DIR', 'images/');
define('DIR_WS_IMAGES', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_IMAGES_DIR);

define('DIR_WS_WEBSITE_IMAGES_DIR', DIR_WS_IMAGES_DIR);
define('DIR_WS_WEBSITE_IMAGES', DIR_WS_IMAGES);

define('DIR_WS_WEBSITE_PHOTOS_DIR', 'tmp_photos/');
define('DIR_WS_WEBSITE_PHOTOS', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_WEBSITE_PHOTOS_DIR);

define('DIR_WS_MOBI_WEBSITE_DIR', 'mobi_website/');
define('DIR_WS_MOBI_WEBSITE', DIR_ROOT.DIR_WS_MOBI_WEBSITE_DIR);


define('DIR_WS_BANNERS_NAME', 'banners/');
define('DIR_WS_BANNERS', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_BANNERS_NAME);
define('DIR_WEBSITE_BANNERS', DIR_WS_BANNERS);

define('DIR_WS_SERVICES_DIR', 'sr_/');
define('DIR_REAL_SERVICES_DIR', 'services/');
define('DIR_WS_SERVICES', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_REAL_SERVICES_DIR);

define('DIR_WS_CSS_NAME', 'css/');
define('DIR_WS_CSS', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_CSS_NAME);

define('DIR_WS_JAVA_DIR', 'javascript/');

define('DIR_WS_TEMP_NAME', 'tmp/');
define('DIR_WS_TEMP', DIR_ROOT.DIR_WS_TEMP_NAME);
define('DIR_WS_TEMP_ON_WEBSITE', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_NAME);

define('DIR_WS_TEMP_IMAGES_NAME', 'tmp_images/');
define('DIR_WS_TEMP_IMAGES', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_IMAGES_NAME);

define('DIR_WS_TEMP_BANNERS_NAME', 'tmp_banners/');
define('DIR_WS_TEMP_BANNERS', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_BANNERS_NAME);

define('DIR_WS_TEMP_UPLOADS_NAME', 'tmp_uploads/');
define('DIR_WS_TEMP_UPLOADS', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_UPLOADS_NAME);

define('DIR_WS_TEMP_CUSTOM_CODE_NAME', 'tmp_custom_code/');
define('DIR_WS_TEMP_CUSTOM_CODE', DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_TEMP_CUSTOM_CODE_NAME);

define('SERVICE_PREFIX', 'srvc');
define('UPGRADE_PREFIX', 'upgrd');
define('CHECKOUT_PREFIX', 'ckut_');
define('UPGRADE_MONTHS_PREFIX', 'mn');
define('UPGRADE_WEEKS_PREFIX', 'wk');
define('ADD_FUNDS_PREFIX', 'add');
define('USERID_PREFIX', 'uid');
define('PAY_LOAN_PREFIX', 'loun');
define('P2P_LEND_PREFIX', 'p2p');
define('P2P_INSTALLMENT_PREFIX', 'p2i');
define('WEBSITE_NUMBER_PREFIX', 'wn_');
define('WEBSITE_NUMBER_SUFFIX', '-');
define('PAYMENT_PROOF_THUMBNAIL_PREFIX', 'proof_thumb-');
define('PAYMENT_PROOF_FULL_IMAGE_PREFIX', 'proof-');
define('PAYOUT_PREFIX', 'paout');
define('BIG_PHOTO_THUMB_PREFIX', 'big_photo_');
define('BIG_PHOTO_LARGE_PREFIX', 'large_');
define('ADDRESS_TO_PAY_PREFIX', 'addr_to_pay_');
define('DEPOSIT_CREATE_PREFIX', 'dpst');
define('STORE_PREFIX', 'mrch');

define('DELETE_DB_DATA_AFTER_DAYS', 60);

define('DEFAULT_MANAGER_POSITION', 'Expert-Consultant');

define('PERMISSION_USER', 'USR');
define('PERMISSION_MANAGER', 'MNG');
define('PERMISSION_GENERAL_MANAGER', 'GENMAN');
define('PERMISSION_AUDIT', 'AUDIT');
define('PERMISSION_FREELANCE', 'FREELANCE');
define('PERMISSION_SALES_MANAGER', 'SLSMGR');
define('PERMISSION_TRESURER', 'TRESURER');
define('PERMISSION_REGIONAL_REP', 'REGREP');
define('PERMISSION_MONITOR', 'MONITOR');

define('TYPE_PREORDER', 'PS');
define('TYPE_VOTE', 'VT');
define('TYPE_CHAT', 'CT');

global $banner_sizes;
$banner_sizes = array('120x240', '120x60', '120x600', '160x600', '300x100', '300x250', '300x600', '468x60', '600x600', '728x90');

define('FIRST_COUNTRIES', 'US,GB,CA,IT,DE,NL,FR,AU,BR,ES,UK,RU,BE,NO,MX,CZ,CH,DK,SE,TR,PL,AR,AT,GR,FI,VE,NZ,PT,IL,CL,RO,IE,TH,HK,IN,JP,CO');
define('WEST_COUNTRIES', 'AU,CA,DK,GB,IE,NO,US,DE,NL,BR,IL,SE');

define('AD_LONG_LINE_MAX_SIZE', 64);
define('AD_HEADLINE_MAX_SIZE', 32);
define('AD_DESCR_MAX_SIZE', 70);
define('AD_URL_MAX_SIZE', 32);

define('IMAGE_BANNER_FORMAT', '<a href="{$targeturl}" target="_top"><img src="{$image_src}" border="0" alt="{$alt}" title="{$alt}" width="{$width}" height="{$height}" /></a>{$impression_track}');
define('TEXT_BANNER_FORMAT', '<style type="text/css">
.Af_Banner_title{color:#0000ff;}
.Af_Banner_descrt{color:#000000;}
.Af_Banner_link{color:#008000;}
</style>
<div align="left" style="padding:10px;"><font face="Arial,Helvetica,sans-serif" size="2"><a href="{$targeturl}" target="_top" class=Af_Banner_title><strong>{$title}</strong></a>
<br>
<div class=Af_Banner_descrt>{$description}</div>
<a href="{$targeturl}" target="_top" class=Af_Banner_link>{$display_url}</a>
</font></div>{$impression_track}');
define('FLASH_BANNER_FORMAT', '<object width="{$width}" height="{$height}">
                                <param name="movie" value="{$flashurl}?clickTAG={$targeturl_encoded}">
                                <param name="loop" value="{$loop}"/>
                                <param name="menu" value="false"/>
                                <param name="quality" value="medium"/>
                                <param name="wmode" value="{$wmode}"/>
                                <embed src="{$flashurl}?clickTAG={$targeturl_encoded}" width="{$width}" height="{$height}" loop="{$loop}" menu="false" swLiveConnect="FALSE" wmode="{$wmode}"></embed>
                            </object>
                            {$impression_track}');
define('ROTATOR_BANNER_FORMAT', '<script type="text/javascript" src="{$FullScriptsUrl}banner_rotator.php?{$targeturl}"></script>');
define('HTML_BANNER_FORMAT', '{$description}{$impression_track}');

define('MOD_REWRITE_PREFIX', 'r/');
define('MOD_REWRITE_SEPARATOR', '/');
define('MOD_REWRITE_SUFIX', '');

define('PARAM_AFFILIATE_ID', 'a_aid');
define('PARAM_BANNER_ID', 'a_bid');

define('FILE_BANNED_AFFILIATES', DIR_WS_TEMP.'$$$banned_userids.txt');
define('VAR_PAYMENT_PROOFS_NAME', 'proofs');
define('FILE_PAYMENT_PROOFS', DIR_WS_TEMP.'$$$'.VAR_PAYMENT_PROOFS_NAME.'.txt');

define('MAXIMUM_REGISTRATIONS_FROM_SAME_IP', 3);

define('MAXIMUM_CART_ITEMS', 20);

global $job_rating;
$job_rating = array('Not Rated', 'Very Bad', 'Bad', 'Good', 'Very Good', 'Excellent',);

define('AUTO_APPROVE_JOB_IN_DAYS', 7);

define('PREPAY_DIVIDENDS_FOR_DAYS', 90);

$profit_from_user = 0;
if ( file_exists(DIR_WS_TEMP.'profit_from_user.txt') )
	$profit_from_user = file_get_contents(DIR_WS_TEMP.'profit_from_user.txt');
if ( $profit_from_user > 0 )
	define('PROFIT_FROM_USER', $profit_from_user);
else
	define('PROFIT_FROM_USER', 0.20);

define('BLOCK_WITHDRAW_IF_SUSPICIOUS', 2);

if ( !isset($dont_read_design_data) ) {
	if ( file_exists(DIR_DATA.'$$$design.ini') ) {
		$s = file_get_contents(DIR_DATA.'$$$design.ini');
		$strings = explode( "\r\n", $s );
		foreach( $strings as $string ) {
			$item_name = substr($string, 0, strpos($string, '='));
			$item_value = substr($string, strpos($string, '=') + 1);
			if ( isset($item_name) && !empty($item_name) && !defined($item_name) )
				define($item_name, $item_value);
		}
	}
}

if ( file_exists(DIR_WS_TEMP.'monthly_roi.txt') )
	$monthly_roi = floatval(file_get_contents(DIR_WS_TEMP.'monthly_roi.txt'));
else
	$monthly_roi = 0.21;

if ( $monthly_roi < 0.1 )
	$monthly_roi = 0.1;
define('MONTHLY_ROI', $monthly_roi);

global $currencies_arr;
$currencies_arr =  json_decode(get_file_variable('currencies'), true);

/////////////////////////////////
if ( file_exists(DIR_DATA.'$$$settings.ini') ) {
	$s = file_get_contents(DIR_DATA.'$$$settings.ini');
	$strings = preg_split('/$\R?^/m', $s);
	foreach( $strings as $string ) {
		$item_name = substr($string, 0, strpos($string, '='));
		if ( !empty($item_name) ) {
			$item_value = substr($string, strpos($string, '=') + 1);
			$item_value = trim($item_value);
			if ( !defined($item_name) )
				define($item_name, $item_value);
		}
	}
}
////////////////////////////////

if ( !defined('HTTP_PREFIX') )
	define('HTTP_PREFIX', 'https://');

if ( substr_count(SITE_SHORTDOMAIN, '.') > 1 )
	define('SITE_DOMAIN', HTTP_PREFIX.SITE_SHORTDOMAIN.'/');
else
	define('SITE_DOMAIN', HTTP_PREFIX.SITE_SHORTDOMAIN.'/');

if ( defined('ORDER_DOMAIN') )
	define('RELAY_PAGE', ORDER_DOMAIN.'order.php?order_url=');

if ( !defined('WITHDRAWAL_FEE') )
	define('WITHDRAWAL_FEE', 0.01);

global $ranks;
try {
	$ranks = json_decode(get_file_variable('ranks'), 1);
}
catch (Exception $e) {
	if ( defined('DEBUG_MODE') ) echo 'Read ranks error: '.$e->getMessage().'<br>';
}

global $payout_options;
try {
	$payout_options = json_decode(get_file_variable('payout_options'), 1);
}
catch (Exception $e) {
	if ( defined('DEBUG_MODE') ) echo 'Read payout_options error: '.$e->getMessage().'<br>';
}

global $seq_questions_arr;
$seq_questions_arr = array(
	"What was your childhood nickname?",
	"What is the name of your favorite childhood friend?",
	"Who is your childhood sports hero?",
	"What were the last four digits of your childhood telephone number?",
	"What was your favorite food as a child?",
	"What was your favorite place to visit as a child?",
	"In what city did your mother and father meet?",
	"What is your favorite team?",
	"What is your favorite movie?",
	"What was the name of movie that you watched first time in your live?",
	"What is the first name of the boy or girl that you first kissed?",
	"What is the name of your first car?",
	"What was the name of the hospital where you were born?",
	"In what town was your first job?",
	"What was the name of the company where you had your first job?",
	"What is the name of street where you used to live in childhood?",
	"What street number where you used to live in childhood?",
	"What was the mascot of your school?",
	"What is the name of your first school?",
	"What was your favorite sport in school?",
	"What was your favorite science in school?",
	"What is the name of your first pet?",
	"Which moniker your oldest child has?",
	"What is the name of the place where your wedding ceremony was held?",
	"What was the name of your first teddy-bear or doll or action figure?",
	"What was the name of city into which, first time in your life, you did fly?",
	"What was the first book you ever read?",
	"What is your favorite book?",
);

define('TEXT_BANNER_CODE', '<p style="text-align:left; font-family:arial; padding:10px; margin:0px;"><a href="'.SITE_DOMAIN.'services/adclick.php?ad={ad_id}&cl={$click_cost}" target="_blank" style="text-decoration:none;"><span style="text-decoration:none; font-size:12px; font-weight:bold; color:{ad_headline_color};">{ad_headline}</span><br><span style="font-size:11px; font-weight:none; color:{ad_description_color};">{ad_description}</span><br><span style="text-decoration:underline; font-size:9px; font-weight:bold; color:{ad_display_url_color}; ">{ad_display_url}</span></a></p>');
define('IMAGE_BANNER_CODE', '<a href="'.SITE_DOMAIN.'services/adclick.php?ad={ad_id}" target="_blank"><img src="{banner_source}" border="0" alt="{url}" title="{url}" width="{banner_width}" height="{banner_height}"/></a>');

define('AD_CODE_728x90', '<SCRIPT TYPE="text/javascript" SRC="'.SITE_DOMAIN.'services/ad.php?s=728x90&t=T,I,H&bkg_color=transparent&head_color=aa0000&url_color=0000aa"></SCRIPT>');

define('AFFILIATE_SITE_SHORTDOMAIN', SITE_SHORTDOMAIN);
define('AFFILIATE_URL', SITE_DOMAIN);

define('BANNERS_URL', SITE_DOMAIN.DIR_WS_BANNERS_NAME);
define('BANNERS_WEBSITE_URL', SITE_DOMAIN.DIR_WS_BANNERS_NAME);

define('SIGNUP_URL', SITE_DOMAIN.'signup');
define('LOGIN_URL', SITE_DOMAIN.'login.php');

if (defined('SITE_SLOGAN'))
	define('SITE_NAME', SITE_SLOGAN);
else
	define('SITE_NAME', '');

if ( defined('IPN_DOMAIN') )
	define('PAYPAL_IPN', IPN_DOMAIN.DIR_WS_SERVICES_DIR.'ipn.php');

if ( !defined('SHARE_SELL_PRICE_MAXIMUM') )
	define('SHARE_SELL_PRICE_MAXIMUM', 90);
if ( !defined('SHARE_PRICE_CANNOT_BE_CHANGED_DURING_DAYS') )
	define('SHARE_PRICE_CANNOT_BE_CHANGED_DURING_DAYS', 10);

//Constant below contains md5-hashed alternate passhrase in upper case.
//You can generate it like this:
//strtoupper(md5('your_passphrase'));
//Where `your_passphrase' is Alternate Passphrase you entered
//in your PerfectMoney account.
if ( !defined('PM_ALTERNATE_PHRASE_HASH') )
	define('PM_ALTERNATE_PHRASE_HASH',  '53C367278C66D0C6AC9952DA1D494E32');

if ( !defined('LOAN_MAX_VALUE') )
	define('LOAN_MAX_VALUE',  40);

if ( !defined('LOAN_INTEREST') )
	define('LOAN_INTEREST',  0.03);

define('CUSTOM_FONT_PREFIX', '$$$custom_fnt');
if ( !defined('VERIFICATION_PIN_SENDING_IN_MINUTES') )
	define('VERIFICATION_PIN_SENDING_IN_MINUTES', 2);
if ( !defined('VERIFICATION_PIN_NEED_IF_BALANCE') )
	define('VERIFICATION_PIN_NEED_IF_BALANCE', 10);

define('REGIONAL_REPS_VAR_NAME', 'regional_reps');
define('FILE_REGIONAL_REPS', DIR_WS_TEMP.REGIONAL_REPS_VAR_NAME.'.txt');
define('PREVIOUS_CONTEST_WINNERS_FILE', DIR_WS_TEMP.'$$$previous_contest_winners.txt');
define('CONTEST_USERS_FILE', DIR_WS_TEMP.'$$$contest_users.txt');

if ( !defined('PM_PURCHASE_DISCOUNT') )
	define('PM_PURCHASE_DISCOUNT', 0.12); // from 0.10 14 Oct 2017
if ( !defined('BITCOIN_PURCHASE_DISCOUNT') )
	define('BITCOIN_PURCHASE_DISCOUNT', 0.28); // from 0.10 14 Oct 2017
if ( !defined('bitcoin_WAIT_FOR_CONFIRMATIONS') )
	define('bitcoin_WAIT_FOR_CONFIRMATIONS', 1);
if ( !defined('LITECOIN_PURCHASE_DISCOUNT') )
	define('LITECOIN_PURCHASE_DISCOUNT', 0.20);

define('BITCOIN_CURRENT_MINERS_FEE_FILE', 'miners_fee_bitcoin');
if ( !defined('bitcoin_MINERS_FEE') ) {
	$bitcoin_current_miners_fee = 0;
	if ( file_exists(DIR_WS_TEMP.'$$$'.BITCOIN_CURRENT_MINERS_FEE_FILE.'.txt') )
		$bitcoin_current_miners_fee = file_get_contents(DIR_WS_TEMP.'$$$'.BITCOIN_CURRENT_MINERS_FEE_FILE.'.txt');
	if ( $bitcoin_current_miners_fee > 0 ) {
		define('bitcoin_MINERS_FEE', $bitcoin_current_miners_fee * 200 * 2 / 100000000); // received value in the file BITCOIN_CURRENT_MINERS_FEE_FILE has been devied by 2
	}
	else
		define('bitcoin_MINERS_FEE', 0.0006); // 26 may 2017 average transaction size 200B * 300 satoshi
}
define('BITCOIN_BLOCKS_EXPLORER', 'https://www.blockchain.com/btc/address/{$address}');
define('LITECOIN_BLOCKS_EXPLORER', 'https://sochain.com/address/LTC/{$address}');

define('BITCOIN_TRANSACTIONS_EXPLORER', 'https://www.blockchain.com/btc/tx/{$hash}');
define('LITECOIN_TRANSACTIONS_EXPLORER', 'https://sochain.com/tx/LTC/{$hash}');

if ( !defined('AFFILIATE_COMMISSION') )
	define('AFFILIATE_COMMISSION', 0.16); // 24 feb 2017

define('TIMELINE_SEQUENCE_STEP', 1000);

if ( !defined('DAO_NAME') )
	define('DAO_NAME', 'project');
if ( !defined('CONTEST_1PLACE') )
	define('CONTEST_1PLACE', 30);

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'add_config.inc.php') )
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'add_config.inc.php');

if ( !defined('HACKER_COUNTRIES') )
	define('HACKER_COUNTRIES', 'HK,KE,MA,MY,NG,SG,TN,TR,VN,');

if ( !defined('PAYOUTS_VS_PURCHASES') )
	define('PAYOUTS_VS_PURCHASES', 3.0); // from 1.3 30 nov 2017

if ( !defined('ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS') )
	define('ACTIVITY_POINTS_COUNT_PURCHASES_FOR_DAYS', 15);

if ( !defined('ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_FOR_DAYS') )
	define('ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_FOR_DAYS', 50);
if ( !defined('ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_IF_MORE') )
	define('ACTIVITY_POINTS_COUNT_SOLID_PURCHASES_IF_MORE', 1);

if ( !defined('MINIMUM_PREORDERED_SHARES_PERCENT') )
	define('MINIMUM_PREORDERED_SHARES_PERCENT', 0.3);

if ( !defined('EXAMINE_NEW_STOCK_DAYS') )
	define('EXAMINE_NEW_STOCK_DAYS', 15);

if ( !defined('NEW_STOCK_APPROVAL_SCORE') )
	define('NEW_STOCK_APPROVAL_SCORE', 0.75);

if ( !defined('MAKE_AUTO_PAYOUT_AFTER_DAYS') )
	define('MAKE_AUTO_PAYOUT_AFTER_DAYS', 15);

if ( !defined('MIN_SECONDS_TO_CONSIDER_DDOS') )
	define('MIN_SECONDS_TO_CONSIDER_DDOS', 5);

if ( !defined('MAX_PAGE_READ_TIME_FILES') )
	define('MAX_PAGE_READ_TIME_FILES', 5);

if ( !defined('DDOS_REQUESTS_PER_SEC') )
	define('DDOS_REQUESTS_PER_SEC', 5);

if ( !defined('VOLUME_DISCOUNT_STEP') )
	define('VOLUME_DISCOUNT_STEP', 20);

if ( !defined('VOLUME_DISCOUNT_MAX_PERCENT') )
	define('VOLUME_DISCOUNT_MAX_PERCENT', 0.3);

if ( !defined('HOLD_NOT_SOLID_PARCHASES_DAYS') )
	define('HOLD_NOT_SOLID_PARCHASES_DAYS', 30);

if ( !defined('USE_HTML_WINDOW_IN_DESIGN') )
	define('USE_HTML_WINDOW_IN_DESIGN', 1);

if ( !defined('MAXIMUM_CART_TOTAL') )
	define('MAXIMUM_CART_TOTAL', 120);
if ( !defined('MINIMUM_CART_TOTAL') )
	define('MINIMUM_CART_TOTAL', 2);
if ( !defined('MAXIMUM_WITHDRAW') )
	define('MAXIMUM_WITHDRAW', 50);
if ( !defined('MAXIMUM_GAIN_FOR_CLICKS') )
	define('MAXIMUM_GAIN_FOR_CLICKS', 10);
if ( !defined('HOLD_REWARD_FOR_CLICKS_DAYS') )
	define('HOLD_REWARD_FOR_CLICKS_DAYS', 30);
if ( !defined('AUTO_ANSWER_TICKET_AFTER_HOURS') )
	define('AUTO_ANSWER_TICKET_AFTER_HOURS', 12);
if ( !defined('DOLLAR_NAME') )
	define('DOLLAR_NAME', 'USD');
if ( !defined('DOLLAR_SIGN') )
	define('DOLLAR_SIGN', '$');
if ( !defined('DOLLAR_SIGN_POSITION') )
	define('DOLLAR_SIGN_POSITION', 'left');
if ( !defined('DOLLAR_DECIMALS') )
	define('DOLLAR_DECIMALS', 2);
if ( !defined('DECIMALS_IN_ALL_FLOAT') )
	define('DECIMALS_IN_ALL_FLOAT', 3);
if ( !defined('ENGLISH_LANGUAGE_ONLY') )
	define('ENGLISH_LANGUAGE_ONLY', 1);
if ( !defined('WORD_MEANING_SHARE') )
	define('WORD_MEANING_SHARE', 'share');
if ( !defined('WORD_MEANING_DIVIDEND') )
	define('WORD_MEANING_DIVIDEND', 'dividend');
if ( !defined('PART_OF_ISSUED_SHARES_GOES_TO_BROCKER') )
	define('PART_OF_ISSUED_SHARES_GOES_TO_BROCKER', 0.2);
if ( !defined('PART_OF_TRANSACTION_GOES_TO_BROCKER') )
	define('PART_OF_TRANSACTION_GOES_TO_BROCKER', 0.5);
if ( !defined('SHARE_PRICE_MINIMUM') )
	define('SHARE_PRICE_MINIMUM', 2.00);
if ( !defined('SHARE_PRICE_MAXIMUM') )
	define('SHARE_PRICE_MAXIMUM', 10);
if ( !defined('LOAN_PP_INTERVAL') )
	define('LOAN_PP_INTERVAL',  7);
if ( !defined('LOAN_PP_STAY_LISTED') )
	define('LOAN_PP_STAY_LISTED',  3);
if ( !defined('SHOW_GOOGLE_TRANSLATE') )
	define('SHOW_GOOGLE_TRANSLATE', 'true');

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE) ) {
	// replace classes with defined in folder: DIR_WS_TEMP_CUSTOM_CODE
	$custom_code_files = scandir(DIR_WS_TEMP_CUSTOM_CODE);
	$folders = scandir(DIR_WS_CLASSES);
	foreach ( $folders as $short_file_name ) {
		$file_name = DIR_WS_CLASSES.$short_file_name;
		if ( !is_dir($file_name) && is_integer(strpos($short_file_name, '.class.php')) ) {
			$text = file_get_contents($file_name);
			if ( preg_match("/class([ ]+)([^ ^\r^\n^$]+)([ ]+|$|\r|\n)/i", $text, $classes_arr) ) {  
				foreach ($custom_code_files as $custom_code_file) {
					$custom_code_file_name = DIR_WS_TEMP_CUSTOM_CODE.$custom_code_file;
					if ( $custom_code_file == $short_file_name ) {
						$custom_code_text = file_get_contents($custom_code_file_name);
						if ( preg_match("/class([ ]+)([^ ^\r^\n^$]+)([ ]+|$|\r|\n)/i", $custom_code_text, $custom_code_classes_arr) ) {  
							if ( !empty($classes_arr[2]) && !empty($custom_code_classes_arr[2]) ) {
								define('CLASS_REPLACE_'.$classes_arr[2], $custom_code_classes_arr[2]);
							}
						}
						break;
					}
				}
			}
		}
	}
}
$s = '../../debug/webserver_debug.php';
if ( file_exists($s) ) {
	include_once($s);
}
if ( !isset($dont_read_design_data) ) {
	if ( !file_exists(DIR_DATA.'$$$design.ini') ) {
		// restore design file from db
		restore_design_data_from_db();
	}
}
?>