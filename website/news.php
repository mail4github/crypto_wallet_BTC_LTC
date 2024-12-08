<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$footer = '';
$news = $user_account->is_news_avalable();
if ( $news ) {
	$header = get_text_between_tags($news['var_text1'], '<header>', '</header>');
	$body = get_text_between_tags($news['var_text1'], '<body>', '</body>');
	$footer = get_text_between_tags($news['var_text1'], '<footer>', '</footer>');
	eval($header);
	require(DIR_WS_INCLUDES.'header.php');
	eval($body);
}
else {
	$page_header = 'Website for sale';
	require(DIR_WS_INCLUDES.'header.php');
	echo '<h2>There are no website for sale</h2>';
}
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_COMMON_PHP.'box_image_preview.php');
eval($footer);
?>
</body>
</html>