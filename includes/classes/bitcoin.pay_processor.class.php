<?php
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
if ( !function_exists('tep_sanitize_string') ) require_once('general.php');

class Bitcoin extends Pay_processor {
	var $adress_must_starts_from = array('1', '3', 'b');
	var $pattern = '^[13b][a-zA-Z0-9]{26,44}$';
	
	public function __construct($password = '') 
	{
		ini_set('default_socket_timeout', 5);
		$this->crypto_name = 'bitcoin';
		$this->crypto_symbol = 'BTC';
		$this->symbol = '&#3647;';
		$this->digits = 5;
	}
	
	function get_exchange_rate($currency = 'USD', $use_cash = true, $source = '')
	{
		$currency = strtolower($currency);
		$exchange_rate = 0;
		$exchange_rate_file = $this->crypto_name.'_exchange_rate_'.$currency;
		if (!is_file_variable_expired($exchange_rate_file, 120) && $use_cash)
			return get_file_variable($exchange_rate_file);
		if (!is_file_variable_expired($this->crypto_name.'_exchange_rate_requested_'.$currency, 2) && $use_cash)
			return get_file_variable($exchange_rate_file);
		update_file_variable($this->crypto_name.'_exchange_rate_requested_'.$currency, '1');
		try {
			if (empty($source))
				$source = rand(1, 2);
			if (defined('DEBUG_MODE')) echo "{$this->crypto_name} <font color=#ff0000>source: $source</font><br>";
			switch ($source) {
				case 1:
				$result = file_get_contents('https://api.coindesk.com/v1/bpi/currentprice.json');
				if ( $result ) {
					$currency = strtoupper($currency);
					$res_array = json_decode($result, true);
					$rate = $res_array['bpi'][$currency]['rate_float'];
					if ( !empty($rate) && $rate > 0 ) {
						if (defined('DEBUG_MODE')) echo "coindesk {$this->crypto_name} rate: $rate<br>";
						update_file_variable($exchange_rate_file, $rate);
						return $rate;
					}
				}
				case 2:
				$result = file_get_contents('http://blockchain.info/ticker');
				if ( $result ) {
					$currency = strtoupper($currency);
					$res_array = json_decode($result, true);
					$rate = $res_array[$currency]['sell'];
					if ( !empty($rate) && $rate > 0 ) {
						if (defined('DEBUG_MODE')) echo "blockchain {$this->crypto_name} rate: $rate<br>";
						update_file_variable($exchange_rate_file, $rate);
						return $rate;
					}
				}
			}
		}
		catch (Exception $e) {}
		return get_file_variable($exchange_rate_file);
	}
	
	function exchange($amount, $currency = 'USD')
	{
		$exchange_rate = $this->get_exchange_rate($currency);
		return ($exchange_rate > 0?$amount / $exchange_rate * 1 : 0);
	}

	function get_last_received_to_address($addr, $last_payment_only = false, $hours_to_search = 0, $amount_to_search = 0, &$time, &$confirmations, &$spent, &$hash, &$trans_fee, &$sat_per_B)
	{
		$received = false;
		$confirmations = 0;
		$spent = 0;
		if ( !empty($amount_to_search) )
			$last_payment_only = 1;
		if ( !$last_payment_only && !$hours_to_search && !$amount_to_search ) {
			$balance = $this->get_balance($addr);
			if ( $balance === false )
				return false;
			$this->get_last_received_to_address($addr, true, 0, 0, $time, $confirmations);
			if ( $balance == 0 ) {
				if ( $confirmations > 0 ) {
					$balance = 0.0001;
					$spent = $balance;
				}
			}
			return $balance;
		}
		try {
			$blockcount = get_file_variable('blockcount');
			if ( is_file_variable_expired('blockcount', 1) ) {
				$b = file_get_contents('https://blockchain.info/q/getblockcount');
				if ( (int)$b > 0 ) {
					$blockcount = (int)$b;
					update_file_variable('blockcount', $blockcount);
				}
			}
			
			$s = file_get_contents('https://blockchain.info/address/'.$addr.'?format=json&limit=10');
			$transactions = json_decode($s);
			if ( $transactions->address != $addr )
				throw new Exception('');
			foreach ($transactions->txs as $transaction) {
				foreach ($transaction->out as $input) {
					if ( $input->addr == $addr && ( empty($hours_to_search) || $transaction->time > time() - $hours_to_search*60*60 ) && (empty($amount_to_search) || ( $input->value > $amount_to_search * 99000000 && $input->value < $amount_to_search * 101000000 ) ) ) {
						$time = $transaction->time;
						if (isset($transaction->block_height)) {
							$confirmations = abs($blockcount - $transaction->block_height);
						}
						$received = $received + $input->value / 100000000;
						$hash = $transaction->hash;
						if ( $last_payment_only )
							break;
					}
				}
				
				$trans_received = 0;
				$trans_spent = 0;
				foreach ($transaction->inputs as $input) {
					$trans_received = $trans_received + $input->prev_out->value;
					if ( $input->prev_out->addr == $addr )
						$spent = $spent + $input->prev_out->value / 100000000;
				}
				foreach ($transaction->out as $input) 
					$trans_spent = $trans_spent + $input->value;
				$trans_fee = $trans_received - $trans_spent;
				$sat_per_B = $trans_fee / $transaction->size;
			}
		} 
		catch (Exception $e) {
			$s = file_get_contents('https://blockexplorer.com/api/txs/?address='.$addr);
			try {
				$transactions = json_decode($s);
				foreach ($transactions->txs as $transaction) {
					$time = (int)$transaction->time;
					foreach ($transaction->vin as $transaction_vin) {
						if ( $transaction_vin->addr == $addr && ( empty($hours_to_search) || $time > time() - $hours_to_search*60*60 ) ) {
							$spent = $spent + $transaction_vin->value;
						}
					}
					foreach ($transaction->vout as $transaction_vout) {
						$for_this_address = false;
						foreach ( $transaction_vout->scriptPubKey->addresses as $scriptPubKey_address ) {
							if ($scriptPubKey_address == $addr) {
								$for_this_address = true;
								break;
							}
						}
						if ( $for_this_address && (float)$transaction_vout->value > 0 && ( empty($hours_to_search) || $time > time() - $hours_to_search*60*60 ) && (empty($amount_to_search) || ( (float)$transaction_vout->value > $amount_to_search * 0.99 && (float)$transaction_vout->value < $amount_to_search * 1.01 ) ) ) {
							if (isset($transaction->confirmations))
								$confirmations = $transaction->confirmations;
							$received = $received + (float)$transaction_vout->value;
							$hash = $transaction->blockhash;
							$trans_fee = $transaction->fees;
							if ( $last_payment_only )
								return $received;
						}
					}
				}
			} catch (Exception $e) {}
		}
		return $received;
	}

	function get_spent_from_address($addr)
	{
		$spent = 0;
		try {
			$s = file_get_contents('https://blockchain.info/address/'.$addr.'?format=json&limit=10');
			$transactions = json_decode($s);
			if ( $transactions->address != $addr )
				throw new Exception('');
			
			foreach ($transactions->txs as $transaction) {
				foreach ($transaction->inputs as $input) {
					if ( $input->prev_out->addr == $addr ) {
						$spent = $spent + $input->prev_out->value / 100000000;
						return $spent;
					}
				}
			}
		} catch (Exception $e) {
			if (defined('DEBUG_MODE')) echo "Exception Error: $e <br>";
		}
		return $spent;
	}

	function get_minimum_fee_per_byte()
	{
		$res = get_file_variable(BITCOIN_CURRENT_MINERS_FEE_FILE);
		if ( is_file_variable_expired(BITCOIN_CURRENT_MINERS_FEE_FILE, 10) /*|| defined('DEBUG_MODE')*/) {
			if ( defined('bitcoin_MINERS_FEE') )
				$res = bitcoin_MINERS_FEE * 100000000 / 300;
			$source = rand(1, 2);
			//$source = 1;
			switch ($source) {
				case 1:
				try {
					$s = file_get_contents('https://bitcoinfees.earn.com/api/v1/fees/recommended');
					//if ( defined('DEBUG_MODE') ) echo "$s <br>";
					$fees = json_decode($s);
					if ($fees && $fees->hourFee > 0) {
						$res = round($fees->hourFee / 3);
						update_file_variable(BITCOIN_CURRENT_MINERS_FEE_FILE, $res);
					}
				} catch (Exception $e) {} 
				break;
				case 2:
				try {
					$s = file_get_contents('https://btc-fee.net/api.json');
					//if ( defined('DEBUG_MODE') ) echo "$s <br>";
					$fees = json_decode($s);
					if ($fees && $fees->medium > 0) {
						$res = $fees->medium;
						update_file_variable(BITCOIN_CURRENT_MINERS_FEE_FILE, $res);
					}
				} catch (Exception $e) {} 
				break;
			}
		}
		if ( $res < 3 )
			$res = 3;
		return $res;
	}

	function get_javascript_to_generate_address()
	{
		return file_get_contents(DIR_WS_INCLUDES.'generate_bitcoin_addr.php').'
		<script type="text/javascript" src="/javascript/bitcoinjs.min.js"></script>
		<script type="text/javascript">
		var bitcoin_privWif = "";
		function generate_bitcoin_address(key)
		{
			var bytes = Crypto.SHA256(key, { asBytes: true });
			var btcKey = new Bitcoin.ECKey(bytes);
			var isCompressed = 0;
			btcKey.setCompressed(isCompressed);
			crypto_address = btcKey.getBitcoinAddress();
			bitcoin_privWif = btcKey.getBitcoinWalletImportFormat();
			return crypto_address;
		}
		function get_bitcoin_privWif()
		{
			return bitcoin_privWif;
		}
		</script>
		';
	}
}

?>