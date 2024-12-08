//<?php

function recurse_folders($folder, $files, $buxtank_website_path = '')
{
	if ( $folder[strlen($folder) - 1] != '\\' )
		$folder = $folder.'\\';
	
	$folders = scandir($folder, SCANDIR_SORT_ASCENDING );
	if ( count($folders) > 0 ) {
		foreach ( $folders as $short_file_name ) {
			$file_name = $folder.$short_file_name;
			if ( !is_dir($file_name) ) {
				$filetime = filemtime($file_name);
				$just_file_name = substr($file_name, strlen($folder) + 0);
				$relative_folder = substr($folder, strlen($buxtank_website_path));
				$files[$relative_folder][] = array('filetime' => $filetime, 'file_name' => $just_file_name, 'file_path' => $file_name, 'folder' => $folder);
			}
			else {
				if ( $short_file_name != '.' && $short_file_name != '..' ) {
					$files = recurse_folders($file_name, $files, $buxtank_website_path);
				}
			}
		}
	}
	return $files;
}

function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
		'method' => 'POST',
		'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		return false;
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		echo "Problem reading data from $url, $php_errormsg\r\n";
		return false;
	}
	return $response;
}

function decrypt_folder_path($folder_name)
{
	$local_folder_arr = explode('\\', $folder_name);
	$local_folder = '';
	foreach ($local_folder_arr as $sub_folder) {
		if (!empty($sub_folder))
			$local_folder = $local_folder.(empty($local_folder) ? '' : '/').hex2bin($sub_folder);
	}
	return $local_folder;
}

function get_file_name_from_path($path)
{
	$res = '';
	for ($i = strlen($path) - 1; $i >= 0; $i--) {
		if ($path[$i] == '/')
			break;
		$res = $path[$i].$res;
	}
	return $res;
}

if ( !function_exists('hex2bin') ) {
	function hex2bin($h)
	{
		if (!is_string($h)) 
			return null;
		$r = '';
		for ($a = 0; $a < strlen($h); $a+=2) { 
			$r=$r.chr( hexdec($h[$a].$h[($a + 1)]) ); 
		}
		return $r;
	}
}

$buxtank_website_path = getenv('buxtank_website_path');
echo 'env: '.$buxtank_website_path."\r\n";

$files = [];
$res = [];

$files = recurse_folders($buxtank_website_path, $files, $buxtank_website_path);

$files_by_folders_with_md5 = [];
foreach ($files as $folder_name => $folder) {
	$md5 = '';
	for ($i = 0; $i < count($folder); $i++) {
		if ( $folder[$i]['file_name'] != 'Array' && $folder[$i]['file_name'] != 'Array.tmp') {
			$s = file_get_contents($folder[$i]['file_path']);
			$file_data = hex2bin($s);
			$folder[$i]['md5'] = md5($file_data);
			$folder[$i]['file_name'] = hex2bin($folder[$i]['file_name']);
			$md5 = $md5.md5($file_data);
		}
	}
	$folder_name = decrypt_folder_path($folder_name).'/';
	$files_by_folders_with_md5[$folder_name] = ['md5' => md5($md5), 'files' => $folder];
}

//echo "files_by_folders_with_md5: \r\n"; var_dump($files_by_folders_with_md5);

$url = 'https://buxtank.com/api/';
$res = do_post_request($url.'mobi_get_dir_md5', '');
if ($res === false) {
	echo "Error: no answer from API server: $url mobi_get_dir_md5\r\n";
}
else {
	$res_arr = json_decode($res, true);
	if ( $res_arr['success'] ) {
		$file_to_download = '';
		$local_folder = '';
		$local_file = '';
		foreach ( $res_arr['values']['subfolders_md5'] as $folder_name => $folder_data ) {
			echo "checking folder: '$folder_name'\r\n";
			$loccal_folder_name = (empty($folder_name) ? '/' : $folder_name);
			echo "loccal_folder_name: '$loccal_folder_name'\r\n";
			if ( !isset($files_by_folders_with_md5[$loccal_folder_name]) || $folder_data['md5'] != $files_by_folders_with_md5[$loccal_folder_name]['md5'] ) {
				echo "not mach folder '$folder_name'\r\n"; 
				foreach ( $folder_data['files'] as $rem_file ) {
					$file_found = false;
					//echo "searching for file: '".$rem_file['file_name']."'\r\n";
					
					if (isset($files_by_folders_with_md5[$loccal_folder_name])) {
						foreach ( $files_by_folders_with_md5[$loccal_folder_name]['files'] as $local_file ) {
							//echo "local file found: '".$local_file['file_name']."'\r\n";
							if ( $rem_file['file_name'] == $local_file['file_name'] ) {
								$file_found = true;
								if ( $rem_file['md5'] != $local_file['md5'] ) {
									echo "found differnt md5 in file ".$local_file['file_name'].", local ".$local_file['md5']." <> remote: ".$rem_file['md5'].", local folder: $loccal_folder_name \r\n"; 
									$file_to_download = $folder_name.$rem_file['file_name'];
									break;
								}
							}
						}
					}
					
					$local_folder_arr = explode('/', $folder_name);
					//echo "folder_name: $folder_name\r\n"; 
					$local_folder = '';
					foreach ($local_folder_arr as $sub_folder) {
						if (!empty($sub_folder))
							$local_folder = $local_folder.(empty($local_folder) ? '' : '\\').bin2hex($sub_folder);
					}

					if (!empty($file_to_download))
						break;

					if ( !$file_found ) {
						//echo "file: '".$rem_file['file_name']." not found'\r\n";
						$file_to_download = $folder_name.$rem_file['file_name'];
						
						//echo "not found in local_folder: $local_folder\r\n"; 
						//$local_file = bin2hex($rem_file['file_name']);
						//echo "not found local_file: $local_file\r\n"; 
						break;
					}
				}
				if ( !empty($file_to_download) ) {
					echo "file_to_download: $file_to_download\r\n";
					$res = do_post_request($url.'mobi_get_file', 'file='.urlencode($file_to_download));
					if ($res === false) {
						echo "Error: no answer from API server: $url mobi_get_file\r\n";
					}
					else {
						$res_arr = json_decode($res, true);
						if ( $res_arr['success'] ) {
							$file_record = $res_arr['values'];
							$local_file = get_file_name_from_path($file_to_download);
							echo "res local_file: $local_file\r\n";
							$local_file = bin2hex($local_file);
							echo "local_file: $local_file\r\n";
							echo "local_folder: $local_folder\r\n";
							$file_path = $buxtank_website_path.''.(empty($local_folder) ? '' : $local_folder.'\\').$local_file;
							echo "File to update: $file_path\r\n";
							
							$unix_CRC = sprintf("%u", $file_record['unix_CRC']);
							$original_unix_size = (int)$file_record['unix_size'];
							$file_dir = dirname($file_path);
							echo "file_dir: $file_dir\r\n";
							if ( !file_exists($file_dir) ) {
								echo "make dir: $file_dir\r\n";
								mkdir($file_dir, 0777, true);
							}
							
							file_put_contents($file_path.'.tmp', $file_record['file_data']);
							
							$tmp_file = hex2bin(file_get_contents($file_path.'.tmp'));
							
							$unix_file_data = '';
							$unix_size = 0;
							for ($i = 0; $i < strlen($tmp_file); $i++) {
								if ( ord($tmp_file[$i]) >= ord('A') && ord($tmp_file[$i]) <= ord('z') ) {
									$unix_file_data = $unix_file_data.$tmp_file[$i];
									$unix_size++;
								}
							}
							//echo "original_unix_size: $original_unix_size, unix_size: $unix_size, file_path: $file_path, counted crc32: ".crc32($tmp_file)." (".sprintf("%u", crc32($tmp_file)).") = received CRC: ".$file_record['file_CRC'].",  unix crc32: ".crc32($unix_file_data)." (".sprintf("%u", crc32($unix_file_data)).") = received unix crc: $unix_CRC, (".sprintf("%u", crc32($unix_CRC)).")\r\n";
							
							if ( $original_unix_size == $unix_size && (
								crc32($tmp_file) == $file_record['file_CRC'] 
								|| sprintf("%u", crc32($tmp_file)) == $file_record['file_CRC'] 
								|| crc32($unix_file_data) == $unix_CRC) 
								) 
							{
								rename($file_path.'.tmp', $file_path);
								echo "File $file_path sucessfully updated.\r\n";
							}
							else {
								echo 'Error: cannot rename tmp file: "'.$file_path.'.tmp'.'"\r\n';
							}
						}
					}
					break;
				}
				
			}
		}
	}
	else {
		echo "Error: answer from API server: ".$res_arr['message']."\r\n";
	}
}


/*
function recurse_folders($folder, $files, $buxtank_website_path = '')
{
	//global $files;
	
	if ( $folder[strlen($folder) - 1] != '\\' )
		$folder = $folder.'\\';
	
	$folders = scandir($folder, SCANDIR_SORT_ASCENDING );
	foreach ( $folders as $short_file_name ) {
		$file_name = $folder.$short_file_name;
		if ( !is_dir($file_name) ) {
			$filetime = filemtime($file_name);
			$just_file_name = substr($file_name, strlen($folder) + 0);
			$relative_folder = substr($folder, strlen($buxtank_website_path));
			$files[$relative_folder][] = array('filetime' => $filetime, 'file_name' => $just_file_name, 'file_path' => $file_name, 'folder' => $folder);
		}
		else {
			if ( $short_file_name != '.' && $short_file_name != '..' ) {
				$files = recurse_folders($file_name, $files, $buxtank_website_path);
			}
		}
	}
	return $files;
}

function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
		'method' => 'POST',
		'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp) {
		return false;
	}
	$response = @stream_get_contents($fp);
	if ($response === false) {
		echo "Problem reading data from $url, $php_errormsg\r\n";
		return false;
	}
	return $response;
}

if ( !function_exists('hex2bin') ) {
	function hex2bin($h)
	{
		if (!is_string($h)) 
			return null;
		$r = '';
		for ($a = 0; $a < strlen($h); $a+=2) { 
			$r=$r.chr( hexdec($h[$a].$h[($a + 1)]) ); 
		}
		return $r;
	}
}

$buxtank_website_path = getenv('buxtank_website_path');
echo 'env: '.$buxtank_website_path."\r\n";

$files = [];
$res = [];

$files = recurse_folders($buxtank_website_path, $files, $buxtank_website_path);

$sum_of_md5 = '';
$files_by_folders_with_md5 = [];
foreach ($files as $folder_name => $folder) {
	//echo "local $folder_name\r\n";
	$md5 = '';
	for ($i = 0; $i < count($folder); $i++) {
		$file_data = file_get_contents($folder[$i]['file_path']);
		if ( filesize($folder[$i]['file_path']) == strlen($file_data) ) {
			$folder[$i]['md5'] = md5($file_data);
			$folder[$i]['file_path'] = str_replace('\\', '/', $folder[$i]['file_path']);
			$md5 = $md5.md5($file_data);
		}
	}
	$sum_of_md5 = $sum_of_md5.md5($md5);
	$files_by_folders_with_md5[str_replace('\\', '/', $folder_name)] = ['md5' => md5($md5), 'files' => $folder];
}
//var_dump($files_by_folders_with_md5); exit;

//var_dump($files_by_folders_sorted); exit;

$website_md5 = md5($sum_of_md5);
echo "website md5: $website_md5\r\n";

$url = 'https://buxtank.com/api/';
$res = do_post_request($url.'mobi_get_dir_md5', '');
if ($res === false) {
	echo "Error: no answer from API server: $url mobi_get_dir_md5\r\n";
}
else {
	$res_arr = json_decode($res, true);
	//var_dump($res_arr);

	if ( $res_arr['success'] ) {
		if ( $res_arr['values']['folder_md5'] != $website_md5 ) {
			$file_to_download = '';
			foreach ( $res_arr['values']['subfolders_md5'] as $folder_name => $folder_data ) {
				//echo "folder '$folder_name'\r\n";
				if ( $folder_data['md5'] != $files_by_folders_with_md5[$folder_name]['md5'] ) {
					//echo "not mach folder '$folder_name'\r\n";
					foreach ( $folder_data['files'] as $rem_file ) {
						$file_found = false;
						foreach ( $files_by_folders_with_md5[$folder_name]['files'] as $local_file ) {
							if ( $rem_file['file_name'] == $local_file['file_name'] ) {
								$file_found = true;
								if ( $rem_file['md5'] != $local_file['md5'] ) {
									$file_to_download = $folder_name.$rem_file['file_name'];
									break;
								}
							}
						}
						if ( !$file_found ) {
							$file_to_download = $folder_name.$rem_file['file_name'];
							break;
						}
					}
					if ( !empty($file_to_download) ) {
						echo "file_to_download: $file_to_download\r\n";
						$res = do_post_request($url.'mobi_get_file', 'file='.urlencode($file_to_download));
						if ($res === false) {
							echo "Error: no answer from API server: $url mobi_get_file\r\n";
						}
						else {
							$res_arr = json_decode($res, true);
							//var_dump($res_arr);
							if ( $res_arr['success'] ) {
								$file_record = $res_arr['values'];
								$file_name = str_replace('/', '\\', $file_record['file']);
								if ( $file_name[0] == '\\' )
									$file_name = substr($file_name, 1);
								$file_path = $buxtank_website_path.$file_name;
								echo "File to update: $file_path\r\n";
								
								$unix_CRC = sprintf("%u", $file_record['unix_CRC']);
								$original_unix_size = (int)$file_record['unix_size'];
								$file_dir = dirname($file_path);
								//echo "file_dir: $file_dir\r\n";
								
								if ( !file_exists($file_dir) ) {
									echo 'make dir: '.$file_dir.'\r\n';
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
								//echo "original_unix_size: $original_unix_size, unix_size: $unix_size, file_path: $file_path, counted crc32: ".crc32($tmp_file)." (".sprintf("%u", crc32($tmp_file)).") = received CRC: ".$file_record['file_CRC'].",  unix crc32: ".crc32($unix_file_data)." (".sprintf("%u", crc32($unix_file_data)).") = received unix crc: $unix_CRC, (".sprintf("%u", crc32($unix_CRC)).")\r\n";
								
								if ( $original_unix_size == $unix_size && (
									crc32($tmp_file) == $file_record['file_CRC'] 
									|| sprintf("%u", crc32($tmp_file)) == $file_record['file_CRC'] 
									|| crc32($unix_file_data) == $unix_CRC) 
									) 
								{
									rename($file_path.'.tmp', $file_path);
									echo "File $file_path sucessfully updated.\r\n";
								}
								else {
									echo 'Error: cannot rename tmp file: "'.$file_path.'.tmp'.'"\r\n';
								}
							}
						}
						break;
					}
				}
			}
			
		}
	}
}
*/