<?php
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'stock.class.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');

class Share
{
	var $shareid = 0;
	var $variables = array();
	/*
	function read_data($shareid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($shareid) )
			$this->shareid = $shareid;
		if ( !empty($this->shareid) ) {
			$q = 'SELECT *, 
				TIMESTAMPDIFF( MINUTE, created, NOW() ) AS created_minutes_ago,
				TIMESTAMPDIFF( DAY, created, NOW() ) AS created_days_ago,
				TIMESTAMPDIFF( DAY, date_dividend_paid, NOW() ) AS dividend_paid_days_ago
				FROM '.TABLE_SHAREHOLDERS.' WHERE shareid = "'.$this->shareid.'" LIMIT 1 ';
			
			if ( $this->variables = tep_db_perform_one_row($q, !empty($_COOKIE['debug'])) ) {
				foreach( $this->variables as $akey => $aval ) 
					$this->$akey = $aval;
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if ( empty($this->shareid) )
			return 'Error: cannot delete Share.';
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		tep_db_perform_one_row('DELETE FROM '.TABLE_SHAREHOLDERS.' WHERE shareid = "'.$this->shareid.'"');
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
	
	function get_stock()
	{
		if ( !$this->stock ) {
			$this->stock = new Stock();
			$this->stock->read_data($this->stockid);
		}
		return $this->stock;
	}
	
	function delete_all_user_shares($userid = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( !empty($userid) )
			$this->userid = $userid;
		$q = 'SELECT shareid FROM '.TABLE_SHAREHOLDERS.' WHERE userid = "'.$this->userid.'" ';
		if ( $r = tep_db_query($q, 'db_link', !empty($_COOKIE['debug']) ) ) {
			while ( $row = tep_db_fetch_array($r) ) {
					$share = new Share();
					if ( $share->read_data($row['shareid']) )
						$share->delete();
			}
		}
	}
	
	function get_list($sort_by = 'created ASC', $condition = 'NOT disabled AND NOT banned', $limit = '')
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( empty($sort_by) )
			$sort_by = 'created ASC';
	
		$shares_objects = array();
		$q = 'SELECT shareid FROM '.TABLE_SHAREHOLDERS.' WHERE '.$condition.' ORDER BY '.$sort_by.' '.$limit;
		if ( $r = tep_db_query($q) ) {
			while ( $row = tep_db_fetch_array($r) ) {
				$share = new Share();
				if ( $share->read_data($row['shareid']) )
					$shares_objects[] = $share;
			}
		}
		return $shares_objects;
	}
	
	function change_owner($paid, $new_owner_id, $new_owner_websiteid = 1, $purchaseid = 0, $description = '', $make_transaction = true)
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		
		if ( $make_transaction && $this->user_websiteid.$this->userid != $new_owner_websiteid.$new_owner_id && $this->userid != MANAGER_ID ) {
			$transaction = new Transaction();
			
			$insert_values = array(
				'userid' => $this->userid,
				'user_websiteid' => $this->user_websiteid,
				'purchaseid' => $purchaseid,
				'_created' => 'DATE_ADD(NOW(), INTERVAL 1 SECOND)',
				'_modified' => 'DATE_ADD(NOW(), INTERVAL 1 SECOND)',
				'modified_websiteid' => WEBSITE_NUMBER,
				'type' => 'SS',
				'commission' => $this->price <= $paid ? $this->price : $paid,
				'status' => 'P',
				'description' => substr('Sell '.WORD_MEANING_SHARE.' of '.$this->get_stock()->name.' ('.$this->get_stock()->stockid.')', 0, 128),
				'_date_will_active' => 'NOW()',
				'balance' => 0,
				'var_int1' => $this->shareid,
			);
			if ( !$transaction->insert_transaction($insert_values, false, false, TABLE_COMMON_TRANSACTIONS) )
				return false;
		}
		$update_values = array(
			'userid' => $new_owner_id,
			'user_websiteid' => $new_owner_websiteid,
			'price' => 0,
			'_date_dividend_paid' => 'NOW()',
			'_modified' => 'NOW()',
			'modified_websiteid' => WEBSITE_NUMBER,
		);
		return tep_db_perform(TABLE_SHAREHOLDERS, $update_values, 'update', 'shareid = "'.$this->shareid.'"', 'db_link', !empty($_COOKIE['debug']));
	}

	function generate_CRC($insert_values)
	{
		return hash('md5', $insert_values['shareid'].$insert_values['userid'].$insert_values['purchaseid'].number_format($insert_values['paid'], 2).date('Y-m-d') );
	}
	
	function insert_transaction($insert_values)
	{
		$insert_values['CRC'] = substr($this->generate_CRC($insert_values), 0, 128);
		global $abort_scrypt_on_db_error;
		$abort_scrypt_on_db_error = 0;
		for ($i = 1; $i <= 5; $i++) {
			if ( tep_db_perform(TABLE_SHARE_TRANSACTIONS, $insert_values, 'insert', '', 'db_link', !empty($_COOKIE['debug']) ) )
				break;
			if ( !empty($_COOKIE['debug']) ) echo 'Attempt: '.$i.'<br>';
		}
	}
	
	function pay_dividend($make_transaction = true)
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		if ( $make_transaction ) {
			if ( $this->get_stock()->userid != MANAGER_ID ) {
				$transaction = new Transaction();
				$insert_values = array(
					'userid' => $this->get_stock()->userid,
					'user_websiteid' => $this->get_stock()->user_websiteid,
					'purchaseid' => 0,
					'_created' => 'DATE_ADD(NOW(), INTERVAL 1 SECOND)',
					'_modified' => 'DATE_ADD(NOW(), INTERVAL 1 SECOND)',
					'modified_websiteid' => WEBSITE_NUMBER,
					'type' => 'FE',
					'commission' => -($this->get_stock()->dividend),
					'status' => 'P',
					'description' => substr('Pay dividend for '.$this->get_stock()->name, 0, 128),
					'_date_will_active' => 'NOW()',
					'balance' => 0,
					'var_int1' => $this->shareid,
				);
				$transaction->insert_transaction($insert_values, false, false, TABLE_COMMON_TRANSACTIONS);
				if (!empty($_COOKIE['debug'])) echo 'fee to: '.$this->get_stock()->userid.', '.$this->get_stock()->user_websiteid.'<br>';
			}
			$transaction = new Transaction();
			$transaction->add_bonus($this->userid, $this->get_stock()->dividend, 'A', 0, false, 'Dividend from '.$this->get_stock()->name.' ('.$this->get_stock()->stockid.')', 0, 'DV', 0, 0, $this->shareid, true);
		}
		$q = 'UPDATE '.TABLE_SHAREHOLDERS.' SET date_dividend_paid = NOW() WHERE shareid = "'.$this->shareid.'" ';
		tep_db_perform_one_row($q, !empty($_COOKIE['debug']));

		return $this->get_stock()->dividend;
	}

	function sell($price)
	{
		if ( ! tep_db_is_connected() ) 
			tep_db_connect();
		$q = 'UPDATE '.TABLE_SHAREHOLDERS.' SET price = '.round($price, 4).' WHERE shareid = "'.$this->shareid.'" ';
		tep_db_perform_one_row($q, !empty($_COOKIE['debug']));
	}

	function get_last_transactionid($table_name = TABLE_SHARE_TRANSACTIONS)
	{
		if ( $row = tep_db_perform_one_row('SELECT transactionid AS value FROM '.$table_name.' ORDER BY created DESC LIMIT 1', !empty($_COOKIE['debug'])) ) 
			return $row['value'];
		return time() + rand(0, 100);
	}*/
}

?>
