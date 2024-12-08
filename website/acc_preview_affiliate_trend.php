<?php
/*
require('../includes/application_top.php');
if ( !empty($_GET['userid']) )
	$userid = $_GET['userid'];
else {
	header('Location: /');
	exit;
}
$page_header = 'Visits';
$page_title = $page_header.'. '.SITE_NAME;
$page_desc = 'See stats about visits.';
$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');
$user = new User();
$user->userid = $userid;
if ( !$user->read_data(false) ) {
	echo '<h3>User not found</h3>';
	exit;
}
if ( empty($_GET['hide_info']) ) {
	$date_created = DateTime::createFromFormat('Y-m-d H:i:s', $user->created);
	$s = $user->stat_last_login;
	if ( !empty($s) ) {
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $s);
		$last_login = $date->format("j M Y H:i");
	}
	else
		$last_login = 'never logged in';
	echo '
	<table class="table">
	<tr>
		<td valign="top" style="border:none;">
			<img class="first_page_image" src="'.$user->get_photo().'" alt="" style="width:60px; height:60px; margin:4px; margin-bottom:4px;"> 
		</td>
		<td valign="top" style="border:none;">
			<h2 style="">'.($user_account->is_manager()?'<a href="/acc_viewuser.php?userid='.$user->userid.'" target="_blank">'.$user->firstname(true).'</a>':$user->firstname(true)).'</h2>
			<img src="'.$user->get_rank_image().'" border="0" alt="">
		</td>
		<td valign="top" style="border:none;">
			<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'stocks100x100.png" border="0" alt="" style="width:50px; height:50px;">
			<h2 style="margin-top:0; padding-top:0;"><span style="font-weight:bold;">'.$user->number_of_shares.'</span> '.WORD_MEANING_SHARE.'s</h2>
		</td>
		<td valign="top" style="border:none;">
			<p>Member since: <strong>'.$date_created->format("j M Y").'</strong></p>
			<p>Last login: <strong>'.$last_login.'</strong></p>
			<p style="margin-top:0px; margin-bottom:0px;">Last 7 days income: <strong>'.currency_format($user->stat_last7days_income, '', 'color:#ff0000', 'color:#008800').'</strong></p>
		</td>
	</tr>
	</table>
	<div class="submit_frm_table" id="graph_container" style="width:100%;height:250px;margin:0px;"></div>'.
		draw_graph('Last 7 Days Income', 'last_7_days_income', '7days', 'day', 1, DOLLAR_SIGN, '', 'graph_container', 'transparent', '', false, false, false, array('graph_of_user' =>$user->userid )).'
		';
}*/
?>
</body>
</html>