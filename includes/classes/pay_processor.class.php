<?php
//define('BITCOIN_WALLET_USERNAME', 'djdjhd');
//define('BITCOIN_WALLET_PASSWORD', 'jhdjhg2765');
//define('BITCOIN_WALLET_HOST', '5.135.181.92');
//define('BITCOIN_WALLET_PORT', '8332');

class Crypto_wallet_RPC
{
    // Configuration options
    private $username;
    private $password;
    private $proto;
    private $host;
    private $port;
    private $url;
    private $CACertificate;

    // Information and debugging
    public $status;
    public $error;
    public $raw_response;
    public $response;
	public $this_server_is_wallet = false;
	public $crypto_name;

    private $id = 0;

    public function __construct($a_crypto_name)
    {
		//$username, $password, $host = 'localhost', $port = 8332, $url = null
		switch ( strtolower($a_crypto_name) ) {
			case 'btc' :
				$a_crypto_name = 'bitcoin';
			break;
			case 'ltc' :
				$a_crypto_name = 'litecoin';
			break;
		}
		$this->crypto_name   = strtoupper($a_crypto_name);
        $this->username      = defined($this->crypto_name.'_WALLET_USERNAME')?constant($this->crypto_name.'_WALLET_USERNAME'):'';
        $this->password      = constant($this->crypto_name.'_WALLET_PASSWORD');
        $this->host          = 'localhost';//constant($this->crypto_name.'_WALLET_HOST');
        $this->port          = constant($this->crypto_name.'_WALLET_PORT');
        //$this->url           = $this->crypto_name.'_WALLET_URL';

        // Set some defaults
        $this->proto         = 'http';
        $this->CACertificate = null;
		//ini_set('default_socket_timeout', 2);
		$this->this_server_is_wallet = constant($this->crypto_name.'_WALLET_HOST') == $_SERVER['SERVER_ADDR'];
		//echo "this_server_is_wallet: {$this->this_server_is_wallet}, ".$_SERVER['SERVER_ADDR'].", ".constant($this->crypto_name.'_WALLET_HOST').""; exit;
    }

    public function setSSL($certificate = null)
    {
        $this->proto         = 'https'; // force HTTPS
        $this->CACertificate = $certificate;
    }

    public function __call($method, $params)
    {
		//return array('success' => 0, 'message' => 'crypto:'.$this->crypto_name, 'values' => $params);
		if (empty($this->username))
			return array('success' => 0, 'message' => 'Error: no such crypto: '.$this->crypto_name, 'values' => '');
		
		$this->status       = null;
        $this->error        = null;
        $this->raw_response = null;
        $this->response     = null;
		
		if ( !$this->this_server_is_wallet ) {
			return make_api_request('cryptwallet_call', '', array('crypto_name' => $this->crypto_name, 'method' => $method, 'params' => bin2hex(json_encode($params))));
		}
        // If no parameters are passed, this will be an empty array
		$params = array_values($params[0]);
		
        // The ID should be unique for each call
        $this->id++;

        // Build the request, it's ok that params might have any empty array
        $request = json_encode(array(
            'method' => $method,
            'params' => $params,
            'id'     => $this->id
        ));

        $curl    = curl_init("{$this->proto}://{$this->host}:{$this->port}/");
        $options = array(
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $request
        );

        // This prevents users from getting the following warning when open_basedir is set:
        // Warning: curl_setopt() [function.curl-setopt]:
        //   CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set
        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }

        if ($this->proto == 'https') {
            // If the CA Certificate was specified we change CURL to look for it
            if (!empty($this->CACertificate)) {
                $options[CURLOPT_CAINFO] = $this->CACertificate;
                $options[CURLOPT_CAPATH] = DIRNAME($this->CACertificate);
            } else {
                // If not we need to assume the SSL cannot be verified
                // so we set this flag to FALSE to allow the connection
                $options[CURLOPT_SSL_VERIFYPEER] = false;
            }
        }
        
		curl_setopt_array($curl, $options);

        // Execute the request and decode to an array
        $this->raw_response = curl_exec($curl);
        $this->response     = json_decode($this->raw_response, true);

        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // If there was no error, this will be an empty string
        $curl_error = curl_error($curl);

        curl_close($curl);

        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if ($this->response['error']) {
            // If bitcoind returned an error, put that in $this->error
            $this->error = $this->response['error']['message'];
        } elseif ($this->status != 200) {
            // If bitcoind didn't return a nice error message, we need to make our own
            switch ($this->status) {
                case 400:
                    $this->error = 'HTTP_BAD_REQUEST';
                    break;
                case 401:
                    $this->error = 'HTTP_UNAUTHORIZED';
                    break;
                case 403:
                    $this->error = 'HTTP_FORBIDDEN';
                    break;
                case 404:
                    $this->error = 'HTTP_NOT_FOUND';
                    break;
            }
        }
        if ($this->error)
            return array('success' => 0, 'message' => tep_sanitize_string($this->error), 'values' => '');
        return array('success' => 1, 'message' => '', 'values' => $this->response['result']);
    }
}

class Pay_processor {
	var $crypto_name = '';
	var $crypto_symbol = '';
	var $symbol = '';
	var $adress_must_starts_from = array();
	var $digits = 6;
	const COIN = 100000000;

	function get_miners_fee()
	{
		$miners_fee_file_name = $this->crypto_name.'_MINERS_FEE';
		if ( is_file_variable_expired($miners_fee_file_name, 60) ) {
			$current_miners_fee = $this->get_minimum_fee_per_byte() * 200 * 2 / 100000000;
			update_file_variable($miners_fee_file_name, $current_miners_fee);
		}
		else {
			$current_miners_fee = get_file_variable($miners_fee_file_name);
		}
		return $current_miners_fee;
	}

	function get_minimum_fee_per_byte()
	{
		// It is just dummy value. Real value counts in the particular currency class
		return 100; // in satoshes
	}

	static function get_crypto_currency_by_address($address)
	{
		if ( $address[0] == '1' || $address[0] == '3' )
			return 'bitcoin';
		if ( $address[0] == 'L' )
			return 'litecoin';
	}

	static function get_crypto_currency_by_name($name)
	{
		switch ( strtolower($name) ) {
			case 'btc' :
			case 'bitcoin' :
				return new Bitcoin();
			break;
			case 'ltc' :
			case 'litecoin' :
				return new Litecoin();
			break;
		}
		return false;
	}
	
	function validate_address($address)
	{
		$crypto_wallet = new Crypto_wallet_RPC($this->crypto_name);
		if ( $crypto_wallet ) {
			$res_arr = $crypto_wallet->validateaddress([$address]);
			//var_dump($res_arr); exit;
			if ($res_arr['success'])
				return $res_arr['values']['isvalid'];
		}
		if ( strlen($address) < 27 || strlen($address) > 34 )
			return false;
		foreach ($this->adress_must_starts_from as $first_letter)
			if ( $address[0] == $first_letter )
				return true;
		return false;
	}
	
	function get_unspent_outs($address)
	{
		$crypto_wallet = new Crypto_wallet_RPC($this->crypto_name);
		if ( $crypto_wallet->this_server_is_wallet ) {
			$already_processing = false;
			//$res_arr = $crypto_wallet->scantxoutset(['status',  ["addr($address)", ["desc" => "addr($address)", "range" => 1000]]]);
			//if ( $res_arr['values']['progress'] )
				//$already_processing = true;
			
			$file_var = 'unspent_outs_'.$address;
			$res = get_file_variable($file_var);
			if ( !$already_processing && is_file_variable_expired($file_var, 120) ) {
				update_file_variable('get_unspent_outs_'.$address, $address);
				//delete_file_variable($file_var);
			}
			return $res;
		}
		else {
			$res_arr = make_api_request('cryptwallet_call', '', array('crypto_name' => $this->crypto_name, 'method' => 'get_unspent_outs', 'params' => bin2hex(json_encode([$address]))));
			if ($res_arr['success'])
				return json_decode($res_arr['values'], true);
		}
		return false;
	}

	function get_balance($address)
	{
		try {
			$response = $this->get_unspent_outs($address);
			if ($response['success']) {
				return $response['total_amount'];
			}
		} catch (Exception $e) {}
		try {
			$s = file_get_contents('https://chain.so/api/v2/get_address_balance/'.$this->crypto_symbol.'/'.$address);
			$response = json_decode($s);
			if ( $response->status == "success" && isset($response->data->confirmed_balance) || isset($response->data->unconfirmed_balance) )
				return (float)$response->data->confirmed_balance + (float)$response->data->unconfirmed_balance;
			else {
				$b = file_get_contents('https://blockchain.info/q/addressbalance/'.$address.'?confirmations=0');
				if ( strlen($b) > 0 && isInteger($b) )
					return intval($b) / 100000000;
				else {
					$b = file_get_contents('https://blockexplorer.com/api/addr/'.$address.'/balance');
					if ( strlen($b) > 0 && isInteger($b) ) {
						$s = file_get_contents('https://blockexplorer.com/api/addr/'.$address.'/unconfirmedBalance');
						if ( strlen($s) > 0 && isInteger($s) )
							return (intval($b) + intval($s)) / 100000000;
					}
				}
			}
			return false;
		} catch (Exception $e) { 
			return false;
		}
	}

	function get_java_code_to_check_address()
	{
		return '
		<script type="text/javascript" src="/javascript/sha256.js"></script> 
		<script type="text/javascript" src="/javascript/BigInt.js"></script> 
		<SCRIPT LANGUAGE="JavaScript">
		function check_ctypto_address(address) {
			try	{
				var decoded = base58_decode(address);     
				if (decoded.length != 25) 
					return false;

				var cksum = decoded.substr(decoded.length - 4); 
				var rest = decoded.substr(0, decoded.length - 4);  

				var good_cksum = hex2a(sha256_digest(hex2a(sha256_digest(rest)))).substr(0, 4);

				if (cksum != good_cksum) 
					return false;
				return true;
			}
			catch(error){
				return false;
			}
		}

		function base58_decode(string) {
		  var table = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
		  var table_rev = new Array();

		  var i;
		  for (i = 0; i < 58; i++) {
			table_rev[table[i]] = int2bigInt(i, 8, 0);
		  } 

		  var l = string.length;
		  var long_value = int2bigInt(0, 1, 0);  

		  var num_58 = int2bigInt(58, 8, 0);

		  var c;
		  for(i = 0; i < l; i++) {
			c = string[l - i - 1];
			long_value = add(long_value, mult(table_rev[c], pow(num_58, i)));
		  }

		  var hex = bigInt2str(long_value, 16);  

		  var str = hex2a(hex);  

		  var nPad;
		  for (nPad = 0; string[nPad] == table[0]; nPad++);  

		  var output = str;
		  if (nPad > 0) output = repeat("\0", nPad) + str;

		  return output;
		}

		function hex2a(hex) {
			var str = "";
			for (var i = 0; i < hex.length; i += 2)
				str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
			return str;
		}
		
		function pow(big, exp) {
			if (exp == 0) return int2bigInt(1, 1, 0);
			var i;
			var newbig = big;
			for (i = 1; i < exp; i++) {
				newbig = mult(newbig, big);
			}

			return newbig;
		}

		function repeat(s, n){
			var a = [];
			while(a.length < n){
				a.push(s);
			}
			return a.join("");
		}
		</SCRIPT>
		';
	}

	function get_priority_fee_in_satoshi($transaction_size = 400, $priority = 'slow')
	{
		if (empty($transaction_size)) {
			if ( is_file_variable_expired($this->crypto_name.'_average_transaction_size', 60 * 24) ) {
				$data = make_api_request('get_average_transaction_size', '', ['crypto_name' => $this->crypto_name], '', null, false, 1);
				if ( $data['success'] )
					update_file_variable($this->crypto_name.'_average_transaction_size', $data['values']);
			}
			$transaction_size = get_file_variable($this->crypto_name.'_average_transaction_size');
			if (empty($transaction_size))
				$transaction_size = 400;
		}
		$result = $this->get_minimum_fee_per_byte() * $transaction_size;
		switch ($priority) {
			case 'slow': $result = round($result * 0.5); break;
			case 'fast': $result = round($result * 1.3); break;
		}
		try {
			$priority_fee_file_name = $this->crypto_name.'_'.$priority.'_PRIORITY_FEE';
			if ( is_file_variable_expired($priority_fee_file_name, 2) ) {
				$data = make_api_request('get_priority_fee_in_satoshi', '', ['crypto_name' => $this->crypto_name, 'transaction_size' => 1, 'priority' => $priority], '', null, false, 1);
				if ( $data['success'] )
					update_file_variable($priority_fee_file_name, $data['values']);
			}
			$data = get_file_variable($priority_fee_file_name);
			//if ( defined('DEBUG_MODE') ) echo "{$this->crypto_name} priority_fee_in_satoshi for one byte: $data, transaction_size: $transaction_size = ".($data * $transaction_size)."<br>";
			if ( $data > 0 )
				return $data * $transaction_size;
		}
		catch (Exception $e) {}
		return $result;
	}

	public static function to_satoshis( $btcAmount )
    {
        return $btcAmount * self::COIN;
    }

	public static function from_satoshis( $amount_in_satoshis )
    {
        return $amount_in_satoshis / self::COIN;
    }


}

?>