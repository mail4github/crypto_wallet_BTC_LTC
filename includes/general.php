<?php

function tep_sanitize_string($string, $max_length = 0, $allow_html = false, $only_standard_chars = false, 
	$replace_non_standard_chars = '<br>', $replace_quotes = true, $remove_quotes = false, $its_unicode = false, $preg_remove = '') 
{
	if ( $only_standard_chars ) {
		for ($i = 0; $i < strlen($string); $i++ ) {
			if ( intval( ord($string[$i]) ) < intval( ord(' ') ) || intval( ord($string[$i]) ) > intval( ord('~') ) )
				$string[$i] = chr(1);
		}
		$string = str_replace(chr(1), $replace_non_standard_chars, $string);
	}
	
	$string = str_replace("\\", '&#92;', $string);
	if ( $replace_quotes ) {
		$string = preg_replace('/"/', '&quot;', $string);
		$string = preg_replace('/\'/', '&#39;', $string);
	}
	if ( $remove_quotes ) {
		$string = preg_replace('/"/', '', $string);
		$string = preg_replace('/\'/', '', $string);
	}
	if ( !empty($preg_remove) )
		$string = preg_replace($preg_remove, '', $string);

	if ( !$allow_html ) {
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
	}
	if ( $max_length > 0 ) {
		if ( $its_unicode )
			$string = mb_substr($string, 0, $max_length, 'HTML-ENTITIES');
		else
			$string = substr($string, 0, $max_length);
	}
		
	return $string;
}

function remove_injections($string, $max_length = 0)
{
	if ( $max_length > 0 )
		$string = substr($string, 0, $max_length);
	$string = str_replace(' ', '', str_replace('%', '', str_replace('0x', '', str_replace('-', '', $string))));
	return $string;
}

// Will return the number of days between the two dates passed in
function count_days( $a, $b )
{
    // First we need to break these dates into their constituent parts:
	$gd_a = getdate( $a );
    $gd_b = getdate( $b );
 
    // Now recreate these timestamps, based upon noon on each day
    // The specific time doesn't matter but it must be the same each day
    $a_new = mktime( 12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year'] );
    $b_new = mktime( 12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year'] );
	
    // Subtract these two numbers and divide by the number of seconds in a
    //  day. Round the result since crossing over a daylight savings time
    //  barrier will cause this time to be off by an hour or two.
    return round( abs( $a_new - $b_new ) / 86400 );
}

function seconds_in_redable($seconds) {
   $then = new DateTime(date('Y-m-d H:i:s', 0));
   $now = new DateTime(date('Y-m-d H:i:s', $seconds));
   $diff = $then->diff($now);
   return array('years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i, 'seconds' => $diff->s);
}

function show_plural($value, $name, $show_value = false)
{
	return ($show_value?$value.' ':'').$name.($value > 1 || $value <= 0?'s':'');
}

function get_interval($seconds)
{
	$interval_arr = seconds_in_redable($seconds);
	return (
	$interval_arr['years'] > 0?
		$interval_arr['years'].' '.show_plural($interval_arr['years'], 'year').($interval_arr['months']>0?' '.$interval_arr['months'].' '.show_plural($interval_arr['months'], 'month'):'')
		:($interval_arr['months'] > 0?
			$interval_arr['months'].' '.show_plural($interval_arr['months'], 'month').($interval_arr['days']>0?' '.$interval_arr['days'].' '.show_plural($interval_arr['days'], 'day'):'')
			:($interval_arr['days']>0?
				$interval_arr['days'].' '.show_plural($interval_arr['days'], 'day')
				:$interval_arr['hours'].' '.show_plural($interval_arr['hours'], 'hour')
			)
		)
	);
}

// Our array of 'small words' which shouldn't be capitalised if 
// they aren't the first word. Add your own words to taste. 

$smallwordsarray = array( 'of','a','the','and','an','or','nor','but','is','if','then','else','when','at','from','by','on','off','for','in','out','over','to','into','with','your','more','info','which','also','there'); 
global $smallwordsarray;

function strtotitle($title) // Converts $title to Title Case, and returns the result. 
{ 
	global $smallwordsarray;
	
	// Split the string into separate words 
	$words = explode(' ', $title); 
	foreach ($words as $key => $word) 
	{ 
		// If this word is the first, or it's not one of our small words, capitalise it 
		// with ucwords(). 
		if ( $key == 0 or !in_array($word, $smallwordsarray) ) 
			$words[$key] = ucwords($word); 
	} // Join the words back into a string 
	$newtitle = implode(' ', $words); 
	return $newtitle; 
} 

function WriteToLogFile($message, $log_name, $max_file_size_in_bytes = 1000000)
{
	if ( defined('DIR_WS_LOG') ) {
		$s = DIR_WS_LOG.$log_name.'.log';
		if ( !is_integer(file_put_contents($s, date("y/m/d H:i:s ").$message."\r\n", FILE_APPEND)) ) {
			$there_is_log_error = true;
		}
		if ( filesize($s) > $max_file_size_in_bytes ) 
			rename($s, DIR_WS_LOG.'_delete_'.$log_name.'_'.date("Ymd").'.log');
	}
}

function ChunkText($text, $max_len = 60, $str_delimeter = "\r\n")
{
	$new_text = '';
	$strings = preg_split('/$\R?^/m', $text);
	foreach($strings as $ss) { 
		$words = explode(" ", $ss);
		$new_str = '';
		foreach($words as $ww) { 
			if ( strlen($new_str) + strlen($ww) > $max_len ) {
				$new_text = $new_text.$str_delimeter.$new_str;
				$new_str = $ww;
			}
			else
				$new_str = $new_str.' '.$ww;
		}
		$new_text = $new_text.$str_delimeter.$new_str;
	}
	return $new_text;
}

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

function xor_decrypt($key, $c_text)
{
	$longkey = '';
	$result = '';
	$c_text = base64_decode($c_text);
	for ($i = 0; $i <= intval( strlen($c_text) / strlen($key) ); $i++ ) {
		$longkey = $longkey.$key;
	}
	for ($i = 0; $i < strlen($c_text); $i++ ) {
		$toto = chr( intval( ord($c_text[$i]) ) ^ intval( ord($longkey[$i]) ) );
		$result = $result.$toto;
	}
	RETURN $result;
}

$numb = '0123456789';
$lwr = 'abcdefghijklmnopqrstuvwxyz';
$upr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

function isValid($parm, $val) 
{
	if ( !isset($parm) ) 
		return false;
	for ($i = 0; $i < strlen($parm); $i++) {
		if ( !is_integer(strpos($val, $parm[$i])) ) {
			return false;
		}
	}
	return true;
}

function isInteger($parm)
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $numb);
}

function isNumber($parm)
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $numb.'.,-+');
}

function isLower($parm)
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $lwr);
}

function isUpper($parm) 
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $upr);
}

function isAlpha($parm) 
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $lwr.$upr);
}

function isAlphanum($parm) 
{
	global $lwr;
	global $upr;
	global $numb;
	return isValid($parm, $lwr.$upr.$numb);
}

function convert_to_alphanum($str)
{
	$res = '';
	for ($i = 0; $i < strlen($str); $i++)
		if (isAlphanum($str[$i]))
			$res = $res.$str[$i];
	return $res;
}

function convert_html2text($html_string, $convert_ASCII_codes = true )
{
    //remove PHP if it exists
    while( substr_count( $html_string, '<'.'?' ) && substr_count( $html_string, '?'.'>' ) && strpos( $html_string, '?'.'>', strpos( $html_string, '<'.'?' ) ) > strpos( $html_string, '<'.'?' ) ) {
        $html_string = substr( $html_string, 0, strpos( $html_string, '<'.'?' ) ) . substr( $html_string, strpos( $html_string, '?'.'>', strpos( $html_string, '<'.'?' ) ) + 2 ); }
    //remove comments
    while( substr_count( $html_string, '<!--' ) && substr_count( $html_string, '-->' ) && strpos( $html_string, '-->', strpos( $html_string, '<!--' ) ) > strpos( $html_string, '<!--' ) ) 
	{
    	$html_string = substr( $html_string, 0, strpos( $html_string, '<!--' ) ) . substr( $html_string, strpos( $html_string, '-->', strpos( $html_string, '<!--' ) ) + 3 ); 
	}
    //now make sure all HTML tags are correctly written (> not in between quotes)
    $goodStr = $html_string;
    
    //now that the page is valid (I hope) for strip_tags, strip all unwanted tags
    $goodStr = strip_tags( $goodStr, '<title><hr><h1><h2><h3><h4><h5><h6><div><p><br><pre><sup><ul><ol><dl><dt><table><caption><tr><li><dd><th><td><a><area><img><form><input><textarea><button><select><option>' );
    //strip extra whitespace except between <pre> and <textarea> tags
    $html_string = preg_split( "/<\/?pre[^>]*>/i", $goodStr );
    for( $x = 0; isset($html_string[$x]) && is_string( $html_string[$x] ); $x++ ) {
        if( $x % 2 ) { $html_string[$x] = '<pre>'.$html_string[$x].'</pre>'; } else {
            $goodStr = preg_split( "/<\/?textarea[^>]*>/i", $html_string[$x] );
            for( $z = 0; isset($goodStr[$z]) && is_string( $goodStr[$z] ); $z++ ) {
                if( $z % 2 ) { $goodStr[$z] = '<textarea>'.$goodStr[$z].'</textarea>'; } else {
                    $goodStr[$z] = preg_replace( "/\s+/", ' ', $goodStr[$z] );
            } }
            $html_string[$x] = implode('',$goodStr);
    } }
    $goodStr = implode('',$html_string);
	if ( $convert_ASCII_codes ) {
		$search = array(
		        "/\r/",                                  // Non-legal carriage return
		        "/[\n\t]+/",                             // Newlines and tabs
		        '/<br[^>]*>/i',                          // <br>
		        '/&nbsp;/i',
		        '/&quot;/i',
		        '/&gt;/i',
		        '/&lt;/i',
		        '/&amp;/i',
		        '/&copy;/i',
		        '/&trade;/i',
		        '/&#8220;/',
		        '/&#8221;/',
		        '/&#8211;/',
		        '/&#8217;/',
		        '/&#38;/',
		        '/&#169;/',
		        '/&#8482;/',
		        '/&#151;/',
		        '/&#147;/',
		        '/&#148;/',
		        '/&#149;/',
		        '/&reg;/i',
		        '/&bull;/i',
		        '/&[&;]+;/i'
		);
	
		$replace = array(
		        '',                                     // Non-legal carriage return
		        ' ',                                    // Newlines and tabs
		        "\n",                                   // <br>
		        ' ',
		        '"',
		        '>',
		        '<',
		        '&',
		        '(c)',
		        '(tm)',
		        '"',
		        '"',
		        '-',
		        "'",
		        '&',
		        '(c)',
		        '(tm)',
		        '--',
		        '"',
		        '"',
		        '*',
		        '(R)',
		        '*',
		        ''
		);
	    
	    $goodStr = preg_replace( $search, $replace, $goodStr );
    }
    //remove all options from select inputs
    $goodStr = preg_replace( "/<option[^>]*>[^<]*/i", '', $goodStr );
    //replace all tags with their text equivalents
    $goodStr = preg_replace( "/<(\/title|hr)[^>]*>/i", "\n          --------------------\n", $goodStr );
    $goodStr = preg_replace( "/<(h|div|p)[^>]*>/i", "\n", $goodStr );
    $goodStr = preg_replace( "/<sup[^>]*>/i", '^', $goodStr );
    $goodStr = preg_replace( "/<(ul|ol|dl|dt|table|caption|\/textarea|tr[^>]*>\s*<(td|th))[^>]*>/i", "\n", $goodStr );
    $goodStr = preg_replace( "/<li[^>]*>/i", "\n· ", $goodStr );
    $goodStr = preg_replace( "/<dd[^>]*>/i", "\n\t", $goodStr );
    $goodStr = preg_replace( "/<(th|td)[^>]*>/i", "\t", $goodStr );
    $goodStr = preg_replace('/<br[^>]*>/i', "\n", $goodStr);
    $goodStr = preg_replace( "/<a[^>]* href=(\"((?!\"|#|javascript:)[^\"#]*)(\"|#)|'((?!'|#|javascript:)[^'#]*)('|#)|((?!'|\"|>|#|javascript:)[^#\"'> ]*))[^>]*>/i", "[LINK: $2$4$6] ", $goodStr );
    $goodStr = preg_replace( "/<img[^>]* alt=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "[IMAGE: $2$3$4] ", $goodStr );
    $goodStr = preg_replace( "/<form[^>]* action=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "\n[FORM: $2$3$4] ", $goodStr );
    $goodStr = preg_replace( "/<(input|textarea|button|select)[^>]*>/i", "[INPUT] ", $goodStr );
    
	//strip all remaining tags (mostly closing tags)
    $goodStr = strip_tags( $goodStr );
    //convert HTML entities
    $goodStr = strtr( $goodStr, array_flip( get_html_translation_table( HTML_ENTITIES ) ) );
    preg_replace( "/&#(\d+);/me", "chr('$1')", $goodStr );
    
	//make sure there are no more than 3 linebreaks in a row and trim whitespace
	$goodStr = preg_replace( "/^\n*|\n*$/", '', preg_replace( "/[ \t]+(\n|$)/", "$1", preg_replace( "/\n(\s*\n){2}/", "\n\n\n", preg_replace( "/\r\n?|\f/", "\n", str_replace( chr(160), ' ', $goodStr ) ) ) ) );
	
	$goodStr = str_replace('[LINK: ]', '', $goodStr);
    
    return $goodStr;
}

function get_text_from_html($html_text, $lowercase = true)
{
	if ($lowercase)
		$html_text = strtolower($html_text);
	while( substr_count( $html_text, '< ' ) ) 
		$html_text = str_replace('< ', '<', $html_text);
	while( substr_count( $html_text, '</ ' ) ) 
		$html_text = str_replace('</ ', '<', $html_text);
	while( substr_count( $html_text, ' >' ) ) 
		$html_text = str_replace(' >', '>', $html_text);
	while( substr_count( $html_text, '<style' ) && substr_count( $html_text, '</style>' ) ) 
		if ( ! delete_text_between_tags($html_text, '<style', '</style>') )
			break;
	while( substr_count( $html_text, '<script' ) && substr_count( $html_text, '</script>' ) ) 
		if ( ! delete_text_between_tags($html_text, '<script', '</script>') )
			break;
	while( substr_count( $html_text, '<' ) && substr_count( $html_text, '>' ) ) 
		if ( ! delete_text_between_tags($html_text, '<', '>') )
			break;
	while( substr_count( $html_text, '&' ) && substr_count( $html_text, ';' ) ) 
		if ( ! delete_text_between_tags($html_text, '&', ';') )
			break;
	while( substr_count( $html_text, "\r" ) ) 
		$html_text = str_replace("\r", ' ', $html_text);
	while( substr_count( $html_text, "\n" ) ) 
		$html_text = str_replace("\n", ' ', $html_text);
	while( substr_count( $html_text, "\t" ) ) 
		$html_text = str_replace("\t", ' ', $html_text);
	while( substr_count( $html_text, "  " ) ) 
		$html_text = str_replace("  ", ' ', $html_text);
	return $html_text;
}

if ( !function_exists('hex2bin') ) {
	function hex2bin($h)
	{
		if (!is_string($h)) 
			return null;
		$r = '';
		for ($a=0; $a < strlen($h); $a+=2) { 
			$r.=chr(hexdec($h[$a].$h[($a+1)])); 
		}
		return $r;
	}
}

function hex32_2bin($h)
{
	if (!is_string($h)) 
		return null;
	$r = '';
	for ($a=0; $a < strlen($h); $a+=4) { 
		$r.=chr(hexdec($h[$a].$h[($a+1)].$h[($a+2)].$h[($a+3)])); 
	}
	return $r;
}

function hex2bin32($h, $show_unicode = false)
{
	if (!is_string($h)) 
		return null;
	$r = '';
	for ($a = 0; $a < strlen($h); $a+=4) { 
		$s = hexdec( $h[$a].$h[($a + 1)].$h[($a + 2)].$h[($a + 3)] ); 
		$j = intval($s);
		if ( $show_unicode ) {
			if ( $j <= 255 )
				$c = chr($j);
			else
				$c = '&#'.$j.';';
		}
		else {
			if ( $j > 255 ) {
				$j = $j % 256 + 176;
			}
			$c = chr($j);
		}
		$r = $r.$c;
	}
	return $r;
}

function get_text_between_tags($inputStr, $delimeterLeft = '', $delimeterRight = '', $debug = false) { 
	if ( empty($delimeterLeft) )
		$posLeft = 0;
	else
		$posLeft = strpos($inputStr, $delimeterLeft); 
    if ( $posLeft === false ) { 
		if ( $debug )
            echo "Warning: left delimiter '".$delimeterLeft."' not found"; 
        return false; 
    } 
    $posLeft = $posLeft + strlen($delimeterLeft); 
	if ( empty($delimeterRight) )
		$posRight = strlen($inputStr);
	else {
		$posRight = strpos($inputStr, $delimeterRight, $posLeft); 
		if ( $posRight === false ) { 
			$posRight = strlen($inputStr);
			if ( $debug )
				echo "Warning: right delimiter '{$delimeterRight}' not found"; 
		} 
	}
    return substr($inputStr, $posLeft, $posRight - $posLeft); 
}

function delete_text_between_tags(&$inputStr, $delimeterLeft = '', $delimeterRight = '', $debug = false, $replace_with = '') 
{ 
	if ( empty($delimeterLeft) )
		$posLeft = 0;
	else
		$posLeft = stripos($inputStr, $delimeterLeft); 
    if ( $posLeft === false ) { 
        if ( $debug )
            echo "Warning: left delimiter '{$delimeterLeft}' not found"; 
        return false; 
    } 
	if ( empty($delimeterRight) )
		$posRight = strlen($inputStr);
	else {
		$posRight = stripos($inputStr, $delimeterRight, $posLeft); 
		if ( $posRight === false ) { 
			if ( $debug )
				echo "Warning: right delimiter '{$delimeterRight}' not found"; 
			return false; 
		}
	}
	$posRight = $posRight + strlen($delimeterRight); 
	$inputStr = substr_replace($inputStr, $replace_with, $posLeft, $posRight - $posLeft);
	return true;
} 

function make_synonyms($intext, $number_of_paragraphs = 3, $paragraph_beg_tag = '<p>', $paragraph_end_tag = '</p>', $make_anyway = false)
{
	if ( !is_integer(strpos($intext, '<paragraph/>')) && !$make_anyway )
		return $intext;
	
	$header = '';
	if ( is_integer(strpos($intext, '<syn_header>')) ) {
		$header = get_text_between_tags($intext, '<syn_header>', '</syn_header>');
		delete_text_between_tags($intext, '<syn_header>', '</syn_header>', true);
	}
	
	$footer = '';
	if ( is_integer(strpos($intext, '<syn_footer>')) ) {
		$footer = get_text_between_tags($intext, '<syn_footer>', '</syn_footer>');
		delete_text_between_tags($intext, '<syn_footer>', '</syn_footer>', true);
	}
	
	$paragraphs = explode('<paragraph/>', $intext);
	for ( $i = 0; $i < count($paragraphs); $i++ ) {
		$paragraphs[$i] = rtrim(ltrim($paragraphs[$i]));
	}
	if ( $number_of_paragraphs > 0 ) {
		$intext = '';
		if ( !empty($paragraphs[0]) ) {
			$intext = $intext.$paragraph_beg_tag.$paragraphs[0].$paragraph_end_tag;
			$paragraphs[0] = '';
		}
		for ( $i = 0; $i < $number_of_paragraphs; $i++ ) {
			if ( count($paragraphs) > 0 ) {
				for ( $j = 0; $j < 10; $j++ ) {
					$k = rand(0, count($paragraphs) - 1);
					if ( !empty($paragraphs[$k]) ) {
						$intext = $intext.$paragraph_beg_tag.$paragraphs[$k].$paragraph_end_tag;
						$paragraphs[$k] = '';
						break;
					}
					else {
						
					}
				}
			}
		}
	}
	else {
		$intext = '';
		foreach ($paragraphs as $value) {
			$intext = $intext.$paragraph_beg_tag.$value.$paragraph_end_tag;
		}
	}
	
	if ( !empty($header) )
		$intext = $paragraph_beg_tag.$header.$paragraph_end_tag.$intext;
	
	if ( !empty($footer) )
		$intext = $intext.$paragraph_beg_tag.$footer.$paragraph_end_tag;
	
	while ( is_integer(strpos($intext, '[')) && is_integer(strpos($intext, ']')) )
	{
		$i = strpos($intext, '[');
		$j = strpos($intext, ']');
		
		if ( $j - $i > 1  ) {
			$synonyms_str = substr($intext, $i + 1, $j - 1 - $i);
			$synonyms = explode('|', $synonyms_str);
			$k = rand(0, count($synonyms) - 1);
			$s = $synonyms[$k];
			$intext = substr_replace($intext, $s, $i, $j - $i + 1);
		}
		else
			break;
	}
	return $intext;
}

function getRgbColorsByHsv($hue, $saturation, $brightness)
{
	if ( $hue < 0 )
		$hue = 360 + $hue;
		
	if ( $hue >= 360 )
		$hue = $hue % 360;
	
	$Hi = (int)floor($hue / 60);
	$f = $hue / 60 - $Hi;
	
	if ( $saturation > 1)
		$saturation= $saturation / 100;
	if( $brightness > 1 )
		$brightness = $brightness / 100;
	
	$p = ($brightness * ( 1 - $saturation));
	$q = ($brightness * ( 1 - ($f * $saturation)));
	$t = ($brightness * ( 1 - ( ( 1 - $f ) * $saturation)));
	
	switch($Hi){
		case 0: // Red is the dominant color 
			$red = $brightness;
			$green = $t;
			$blue = $p;
		break;
		case 1: // Green is the dominant color 
			$red = $q;
			$green = $brightness;
			$blue = $p;
		break;
		case 2: // Green is the dominant color 
			$red = $p;
			$green = $brightness;
			$blue = $t;
		break;
		case 3: // Blue is the dominant color 
			$red = $p;
			$green = $q;
			$blue = $brightness;
		break;
		case 4: // Blue is the dominant color 
			$red = $t;
			$green = $p;
			$blue = $brightness;
		break;
		default: // Red is the dominant color 
			$red = $brightness;
			$green = $p;
			$blue = $q;
		break;
	}
	
	if ( $saturation == 0 ){
		$red = $brightness;
		$green = $brightness;
		$blue = $brightness;
	}
	
	$red = $red * 255;
	$green = $green * 255;
	$blue = $blue * 255;
	
	$red = round($red);
	$green = round($green);
	$blue = round($blue);
	
	$retArray = array();
	
	$retArray['red'] = bin2hex(chr($red));
	$retArray['green'] = bin2hex(chr($green));
	$retArray['blue'] = bin2hex(chr($blue));
	return $retArray;
}

function shorter_text($text, $max_symbols)
{
	if ( strlen($text) <= $max_symbols )
		return $text;
	$result = '';
	$space_found = false;
	$s = substr($text, 0, $max_symbols);
	for ($i = strlen($s) - 1; $i > 0; $i = $i - 1 ) {
		$result = substr($s, 0, $i + 1);
		if ( $s[$i] == ' ' ) {
			$space_found = true;
			break;
		}
	}
	if ( empty($result) )
		$result = substr($text, 0, $max_symbols);
	return $result;
}

function leading_zero($aNumber, $intPart, $floatPart = NULL, $dec_point = NULL, $thousands_sep = NULL) {        //Note: The $thousands_sep has no real function because it will be "disturbed" by plain leading zeros -> the main goal of the function
	$formattedNumber = $aNumber;
	if ( !is_null($floatPart) ) {    //without 3rd parameters the "float part" of the float shouldn't be touched
		$formattedNumber = number_format($formattedNumber, $floatPart, $dec_point, $thousands_sep);
	}
	$formattedNumber = str_repeat("0", ($intPart + -1 - floor(log10($formattedNumber)))).$formattedNumber;
	return $formattedNumber;
}

function get_domain($url, $short_domain = true, $force_short_domain = false)
{
	if ( empty($url) )
		return '';
	$website_host = $url;
	if ( is_integer(strpos($website_host, '://')) )
		$website_host = parse_url($website_host, PHP_URL_HOST);
	$website_host = strtolower(trim($website_host));
	if ( $force_short_domain
		|| $short_domain 
		&& substr_count($website_host, '.') > 1 
		&& !is_integer(strpos($website_host, '.com.af'))
		&& !is_integer(strpos($website_host, '.com.ag'))
		&& !is_integer(strpos($website_host, '.com.ar')) 	
		&& !is_integer(strpos($website_host, '.com.au')) 	
		&& !is_integer(strpos($website_host, '.com.bd'))	
		&& !is_integer(strpos($website_host, '.com.bh'))
		&& !is_integer(strpos($website_host, '.com.br')) 
		&& !is_integer(strpos($website_host, '.com.cn')) 
		&& !is_integer(strpos($website_host, '.com.co'))
		&& !is_integer(strpos($website_host, '.com.cy')) 
		&& !is_integer(strpos($website_host, '.com.do')) 
		&& !is_integer(strpos($website_host, '.com.eg'))
		&& !is_integer(strpos($website_host, '.com.et'))
		&& !is_integer(strpos($website_host, '.com.ec'))
		&& !is_integer(strpos($website_host, '.com.jm'))
		&& !is_integer(strpos($website_host, '.com.gh'))
		&& !is_integer(strpos($website_host, '.com.hk'))
		&& !is_integer(strpos($website_host, '.com.kh'))
		&& !is_integer(strpos($website_host, '.com.kw'))
		&& !is_integer(strpos($website_host, '.com.lb')) 
		&& !is_integer(strpos($website_host, '.com.ly')) 
		&& !is_integer(strpos($website_host, '.com.mm')) 
		&& !is_integer(strpos($website_host, '.com.mt'))
		&& !is_integer(strpos($website_host, '.com.mx')) 
		&& !is_integer(strpos($website_host, '.com.my'))
		&& !is_integer(strpos($website_host, '.com.na'))
		&& !is_integer(strpos($website_host, '.com.ng'))
		&& !is_integer(strpos($website_host, '.com.np'))
		&& !is_integer(strpos($website_host, '.com.pa')) 
		&& !is_integer(strpos($website_host, '.com.pe'))
		&& !is_integer(strpos($website_host, '.com.ph'))
		&& !is_integer(strpos($website_host, '.com.pg'))
		&& !is_integer(strpos($website_host, '.com.pr')) 
		&& !is_integer(strpos($website_host, '.com.pk'))
		&& !is_integer(strpos($website_host, '.com.sa')) 
		&& !is_integer(strpos($website_host, '.com.sg')) 
		&& !is_integer(strpos($website_host, '.com.tr')) 
		&& !is_integer(strpos($website_host, '.com.vc'))
		&& !is_integer(strpos($website_host, '.com.ve')) 
		&& !is_integer(strpos($website_host, '.com.vn')) 
		&& !is_integer(strpos($website_host, '.com.ua'))
		&& !is_integer(strpos($website_host, '.com.uy'))
		
		&& !is_integer(strpos($website_host, '.co.bw'))
		&& !is_integer(strpos($website_host, '.co.cr')) 
		&& !is_integer(strpos($website_host, '.co.id')) 
		&& !is_integer(strpos($website_host, '.co.il')) 
		&& !is_integer(strpos($website_host, '.co.in'))
		&& !is_integer(strpos($website_host, '.co.jp'))
		&& !is_integer(strpos($website_host, '.co.nz'))
		&& !is_integer(strpos($website_host, '.co.th'))
		&& !is_integer(strpos($website_host, '.co.tz'))
		&& !is_integer(strpos($website_host, '.co.uk')) 
		&& !is_integer(strpos($website_host, '.co.ke'))
		&& !is_integer(strpos($website_host, '.co.kr'))
		&& !is_integer(strpos($website_host, '.co.ls'))
		&& !is_integer(strpos($website_host, '.co.ma'))
		&& !is_integer(strpos($website_host, '.co.ve'))
		&& !is_integer(strpos($website_host, '.co.za'))
		&& !is_integer(strpos($website_host, '.co.zm'))
		&& !is_integer(strpos($website_host, '.co.zw'))

		&& !is_integer(strpos($website_host, '.t.co'))
		&& !is_integer(strpos($website_host, '.yn.lt'))
		&& !is_integer(strpos($website_host, '.wapka.mobi'))
		) 
	{
		$last_dot = strrpos($website_host, '.');
		$prev_dot = strrpos($website_host, '.', -(strlen($website_host) - $last_dot + 1) );
		$website_host = substr($website_host, $prev_dot + 1);
	}
	if ( is_integer(strpos($website_host, 'www.')) && strpos($website_host, 'www.') == 0 )
		$website_host = substr($website_host, 4);

	return $website_host;
}

function replace_non_ascii_chars($string)
{
	for ($i = 0; $i < strlen($string); $i++ ) {
		if ( intval( ord($string[$i]) ) < intval( ord(' ') ) || intval( ord($string[$i]) ) > intval( ord('~') ) ) {
			$string[$i] = chr(rand( intval(ord('a')), intval(ord('z')) ));
		}
	}
	return $string;
}

function parse_web_page($url, &$page_title, &$meta_descr, &$keywords, &$html_text)
{
	if ( !is_integer(stripos($url, 'http')) ) 
		$url = 'http://'.$url;
	$html_text = file_get_contents($url);
	$s = $html_text;
	if ( !$s )
		return false;
	$s = str_replace("\r", ' ', $s);
	$s = str_replace("\n", ' ', $s);
	if ( preg_match("/(<title>(.*?)<\/title>)/i", $s, $tit_arr ) ) {  
		foreach ($tit_arr as $key => $page_title) {
			$page_title = preg_replace("/<title>/i", ' ', $page_title);
			$page_title = preg_replace("/<\/title>/i", ' ', $page_title);
			$page_title = preg_replace('/\s\s+/', ' ', $page_title);
			$page_title = trim($page_title);
			if ( !empty($page_title) )
				break;
		}
	}
	
	if ( preg_match("/(<meta\s*([^>]*)name=\"keywords\"([^>]*)>)/i", $s, $descr ) ) {  
		$found = false;
		foreach ($descr as $key => $keywords2) {
			$keywords2 = preg_replace("/content\s*=/i", 'content=', $keywords2);
			$keywords2 = str_replace("'", '"', $keywords2);
			if ( preg_match("/(content=\"(.*?)\")/i", $keywords2, $desc_arr1 ) ) {  
				foreach ($desc_arr1 as $key => $keywords1) {
					$keywords1 = trim($keywords1);
					if ( !empty($keywords1) && !is_integer(stripos($keywords1, 'content=')) ) {
						$keywords = $keywords1;
						$keywords = str_replace('"', '', $keywords);
						$keywords = preg_replace('/\s\s+/', ' ', $keywords);
						if ( !empty($keywords) ) {
							$found = true;
							break;
						}
					}
				}
			}
			if ( $found )
				break;
		}
	}
	
	if ( empty($keywords) ) {
		$s = strtolower($s);
		while( substr_count( $s, '<script' ) && substr_count( $s, '</script>' ) && strpos( $s, '</script>', strpos( $s, '<script' ) ) > strpos( $s, '<script' ) ) 
			delete_text_between_tags($s, '<script', '</script');
		$s = html_entity_decode(strip_tags($s));

		$occurrence_array = get_occurrence_array($s);
		for ($i = 0; $i < count($occurrence_array); $i++) {
			$keywords = $keywords.$occurrence_array[$i][0].',';
			if ($i > 9)
				break;
		}
	}
	if ( preg_match("/(<meta\s*([^>]*)name=\"description\"([^>]*)>)/i", $s, $descr ) ) {  
		$found = false;
		foreach ($descr as $key => $meta_descr2) {
			$meta_descr2 = preg_replace("/content\s*=/i", 'content=', $meta_descr2);
			$meta_descr2 = str_replace("'", '"', $meta_descr2);
			if ( preg_match("/(content=\"(.*?)\")/i", $meta_descr2, $desc_arr1 ) ) {  
				foreach ($desc_arr1 as $key => $meta_descr1) {
					$meta_descr1 = trim($meta_descr1);
					if ( !empty($meta_descr1) && !is_integer(stripos($meta_descr1, 'content=')) ) {
						$meta_descr = $meta_descr1;
						$meta_descr = str_replace('"', '', $meta_descr);
						$meta_descr = preg_replace('/\s\s+/', ' ', $meta_descr);
						if ( !empty($meta_descr) ) {
							$found = true;
							break;
						}
					}
				}
			}
			if ( $found )
				break;
		}
	}
	if ( empty($meta_descr) ) {
		if ( preg_match("/(<h1\s*(.*?)\s*<\/h1>)/i", $s, $descr ) ) {  
			foreach ($descr as $key => $meta_descr) {
				$meta_descr = preg_replace("/<h1/i", 'aa11', $meta_descr);
				$meta_descr = preg_replace("/h1>/i", 'hhbb', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(aa11(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)hhbb)/i", ' ', $meta_descr);
				$meta_descr = preg_replace('/\s\s+/', ' ', $meta_descr);
				$meta_descr = trim($meta_descr);
				if ( !empty($meta_descr) ) {
					break;
				}
			}
		}
	}
	if ( empty($meta_descr) ) {
		if ( preg_match("/(<h2\s*(.*?)\s*<\/h2>)/i", $s, $descr ) ) {  
			foreach ($descr as $key => $meta_descr) {
				$meta_descr = preg_replace("/<h2/i", 'aa11', $meta_descr);
				$meta_descr = preg_replace("/h2>/i", 'hhbb', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(aa11(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)hhbb)/i", ' ', $meta_descr);
				$meta_descr = preg_replace('/\s\s+/', ' ', $meta_descr);
				$meta_descr = trim($meta_descr);
				if ( !empty($meta_descr) ) {
					break;
				}
			}
		}
	}
	if ( empty($meta_descr) ) {
		if ( preg_match("/(<p\s*(.*?)\s*<\/p>)/i", $s, $descr ) ) {  
			foreach ($descr as $key => $meta_descr) {
				$meta_descr = preg_replace("/<p/i", 'aa11', $meta_descr);
				$meta_descr = preg_replace("/p>/i", 'hhbb', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(aa11(.*?)>)/i", ' ', $meta_descr);
				$meta_descr = preg_replace("/(<(.*?)hhbb)/i", ' ', $meta_descr);
				$meta_descr = preg_replace('/\s\s+/', ' ', $meta_descr);
				$meta_descr = trim($meta_descr);
				if ( !empty($meta_descr) ) {
					break;
				}
			}
		}
	}
	return true;
}

function replaceCustomConstantInText($code, $value, $text)
{
	return str_ireplace( '{$'.$code.'}', $value, $text );
}

function toStandardCurrencyFormat($value, $commissionType = '') 
{
	if ($commissionType == '%')
		return number_format($value, 0).'%';
	else
		return '$'.number_format($value, 2);
}

function do_post_request($url, $data = '', $optional_headers = null)
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
		if ( defined('DEBUG_MODE') ) echo "Problem reading data from $url, $php_errormsg<br>";
		return false;
	}
	return $response;
}

function currency_format($amount, $positive_color = '', $negative_color = '', $revenue_color = '', $show_small_cents = false, $show_sign = false, $dollar_sign = '', $digits = '', $add_notranslate = false)
{
	if ( $dollar_sign == '~' ) {
		$dollar_sign = '';
	}
	else
	if (isset($dollar_sign) && empty($dollar_sign)) {
		if ( defined('DOLLAR_SIGN') )
			$dollar_sign = DOLLAR_SIGN;
		else
			$dollar_sign = '$';
	}
	if ( defined('DOLLAR_SIGN_POSITION') )
		$dollar_sign_position = DOLLAR_SIGN_POSITION;
	else
		$dollar_sign_position = 'left';

	if ( is_string($digits) && strlen($digits) == 0 ) {
		if ( abs($amount) > 0.2 )
			$digits = 2;
		else
		if ( abs($amount) > 0.02 )
			$digits = 3;
		else
		if ( abs($amount) > 0.002 )
			$digits = 4;
		else
		if ( abs($amount) > 0.0002 )
			$digits = 5;
		else
		if ( abs($amount) > 0.00002 )
			$digits = 6;
		else
			$digits = 2;
	}
	
	$s = 
		(
			$amount >= 0
				?
					(
						!empty($positive_color)
							?'<span style=" '.$positive_color.';">'
							:(!empty($revenue_color) && $amount > 0
								?'<span style=" '.$revenue_color.';">'
								:''
							)
					).''.
					(
						$show_sign
						?($amount > 0?'+':'')
						:''
					).
					(
						$dollar_sign_position == 'left'
						?$dollar_sign
						:''
					).
					(
						$amount < 0.2 && $amount > 0 && $show_small_cents
							?( $amount < 0.02?number_format($amount, 4):number_format($amount, 3) )
							:($show_small_cents?number_format(floor($amount * 100)/100, 2).'<span style="font-size:8px;">'.substr(number_format($amount*10000, 0), -2).'</span>':($amount < 1000000?number_format($amount, $digits): ($amount < 1000000000?round($amount / 1000000, 1).'M':round($amount / 1000000000, 1).'B')))
					).
					(
						$dollar_sign_position == 'right'
						?$dollar_sign
						:''
					)
				:
					(
						!empty($negative_color)
							?'<span style=" '.$negative_color.';">'
							:''
					).'-'.
					(
						$dollar_sign_position == 'left'
						?$dollar_sign
						:''
					).
					(
						abs($amount) < 0.2 && abs($amount) > 0&& $show_small_cents
							?( $amount < 0.02?number_format(abs($amount), 4):number_format(abs($amount), 3) )
							:number_format(abs($amount), $digits)
					).
					(
						$dollar_sign_position == 'right'
						?$dollar_sign
						:''
					)
		);
		if ( is_integer(strpos($s, '<span')) ) 
			$s = $s.'</span>';
		if ($add_notranslate)
			$s = '<span class="notranslate"> '.$s.' </span>';
		return $s;
}

function currency_format_number($amount)
{
	return 
		(
			$amount >= 0
				?
					(
						$amount < 0.01 && $amount > 0
							?($amount < 0.001?number_format($amount, 4):number_format($amount, 3))
							:number_format($amount, 2)
					)
				:
					(
						$amount > 0.01
							?number_format(abs($amount), 3)
							:number_format(abs($amount), 2)
					)
		);
}

function round_to_nearest_digits($amount)
{
	if ( abs($amount) > 0.2 )
		$digits = 2;
	else
	if ( abs($amount) > 0.02 )
		$digits = 3;
	else
	if ( abs($amount) > 0.002 )
		$digits = 4;
	else
	if ( abs($amount) > 0.0002 )
		$digits = 5;
	else
	if ( abs($amount) > 0.00002 )
		$digits = 6;
	else
		$digits = 2;
	return round($amount, $digits);
}

function validate_URL($url, &$URL_is_valid, &$have_URL, &$have_text, $must_have_URL = '', $must_have_text = '', &$must_have_URL_anchor )
{
	$have_URL = false;
	$have_text = false;
	$URL_is_valid = false;
	if ( empty($url) )
		return false;
	if ( !is_integer(strpos($url, '://')) )
		$url = 'http://'.$url;
	
	$read_content = strtolower(file_get_contents($url));
	if ( empty($read_content) )
		return false;
	$URL_is_valid = true;
	$site_content = $read_content;

	$must_have_text = trim($must_have_text);
	$have_text = stripos($site_content, trim($must_have_text));
	
	//remove comments
	while( substr_count( $site_content, '<!--' ) && substr_count( $site_content, '-->' ) && strpos( $site_content, '-->', strpos( $site_content, '<!--' ) ) > strpos( $site_content, '<!--' ) ) {
		$site_content = substr( $site_content, 0, strpos( $site_content, '<!--' ) ) . substr( $site_content, strpos( $site_content, '-->', strpos( $site_content, '<!--' ) ) + 3 ); }
	
	while( substr_count($site_content, "\r") )
		$site_content = str_replace("\r", ' ', $site_content);
	while( substr_count($site_content, "\n") )
		$site_content = str_replace("\n", ' ', $site_content);
	while( substr_count($site_content, 'href =') )
		$site_content = str_replace('href =', 'href=', $site_content);
	while( substr_count($site_content, 'href  =') )
		$site_content = str_replace('href  =', 'href=', $site_content);
	while( substr_count($site_content, 'href= "') )
		$site_content = str_replace('href= "', 'href="', $site_content);
	while( substr_count($site_content, 'href=  "') )
		$site_content = str_replace('href=  "', 'href="', $site_content);
	$goodStr = $site_content;
	$goodStr = preg_replace( "/<a[^>]* href=(\"((?!\"|#|javascript:)[^\"#]*)(\"|#)|'((?!'|#|javascript:)[^'#]*)('|#)|((?!'|\"|>|#|javascript:)[^#\"'> ]*))[^>]*>/i", "[LINK:$2$4$6:LINK] ", $goodStr );
	$goodStr = preg_replace( "/<script[^>]* src=(\"([^\"]+)\"|'([^']+)'|([^\"'> ]+))[^>]*>/i", "[LINK:$2$3$4:LINK] ", $goodStr );
	
	$links = explode('[LINK:', $goodStr);
	foreach ($links as $value) {
		$url = substr($value, 0, strpos($value, ':LINK]') );
		if ( is_integer( stripos($url, $must_have_URL) ) ) {
			$i = stripos($read_content, $must_have_URL) + strlen($must_have_URL);
			$s = substr($read_content, $i);
			$i = stripos($s, '<') + 3;
			$s = substr($s, 0, $i);
			$s = str_replace(' ', '', $s);
			if ( !is_integer(stripos($s, '></a')) ) {
				$have_URL = true;
				$must_have_URL_anchor = trim(get_text_between_tags($value, ']', '</'));
			}
			break;
		}
	}
	return $read_content;
}

function get_payment_option_email($payment_method)
{
	global $payout_options;
	foreach ( $payout_options as $value ) {
		if ( $value['id'] == $payment_method )
			return $value['email'];
	}
}

function int_to_words($x)
{
	$nwords = array(    "zero", "one", "two", "three", "four", "five", "six", "seven",
 	                     "eight", "nine", "ten", "eleven", "twelve", "thirteen",
 	                     "fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
 	                     "nineteen", "twenty", 30 => "thirty", 40 => "forty",
 	                     50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty",
 	                     90 => "ninety" );
	if (!is_numeric($x))	{
		$w = '#';
	}
	else 
	if(fmod($x, 1) != 0) {
		$w = '#';
	}
	else{
		if($x < 0) {
			$w = 'minus ';
			$x = -$x;
		}
		else{
			$w = '';
		}
		if($x < 21)	{
			$w .= $nwords[$x];
		}
		else 
		if($x < 100){
			$w .= $nwords[10 * floor($x/10)];
			$r = fmod($x, 10);
			if($r > 0){
				$w .= '-'. $nwords[$r];
			}
		} 
		else 
		if($x < 1000) {
			$w .= $nwords[floor($x/100)] .' hundred';
			$r = fmod($x, 100);
			if($r > 0){
				$w .= ' and '. int_to_words($r);
			}
		} 
		else 
		if($x < 1000000) {
			$w .= int_to_words(floor($x/1000)) .' thousand';
			$r = fmod($x, 1000);
			if($r > 0) 	{
				$w .= ' ';
				if($r < 100){
					$w .= 'and ';
				}
				$w .= int_to_words($r);
			}
		} 
		else {
			$w .= int_to_words(floor($x/1000000)) .' million';
			$r = fmod($x, 1000000);
			if($r > 0)	{
				$w .= ' ';
				if($r < 100)	{
					$word .= 'and ';
				}
				$w .= int_to_words($r);
			}
		}
	}
	return $w;
}

if ( !function_exists('imageantialias') ) {
	function imageantialias($image, $enabled){
		return true;
    }
}

function draw_image_on_background($in_file_name, $out_file_name, $background_file_name, $in_file_extension, $width, $height, $rotate = 0, $in_file_data = '')
{
	$res = '';
	if ( empty($background_file_name) )
		$out_image = imagecreatetruecolor($width, $height);
	else {
		if ( !$out_image = imagecreatefromjpeg($background_file_name) )
			return 'Error: cannot open backgrounf file.';
	}

	if ( function_exists('imageantialias') )
		imageantialias($out_image, true);
	
	if (empty($in_file_name)) {
		if ( ! $photo_img = imagecreatefromstring($in_file_data) )
			return "Error: cannot create image. $in_file_data";
	}
	else
	if ( $in_file_extension == 'jpg' || $in_file_extension == 'jpeg' ) {
		if ( ! $photo_img = imagecreatefromjpeg($in_file_name) )
			return "Error: cannot open file $in_file_name. Unknown file format.";
	}
	else
	if ( $in_file_extension == 'png' ) {
		if ( ! $photo_img = imagecreatefrompng($in_file_name) )
			return "Error: cannot open file $in_file_name. Unknown file format.";
	}
	else
	if ( $in_file_extension == 'gif' ) {
		if ( ! $photo_img = imagecreatefromgif($in_file_name) )
			return "Error: cannot open file $in_file_name. Unknown file format.";
	}
	if ( $rotate ) {
		imagealphablending($photo_img, false);
		imagesavealpha($photo_img, true);

		$photo_img = imagerotate($photo_img, $rotate, 0xffffff);
	}

	$srcWtoH = imagesx($photo_img) / imagesy($photo_img);
	$dstWtoH = imagesx($out_image) / imagesy($out_image);
	if ( imagesx($photo_img) > imagesy($photo_img) ) {
		if ( $srcWtoH > $dstWtoH ) {
			$srcH = imagesy($photo_img);
			$srcW = $srcH * $dstWtoH;
			$srcY = 0;
			$srcX = round( ( imagesx($photo_img) - $srcW ) / 2 );
		}
		else {
			$srcW = imagesx($photo_img);
			$srcH = $srcW / $dstWtoH;
			$srcX = 0;
			$srcY = round( ( imagesy($photo_img) - $srcH ) / 2 );
		}
	}
	else {
		if ( $dstWtoH > $srcWtoH ) {
			$srcW = imagesx($photo_img);
			$srcH = $srcW / $dstWtoH;
			$srcX = 0;
			$srcY = round( ( imagesy($photo_img) - $srcH ) / 2 );
		}
		else {
			$srcH = imagesy($photo_img);
			$srcW = $srcH * $dstWtoH;
			$srcY = 0;
			$srcX = round( ( imagesx($photo_img) - $srcW ) / 2 );
		}
	}
	if ( $srcX < 0 )
		$srcX = 0;
	if ( $srcY < 0 )
		$srcY = 0; 
	if ( $srcW > imagesx($photo_img) )
		$srcW = imagesx($photo_img);
	if ( $srcH > imagesy($photo_img) )
		$srcH = imagesy($photo_img);
	imagecopyresampled(
		$out_image, $photo_img, 
		0, 0, 
		$srcX, $srcY, 
		imagesx($out_image) - 0, imagesy($out_image) - 0, 
		$srcW, $srcH
	);
	
	if (empty($out_file_name)) {
		$stream = fopen("php://memory", "w+");
		imagejpeg($out_image, $stream, 100);
		rewind($stream);
		$res = base64_encode(stream_get_contents($stream));
	}
	else
		imagejpeg($out_image, $out_file_name, 100);
	imagedestroy($out_image);
	imagedestroy($photo_img);
	return $res;
}

function draw_image_on_a_backround($photo_file, $out_file_name, $background_file_name, $in_file_extension, $width, $height)
{
	if ( empty($background_file_name) )
		$out_image = imagecreatetruecolor($width, $height);
	else {
		if ( !$out_image = imagecreatefromjpeg($background_file_name) )
			return 'Error: cannot open backgrounf file.';
	}	
	if ( function_exists('imageantialias') )
		imageantialias($out_image, true);
	
	if ( $in_file_extension == 'jpg' || $in_file_extension == 'jpeg' ) {
		if ( ! $photo_img = imagecreatefromjpeg($photo_file) )
			return 'Error: cannot open file. Unknown file format.';
	}
	else
	if ( $in_file_extension == 'png' ) {
		if ( ! $photo_img = imagecreatefrompng($photo_file) )
			return 'Error: cannot open file. Unknown file format.';
	}
	else
	if ( $in_file_extension == 'gif' ) {
		if ( ! $photo_img = imagecreatefromgif($photo_file) )
			return 'Error: cannot open file. Unknown file format.';
	}
	
	$srcWtoH = imagesx($out_image) / imagesy($out_image);
	$dstWtoH = imagesx($out_image) / imagesy($out_image);
	if ( imagesx($photo_img) > imagesy($photo_img) ) {
		$srcH = imagesy($photo_img);
		$srcW = $srcH * $srcWtoH;
		$srcY = 0;
		$srcX = round( ( imagesx($photo_img) - $srcW ) / 2 );
	}
	else {
		$srcW = imagesx($photo_img);
		$srcH = $srcW / $srcWtoH;
		$srcX = 0;
		$srcY = round( ( imagesy($photo_img) - $srcH ) / 2 );
	}
	
	if ( $srcX < 0 )
		$srcX = 0;
	if ( $srcY < 0 )
		$srcY = 0; 
	
	if ( $srcW > imagesx($photo_img) )
		$srcW = imagesx($photo_img);
	if ( $srcH > imagesy($photo_img) )
		$srcH = imagesy($photo_img);
	
	imagecopyresampled(
		$out_image, $photo_img, 
		0, 0, 
		$srcX, $srcY, 
		imagesx($out_image), imagesy($out_image), 
		$srcW, $srcH
	);
	imagejpeg($out_image, $out_file_name, 100);
	
	imagedestroy($out_image);
	imagedestroy($photo_img);
	return '';
}


function get_hash_code($constant, $hour_minus = 0)
{
	$hour = date("H") - $hour_minus;
	if ($hour < 0)
		$hour = 0;
	return sprintf("%u", crc32($constant.date("Y-m-d").($hour)));
}

function is_hash_code_valid($constant, $code)
{
	return $code == get_hash_code($constant, -1) || $code == get_hash_code($constant, 0) || $code == get_hash_code($constant, 1) || $code == get_hash_code($constant, 2);
}

function get_occurrence_array($text)
{
	function search_in_words_array($word, $array)
	{
		foreach ( $array as $word_arr ) {
			if ($word_arr[0] == $word)
				return true;
		}
		return false;
	}
	function compare_function($a, $b)
	{
		if ($a[1] == $b[1]) {
			return 0;
		}
		return ($a[1] > $b[1]) ? -1 : 1;
	}
	global $smallwordsarray;
	$text = strtolower($text);
	$n_words = preg_match_all('/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){4,}/', $text, $match_arr);
	$word_arr = $match_arr[0];
	$sorted_word_arr = array();
	foreach ( $word_arr as $word ) {
		if ( !search_in_words_array($word, $sorted_word_arr) ) {
			if ( !in_array($word, $smallwordsarray) ) 
				$sorted_word_arr[] = array($word, substr_count($text, $word));
		}
	}
	usort($sorted_word_arr, 'compare_function');
	return $sorted_word_arr;
}

function get_word_sequences($text, $additional_ignore_words = '', $min_sequence = 2, $max_sequence = 4, $min_frequency = 2, $min_chars = 3, $ascii_only = true)
{
	$comm = array_unique(preg_split("(\b\W+\b)", file_get_contents(DIR_COMMON_PHP.'common_words.txt'))); 
	//$source = preg_split("(\b\W+\b)", $text);
	if (is_array($text)) {
		$source = $text;
	}
	else {
		$text = strtolower($text);
		$source = preg_split("/(.+?)([\s,.!?;:)([\]]+)/", $text); 
	}
	//var_dump($source);
	$ignore = explode(',', $additional_ignore_words);
	//$num_ignore = count($ignore); 

	$minseq = $min_sequence;
	$maxseq = $max_sequence;
	$minfreq = $min_frequency;
	$minchars = $min_chars; 

	foreach ($source as $w) { 
		$w = trim($w);
		if (strlen($w) >= $minchars) 
			if (!preg_match("/\A\d+\Z/", $w)) 
				if (!preg_match("/\A(\w)\1+\Z/", $w)) 
					if (!in_array($w, $comm)) 
						if (!in_array($w, $ignore)) {
							$non_ascii = false;
							if ( $ascii_only ) {
								for ($i = 0; $i < strlen($w); $i ++)
									if ( ord($w[$i]) > ord('z') ) {
										$non_ascii = true;
										break;
									}
							}
							if ( !$non_ascii )
								$words[] = $w;
						}
	} 
	$num_words = count($words); 
	$str = strtolower(implode(' ', $words)); 
	$seqs = array(); 
	for ($i = 0; $i < $num_words; $i ++) { 
		for ($j = $maxseq; $j >= $minseq; $j --) {
			$try = $words[$i]; 
			if ($j > 1) { 
				for ($k = 1; $k < $j; $k ++) { // fetch words to try 
					$s = $words[$i + $k];
					if (!empty($s))
						$try .= ' '.$s; 
					else {
						$try = '';
						break;
					}
				}
			}
			$try = trim($try);
			if ( !empty($try) ) {
				$matches = substr_count($str, $try); 
				if ($matches >= $minfreq) { 
					$seqs[$try] = $matches;
					break; 
				}
			}
			else
				continue;
		} 
	}  
	arsort($seqs); 
	return $seqs;
}

function get_number_of_plagiarisms($text)
{
	$number_of_similar_found = 0;
	return $number_of_similar_found;
}

function get_file_variable($variable_name) 
{
	if ( file_exists(DIR_WS_TEMP.'$$$'.$variable_name.'.txt') )
		return file_get_contents(DIR_WS_TEMP.'$$$'.$variable_name.'.txt');
	return false;
}

function update_file_variable($variable_name, $value) 
{
	file_put_contents(DIR_WS_TEMP.'$$$'.$variable_name.'.txt', $value);
}

function is_file_variable_expired($variable_name, $timeout_in_mins = 1, $timeout_in_secs = '') 
{
	if ( empty($timeout_in_secs) ) {
		$timeout_in_mins = round((int)$timeout_in_mins);
		if ( (int)$timeout_in_mins < 1 )
			$timeout_in_mins = 1;
		if ( (int)$timeout_in_mins > 60*24*7 )
			$timeout_in_mins = 60*24*7;
		$timeout_in_secs = $timeout_in_mins * 60;
	}
	$file_name = DIR_WS_TEMP.'$$$'.$variable_name.'.txt';
	if ( file_exists($file_name) )
		return time() - filemtime($file_name) > $timeout_in_secs || filemtime($file_name) > time();
	else
		return true;
}

function delete_file_variable($variable_name) 
{
	if ( file_exists(DIR_WS_TEMP.'$$$'.$variable_name.'.txt') )
		unlink(DIR_WS_TEMP.'$$$'.$variable_name.'.txt');
}

function encrypt_decrypt($action, $string, $key, $cipher = '')
{
	$output = false;
	if (empty($cipher))
		$cipher = "aes-128-cbc";
	$list_of_ciphers = openssl_get_cipher_methods();
	if ( !in_array(strtolower($cipher), $list_of_ciphers) && !in_array(strtoupper($cipher), $list_of_ciphers) )
		return false;
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = openssl_random_pseudo_bytes($ivlen);
	$options = OPENSSL_RAW_DATA;
	$as_binary = true;

	if( $action == 'encrypt' ) {
		$ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary);
		$output = base64_encode($iv.$hmac.$ciphertext_raw);
	}
	else {
		$c = base64_decode($string);
		$iv = substr($c, 0, $ivlen);
		$sha2len = 32;
		$hmac = substr($c, $ivlen, $sha2len);
		$ciphertext_raw = substr($c, $ivlen + $sha2len);
		$output = openssl_decrypt($ciphertext_raw, $cipher, $key, $options, $iv);
	}
	return $output;
}

function send_SMS_alert($message)
{
	return false;
}

function reboot_server($provider, $login_name, $password, $serverid, $userid)
{
	
}

function reboot_server_by_ip($ip)
{
	
}

function get_currency_rate($currency, $currency2 = DOLLAR_NAME)
{
	$currency = strtolower($currency);
	$currency2 = strtolower($currency2);
	if ( $currency == $currency2 )
		return 1;
	$rate = get_file_variable($currency.'_'.$currency2.'_last_price'); 
	if (empty($rate)) {
		$rate = get_file_variable($currency2.'_'.$currency.'_last_price'); 
		if (!empty($rate))
			$rate = 1 / $rate;
		else {
			if ( $currency == 'btc' && $currency2 == strtolower(DOLLAR_NAME) ) {
				$rate = get_file_variable('bitcoin_exchange_rate_USD'); 
				if (empty($rate))
					$rate = get_file_variable('bitcoin_exchange_rate_usd'); 
			}
		}
	}
	return (float)$rate;
}

function convert_amount_to_default_currency($amount, $currency)
{
	return $amount * (strtolower($currency) == strtolower(DOLLAR_NAME)?1:get_currency_rate($currency));
}

function convert_default_currency_to_amount_to($amount_in_dollars, $currency)
{
	return $amount_in_dollars / (strtolower($currency) == strtolower(DOLLAR_NAME)?1:get_currency_rate($currency));
}

if ( !function_exists('check_image_path') ) {
	function check_image_path($image_path)
	{
		if (!empty($image_path) && $image_path != 'none' && !is_integer(strpos($image_path, 'url(/')) )
			return str_replace ('url(', 'url(/', $image_path);
		else
			return $image_path;
	}
}
if ( !function_exists('adjustBrightness') ) {
	function adjustBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}
		return $return;
	}
}

function HTMLToRGB($htmlCode)
{
	if($htmlCode[0] == '#')
		$htmlCode = substr($htmlCode, 1);
	if (strlen($htmlCode) == 3) {
		$htmlCode = $htmlCode[0] . $htmlCode[0] . $htmlCode[1] . $htmlCode[1] . $htmlCode[2] . $htmlCode[2];
	}
	$r = hexdec($htmlCode[0] . $htmlCode[1]);
	$g = hexdec($htmlCode[2] . $htmlCode[3]);
	$b = hexdec($htmlCode[4] . $htmlCode[5]);
	return $b + ($g << 0x8) + ($r << 0x10);
}

function RGBToHSL($RGB) 
{
	$r = 0xFF & ($RGB >> 0x10);
	$g = 0xFF & ($RGB >> 0x8);
	$b = 0xFF & $RGB;

	$r = ((float)$r) / 255.0;
	$g = ((float)$g) / 255.0;
	$b = ((float)$b) / 255.0;

	$maxC = max($r, $g, $b);
	$minC = min($r, $g, $b);

	$l = ($maxC + $minC) / 2.0;

	if($maxC == $minC) {
		$s = 0;
		$h = 0;
	}
	else {
		if($l < .5) {
			$s = ($maxC - $minC) / ($maxC + $minC);
		}
		else {
			$s = ($maxC - $minC) / (2.0 - $maxC - $minC);
		}
		if($r == $maxC)
			$h = ($g - $b) / ($maxC - $minC);
		if($g == $maxC)
			$h = 2.0 + ($b - $r) / ($maxC - $minC);
		if($b == $maxC)
			$h = 4.0 + ($r - $g) / ($maxC - $minC);
		$h = $h / 6.0; 
	}
	$h = (int)round(255.0 * $h);
	$s = (int)round(255.0 * $s);
	$l = (int)round(255.0 * $l);
	return (object) Array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
}

function is_color_light($colour)
{
	$rgb = HTMLToRGB($colour);
	$hsl = RGBToHSL($rgb);
	return $hsl->lightness > 128;
}

function show_intro($intro_image, $intro_text, $color = 'alert-success')
{
	$class = 'alert '.$color;
	if ( defined('TOP_INFO_PANE_STYLE_NUMBER1') && TOP_INFO_PANE_STYLE_NUMBER1 == '1' )
		$class = 'box_type1';
	else
	if ( defined('TOP_INFO_PANE_STYLE_NUMBER2') && TOP_INFO_PANE_STYLE_NUMBER2 == '1' )
		$class = 'box_type2';
	else
	if ( defined('TOP_INFO_PANE_STYLE_NUMBER3') && TOP_INFO_PANE_STYLE_NUMBER3 == '1' )
		$class = 'box_type3';
	return '
	<div class="'.$class.'" style="margin-bottom:10px;" id="intro_top_alert">
		<table class="table" style="margin:0;">
			<tr>
				'.(!empty($intro_image)?'<td style="border:none; position:relative;" class="visible_on_big_screen"><img src="'.$intro_image.'" class="first_page_image" style="width:64px; height:64px; margin:6px; '.(defined('IMAGE_IN_INFO_PANE_GRAYSCALE') && IMAGE_IN_INFO_PANE_GRAYSCALE <> '0' ?'filter:grayscale('.IMAGE_IN_INFO_PANE_GRAYSCALE.'%);':'').'">
				'.(defined('IMAGE_IN_INFO_PANE_GRAYSCALE') && IMAGE_IN_INFO_PANE_GRAYSCALE <> '0' && defined('IMAGE_IN_INFO_PANE_BLEND_COLOR') && defined('IMAGE_IN_INFO_PANE_BLEND_OPACITY') ? '<div class="first_page_image visible_on_big_screen" style="position:absolute; width:64px; height:64px; background-color:#'.IMAGE_IN_INFO_PANE_BLEND_COLOR.'; left:14px; top:4px; opacity:'.(IMAGE_IN_INFO_PANE_BLEND_OPACITY / 100).';"></div></td>':''):'').'
				<td style="width:100%; border:none;">'.$intro_text.'</td>
			</tr>
		</table>
	</div>
	';
}

function show_help($text, $help_icon = 'glyphicon-question-sign', $text_class = 'text-success', $table_class = '', $autotranslate = false)
{
	return '
	<table class="'.$table_class.'" style="margin-top:0px;">
		<tr>
			<td style="vertical-align:top;">
				<h1 style="margin:0 10px 0 0;" class="visible_on_big_screen"><span class="glyphicon '.$help_icon.' '.$text_class.'" aria-hidden="true"></span></h1>
			</td>
			<td style="vertical-align: middle;" class="'.$text_class.'">
				<span class="description '.($autotranslate ? 'string_to_translate' : '').'">'.$text.'</span>
			</td>
		</tr>
	</table>
	';
}

function show_incremented_value($name, $id, $value = '1', $width = 140, $onBlur = '', $placeholder = '', $prefix = '', $message = '', $step = 'any', $add_code_on_inc_and_dec = '', $onKeyup = '', $onChange = '', $add_class = '')
{
	if ( !empty($width) && $width < 120 )
		$width = 120;
	return '
	<div style="display:inline-block;">
		<div class="input-group input-group-sm" style="'.(!empty($width)?'width:'.$width.'px;':'width:100%;').'">
			<span class="input-group-btn visible_on_big_screen">
				<button class="btn btn-info" onclick="decrement_input_value(this.parentNode.parentNode.getElementsByTagName(\'input\')[0], '.($step == 'any'?'1':$step).'); '.$add_code_on_inc_and_dec.' return false;" title="decrease value"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
			</span>
			'.(!empty($message) || !empty($prefix)?'<div class="input-group">':'').'
				'.(!empty($prefix)?'<span class="input-group-addon">'.$prefix.'</span>':'').'
				<input type="number" step="'.$step.'" autocomplete="off" class="form-control input-sm '.$add_class.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" onBlur="'.$onBlur.'" onKeyup="'.$onKeyup.'" onChange="'.$onChange.'" placeholder="'.$placeholder.'">
				'.(!empty($message)?show_more_info_popover($message):'').'
			'.(!empty($message) || !empty($prefix)?'</div>':'').'
			<span class="input-group-btn visible_on_big_screen">
				<button class="btn btn-info" onclick="increment_input_value(this.parentNode.parentNode.getElementsByTagName(\'input\')[0], '.($step == 'any'?'1':$step).'); '.$add_code_on_inc_and_dec.' return false;" title="increase value"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
			</span>
		</div>
	</div>
	';
}

function show_more_info_popover($message)
{
	return '
	<span class="input-group-addon" data-container="body" data-toggle="popover" data-placement="bottom" data-content="'.$message.'" style="cursor:pointer;"><span class="glyphicon glyphicon-question-sign" aria-hidden="true" ></span></span>
	';
}

function generate_popup_code($popup_name, $popup_body, $yes_js, $title = '', $button_yes_caption = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>', $button_cancel_class = 'btn-link', $button_cancel_caption = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>', $cancel_js = '', $focused_id = '', $modal_dialog_style = '', $on_show_js = '', $return_string = false)
{
	$res = '
	<!-- '.$popup_name.' box -->  
	<div class="modal fade" id="'.$popup_name.'_box" role="dialog">
		<div class="modal-dialog" style="'.$modal_dialog_style.'">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" id="btn_close_box">&times;</button>
					<h2 class="modal-title"><span id="'.$popup_name.'_title">'.$title.'</span></h2>
				</div>
				<div class="modal-body">
					'.$popup_body.'
				</div>
				<div class="modal-footer" style="text-align:center;">
					'.(!empty($button_yes_caption)?'<button type="submit" class="btn btn-success" id="'.$popup_name.'_btn_yes" onClick="return '.$popup_name.'_yes();" style="margin-bottom:0px;">'.$button_yes_caption.'</button>':'').'
					'.(!empty($button_cancel_caption)?'<button type="button" class="btn '.$button_cancel_class.'" data-dismiss="modal" id="'.$popup_name.'_btn_no" '.(!empty($cancel_js)?'onclick="'.$cancel_js.'"':'').'>'.$button_cancel_caption.'</button>':'').'
				</div>
			</div>
		</div>
	</div>

	<script language="JavaScript">

	function '.$popup_name.'_yes()
	{
		var res = '.(empty($yes_js)?'1':$yes_js).';
		if ( res || typeof res == "undefined" )
			$("#'.$popup_name.'_box").modal("hide"); 
		return false;
	}
	
	function hide_'.$popup_name.'()
	{
		$("#'.$popup_name.'_box").modal("hide"); 
		return false;
	}

	function show_'.$popup_name.'(box_title, btn_yes_caption, btn_no_caption)
	{
		try	{
			if ( box_title != null )
				$("#'.$popup_name.'_title").html(box_title);
			if ( btn_yes_caption != null )
				$("#'.$popup_name.'_btn_yes").html(btn_yes_caption);
			if ( btn_no_caption != null )
				$("#'.$popup_name.'_btn_no").html(btn_no_caption);
			
			'.(!empty($on_show_js)?$on_show_js.';':'').'

			'.(!empty($focused_id)?'$("#'.$popup_name.'_box").on("shown.bs.modal", function () {setTimeout(function() { $("#'.$focused_id.'").focus(); }, 100);});':'').'
	
			$("#'.$popup_name.'_box").modal("show"); 
		}
		catch(error){}	
		return false;
	}
	</script>
	';
	if ($return_string)
		return $res;
	else
		echo $res;
}

function show_data_period_toolbar($toolbar)
{
	$res = '<div class="btn-group btn-group-justified" role="group" aria-label="...">';
	$cat_number = 0;
	foreach ($toolbar as $category) {
		$buttons_list = '';
		foreach ($category['buttons'] as $button) {
			$selected_style = '';
			if ( !empty($category['selected']) && $category['selected'] == $button['value'] ) {
				$category['name'] = $button['name'];
				$selected_style = 'background-color:#'.COLOR1BASE.';';
			}
			$buttons_list = $buttons_list.'<li style="'.$selected_style.'"><a href="#" onclick="'.$button['onclick'].'">'.$button['name'].'</a></li>';
		}
		$res = $res.'
		<div class="btn-group" role="group">
			<a href="#" id="data_period_toolbar_cat_'.$cat_number/*strtolower(convert_to_alphanum($category['name']))*/.'" class="btn '.(!empty($category['btn-class'])?$category['btn-class']:'btn-default').' btn-xs dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="'.$category['style'].'">'.$category['name'].'&nbsp;&nbsp;<span class="caret"></span></a>
			<ul class="dropdown-menu" role="menu">
		'.$buttons_list.'
			</ul>
		</div>';
		$cat_number++;
	}
	$res = $res.'</div>';
	return $res;
}

function show_upload_button($name, $caption, $file_token, $on_file_received, $file_token_prefix = '', $btn_class = 'btn btn-info', $valid_file_extensions = '"gif", "png", "jpg", "jpeg"', $style = 'display:inline; width:auto; height:auto; position:relative; left:0px; top:0px; margin:0px; vertical-align:top;', $on_upload = '', $page_which_process_upload = '', $file_input_name = 'an_image_upload')
{
	if (empty($on_upload))
		$on_upload = 'return upload_'.$name.'(this.form);';
	if (empty($page_which_process_upload))
		$page_which_process_upload = $_SERVER['SCRIPT_NAME'];
	return '
	<iframe name="'.$name.'_upload_frame" id="'.$name.'_upload_frame" src="about:blank" style="width:0px; height:0px; border:none; display:block;"></iframe>
	<input type="hidden" name="this_is_upload_'.$name.'" value="">
	<input name="file_token" id="file_token" type="hidden" value="'.$file_token.'">
	<div style="'.$style.'">
		<button class="'.$btn_class.'" name="'.$name.'" id="'.$name.'" style="cursor:pointer;" onclick="return false;">'.$caption.'</button>
		<input type="file" name="'.$file_input_name.'" size="1" style="cursor:pointer; font-size:18px; width:100%; height:40px; position:absolute; left:0px; top:0px; opacity:0; filter:alpha(opacity = 0);" onchange="'.$on_upload.'" >
		<img src="/images/wait64x64.gif" width="20" height="20" border="0" id="'.$name.'_uploading_sign" alt="" style="position:relative; left:0px; top:6px; display:none; ">
	</div>
	<script language="JavaScript"> 
	var uploadImage_'.$name.'ValidFileExtensions = new Array('.$valid_file_extensions.');
	var uploadImage_'.$name.'ValidFileExtensionMessage = "";
	var upload_check_'.$name.'_delay = 500;
	
	for( var i = 0; i < uploadImage_'.$name.'ValidFileExtensions.length; i++){
		if ( i != uploadImage_'.$name.'ValidFileExtensions.length - 1 ){
			uploadImage_'.$name.'ValidFileExtensionMessage += uploadImage_'.$name.'ValidFileExtensions[i] + ", ";
		}
		else{
			uploadImage_'.$name.'ValidFileExtensionMessage += "or " + uploadImage_'.$name.'ValidFileExtensions[i];
		}
	}
	
	function upload_'.$name.'(form)
	{
		var fileExtension = form.'.$file_input_name.'.value;
		fileExtension = fileExtension.substring((fileExtension.lastIndexOf(".") + 1), fileExtension.length).toLowerCase();
		
		var imageUploadValidFile = uploadImage_'.$name.'ValidFileExtensions.length == 0;
		for(var i = 0; i < uploadImage_'.$name.'ValidFileExtensions.length; i++){
			if( fileExtension == uploadImage_'.$name.'ValidFileExtensions[i] ){
				imageUploadValidFile = true;
			}
		}
		if ( !imageUploadValidFile ){
			alert("Invalid file type. Please select a " + uploadImage_'.$name.'ValidFileExtensionMessage + " file.");
			return false;
		}
		
		form.action = "'.$page_which_process_upload.'";
		form.target = "'.$name.'_upload_frame";
		form.this_is_upload_'.$name.'.value = "1";
		form.file_token.value = "'.$file_token_prefix.'" + Math.floor(Math.random() * Math.random() * 500 * 500 + (Math.random() * 500));
		form.submit();
		form.action = "";
		form.target = "";
		form.this_is_upload_'.$name.'.value = "";
		form.file_token.value = 0;
		$("#'.$name.'").hide();
		$("#'.$name.'_uploading_sign").show();
		
		upload_check_'.$name.'();
		
		return true;
	}
	
	function upload_check_'.$name.'()
	{
		upl_frame = document.getElementById("'.$name.'_upload_frame");
		if ( upl_frame && upl_frame.contentWindow && upl_frame.contentWindow.document.title ) {
			s = upl_frame.contentWindow.document.title;
			if ( s.length > 0 ) {
				if ( s.indexOf("FILE:") >= 0 ) {
					image_file = s.substr(5);
					'.$on_file_received.'
				}
				else {
					alert("Upload error: " + s);
				}
				upl_frame.contentWindow.location = "about:blank";
				$("#'.$name.'").show();
				$("#'.$name.'_uploading_sign").hide();
				return true;
			}
		}
		setTimeout("upload_check_'.$name.'()", upload_check_'.$name.'_delay);
	}
	</script>
	';
}

function convert_image_to_data_url($relative_path, $erase_file = false, $mime = 'image/jpeg')
{
	if ( !is_integer(strpos($relative_path, 'data:')) ) {
		while ( $relative_path[0] == '.' )
			$relative_path = substr($relative_path, 1);
		if ( $relative_path[0] == '/' )
			$relative_path = substr($relative_path, 1);
		$image_img = file_get_contents(DIR_ROOT.WEBSITE_FRONT_DIR.'/'.$relative_path);
		if ( $erase_file )
			unlink(DIR_ROOT.WEBSITE_FRONT_DIR.'/'.$relative_path);
		return 'data:'.$mime.';base64,'.base64_encode($image_img);
	}
	else
		return $relative_path;
}

function convert_data_url_to_image($data, $local_path, $relative_path, $file_name, $rewrite_file = false)
{
	if ( is_integer(strpos($data, 'data:image/')) ) {
		$extension = get_text_between_tags($data, 'data:image/', ';');
		$file_path = $local_path.$file_name.'.'.$extension;
		if ( !file_exists($file_path) || filesize($file_path) < 1 || $rewrite_file )
			file_put_contents($file_path, base64_decode(get_text_between_tags($data, ',', '')));
		return $relative_path.$file_name.'.'.$extension;
	}
	else {
		return $data;
	}
}

function generate_json_answer($success, $message, $values = '', $error_code = '')
{
	$res = '';
	return '{"success":'.$success.', "message":"'.$message.'", "error_code":"'.$error_code.'", "values":'.json_encode($values).'}';
}

function login_hash_suffix() 
{
	//return md5(date('Y-m-d').DB_SERVER_PASSWORD);
}

function byte_string_to_int($byteString) 
{
	$val = trim($byteString);
    $last = strtolower($val[strlen($val)-1]);
    switch($last) {
        case 't':
            $val *= 1024;
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function generate_answer($success = 1, $message = '', $values = '', $error_code = '')
{
	return '{"success":'.$success.', "message":"'.$message.'", "error_code":"'.$error_code.'", "values":'.json_encode($values).'}';
}

function get_api_domain()
{
	if (defined('USE_API_SERVER_NOT_COLD_MIRROR') && USE_API_SERVER_NOT_COLD_MIRROR == 'false') {
		// Connect via cold mirror
		$url = 'cold_mirror.'.SITE_SHORTDOMAIN;
		$api_server_domain = trim(get_file_variable('api_server_domain'));
		if (!empty($api_server_domain) && !is_file_variable_expired('api_server_domain', 15)) // During 15 minutes continue connect via API server
			$url = $api_server_domain;
		else {
			update_file_variable('api_server_domain', '');
		}
	}
	else {
		$url = 'api1.'.SITE_SHORTDOMAIN;
		$api_server_domain = trim(get_file_variable('api_server_domain'));
		if (!empty($api_server_domain) && !is_file_variable_expired('api_server_domain', 60 * 1))
			$url = $api_server_domain;
		else {
			update_file_variable('api_server_domain', '');
		}
	}
	return $url;
}

function make_api_request($request = '', $get_params = '', $post_params = '', $token = '', $user = null, $local_request = false, $max_attempts = 10, $api_domain = '')
{
	if ( isset($_GET['debug']) ) {
		echo "
		<div class='notranslate'>
		<br>Request $request started: ".(time() - SCRIPT_STARTED_SEC)."*****<br>
		</div>";
	}
	
	if (defined('USE_API_SERVER_NOT_COLD_MIRROR') && USE_API_SERVER_NOT_COLD_MIRROR == 'false')
		$max_attempts = $max_attempts * 0.3;

	if ( empty($request) ) {
		if ( !is_array($get_params) ) {
			parse_str($get_params, $tmp_params);
			$get_params = $tmp_params;
		}
		foreach ( $get_params as $key => $value) {
			// make first value as request
			$request = $key; 
			break;
		}
		// remove first value from params
		$i = 0;
		$tmp_get_params = array();
		foreach ( $get_params as $key => $value) {
			if ( $i > 0 )
				$tmp_get_params[$key] = $value; 
			$i++;
		}
		$get_params = $tmp_get_params;
	}
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
	global $user_account;
	if ( !isset($user) ) {
		if ( isset($user_account) ) {
			$user = $user_account;
		}
		else
		if ( !empty($_COOKIE['userid']) && !empty($_COOKIE['password']) ) {
			$user = new User();
			$user->userid = $_COOKIE['userid'];
			$user->psw_hash = $_COOKIE['password'];
		}
		else
		if ( !isset($user_account) && !empty($_POST['userid']) && !empty($_POST['psw_hash']) ) {
			$user = new User();
			$user->userid = $_POST['userid'];
			$user->psw_hash = $_POST['psw_hash'];
		}
	}
	if ( isset($user) && $user->is_loggedin() ) {
		$post_params = 'token='.$user->psw_hash.'&'.$post_params;
		//echo "request: $request, post_params: $post_params"; exit;
	}
	else {
		if ( empty($token) )
			$token = MD5( get_api_token_seed().(round(time() / 60)) );
		$post_params = 'token='.$token.'&'.$post_params;
	}
	
	if ( isset($user) )
		$post_params = 'userid='.$user->userid.'&user_email='.$user->email.'&'.$post_params;

	if ( isset($user_account) && $user_account->is_loggedin() && $user_account->is_manager() )
		$post_params = 'manager_userid='.$user_account->userid.'&manager_token='.$user_account->psw_hash.'&'.$post_params;
	
	if ( $local_request )
		$url = 'http://'.SITE_SHORTDOMAIN.'/services/api.php?'.$request.'=1&'.$get_params;
	else {
		parse_str($get_params, $get_arr);
		$get_params = '';
		foreach($get_arr as $key => $value)
			$get_params = $get_params.$value.'/';
		if (empty($api_domain))
			$api_domain = get_api_domain();
		$url = (defined('API_HTTP_PREFIX') && !empty(API_HTTP_PREFIX) ? API_HTTP_PREFIX : 'http://').$api_domain.'/api/'.$request.'/'.$get_params;
	}
	$post_params = $post_params.'&user_ip='.urlencode($_SERVER['REMOTE_ADDR']).'&webserver_ip='.urlencode($_SERVER['SERVER_ADDR']);
	
	$attempts = 0;
	$res = '';
	do {
		if ( $attempts > 0 )
			sleep($attempts);
		ini_set('default_socket_timeout', 60 * 3);
		$res = trim(do_post_request($url, $post_params));
		if ( isset($_GET['debug']) ) {
			echo "
			<div class='notranslate'>
			<br>Request ended: ".(time() - SCRIPT_STARTED_SEC)."*****<br>
			url:$url<br>
			request: $request<br>
			post_params: $post_params<br>
			res: '".strlen($res)//.substr($res, 0, 20000)
			."'<br>
			backtrace:<br>";
			//var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			echo "<br>*****<br>
			</div>";
			$attempts = $max_attempts + 1;
		}
		//if ( defined('DEBUG_MODE') && $request == 'get_priority_fee_in_satoshi' ) {echo "$url<br>post params:<br>"; var_dump($post_params); echo '<br>res:<br>'; var_dump($res); echo '<br>'; exit; }
		$attempts++;
	} while ( $attempts < $max_attempts && !is_integer(strpos($res, '"success')) );
	
	if ( defined('HOT_COLD_SWAP_SUSPENSE_PERIOD_IN_SEC') && !empty(HOT_COLD_SWAP_SUSPENSE_PERIOD_IN_SEC) )
		$timeout_to_switch_to_cold_mirror = HOT_COLD_SWAP_SUSPENSE_PERIOD_IN_SEC;
	else
		$timeout_to_switch_to_cold_mirror = 60 * 3;
	if ( isset($_GET['debug']) ) echo "timeout_to_switch_to_cold_mirror: $timeout_to_switch_to_cold_mirror seconds<br>";
		

	if ($res === false || empty($res)) {
		$no_responce_since = get_file_variable('no_responce_from_api');
		if (empty($no_responce_since))
			update_file_variable('no_responce_from_api', time());
		else 
		if (time() - intval($no_responce_since) > $timeout_to_switch_to_cold_mirror) {
			if ( !is_integer(strpos($url, 'cold_mirror.')) ) {
				// API server does not respond. Swith to cold mirror
				if (defined('USE_API_SERVER_NOT_COLD_MIRROR') && USE_API_SERVER_NOT_COLD_MIRROR == 'false') {
					delete_file_variable('api_server_domain');
				}
				else {
					$cold_mirror_url = (defined('API_HTTP_PREFIX') && !empty(API_HTTP_PREFIX) ? API_HTTP_PREFIX : 'http://').'cold_mirror.'.SITE_SHORTDOMAIN.'/api/master_is_out_of_order/';
					if ( defined('DEBUG_MODE') ) echo "No responce during ".(time() - intval($no_responce_since))." minutes from url: $url<br>cold_mirror_url: $cold_mirror_url<br>";
			
					$res_tmp = trim(do_post_request($cold_mirror_url)); // let the cold mirror know that API server does not respond
					if ( defined('DEBUG_MODE') ) echo "res_tmp: $res_tmp<br>";
					if ($res_tmp !== false && !empty($res_tmp)) {
						try {
							$res_tmp_json = json_decode($res_tmp, true);
							if ( defined('DEBUG_MODE') ) {echo "res_tmp_json:<br>"; var_dump($res_tmp_json); echo '<br>';}
							if ( $res_tmp_json["success"] ) {
								update_file_variable('api_server_domain', $res_tmp_json['values']['this_server_is_master']);
							}
						}
						catch (Exception $e) {}
					}
				}
			}
			else {
				// Cold mirror does not respond. Swith to api server
				if (defined('USE_API_SERVER_NOT_COLD_MIRROR') && USE_API_SERVER_NOT_COLD_MIRROR == 'false')
					update_file_variable('api_server_domain', 'api1.'.SITE_SHORTDOMAIN);
				else 
					delete_file_variable('api_server_domain');
				if ( defined('DEBUG_MODE') ) echo "Cold mirror does not respond. Swithing to api server<br>";
			}
		}
		return array('success' => false, 'empty_result' => true);
	}
	else {
		delete_file_variable('no_responce_from_api');
		$res_json = json_decode($res, true);
		switch ( $res_json["error_code"] ) {
			case 2: // wrong token
				if ( isset($user_account) && $user_account->is_loggedin() )
					$user_account->logout();
			break;
			case 'api_redirect_to_master': // server answers that this server is not master
				update_file_variable('api_server_domain', $res_json['values']['this_server_is_master']);
			break;
			default: // wrong password
			break;
		}
		global $received_API_server_IP;
		global $received_cold_mirror_IP;
		if (isset($res_json['api'])) {
			if ($res_json['api'] == '1')
				$received_API_server_IP = $res_json['ip'];
			else
				$received_cold_mirror_IP = $res_json['ip'];
		}
		return $res_json;
	}
}

function get_api_value($request = '', $get_params = '', $post_params = '', $token = '', $user_account = null, $local_request = false, $max_attempts = 10)
{
	$data = make_api_request($request, $get_params, $post_params, $token, $user_account, $local_request, $max_attempts);
	if ( $data['success'] )
		return $data['values'];
	else
		return false;
}

function make_api_request_with_error_message($request = '', $get_params = '', $post_params = '', $token = '', $user_account = null, $local_request = false, $max_attempts = 10)
{
	$data = make_api_request($request, $get_params, $post_params, $token, $user_account, $local_request, $max_attempts);
	if ( $data['success'] )
		return '';
	else
		return $data['message'];
}

function get_api_token_seed($force_update = false)
{
	if ( is_file_variable_expired('api_token_seed') || $force_update)
		update_file_variable('api_token_seed', get_api_value('api_token_seed', '', '', 'no token', null, false, 1));
	
	return get_file_variable('api_token_seed');
}

function get_global_token($force_update = false)
{
	if ( is_file_variable_expired('global_token') || $force_update) {
		$data = make_api_request('api_global_token', '', []);
		if ( $data['success'] && !empty($data['values']['token']) ) {
			update_file_variable('global_token', $data['values']);
		}
	}
	return get_file_variable('global_token');
}

function is_token_valid($token, $confidence_interval_in_minutes = 6)
{
	$api_token_seed = get_api_token_seed();
	for ($i = 0; $i <= $confidence_interval_in_minutes; $i++) 
		if ( $token == MD5( $api_token_seed.(round(time() / 60) + $i) ) || $token == MD5( $api_token_seed.(round(time() / 60) - $i) ) ) 
			return true;
	return false;
}

function is_token_from_hash_valid($token, $hash, $confidence_interval_in_days = 2)
{
	for ($i = 0; $i <= $confidence_interval_in_days; $i++) 
		if ( $token == MD5( $hash.(date('z') + $i) ) || $token == MD5( $hash.(date('z') - $i) ) ) 
			return true;
	return false;
}

function performEmbededCodeInText($text)
{
	while ( is_string($text) && is_integer(strpos($text, '{{$')) && is_integer(strpos($text, '}}')) ) {
		$code = get_text_between_tags($text, '{{$', '}}');
		$replace_with = '';
		$code = '$replace_with = '.$code.';';
		eval($code);
		delete_text_between_tags($text, '{{$', '}}', false, $replace_with);
	}
	return $text;
}

function hide_show_menu_item($webpage, $hide = 1)
{
	if ( is_integer(strpos($webpage, '/')) )
		$webpage = get_text_between_tags($webpage, '/');
	if ( $hide )
		update_file_variable('hidden_menu_item_'.$webpage, '1');
	else
		delete_file_variable('hidden_menu_item_'.$webpage);
}

function is_menu_item_hidden($webpage)
{
	if ( is_integer(strpos($webpage, '/')) )
		$webpage = get_text_between_tags($webpage, '/');
	if ( get_file_variable('hidden_menu_item_'.$webpage) )
		return true;
	if ( defined('HIDDEN_PAGES') && (is_integer(strpos(HIDDEN_PAGES, '"'.$webpage.'"')) || is_integer(strpos(HIDDEN_PAGES, '&quot;'.$webpage.'&quot;')) ) ) 
		return true;
	return false;
}

function easy_scramble($instring)
{
	$shift = rand(1, 9);
	$res = ':'.$shift;
	for ($i = 0; $i < strlen($instring); $i++ ) {
		if ( (ord($instring[$i]) >= ord('a') && ord($instring[$i]) <= ord('z')) || (ord($instring[$i]) >= ord('A') && ord($instring[$i]) <= ord('Z')) ) {
			$symb_ord = ord($instring[$i]) + $shift;
			if ( $symb_ord > ord('Z') && ord($instring[$i]) < ord('a') )
				$symb_ord = ord('A') + $symb_ord - ord('Z') - 1;
			else
			if ( $symb_ord > ord('z') )
				$symb_ord = ord('a') + $symb_ord - ord('z') - 1;
			$res = $res.chr($symb_ord);
		}
		else
			$res = $res.$instring[$i];
	}
	return $res;
}

function easy_descramble($instring)
{
	if ( $instring[0] != ':' )
		return $instring;
	$shift = (int)$instring[1];
	$res = '';
	for ($i = 2; $i < strlen($instring); $i++ ) {
		if ( (ord($instring[$i]) >= ord('a') && ord($instring[$i]) <= ord('z')) || (ord($instring[$i]) >= ord('A') && ord($instring[$i]) <= ord('Z')) ) {
			$symb_ord = ord($instring[$i]) - $shift;
			if ( $symb_ord < ord('A') )
				$symb_ord = ord('Z') - (ord('A') - $symb_ord - 1);
			else
			if ( $symb_ord < ord('a') && ord($instring[$i]) > ord('Z') )
				$symb_ord = ord('z') - (ord('a') - $symb_ord - 1);
			$res = $res.chr($symb_ord);
		}
		else
			$res = $res.$instring[$i];
	}
	return $res;
}

function formatSizeUnits($bytes, $digits = 2)
{
	if ($bytes >= 1125899906842624)
		$bytes = number_format($bytes / 1125899906842624, $digits) . ' PB';
	else
	if ($bytes >= 1099511627776)
		$bytes = number_format($bytes / 1099511627776, $digits) . ' TB';
	else
	if ($bytes >= 1073741824)
		$bytes = number_format($bytes / 1073741824, $digits) . ' GB';
	else
	if ($bytes >= 1048576)
		$bytes = number_format($bytes / 1048576, $digits) . ' MB';
	else
	if ($bytes >= 1024)
		$bytes = number_format($bytes / 1024, $digits) . ' KB';
	else
	if ($bytes > 1)
		$bytes = $bytes . ' bytes';
	else
	if ($bytes == 1)
		$bytes = $bytes . ' byte';
	else
		$bytes = '0 bytes';
	return $bytes;
}

function stat_get_disk_total_space()
{
	$str = shell_exec('df --total --block-size=1 / | grep total');
	while ( is_integer(strpos($str, '  ')) )
		$str = str_replace('  ', ' ', $str);
	return get_text_between_tags($str, 'total ', ' ');
}

function stat_get_disk_free_space()
{
	$str = shell_exec('df --total --block-size=1 / | grep total');
	while ( is_integer(strpos($str, '  ')) )
		$str = str_replace('  ', ' ', $str);
	$total = get_text_between_tags($str, 'total ', ' ');
	delete_text_between_tags($str, 'total ', ' ');
	delete_text_between_tags($str, ' ', ' ');
	return get_text_between_tags($str, ' ', ' ');
}

function stat_get_server_cpu_usage()
{
	$str = shell_exec('top -bn2 | grep \'%Cpu\' | tail -1 | grep -P  \'(....|...) id,\'');
	$str = trim(get_text_between_tags($str, 's): ', ' us'));
	return floatval($str) / 100;
}
function stat_get_server_memory_usage()
{
	$free = shell_exec('free');
	$free = (string)trim($free);
	$free_arr = explode("\n", $free);
	$mem = explode(" ", $free_arr[1]);
	$mem = array_filter($mem);
	$mem = array_merge($mem);
	$memory_usage = ($mem[1] - $mem[3]) / $mem[1] * 100;//$mem[2]/$mem[1]*100;
	return $memory_usage;
}

function stat_www_requests_p_sec()
{
	$s = shell_exec('apache2ctl status');
	if ( empty($s) )
		$s = shell_exec('/usr/sbin/apache2ctl status');
	return (float)trim(get_text_between_tags($s, 'CPU load', 'requests/sec'));
}

function getALLfromIP($addr) 
{
	return get_api_value('get_all_from_ip', array('addr' => $addr));
}

function get_country_code_from_geo_ip($ip)
{
	$res = '';
	
	// !!!! this function removed because takes lot of time

	/*$ip_numb = ip2long($ip);
	if ($file = fopen(DIR_DATA.'geo_ip', "r")) {
		while(!feof($file)) {
			$line = fgets($file);
			$line_arr = explode(',', $line);
			$line_arr[0] = ip2long(trim(str_replace('"', '', $line_arr[0])));
			$line_arr[1] = ip2long(trim(str_replace('"', '', $line_arr[1])));
			if ( ($ip_numb >= $line_arr[0] && $ip_numb <= $line_arr[1]) || ($ip_numb >= $line_arr[1] && $ip_numb <= $line_arr[0]) ) {
				$res = $line_arr[2] = trim(str_replace('"', '', $line_arr[2]));
				break;
			}
		}
		fclose($file);
	}*/
	return $res;
}

function getCountryCodefromIP($addr = '') 
{
	if ( empty($addr) )
		$addr = $_SERVER['REMOTE_ADDR'];
	
	$cc = get_country_code_from_geo_ip($addr);
	if ( !empty($cc) )
		return $cc;

	$data = getALLfromIP($addr);
	if ( $data )
		return $data['cc'];
	return false;
}

function getCountryNamefromIP($addr = '') 
{
	if ( empty($addr) )
		$addr = $_SERVER['REMOTE_ADDR'];
	$data = getALLfromIP($addr);
	if ( $data )
		return $data['name'];
	return false;
}

function getCountryName($CountryCode) 
{
	if ( empty($CountryCode) )
		return false;
	if ( file_exists(DIR_WS_TEMP.'countries.txt') ) {
		$countries_text = file_get_contents(DIR_WS_TEMP.'countries.txt');
		$countries_tmp = preg_split('/$\R?^/m', $countries_text);
		foreach ($countries_tmp as $value) {
			if ( !empty($value) ) {
				$cntry_arr = explode("=", $value);
				if ( strtoupper($CountryCode) == strtoupper($cntry_arr[0]) ) 
					return $cntry_arr[1];
			}
		}
	}
	else
		return get_api_value('get_country_name', array('country_code' => $CountryCode));
}

function getEmailTemplate($template_name, &$subject, &$body, $params = '', $use_db = true) 
{
	if ($use_db) {
		$result = get_api_value('email_get_template', array('template_name' => $template_name));
		if ( $result ) {
			$subject = $result['subject'];
			$body = $result['body'];
		}
		else
			return false;
	}
	$paramsArr = array();
	if ( !empty($params) ) {
		parse_str($params, $paramsArr);
		foreach ($paramsArr as $key => $value) {
			if ( strpos($key, '_x0') === 0 ) {
				$value = hex2bin($value);
				$key = substr($key, 3);
			}
			else
				$value = urldecode($value);
			$subject = str_replace('{$'.$key.'}', $value, $subject);
			$body = str_replace('{$'.$key.'}', $value, $body);
		}
	}	
	return true;
}

function make_countries_file()
{
	$file_name = DIR_WS_TEMP.'countries.txt';
	if ( file_exists($file_name) && time() - filemtime($file_name) < 600 )
		return true;
	try {
		$countries = get_api_value('get_countries_list');
		if ( isset($countries) && $countries !== false && is_array($countries)) {
			unlink($file_name);
			foreach( $countries as $country ) {
				file_put_contents($file_name, $country['cc'].'='.$country['name']."\r\n", FILE_APPEND);
			}
		}
	}
	catch (Exception $e) {}
	return true;
}

function get_fiat_info_by_country($country_code)
{
	$countries = get_api_value('get_countries_list', '', ['condition' => 'cc = `'.strtolower($country_code).'`', 'order_by' => 'name ASC LIMIT 1']);
	if ( $countries && count($countries) > 0 ) {
		return $countries[0];
	}
	return false;
}

function draw_graph($caption, $graphs, $data_period = '12month', $group_by = 'week', $date = 0, 
	$prefix = '', $pie_quiery = '', $name_of_the_graph_container = 'graph_container', $bg_color = 'transparent',
	$bottom_captions_frequiency = '', $return_script_only = false, $minimum_not_zero = false, $return_data_only = false, $additional_data = null, $return_ajax_code = false, $custom_term_bigin = '', $custom_term_end = '')
{
	if ( $return_ajax_code ) {
		global $user_account;
		switch ( $group_by ) {
			case 'day' : 
				if ( empty($bottom_captions_frequiency) ) {
					if ( $data_period == '7days' )
						$bottom_captions_frequiency = 1; 
					else
					if ( $data_period == '30days' )
						$bottom_captions_frequiency = 7; 
					else
					if ( $data_period == '90days' )
						$bottom_captions_frequiency = 14; 
					else
						$bottom_captions_frequiency = 30; 
				}
				$value_format_string = 'YYYY, DD MMM.'; 
				$graph_interval = 'interval:'.$bottom_captions_frequiency.', intervalType:"day", ';
			break;
			case 'week' : 
				if ( empty($bottom_captions_frequiency) ) {
					if ( $data_period == '30days' || $data_period == '90days' )
						$bottom_captions_frequiency = 1; 
					else
						$bottom_captions_frequiency = 4; 
				}				
				$value_format_string = 'YYYY, DD MMM.'; 
				$graph_interval = 'interval:'.$bottom_captions_frequiency.', intervalType:"week", ';
			break;
			case 'month' : 
				if ( empty($bottom_captions_frequiency) )
					$bottom_captions_frequiency = 1; 
				$value_format_string = 'MMM'; 
				$graph_interval = 'interval:1, intervalType:"month", ';
			break;
		}
		$additional_params = '';
		if (isset($additional_data) ) {
			if (is_array($additional_data)) {
				foreach($additional_data as $key => $value)
					$additional_params = $additional_params.", $key: '$value'";
			}
			else
				$additional_params = $additional_data;
		}
		$res = '
		<script type="text/javascript" src="/javascript/canvasjs/source/canvasjs.js"></script>
		<script type="text/javascript">
		$( document ).ready(function() {
			try {
				$.ajax({
					method: "POST",
					url: "/api/draw_graph",
					data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'", graphs: "'.$graphs.'", caption:"", data_period:"'.$data_period.'", group_by:"'.$group_by.'", prefix:"'.$prefix.'", name_of_the_graph_container:"", bg_color:"", bottom_captions_frequiency:"", return_script_only:0, minimum_not_zero:0, return_data_only:1, custom_term_bigin: "'.$custom_term_bigin.'", custom_term_end:"'.$custom_term_end.'" '.$additional_params.'}
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							var Y_min = 0;
							var Y_max = 0;
							var graph_points = JSON.parse(arr_ajax__result["values"], 
								function(key, value) {
									if (key == "x") { 
										var date = new Date(value[0], value[1], value[2], value[3], value[4]); 
										return date;
									}
									if (key == "y") {
										if (Y_max < value)
											Y_max = value;
										//if (Y_min > value)
											//Y_min = value;
									}
									return value;
								}
							);
							Y_max = Y_max * 1.2;
							if (Y_max == 0)
								Y_max = 1;
							var graph_js = new CanvasJS.Chart("'.$name_of_the_graph_container.'",
							{
								theme: "theme1",
								backgroundColor: "'.$bg_color.'",
								title:{
									fontSize: 12,
									text: "'.$caption.'",
								},
								axisY: {
									minimum: Y_min, 
									maximum: Y_max, 
									titleFontSize: 14,
									lineThickness: 1,
									lineColor: "#'.COLOR1LIGHT.'",
									includeZero: false,
									prefix: "'.$prefix.'",
									gridColor: "#'.COLOR1LIGHT.'",
									gridThickness: 1,     
									tickColor: "#'.COLOR1LIGHT.'",
									tickLength: 2,
									tickThickness: 1,
									interlacedColor: "#'.COLOR2LIGHT.'",
									labelFontColor: "#'.COLOR1DARK.'",
									labelFontSize: 10
								},
								axisX: {
									'.($data_period == 'today'?'interval:1, intervalType:"hour", valueFormatString:"hh", ':$graph_interval.' valueFormatString: "'.$value_format_string.'",').'
									titleFontSize: 14,
									lineThickness: 1,
									lineColor: "#'.COLOR1LIGHT.'",
									gridColor: "#'.COLOR1LIGHT.'" ,
									gridThickness: 1,
									tickColor: "#'.COLOR1LIGHT.'",
									tickLength: 2,
									tickThickness: 1,
									labelFontColor: "#'.COLOR1DARK.'",
									labelFontSize: 10
								},
								data: graph_points
							});
							graph_js.render();
						}
					}
					catch(error){console.error(ajax__result + " '.$name_of_the_graph_container.' " + error);}
				});
			}
			catch(error){console_log("'.$name_of_the_graph_container.': " + error);}
		});
		</script>
		';
	}
	else {
		$data = array('caption' => $caption, 'graphs' => $graphs, 'data_period' => $data_period, 'group_by' => $group_by, 'date' => $date, 'prefix' => $prefix, 'name_of_the_graph_container' => $name_of_the_graph_container, 'bg_color' => $bg_color, 'bottom_captions_frequiency' => $bottom_captions_frequiency, 'return_script_only' => $return_script_only, 'minimum_not_zero' => $minimum_not_zero, 'return_data_only' => $return_data_only, 'custom_term_bigin' => $custom_term_bigin, 'custom_term_end' => $custom_term_end);
		if (isset($additional_data) && is_array($additional_data))
			$data = array_merge($data, $additional_data);
		$res = get_api_value('draw_graph', '', $data);
	}
	return $res;
}

function make_enumeration($values_arr)
{
	$enumeration = '';
	foreach ( $values_arr as $value ) {
		$enumeration = str_replace(' or ', ', ', $enumeration);
		if ( !empty($enumeration) )
			$enumeration = $enumeration.' or ';
		$enumeration = $enumeration.$value;
	}
	return $enumeration;
}

if ( !function_exists('parse_params') ) {
	function parse_params($input_str, $params)
	{
		$paramsArr = array();
		if ( !empty($params) ) {
			parse_str($params, $paramsArr);
			foreach ($paramsArr as $key => $value) {
				if ( strpos($key, '_x0') === 0 ) {
					$value = hex2bin($value);
					$key = substr($key, 3);
				}
				else
					$value = urldecode($value);
				$input_str = str_replace('{$'.$key.'}', $value, $input_str);
			}
		}
		return $input_str;
	}
}

function remove_from_black_list($email)
{
	if ( empty($email) )
		return false;
	return get_api_value('admin_remove_from_black_list', '', array('email' => $email));
}

function save_click($bannerId = '', $user_id = '', $referer_url = '', $fingerprint = '')
{
	$clicks_arr = json_decode(get_file_variable('last_ip'), true);
	do {
		$oldest_val = 0;
		foreach($clicks_arr as $ip => $val) {
			if ( $val['time'] < time() - 60 * 60 ) {
				$oldest_val = $ip;
			}
		}
		if ( !empty($oldest_val) )
			unset($clicks_arr[$oldest_val]);
	} while ( !empty($oldest_val) );
	if ( empty($referer_url) )
		$referer_url = @$_SERVER['HTTP_REFERER'];
	else
		$referer_url = urldecode($referer_url);
	
	$ref_domain = get_domain($referer_url);

	$referer_url = substr($referer_url, 0, 128);
	if ( empty($referer_url) || !is_string($referer_url) )
		$referer_url = '';
	
	if ( empty($user_id) && isset($_GET['a_aid']) )
		$user_id = $_GET['a_aid'];
	if ( empty($user_id) && $clicks_arr[$_SERVER['REMOTE_ADDR']] && intval($clicks_arr[$_SERVER['REMOTE_ADDR']]['user']) > 0 )
		$user_id = $clicks_arr[$_SERVER['REMOTE_ADDR']]['user'];
	if ( $clicks_arr[$_SERVER['REMOTE_ADDR']] && !empty($clicks_arr[$_SERVER['REMOTE_ADDR']]['referer_url']) )
		$referer_url = $clicks_arr[$_SERVER['REMOTE_ADDR']]['referer_url'];
	if ( $clicks_arr[$_SERVER['REMOTE_ADDR']] && !empty($clicks_arr[$_SERVER['REMOTE_ADDR']]['fingerprint']) )
		$fingerprint = $clicks_arr[$_SERVER['REMOTE_ADDR']]['fingerprint'];
	$clicks_arr[$_SERVER['REMOTE_ADDR']] = array('user' => $user_id, 'time' => time(), 'clickcount' => $clicks_arr[$_SERVER['REMOTE_ADDR']]['clickcount'] + 1, 'referer_url' => $referer_url, 'bannerid' => $bannerId, 'fingerprint' => $fingerprint);
	update_file_variable('last_ip', json_encode($clicks_arr));
	
	if ( !empty($user_id) )
		setcookie(TRACK_COOKIE_NAME, 'type=C&banner='.$bannerId.'&user='.$user_id, time() + 60 * 60 * 24 * 365, '/');
	if ( !empty($ref_domain) && empty($_COOKIE[TRACK_COOKIE_DOMAIN]) )
		setcookie(TRACK_COOKIE_DOMAIN, $ref_domain, time() + 60 * 60 * 24 * 10, '/');
}

function search_userid($fingerprint = '')
{
	$user_id = '';
	if ( !empty($_COOKIE[TRACK_COOKIE_NAME]) ) {
		$s = $_COOKIE[TRACK_COOKIE_NAME];
		parse_str($s, $cookie_arr);
		$user_id = $cookie_arr['user'];
	}
	if ( empty($user_id) ) {
		$clicks_arr = json_decode(get_file_variable('last_ip'), true);
		$user_id = $clicks_arr[$_SERVER['REMOTE_ADDR']]['user'];
	}
	if ( empty($user_id) ) {
		$user_id_arr = get_api_value('user_search_userid_in_db', '', array('search_userid_ip' => $_SERVER['REMOTE_ADDR']));
		if ( $user_id_arr && $user_id_arr['userid'] )
			$user_id = $user_id_arr['userid'];
	}
	if ( empty($user_id) && !empty($fingerprint) ) {
		$clicks_arr = json_decode(get_file_variable('last_ip'), true);
		foreach($clicks_arr as $ip => $val) {
			if ( $val['fingerprint'] == $fingerprint && !empty($val['user']) ) {
				$user_id = $val['user'];
				break;
			}
		}
	}
	return $user_id;
}

function restore_design_data_from_db()
{
	$data = get_api_value('restore_file_from_db', '', array('file_name' => '$$$design.ini'));
	if ( $data && !empty($data['file_body']) ) {
		$data['file_body'] = base64_decode($data['file_body']);
		file_put_contents(DIR_DATA.'$$$design.ini', $data['file_body']);
	}
	return $data;
}

function get_credit_rating($score, &$rating_color, &$rating_text_color)
{
	$ratings = array("F-", "E-", "E+", "D-", "D+", "C-", "C+", "B-", "B+", "A-", "AA");
	//					0     1     2     3     4   5     6     7     8     9     10		 
	//					0-49  50    150   250   350 450   550   650   750   850   950		 
	//               <--- danger     ---><-warning-><   info   ><--   success    -->
	if ( $score < 0 )
		$score = 0;
	if ( $score > 999 )
		$score = 999;
	if ( $score < 250 ) {
		$rating_color = "danger";
		$rating_text_color = "ffffff";
	}
	else
	if ( $score < 450 ) {
		$rating_color = "warning";
		$rating_text_color = "ffffff";
	}
	else
	if ( $score < 650 ) {
		$rating_color = "info";
		$rating_text_color = "ffffff";
	}
	else {
		$rating_color = "success";
		$rating_text_color = "ffffff";
	}
	return $ratings[round($score / 100)];
}

function file_has_malicious_code($filename = '', $name = '', $file_data = '')
{
	if (empty($file_data))
		$file_data = file_get_contents($filename);
	return is_integer(strpos($file_data, '<?'.'php')) || is_integer(strpos($name, '/')) || is_integer(strpos($name, '\\'));
}

function convert_regex_to_description($input_pattern)
{
	$length = get_text_between_tags($input_pattern, '{', '}');
	$length_arr = explode(',', $length);
	if (!empty($length_arr[1]))
		$length = 'from '.($length_arr[0]).' to '.(empty($length_arr[1])?'infinity':($length_arr[1] + 1));
	else {
		if (isset($length_arr) && $length_arr[0] > 0)
			$length = 'minimum '.($length_arr[0]);
		else
			$length = '';
	}
	$start_let = get_text_between_tags($input_pattern, '^[', ']');
	$start_letters = '';
	if (!empty($start_let)) {
		if (is_integer(strpos($start_let, '-'))) {
			$start_letters = 'having '.$start_let;
		}
		else {
			for ($j = 0; $j < strlen($start_let); $j++) {
				if ( !empty($start_letters) )
					$start_letters = $start_letters.' or ';
				$start_letters = $start_letters.'&quot;'.$start_let[$j].'&quot;';
			}
			$start_letters = 'beginning with the '.$start_letters;
		}
	}
	$s = get_text_between_tags($input_pattern, '[^', ']');
	$not_allow_chars_arr = explode('^', '^'.$s);
	$not_allow_chars = '';
	foreach ($not_allow_chars_arr as $not_allow_char) {
		if (!empty($not_allow_char))
			$not_allow_chars = $not_allow_chars.(!empty($not_allow_chars)?' or ':'').'&quot;'.str_replace('\\', '', $not_allow_char).'&quot;';
	}
	return 'must be '.$length.' '.(is_integer(strpos($input_pattern, '[0-9]'))?'digits':'alphanumeric characters').(!empty($start_letters)?', '.$start_letters:'').(!empty($not_allow_chars)?', cannot include '.$not_allow_chars.' characters':'');
}

function euclidean_distance($vector1, $vector2)
{
	$n = count($vector1);
	$sum = 0;
	for ($i = 0; $i < $n; $i++) {
		$sum += ($vector1[$i] - $vector2[$i]) * ($vector1[$i] - $vector2[$i]);
	}
	return sqrt($sum);
}

function replace_with_translated_text($str, $language, $script_name, $left_marker = '', $right_marker = '', $replace_left_marker = '', $replace_right_marker = '', $replace_left_marker_if_not_found = '')
{
	$tx = get_text_between_tags($str, $left_marker, $right_marker);
	if (empty($tx)) {
		return false;
	}
	else {
		$file_name = DIR_WS_TEMP_ON_WEBSITE.$language.'/'.$script_name.'/'.md5($tx).'.lng';
		if (file_exists($file_name)) {
			$translated_text = base64_decode(file_get_contents($file_name));
			delete_text_between_tags($str, $left_marker, $right_marker, false, $replace_left_marker.$translated_text.$replace_right_marker);
		}
		else {
			delete_text_between_tags($str, $left_marker, $right_marker, false, $replace_left_marker_if_not_found.$tx.$replace_right_marker);
		}
	}
	return $str;
}

function get_selected_language()
{
	if (!defined('SHOW_GOOGLE_TRANSLATE') || SHOW_GOOGLE_TRANSLATE != 'true')
		return '';

	$language = '';
	if (!empty(@$_COOKIE['googtrans'])) {
		$not_good = false;
		$language = tep_sanitize_string($_COOKIE['googtrans']);
		while (is_integer(strpos($language, '/'))) {
			$language = get_text_between_tags($language, '/');
		}
		if ( is_integer(strpos($language, '/')) || is_integer(strpos($language, '\\')) )
			$not_good = true;
		if ( strlen($language) > 3 )
			$not_good = true;
		if (!$not_good) {
			return $language;
		}
	}
	else 
	if (defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true') {
		if (empty(@$_COOKIE['language_from_ip'])) {
			$country_code = getCountryCodefromIP();
			$lang_dir = DIR_WS_TEMP_ON_WEBSITE.strtolower($country_code).'/';
			if (file_exists($lang_dir)) {
				$language = strtolower($country_code);
			}
			setcookie('language_from_ip', !empty($language) ? $language : 'unknown');
		}
		else {
			$language = $_COOKIE['language_from_ip'] == 'unknown' ? '' : $_COOKIE['language_from_ip'];
		}
	}
	return $language;
}

function parse_script_name()
{
	return preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $_SERVER['PHP_SELF'])));
}

// Translate string which has special HTML tags around it
function translate_str($str, $en_hash = '')
{
	if (!empty(@$_COOKIE['googtrans']) && empty(@$_POST['no_translate_strings']) && !empty($str)) {
		$language = get_selected_language();
		if (!empty($language)) {
			$script_name = parse_script_name();
			while ( true ) {
				$res = replace_with_translated_text($str, $language, $script_name, '<span class="string_to_translate" >', '</span>', '<span class="notranslate">', '</span>', '<span class="string_to_translate"'.(!empty($en_hash) ? ' _en_hash="'.$en_hash.'"' : '').'  >');
				if ($res !== false)
					$str = $res;
				else {
					$res = replace_with_translated_text($str, $language, $script_name, 'string_to_translate">', '<', 'notranslate">', '<', 'string_to_translate"'.(!empty($en_hash) ? ' _en_hash="'.$en_hash.'"' : '').'  >');
					if ($res !== false)
						$str = $res;
					else {
						$res = replace_with_translated_text($str, $language, $script_name, "string_to_translate'>", '<', "notranslate'>", '<', "string_to_translate'".(!empty($en_hash) ? " _en_hash='".$en_hash."'" : "")."  >");
						if ($res !== false)
							$str = $res;
						else {
							break;
						}
					}
				}
			}
		}
	}
	return $str;
}

// Translate string which has no HTML tags
function translate_text($str, $left_marker = '', $right_marker = '', $replace_left_marker = '', $replace_right_marker = '', $replace_left_marker_if_not_found = '')
{
	$language = get_selected_language();
	if (!empty($language)) {
		$script_name = parse_script_name();
		$str = replace_with_translated_text($str, $language, $script_name, $left_marker, $right_marker, $replace_left_marker, $replace_right_marker, $replace_left_marker_if_not_found);
	}
	return $str;
}

// Translate string by adding has special HTML tags to the beginning and to the end
function make_str_translateable($str, $add_prefix = '<span class=\'string_to_translate\'>', $add_suffix = '</span>')
{
	$html_str = $str;
	if (!empty($add_prefix))
		$html_str = $add_prefix.$html_str;

	if (!empty($add_suffix))
		$html_str = $html_str.$add_suffix;
	
	return translate_str($html_str, md5($str));
}

function get_page_name()
{
	if ( !function_exists('clean_name') ) {
		function clean_name($script_name)
		{
			$s = '';
			for ($i = strlen($script_name) - 1; $i >= 0; $i-- ) {
				if ( $script_name[$i] != '/' )
					$s = $script_name[$i].$s;
				else
					break;
			}
			$script_name = $s;
			if ($script_name[0] == '/')
				$script_name = substr($script_name, 1);
			$script_name = get_text_between_tags($script_name, '', '?');
			$script_name = get_text_between_tags($script_name, '', '&');
			$script_name = get_text_between_tags($script_name, '', '/');
			return $script_name;
		}
	}
	return [clean_name($_SERVER['SCRIPT_NAME']), clean_name($_SERVER['REQUEST_URI'])];
}

function parse_value_by_locale($string_to_parse)
{
	$result = '';
	if (is_integer(strpos($string_to_parse, '{'))) {
		$language = get_selected_language();
		if (!empty($language)) {
			$tags = $string_to_parse;
			for ($i = 0; $i < 50; $i++) {
				$tag = get_text_between_tags($tags, '{', '}');
				if (empty($tag))
					break;
				delete_text_between_tags($tags, '{', '}');
				$list_of_languages = get_text_between_tags($tag, '', '|');
				if (!empty($list_of_languages)) {
					if (is_integer(strpos($list_of_languages, $language))) {
						$result = get_text_between_tags($tag, '|', '');
					}
				}
			}
		}
		if (empty($result))
			$result = get_text_between_tags($string_to_parse, '{|', '}');
	}
	else
		$result = $string_to_parse;
	return $result;
}
?>
