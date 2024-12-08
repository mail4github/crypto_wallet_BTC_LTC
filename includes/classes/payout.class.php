<?php
require_once(DIR_WS_CLASSES.'user.class.php');

class Payout
{
	var $payoutid = 0;
	var $variables = array();
	var $userid = 0;
	var $owner;
	var $transactionid;
	
	function read_data($payoutid = '', $transactionid = '')
	{
		if ( !empty($payoutid) )
			$this->payoutid = (int)$payoutid;
		if ( !empty($transactionid) )
			$this->transactionid = (int)$transactionid;
		if ( !empty($this->payoutid) || !empty($this->transactionid) ) {
			$variables = get_api_value('payout_read_data', '', array('transactionid' => $this->transactionid, 'payoutid' => $this->payoutid));
			if ( $variables ) {
				foreach($variables as $akey => $aval) {
					$this->$akey = base64_decode($aval);
					$this->variables[$akey] = base64_decode($aval);
				}
				return true;
			}
		}
		return false;
	}
	
	function create_owner($userid = '')
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
	
	function finish_payment($status = 'A', $pay_processor_email = '', $sales_manager_id = 0, $send_email = false, $number_of_attempts_to_pay = '')
	{
		return get_api_value('payout_finish', '', array('payoutid' => $this->payoutid, 'status' => $status, 'pay_processor_email' => $pay_processor_email, 'number_of_attempts_to_pay' => $number_of_attempts_to_pay));
	}
	
	function resend_payout($payoutid = '', $transactionid_to_decline = '', $resend_anyway = false)
	{
		if ( !empty($payoutid) )
			$this->payoutid = (int)$payoutid;
		return get_api_value('payout_resend', '', array('payoutid' => $this->payoutid, 'transactionid_to_decline' => $transactionid_to_decline, 'resend_anyway' => $resend_anyway));
	}

	function get_status_name($status = '')
	{
		if (empty($status))
			$status = $this->status;
		switch ($status) {
			case 'A': return '<font color="#008000">completed</font>';
			case 'P': return 'processing';
			case 'D': return '<font color="#FF0000">declined</font>';
		}
	}
	
	function decline($decline_transaction = true, $decline_anyway = 1)
	{
		return get_api_value('payout_cancel', '', array('payoutid' => $this->payoutid));
	}
	
	function calculate_withdrawal_fee($amount, $payoutoption = '')
	{
		return get_api_value('payout_calculate_withdrawal_fee', '', array('amount' => $amount, 'payoutoption' => $payoutoption));
	}
	
	function amount()
	{
		return get_api_value('payout_get_amount', '', array('payoutid' => $this->payoutid));
	}

	function get_payout_option()
	{
		parse_str($this->adminnote, $payout_option);
		return $payout_option["payopt"];
	}

}
?>