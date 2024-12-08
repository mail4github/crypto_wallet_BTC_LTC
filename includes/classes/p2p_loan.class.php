<?php
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');

class P2p_loan
{
	var $transactions_global_transactionid = '';
	var $variables = array();
	var $period_in_days = LOAN_PP_INTERVAL;
	var $crypto = '';
	
	function read_data($transactions_global_transactionid = '', $userid = '')
	{
		$this->crypto = new Bitcoin();
		
		if ( !empty($transactions_global_transactionid) )
			$this->transactions_global_transactionid = $transactions_global_transactionid;
		else
		if ( !empty($userid) ) {
			$this->userid = $userid;
		}
		if ( !empty($this->transactions_global_transactionid) || !empty($this->userid) ) {
			$variables = get_api_value('p2p_loan_read_data', '', array('transactions_global_transactionid' => $this->transactions_global_transactionid, 'borrower_userid' => $this->userid));
			if ( $variables ) {
				foreach($variables as $akey => $aval) {
					$this->$akey = base64_decode($aval);
					$this->variables[$akey] = base64_decode($aval);
				}
				$this->assets = json_decode($this->assets_data);
				return true;
			}
		}
		return false;
	}
}
?>