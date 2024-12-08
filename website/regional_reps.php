<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'Our Representatives';
$page_title = $page_header;
require(DIR_WS_INCLUDES.'header.php');

if ( $_POST['cancel_reg_rep'] == '1' ) {
	if ( $user_account->is_loggedin() && $_POST['userid'] == $user_account->userid ) {
		$user_account->cancel_regional_rep();
	}
}
if ( is_file_variable_expired(REGIONAL_REPS_VAR_NAME, 10) || $user_account->is_loggedin() ) {
	$output_str = '';
	$country = '';
	$number_of_rows = 0;
	$reg_reps = get_api_value('get_reg_reps');
	foreach($reg_reps as $row) {
		if ( $country != $row['c_country_name'] ) {
			$country = $row['c_country_name'];
			$output_str = $output_str.'
			<tr style="height:80px;">
				<td style="padding-top:20px; padding-bottom:0px; margin-bottom:0px; text-align:center;">
					<img class="" src="/images/flags_big/'.$row['country'].'.gif" border="0" title="Regional Representatives from '.$row['c_country_name'].'" style="width:70px; height:40px;">
				</td>
				<td colspan="3" style="padding-top:22px; padding-bottom:0px; margin-bottom:0px; width:100%;">
					<h3 style="">'.$row['c_country_name'].'</h3>	
				</td>
			</tr>
			';
		}
		$user = new User();
		$user->userid = $row["userid"];
		if ( $user->read_data(false) ) {
			$row["c_photo"] = $user->get_photo();
		}
		else
			$row["c_photo"] = DIR_WS_WEBSITE_IMAGES_DIR."no_photo_60x60".($row["gender"] == "F"?"girl":"boy").".png";

		$output_str = $output_str.'
		<tr>
			<td style="border-bottom: 1px solid #'.COLOR1DARK.'; padding-top:10px; padding-bottom:10px;">
				<a href="#" onClick="return show_image_preview_box(\''.$row["c_photo"].'?tmp='.rand(10000, 20000).'\');">
					<img class="first_page_image" src="'.$row['c_photo'].'" style="width:70px; height:70px;">
				</a>
			</td>
			<td style="border-bottom: 1px solid #'.COLOR1DARK.'; padding-top:10px; padding-bottom:10px; padding-right:10px;">
				<div class="row">
					<div class="col-md-4" style="" >
						<h3 style="margin:0px; padding:0px; text-transform:capitalize;">'.($user_account->is_manager()?'<a href="/acc_viewuser.php?userid='.$row['userid'].'" target="_blank">':'').$row['firstname'].' '.$row['lastname'].($user_account->is_manager()?'</a>':'').'</h3>
						<span style="color:#'.COLOR3DARK.';">from</span> <span style="text-transform:capitalize;">'.$row['city'].'</span><br>
						<a href="'.$row['website'].'" target="_blank">'.get_domain($row['website']).'</a>
					</div>
					<div class="col-md-4" style="" >
						E-Mail: <a href="mailto:'.$row['email'].'" target="_blank"><b>'.$row['email'].'</b></a><br>
						'.(!empty($row['phone'])?'Phone: <b>'.$row['phone'].'</b><br>':'').'
						'.( $user_account->is_loggedin() && $row['userid'] == $user_account->userid?'
						<form method="get" action="/regional_rep_form.php" style="">
							<button class="btn btn-info btn-xs" style="min-height:30px;"><span class="glyphicon glyphicon-pencil" aria-hidden="true" title="Edit Your Profile"></span><span style="padding:0 20px 0 20px;">Edit Your Profile...</span></button>
						</form>
						<form method="post" style="display:inline;">
							<input type="hidden" name="userid" value="'.$user_account->userid.'">
							<input type="hidden" name="cancel_reg_rep" value="1">
							<button class="btn btn-danger btn-xs" style="min-height:30px;" onClick="return ( confirm(\'Do you really want to cancel your representation?\') );">
								<span class="glyphicon glyphicon-remove" aria-hidden="true" title="Cancel Regional Representation"></span>
								<span style="padding:0 20px 0 20px;">Cancel Regional Representation</span>
							</button>
						</form>
						':'' ).'
					</div>
					<div class="col-md-4" style="" >
						Language:<br>
						<b>'.$row['languages'].'</b>
					</div>
				</div>
			</td>
		</tr>
		';
		$number_of_rows++;
	}
	if ( !empty($output_str) ) {
		$output_str = '
		<table cellspacing="0" cellpadding="0" border="0" style="" class="table">
			'.$output_str.'
		</table>
		';
	}
	if ( !$user_account->is_loggedin() ) {
		if ( !empty($output_str) ) {
			hide_show_menu_item($_SERVER['SCRIPT_NAME'], 0);
			update_file_variable(REGIONAL_REPS_VAR_NAME, $output_str);
			echo $output_str;
		}
		else {
			hide_show_menu_item($_SERVER['SCRIPT_NAME']);
			echo get_file_variable(REGIONAL_REPS_VAR_NAME);
		}
	}
	else
		echo $output_str;
}
else {
	if ( defined('DEBUG_MODE') ) { echo 'from file $$$'.REGIONAL_REPS_VAR_NAME.'.txt:<br>'; }
	echo get_file_variable(REGIONAL_REPS_VAR_NAME);
}
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_image_preview.php');
?>
</body>
</html>
