<?php
if ( !empty($_GET['purchaseid']) ) 
	$purchaseid = $_GET['purchaseid'];

if ( !empty($_POST['purchaseid']) ) 
	$purchaseid = $_POST['purchaseid'];
	
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');

$transaction = new Transaction();
$transaction->read_data('', '', $purchaseid, 1, 1);

if ( !empty($_POST['cancel_purchase']) ) {
	$transaction->cancel_purchase();
	$transaction->read_data('', '', $purchaseid, 1, 1);
}

$page_header = 'Purchase';
$page_title = $page_header;
$page_desc = 'Product Purchase.';
require(DIR_WS_INCLUDES.'header.php');

echo '
<table class="table table-striped table-borderless">
<tr>
	<td>
		Amount: <strong>'.currency_format(abs($transaction->purchase_amount)).'</strong>
	</td>
	<td>
		Status: <strong>'.$transaction->get_purchase_status_name().'</strong>
		'.($user_account->is_manager() && $transaction->purchase_status == 'A'?'
			<form method="POST" style="display:inline;">
				<input type="hidden" name="purchaseid" value="'.$transaction->purchaseid.'">
				<input type="hidden" name="transactionid" value="'.$transaction->transactionid.'">
				<input type="hidden" name="cancel_purchase" value="1">
				<button style="margin-left:0px;" class="btn btn-warning btn-xs" onClick="return ( confirm(\'Do you really want to cancel this purchase?\') );">Cancel Purchase</button>
			</form>'
			:''
		).'
	</td>
</tr>
<tr>
	<td style="">
	Initiated: <strong>'.($transaction->purchase_created).'</strong>
	</td>
	<td>
	Quantity: <strong>'.(number_format($transaction->purchase_quantity, 0)).'</strong>
	</td>
</tr>
<tr>
	<td style="">
	Transaction Id: <strong>'.$transaction->pay_processor_txn_id.'</strong>
	</td>
	<td>
	Invoice: <strong>'.$transaction->purchase_invoice.'</strong>
	</td>
</tr>
<tr>
	<td style="">
	From address: <strong>'.$transaction->purchase_user_email.'</strong> ('.((bool)$transaction->purchase_user_verified?'<font color="#008800">verified</font>':'<font color="#ff0000">not verified</font>').')
	</td>
	<td>
	From Country: <strong>'.getCountryName($transaction->purchase_user_country).'</strong> ('.((bool)$transaction->user_address_confirmed?'<font color="#008800">confirmed</font>':'<font color="#ff0000">not confirmed</font>').')
	</td>
</tr>
<tr>
	<td style="">
	Payer ID: <strong>'.$transaction->purchase_user_payer_id.'</strong>
	</td>
	<td>
	Paid by: <strong>'.$transaction->purchase_first_name.' '.$transaction->purchase_last_name.'</strong> ('.((bool)$transaction->user_protected?'<font color="#008800">protected</font>':'<font color="#ff0000">not protected</font>').')
	</td>
</tr>
<tr>
	<td style="text-align:right;">
		To address:
	</td>
	<td>
		<strong>'.$transaction->pay_processor_email.'</strong>
	</td>
</tr>
</table>';
?>
<button class="btn btn-link btn-lg" style="margin:0px 0 40px 0;" onclick="document.location.replace('/acc_purchases.php');"><span class="glyphicon glyphicon-backward" aria-hidden="true"></span>&nbsp;&nbsp; Back to Purchases</button>
<?php
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
