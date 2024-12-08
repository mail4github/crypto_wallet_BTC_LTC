<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);

include_once('../../includes/config.inc.php');

//protect_from_DDOS();

include_once(DIR_COMMON_PHP.'general.php');

require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'pay_processor.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');
require_once(DIR_WS_CLASSES.'litecoin.pay_processor.class.php');

function make_appserver_request($request, $get_params, $post_params, $token = '', $user_account = null)
{
	try {
		$data = make_api_request($request, $get_params, $post_params, $token, $user_account);
		if ( !isset($data['empty_result']) || !$data['empty_result'] )
			return $data;
		else
			return false;
	}
	catch (Exception $e) {
		return false;
	}
}

function get_appserver_value($request, $get_params = '', $post_params = '', $token = '', $user_account = null)
{
	$data = make_appserver_request($request, $get_params, $post_params, $token, $user_account);
	if ( $data['success'] )
		return $data['values'];
	else
		return false;
}

function recurse_files($folder, $exclude_tmp = 1)
{
	global $files;
	
	if ( $folder[strlen($folder) - 1] != '/' )
		$folder = $folder.'/';
	
	$folders = scandir($folder);
	foreach ( $folders as $short_file_name ) {
		$file_name = $folder.$short_file_name;
		if ( !is_dir($file_name) ) {
			$filetime = filemtime($file_name);
			$s = getcwd();
			$just_file_name = substr($file_name, strlen($s));
			if ( ($filetime > $_POST['last_check_time']) ) 
				$files[] = array('filetime' => $filetime, 'file_name' => $just_file_name, 'file_path' => $file_name, 'folder' => $folder);
		}
		else {
			if ( $short_file_name != '.' && $short_file_name != '..' ) {
				if ( ( !is_integer(strpos($file_name, 'tmp')) && !is_integer(strpos($file_name, 'backup')) && !is_integer(strpos($file_name, 'ini_data')) ) || !$exclude_tmp || is_integer(strpos($file_name, 'tmp_images')) || is_integer(strpos($file_name, 'tmp_custom_code')) ) {
					recurse_files($file_name, $exclude_tmp);
				}
			}
		}
	}
}

function recurse_folders($folder, $files, $buxtank_website_path = '')
{
	if ( $folder[strlen($folder) - 1] != '/' )
		$folder = $folder.'/';
	
	$folders = scandir($folder, SCANDIR_SORT_ASCENDING );
	foreach ( $folders as $short_file_name ) {
		$file_name = $folder.$short_file_name;
		if ( !is_dir($file_name) ) {
			$filetime = filemtime($file_name);
			$just_file_name = substr($file_name, strlen($folder) + 0);
			$relative_folder = substr($folder, strlen($buxtank_website_path));
			$files[$relative_folder][] = array('filetime' => $filetime, 'file_name' => $just_file_name, 'file_path' => $file_name/*, 'folder' => $folder*/);
		}
		else {
			if ( $short_file_name != '.' && $short_file_name != '..' ) {
				$files = recurse_folders($file_name, $files, $buxtank_website_path);
			}
		}
	}
	return $files;
}

function design_mode()
{
	$design_mode = true;
	$design_mode_value_from_file = get_file_variable('DESIGN_MODE');
	if ( is_string($design_mode_value_from_file) ) {
		if ( get_file_variable('DESIGN_MODE') == '0' )
			$design_mode = false;
		if ( get_file_variable('DESIGN_MODE') == '1' )
			$design_mode = true;
	}
	else {
		$data = make_api_request('is_design_mode');
		if ( $data['success'] )
			$design_mode = (int)$data['values'];
	}
	return $design_mode;
}

if ( !isset($_POST['nonce']) )
	$_POST['nonce'] = '';
$_POST['nonce'] = tep_sanitize_string($_POST['nonce']);
if ( !isset($_POST['userid']) )
	$_POST['userid'] = '';
$_POST['userid'] = tep_sanitize_string($_POST['userid']);
if ( !isset($_POST['token']) )
	$_POST['token'] = '';
$_POST['token'] = tep_sanitize_string($_POST['token']);
if ( !isset($_POST['signature']) )
	$_POST['signature'] = '';
$_POST['signature'] = tep_sanitize_string($_POST['signature']);

if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'add_api.php') )
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'add_api.php');

if ( !empty($_GET['do_tasks']) ) {
	$message = '';
	$success = 1;
	// restore files from DB
	if ( is_file_variable_expired('restore_files_from_db') ) {
		update_file_variable('restore_files_from_db', '1');
		$data = get_appserver_value('restore_file_from_db', '', array('file_name' => '*'));
		if ( $data ) {
			$data['file_body'] = base64_decode($data['file_body']);
			if ( !empty($data['file_name']) && !empty($data['file_body']) && !file_exists(DIR_WS_TEMP_IMAGES.$data['file_name']) ) {
				file_put_contents(DIR_WS_TEMP_IMAGES.$data['file_name'], $data['file_body']);
				$message = 'Restored: '.$data['file_name'];
			}
		}
	}
	// refresh files from DB
	if ( is_file_variable_expired('refresh_files_from_db') ) {
		update_file_variable('refresh_files_from_db', '1');

		$folders = glob(DIR_WS_TEMP_IMAGES.'*');
		shuffle($folders);
		
		$file_name = $folders[0];
		if ( !is_dir($file_name) ) {
			$filectime = filemtime($file_name);
			$base_name = basename($file_name);
			$message = "$base_name : $filectime<br>";
			$data = get_appserver_value('restore_file_from_db', '', array('file_name' => $base_name));
			if ( $data ) {
				$data['file_body'] = base64_decode($data['file_body']);
				if ( !empty($data['file_name']) && !empty($data['file_body']) && $filectime < $data['changed_timestamp'] ) {
					file_put_contents($file_name, $data['file_body']);
					$message = "<br>$base_name : $filectime".'<br>Refreshed: '.$data['file_name'].' time: '.$data['changed_timestamp'];
				}
			}
		}
	}

	$processes = array( 
		array('frequency' => 10, 'file' => 'delete_garbage.php'),
		array('frequency' => 20, 'file' => 'update_stats.php'),
		array('frequency' => 60, 'file' => 'update_tmp_data.php'),
		array('frequency' => 600, 'file' => 'synchro_files.php'),
		array('frequency' => 600, 'file' => 'banner_data_refresh.php'),
	);
	shuffle($processes);
	
	$to_run = '';
	if ( !empty($_GET['task_to_do']) ) {
		foreach ($processes as $process) {
			if ( $process['file'] == $_GET['task_to_do'] ) {
				$to_run = $process;
				break;
			}
		}
	}
	else {
		for ($i = 0; $i < count($processes); $i++) {
			$process = $processes[$i];
			if ( is_file_variable_expired($process['file'], 0, $process['frequency']) ) {
				$to_run = $process;
				break;
			}
		}
	}
	if ( !empty($to_run) ) {
		update_file_variable($to_run['file'], '1');
		$message = "to_run: ".$to_run['file'];
		$eval_code = file_get_contents(DIR_WS_INCLUDES.'$$$daimon_'.$to_run['file']);
		$eval_code = str_replace('?>', '', str_replace('<?php', '', $eval_code));
		eval($eval_code);
	}
	echo generate_answer($success, $message);
	exit;
}
else
if ( !empty($_GET['refresh_global_constants']) ) {
	$is_ok = 1;
	$message = '';
	$data = get_appserver_value('admin_get_global_constants', '', $_POST);
	if ( $data ) {
		$s = '';
		foreach($data as $key => $value)
			$s = $s."$key=$value\r\n";
		if (!empty($s)) {
			$res = file_put_contents(DIR_DATA.'$$$settings.ini', $s);
			if ($res === false) {
				$is_ok = 0;
				$message = 'Cannot write to file';
			}
		}
		else {
			$is_ok = 0;
			$message = 'Data is empty';
		}
	}
	else {
		$is_ok = 0;
		$message = 'Server does not send data';
	}
	echo generate_answer($is_ok, $message/*, $data*/);
	exit;
}
else
if ( !empty($_GET['user_login']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');

	$data = urldecode(hex2bin($_POST['data']));
	$data_arr = explode('<div>', $data);
	$login_data_arr = array(
		'email' => tep_sanitize_string($data_arr[0]),
		'userid' => tep_sanitize_string($data_arr[1]),
		'password' => $data_arr[4],
		'verification_pin' => $data_arr[6],
		'password_sign' => $data_arr[7],
		'fingerprint' => $data_arr[8],
	);
	if ( !empty($_POST['real_user_ip']) )
		$_SERVER['REMOTE_ADDR'] = $_POST['real_user_ip'];
	$data = make_appserver_request('user_login', '', $login_data_arr);
	if ($data !== false && !empty($data)) {
		if ( $data['success'] ) {
			$login_data = $data['values'];
			session_start();
			echo generate_answer(1, '', $login_data);
			exit;
		}
	}
	else
		$data = array('success' => 0, 'message' => "Error: no answer", 'values' => '', 'error_code' => 3);
	sleep(2);
	echo generate_answer(0, $data['message'], $data['values'], $data['error_code']);
	exit;
}
else
if ( !empty($_GET['user_update_crypto_addr']) || !empty($_GET['user_exchange_crypto']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	$_POST['ip'] = $_SERVER['REMOTE_ADDR'];
	$data = make_appserver_request('', $_SERVER['QUERY_STRING'], $_POST);
	echo generate_answer($data['success'], $data['message'], $data['values']);
	exit;
}
else
if ( !empty($_GET['synchro_files']) ) {
	
	function file_array_sort($array, $on, $order = SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();
		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} 
				else {
					$sortable_array[$k] = $v;
				}
			}
			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
				break;
				case SORT_DESC:
					arsort($sortable_array);
				break;
			}
			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}
		return $new_array;
	}

	$res = array();

	/*if ( get_api_token_seed(true) != $_POST['token'] ) {
		echo generate_answer(0, 'Error: wrong token '.get_api_token_seed().' != '.$_POST['token']);
		sleep(2);
		exit;
	}*/

	/*$design_mode = true;
	$design_mode_value_from_file = get_file_variable('DESIGN_MODE');
	if ( is_string($design_mode_value_from_file) ) {
		if ( get_file_variable('DESIGN_MODE') == '0' )
			$design_mode = false;
		if ( get_file_variable('DESIGN_MODE') == '1' )
			$design_mode = true;
	}
	else {
		$data = make_api_request('is_design_mode');
		if ( $data['success'] )
			$design_mode = (int)$data['values'];
	}*/
	if ( design_mode() ) {
		echo generate_answer(0, 'Error: design mode.');
		exit;
	}
	if ( !isset($_POST['exclude_tmp_files']) ) 
		$_POST['exclude_tmp_files'] = 1;

	chdir(DIR_ROOT);

	if ( $_POST['tmp_custom_code'] )
		chdir(DIR_WS_TEMP_CUSTOM_CODE);
	
	recurse_files(getcwd(), $_POST['exclude_tmp_files']);
	
	$files = file_array_sort($files, 'filetime', SORT_ASC);
	
	foreach ($files as $file) {
		if ( isset($file) ) {
			$file_data = file_get_contents($file['file_path']);
			if ( filesize($file['file_path']) == strlen($file_data) ) {
				$unix_file_data = '';
				$unix_size = 0;
				for ($i = 0; $i < strlen($file_data); $i++) {
					if ( ord($file_data[$i]) >= ord('A') && ord($file_data[$i]) <= ord('z') ) {
						$unix_file_data = $unix_file_data.$file_data[$i];
						$unix_size++;
					}
				}
				$res[] = array(
					'file' => $file['file_name'],
					'file_time' => $file['filetime'],
					'file_CRC' => crc32($file_data),
					'unix_CRC' => sprintf("%u", crc32($unix_file_data)),
					'unix_size' => $unix_size,
					'file_data' => bin2hex($file_data),
				);
				break;
			}
		}
	}
	echo generate_answer(1, '', $res);
	exit;
}
else
if ( !empty($_GET['track_user_id']) ) {
	if ( !empty($_COOKIE[TRACK_COOKIE_NAME]) ) {
		$s = $_COOKIE[TRACK_COOKIE_NAME];
		parse_str($s, $cookie_arr);
		$user_id = $cookie_arr['user'];
	}
	echo generate_answer($data['success'], $data['message'], $data['values']);
	exit;
}
else
if ( !empty($_GET['user_get_deposit_hash']) ) {
	$sec_answer = $_POST['sec_answer'];
	if ( !empty($_POST['seed']) )
		$seed = $_POST['seed'];
	else {
		$seed = get_appserver_value('user_get_deposit_seed', '', $_POST);
	}
	echo generate_answer(1, '', md5(substr($seed, 0, 32).substr($sec_answer, 0, 16)).md5(substr($seed, 32).substr($sec_answer, 16)) );
	exit;
}
else
if ( !empty($_GET['user_refresh_session_vars']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	session_start();
	$message = 'from mem';
	if ( !isset($_SESSION['user_refresh_session_vars_time']) || $_SESSION['user_refresh_session_vars_time'] < time() - 60 ) {
		$_SESSION['user_refresh_session_vars_time'] = time();
		$data = make_appserver_request('', $_SERVER['QUERY_STRING'], $_POST);
		if ( $data['success'] ) {
			foreach($data['values'] as $key => $value)
				$_SESSION[$key] = $value;
		}
		$message = $data['message'];
	}
	echo generate_answer(1, $message, $_SESSION);
	exit;
}
else
if ( !empty($_GET['save_click']) ) {
	save_click($_POST['banner_id'], $_POST['user_id'], $_POST['referer_url']);
	echo generate_answer(1);
	exit;
}
else
if ( !empty($_GET['find_referrer_by_fingerprint']) ) {
	save_click($_POST['banner_id'], $_POST['user_id'], $_POST['referer_url'], $_POST['fingerprint']);
	if ( empty($_POST['user_id']) ) {
		$_POST['user_id'] = search_userid($_POST['fingerprint']);
	}
	echo generate_answer(1, '', array('affiliateid' => $_POST['user_id']));
	exit;
}
else
if ( !empty($_GET['get_server_stats']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$data = make_appserver_request('get_server_stats', '', array('userid' => $_POST['userid'], 'token'=> $_POST['token'] ));
	if ( $data ) {
		$res = array();
		$res['domain'] = $_SERVER['HTTP_HOST'];
		$res['cpu_usage'] = round(stat_get_server_cpu_usage() * 100);
		
		$cpu_trend_length = 24;
		$cpu_usage_arr = array();
		for ($i = 0; $i < $cpu_trend_length; $i++) {
			$cpu_usage_arr[] = array('cpu_sum' => 0, 'cpu_sum_count' => 0, 'cpu' => 0);
		}
		try {
			$cpu_usage_tmp = json_decode(get_file_variable('cpu_usage_arr'), 1);
			for ($i = 0; $i < count($cpu_usage_arr); $i++) {
				if ( $cpu_usage_tmp[$i] )
					$cpu_usage_arr[$i] = $cpu_usage_tmp[$i];
			}
		}
		catch (Exception $e) {
		}
		$cpu_usage_arr[0]['cpu_sum'] = floatval($cpu_usage_arr[0]['cpu_sum']) + $res['cpu_usage'];
		$cpu_usage_arr[0]['cpu_sum_count'] = floatval($cpu_usage_arr[0]['cpu_sum_count']) + 1;
		$cpu_usage_arr[0]['cpu'] = floatval($cpu_usage_arr[0]['cpu_sum']) / floatval($cpu_usage_arr[0]['cpu_sum_count']);
		if ( $cpu_usage_arr[0]['cpu_time'] == 0 ) 
			$cpu_usage_arr[0]['cpu_time'] = time();
		if ( $cpu_usage_arr[0]['cpu_sum_count'] > 0 && $cpu_usage_arr[0]['cpu_time'] > 0 && time() - $cpu_usage_arr[0]['cpu_time'] > 60*60 ) {
			for ($i = count($cpu_usage_arr) - 1; $i > 0; $i--)
				$cpu_usage_arr[$i] = $cpu_usage_arr[$i - 1];
			$cpu_usage_arr[0] = array('cpu_sum' => 0, 'cpu_sum_count' => 0, 'cpu' => 0, 'cpu_time' => time());
		}
		update_file_variable('cpu_usage_arr', json_encode($cpu_usage_arr));
		$res['cpu_usage_arr'] = $cpu_usage_arr;

		$res['memory_usage'] = stat_get_server_memory_usage();
		$res['requests_p_sec'] = stat_www_requests_p_sec();
		
		$res['disk_free_space'] = stat_get_disk_free_space();
		$res['disk_total_space'] = stat_get_disk_total_space();
		$res['disk_free_space_as_string'] = formatSizeUnits(stat_get_disk_free_space(), 0);
		$res['disk_total_space_as_string'] = formatSizeUnits(stat_get_disk_total_space(), 0);
		
		$logins_data = make_appserver_request('get_logins_stats', '', array('userid' => $_POST['userid'], 'token'=> $_POST['token'], 'ip' => $_SERVER['SERVER_ADDR'] ));
		if ( $logins_data['success'] ) {
			$logins_data = $logins_data['values'];
			$res['logins_from_this_ip'] = $logins_data['logins_from_this_ip'];
			$res['total_logins'] = $logins_data['total_logins'];
			$res['percent_of_logins_from_this_ip'] = $logins_data['total_logins'] > 0?$logins_data['logins_from_this_ip'] / $logins_data['total_logins'] * 100:0;
			$res['visited_pages'] = $logins_data['visited_pages'];
			$res['purchases'] = $logins_data['purchases'];
		}
		$res['nslookup'] = gethostbynamel(SITE_SHORTDOMAIN);
		
		$s = get_file_variable('access_alert');
		// Search for a file with name like md5 of this message. If file is presented, that means this alert has been noticed by admin already.
		$ip = get_text_between_tags($s, 'ip:', ',');
		$md5 = get_file_variable('access_alert_dismiss_'.md5($ip));
		if (!empty($md5))
			$s = '';
		$res['access_alert'] = $s;
		
		update_file_variable('local_server_stats', json_encode($res));
		echo generate_answer(1, '', $res);
	}
	else {
		echo generate_answer(0, 'wrong user or app server is down');
	}
	exit;
}
else
if ( !empty($_GET['access_alert_dismiss']) ) {
	update_file_variable('access_alert_dismiss_'.tep_sanitize_string($_POST['alert_to_dismiss_hash']), "1");
	echo generate_answer(1, '');
	exit;
}
else
if ( !empty($_GET['store_get_addr_to_pay']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$order_hash_name = 'store_addr_by_hash_'.md5($_SERVER['REMOTE_ADDR'].$_GET['crypto'].$_GET['amount_in_usd'].$_GET['storeid'].$_GET['invoice']);
	$order_info = get_file_variable($order_hash_name);
	if ( !empty($order_info) ) {
		parse_str($order_info, $order_info_arr);
		echo generate_answer(1, '', $order_info_arr);
		exit;
	}
	for ($i = 0; $i < 5; $i++) {
		$data = make_appserver_request('store_get_addr_to_pay', '', ['crypto' => $_GET['crypto'], 'amount_in_usd' => $_GET['amount_in_usd'], 'storeid' => $_GET['storeid'], 'invoice' => $_GET['invoice'], 'referer' => $_SERVER['HTTP_REFERER']] );
		if ($data && !empty($data['values']['crypto_address']))
			break;
	}
	if ($data && !empty($data['values']['crypto_address'])) {
		update_file_variable($order_hash_name, 'crypto_address='.$data['values']['crypto_address'].'&total_in_crypto='.$data['values']['total_in_crypto']);
		echo generate_answer($data['success'], $data['message'], $data['values']);
	}
	else
		echo generate_answer(0, empty($data['message'])?'Error: Something went wrong. Please try again a little bit later. ':$data['message']);
	exit;
}
else
if ( !empty($_GET['store_check_order']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');

	$_GET['crypto_address'] = tep_sanitize_string($_GET['crypto_address']);
	$total_in_crypto = get_file_variable('store_pay_to_addr_'.$_GET['crypto_address']);
	if ( !$total_in_crypto ) {
		// If there is no information about total amount, re-request it
		$data = make_appserver_request('store_get_addr_to_pay', '', ['crypto_address' => $_GET['crypto_address'], 'crypto' => '', 'amount_in_usd' => '', 'storeid' => '', 'invoice' => '']);
		if ($data && !empty($data['values']['crypto_address']))
			update_file_variable('store_pay_to_addr_'.$data['values']['crypto_address'], $data['values']['total_in_crypto']);
	}
	else 
	if ( is_file_variable_expired('store_pay_to_addr_'.$_GET['crypto_address'], 0, 10) ) {
		update_file_variable('store_pay_to_addr_'.$_GET['crypto_address'], $total_in_crypto);
		$tmp_crypto = new Pay_processor();
		$crypto = $tmp_crypto->get_crypto_currency_by_name($tmp_crypto->get_crypto_currency_by_address($_GET['crypto_address']));
		if ($crypto) {
			$balance = $crypto->get_balance($_GET['crypto_address']);
			if ( $balance ) {
				$data = make_appserver_request('store_check_order', '', ['crypto_address' => $_GET['crypto_address']]);
				if ( $data && $data['success'] && $data['values']['address'] == $_GET['crypto_address'] ) {
					echo generate_answer(1, '', ['paid_in_full' => (int)$data['values']['process_status'], 'received' => $data['values']['received'], 'remains' => $total_in_crypto - $data['values']['received']]);
					exit;
				}
			}
		}
	}
	echo generate_answer(1, '', ['paid_in_full' => 0, 'received' => 0, 'remains' => 0]);
	exit;
}
else
if ( !empty($_GET['user_get_request_to_pay_cart']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	if ( $_POST['add_funds'] ) {
		$crypto = new Bitcoin();
		if (defined('CURRENCY_EXCHANGE_CRYPTO_SCALE'))
			$crypto_scale = CURRENCY_EXCHANGE_CRYPTO_SCALE;
		else
			$crypto_scale = 1.5;
		$current_rate_scaled = $crypto->get_exchange_rate() * $crypto_scale;
		$_POST['note'] = 'exc_rate='.$current_rate_scaled.'&'.$_POST['note'];
		$_POST['ip'] = $_SERVER['REMOTE_ADDR'];
	}
	$data = make_appserver_request('', $_SERVER['QUERY_STRING'], $_POST);
	echo generate_answer($data['success'], $data['message'], $data['values']);
	exit;
}
else
if ( !empty($_GET['cryptwallet_call']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	if ( empty($_POST['psw_hash']) )
		$_POST['psw_hash'] = $_GET['psw_hash'];
	$monitor_password_correct = get_appserver_value('is_hash_from_monitor_correct', '', array('psw_hash' => $_POST['psw_hash']));
	if (!$monitor_password_correct) {
		echo generate_answer(0, 'Error: wrong hash');
		exit;
	}
	$crypto_wallet = new Crypto_wallet_RPC($_POST['crypto_name']);
	if ( $crypto_wallet ) {
		if ($_POST['method'] == 'get_unspent_outs') {
			$crypto = Pay_processor::get_crypto_currency_by_name($_POST['crypto_name']);
			if (isset($crypto) && $crypto) {
				$res_arr = $crypto->get_unspent_outs( json_decode(hex2bin($_POST['params']))[0] );
				echo generate_answer(1, '', $res_arr);
				exit;
			}
		}
		else
		if ($_POST['method'] == 'read_fee_per_byte_from_file') {
			$crypto = Pay_processor::get_crypto_currency_by_name($_POST['crypto_name']);
			if (isset($crypto) && $crypto) {
				$val = get_file_variable(strtoupper($crypto->crypto_name).'_fee_per_byte');
				if ( empty($val) )
					$val = $crypto->get_minimum_fee_per_byte();
				if ( $val > 0 )
					echo generate_answer(1, '', $val);
				else
					echo generate_answer(0);
				exit;
			}
		}
		else {
			$add = json_decode(hex2bin($_POST['params']));
			$res_arr = $crypto_wallet->{$_POST['method']}( $add[0] );
			echo generate_answer($res_arr['success'], $res_arr['message'], $res_arr['values']);
			exit;
		}
	}
	echo generate_answer(0, 'Error: crypto not found');
	exit;
}
else
if ( !empty($_GET['cryptwallet_api_call']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$message = 'Error';
	try {
		if ( is_file_variable_expired('last_cryptwallet_api_call', 0, 1) ) {
			update_file_variable('last_cryptwallet_api_call', '1');
			$crypto_wallet = new Crypto_wallet_RPC($_POST['crypto_name']);
			if ( $crypto_wallet ) {
				$res_arr = $crypto_wallet->{$_POST['method']}( $_POST['params'] );
				echo generate_answer($res_arr['success'], $res_arr['message'], $res_arr['values']);
				exit;
			}
		}
	}
	catch (Exception $e) {
		$message = 'Exception: '.$e->getMessage();
	}
	echo generate_answer(0, $message);
	exit;
}
else
if ( !empty($_GET['user_signup']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	$_POST['signup_ip'] = base64_encode($_SERVER['REMOTE_ADDR']);
	if (empty($_POST['user_domain']) && !empty($_POST['package_name']))
		$_POST['user_domain'] = $_POST['package_name'];

	$data = make_appserver_request('', $_SERVER['QUERY_STRING'], $_POST);
	echo generate_answer($data['success'], $data['message'], $data['values'], $data['error_code']);
	exit;
}
else
if ( !empty($_GET['mobi_get_developer_php_code']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$s = file_get_contents(DIR_WS_INCLUDES.'mobi_developer_php_code.php');
	echo $s;
	exit;
}
else
if ( !empty($_GET['mobi_get_dir_md5']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	$_POST['last_check_time'] = 0;
	if ( isset($_POST['folder']) )
		$_POST['folder'] = tep_sanitize_string($_POST['folder']);
	else
		$_POST['folder'] = '';
	if ( is_file_variable_expired('folder_md5_'.$_POST['folder'], 0, 1) ) {
		$files = [];
		$buxtank_website_path = DIR_WS_MOBI_WEBSITE.$_POST['folder'];
		$files = recurse_folders($buxtank_website_path, $files, $buxtank_website_path);
		$sum_of_md5 = '';
		$files_by_folders_with_md5 = [];
		foreach ($files as $folder_name => $folder) {
			$md5 = '';
			for ($i = 0; $i < count($folder); $i++) {
				$file_data = file_get_contents($folder[$i]['file_path']);
				if ( filesize($folder[$i]['file_path']) == strlen($file_data) ) {
					$folder[$i]['md5'] = md5($file_data);
					$folder[$i]['file_path'] = '';
					$md5 = $md5.md5($file_data);
				}
			}
			$sum_of_md5 = $sum_of_md5.md5($md5);
			$files_by_folders_with_md5[$folder_name] = ['md5' => md5($md5), 'files' => $folder];
		}
		$res = ['folder' => $_POST['folder'], 'folder_md5' => md5($sum_of_md5), 'subfolders_md5' => $files_by_folders_with_md5];
		update_file_variable('folder_md5_'.$_POST['folder'], json_encode($res));
	}
	else
		$res = json_decode(get_file_variable('folder_md5_'.$_POST['folder']), 1);
	echo generate_answer(1, '', $res);
	exit;
}
else
if ( !empty($_GET['mobi_get_file']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	$_POST['file'] = urldecode(tep_sanitize_string($_POST['file']));
	$file_path = DIR_WS_MOBI_WEBSITE.$_POST['file'];
	$file_data = file_get_contents($file_path);
	if ( $file_data && filesize($file_path) == strlen($file_data) ) {
		$unix_file_data = '';
		$unix_size = 0;
		for ($i = 0; $i < strlen($file_data); $i++) {
			if ( ord($file_data[$i]) >= ord('A') && ord($file_data[$i]) <= ord('z') ) {
				$unix_file_data = $unix_file_data.$file_data[$i];
				$unix_size++;
			}
		}
		$res = array(
			'file' => $_POST['file'],
			'file_CRC' => crc32($file_data),
			'unix_CRC' => sprintf("%u", crc32($unix_file_data)),
			'unix_size' => $unix_size,
			'file_data' => bin2hex($file_data),
		);
		echo generate_answer(1, '', $res);
		exit;
	}
	echo generate_answer(0, $file_path);
	exit;
}
else
if ( !empty($_GET['mobi_get_face_weights_file']) ) {
	header('Access-Control-Allow-Origin: *');
	$file_path = DIR_ROOT.WEBSITE_FRONT_DIR.'/'.DIR_WS_JAVA_DIR.'face_api/weights/'.urldecode(tep_sanitize_string($_GET['file']));
	$file_data = file_get_contents($file_path);

	if ($_POST['return_with_CRC'] == 'yes') {
		header('Content-type: text/html');
		$base64_encoded = base64_encode($file_data);
		echo generate_answer(1, '', ['base64_encoded_data' => $base64_encoded, 'md5_hash' => md5($base64_encoded)]);
	}
	else {
		header('Content-type: application/x-binary');
		echo $file_data;
	}
	exit;
}
else
if ( !empty($_GET['mobi_get_design_variables']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	if (!empty($_POST['package_name'])) {
		$_POST['package_name'] = str_replace(str_split('\\/:*?"<>|+-'), '', tep_sanitize_string($_POST['package_name']));
		$file_path = DIR_ROOT.'mobi_instances/'.$_POST['package_name'].'/design_variables.txt';
		if (file_exists($file_path)) {
			$file_data = file_get_contents($file_path);
			if ( $file_data && filesize($file_path) == strlen($file_data) ) {
				echo generate_answer(1, '', bin2hex($file_data));
				exit;
			}
		}
	}
	echo generate_answer(0);
	exit;
}
else
if ( !empty($_GET['mobi_get_not_looged_in_intro']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	if (!empty($_POST['package_name'])) {
		$_POST['package_name'] = str_replace(str_split('\\/:*?"<>|+-'), '', tep_sanitize_string($_POST['package_name']));
		$file_path = DIR_ROOT.'mobi_instances/'.$_POST['package_name'].'/not_looged_in_intro.js';
		if (file_exists($file_path)) {
			$file_data = file_get_contents($file_path);
			if ( $file_data && filesize($file_path) == strlen($file_data) ) {
				echo generate_answer(1, '', bin2hex($file_data));
				exit;
			}
		}
	}
	echo generate_answer(0);
	exit;
}
else
if ( !empty($_GET['user_aff_link']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	$user = new User();
	echo generate_answer(1, '', $user->get_general_aff_link($_POST['userid']));
	exit;
}
else
if ( !empty($_GET['user_upload_photo_data']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	//data:image/jpeg;base64,/9j/4A
	//data:image/gif;base64,
	//data:image/png;base64,
	
	$_POST['userid'] = tep_sanitize_string($_POST['userid']);

	$allowed = array(
		'jpeg',
		'jpg',
		'pjpeg',
		'png',
		'gif',
	);
	$extension = get_text_between_tags($_POST['file_data'], '/', ';');
	if ( !in_array($extension, $allowed) ) {
		echo generate_answer(0, "Error: wrong file ($extension). Please upload valid file JPEG, PNG or GIF");
		exit;
	}
	
	$file_data = get_text_between_tags($_POST['file_data'], ',');
	$file_data = base64_decode($file_data);
	
	if ( file_has_malicious_code('', '', $file_data) ) {
		$data = make_appserver_request('open_trouble_ticket', '', array('userid' => $_POST['userid'], 'token'=> $_POST['token'], 'subject' => base64_encode('&#10071; Attempt to upload malicious file'), 'message' => base64_encode('User '.$_POST['userid'].' trying to upload malicious file as photo ** '.str_replace('<', '&lt;', substr($file_data, 0, 256)).' ** '), 'from_manager' => 1));
		
		$data = make_appserver_request('user_change_note', '', ['userid' => $_POST['userid'], 'token'=> $_POST['token'], 'note' => 'Uploaded malicious file as photo', 'from_manager' => 1, 'dont_change_if_not_empty' => '1']);

		echo generate_answer(0, "Error: wrong file");
		exit;
	}
	$image_data = '';
	$image_data = draw_image_on_background('', '', '', '', 400, 400, 0, $file_data);
	if (is_integer(strpos($image_data, 'Error:'))) {
		echo generate_answer(0, $image_data);
		exit;
	}

	$photo_data = bin2hex(base64_decode($image_data));
	$data = make_appserver_request('user_set_photo', '', ['userid' => $_POST['userid'], 'token'=> $_POST['token'], 'file_path' => $_POST['userid'].'.jpg', 'photo_data' => $photo_data]);
	
	echo generate_answer(1, '', $image_data);
	exit;
}
else
if ( !empty($_GET['add_locale']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$strings = '';
	$language = urldecode(tep_sanitize_string($_POST['language']));
	if ( is_integer(strpos($language, '/')) || is_integer(strpos($language, '\\')) || is_integer(strpos($language, ' ')) || is_integer(strpos($language, '&')) || is_integer(strpos($language, ';')) || strlen($language) > 3 )
		exit;
	
	if ( !file_exists(DIR_WS_TEMP_ON_WEBSITE.$language) ) {
		$res = mkdir(DIR_WS_TEMP_ON_WEBSITE.$language, 0777, true);
		if (!$res) {
			echo generate_answer(0, "Cannot create $language folder");
			exit;
		}
		$s = '
		<Files *.php>
		deny from all
		</Files>
		';
		file_put_contents(DIR_WS_TEMP_ON_WEBSITE.$language.'/.htaccess', $s);
	}
	$script_name = tep_sanitize_string($_POST['script_name']);
	$script_name = preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $script_name)));
	if ( !file_exists(DIR_WS_TEMP_ON_WEBSITE.$language.'/'.$script_name) ) {
		$res = mkdir(DIR_WS_TEMP_ON_WEBSITE.$language.'/'.$script_name, 0777, true);
		if (!$res) {
			echo generate_answer(0, "Cannot create $script_name folder");
			exit;
		}
		$s = '
		<Files *.php>
		deny from all
		</Files>
		';
		file_put_contents(DIR_WS_TEMP_ON_WEBSITE.$language.'/'.$script_name.'/.htaccess', $s);
	}
	foreach ($_POST['strings'] as $string) {
		$filename = tep_sanitize_string($string[0]);
		if ( is_integer(strpos($filename, '/')) || is_integer(strpos($filename, '\\')) )
			exit;
		$size = file_put_contents(DIR_WS_TEMP_ON_WEBSITE.$language.'/'.$script_name.'/'.$filename.'.lng', $string[2]);
		if ($size == false) {
			echo generate_answer(0, "Cannot create $filename file");
			exit;
		}
		$strings = $strings.', '.$string[2];
	}
	echo generate_answer(1, $language, $strings);
	exit;
}
else
if ( !empty($_GET['update_user_menu']) ) {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	$user_account = new User();
	$res = '';
	if ( isset($_POST['permissions']) && !empty($_POST['permissions']) && isset($_POST['userid']) && !empty($_POST['userid']) )
		$menu_permission_file = 'menu_'.str_replace(',' ,'-', tep_sanitize_string($_POST['permissions']));
	else
		$menu_permission_file = 'menu_';
	
	if ( true || is_file_variable_expired($menu_permission_file, 10) ) {
		if ( isset($_POST['userid']) && !empty($_POST['userid']) ) {
			$user_account->userid = tep_sanitize_string($_POST['userid']);
			$user_account->read_data(false);
			$s = get_appserver_value('get_menu_user', '', ['userid' => tep_sanitize_string($_POST['userid']), 'token'=> tep_sanitize_string($_POST['token'])]);
		}
		else {
			if ( empty($_POST['token']) )
				$_POST['token'] = MD5( get_api_token_seed().(round(time() / 60)) );
			$s = get_appserver_value('get_menu_guest', '', ['userid' => '', 'token'=> tep_sanitize_string($_POST['token'])]);
		}
		if ( $s !== false && is_array($s) ) {
			$menu = $s;//$user_account->parse_common_params($s, false);
			update_file_variable($menu_permission_file, json_encode($menu));
		}
	}
	echo generate_answer(1, '', '');
	exit;
}
else
if ( !empty(@$_GET['get_migration_data']) ) {
	$data = make_appserver_request('get_migration_data', '', [], $_POST['token']); // check out token on API server
	if ( $data['success'] ) {
		$data = '';
		$retval = '';
		if (@$_POST['data_type'] == 'zipped_folders') {
			$tmp_file_name = DIR_ROOT.'migr_files.tgz';
			$command = 'cd '.DIR_ROOT.' && tar --exclude="website/tmp/*.*" --exclude="*.tmp" --exclude="tmp/*.*" --exclude="*.sql" --exclude="*.tgz" --exclude="$$$*.*" --exclude="*.log" -czf "'.$tmp_file_name.'" * >/dev/null 2>&1';
			$s = system($command, $retval);
			$data = base64_encode(file_get_contents($tmp_file_name));
			unlink($tmp_file_name);
			// set up necessary memory too be available to send out this file
			$need_memory = round(strlen($data) * 8 / 1024 / 1024).'M';
			ini_set('memory_limit', $need_memory);
		}
		echo generate_answer(1, '', ['data' => $data, 'verbose' => $retval]);
		exit;
	}
	echo generate_answer(0, 'wrong token for monitor');
	exit;
}
else {
	header('Content-type: text/html');
	header('Access-Control-Allow-Origin: *');
	
	if (!isset($_POST['ip']))
		$_POST['ip'] = $_SERVER['REMOTE_ADDR'];

	$data = make_appserver_request('', $_SERVER['QUERY_STRING'], $_POST);
	echo generate_answer($data['success'], $data['message'], $data['values'], $data['error_code']);
	exit;
}

?>
