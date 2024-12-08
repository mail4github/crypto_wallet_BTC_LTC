<?php
include_once('../../includes/config.inc.php');

$showDebugInfo = defined('DEBUG_MODE');
if ( $showDebugInfo )
	error_reporting(E_ALL);

include_once(DIR_COMMON_PHP.'general.php');

/*if ( $_POST['save_article'] == '1' ) {
	if ( !empty($_POST['keyword']) ) {
		get_api_value('save_article', '', array('keyword' => $_POST['keyword'], 'article' => $_POST['article'], 'authorid' => $_POST['authorid']) );
		echo 'success';
		exit;
	}
}
else*/
if ( $_GET['get_article'] == '1' ) {
	$keywords_arr = get_api_value('get_article', '', array('keyword' => '', 'count' => 1) );
	$keywords_arr = $keywords_arr[0];
	if ( $keywords_arr ) {
		$text = mb_convert_encoding($keywords_arr['article'], 'HTML-ENTITIES', 'UTF-8');
		$keyword = mb_convert_encoding($keywords_arr['keyword'], 'HTML-ENTITIES', 'UTF-8');
		$subject = $keyword;
	}
	else {
		$kw = SITE_KEYWORDS;
	    if ( defined('SITE_KEYWORDS') && !empty($kw) ) {
		    $kw = str_replace(', ', '|', $kw);
		    $kw = str_replace(',', '|', $kw);
			$keyword = make_synonyms('['.$kw.']', 3, '', '', true);
			$text = mb_convert_encoding(SITE_DESCRIPTION, 'HTML-ENTITIES', 'UTF-8');
			$keyword = mb_convert_encoding($keyword, 'HTML-ENTITIES', 'UTF-8');
			$subject = $keyword;
	    }
	}
	$url = SITE_DOMAIN.'_/'.str_replace(' ', '_', $keyword).'/'.(!empty($_GET['for_userid'])?'r'.tep_sanitize_string($_GET['for_userid'], 10).'_':'').mb_convert_encoding(make_synonyms('[Looking-for|Searching|Where|How|Who-is|Who-knows|Help||]', 3, '', '', true).'-'.str_replace(' ', '-', $keyword).'.html', 'HTML-ENTITIES', 'UTF-8');
	if ($showDebugInfo)
		echo 'url='.$url.'&keyword='.$keyword.'&subject='.$subject.'&text='.$text.'&';
	else
		echo 'url='.bin2hex($url).'&keyword='.bin2hex($keyword).'&subject='.bin2hex($subject).'&text='.bin2hex($text).'&';
}
else
if ( !empty($_GET['is_keyword']) ) {
	echo '<RESULT>'.get_api_value('is_keyword', '', array('is_keyword' => bin2hex(tep_sanitize_string($_GET['is_keyword'], 256))) ).'</RESULT>';
}

?>