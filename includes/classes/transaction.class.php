<?php
require_once(DIR_COMMON_PHP.'performance_time.php');
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'payout.class.php');
require_once(DIR_WS_CLASSES.'stock.class.php');
require_once(DIR_WS_CLASSES.'share.class.php');

class Transaction
{
	var $transactionid = 0;
	var $variables = array();
	var $owner;
	var $userid = 0;
	var $invoice = '';
	var $days_since_created = 0;
	
	function read_data($transactionid = 0, $invoice = '', $purchaseid = 0, $init_purchase_data = 0, $init_share_transaction_data = 0)
	{
		if ( empty($transactionid) )
			$transactionid = 0;
			
		if ( $transactionid != 0 )
			$this->transactionid = $transactionid;
		if ( !empty($invoice) )
			$this->invoice = $invoice;
		if ( $purchaseid != 0 )
			$this->purchaseid = $purchaseid;
		if ( $this->transactionid != 0 || !empty($this->invoice) || !empty($this->purchaseid) ) {
			$variables = get_api_value('transaction_read_data', '', array('transactionid' => $this->transactionid, 'invoice' => $this->invoice, 'purchaseid' => $this->purchaseid, 'init_purchase_data' => $init_purchase_data, 'init_share_transaction_data' => $init_share_transaction_data));
			if ( $variables ) {
				foreach($variables as $akey => $aval) {
					$this->$akey = base64_decode($aval);
					$this->variables[$akey] = base64_decode($aval);
				}
				return true;
			}
		}
		else
			return false;
	}
	
	function init_owner($userid = '')
	{
		if ( !$this->owner ) {
			if ( !empty($userid) )
				$this->userid = $userid;
			$this->owner = new User();
			$this->owner->userid = $this->userid;
			$this->owner->read_data(false);
		}
		return $this->owner;
	}
	
	function init_purchase_data()
	{
		$variables = get_api_value('transaction_init_purchase_data', '', array('purchaseid' => $this->purchaseid));
		if ( $variables ) {
			foreach($variables as $akey => $aval) {
				$this->$akey = base64_decode($aval);
				$this->variables[$akey] = base64_decode($aval);
			}
		}
		return true;
	}
	
	function init_share_transaction_data()
	{
		
	}
	
	function delete()
	{
		
	}

  	function get_status_name()
	{
		switch ($this->status) {
			case 'A': return 'done';
			case 'P': return 'pending';
			case 'D': return 'canceled';
		}
	}

	function get_purchase_status_name()
	{
		switch ($this->purchase_status) {
			case 'A': return 'approved';
			case 'P': return 'pending';
			case 'D': return 'declined';
		}
	}
	
	function add_bonus(
		$userid, 
		&$amount,
		$status = 'A', 
		$will_active_in_days = 0, 
		$send_email = true,
		$note = '',
		$purchaseid = 0,
		$transaction_type = 'BN',
		$servingid = 0,
		$parent_transactionid = 0,
		$var_int1 = 0,
		$this_is_commission = false,
		$finish_transaction = true,
		$created = 'NOW()',
		$use_memory_table = false,
		$paid = '',
		$currency = 'USD'
		)
	{
		
	}
	
	function update_status($status = 'A', $make_reversal = true, $type = '', $commission = null, $payment_status = '')
	{
		$variables = get_api_value('transaction_update_status', '', array('transactionid' => $this->transactionid, 'status' => $status, 'make_reversal' => $make_reversal, 'type' => $type, 'commission' => $commission, 'payment_status' => $payment_status));
		$this->read_data($this->transactionid);
		return false;
	}
	
	function make_withdrawal($amount, $transaction_description = 'Money Withdraw', $note_to_franchisee = '', $charge_withdrawal_fee = true, $payout_is_already_done = false, $currency = 'USD', $payoutoption = 'paypal', $target_address, $start_n_fin_transaction = 1)
	{
		
	}
	
	function charge_fee($amount, $description = 'Monthly Fee', $var_int1 = 0, $userid = '', $type = 'FE')
	{
		
	}
	
	function generate_CRC($insert_values)
	{
		
	}
		
	function insert_transaction($insert_values, $use_memory_table = false, $calculate_balance = true, $table_name = TABLE_TRANSACTIONS, $CRC_value = '')
	{
		
	}
	
	function adjust_balance($userid = '', $currency = 'USD')
	{
		
	}


	function cancel_all_pending($userid = '')
	{
		
	}
	
	function make_purchase(
		$amount, 
		$item_number, 
		$invoice, 
		$paypal_txn_id, 
		$client_email,
		$client_firstname,
		$client_lastname,
		$client_address,
		$client_city,
		$client_state,
		$client_zip,
		$client_country,
		$quantity,
		$status = 'A', 
		$type = 'SA', 
		$will_active_in_days = 0, 
		$userid = 0, 
		$royalty = SALES_ROYALTY,
		$check_pay_attempts = true,
		$servingid = 0,
		$send_email_to_customer = true,
		$send_email_to_franchise = true,
		$client_note = '',
		$purchase_for_money = true,
		$its_debug = false,
		$paypal_account = '',
		$custom_data = '',
		$payprocessor = 'paypal',
		$payment_status = '',
		$payment_fee = 0,
		$currency = 'usd',
		$user_protected = 0,
		$user_address_confirmed = 0,
		$user_payer_id = '',
		$user_verified = 0,
		$user_country = ''
		)
	{
		
	}
	
	function change_paid_date()
	{
		
	}
	
	function move_old_to_archive($userid = '', $owner = NULL)
	{
		
	}
	
	function process_tmp_transactions($balance, $owner = NULL, $userid = '')
	{
		
	}

	function get_last_transactionid($table_name = TABLE_TRANSACTIONS, $field_name = 'transactionid')
	{
		
	}
	
	function change_date_will_active($date_will_active)
	{
		$variables = get_api_value('transaction_change_date_will_active', '', array('transactionid' => $this->transactionid, 'date_will_active' => $date_will_active));
		if ( $variables ) {
			foreach($variables as $akey => $aval) {
				$this->$akey = base64_decode($aval);
				$this->variables[$akey] = base64_decode($aval);
			}
			return true;
		}
		return false;
	}
	
	function update_commission($commission)
	{
		
	}

	function cancel_purchase()
	{
		$variables = get_api_value('transaction_cancel_purchase', '', array('transactionid' => $this->transactionid));
		$this->read_data($this->transactionid);
		return false;
	}

	function reprocess_purchase($purchase_for_money = true)
	{
		
	}
}	

?>