<?php
if ( !empty($_SERVER['QUERY_STRING']) && (int)$_SERVER['QUERY_STRING'] > 0 )
	$_GET['a_aid'] = (int)$_SERVER['QUERY_STRING'];
chdir('services');
include('click.php');
?>