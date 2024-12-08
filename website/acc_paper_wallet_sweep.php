<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$page_header = 'Spend out a cold wallet';
$page_title = $page_header;
$page_desc = $page_header;
$no_prototype_js_in_header = true;
require(DIR_WS_INCLUDES.'header.php');
echo '
<script type="text/javascript">'.file_get_contents(DIR_WS_MOBI_WEBSITE.'javascript/box.select.boxed.js').'</script>
<script src="javascript/bitcoinjs.min.js" type="text/javascript"></script>
'. 
show_intro('/'.DIR_WS_IMAGES_DIR.'paper_wallet.png', '
<p>A paper wallet (or cold storage) contains a private key (40 symbols string) being printed out onto paper as QR code or presented in any other manner. Scan the private key, paste it from clipboard or enter manually.</p>
<p>All funds from the cold wallet will be swiped out to your balance.</p>
<p>The paper wallet will be cleaned out and cannot be reused.</p>', 'alert-info');

$file_path = DIR_ROOT.'mobi_website/paper_wallet_sweep.html';
$file_data = file_get_contents($file_path);
if ( $file_data && filesize($file_path) == strlen($file_data) ) {
	$file_data = get_text_between_tags($file_data, '<!-- for_site>> -->', '<!-- <<for_site -->');
	echo $file_data;
}

require_once(DIR_WS_INCLUDES.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_message.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require_once(DIR_WS_INCLUDES.'box_edit_item.php');
require_once(DIR_WS_INCLUDES.'box_qr_scanner.php');

require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>