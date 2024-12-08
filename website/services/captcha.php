<?php

require_once('../../includes/classes/CaptchaGenerator.class.php');

function xor_crypt($key, $text)
{
	$longkey = '';
	$result = '';
	for ($i = 0; $i <= intval( strlen($text) / strlen($key) ); $i++ ) {
		$longkey = $longkey.$key;
	}
	
	for ($i = 0; $i < strlen($text); $i++ ) {
		$toto = chr( intval( ord($text[$i]) ) ^ intval( ord($longkey[$i]) ) );
		$result = $result.$toto;
	}
	RETURN base64_encode($result);
}

$captchaGenerator = new CaptchaGenerator('');
if ( empty($_GET['width']) )
	$_GET['width'] = 161;
if ( empty($_GET['height']) )
	$_GET['height'] = 66;
	
$captchaGenerator->setSize($_GET['width'], $_GET['height']);
if ( !empty($_GET['char_min_size']) )
	$captchaGenerator->setCharMinMaxSize($_GET['char_min_size'], $_GET['char_max_size']);
else
	$captchaGenerator->setCharMinMaxSize(25, 30);
if ( isset($_GET['max_rotation']) )
	$captchaGenerator->maxRotation = $_GET['max_rotation'];
if ( isset($_GET['jpeg_quality']) )
	$captchaGenerator->jpegQuality = $_GET['jpeg_quality'];
if ( $_GET['draw_toward_hor_line'] )
	$captchaGenerator->draw_toward_hor_line = 1;

if ( $_GET['generate_formula'] ) {
	$first_numb = rand(0, 5);
	$second_numb = rand(0, $first_numb);
	if ( $second_numb > 4 )
		$second_numb = 4;
	$symbol = rand(0, 1);
	if ( $symbol )
		$symbol = 1;
	else
		$symbol = -1;
	$s = $first_numb.($symbol > 0?'+':'-').$second_numb.'=?';
	$captcha_psw = ''.(round($first_numb + $symbol * $second_numb));
}
else
if ( $_GET['generate_text'] ) {
	$s = $_GET['generate_text'];
	$captcha_psw = $s;
}
else {
	$s = $captchaGenerator->generateRandText(4, ord('0'), ord('9'));
	$captcha_psw = $s;
}
$captchaGenerator->setText( $s );
$crypted_value = xor_crypt('captcha psw', $captcha_psw);

if ( $_GET['use_session'] == '1' ) {
	session_start();
	$_SESSION['captcha'] = $crypted_value;
}
else
	setcookie('cpc', $crypted_value, 0, '/');

echo $captchaGenerator->generate();

?>

