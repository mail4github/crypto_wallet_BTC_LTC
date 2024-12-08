<?php
require('../../includes/application_top.php');
require_once(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'banner.class.php');

$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
if ( empty($_GET['user']) || empty($_GET['banner']) )
	exit;
echo '
<p class="banner_code_text" id="text_p">';
$banner = new Banner();
$banner->owner = $user_account;
if ( $banner->get_info((int)$_GET['banner']) ) {
	$banner_code = $banner->getBannerCode($user_account);
	if ( $banner->type != 'E' && empty($_GET['as_is']) ) {
		$banner_code = str_replace('<', '&lt;', $banner_code);
		$banner_code = str_replace('>', '&gt;', $banner_code);
		$banner_code = str_replace("'", '&#39;', $banner_code);
		$banner_code = str_replace("\r\n", '<br>', $banner_code);
		$banner_code = str_replace("\t", '&nbsp;', $banner_code);
	}
	echo $banner_code;
}
echo '</p>';
?>
<script type="text/javascript">

function select_text_in_frame(objId) {
	if (document.selection) {
		var range = document.body.createTextRange();
		range.moveToElementText(document.getElementById(objId));
		range.select();
	}
	else 
	if (window.getSelection) {
		var range = document.createRange();
		range.selectNode(document.getElementById(objId));
		window.getSelection().addRange(range);
	}
}
<?php
echo (empty($_GET['as_is'])?'document.body.onclick = select_text_in_frame(\'text_p\')'.";\r\n":'');
?>
</script>
</body>
</html>
