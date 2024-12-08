<?php

$showDebugInfo = defined('DEBUG_MODE');
if ( $showDebugInfo )
	error_reporting(E_ALL);
echo '/*';
include_once('../../includes/config.inc.php');
include_once(DIR_COMMON_PHP.'general.php');
//$user_id = track_user_id( get_number_of_sql_connections() < MIN_NUMB_OF_SQL_CONNECTIONS * 1.5 );
$user_id = search_userid();
echo '*/'."\r\n".'
var af_id = "a_'.$user_id.'"; 
var parent_id = "'.$user_id.'"; 
';
?>



