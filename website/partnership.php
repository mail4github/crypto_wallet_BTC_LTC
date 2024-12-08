<?php
$showDebugMessages = false;

if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);

$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'Earn the big money on referrals';
$page_title = $page_header;
$parent_page = 'acc_help.php';
require(DIR_WS_INCLUDES.'header.php');

if ( $user_account->is_loggedin() || is_file_variable_expired('partnership', 60) || defined('DEBUG_MODE') ) {
	$average_investor_invests = $user_account->get_average_investor_invests(30, false);
	if ( !$user_account->is_loggedin() )
		$user_account->rank = 1;
	if ( isset($user_account->common_param_payout_providers_logos) ) {
		$payout_providers_logos = $user_account->common_param_payout_providers_logos;
	}
	else {
		$common_params = $user_account->get_list_of_common_params();
		$payout_providers_logos = $common_params['payout_providers_logos'];
	}
	
	$out_str = '
	<style type="text/css">
	.col-md-4 h2{margin-bottom:20px; }
	.col-md-4{padding:20px; text-align:center;}
	.payout_providers_logos{width:64px;height:64px;margin-right:10px;}
	</style>
	
	<p class="string_to_translate">We are glad to see at our site all individuals who want to rise money online helping us to grow up our client base.</p>
	<p class="string_to_translate">When you sign up for our Affiliate Program we will give you a special link to us that you can share through an advertising platform or post on social network or insert into your blog or tweet it or spread it any way! You will earn income for every customer who signs up through your link.</p>
	<h2 class="string_to_translate">Invite users and get revenue</h2>
	<p class="string_to_translate">You can be a blogger, webmaster, or ordinary user to earn really good money using your skill. All you need is add our advertising code into your personal website, twitter, blog, social network, or wherever else.</p>
	<div class="row box_type2" style="padding:0 0 20px 0; margin:20px 0 20px 0;">
		<div class="col-md-1"></div>
		<div class="col-md-4 " style="">
			<h2 class="string_to_translate">Are you a blogger?</h2>
			<p class="string_to_translate">Monetize your blog by adding our banners or affiliate links to your posts. Why not convert the most of your site traffic into money by promoting one of the most profitable affiliate program in the industry?</p>
		</div>
		<div class="col-md-1"></div>
		<div class="col-md-1"></div>
		<div class="col-md-4" style="">
			<h2 class="string_to_translate">Are you a publisher?</h2>
			<p class="string_to_translate">Use your online marketing medium to bring customers to us: email, media buys, contextual ads, social networks.</p>
		</div>
		<div class="col-md-1"></div>
	</div>
	<h2 class="string_to_translate">How do our partners earn money?</h2>
	<ul class="ul">
		<li class="string_to_translate">You invite users through special affiliate address, which they should use to sign up:</li>
	</ul>
	<div style="position:relative; margin:0; background-image:url(/'.DIR_WS_WEBSITE_IMAGES_DIR.'partnership.png); background-repeat:no-repeat; width:100%; max-width:800px; height:40px; background-position-x:20px; background-size:400px 29px;">
		<div style="position:absolute; top:14px; left:72px; width:200px; overflow:hidden; font-size:9px;">'.(!empty($user_account->userid)?'<span style="cursor:pointer;" onclick="if (document.selection){var range = document.body.createTextRange(); range.moveToElementText(this);range.select();}else{if (window.getSelection) {var range = document.createRange();range.selectNode(this);window.getSelection().removeAllRanges();window.getSelection().addRange(range);}}">'.$user_account->get_general_aff_link().'</span>':$user_account->get_general_aff_link('XXXXX')).'</div>
	</div>
	<p style="margin-left:20px;"><span class="string_to_translate">where your affiliate ID</span>: <b>'.(!empty($user_account->userid)?$user_account->userid:'XXXXX').'</b></p>
	<ul class="ul">
		<li><span class="string_to_translate">On each purchase from your referrals we pay</span>: <b>'.number_format(AFFILIATE_COMMISSION * 100, 0).'%</b>. <span class="string_to_translate">Our average user makes monthly purchases for</span> <b>'.currency_format($average_investor_invests, '', '', '', false, false, '', '', true).'</b>, <span class="string_to_translate">that means every month you can receive from each of your referrals</span>: <b>'.currency_format($average_investor_invests * AFFILIATE_COMMISSION, '', '', '', false, false, '', '', true).'</b>.</li>
		'.(COST_OF_VISITOR > 0?'<li><span class="string_to_translate">Also, for every unique visitor, you will be rewarded</span> <b>'.currency_format(COST_OF_VISITOR, '', '', '', false, false, '', '', true).'</b> (<span class="string_to_translate">all visitors are subject to audit</span>).</li>':'').'
		<li class="string_to_translate">We pay through:</li>
		<p style="margin-left:20px;" class="notranslate">'.$payout_providers_logos.'</p>
	</ul>
	<h2 class="string_to_translate">How to invite users?</h2>
	<p><span class="string_to_translate">Please see the</span> <a href="/acc_promo_tips.php" class="string_to_translate">Promotion Tips</a></p>
	<h2 class="string_to_translate">Marketing Materials</h2>
	<p><span class="string_to_translate">To help your promotions we have created</span> <a href="/acc_banners.php" class="string_to_translate">banners, texts, and ads</a></p>
	<br><br><br>';
	
	if ( !$user_account->is_loggedin() )
		update_file_variable('partnership', $out_str);

	echo translate_str($out_str);
}
else {
	echo get_file_variable('partnership');
}

if ( !$user_account->is_loggedin() ) {
	echo translate_str('<div style="text-align:center;"><a href="#" onclick="window.location.assign(\'/signup\'); return false;" class="btn btn-primary btn-lg string_to_translate">Join Now...</a></div>');
}
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>