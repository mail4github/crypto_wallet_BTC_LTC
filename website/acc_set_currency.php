<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

$page_header = 'Select Currency';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
$file_path = DIR_ROOT.'mobi_website/set_currency.html';
$file_data = file_get_contents($file_path);
if ( $file_data && filesize($file_path) == strlen($file_data) ) {
	$file_data = get_text_between_tags($file_data, '<!-- for_site>> -->', '<!-- <<for_site -->');
	echo $file_data;
}

require_once(DIR_WS_INCLUDES.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require_once(DIR_WS_INCLUDES.'box_edit_item.php');

require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>