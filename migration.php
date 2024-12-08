<?php
function do_post_request($url, $data, $optional_headers = null)
{
	$response = false;
	$params = array(
		'http' => array('header' => "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\nUser-Agent:MyAgent/1.0\r\n", 'method' => 'POST', 'content' => $data),
		"ssl" => array("verify_peer" => false, "verify_peer_name" => false),
	);
	if ($optional_headers !== null)
		$params['http']['header'] = $optional_headers;
	
	$ctx = stream_context_create($params);
	try {
		$fp = @fopen($url, 'r', false, $ctx);
		if ( !$fp ) {
			return false;
		}
	}
	catch (Exception $e) {
		return false;
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		return false;
	}
	return $response;
}

function make_api_request($API_server_domain = '', $request = '', $get_params = '', $post_params = '', $token = '')
{
	if (empty($API_server_domain))
		return ['success' => 0, 'message' => 'Empty API_server_short_domain'];

	if ( is_array($get_params) ) {
		$s = '';
		foreach($get_params as $key => $value)
			$s = $s.$key.'='.urlencode($value).'&';
		$get_params = $s;
	}
	if ( is_array($post_params) ) {
		$s = '';
		foreach($post_params as $key => $value) {
			if ( !isset($value) )
				$value = '';
			$s = $s.$key.'='.urlencode($value).'&';
		}
		$post_params = $s;
	}
	if ( empty($token) ) {
		return ['success' => 0, 'message' => 'Empty token'];
	}
	$post_params = 'token='.$token.'&'.$post_params;
	$server_number = 1;

	$url = 'http://'.$API_server_domain.'/services/api.php?'.$request.'=1&'.$get_params;
	try {
		echo "connectiong to $url\r\n";
		$res = trim(do_post_request($url, $post_params));
		if ($res !== false && !empty($res)) {
			return json_decode($res, true);
		}
		else {
			return ['success' => 0, 'message' => "server returned: $res"];
		}
	}
	catch (Exception $e) {
		return ['success' => 0, 'message' => 'Exception: '.$e->getMessage()];
	}
	return ['success' => 0, 'message' => "no responce from server: $url"];
}

global $link;

$password = readline('Enter the password of user "monitor":'); // 'Info for Me';//
$api_url = readline('Enter server domain (like srv.com):');
$server_domain = $api_url;
echo "Enter:\r\n0 - make copy of server\r\n1 - create new server\r\n";
$make_new_server = intval(readline("Copy or new server:"));
if ($make_new_server) {
	$server_domain = readline('Enter the domain of new server (like srv.com):'); 
}
echo "Enter step:\r\n0 - get folders\r\n1 - create apache files\r\n2 - add synchro files in crontab\r\n";
$step = readline("Step:");
/*
$password = '';
$api_url = '';
$server_domain = '';
$step = 0;
$make_new_server = 1;
*/
define('CONSTANT_PASSWORD_HASH_SUFFIX', '26a0F1d0qHM27p8Z');
$new_psw3 = md5(md5($password).CONSTANT_PASSWORD_HASH_SUFFIX);
$token = MD5( $new_psw3.date('z') );

if (empty($step)) {
	echo "reading data...\r\n";
	$res = make_api_request($api_url, 'get_migration_data', '', ['data_type' => 'zipped_folders'], $token);
	if ($res && $res['success']) {
		$data = base64_decode($res['values']['data']);
		echo strlen($data)." bytes received \r\n";
		$tmp_file_name = 'migr_files.tgz';
		file_put_contents($tmp_file_name, $data);
		echo "saved to file: $tmp_file_name\r\n";
		system('tar -xzf '.$tmp_file_name.' >/dev/null 2>&1', $retval);
		echo "file unzipped\r\n";
		unlink($tmp_file_name);

		$ini_arr = [];
		$core_settings_file_name = 'ini_data/$$$core_settings.ini';
		if ($file = fopen($core_settings_file_name, "r")) {
			while(!feof($file)) {
				$line = trim(fgets($file));
				if (is_integer(strpos($line, 'SITE_SHORTDOMAIN='))) $line = "SITE_SHORTDOMAIN=$server_domain";
				if (is_integer(strpos($line, 'HTTP_PREFIX='))) $line = "HTTP_PREFIX=http://";
				$ini_arr[] = $line;
			}
			fclose($file);
		}
		$outstr = '';
		foreach ($ini_arr as $str) {
			$outstr = $outstr.$str."\n";
		}
		file_put_contents($core_settings_file_name, $outstr);
		echo "changed file: $core_settings_file_name\r\n";

		system('find * -type d -exec chmod augo+rwx {} \; >/dev/null 2>&1', $retval);
		system('find * -type f -exec chmod augo+rwx {} \; >/dev/null 2>&1', $retval);
		
	}
	else {
		echo "\033[91mError: ".($res && $res['message'] ? $res['message'] : "no answer from: $api_url")."\033[0m\r\n";
		exit;
	}
}

if (empty($step) || $step <= 1) { // creating apache files
	$apache_file_name = "/etc/apache2/sites-available/$server_domain";
	if (!file_exists($apache_file_name)) {
		echo "Creating apache file: $apache_file_name\r\n";
		file_put_contents($apache_file_name, "
<VirtualHost *:80>
    ServerName $server_domain
    ServerAlias www.$server_domain
    DocumentRoot ".dirname(__FILE__)."/website/
    DirectoryIndex index.php
    IndexIgnore *
</VirtualHost>
		");
	}
	else {
		echo "\033[93mApache file: $apache_file_name already exists. Creation skipped.\033[0m\r\n";
	}

	echo "Enter:\r\n0 - server doesn't have HTTPS files\r\n1 - server has HTTPS files\r\n";
	$need_https = intval(readline("Need HTTPS:"));
	if ($need_https) {
		$apache_file_name = "/etc/apache2/sites-available/".$server_domain."_443";
		if (!file_exists($apache_file_name)) {
			echo "Creating apache file: $apache_file_name\r\n";
			$all_files_found = true;
			
			$chainfile = glob(dirname(__FILE__).'/*.ca-bundle');
			$filename_not_found = '';
			if ($chainfile && is_array($chainfile) && count($chainfile) > 0)
				$chainfile = $chainfile[0];
			else {
				$chainfile = glob(dirname(__FILE__).'/*.ca*');
				if ($chainfile && is_array($chainfile) && count($chainfile) > 0)
					$chainfile = $chainfile[0];
				else {
					$all_files_found = false;
					$filename_not_found = 'SSLCertificateChainFile';
				}
			}
			
			$keyfile = glob(dirname(__FILE__).'/*.key');
			$filename_not_found = '';
			if ($keyfile && is_array($keyfile) && count($keyfile) > 0)
				$keyfile = $keyfile[0];
			else {
				$keyfile = glob(dirname(__FILE__).'/*_key.*');
				if ($keyfile && is_array($keyfile) && count($keyfile) > 0)
					$keyfile = $keyfile[0];
				else {
					$all_files_found = false;
					$filename_not_found = 'SSLCertificateKeyFile';
				}
			}

			if ($all_files_found) {
				file_put_contents($apache_file_name, "
<VirtualHost *:443>
    ServerName $server_domain
    ServerAlias www.$server_domain
    DocumentRoot ".dirname(__FILE__)."/website/
    DirectoryIndex index.php
    IndexIgnore *
    SSLEngine on
    SSLCertificateFile ".glob(dirname(__FILE__).'/*.crt')[0]."
    SSLCertificateKeyFile $keyfile
    SSLCertificateChainFile $chainfile
</VirtualHost>
				");
			}
			else {
				echo "\033[91m Error: file: $filename_not_found not found \033[0m\r\n";
			}
		}
		else {
			echo "\033[93mApache file: $apache_file_name already exists. Creation skipped.\033[0m\r\n";
		}
	}
}
if (empty($step) || $step <= 2) { // add synchro files in cron
	echo "Adding synchro files in crontab\r\n";
	$s = file_get_contents('/etc/crontab');
	if ( !is_integer(strpos($s, dirname(__FILE__))) ) {
		file_put_contents('/etc/crontab', $s.
"
# Synchronize files for web server: $server_domain
*   * * * *     root    php ".dirname(__FILE__)."/includes/service_synchro_files.php ".dirname(__FILE__)."/ >/dev/null 2>&1
#
");
		echo "Synchro files has been added in crontab\033[0m\r\n";
	}
	else {
		echo "\033[93mSynchro files already presented in crontab\033[0m\r\n";
	}
}
echo "\033[92mAll done\033[0m\r\n";
echo "\033[92m Do not forget to run: service apache2 restart \033[0m\r\n";
?>