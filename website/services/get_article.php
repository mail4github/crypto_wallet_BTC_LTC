<?php
$showDebugInfo = defined('DEBUG_MODE');
if ( $showDebugInfo )
	error_reporting(E_ALL);

include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
require_once(DIR_WS_CLASSES.'stock.class.php');
if ( !empty($_GET['productid']) ) {
	$stock = new Stock();
	$stock_list = $stock->get_list('random', 'only_rated_shares', 1, 2);
	$stock = $stock_list[0];
	if ( $showDebugInfo )
		$article = 'stockid='.$stock->stockid.'&name='.$stock->name.'&current_price='.currency_format($stock->stat_current_price).'&dividend_frequency='.($stock->dividend_frequency <= 1?'Daily':($stock->dividend_frequency == 7?'Weekly':'Monthly')).'&dividend='.currency_format($stock->dividend).'&url='.$stock->get_url($_GET['for_userid']).'&image_banner_code='.$stock->get_image_banner_code();
	else
		$article = 'stockid='.$stock->stockid.'&name='.$stock->name.'&current_price='.currency_format($stock->stat_current_price).'&dividend_frequency='.($stock->dividend_frequency <= 1?'Daily':($stock->dividend_frequency == 7?'Weekly':'Monthly')).'&dividend='.currency_format($stock->dividend).'&url='.bin2hex($stock->get_url($_GET['for_userid'])).'&image_banner_code='.bin2hex($stock->get_image_banner_code());
	echo $article;
}
?>