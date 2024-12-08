<?php
define('DEBUG_MODE', 1);
include_once('config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
//require_once(DIR_WS_CLASSES.'user.class.php');

function recurse_file_times($folder, $exclude_tmp = 1)
{
	global $recent_filetime;
	if ( $folder[strlen($folder) - 1] != '/' )
		$folder = $folder.'/';
	$folders = glob($folder.'*');
	foreach ( $folders as $file_name ) {
		if ( !is_dir($file_name) ) {
			$filectime = filemtime($file_name);
			if ( $filectime > $recent_filetime )
				$recent_filetime = $filectime;
		}
		else {
			if ( !is_integer(strpos($file_name, 'tmp')) || !$exclude_tmp )
				recurse_file_times($file_name, $exclude_tmp);
		}
	}
}

$showDebugInfo = defined('DEBUG_MODE');

if ( !isset($_GET['exclude_tmp_files']) ) 
	$_GET['exclude_tmp_files'] = 1;

chdir('..');

global $recent_filetime;
global $files;
global $message;
$files = array();

if ( (int)get_file_variable('DESIGN_MODE') == 1 ) {
	if ( $showDebugInfo ) echo 'Error: Design Mode<br>'."\r\n";
	exit;
}
$recent_filetime = '';

if ( !isset($_GET['tmp_custom_code']) )
	$_GET['tmp_custom_code'] = '';

$recent_filetime = get_file_variable('last_synchro_file_time'.$_GET['tmp_custom_code']);

if ( !$_GET['exclude_tmp_files'] )
	chdir('..');

if ( !empty($_GET['tmp_custom_code']) ) {
	if ( !defined('DIR_WS_TEMP_CUSTOM_CODE') ) 
		exit;
	if ( !file_exists(DIR_WS_TEMP_CUSTOM_CODE) ) {
		if ( $showDebugInfo ) echo 'make dir: '.DIR_WS_TEMP_CUSTOM_CODE.'<br>'."\r\n";
		mkdir(DIR_WS_TEMP_CUSTOM_CODE, 0777, true);
	}
	chdir(DIR_WS_TEMP_CUSTOM_CODE);
}
$dir_root = DIR_ROOT;
if ( $dir_root[strlen($dir_root) - 1] != '/' )
	$dir_root = $dir_root.'/';
if ( $showDebugInfo ) echo 'DIR_ROOT: '.$dir_root.'<br>';

if ( empty($recent_filetime) )
	recurse_file_times($dir_root, $_GET['exclude_tmp_files']);

$url = '';
if ( defined('SCRIPT_UPDATE_SERVER') )
	$url = SCRIPT_UPDATE_SERVER;
	
if ( empty($url) ) {
	if ( $showDebugInfo ) echo 'Error: update URL is empty<br>'."\r\n";
	exit;
}

if ( $showDebugInfo ) echo "url: $url<br>"."\r\n"; 

$target_domain = get_domain($url, false);
$target_short_domain = get_domain($url, true);
$local_short_domain = SITE_SHORTDOMAIN;//get_domain($_SERVER['SERVER_NAME'], true);
$rem_ip = gethostbyname($target_domain);
$loc_ip = get_text_between_tags(exec('hostname -I'), '', ' '); //$_SERVER['SERVER_ADDR'];
$loc_ip_str = exec('hostname -I');
$loc_ip_arr = explode(' ', $loc_ip_str);
if ( $showDebugInfo ) {echo "local IPs:\r\n"; var_dump($loc_ip_arr); echo "\r\r";}

if ( $showDebugInfo ) echo "target_domain: $target_domain, target_short_domain: $target_short_domain, local_short_domain: $local_short_domain, rem_ip: $rem_ip\r\n";
if ( (!in_array($rem_ip, $loc_ip_arr) || $target_short_domain != $local_short_domain) ) {
	if ( $url[strlen($url) - 1] != '/' )
		$url = $url.'/';
	$url = $url.'api/synchro_files';
	if ( $showDebugInfo ) echo "url: $url<br>"."\r\n";
	$res = do_post_request($url, 'last_check_time='.($recent_filetime).'&last_synchro_file='.urlencode(get_file_variable('last_synchro_file'.$_GET['tmp_custom_code'])));//.'&token='.get_api_token_seed())
	if ($res === false) {
		if ( $showDebugInfo ) echo "Error: no answer from API server: $url<br>";
	}
	else {
		$res_arr = json_decode($res, true);
		if ( $showDebugInfo ) var_dump($res_arr);
		if ( $res_arr['success'] ) {
			foreach ( $res_arr['values'] as $file_record ) {
				if ( !empty($file_record['file']) ) {
					$file_name = $file_record['file'];
					$message = 'File to update: '.$file_name.'<br>';
					if ( $file_name[0] == '/' )
						$file_name = substr($file_name, 1);
					$file_path = $dir_root.$file_name;
					if ( $showDebugInfo ) echo 'File to update: '.$file_name.'<br>'."\r\n";
					$unix_CRC = sprintf("%u", $file_record['unix_CRC']);
					$original_unix_size = (int)$file_record['unix_size'];
					$file_dir = dirname($file_path);
					if ( !file_exists($file_dir) ) {
						if ( $showDebugInfo ) echo 'make dir: '.$file_dir.'<br>'."\r\n";
						mkdir($file_dir, 0777, true);
					}
					file_put_contents($file_path.'.tmp', hex2bin($file_record['file_data']));
					$tmp_file = file_get_contents($file_path.'.tmp');
					$unix_file_data = '';
					$unix_size = 0;
					for ($i = 0; $i < strlen($tmp_file); $i++) {
						if ( ord($tmp_file[$i]) >= ord('A') && ord($tmp_file[$i]) <= ord('z') ) {
							$unix_file_data = $unix_file_data.$tmp_file[$i];
							$unix_size++;
						}
					}
					if ( $showDebugInfo ) echo "original_unix_size: $original_unix_size, unix_size: $unix_size, file_path: $file_path, 
						counted crc32: ".crc32($tmp_file)." (".sprintf("%u", crc32($tmp_file)).")
						= received CRC: ".$file_record['file_CRC'].", 
						unix crc32: ".crc32($unix_file_data)." (".sprintf("%u", crc32($unix_file_data)).") 
						= received unix crc: $unix_CRC, (".sprintf("%u", crc32($unix_CRC)).")<br>"."\r\n";
					if ( $original_unix_size == $unix_size && (
						crc32($tmp_file) == $file_record['file_CRC'] 
						|| sprintf("%u", crc32($tmp_file)) == $file_record['file_CRC'] 
						|| crc32($unix_file_data) == $unix_CRC) 
						) 
					{
						rename($file_path.'.tmp', $file_path);
						update_file_variable('last_synchro_file'.$_GET['tmp_custom_code'], $file_name);
						update_file_variable('last_synchro_file_time'.$_GET['tmp_custom_code'], $file_record['file_time']);
						if ( is_integer(strpos($file_name, '.sh')) ) {
							$s = str_replace('$', '\$', $file_path);
							system('chmod +x '.$s, $retval);
							if ( $showDebugInfo ) echo 'File access changed (chmod +x '.$s.').<br>'."\r\n";
						}
						if ( $showDebugInfo ) echo 'File sucessfully updated.<br>'."\r\n";
					}
					else {
						if ( $showDebugInfo ) echo 'Error: cannot create tmp file: "'.$file_path.'.tmp'.'"<br>'."\r\n";
					}
				}
				else {
					if ( $showDebugInfo ) echo 'Error: empty file name<br>'."\r\n";	
				}
			}
		}
		else {
			if ( $showDebugInfo ) echo 'Error: API answered with error. '.$res_arr['message'].'<br>'."\r\n";
		}
	}
}
else {
	if ( $showDebugInfo ) echo 'Error: host has the same domain'."<br>\r\n";
}

if ($showDebugInfo) echo '<br>All done<br>'."\r\n";

?>