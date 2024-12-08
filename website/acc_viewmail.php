<?php
if ( !empty($_GET['mailid']) ) 
	$mailid = $_GET['mailid'];

if ( !empty($_POST['mailid']) ) 
	$mailid = $_POST['mailid'];
	
if ( empty($mailid) )	{
	header('Location: /messages', true, 301);
	exit;
}
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

$email_arr = get_api_value('email_get', '', array('mailid' => $mailid));

if ( !$user_account->is_manager() || ($user_account->is_manager() && $email_arr['userid'] == $user_account->userid) )
	get_api_value('email_set_opened', '', array('mailid' => $mailid));

$page_header = '';
$page_title = $page_header;
$page_desc = $page_header;
$list_of_common_params = $user_account->get_list_of_common_params();
require(DIR_WS_INCLUDES.'header.php');
echo '
<p>Sent: <b class="local_pline_date_and_hour" unix_time="'.$email_arr['c_created_unix_time'].'">'.$email_arr['c_created'].'</b></p>
<p>Subject: <b>'.$email_arr['subject'].'</b></p>
<h2>Message:</h2>
<div style="" >'.$email_arr['body_html'].'</div>
<br>
<a class="btn btn-primary" href="/messages"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span>&nbsp;&nbsp;Back to Messages</a>
';
?>
<script src="javascript/QRCode.js"></script>
<script language="JavaScript">
$( document ).ready(function() {
	if (typeof ninja !== "undefined" ) {
		$(".qrcode_rect").each(function() {
			eval(`
				var keyValuePair = {
					` + $(this).attr("id") + ` : $(this).attr("_value")
				};
			`);
			ninja.qrCode.showQrCode(keyValuePair, 4, "qr_canvas");
			$(this).show();
		});
	}
});
</script>
<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>

</body>
</html>
