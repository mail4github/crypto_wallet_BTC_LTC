<?php

$showDebugInfo = defined('DEBUG_MODE');
if ( $showDebugInfo )
	error_reporting(E_ALL);

if ( !$showDebugInfo ) 
	header("HTTP/1.0 304 Not Modified");
				
?>
