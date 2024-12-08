<?php
require_once(DIR_WS_CLASSES.'widget.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');
//require_once(DIR_WS_CLASSES.'send_mail.class.php');
require_once(DIR_WS_CLASSES.'job.class.php');

class User 
{
	var $userid = '';
	var $email = '';
	var $firstname = '';
	var $lastname = '';
	var $paypalemail = '';
	var $useSession = true;
	var $user_variables = array();
	var $login_log_rec_id = 0;
	var $offer = '';
	var $hideoffer = 0;
	var $rank = 3;
	var $permissions = 'GUEST';
	var $pop_currency1 = DOLLAR_NAME;
	var $pop_currency1symbol = DOLLAR_SIGN;
	
	function read_data($aUseSession = true, $get_list_of_common_params = false, $web_page = '')
	{
		$this->useSession = $aUseSession;
		if ( $this->useSession ) {
			if ( empty($this->userid) )
				$this->userid = $_COOKIE['userid'];
			if ( empty($this->psw_hash) )
				$this->psw_hash = $_COOKIE['password'];
		}
		if ( empty($this->userid) && !empty($this->email) )
			$this->userid = $this->search_userid_by_email();
		$post_data = array(
			'read_userid' => $this->userid, 
			'email' => $this->email, 
			'firstname' => $this->firstname, 
			'lastname' => $this->lastname, 
			'paypalemail' => $this->paypalemail,
			'get_list_of_common_params' => $get_list_of_common_params,
			'web_page_to_access' => $web_page,
		);
		if ( !empty($this->psw_hash) ) {
			$post_data['userid'] = $this->userid;
			$post_data['psw_hash'] = $this->psw_hash;
		}
		$user_data = make_api_request('user_read_data', '', $post_data, $this->psw_hash);
		if ( @$user_data["success"] ) {
			foreach(@$user_data["values"] as $akey => $aval) {
				$aval = base64_decode($aval);
				$this->$akey = $aval;
				$this->user_variables[$akey] = $aval;
				if ( is_integer(strpos($akey, 'common_param_')) ) {
					if ( empty($this->tmp_list_of_common_params) )
						$this->tmp_list_of_common_params = array();
					$this->tmp_list_of_common_params[substr($akey, strlen('common_param_'))] = $aval;
				}
			}
			if ( !empty($this->tmp_list_of_common_params['photo']) )
				$this->tmp_list_of_common_params['photo'] = $this->get_photo();
			$this->permissions_arr = explode(',', $this->permissions);
			return true;	
		}
		else {
			//var_dump($user_data); exit;
			return false;
		}
	}
	
	function search_userid_by_email($email = '')
	{
		if ( !empty($email) )
			$this->email = $email;
		$user_data = make_api_request('user_read_data', '', array('read_userid' => '0', 'userid' => '1', 'email' => $this->email ));
		if ( @$user_data["success"] )
			return base64_decode($user_data['values']['userid']);
		return false;
	}

	function is_password_correct($aPassword, $manager_password = '', $hash_password = '', $hash_manager_password = '', $check_second_password = true, $hash_suffix = '', $verification_pin = '', $double_hash_password = '')
	{
		return get_api_value('user_is_password_correct', '', array('raw_password' => $aPassword, 'hash_password' => $hash_password, 'hash_suffix' => $hash_suffix, 'verification_pin' => $verification_pin, 'double_hash_password' => $double_hash_password), '', $this);
	}

	function is_loggedin()
	{
		return !empty($_COOKIE['userid']) && !empty($_COOKIE['password'])/* && $this->session_active*/;
	}
	
	function login($aPassword, $fingerprint = '', $real_user_ip = '')
	{
		$password_sign = '';
		$login_data = bin2hex($this->email."<div><div><div><div>".md5($aPassword)."<div><div><div>".$password_sign."<div>".$fingerprint."<div>");
		$user_data = make_api_request('user_login', '', ['data' => $login_data, 'real_user_ip' => $real_user_ip], '', null, true);
		if ( @$user_data["success"] ) {
			$_COOKIE['userid'] = $user_data["values"]["userid"];
			setcookie('userid', $_COOKIE['userid']);
			setcookie('cookie_vars_time', 0);
			$_COOKIE['cookie_vars_time'] = 0;
			$_COOKIE['password'] = $user_data["values"]["hash"];
			setcookie('password', $_COOKIE['password']);
			$this->psw_hash = $_COOKIE['password'];
			$this->userid = $_COOKIE['userid'];
			return true;
		}
		else {
			//var_dump($user_data); exit;
		}
		return false;
	}	

	function logout()
	{
		foreach($_SESSION as $akey => $aval) 
			$_SESSION[$akey] = '';
		session_destroy();
		setcookie('userid', '');
		setcookie('password', '');
		setcookie('cookie_vars_time', 0);
	}
	
	function update_stat_fields()
	{
		make_api_request('user_update_stat_fields', '', '', '', $this);
	}

	function set_stat_relative_to_somebody($userid)
	{
		make_api_request('user_set_stat_relative_to_somebody', '', array('relative_to' => $userid), '', $this);
	}
	
	function refresh_data()
	{
		$this->read_data($this->useSession);
	}
	
	function get_calculated_balance_from_cookie($currency = 'usd')
	{
		$currency = strtolower($currency);
		if (isset($_COOKIE['calculated_balance'.$currency]) && time() - $_COOKIE['calculated_balance_time'.$currency] < 300 ) {
			return $_COOKIE['calculated_balance'.$currency];
		}
		else {
			$bal = $this->get_calculated_balance(true, $currency);
			setcookie('calculated_balance'.$currency, $bal);
			setcookie('calculated_balance_time'.$currency, time());
			return $bal;
		}
	}
	
	function get_calculated_balance($recalculate = false, $currency = 'USD')
	{
		$user_data = make_api_request('balance', $currency, '', '', $this);
		if ( $user_data['success'] )
			return $user_data['values'][0]['amount'];
		else
			return false;
	}

	function get_list_of_common_params($read_common_params = true, $refresh_common_params = false)
	{
		if ( empty($this->tmp_list_of_common_params) || $refresh_common_params /*|| defined('DEBUG_MODE')*/) {
			//if ( defined('DEBUG_MODE') ) echo "logged: ".$this->is_loggedin()."<br>";
			if ( $this->is_loggedin() ) {
				if ($read_common_params) {
					$this->tmp_list_of_common_params = get_api_value('user_get_list_of_common_params');
					if ( $this->useSession ) {
						$_COOKIE['number_of_new_emails'] = $this->tmp_list_of_common_params['number_of_new_emails'];
						setcookie('number_of_new_emails', $_COOKIE['number_of_new_emails']);
						$_COOKIE['number_of_open_tickets'] = $this->number_of_open_tickets(true);
						setcookie('number_of_open_tickets', $_COOKIE['number_of_open_tickets']);
					}
				}
				$this->tmp_list_of_common_params['photo'] = $this->get_photo();
			}
			else {
				$guest_list_of_common_params = get_file_variable('guest_list_of_common_params');
				if ( !empty($guest_list_of_common_params) )
					$this->tmp_list_of_common_params = json_decode($guest_list_of_common_params, true);
				if ( is_file_variable_expired('guest_list_of_common_params', 10) || $refresh_common_params ) {
					$s = get_api_value('user_get_list_of_common_params', '', '', '', null, false, 1);
					if ( $s !== false && !empty($s) ) {
						//if ( defined('DEBUG_MODE') ) 
						//{var_dump($s); echo '<br><br><br>'.$s['monthly_roi']; exit;}
						file_put_contents(DIR_WS_TEMP.'monthly_roi.txt', $s['monthly_roi']);
						update_file_variable('guest_list_of_common_params', json_encode($s));
					}
					else {
						//echo "error: cannot get user_get_list_of_common_params<br>"; exit;
					}
				}
				
			}
		}
		$this->tmp_list_of_common_params['new_emails'] = $_COOKIE['number_of_new_emails'];
		$this->tmp_list_of_common_params['open_tickets'] = $_COOKIE['number_of_open_tickets'];
		$this->tmp_list_of_common_params['cur_year'] = date("Y");
		$this->tmp_list_of_common_params['this_is_mobi_version'] = defined('THIS_IS_MOBI_VERSION') ? (THIS_IS_MOBI_VERSION ? '1' : '0') : '0';
		$this->tmp_list_of_common_params['this_is_not_mobi_version'] = defined('THIS_IS_MOBI_VERSION') ? (THIS_IS_MOBI_VERSION ? '0' : '1') : '1';
		return $this->tmp_list_of_common_params;
	}
	
	function parse_common_params($text, $read_common_params = true, $refresh_common_params = false)
	{
		$text = performEmbededCodeInText($text);
		$common_params = $this->get_list_of_common_params($read_common_params, $refresh_common_params);
		foreach($common_params as $key => $val)
			$text = str_replace('{$'.$key.'}', $val, $text);
		while ( is_string($text) && is_integer(strpos($text, '{$')) ) {
			$s = get_text_between_tags($text, '{$', '}');
			if ( !empty($s) && defined(strtoupper($s)) ) {
				delete_text_between_tags($text, '{$', '}', false, constant(strtoupper($s)));
			}
			else 
				break;
		}
		return $text;
	}

	function return_to_old_interface()
	{
		return true;
	}
	
	function get_list_of_widgets($sort_by = 1, $condition = 1, $limit = 0)
	{
		$widget = new Widget();
		return $widget->get_list_of_widgets($sort_by, $condition, $limit);
	}
	
	function have_p2p_loan($count_requests_for_loan = false)
	{
		return get_api_value('user_have_p2p_loan', '', array('count_requests_for_loan' => $count_requests_for_loan), '', $this);
	}

	function have_unpaid_loan()
	{
		return get_api_value('user_have_unpaid_loan', '', '', '', $this);
	}

	function get_number_of_new_emails($refresh = false)
	{
		if ( !$this->useSession || $refresh || empty($_COOKIE['number_of_new_emails']) || $_COOKIE['number_of_new_emails_time'] < time() - 300 ) {
			$result = get_api_value('user_get_number_of_new_emails', '', '', '', $this);
			if ( $this->useSession ) {
				setcookie('number_of_new_emails', $result);
				$_COOKIE['number_of_new_emails'] = $result;
				setcookie('number_of_new_emails_time', time());
			}
			//setcookie('from', 'from API');
			return $result;
		}
		else {
			//setcookie('from', 'from session');
			return $_COOKIE['number_of_new_emails'];
		}
	}
	
	function number_of_open_tickets($refresh = false)
	{
		if (!$this->is_manager())
			return 0;
		if ( !$this->useSession || $refresh || empty($_COOKIE['number_of_open_tickets']) || $_COOKIE['number_of_open_tickets_time'] < time() - 300 ) {
			$result = get_api_value('user_number_of_open_tickets', '', '', '', $this);
			if ( $this->useSession ) {
				setcookie('number_of_open_tickets', $result);
				setcookie('number_of_open_tickets_time', time());
			}
			return $result;
		}
		else {
			return $_COOKIE['number_of_open_tickets'];
		}
	}

	function previous_login(&$ip, &$country)
	{
		$result = get_api_value('user_previous_login', '', '', '', $this);
		if ( $result ) {
			$ip = $result['ip'];
			$country = $result['country'];
		}
		return $result['previous_login_date'];
	}
	
	function get_rank_value($value_name, $rank = '')
	{
		global $ranks;
		if (empty($rank))
			$rank = $this->rank;
		return $ranks[$rank][$value_name];
	}
	
	function full_name()
	{
		return ucfirst(strtolower($this->firstname)).' '.ucfirst(strtolower($this->lastname));
	}
	
	function firstname($use_unicodes = true)
	{
		if ( $use_unicodes ) {
			$s = $this->firstname;
			$first = mb_substr($s, 0, 1, 'HTML-ENTITIES');
			$last = mb_substr($s, 1, 1024, 'HTML-ENTITIES');
			$s = mb_strtoupper($first, 'HTML-ENTITIES').mb_strtolower($last, 'HTML-ENTITIES');
			return $s;
		}
		else
			return ucfirst(strtolower($this->firstname));
	}
	
	function get_photo()
	{
		if ( !empty($this->photo) ) {
			if ( file_exists(DIR_WS_WEBSITE_PHOTOS.$this->photo) && ( strtotime($this->modified) <= filemtime(DIR_WS_WEBSITE_PHOTOS.$this->photo) ) ) {
				if ( empty($this->photo_data) ) {
					$this->set_photo($this->photo);
				}
				return '/'.DIR_WS_WEBSITE_PHOTOS_DIR.$this->photo;
			}
			else 
			if ( !empty($this->photo_data) ) {
				file_put_contents(DIR_WS_WEBSITE_PHOTOS.$this->photo, $this->photo_data);
				if ( file_exists(DIR_WS_WEBSITE_PHOTOS.$this->photo) )
					return '/'.DIR_WS_WEBSITE_PHOTOS_DIR.$this->photo;
			}
		}
		return '/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60'.(@$this->gender == 'F'?'girl':'boy').'.png';
	}
	
	function set_photo($file_path = '', $photo_data = null)
	{
		//if ( file_exists(DIR_WS_WEBSITE_PHOTOS.$file_path) ) {
			//$photo_data = bin2hex(file_get_contents(DIR_WS_WEBSITE_PHOTOS.$file_path));
			//return get_api_value('user_set_photo', '', array('file_path' => $file_path, 'photo_data' => $photo_data), '', $this);
		//}

		if ( !isset($photo_data) && file_exists(DIR_WS_WEBSITE_PHOTOS.$file_path) ) {
			$photo_data = bin2hex(file_get_contents(DIR_WS_WEBSITE_PHOTOS.$file_path));
		}
		if ( isset($photo_data) ) {
			return get_api_value('user_set_photo', '', array('file_path' => $file_path, 'photo_data' => $photo_data), '', $this);
		}
		return false;
	}
	
	function get_cart_total()
	{
		return get_api_value('user_get_cart_total', '', '', '', $this);
	}
	
	function cart_update($cartid, $item_name = null, $type = null, $var_int1 = null, $price = null, $quantity = null)
	{
		$update_values = array();
		if ( isset($item_name) ) $update_values['item_name'] = tep_sanitize_string($item_name);
		if ( isset($type) ) $update_values['type'] = tep_sanitize_string($type);
		if ( isset($var_int1) ) $update_values['var_int1'] = (int)tep_sanitize_string($var_int1);
		if ( isset($price) ) $update_values['price'] = tep_sanitize_string($price);
		if ( isset($quantity) ) $update_values['quantity'] = tep_sanitize_string($quantity);
		get_api_value('user_cart_update', array('cartid' => $cartid), $update_values, '', $this);
		return '';
	}

	function get_available_funds()
	{
		//return get_api_value('user_get_available_funds', '', '', '', $this);
		$user_data = make_api_request('balance', '', 'add_amount_in_usd=1', '', $this);
		if ( $user_data['success'] ) {
			$available_funds = 0;
			foreach ($user_data['values'] as $a_currency)
				$available_funds = $available_funds + floatval($a_currency['amount_in_usd']);
			return $available_funds;
		}
		else
			return false;
	}
	
	function get_payout_option_value($value_name, $check_can_payout = true, $payoutoption = '')
	{
		if ( empty($payoutoption) )
			$payoutoption = $this->payoutoption;
		global $payout_options;
		foreach ( $payout_options as $value ) {
			if ( $value['id'] == $payoutoption && ( $value['can_payout'] || !$check_can_payout ) )
				return $value[$value_name];
		}
		return false;
	}
	
	function calculate_discount($payment_method)
	{
		if ( !$this->is_discount() )
			return 0;
		$discount = 0;
		if ( defined(strtoupper($payment_method.'_PURCHASE_DISCOUNT')) && $this->is_discount() )
			$discount = constant(strtoupper($payment_method.'_PURCHASE_DISCOUNT'));
		return $discount;
	}

	function calculate_volume_discount($amount)
	{
		if ( !$this->is_discount() )
			return 0;
		$res = floor($amount / VOLUME_DISCOUNT_STEP_CALCULATED) / 100;
		if ( $res > VOLUME_DISCOUNT_MAX_PERCENT )
			$res = VOLUME_DISCOUNT_MAX_PERCENT;
		return $res;
	}
	
	function get_request_to_add_funds($total, $payment_method = 'paypal', $note = '', &$make_post_request = true, $transaction_prefix = ADD_FUNDS_PREFIX, $pay_email = '', $currency = 'usd', $currency_symbol = '$', $invoice_suffix = '', $return_post_request_as_text = 0, $force_to_use_pay_email = false)
	{
		$data = make_api_request('user_get_request_to_pay_cart', '', array('add_funds' => 1, 'payment_method' => $payment_method, 'make_post_request' => $make_post_request, 'pay_email' => $pay_email, 'cross_payoutid' => 0, 'total' => $total, 'transaction_prefix' => $transaction_prefix, 'note' => $note, 'currency' => $currency, 'currency_symbol' => $currency_symbol, 'invoice_suffix' => $invoice_suffix, 'return_post_request_as_text' => $return_post_request_as_text, 'force_to_use_pay_email' => $force_to_use_pay_email, 'ip' => $_SERVER['REMOTE_ADDR'] ), '', $this);
		if ( @$data["success"] ) {
			$res = $data['values'];
			$make_post_request = $res['make_post_request'];
			return $res['url'];
		}
		else {
			if (strpos($data['message'], 'Purchase Error:') === 0)
				return $data['message'];
			else
				return 'Error: because of some third party restrictions, this amount of money is too big for single <b>'.ucfirst(strtolower($payment_method)).'</b> purchase. Please split your order to smaller purchases.';
		}
	}
	
	function get_request_to_pay_cart($payment_method = 'paypal', &$make_post_request = true, $pay_email = '', $cross_payoutid = 0, $total = 0, $transaction_prefix = ADD_FUNDS_PREFIX, $note = '', $currency = 'usd', $currency_symbol = '$', $invoice_suffix = '', $return_post_request_as_text = 0, $from_balance_pay_currency = '')
	{
		$data = make_api_request('user_get_request_to_pay_cart', '', array('payment_method' => $payment_method, 'make_post_request' => $make_post_request, 'pay_email' => $pay_email, 'cross_payoutid' => $cross_payoutid, 'total' => $total, 'transaction_prefix' => $transaction_prefix, 'note' => $note, 'currency' => $currency, 'currency_symbol' => $currency_symbol, 'invoice_suffix' => $invoice_suffix, 'return_post_request_as_text' => $return_post_request_as_text, 'from_balance_pay_currency' => $from_balance_pay_currency, 'ip' => $_SERVER['REMOTE_ADDR'] ), '', $this);
		if ( @$data["success"] ) {
			$res = $data['values'];
			$make_post_request = $res['make_post_request'];
			return $res['url'];
		}
		else {
			if (strpos($data['values'], 'Purchase Error:') === 0)
				return $data['values'];
			else
				return 'Error: because of some third party restrictions, this amount of money is too big for single <b>'.ucfirst(strtolower($payment_method)).'</b> purchase. Please split your order to smaller purchases. '.$data['message'];
		}
	}
	
	function get_request_to_payout($total, $recepient_id, $recepient_email, $payoutid, $sales_manager_id, $payment_method = 'paypal', &$make_post_request = true)
	{
		$res = get_api_value('user_get_request_to_payout', '', array('total' => $total, 'recepient_id' => $recepient_id, 'recepient_email' => $recepient_email, 'payoutid' => $payoutid, 'sales_manager_id' => $sales_manager_id, 'payment_method' => $payment_method, 'make_post_request' => $make_post_request, 'ip' => $_SERVER['REMOTE_ADDR'] ), '', $this);
		if ( $res ) {
			$make_post_request = $res['make_post_request'];
			return $res['url'];
		}
		else
			return '';
	}

	function add_to_cart($item_name, $type, $var_int1 = 0, $var_txt1 = '', $price = null, $quantity = null)
	{
		return get_api_value('user_add_to_cart', '', array('item_name' => $item_name, 'type' => $type, 'var_int1' => $var_int1, 'var_txt1' => $var_txt1, 'price' => $price, 'quantity' => $quantity), '', $this);
	}

	function get_payment_methods($total = 0, $solid_cur_only = 0, $can_get_loan = 1, $additioanal_options = '', $include_balance = true)
	{
		if (!empty($additioanal_options))
			$additioanal_options = implode("|", $additioanal_options);
		return get_api_value('user_get_payment_methods', '', array('total' => $total, 'solid_cur_only' => $solid_cur_only, 'can_get_loan' => $can_get_loan, 'additioanal_options' => $additioanal_options, 'include_balance' => $include_balance), '', $this);
	}

	function is_user_from_country($from_country = '')
	{
		if (empty($from_country))
			$from_country = HACKER_COUNTRIES;
		return is_integer(strpos($from_country, $this->stat_country_last_login)) 
					|| is_integer(strpos($from_country, $this->stat_country_register)) 
					|| is_integer(strpos($from_country, $this->country));
	}
	
	function make_user_from_hacker_country($country = 'VN')
	{
		return get_api_value('user_make_user_from_hacker_country', '', ['country' => $country], '', $this);
	}

	function verify_password_from_box_password($hashed_password_name, $plain_password = '', $password_sign = '')
	{
		if ( empty($password_sign) ) {
			if (!empty($plain_password))
				$password_sign = md5( substr($plain_password, strlen($plain_password) -3 , 3).date('Y-m-d') );
		}
		if ( !empty($hashed_password_name) )
			$hashed_password = $_POST[$hashed_password_name];
		else
			$hashed_password = md5($plain_password);
		return $this->is_password_correct( $password_sign, '', $hashed_password, '', false, '', '', md5($hashed_password) );
	}

	function check_verification_pin($verification_pin)
	{
		return make_api_request('user_check_verification_pin', '', array('verification_pin' => $verification_pin), '', $this);
	}

	function clear_verification_pin()
	{
		return make_api_request('user_clear_verification_pin', '', '', '', $this);
	}

	function set_verification_pin($send_sms = false, $recipient_email = '', $send_email = true, $send_anyway = false)
	{
		return make_api_request('user_set_verification_pin', '', array('send_sms' => $send_sms, 'send_email' => $send_email, 'send_anyway' => $send_anyway), '', $this);
	}
	
	function get_seq_question()
	{
		$data = make_api_request('user_get_seq_question', '', array('entered_email' => $this->email));
		if ( @$data["success"] )
			return ($data['values']);
		return false;
	}

	function get_face_recog_when()
	{
		$data = make_api_request('user_get_face_recog_when', '', array('entered_email' => $this->email));
		if ( @$data["success"] )
			return intval($data['values']['face_recog_when']);
		return false;
	}
	
	function send_email_with_verification_pin()
	{
		return make_api_request('user_send_email_with_verification_pin', '', array('entered_email' => $this->email));
	}

	function send_email($template, $add_params = '', $sender_userid = '', $recipient_email = '', $send_actual_email = true, $save_to_db = true, $send_email_second_time = false)
	{
		$res = get_api_value('user_send_email', '', array('template' => $template, 'add_params' => $add_params, 'sender_userid' => $sender_userid, 'recipient_email' => $recipient_email, 'send_actual_email' => $send_actual_email, 'save_to_db' => $save_to_db, 'send_email_second_time' => $send_email_second_time), '', $this);
		return $res;
	}
	
	function update_email($email)
	{
		return get_api_value('user_update_email', '', array('email' => $email), '', $this);
	}

	function update_paypalemail($payout_email, $payprocessor = 'paypal', $force = false)
	{
		return get_api_value('user_update_payout_email', '', array('payout_email' => $payout_email, 'payprocessor' => $payprocessor, 'force' => $force), '', $this);
	}
	
	function update_payment_details($payout_email, $payprocessor = 'paypal')
	{
		return $this->update_paypalemail($payout_email, $payprocessor);
	}
		
	function update_address($address, $city = '', $state = '', $zip = '', $phone = '')
	{
		return make_api_request_with_error_message('user_update_address', '', array('address' => $address, 'city' => $city, 'state' => $state, 'zip' => $zip, 'phone' => $phone), '', $this);
	}
		
	function update_personal($gender, $married = '', $birth_year = '', $education = '')
	{
		return make_api_request_with_error_message('user_update_personal', '', array('gender' => $gender, 'married' => $married, 'birth_year' => $birth_year, 'education' => $education), '', $this);
	}
	
	function update_password($password_hash = '', $old_password_hash = '', $check_password = true, $check_old_password = true, $password_sign = '', $face_descriptor = '', $sec_answer_hash = '')
	{
		return get_api_value('user_update_password', '', array('password_hash' => $password_hash, 'old_password_hash' => $old_password_hash, 'face_descriptor' => $face_descriptor, 'sec_answer_hash' => $sec_answer_hash), '', $this);
	}
		
	function update_country($country)
	{
		return get_api_value('user_update_country', '', array('country' => $country), '', $this);
	}

	function update($firstname, $lastname, $country)
	{
		return make_api_request_with_error_message('user_update', '', array('firstname' => $firstname, 'lastname' => $lastname, 'country' => $country), '', $this);
	}
	
	function get_average_investor_invests($period = 30)
	{
		if ( is_file_variable_expired('average_investor_invests'.$period, 60) ) {
			$val = get_api_value('user_get_average_investor_invests', '', array('period' => $period), '', $this);
			if ($val < 190)
				$val = rand(190, 240);
			if ($val)
				update_file_variable('average_investor_invests'.$period, $val);
		}
		else
			$val = get_file_variable('average_investor_invests'.$period);
		return $val;
	}
	
	function get_general_aff_link($userid = '')
	{
		if ( empty($userid) )
			$userid = $this->userid;
		$s = HTTP_PREFIX.SITE_SHORTDOMAIN.'/';
		if ( $s[strlen($s) - 1] != '/' )
			$s = $s.'/';
		$s = $s.MOD_REWRITE_PREFIX.$userid.MOD_REWRITE_SUFIX;
		return $s;
	}
	
	function replaceUserConstantsInText($text) 
	{
		foreach($this->user_variables as $code => $value) {
			$text = replaceCustomConstantInText($code, $value, $text);
		}
		return $text;
	}
	
	function parse_job_text($jobid, Job $job = NULL, $display_text = '', &$var_text1, &$var_text2, &$var_int1, &$var_int2, &$var_int3)
	{
		$res = get_api_value('user_parse_job_text', '', array('jobid' => $jobid, 'display_text' => $display_text, 'var_text1' => $var_text1, 'var_text2' => $var_text2, 'var_int1' => $var_int1, 'var_int2' => $var_int2, 'var_int3' => $var_int3));
		if ( $res ) {
			$display_text = $res['display_text'];
			$var_text1 = $res['var_text1'];
			$var_text2 = $res['var_text2'];
			$var_int1 = $res['var_int1'];
			$var_int2 = $res['var_int2'];
			$var_int3 = $res['var_int3'];
		}
		else
			$display_text = '';
		
		return $display_text;
	}
	
	function get_skills($order_by = 0)
	{
		if ( !isset($this->skills) ) {
			$result = get_api_value('user_get_skills', '', array('order_by' => $order_by));
			$this->skills = $result;
		}
		return $this->skills;
	}
	
	function get_skill_level($skill)
	{
		$skill_array = $this->get_skills();
		if ( isset($skill_array[$skill]['skill_level']) )
			return $skill_array[$skill]['skill_level'];
		else 
			return 0;
	}
	
	function check_skill_level_enough($job)
	{
		$skillid = $job->skillid;
		if ( !empty($skillid) ) {
			$user_skill_level = $this->get_skill_level($skillid);
			if ( $user_skill_level < $job->skill_level || ( !$job->skill_level_this_and_higher && $user_skill_level > $job->skill_level ) ) 
				return false;
		}
		if ($job->skillid == 'review' && $job->review_jobid > 0 ) {
			$review_job = new Job();
			$review_job->read_data($job->review_jobid);
			$skillid = $review_job->skillid;
			if ( !empty($skillid) ) {
				$user_skill_level = $this->get_skill_level($skillid);
				if ( $user_skill_level < $job->skill_level || ( !$job->skill_level_this_and_higher && $user_skill_level > $job->skill_level ) ) 
					return false;
			}
		}
		return true;
	}

	function get_permissions_arr($order_by = 0)
	{
		if ( !isset($this->permissions_arr) ) {
			$this->permissions_arr = get_api_value('user_get_permissions_arr', '', array('order_by' => $order_by));
		}
		return $this->permissions_arr;
	}

	function get_list_of_users_permissions_types($sort_by = 0, $condition = 0, $limit = '')
	{
		if ( !isset($this->list_of_users_permissions_types) )
			$this->list_of_users_permissions_types = get_api_value('user_get_list_of_users_permissions_types', '', array('sort_by' => $sort_by, 'condition' => $condition, 'limit' => $limit));
		return $this->list_of_users_permissions_types;
	}

	function add_permission($permissionid)
	{
		return get_api_value('user_add_permission', '', array('permissionid' => $permissionid), '', $this);
	}

	function get_number_of_not_fully_paid_orders($crypto_curreny = 'bitcoin', $refresh = false, $payment_option = '')
	{
		return get_api_value('user_get_number_of_not_fully_paid_orders', '', array('crypto_curreny' => $crypto_curreny, 'refresh' => $refresh, 'payment_option' => $payment_option), '', $this);
	}
	
	function get_activity_points($kind = 'activity')
	{
		return get_api_value('user_get_activity_points', '', array('kind' => $kind), '', $this);
	}

	function get_country_name()
	{
		if ( !isset($this->country_name) ) {
			$this->country_name = getCountryName($this->country);
		}
		return $this->country_name;
	}

	function get_rank_image()
	{
		return '/'.DIR_WS_WEBSITE_IMAGES_DIR.'rank'.$this->rank.'.png';
	}
	
	function get_numb_of_pending_payouts()
	{
		return (int)get_api_value('user_get_numb_of_pending_payouts', '', '', '', $this);
	}
	
	function can_use_this_payout_option($can_payout, $banned_countries, $west_users_only, $payout_id )
	{
		return $can_payout && ($this->paypalemail_confirmed || !$west_users_only) /*|| defined('DEBUG_MODE')*/;
	}
	
	function update_notifications($job_emails = '', $order_emails = '', $ticket_emails = '')
	{
		$this->job_emails = $job_emails;
		$this->order_emails = $order_emails;
		$this->ticket_emails = $ticket_emails;
		return get_api_value('user_update_notifications', '', array('job_emails' => $job_emails, 'order_emails' => $order_emails, 'ticket_emails' => $ticket_emails), '', $this);
	}

	function delete($comment = '', $just_mark_as_deleted = 0, $force_to_delete = false)
	{
		unlink(DIR_WS_WEBSITE_PHOTOS.$this->userid.'.jpg');
		$s = DIR_WS_WEBSITE_PHOTOS.'*'.BIG_PHOTO_THUMB_PREFIX.$this->userid.'*.*';
		$folder = glob($s);
		foreach($folder as $file)
			unlink($file);
		if ( get_api_value('user_delete', '', array('comment' => base64_encode($comment)), '', $this) )
			return '';
		else
			return 'Error: cannot delete account';
	}

	function calculate_withdraw(&$amount, &$box_message, &$numb_of_pending_payouts, &$maximum_withdraw, &$minimum_withdraw, &$available_funds, &$trans_fee, $currency = '')
	{
		$res = get_api_value('user_calculate_withdraw', '', array('amount' => $amount, 'currency' => $currency), '', $this);
		if ( $res ) {
			$amount = $res['amount'];
			$box_message = $res['box_message'];
			$numb_of_pending_payouts = $res['numb_of_pending_payouts'];
			$maximum_withdraw = $res['maximum_withdraw'];
			$minimum_withdraw = $res['minimum_withdraw'];
			$available_funds = $res['available_funds'];
			$trans_fee = $res['trans_fee'];
			//var_dump($res); exit;
			return true;
		}
		return false;
	}
	
	function withdraw($amount, $transaction_description = '', $note_to_franchisee = '', $charge_withdrawal_fee = true, $payout_is_already_done = false, $widthdraw_anyway = false, $create_zero_transaction = false, $start_n_fin_transaction = true, $pay_processor_email = '', $adminnote = '', $currency = '')
	{
		return get_api_value('user_withdraw', '', array('amount' => $amount, 'transaction_description' => $transaction_description, 'note_to_franchisee' => $note_to_franchisee, 'charge_withdrawal_fee' => $charge_withdrawal_fee, 'payout_is_already_done' => $payout_is_already_done, 'widthdraw_anyway' => $widthdraw_anyway, 'pay_processor_email' => $pay_processor_email, 'currency' => $currency), '', $this);
	}

	function is_manager()
	{
		return is_integer(strpos($this->permissions, PERMISSION_MANAGER)) || $this->permission_rank >= PERMISSION_MANAGER_MIN_RANK;
	}
	
	function is_general_manager()
	{
		return is_integer(strpos($this->permissions, PERMISSION_GENERAL_MANAGER));
	}
	
	function is_sales_manager()
	{
		return is_integer(strpos($this->permissions, PERMISSION_SALES_MANAGER));
	}
		
	function is_tresurer()
	{
		return is_integer(strpos($this->permissions, PERMISSION_TRESURER));
	}
	
	function is_regional_rep()
	{
		return is_integer(strpos($this->permissions, PERMISSION_REGIONAL_REP));
	}

	function has_permission($permission)
	{
		return in_array($permission, $this->permissions_arr);
	}
	
	function get_permission_name($permissionid)
	{
		return get_api_value('user_get_permission_name', '', array('permissionid' => $permissionid), '', $this);
	}
	
	function get_permission_age($permissionid)
	{
		return get_api_value('user_get_permission_age', '', array('permissionid' => $permissionid), '', $this);
	}
	
	function remove_permission($permissionid)
	{
		return get_api_value('user_remove_permission', '', array('permissionid' => $permissionid), '', $this);
	}

	function get_balance($reset_balance_time = false)
	{
		return $this->get_calculated_balance($reset_balance_time, DOLLAR_NAME);
	}
	
	function paypalemail_confirmed()
	{
		return $this->paypalemail_confirmed 
			|| !$this->get_payout_option_value('need_confirm_from_banned_countries') 
			|| ( 
				!is_integer(strpos($this->get_payout_option_value('banned_countries'), $this->stat_country_last_login))
				&& !is_integer(strpos($this->get_payout_option_value('banned_countries'), $this->country))
			);
	}
	
	function get_payout_method()
	{
		global $payout_options;
		foreach ( $payout_options as $value ) {
			if ( $value['id'] == $this->payoutoption && $value['can_payout'] ) {
				return $value['name'];
			}
		}
	}
	
	function cancel_regional_rep()
	{
		return get_api_value('user_cancel_regional_rep', '', '', '', $this);
	}

	function can_apply_for_regional_rep()
	{
		if ( $this->is_regional_rep() ) {
			return true;
		}
		else {
			$arr = $this->get_activity_points('referrals');
			$users_permission = $this->get_list_of_users_permissions_types(0, 1, 1);
			return $this->can_apply_for_regional_rep && $arr['total_points'] > $users_permission[0]['need_points'];
		}
	}
	
	function convert_to_regional_rep($country, $city, $languages, $website, $phone)
	{
		return get_api_value('user_convert_to_regional_rep', '', array('country' => $country, 'city' => base64_encode($city), 'languages' => $languages, 'website' => base64_encode($website), 'phone' => base64_encode($phone)), '', $this);
	}

	function signup($email, $hashed_password, $firstname, $lastname, $country, $parentid = '', $note = '', $send_email = true, $check_errors = true, $must_be_disabled_in_days = '', $user_ip = '', $user_domain = '', $parent_websiteid = '', $parent_website = '')
	{
		$error_str = '';

		if ( is_integer(strpos($email, '<')) )
			$error_str = $error_str.' email,';
		
		$email = tep_sanitize_string($email, 64);
		$firstname = mb_convert_encoding($firstname, 'HTML-ENTITIES', 'UTF-8');
		$lastname = mb_convert_encoding($lastname, 'HTML-ENTITIES', 'UTF-8');
		$firstname = tep_sanitize_string($firstname, 224);
		$lastname = tep_sanitize_string($lastname, 224);
		$country = tep_sanitize_string($country, 2);
		
		//if ( $check_errors && defined('ACCOUNT_EMAIL_TEMPLATE') && !empty(ACCOUNT_EMAIL_TEMPLATE) && !preg_match('/'.ACCOUNT_EMAIL_TEMPLATE.'/', $email, $match_arr ) )
			//$error_str = $error_str.' email - , '.ACCOUNT_EMAIL_TEMPLATE.' = '.$email;

		//if ( $check_errors && ( !is_integer(strpos($email, '@')) || !is_integer(strpos($email, '.')) ) ) {
			//$error_str = $error_str.' email,';
		//}

		if ( $check_errors && ( empty($email) || strlen($email) < 4) ) {
			$error_str = $error_str.' email,';
		}

		if ( $check_errors && empty($hashed_password) ) {
			$error_str = $error_str.' password,';
		}
		
		if ( $check_errors && empty($firstname) )
			$error_str = $error_str.' first name,';
		
		if ( $check_errors && empty($lastname) ) 
			$error_str = $error_str.' last name,';
		
		if ( !empty($error_str) )
			return $error_str;

		if ( empty($user_domain) )
			$user_domain = @$_COOKIE[TRACK_COOKIE_DOMAIN];

		if ( empty($user_ip) )
			$user_ip = $_SERVER['REMOTE_ADDR'];

		return get_api_value('user_signup', '', array('email' => $email, 'hashed_password' => $hashed_password, 'firstname' => base64_encode($firstname), 'lastname' => base64_encode($lastname), 'country' => $country, 'parentid' => $parentid, 'note' => base64_encode($note), 'send_email' => $send_email, 'check_errors' => $check_errors, 'must_be_disabled_in_days' => $must_be_disabled_in_days, 'signup_ip' => base64_encode($user_ip), 'user_domain' => $user_domain, 'parent_websiteid' => $parent_websiteid, 'parent_website' => $parent_website), '', $this);
	}

	function get_upgrade_name()
	{
		global $ranks;
		return $ranks[$this->rank]['name'];
	}
	
	function get_upgrade_cost()
	{
		global $ranks;
		return $ranks[$this->rank]['upgrade'];
	}
	
	function set_upgrade_expires($date)
	{
		$result = get_api_value('user_set_upgrade_expires', '', array('upgrade_expires' => $date), '', $this);
	}

	function is_user_in_black_list(&$website_which_posted, &$post_date)
	{
		$result = get_api_value('user_is_in_black_list', '', '', '', $this);
		$website_which_posted = $result['website_which_posted'];
		$post_date = $result['post_date'];
		return $result['user_in_black_list'];
	}
	
	function is_user_punished()
	{
		return $this->rank == 0 && $this->account_type != 'B';
	}

	function last_login()
	{
		return get_api_value('user_last_login', '', '', '', $this);
	}

	function is_relative_to($userid)
	{
		return get_api_value('user_is_relative_to', '', array('relative_to' => $userid), '', $this);
	}

	function get_total_purchases($recalculate = false, $additonal_conditions = '', $currency = '')
	{
		if (empty($currency))
			$currency = DOLLAR_NAME;
		return get_api_value('user_get_total_purchases', '', array('additonal_conditions' => $additonal_conditions, 'currency' => $currency), '', $this);
	}
	
	function get_total_payouts($recalculate = false, $additonal_conditions = '', $currency = '')
	{
		if (empty($currency))
			$currency = DOLLAR_NAME;
		return get_api_value('user_get_total_payouts', '', array('additonal_conditions' => $additonal_conditions, 'currency' => $currency), '', $this);
	}

	function change_note($note)
	{
		return get_api_value('user_change_note', '', array('note' => $note), '', $this);
	}
	
	function disable_enable_account($disable = '1', $cancel_all_transactions = 1, $note = '', $add_to_black_list = 1, $add_domain_to_black_list = 0)
	{
		return get_api_value('user_disable_enable_account', '', array('disable' => $disable, 'cancel_all_transactions' => $cancel_all_transactions, 'note' => $note, 'add_to_black_list' => $add_to_black_list, 'add_domain_to_black_list' => $add_domain_to_black_list), '', $this);
	}

	function remove_from_blacklist()
	{
		return get_api_value('user_remove_from_blacklist', '', '', '', $this);
	}

	function set_purchases_disabled($purchases_disabled = 1)
	{
		return get_api_value('user_set_purchases_disabled', '', array('purchases_disabled' => $purchases_disabled), '', $this);
	}

	function set_disabled_shares_issue($disabled_shares_issue = 1)
	{
		return get_api_value('user_set_disabled_shares_issue', '', array('disabled_shares_issue' => $disabled_shares_issue), '', $this);
	}

	function set_dontdelete($dontdelete = '1')
	{
		return get_api_value('user_set_dontdelete', '', array('dontdelete' => $dontdelete), '', $this);
	}
	
	function change_rank($rank = null, $increase = true)
	{
		if ( !isset($rank) )
			$rank = -1;
		return get_api_value('user_change_rank', '', array('rank' => $rank, 'increase' => $increase), '', $this);
	}

	function increase_rank($rank = null)
	{
		$this->change_rank($rank, true);
	}

	function decrease_rank()
	{
		$this->change_rank(null, false);
	}
	
	function change_rank_according_to_activity_points()
	{
		return get_api_value('user_change_rank_according_to_activity_points', '', '', '', $this);
	}

	function set_max_rank($max_rank)
	{
		return get_api_value('user_set_max_rank', '', array('max_rank' => $max_rank), '', $this);
	}
	
	function block_trouble_tickets($trouble_tickets_blocked = 1)
	{
		return get_api_value('user_block_trouble_tickets', '', array('trouble_tickets_blocked' => $trouble_tickets_blocked), '', $this);
	}

	function change_parent($new_parentid)
	{
		return get_api_value('user_change_parent', '', array('new_parentid' => $new_parentid), '', $this);
	}

	function restore_account()
	{
		return get_api_value('user_restore_account', '', '', '', $this);
	}

	function confirm_paypal_email($email, $value = 1)
	{
		return get_api_value('user_confirm_paypal_email', '', array('email' => $email, 'value' => $value), '', $this);
	}

	function withdrawal_disable($withdrawal_disable = 1)
	{
		return get_api_value('user_withdrawal_disable', '', array('withdrawal_disable' => $withdrawal_disable), '', $this);
	}

	function get_payout_address($payoutoption = 'paypal')
	{
		return $this->paypalemail;
	}
	
	function is_search_has_many_results()
	{
		return get_api_value('user_is_search_has_many_results', '', array('read_userid' => $this->userid, 'email' => $this->email, 'firstname' => $this->firstname, 'lastname' => $this->lastname, 'paypalemail' => $this->paypalemail), '', $this);
	}

	function get_site_purchases($period = 30, $additonal_conditions = '', $refresh = false, $local_site = 1, $currency = '')
	{
		return get_api_value('user_get_site_purchases', '', array('period' => $period, 'additonal_conditions' => $additonal_conditions, 'currency' => $currency), '', $this);
	}

	function find_next_job($additional_conditions = '', $fake_perform = false, $limit = 20)
	{
		return get_api_value('user_find_next_job', '', array('additional_conditions' => $additional_conditions, 'fake_perform' => $fake_perform, 'limit' => $limit), '', $this);
	}
	
	function get_next_pending_transaction(&$date)
	{
		$result = get_api_value('user_get_next_pending_transaction', '', '', '', $this);
		if ( $result ) {
			$date = $result['date'];
			return $result['value'];
		}
		return false;
	}

	function hold_rank()
	{
		return get_api_value('user_hold_rank', '', '', '', $this);
	}

	function unhold_rank()
	{
		return get_api_value('user_unhold_rank', '', '', '', $this);
	}

	function is_discount()
	{
		if ( !isset($this->is_discount) )
			$this->is_discount = get_api_value('user_is_discount', '', '', '', $this);
		return $this->is_discount;
	}

	function is_news_avalable()
	{
		return get_api_value('user_is_news_avalable', '', '', '', $this);
	}
	
	function get_loan_vars()
	{
		if ( !isset($this->loan_vars) )
			$this->loan_vars = get_api_value('user_get_loan_vars', '', '', '', $this);
		return $this->loan_vars;
	}
	
	function calculate_loan_payment(&$amount_of_loan, &$term_in_days, &$rate_per_day, &$period_in_days, &$transactions_global_transactionid, &$current_payment)
	{
		$crypto = new Bitcoin();
		if ( !empty($amount_of_loan) && !empty($term_in_days) && !empty($rate_per_day) && !empty($period_in_days) ) {
			$term_in_periods = $term_in_days / $period_in_days; // number of weeks
			$rate = $rate_per_day * $period_in_days; // per week
			return ceil($amount_of_loan * ($rate / (1 - pow(1 + $rate, - $term_in_periods))) * pow(10, $crypto->digits)) / pow(10, $crypto->digits);
		}
		$result = get_api_value('user_calculate_loan_payment', '', array('amount_of_loan' => $amount_of_loan, 'term_in_days' => $term_in_days, 'rate_per_day' => $rate_per_day, 'period_in_days' => $period_in_days, 'transactions_global_transactionid' => $transactions_global_transactionid), '', $this);
		if ( $result ) {
			$amount_of_loan = $result['amount_of_loan'];
			$term_in_days = $result['term_in_days'];
			$rate_per_day = $result['rate_per_day'];
			$period_in_days = $result['period_in_days'];
			$transactions_global_transactionid = $result['transactions_global_transactionid'];
			$current_payment = $result['current_payment'];
		}
		return $result['loan_payment'];
	}

	function get_part_of_loan_payment_which_goes_to_lender($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days = 7)
	{
		$loan_payment = $this->calculate_loan_payment($amount_of_loan, $term_in_days, $rate_per_day, $period_in_days, $transactions_global_transactionid, $current_payment);
		$rate = $rate_per_day * $period_in_days; // per week
		$margin = $loan_payment * $rate;
		$admins_margin = $margin * PART_OF_TRANSACTION_GOES_TO_BROCKER;
		return $loan_payment - $admins_margin;
	}
	
	function cancel_loan($transactionid, $userid = '')
	{
		return get_api_value('user_cancel_loan', '', array('transactionid' => $transactionid, 'owner_userid' => $userid), '', $this);
	}
	
	function has_p2p_loan_past_due_days(&$next_unix_time_to_pay, &$transactions_global_transactionid, &$created_unix_timestamp)
	{
		$result = get_api_value('user_has_p2p_loan_past_due_days', '', array('transactions_global_transactionid' => $transactions_global_transactionid), '', $this);
		if ( $result ) {
			$transactions_global_transactionid = $result['transactions_global_transactionid'];
			$next_unix_time_to_pay = $result['next_unix_time_to_pay'];
			$created_unix_timestamp = $result['created_unix_timestamp'];
			return $result['has_p2p_loan_past_due_days'];
		}
		return false;
	}

	function get_parentid_by_ip($ip)
	{
		$www_requests_p_sec = 0;
		if ( is_file_variable_expired('stat_www_requests_p_sec', 1) ) {
			$www_requests_p_sec = stat_www_requests_p_sec();
			update_file_variable('stat_www_requests_p_sec', $www_requests_p_sec);
		}
		else
			$www_requests_p_sec = get_file_variable('stat_www_requests_p_sec');
		if ( $www_requests_p_sec < 40 )
			return get_api_value('user_get_parentid_by_ip', '', array('signup_ip' => $ip), '', $this);
		else
			return 0;
	}
	
	function update_security_question($sec_question, $sec_answer_hash, $password_hash, $face_descriptor = '', $old_sec_answer_hash = '')
	{
		$res = make_api_request('user_update_security_question', '', array('sec_question' => $sec_question, 'sec_answer_hash' => $sec_answer_hash, 'password_hash' => $password_hash, 'face_descriptor' => $face_descriptor, 'old_sec_answer_hash' => $old_sec_answer_hash), '', $this);
		if ( @$res["success"] )
			return '';
		else
			return $res["message"];
	}

	function get_min_max_term_deposit()
	{
		return get_api_value('user_get_min_max_term_deposit', '', '', '', $this);
	}
	
	function get_individual_withdraw_period()
	{
		return get_api_value('user_get_individual_withdraw_period', '', '', '', $this);
	}

	function get_current_job_text($additional_conditions = '', &$sentid)
	{
		$result = get_api_value('job_get_current_job_text', '', array('additional_conditions' => $additional_conditions, 'sentid' => $sentid), '', $this);
		if ( $result ) {
			$sentid = $result['sentid'];
			return $result['current_job_text'];
		}
		return false;
	}

	function get_days_since_last_payout()
	{
		return get_api_value('user_get_hours_since_last_payout', '', '', '', $this) / 24;
	}

	function get_hours_since_last_payout()
	{
		return get_api_value('user_get_hours_since_last_payout', '', '', '', $this);
	}
	
	function get_not_paid_or_not_fully_paid_order($crypto_currency = '')
	{
		return get_api_value('user_get_not_paid_or_not_fully_paid_order', '', array('crypto_currency' => $crypto_currency), '', $this);
	}

	function lend_from_balance($transactionid, $crypto_address_to_receive_installments)
	{
		return get_api_value('user_lend_from_balance', '', array('transactions_global_transactionid' => $transactionid, 'crypto_address_to_receive_installments' => $crypto_address_to_receive_installments), '', $this);
	}
	
	function is_face_correct($face_descriptor)
	{
		return get_api_value('user_is_face_correct', '', array('face_descriptor' => $face_descriptor), '', $this);
	}

	function clear_sec_question()
	{
		return get_api_value('user_clear_sec_question', '', '', '', $this);
	}

	function is_ip_good_for_admin_login($ip)
	{
		return get_api_value('is_ip_good_for_admin_login', '', ['ip' => $ip], '', $this);
	}

	function reward($amount_in_usd, $description = '', $crypto_name = 'bitcoin', $transaction_type = 'BN', $hold_reward_for_days = 0, $parent_transactionid = 0)
	{
		return get_api_value('user_reward', '', ['amount_in_usd' => $amount_in_usd, 'description' => $description, 'crypto_name' => $crypto_name, 'transaction_type' => $transaction_type, 'hold_reward_for_days' => $hold_reward_for_days, 'parent_transactionid' => $parent_transactionid], '', $this);
	}
	
	function save_face_descriptor($face_descriptor, $password_hash = '', $sec_answer_hash = '')
	{
		$res = make_api_request('user_save_face_descriptor', '', ['face_descriptor' => $face_descriptor, 'password_hash' => $password_hash, 'sec_answer_hash' => $sec_answer_hash], '', $this);
		if ( @$res["success"] )
			return '';
		else
			return $res["message"];
	}

}

?>