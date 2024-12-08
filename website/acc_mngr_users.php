<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$display_in_short = isset($_GET['parentid']) && !empty($_GET['parentid']);
if (!$display_in_short)
	require_once(DIR_WS_INCLUDES.'box_protected_page.php');

$page_header = 'Users List';
$page_title = $page_header;
$page_desc = 'Search for user.';
global $ranks;
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

if ( !$display_in_short && !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}
if ( !$display_in_short ) {
	?>
	<h2><?php echo make_str_translateable('Search by:'); ?></h2>
	<form class="form-inline" action="/acc_viewuser.php" method="get">
	<div class="row">
		<div class="col-md-2" style="">
			<div class="input-group input-group-sm" style="width:100%">
				<input type="text" name="userid" id="userid" class="form-control" value="<?php echo $_GET['userid']; ?>" placeholder="User ID">
				<span class="input-group-btn"><button class="btn btn-info btn-sm" name="btn"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button></span>
			</div>
		</div>
		<div class="col-md-2" style="">
			<div class="input-group input-group-sm" style="width:100%">
				<input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo $_GET['firstname']; ?>" placeholder="First Name">
				<span class="input-group-btn"><button class="btn btn-info btn-sm" name="btn"><span class="glyphicon glyphicon-search" aria-hidden="true"></button></span>
			</div>
		</div>
		<div class="col-md-2" style="">
			<div class="input-group input-group-sm" style="width:100%">
				<input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo $_GET['lastname']; ?>" placeholder="Last Name">
				<span class="input-group-btn"><button class="btn btn-info btn-sm" name="btn"><span class="glyphicon glyphicon-search" aria-hidden="true"></button></span>
			</div>
		</div>
		<div class="col-md-2" style="">
			<div class="input-group input-group-sm" style="width:100%">
				<input type="text" name="email" id="email" class="form-control" value="<?php echo $_GET['email']; ?>" placeholder="Email">
				<span class="input-group-btn"><button class="btn btn-info btn-sm" name="btn"><span class="glyphicon glyphicon-search" aria-hidden="true"></button></span>
			</div>
		</div>
		<div class="col-md-2" style="">
			<?php if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) { ?>
			<div class="input-group input-group-sm" style="width:100%">
				<input type="text" name="paypalemail" id="paypalemail" class="form-control" value="<?php echo $_GET['paypalemail']; ?>" placeholder="Payment Email">
				<span class="input-group-btn"><button class="btn btn-info btn-sm" name="btn"><span class="glyphicon glyphicon-search" aria-hidden="true"></button></span>
			</div>
			<?php } ?>
		</div>
		<div class="col-md-2" style="">
			
		</div>
	</div>
	</form>
	<br>
	<div class="box_type1" >
		<ul class="nav nav-tabs" role="tablist" >
			<?php echo '
			<li role="presentation" class="'.(empty($_GET['recruits'])?'active':'').'"><a role="tab" href="?recruits=">'.make_str_translateable('All').'</a></li>
			<li role="presentation" class="'.($_GET['recruits'] == 'today'?'active':'').'"><a role="tab" href="?recruits=today">'.make_str_translateable('Today Recruiters').'</a></li>
			<li role="presentation" class="'.($_GET['recruits'] == 'yesterday'?'active':'').'"><a role="tab" href="?recruits=yesterday">'.make_str_translateable('Yesterday Recruiters').'</a></li>
			<li role="presentation" class="'.($_GET['recruits'] == '7day'?'active':'').'"><a role="tab" href="?recruits=7day">'.make_str_translateable('Recruiters of last 7 days').'</a></li>
			<li role="presentation" class="'.($_GET['recruits'] == 'buyers'?'active':'').'"><a role="tab" href="?recruits=buyers">'.make_str_translateable('Buyers').'</a></li>
			';
			?>
		</ul>
	<?php
}
include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !$display_in_short )
	$rows_per_page = 14;
else
	$rows_per_page = 8;

$current_page_number = 1;
$total_rows = 0;
$row_eval = '';
$managers = array();
if ( !$display_in_short ) {
	$row_eval = '
		global $managers;
		if ( $row["c_local_parent"] ) {
			if ( $row["c_parentid"] != 1 && $row["c_parentid"] != MANAGER_ID ) {
				$manager_name = "";
				foreach ( $managers as $manager => $name ) {
					if ( $manager == $row["c_parentid"] ) {
						$manager_name = $name;
						break;
					}
				}
				if ( empty($manager_name) ) {
					$tmp_user = new User();
					$tmp_user->userid = $row["c_parentid"];
					if ( $tmp_user->read_data(false) ) {
						$manager_name = $tmp_user->firstname;
						$managers[$row["c_parentid"]] = $manager_name;
					}
				}
				if ( !empty($manager_name) )
					$row["c_parentid"] = "<a href=\'/acc_viewuser.php?userid=".$row["c_parentid"]."\' target=\'_blank\' class=description style=\'margin:0;padding:0;\'><image src=/".DIR_WS_WEBSITE_PHOTOS_DIR.$row["c_parentid"].".jpg"." class=\'first_page_image user_thumbnail_hidden\' style=\'width:26px; height:26px; display:none; margin: 0 auto 0 auto;\'>".$manager_name."</a>";
			}
			else
				$row["c_parentid"] = "";
		}
		else {
			$row["c_parentid"] = "<a href=\'".$row["c_parent_website_url"]."acc_viewuser.php?userid=".$row["c_parentid"]."\' target=\'_blank\' class=description style=\'margin:0;padding:0;\'><image src=".$row["c_parent_photo"]." class=\'first_page_image user_thumbnail_hidden\' style=\'width:26px; height:26px; display:none; margin: 0 auto 0 auto;\'>".$row["c_parentid"]."</a>";
		}
		if ( $row["c_deleted"] )
			$row["c_name_style"] = $row["c_name_style"]." text-decoration:line-through;";
		if ( $row["c_suspisious"] )
			$row["c_name_style"] = $row["c_name_style"]." color:#ff0000;";
		$row["c_photo"] = (file_exists(DIR_WS_WEBSITE_PHOTOS.$row["c_userid"].".jpg")?"<image src=\'/".DIR_WS_WEBSITE_PHOTOS_DIR.$row["c_userid"].".jpg\' class=\'first_page_image\' style=\'width:26px; height:26px; display:inline; margin:0 0 0 10px;\'>":"");
		';
	
	$whole_header_html = '
		<tr>
			<td style="width:50%;">
				<div class="row">
					<div class="col-md-3" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>'.make_str_translateable('Name').'</strong></a>#sort_label0#</div>
					<div class="col-md-1" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>'./*make_str_translateable('Rank').*/'</strong></a>#sort_label1#</div>
					<div class="col-md-2" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>'./*make_str_translateable('Country').*/'</strong></a>#sort_label2#</div>
					<div class="col-md-2" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>'.make_str_translateable('Date of').'</strong></a>#sort_label3#</div>
					<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(4);"><strong>'.make_str_translateable('Clicks').'</strong></a>#sort_label4#</div>
					<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(5);"><strong>'.make_str_translateable('Descendants').'</strong></a>#sort_label5#</div>
				</div>
			</td>
			<td style="width:50%;">
				<div class="row">
					'./*( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ? */
						'<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(6);"><strong>'.make_str_translateable('Revenue from descendants').'</strong></a>#sort_label6#</div>'
						/*: ''
					)*/.'
					<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(7);"><strong>'.make_str_translateable('Balance').'</strong></a>#sort_label7#</div>
					<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(8);"><strong>'.make_str_translateable('Purchases').'</strong></a>#sort_label8#</div>
					<div class="col-md-2" style="text-align:right;"><a class=sorted_table_link href="javascript: RedrawWithSort(9);"><strong>'.make_str_translateable('Payouts').'</strong></a>#sort_label9#</div>
					<div class="col-md-2" style="text-align:center;"><a class=sorted_table_link href="javascript: RedrawWithSort(10);"><strong>'.make_str_translateable('Recruiter').'</strong></a>#sort_label10#</div>
					<div class="col-md-2" style="text-align:left;"><a class=sorted_table_link href="javascript: RedrawWithSort(11);"><strong>'.make_str_translateable('From site').'</strong></a>#sort_label11#</div>
				</div>
			</td>
		</tr>
	';
	$header = '';
	
	$whole_row_html = '
		<tr class="notranslate">
			<td style="">
				<div class="row">
					<div class="col-md-3 description" style="text-align:left;"><a href="/acc_viewuser.php?userid=#c_userid#" target="_blank" style="#c_name_style#">#c_name#</a></div>
					<div class="col-md-1" style="text-align:left;">#c_rank#</div>
					<div class="col-md-2" style="text-align:left;">#c_country##c_photo#</div>
					<div class="col-md-2 description" style="text-align:left;"><span class="local_time_date_next_hour" unix_time="#c_unix_created#">#c_created_date#<br> <span style="color:#'.COLOR1BASE.';">#c_created_time#</span></span></div>
					<div class="col-md-2" style="text-align:right;">#c_stat_clicks#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_childs#</div>
				</div>
			</td>
			<td style="">
				<div class="row">
					<div class="col-md-2" style="text-align:right;">#c_stat_revenue_from_childs#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_balance#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_purchases#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_payouts#</div>
					<div class="col-md-2" style="text-align:center;">#c_parentid#</div>
					<div class="col-md-2" style="text-align:left;">#c_born_domain#</div>
				</div>
			</td>
		</tr>
	';
	
	$row_html = '';
}
else {
	$whole_header_html = '
		<tr>
			<td style="width:50%;">
				<div class="row">
					<div class="col-md-4" style="text-align:left;"><a href="javascript: RedrawWithSort(0);"><strong>'.make_str_translateable('Name').'</strong></a>#sort_label0#</div>
					<div class="col-md-2" style="text-align:left;"><a href="javascript: RedrawWithSort(1);"><strong>'.make_str_translateable('Last Login').'</strong></a>#sort_label1#</div>
					<div class="col-md-2" style="text-align:left;"><a href="javascript: RedrawWithSort(2);"><strong>'.make_str_translateable('Country').'</strong></a>#sort_label2#</div>
					<div class="col-md-2" style="text-align:left;"><a href="javascript: RedrawWithSort(3);"><strong>'.make_str_translateable('Member Since').'</strong></a>#sort_label3#</div>
					<div class="col-md-2" style="text-align:right;"><a href="javascript: RedrawWithSort(4);"><strong>'.make_str_translateable('Clicks').'</strong></a>#sort_label4#</div>
				</div>
			</td>
			<td style="width:50%;">
				<div class="row">
					<div class="col-md-2" style="text-align:right;"><a href="javascript: RedrawWithSort(5);"><strong>'.make_str_translateable('Purchases').'</strong></a>#sort_label5#</div>
					<div class="col-md-2" style="text-align:right;"><a href="javascript: RedrawWithSort(6);"><strong>'.make_str_translateable('Children').'</strong></a>#sort_label6#</div>
					<div class="col-md-2" style="text-align:right;"><a href="javascript: RedrawWithSort(7);"><strong>'.make_str_translateable('Active Children').'</strong></a>#sort_label7#</div>
					<div class="col-md-2" style="text-align:right;"><a href="javascript: RedrawWithSort(8);"><strong>'.make_str_translateable('Grand Children').'</strong></a>#sort_label8#</div>
					<div class="col-md-4" style="text-align:right;"><a href="javascript: RedrawWithSort(9);"><strong>'.make_str_translateable('Balance').'</strong></a>#sort_label9#</div>
				</div>
			</td>
		</tr>
	';
	$header = '';
	
	$whole_row_html = '
		<tr class="notranslate">
			<td style="">
				<div class="row">
					<div class="col-md-4" style="text-align:center;">
						<a href="#c_user_website_url#/acc_viewuser.php?userid=#c_userid#" target="_blank"><img src="#c_photo#" class="first_page_image user_thumbnail" style="width:40px; height:40px; margin:0 auto 0 auto;">
						<span class="description" style="padding:0px; #c_name_style#">#c_name#</span><br>
						<span class="description" style="padding:0px;">#c_user_websiteid#</span>
						</a>
					</div>
					<div class="col-md-2 description" style="text-align:left;">#last_login_local_time_tag#</div>
					<div class="col-md-2" style="text-align:left;">#c_stat_country_last_login#</div>
					<div class="col-md-2 description" style="text-align:left;"><span class="local_time_date_next_hour" unix_time="#c_unix_created#">#c_created#</span></div>
					<div class="col-md-2" style="text-align:right;">#c_stat_clicks#</div>
				</div>
			</td>
			<td style="">
				<div class="row">
					<div class="col-md-2" style="text-align:right;">#c_stat_purchases#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_childs#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_active_childs#</div>
					<div class="col-md-2" style="text-align:right;">#c_stat_active_grandchilds#</div>
					<div class="col-md-4" style="text-align:right;">#c_stat_balance#</div>
				</div>
			</td>
		</tr>
	';
	$row_eval = '
		if ( !empty($row["c_unix_last_login"]) )
			$row["last_login_local_time_tag"] = "<span class=local_time_date_next_hour unix_time=".$row["c_unix_last_login"].">".$row["c_last_login"]."</span>";
		else
			$row["last_login_local_time_tag"] = "";
		
	';
}
$table_tag = '<table class="table table-striped">';
$output_str = '';
$_GET['display_in_short'] = $display_in_short;
if ( print_sorted_table(
		'admin_users_list', 
		$header, 
		$_GET, 
		$row_html, 
		$rows_per_page, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', 
		$table_tag, 
		'',
		'', 
		3, 
		'DESC', 
		$whole_row_html, 
		$whole_header_html,
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
		'', // $not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval,
		NULL, // $data_array
		'', // $whole_footer_html
		false, // $always_use_default_sort
		0, // $number_of_grid_columns
		'<div class="row">', // $grid_row_prefix
		'</div>', // $grid_row_suffix
		$sort_column,
		$sort_order,
		null // $user
		)
	)
{
	if ( !$display_in_short )
		echo $output_str.'</div>';
	else
		echo $output_str;
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
}
else {
	echo '</div><p class=account_paragraph><strong>'.make_str_translateable('No users yet').'</strong></p>';
}
if ( !$display_in_short ) {
	require(DIR_WS_INCLUDES.'footer.php');
}
?>

</body>
</html>
