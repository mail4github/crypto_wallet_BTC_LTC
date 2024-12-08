<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Exchange';
$page_title = $page_header;
$page_desc = $page_header;

require(DIR_WS_INCLUDES.'header.php');
/*
if ( ($user_account->stat_purchases == 0 && is_integer(strpos(HACKER_COUNTRIES, $user_account->country)) && is_integer(strpos(HACKER_COUNTRIES, $user_account->stat_country_last_login))) || !$user_account->get_rank_value('can_buy_shares') || ($user_account->account_type == 'G' && $user_account->rank == 0 && $user_account->purchases_disabled) ) {
	echo show_intro('', 'Not available.', 'alert-warning');
	require(DIR_WS_INCLUDES.'footer.php');
	exit;
}
*/
echo show_intro('/'.DIR_WS_IMAGES_DIR.'exchange.png', 'Instant and secure cryptocurrency exchange allows to swap funds in an easy way.', 'alert-info');
$file_code = DIR_WS_INCLUDES.'$$$widget_exchange.php';

if ( file_exists($file_code) )
	eval("?>" . file_get_contents($file_code) . "<?php ");

require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>