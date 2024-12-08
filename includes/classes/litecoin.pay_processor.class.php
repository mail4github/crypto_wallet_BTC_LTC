<?php
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
if ( !function_exists('tep_sanitize_string') ) require_once('general.php');

class Litecoin extends Pay_processor {
	var $server_addr = '';
	var $adress_must_starts_from = array('L', 'M', '3');
	var $pattern = '^[LlM3][a-zA-Z0-9]{26,44}$';
	
	public function __construct($password = '') 
	{
		ini_set('default_socket_timeout', 5);
		$this->crypto_name = "litecoin";
		$this->crypto_symbol = "LTC";
		$this->symbol = '&#321;';
		$this->digits = 3;
	}
	
	function get_exchange_rate($currency = 'USD', $use_cash = true, $source = 0)
	{
		$exchange_rate_file = $this->crypto_name.'_exchange_rate_'.$currency;
		if (!is_file_variable_expired($exchange_rate_file, 120) && $use_cash)
			return get_file_variable($exchange_rate_file);
		if (!is_file_variable_expired($this->crypto_name.'_exchange_rate_requested_'.$currency, 2) && $use_cash)
			return get_file_variable($exchange_rate_file);
		update_file_variable($this->crypto_name.'_exchange_rate_requested_'.$currency, '1');
		if (empty($source))
			$source = rand(1, 2);
		if (defined('DEBUG_MODE')) echo "{$this->crypto_name} <font color=#ff0000>source: $source</font><br>";
		switch ($source) {
			case 1:
			$result = file_get_contents('https://chain.so/api/v2/get_price/'.$this->crypto_symbol.'/'.$currency);
			if ( $result ) {
				$res_array = json_decode($result);
				$number_of_exchanges = 0;
				$sum_price = 0;
				if ( $res_array->status == 'success' ) {
					foreach ( $res_array->data->prices as $exchange ) {
						$number_of_exchanges++;
						$sum_price = $sum_price + $exchange->price;
					}
				}
				if ( $number_of_exchanges > 0 && $sum_price > 0 ) {
					$rate = $sum_price / $number_of_exchanges;
					if (defined('DEBUG_MODE')) echo "chain.so {$this->crypto_name} rate: $rate<br>";
					update_file_variable($exchange_rate_file, $rate);
					return $rate;
				}
			}
			case 2:
			$result = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym='.$this->crypto_symbol.'&tsyms='.$currency);
			if ( $result ) {
				$res_array = json_decode($result, true);
				if ( $res_array[strtoupper($currency)] > 0 ) {
					$rate = $res_array[strtoupper($currency)];
					if (defined('DEBUG_MODE')) echo "cryptocompare.com {$this->crypto_name} rate: $rate<br>";
					update_file_variable($exchange_rate_file, $rate);
					return $rate;
				}
			}
			/*case 3:
			$result = file_get_contents('https://api.coinmarketcap.com/v1/ticker/'.$this->crypto_name.'/');
			if ( $result ) {
				$res_array = json_decode($result);
				$price_usd = 'price_'.strtolower($currency);
				if ( $res_array[0]->{$price_usd} > 0 ) {
					$rate = $res_array[0]->{$price_usd};
					if (defined('DEBUG_MODE')) echo "coinmarketcap.com {$this->crypto_name} rate: $rate<br>";
					update_file_variable($exchange_rate_file, $rate);
					return $rate;
				}
			}
			break;*/
		}
		return get_file_variable($exchange_rate_file);
	}
	
	function exchange($amount, $currency = 'USD')
	{
		return $amount / $this->get_exchange_rate($currency) * 0.9;
	}

	function get_last_received_to_address($addr, $last_payment_only = false, $hours_to_search = 0, $amount_to_search = 0, &$time, &$confirmations, &$spent, &$hash, &$trans_fee, &$sat_per_B)
	{
		$received = 0;
		$confirmations = 0;
		$spent = 0;
		if ( !empty($amount_to_search) )
			$last_payment_only = 1;
		try {
			$s = file_get_contents('https://chain.so/api/v2/get_tx_received/'.$this->crypto_symbol.'/'.$addr);
			$transactions = json_decode($s);
			foreach ($transactions->data->txs as $transaction) {
				$time = (int)$transaction->time;
				if ( (float)$transaction->value > 0 && ( empty($hours_to_search) || $time > time() - $hours_to_search*60*60 ) && (empty($amount_to_search) || ( (float)$transaction->value > $amount_to_search * 0.95 && (float)$transaction->value < $amount_to_search * 1.05 ) ) ) {
					if (isset($transaction->confirmations))
						$confirmations = $transaction->confirmations;
					$received = $received + (float)$transaction->value;
					$hash = $transaction->script_hex;
					if ( $last_payment_only )
						return $received;
				}
			}
			$s = file_get_contents('https://chain.so/api/v2/get_tx_spent/'.$this->crypto_symbol.'/'.$addr);
			$transactions = json_decode($s);
			foreach ($transactions->data->txs as $transaction) {
				$spent = $spent + (float)$transaction->value;
			}
		} 
		catch (Exception $e) {
			try {
				$s = file_get_contents('https://chainz.cryptoid.info/'.strtolower($this->crypto_symbol).'/api.dws?q=getreceivedbyaddress&a='.$addr);
				return (float)$s;
			} catch (Exception $e) {}
		}
		return $received;
	}


	function get_javascript_to_generate_address()
	{
		
		return file_get_contents(DIR_WS_INCLUDES.'generate_litecoin_addr.php').'
		<script type="text/javascript">
		var litecoin_privWif = "";
		function generate_litecoin_address(key)
		{
			var bytes = Crypto.SHA256(key, { asBytes: true });
			var btcKey = new Litecoin.ECKey(bytes);
			var isCompressed = 0;
			btcKey.setCompressed(isCompressed);
			crypto_address = btcKey.getBitcoinAddress();
			litecoin_privWif = btcKey.getBitcoinWalletImportFormat();
			return crypto_address;
		}
		function get_litecoin_privWif()
		{
			return litecoin_privWif;
		}
		</script>
		';
	}
}

?>