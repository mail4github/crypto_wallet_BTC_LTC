<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'Top partner challenge';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');

// Each day this page are updated with a list of members who have accumulated the most number of referrals.

$top_users_tmp_file = 'top_users';
if ( $user_account->is_loggedin() || is_file_variable_expired($top_users_tmp_file, 60) || defined('DEBUG_MODE') ) {
	global $ranks;
	$box_message = '';
	include_once(DIR_COMMON_PHP.'print_sorted_table.php');
	$current_contest_data = get_api_value('get_contest_data');
	
	$output_str = show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'award.png', '<div style="min-height:32px;">
		<span style="font-size:200%; text-transform:uppercase; font-weight:bold;">Partner contest</span><br>
		Starts <b>'.$current_contest_data['current_contest_starts'].'</b>, ends <b>'.$current_contest_data['current_contest_ends'].'</b>
		</div>', 'alert-info').'
		<p style="font-size:200%; text-transform:uppercase; font-weight:bold; text-align:center;">Every month we are giving out 3 prizes:</p>
		<h2 style="text-align:center;">First Place:</h2>
		<p style="font-size:300%; font-weight:bold; color:#'.COLOR1DARK.'; text-align:center;" class="notranslate">'.currency_format($current_contest_data['current_contest_1place']).'</p>
		<h2 style="text-align:center;">Second Place:</h2>
		<p style="font-size:270%; font-weight:bold; color:#'.COLOR1DARK.'; text-align:center;" class="notranslate">'.currency_format($current_contest_data['current_contest_2place']).'</p>
		<h2 style="text-align:center;">Third Place:</h2>
		<p style="font-size:220%; font-weight:bold; color:#'.COLOR1DARK.'; text-align:center;" class="notranslate">'.currency_format($current_contest_data['current_contest_3place']).'</p>
		<p style="font-size:250%; text-transform:uppercase; font-weight:bold; text-align:center; margin-top:30px;">Current round ends in '.show_plural(count_days( strtotime($current_contest_data['current_contest_ends']), time() ), 'day', true).'</p>
		<p style="text-align:center;">How to participate?</p>
		<p style="text-align:center;">Just create account at this site and start promote your <b>Referral Link</b>. Visit this page periodically to see the results.</p>
		';
	if ( !empty($current_contest_data['contest_winner_name1']) ) {
		$previous_contest_winners = '';
		for ($i = 1; $i <= 3; $i++) {
			if ( $i == 1 ) {
				$user = new User();
				$user->userid = $current_contest_data['contest_winner_id'.$i];
				if ( $user->read_data(false) ) {
					$photo = $user->get_photo();
					if ( is_integer(strpos($photo, 'no_photo')) )
						$photo = '/'.DIR_WS_WEBSITE_IMAGES_DIR.'1st-place.png';
				}
				else
					$photo = '/'.DIR_WS_WEBSITE_IMAGES_DIR.'1st-place.png';
				$previous_contest_winners = $previous_contest_winners.show_intro($photo, '
					<div class="row">
						<div class="col-lg-1" style="min-width:100px;"><h1>1st</h1></div>
						<div class="col-lg-1" style="min-width:50px;min-height:50px; padding-top:15px;">place</div>
						<div class="col-lg-2" style="min-width:200px;"><h1 style="text-transform:capitalize;">'.($user_account->is_manager()?'<a href=/acc_viewuser.php?userid='.$current_contest_data['contest_winner_id'.$i].' target=_blank>'.$current_contest_data['contest_winner_name'.$i].'</a>':$current_contest_data['contest_winner_name'.$i]).'</h1></div>
						<div class="col-lg-1" style="min-width:50px;min-height:50px; padding-top:15px;">won</div>
						<div class="col-lg-1" style="min-width:100px;"><h1 style="font-weight:bold; color:#007000;">'.currency_format($current_contest_data['current_contest_1place']).'</h1></div>
						<div class="col-lg-6"></div>
					</div>
					');
			}
			else
				$previous_contest_winners = $previous_contest_winners.'Place <b>'.$i.'</b>:'.($user_account->is_manager()?'<a href=/acc_viewuser.php?userid='.$current_contest_data['contest_winner_id'.$i].' target=_blank>'.$current_contest_data['contest_winner_name'.$i].'</a>':' <b style="text-transform:capitalize;">'.$current_contest_data['contest_winner_name'.$i].'</b>').', won <b>'.currency_format(($i == 1?$current_contest_data['current_contest_1place']:($i == 2?$current_contest_data['current_contest_2place']:$current_contest_data['current_contest_3place']))).'</b><br><br>';
		}
		if ( !empty($previous_contest_winners) ) {
			$previous_contest_winners = '<h2>The winners of previous contest:</h2>'.$previous_contest_winners.'';
			file_put_contents(PREVIOUS_CONTEST_WINNERS_FILE, $previous_contest_winners);
		}
		else		
			unlink(PREVIOUS_CONTEST_WINNERS_FILE);
		$output_str = $output_str.$previous_contest_winners;
	}
	
	$rows_per_page = 10;
	$current_page_number = 1;
	$total_rows = 0;

	$row_eval = '
		if (!isset($position_number))
			$position_number = 1;
		if ( $position_number == 1 )
			$row["color"] = "'.COLOR3LIGHT.'";
		else
			$row["color"] = "'.COLOR1LIGHT.'";
		$row["c_position"] = $position_number;
		$position_number++;
		$user = new User();
		$user->userid = $row["c_userid"];
		if ( $user->read_data(false) )
			$row["c_photo"] = $user->get_photo();
		else
			$row["c_photo"] = DIR_WS_WEBSITE_IMAGES_DIR."no_photo_60x60".($row["c_gender"] == "F"?"girl":"boy").".png";
	';

	$whole_header_html = '
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
	';
	$whole_row_html = '
		<tr>
			<td style="vertical-align:middle;">
				<b>#c_position#</b>
			</td>
			<td style="vertical-align:middle;">
				<img src="#c_photo#" border="0" class="first_page_image" style="width:40px; height:40px;">
			</td>
			<td style="vertical-align:middle;">
				'.($user_account->is_manager()?'<a href=/acc_viewuser.php?userid=#c_userid# target=_blank>#c_first_name#</a>':'#c_first_name#').'
			</td>
			<td style="vertical-align:middle;">
				<img src="/images/flags/#c_country#.jpeg" border="0" title="From #c_country_name#" width="16" height="10" style="position:relative; top:-2px; left:0px;">
			</td>
			<td style="vertical-align:middle;">
				<span class="description" style="color:#'.COLOR1DARK.'">Member since:<br>
				#c_created#</span> 
			</td>
			<td style="vertical-align:middle; text-align:left; padding-left:10px;">Active Referrals: <b>#c_referrals#<b></td>
		</tr>
	';
	$row_html = array();
	$table_tag = '<table class="table table-hover" cellspacing="0" cellpadding="0" border="0" style="">';
	$out_table = '';
	
	if ( print_sorted_table(
			'referrals_contest', 
			$header, 
			NULL, 
			$row_html, 
			$rows_per_page, 
			$current_page_number, 
			$row_number, 
			$total_rows, 
			$out_table,
			'', 
			$table_tag, 
			'class=sorted_table_cell',
			'', 
			0, 
			'DESC', 
			$whole_row_html, 
			$whole_header_html,
			'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
			'', //$not_sorted_column_label_mask
			true, // $read_page_nmb_from_cookie
			$row_eval
			)
		)
	{
		$contest_users = ($total_rows > 1 || ( $total_rows > 0 && count_days(strtotime(date("Y-m-d")), strtotime($current_contest_data['current_contest_starts'])) < 10)?'<p>'.$out_table.'</p>':'');
		if ( !empty($contest_users) ) {
			file_put_contents(CONTEST_USERS_FILE, $contest_users);
		}
		else		
			unlink(CONTEST_USERS_FILE);

		$output_str = $output_str.$contest_users;
	}
	$output_str = $output_str.'
		<p style="font-size:200%; text-transform:uppercase; font-weight:bold; text-align:center; margin-top:30px;">Who wins?</p>
		<p style="text-align:center;">
		The winner will be a partner with the highest volume of purchases made by referrals. The more purchases your referrals make the bigger chance for you to be a winner.<br>
		If you already made account and you are bringing referrals to our site, there is nothing you need to do to enter contest because you are already in!<br>
		The system is already tracking all purchases from your referrals and the winners will be announced on this page on <b>'.$current_contest_data['current_contest_ends'].'</b>.<br>
		We wish you to be a winner!!!
		</p>
		';
	if ( !$user_account->is_loggedin() )
		update_file_variable($top_users_tmp_file, $output_str);
	
	echo $output_str;
}
else {
	if ( defined('DEBUG_MODE') ) echo "read from file: $top_users_tmp_file<br>";
	echo get_file_variable($top_users_tmp_file);
}
echo '
'.(!$user_account->is_loggedin()?'<button class="btn btn-lg btn-success" style="display:block; margin:40px auto;" onclick="window.location.assign(\'/signup\');">Become a Partner...</button>':'').'
';
require(DIR_COMMON_PHP.'box_message.php');
$show_frame_width = 600; 
$show_frame_height = 400; 
$show_frame_bg_color = COLOR3LIGHT;
require(DIR_COMMON_PHP.'box_show_frame.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
