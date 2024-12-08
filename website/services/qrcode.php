<?php  
include_once('../../includes/config.inc.php');

include_once(DIR_WS_INCLUDES.'phpqrcode/qrlib.php');

$filename = 'qr_code'.md5($_SERVER['QUERY_STRING']).'.png';

//echo "filename: $filename<br>";

$errorCorrectionLevel = 'L';

if (isset($_GET['size']))
	$matrixPointSize = min(max((int)$_GET['size'], 1), 10);
else
	$matrixPointSize = 4;

$_GET['data'] = trim($_GET['data']);
if (empty($_GET['data']))
	$_GET['data'] = 'error: no data';

QRcode::png($_GET['data'], DIR_WS_TEMP_ON_WEBSITE.$filename, $errorCorrectionLevel, $matrixPointSize, 2);

$location = '/'.DIR_WS_TEMP_NAME.$filename;
header('Location: '.$location);
?>
