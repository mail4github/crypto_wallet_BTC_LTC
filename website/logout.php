<?php
$showDebugMessages = false;

if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);
	
require('../includes/application_top.php');

$user_account->logout();

$page_title = 'Logging out'.'. '.SITE_TITLE;
require(DIR_WS_INCLUDES.'header.php');
?>
<h1><?php echo make_str_translateable('Sign Out'); ?></h1>
<br><br>
<h2><?php echo make_str_translateable('You have been logged out.'); ?></h2>
<br>
<form action="/login" method="get">
<div class="row">
	<div class="col-md-12" style="text-align:left;" >
		<button name="submit_btn" class="btn btn-primary btn-lg"><?php echo make_str_translateable('Login Again'); ?></button>
	</div>
</div>
</form>
<?php 
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
