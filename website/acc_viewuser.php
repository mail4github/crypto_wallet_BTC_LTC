<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

if ( $user_account->permission_rank < PERMISSION_MANAGER_MIN_RANK ) {
	if ( !$user_account->has_permission(PERMISSION_MANAGER) ) {
		header('Location: /');
		exit;
	}
}

require_once(DIR_WS_INCLUDES.'box_protected_page.php');

if ( !empty($_POST['test_sales_manager']) ) {
	$make_post_request = true;
	if (!empty($_POST['email'])) {
		$req = $user_account->get_request_to_add_funds(1, 'paypal', 'Add funds', $make_post_request, ADD_FUNDS_PREFIX, $_POST['email'], DOLLAR_NAME, DOLLAR_SIGN, '', 0, 1);
		if ( !$make_post_request )
			header('Location: '.$req);
		exit();
	}
	else
		$box_message = 'Error: empty email.';
}


function draw_frame($category, $title, $webpage, $image = '')
{
	return '
	<div class="dropdown" style="display:inline;" id="drop_down_'.$category.'">
	  <button type="button" class="btn btn-info" style="min-width:300px; max-width:300px;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="show_hide_frame(\''.$category.'\', \'/'.$webpage.'\');">
		<table><tr>
			<td style="width:100%; text-transform:capitalize;">'.$title.'<span class="caret" style="margin-left:10px;"></span></td>
			<td>'.(!empty($image)?'<img src="/images/'.$image.'" border="0" alt="" style="width:20px; height:20px;">':'&nbsp;').'</td>
		</tr></table>
	  </button>
	</div>
	<div style="display:none;" id="div_'.$category.'">
		<iframe frameborder="0" class="iframe_page" style="margin:0; height:800px;" src="/acc_banner_code_wait.html" id="frame_'.$category.'"></iframe>
	</div>
	<div id="div_'.$category.'_line">
		<iframe frameborder="0" class="iframe_page" height="2"></iframe>
	</div>
	';
}

function draw_switch($input_name_yes, $input_name_no, $value, $yes_value_label, $yes_value_confirm, $yes_value_button, $no_value_label, $no_value_confirm, $no_value_button, $user, $yes_is_danger = true, $max_width = 200, $margin_right = 10)
{
	if ( !$yes_is_danger ) {
		$s = $yes_value_label; $yes_value_label = $no_value_label; $no_value_label = $s;
		$s = $yes_value_confirm; $yes_value_confirm = $no_value_confirm; $no_value_confirm = $s;
		$s = $yes_value_button; $yes_value_button = $no_value_button; $no_value_button = $s;
	}
	if ( $yes_is_danger )
		$value_for_display = $value;
	else
		$value_for_display = !$value;
	if ( $value_for_display && empty($yes_value_button) )
		return '';
	if ( !$value_for_display && empty($no_value_button) )
		return '';
	return '
	<form method="post" style="display:inline-block; max-width:1000px; margin-right:'.$margin_right.'px;">
		<input type="hidden" name="userid" value="'.$user->userid.
		'">'.($value?'<input type="hidden" name="'.$input_name_yes.'" value="'.($input_name_yes == $input_name_no?'0':'1').'">':'<input type="hidden" name="'.$input_name_no.'" value="'.($input_name_yes == $input_name_no?'1':'1').'">').''.
		( $value_for_display ?
			'
			<div class="input-group input-group-sm" style="">
				<label class="form-control" style="background-color:transparent;color:#a94442; border-color:#dca7a7; width:'.$max_width.'px; white-space:nowrap;">'.$yes_value_label.'</label>
				<span class="input-group-btn">
					<button class="btn btn-success btn-sm" '.(!empty($yes_value_confirm)?'onClick="return ( confirm(`'.$yes_value_confirm.'`) );"':'').'>'.$yes_value_button.'</button>
				</span>
			</div>'
			:'
			'.(!empty($no_value_label)?'
				<div class="input-group input-group-sm" style="">
					<label class="form-control" style="background-color:transparent; color:#3c763d; border-color:#b2dba1; width:'.$max_width.'px; white-space:nowrap;">'.$no_value_label.'</label>
					<span class="input-group-btn">
						<button class="btn btn-danger btn-sm" '.(!empty($no_value_confirm)?'onClick="return ( confirm(`'.$no_value_confirm.'`) );"':'').'>'.$no_value_button.'</button>
					</span>
				</div>'
				:'<button class="btn btn-danger btn-xs" '.(!empty($no_value_confirm)?'onClick="return ( confirm(`'.$no_value_confirm.'`) );"':'').'>'.$no_value_button.'</button>'
			).'
			'
		).'
	</form>';
}
$display_in_short = !empty($_GET['quick_stats']);

$page_header = 'User Information';
$page_title = $page_header;
$page_desc = $page_header;

$user = new User();
$user->userid = tep_sanitize_string($_GET['userid']);
$user->email = tep_sanitize_string($_GET['email']);
$user->firstname = tep_sanitize_string($_GET['firstname']);
$user->lastname = tep_sanitize_string($_GET['lastname']);
$user->paypalemail = tep_sanitize_string($_GET['paypalemail']);

if ( empty($user->userid) ) {
	$search_res = get_api_value('user_is_search_has_many_results', '', array('email' => '%'.$user->email.'%', 'firstname' => '%'.$user->firstname.'%', 'lastname' => '%'.$user->lastname.'%', 'paypalemail' => '%'.$user->paypalemail.'%'));
	if ( $search_res['multiple_found'] > 1 ) {
		header('Location: /acc_mngr_users.php?firstname='.$user->firstname.'&lastname='.$user->lastname.'&email='.$user->email.'&paypalemail='.$user->paypalemail);
		exit;
	}
	else
		$user->userid = $search_res['found_userid'];
}
if ( $display_in_short )
	$_GET['noheader'] = 1;
$page_header = '';
require(DIR_WS_INCLUDES.'header.php');

if ( !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}

include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !$user->read_data(false, true) || $user->no_permission_to_this_user ) {
	echo '<h2>User not found</h2><br><br>';
}
else {
	//var_dump($user);
	echo "<h1>".make_str_translateable('User')." {$user->userid}</h1>";
	if ( !empty($_POST['restore_user']) ) {
		$user->restore_account();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['new_parentid']) ) {
		$user->change_parent($_POST['new_parentid']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['disable_user']) ) {
		$user->disable_enable_account('1');
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['enable_user']) ) {
		$user->disable_enable_account('0');
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['send_message']) ) {
		$res = $user->send_email('message_from_manager', 
			'_x0emailtext='.bin2hex( str_replace( "\r\n", '<br>', $_POST['message'] ) ).'&'.
			'sender_name='.$user_account->full_name().'&'.
			'sender_title='.$user_account->positiontitle.'&',
			$user_account->userid);
		if ( $res === true || empty($res) )
			$box_message = 'Message has been sent.';
		else
			$box_message = 'Error: Message not sent. '.$res;
	}
	else
	if ( !empty($_POST['rank_increase']) ) {
		$user->increase_rank();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['rank_decrease']) ) {
		$user->decrease_rank();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['hold_rank']) ) {
		$user->hold_rank();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['unhold_rank']) ) {
		$user->unhold_rank();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['set_upgrade_expires']) ) {
		$_POST['upgrade_expires'] = date("Y-m-d 00:00:00", strtotime($_POST['upgrade_expires']));
		$user->set_upgrade_expires($_POST['upgrade_expires']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['cancel_regional_rep']) ) {
		$user->cancel_regional_rep(1);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['withdrawal_enable']) ) {
		$user->withdrawal_disable(0);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['withdrawal_disable']) ) {
		$user->withdrawal_disable(1);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_country']) ) {
		$user->update_country($_POST['country']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['make_paypalemail_not_confirmed']) ) {
		$user->confirm_paypal_email($user->paypalemail, 0);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['make_paypalemail_confirmed']) ) {
		$user->confirm_paypal_email($user->paypalemail, 1);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_name']) ) {
		$box_message = $user->update($_POST['firstname'], $_POST['lastname'], $user->country);
		if (!empty($box_message))
			$box_message = 'Error: '.$box_message;
		else
			$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_note']) ) {
		$user->change_note($_POST['note']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['block_trouble_tickets']) ) {
		$user->block_trouble_tickets(1);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['enable_trouble_tickets']) ) {
		$user->block_trouble_tickets(0);
		$user->refresh_data();
	}
	else
	if ( isset($_POST['purchases_disabled']) ) {
		$user->set_purchases_disabled($_POST['purchases_disabled']);
		$user->refresh_data();
	}
	else
	if ( isset($_POST['disabled_shares_issue']) ) {
		$user->set_disabled_shares_issue($_POST['disabled_shares_issue']);
		$user->refresh_data();
	}
	else
	if ( isset($_POST['set_dontdelete']) ) {
		$user->set_dontdelete($_POST['set_dontdelete']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['remove_from_blacklist']) ) {
		$user->remove_from_blacklist();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['set_max_rank']) ) {
		$user->set_max_rank($_POST['max_rank']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_payoutoption_and_email']) ) {
		$box_message = $user->update_paypalemail($_POST['paypalemail'], $_POST['payoutoption'], 1);
		if (!empty($box_message))
			$box_message = 'Error: '.$box_message;
		else
			$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_email']) ) {
		$box_message = $user->update_email($_POST['email']);
		if (!empty($box_message))
			$box_message = 'Error: '.$box_message;
		else
			$user->refresh_data();
	}
	else
	if ( !empty($_POST['change_password']) ) {
		//echo 'password_new: "'.$user->password_new.'"'; exit;
		$box_message = $user->update_password(md5($_POST['new_password']));
		if (!empty($box_message))
			$box_message = 'Error: '.$box_message;
	}
	else
	if ( !empty($_POST['clear_sec_question']) ) {
		$user->clear_sec_question();
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['user_reward']) ) {
		$user->reward($_POST['amount_in_usd'], $_POST['description'], $_POST['crypto_name']);
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['make_user_from_hacker_country']) ) {
		$res = $user->make_user_from_hacker_country('');
		$user->refresh_data();
	}
	else
	if ( !empty($_POST['restore_country_register']) ) {
		$res = $user->make_user_from_hacker_country($user->stat_country_last_login);
		$user->refresh_data();
	}
	?>
	<div class="row" style="">
		<div class="col-md-10" style="padding:0;">
			<?php
			echo '
			<form class="form-horizontal row" method="post" action="" style="">
				<input type="hidden" name="userid" value="'.$user->userid.'">
				<input type="hidden" name="change_name" value="1">
				<div class="col-md-6">
					<input type="text" name="firstname" class="form-control input-sm notranslate" value="'.$user->firstname.'" style="">
				</div>
				<div class="col-md-6">
					<div class="input-group input-group-sm">
						<input type="text" name="lastname" class="form-control notranslate" value="'.$user->lastname.'" style="">
						<span class="input-group-btn">
							<button class="btn btn-info" name="note_btn" style="">'.make_str_translateable('Change Name').'</button>
						</span>
					</div>
				</div>
			</form>
			<form class="form-horizontal row" method="post" name="note_frm" action="" style="margin-top:10px;">
				<input type="hidden" name="userid" value="'.$user->userid.'">
				<input type="hidden" name="change_note" value="1">
				<div class="col-md-12">
					<div class="input-group input-group-sm">
						<input type="text" name="note" class="form-control" value="'.$user->note.'" placeholder="admin&apos;s note">
						<span class="input-group-btn">
							<button class="btn btn-info" name="note_btn" style="">'.make_str_translateable('Change note').'</button>
						</span>
					</div>
				</div>
			</form>
			';
			if ($user->stat_relative_to_somebody)
				echo make_str_translateable('Has same hardware like user:').' <a href="/acc_viewuser.php?userid='.$user->stat_relative_to_somebody.'">'.$user->stat_relative_to_somebody.'</a>';
			if ($user->stats_suspisious) {
				$res = get_api_value('user_check_suspisious', '', array('userid' => $user->userid));
				echo '<span style="color:#a00000; margin-left:20px;">'.make_str_translateable('Suspisious:').' '.round($res['suspisious'] * 100).'% ('.make_str_translateable($res['reason']).')</span>';
			}
			?>
			<table class="table" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td style="width:200px;">
					<?php 
					if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) {
						echo 'Status:<br><strong>'; 
						if ( is_integer(strpos($user->permissions, PERMISSION_GENERAL_MANAGER)) )
							echo 'General Manager'; 
						else
						if ( is_integer(strpos($user->permissions, PERMISSION_MANAGER)) )
							echo 'Manager'; 
						else
						if ( is_integer(strpos($user->permissions, PERMISSION_AUDIT)) )
							echo 'Auditor';
						else
						if ( is_integer(strpos($user->permissions, PERMISSION_REGIONAL_REP)) )
							echo 'Regional Rep.'; 
						else
						if ( is_integer(strpos($user->permissions, PERMISSION_USER)) )
							echo 'User'; 
						echo '
						</strong>
						<div style="position:relative; top:0px; width:40px; height:10px; display:inline-block;">
							<div style="position:absolute; width:0px; height:0px;">
								<img src="'.$user->get_rank_image().'" border="0" title="'.$user->get_upgrade_name().'" style="position:relative; left:10px; top:-5px;"
								onmouseover = "getElementsByClassName_PY(this.parentNode, \'more_info_hint\')[0].style.display = \'inline\';"
								onmouseout = "getElementsByClassName_PY(this.parentNode, \'more_info_hint\')[0].style.display = \'none\';"
								>
								<span class="more_info_hint" style="width:300px;">
									Rank <strong>"'.ucfirst($ranks[$user->rank]['name']).'"</strong>. 
									<ul>
										<li><strong'.($ranks[$user->rank]['can_buy_shares']?' style="color:#008800;">Can':' style="color:#ee0000;">Cannot').'</strong> buy shares.</li>
										<li><strong'.($ranks[$user->rank]['sell_shares']?' style="color:#008800;">Can':' style="color:#ee0000;">Cannot').'</strong> sell shares.</li>
										<li><strong'.($ranks[$user->rank]['buy_from_balance']?' style="color:#008800;">Can':' style="color:#ee0000;">Cannot').'</strong> buy from balance.</li>
										<li>Frequency of withdrawal: once in <strong>'.($ranks[$user->rank]['cashout'] * WITHDRAW_INTERVAL_IN_MINUTES).'</strong> minutes ('.round($ranks[$user->rank]['cashout'] * WITHDRAW_INTERVAL_IN_MINUTES / 60).' hours).</li>
										<li>Minimum withdrawal: <strong>'.currency_format($ranks[$user->rank]['min_cashout'], '', '', '', false, false, '', '', true).'</strong></li>
										<li>Maximum withdrawal: <strong>'.currency_format($ranks[$user->rank]['max_cashout'], '', '', '', false, false, '', '', true).'</strong></li>
										<li>Withdrawal fee: <strong>'.number_format($user->get_rank_value('withdrawal_fee')*100, 0).'%</strong></li>
										
									</ul>
								<span class="hint-pointer">&nbsp;</span></span>
							</div>
						</div>
						'.($user->account_type != 'B'?'
						<div class="panel panel-danger" style="display: inline-block;" title="Rank is locked"><div class="panel-body panel-success" style="padding:6px;"><span class="glyphicon glyphicon-lock" aria-hidden="true" style="color:#880000;"></span></div></div>':'').'
						';
						echo '<br><span style="color:#008800;">'.$user->stat_childs.'</span> referrals<br><span style="color:#aa0000; font-weight:bold;">'.$user->stat_child_relatives.'</span> of them are relatives'; 
						echo '<br>Revenue: <font color="#ff0000">'.currency_format($user->stat_revenue_from_childs, '', 'font-weight:bold; color:#ff0000;', 'font-weight:bold; color:#008800;', false, false, '', '', true).'</font>';
						$have_unpaid_loan = $user->common_param_have_unpaid_loan;
						if ( $have_unpaid_loan )
							echo '<br>Loan: <font color="#ff0000">'.currency_format($have_unpaid_loan, '', 'font-weight:bold; color:#ff0000;', 'font-weight:bold; color:#008800;', false, false, '', '', true).'</font>';
					}
					?>
				</td>
				<td>
					<?php 
					$list_of_max_ranks = '';
					for ($i = 1; $i < count($ranks); $i++) 
						$list_of_max_ranks = $list_of_max_ranks.'<option value="'.$i.'" '.($user->max_rank == $i?'SELECTED':'').'>'.$ranks[$i]['name'].'</option>';
					echo 
					draw_switch('restore_user', '', $user->deleted, make_str_translateable('Deleted'), 'Do you really want to restore this user?', make_str_translateable('Restore User'), '', '', '', $user).
					draw_switch('enable_user', 'disable_user', $user->disabled, make_str_translateable('Turned off'), 'Do you really want to enable this user?', make_str_translateable('Enable User'), make_str_translateable('Active'), 'Do you really want to disable this user?', make_str_translateable('Disable User'), $user, true, 100).
					( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ?
						draw_switch('purchases_disabled', 'purchases_disabled', $user->purchases_disabled, 'Cannot purchase via Paypal', 'Do you really want to enable purchases via Paypal?', 'Enable', 'Can use Paypal', 'Do you really want to disable purchases via Paypal?', 'Disable', $user, true, 180).
						draw_switch('disabled_shares_issue', 'disabled_shares_issue', $user->disabled_shares_issue, 'Cannot propose new '.DAO_NAME.'s', 'Do you really want to enable '.DAO_NAME.'s proposing?', 'Enable', 'Can propose new '.DAO_NAME.'s', 'Do you really want to disable '.DAO_NAME.'s proposing?', 'Disable', $user, true, 180).
						draw_switch('restore_country_register', 'make_user_from_hacker_country', $user->is_user_from_country(), 'From hacker country', 'Do you really want to restore registration country?', 'Restore Original Country', 'Country ok', 'Do you really want to make this user from Hacker Country?', 'Make User from Hacker Country', $user, true, 150)
						: ''
					).
					( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ?
						'<br>
						<div class="row" style="margin:0;">
							<div class="col-md-2" style="padding:0;">
								<form method="post" style="display:inline; margin-right:0px;"><input type="hidden" name="userid" value="'.$user->userid.'"><input type="hidden" name="rank_increase" value="1"><button class="btn-success btn-xs" onClick=""><span class="glyphicon glyphicon-chevron-up" aria-hidden="true"></span> Increase Rank</button></form>
							</div>
							<div class="col-md-2" style="padding:0;">
								<form method="post" style="display:inline; margin-right:0px;"><input type="hidden" name="userid" value="'.$user->userid.'"><input type="hidden" name="rank_decrease" value="1"><button class="btn-warning btn-xs" onClick=""><span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span> Decrease Rank</button></form>
							</div>
							<div class="col-md-4" style="padding:0;">
								<form method="post" style="width:200px; vertical-align:top; margin:-3px 20px 0 0; display:inline-block;">
									<input type="hidden" name="userid" value="'.$user->userid.'">
									<input type="hidden" name="set_max_rank" value="1">
									<div class="input-group input-group-sm" style="">
										<span style="display:table-cell; font-size:10px; vertical-align:top; padding-top:8px;">Max. Rank:</span>
										<select name="max_rank" id="max_rank" class="form-control">
											<option value="0">Not Set</option>
											'.$list_of_max_ranks.'
										</select>
										<span class="input-group-btn">
											<button class="btn btn-success" title="Save"><span class="glyphicon glyphicon-save" aria-hidden="true"></span></button>
										</span>
									</div>
								</form>
							</div>
							<div class="col-md-4" style="padding:0;">
								'.( $user->is_regional_rep()?
									'<form method="post" style="display:inline; margin-right:0px;"><input type="hidden" name="userid" value="'.$user->userid.
									'"><input type="hidden" name="cancel_regional_rep" value="1"><button class="btn-danger btn-xs" onClick="return ( confirm(\'Do you really want to cancel this regional representative?\') );">Cancel Regional Representative</button></form>'
									:''
								).'
							</div>
						</div>
						<br>'.
						draw_switch('hold_rank', 'unhold_rank', $user->account_type == 'B', 'Rank is Automatically Changed', 'Do you really want to prevent the rank to be dynamically changed?', '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span>&nbsp;Lock Rank', 'Rank is locked', '', 'Unlock Rank', $user, false, 190)
						: ''
					).'
					'.
					draw_switch('set_dontdelete', 'set_dontdelete', $user->dontdelete, make_str_translateable('He cannot be deleted'), 'Do you really want to allow this user to be deleted?', make_str_translateable('Allow to Delete'), make_str_translateable('He can be deleted'), 'Do you really want to prevent this user from deletion?', make_str_translateable('Make undeletable'), $user, false, 150)
					;
					echo '
					<form method="post" style="display:'.($user->account_type == 'B'?'none':'block').'; width:280px; vertical-align:-webkit-baseline-middle; margin-top:-10px;">
						<span class="description" style="">Rank locking expires:</span>
						<input type="hidden" name="userid" value="'.$user->userid.'"><input type="hidden" name="set_upgrade_expires" value="1">
						<div class="input-group input-group-sm" >
							<input type="date" name="upgrade_expires" class="form-control input-sm" value="'.(empty($user->upgrade_expires)?'never':date("Y-m-d", strtotime($user->upgrade_expires))).'" style="">
							<span class="input-group-btn">
								<button class="btn btn-success" title="Save"><span class="glyphicon glyphicon-save" aria-hidden="true"></span></button>
							</span>
						</div>
					</form>
					';
					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo make_str_translateable('Joined:'); ?>
					<?php 
						$date = DateTime::createFromFormat('Y-m-d H:i:s', $user->created);
						echo '<strong class="local_time_date_next_hour" unix_time="'.$date->getTimestamp().'">'.$date->format("j M Y").'</strong>';
					?>
				</td>
				<td >
					<?php echo make_str_translateable('Last Login:'); ?>
					<?php 
						$s = $user->common_param_last_login;
						if ( !empty($s) ) {
							$date = DateTime::createFromFormat('Y-m-d H:i:s', $s);
							echo '<strong class="local_pline_date_and_hour" unix_time="'.$date->getTimestamp().'">'.$date->format("j M Y H:i").'</strong>';
						}
						else
							echo '<strong>'.make_str_translateable('never logged in').'</strong>';
					?>
					<?php
					if ( !empty($user->stat_country_last_login) )
						echo ' '.make_str_translateable('from country:').' <strong>'.$user->common_param_last_login_country_name.'</strong>'; 
					?>
					<?php 
					if ( !empty($user->born_domain) )
						echo '<br>'.make_str_translateable('came from:').' <a href="http://'.$user->born_domain.'" target="_blank">'.$user->born_domain.'</a>';
					echo ', '.make_str_translateable('first time came from the IP address:').' '.long2ip($user->born_ip);
					?>
				</td>
			</tr>
			<tr>
				<td><?php echo make_str_translateable('Address:'); ?></td>
				<td style="vertical-align:middle;">
				<?php 
					echo '<b style="vertical-align:super;">'.(!empty($user->address)?$user->address.', ':'').' '.(!empty($user->city)?$user->city.', ':'').' '.(!empty($user->state)?$user->state.', ':'').' '.(!empty($user->zip)?$user->zip.' ':'').'</b>
					<form method="post" style="display:inline-flex; vertical-align:sub;">
						<input type="hidden" name="change_country" value="1">
						<input type="hidden" name="userid" value="'.$user->userid.'">
						<div class="input-group input-group-sm" style="width:200px;">
							<select name="country" class="form-control">
							';
							$countries_text = file_get_contents(DIR_WS_TEMP.'countries.txt');
							$countries_tmp = preg_split('/$\R?^/m', $countries_text);
							foreach ($countries_tmp as $value) {
								if ( !empty($value) ) {
									$cntry_arr = explode("=", $value);
									echo '<option value="'.$cntry_arr[0].'" '.($user->country == $cntry_arr[0]?' SELECTED ':'').'>'.$cntry_arr[1].'</option>'."\r\n";
								}
							}
							echo '
							</select>
							<span class="input-group-btn">
								<button class="btn btn-info" style=""><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span></button>
							</span>
						</div>
					</form>
					';
				?>
				<span style="padding-left:20px; padding-right:10px; vertical-align:super;"><?php echo make_str_translateable('Phone:'); ?></span><b style="vertical-align:super;" class="notranslate"><?php echo $user->phone; ?></b>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo make_str_translateable('Email:'); ?>
				</td>
				<td>
				<?php
				echo '
				<form method="post" style="display:inline-block; margin:0px; max-width:400px; vertical-align:top;">
					<input type="hidden" name="change_email" value="1">
					<input type="hidden" name="userid" value="'.$user->userid.'">
					<div class="input-group input-group-sm" style="max-width:400px; min-width:300px;">
						<input type="text" name="email" class="form-control" value="'.$user->email.'">
						<span class="input-group-btn">
							<button class="btn btn-info" title="Save" onClick="return ( confirm(`Do you really want to change email?`) );"><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span></button>
						</span>
					</div>
				</form>
				'.
				( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ?
					draw_switch('enable_trouble_tickets', 'block_trouble_tickets', $user->trouble_tickets_blocked, 'Tickets from this user are hidden', 'Do you really want to show trouble tickets from this user?', 'Show Tickets', '', 'Do you really want to never see trouble tickets from this user?', 'Hide Trouble Tickets from this user', $user)
					: ''
				);
				?>
				</td>
			</tr>
			<tr>
				<td><?php echo make_str_translateable('Password:'); ?></td>
				<td>
					<button class="btn btn-info btn-xs" onclick="show_password('new_password', 'change_password_submit();');"><?php echo make_str_translateable('Change Password...'); ?></button>
					<form method="post" id="change_password_frm" style="display:inline-block;">
					<input type="hidden" name="userid" value="<?php echo $user->userid; ?>">
					<input type="hidden" name="change_password" value="1">
					<input type="hidden" name="new_password" id="new_password" value="">
					</form>
					<SCRIPT language="JavaScript">
						function change_password_submit() {
							document.getElementById("change_password_frm").submit();
						}
					</SCRIPT>
					<form method="post" style="display:inline; margin-right:0px;"><input type="hidden" name="userid" value="<?php echo $user->userid; ?>"><input type="hidden" name="clear_sec_question" value="1"><button class="btn-success btn-xs" style="vertical-align:top;" onClick="return (confirm('Do you really want to clear security question?'));"><?php echo make_str_translateable('Clear Security Question'); ?></button></form>
					<button class="btn-link btn-xs" style="vertical-align:top;" onClick="return pop_up_logins_table();"><?php echo make_str_translateable('Logins...'); ?></button>
				</td>
			</tr>
			<?php if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) { ?>
			<tr>
				<td>
				Recruiter:
				</td>
				<td>
					<form method="post" style="margin:0;" class="row form-horizontal" >
						<input type="hidden" name="userid" value="<?php echo $user->userid; ?>">
						<label class="col-md-6 control-label" style="text-align:left; padding:0;">
							<strong>
								<?php 
								if ( $user->parentid > 0 && $user->parentid != $user->userid ) {
									echo '<a href="/acc_viewuser.php?userid='.$user->parentid.'">'.$user->common_param_parents_full_name.'</a>';
									if ( $user->common_param_is_relative_to_parent ) 
										echo '&nbsp;&nbsp;<font color="#ff0000">relatives</font>';
								}
								else
									echo 'No Manager';
								?>
							</strong>
						</label>
						<div class="col-md-6" style="padding: 0 20px 0 0;">
							<div class="input-group input-group-sm">
								<input type="text" name="new_parentid" style="" value="<?php echo $user->parentid; ?>" class="form-control">
								<span class="input-group-btn">
									<button class="btn btn-info" onClick="return ( confirm('Do you really want to change manager?') );">Change</button>
								</span>
							</div>
						</div>
					</form>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td>
					<?php echo make_str_translateable('Balance:'); ?>
				</td>
				<td id="user_all_currencies_balance_body" class="nottranslate">
				<?php
				$user_balance = $user->common_param_calculated_balance;
				echo '<b>'.currency_format($user_balance, '', '', '', false, false, '', '', true).'</b>
				<span style="padding-left:30px;">available to withdraw: <b>'.currency_format($user->common_param_available_funds, '', '', '', false, false, '', '', true).'</b></span>
				';
				?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo make_str_translateable('Purchases:'); ?>
				</td>
				<td>
					<b><?php echo currency_format($user->common_param_total_purchases, '', '', '', false, false, '', '', true); ?></b> <span style="padding-left:20px; padding-right:10px;"><?php echo make_str_translateable('Payouts:'); ?></span><b><?php echo currency_format($user->common_param_total_payouts, '', '', '', false, false, '', '', true); ?></b>
				</td>
			</tr>
			<?php if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) { ?>
			<tr>
				<td>
					Payment Method: 
				</td>
				<td>
					<?php
					$select_options = '<option value="" selected>Not Selected</option>';
					$i = 0;
					foreach ( $payout_options as $value ) {
							$select_options = $select_options.'<option value="'.$value['id'].'" '.($user->payoutoption == $value['id']?' selected ':'').'>'.$value['name'].'</option>'."/r/n";
							$i++;
					}
					echo '
					<div class="row" style="margin:0;">
						<form method="post" style="display:inline; margin:0px;">
							<input type="hidden" name="change_payoutoption_and_email" value="1">
							<input type="hidden" name="userid" value="'.$user->userid.'">
							<div class="col-md-2" style="padding: 0 20px 0 0;">
								<select name="payoutoption" onChange="show_payment_data(this.selectedIndex);" class="form-control input-sm" style="">
									'.$select_options.'
								</select>
							</div>
							<div class="col-md-6" style="padding-left:0;">
								<div class="input-group input-group-sm">
									<input type="text" name="paypalemail" class="form-control" value="'.$user->paypalemail.'">
									<span class="input-group-btn">
										<button class="btn btn-info" title="Save" onClick="return ( confirm(\'Do you really want to change payment data?\') );"><span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span></button>
									</span>
								</div>
							</div>
						</form>
						<div class="col-md-1" style="padding:0;">
							<form method="post" style="display:inline; margin:0px;">
							<input type="hidden" name="test_sales_manager" value="1">
							<input type="hidden" name="email" value="'.$user->paypalemail.'">
							<button class="btn btn-info btn-xs" title="Test Paypal email to obtain payment">Test...</button>
							</form>
						</div>
						<div class="col-md-3" style="padding:0;">
							'.draw_switch('make_paypalemail_not_confirmed', 'make_paypalemail_confirmed', $user->paypalemail_confirmed, '', '', 'Disable Paypal withdrawals', 'Paypal withdrawals disabled', '', 'Enable', $user, false).'
						</div>
					</div>
					'.draw_switch('withdrawal_disable', 'withdrawal_enable', !$user->withdrawal_disabled, '', '', 'Disable Withdrawals', 'Withdrawals Disabled', '', 'Enable', $user, false).'
					';
					?>
				</td>
			</tr>
			
			<tr>
				<td>
					Reward:
				</td>
				<td>
					<?php
					$select_options = '';
					foreach ( $payout_options as $value ) {
						if ($value['it_is_crypto'])
							$select_options = $select_options.'<option value="'.$value['id'].'">'.$value['name'].'</option>'."/r/n";
					}
					?>
					<form method="post" action="" class="form-horizontal row" style="margin:0px;">
						<input type="hidden" name="userid" value="<?php echo $user->userid; ?>">
						<input type="hidden" name="user_reward" value="1">
						<div class="col-md-2" style="padding: 0 20px 0 0;">
							<select name="crypto_name" class="form-control input-sm">
								<?php echo $select_options; ?>
							</select>
						</div>
						<div class="form-group col-md-2">
							<div class="input-group input-group-sm">
								<span class="input-group-addon" >$</span>
								<input type="number" name="amount_in_usd" class="form-control" placeholder="0.00">
							</div>
						</div>
						<div class="col-md-8">
							<div class="input-group input-group-sm">
								<span class="input-group-addon" style="border:0; background-color:transparent;">description:</span>
								<input type="text" name="description" style="" class="form-control" value="Reward">
								<span class="input-group-btn">
									<button class="btn-info btn" name="charge_btn" onClick="return ( confirm('Do you really want to reward user?') );">Reward</button>
								</span>
							</div>
						</div>
						
					</form>
				</td>
			</tr>
			<?php } ?>
			</table>
		</div>
		<div class="col-md-2" style="">
			<a href="#" onClick="return show_image_preview_box('<?php echo $user->get_photo(); ?>');"><img class="first_page_image" src="<?php echo $user->get_photo().'?tmp='.rand(); ?>" border="0" alt="Your Photo" id="personal_photo" style="width:150px; height:150px; margin:4px; margin-bottom:10px;"></a>
		</div>
	</div>	
	<script type="text/javascript">
	function show_hide_frame(frame_name, page_name)
	{
		if( $("#div_" + frame_name).css("display") == "none" ) {
			$("#div_" + frame_name).show();
			$("#div_" + frame_name + "_line").hide();
			$("#frame_" + frame_name).attr("src", page_name + "<?php echo $user->userid; ?>");
			$("#drop_down_" + frame_name).addClass("dropup").removeClass("dropdown");
		}
		else {
			$("#div_" + frame_name).hide();
			$("#div_" + frame_name + "_line").show();
			$("#drop_down_" + frame_name).addClass("dropdown").removeClass("dropup");
		}
	}
	$(document).ready(function(){
		try {
			$.ajax({
				method: "POST",
				url: "/api/balance",
				data: { userid: "<?php echo $user->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", manager_userid:"<?php echo $user_account->userid; ?>", manager_token: "<?php echo $user_account->psw_hash; ?>", add_available_funds:1, add_amount_in_usd:1 }
			})
			.done(function( ajax__result ) {
				try
				{
					var arr_ajax__result = JSON.parse(ajax__result);
					if ( arr_ajax__result["success"] ) {
						var all_currencies_balance_body = "";
						var available_in_usd = 0;
						for (i = 0; i < arr_ajax__result["values"].length; i++) { 
							if (arr_ajax__result["values"][i]["currency"] == "usd"){
								$("#balance_label").show();
								var positive_color = "color:inherit";

								$(".balance1").each(function () {
									if ( $(this).attr("positive_color") && $(this).attr("positive_color").length > 0 )
										positive_color = $(this).attr("positive_color");
								});
								$(".balance1").html(currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], positive_color, "color:#FF0000", "<?php echo DOLLAR_SIGN_POSITION; ?>"));
							}
							$("." + arr_ajax__result["values"][i]["currency"] + "_balance").html(currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]));
							$("." + arr_ajax__result["values"][i]["currency"] + "_available_funds").html(currency_format(arr_ajax__result["values"][i]["available_funds"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]));
							$("." + arr_ajax__result["values"][i]["currency"] + "_available_funds").css("opacity", "1");
							if ( arr_ajax__result["values"][i]["amount"] > 0 )
								$("." + arr_ajax__result["values"][i]["currency"] + "_row").show();
							available_in_usd = available_in_usd + Number(arr_ajax__result["values"][i]["amount_in_usd"]);
							all_currencies_balance_body = all_currencies_balance_body + "<span>" + arr_ajax__result["values"][i]["description"] + ":&nbsp; " + currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]) + (arr_ajax__result["values"][i]["amount"] > 0?", avalable: " + currency_format(arr_ajax__result["values"][i]["available_funds"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]):"") + "</span><br>";
						}
						$("#user_all_currencies_balance_body").html(all_currencies_balance_body + `Total available: ` + currency_format(available_in_usd));
					}
				}
				catch(error){ console_log(ajax__result + " --- " + error);}
			});
		}
		catch(error){console_log("1: " + error);}
	});
	</script>
	<?php
	echo
		( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ?
			draw_frame('childtrend', 'Trend', 'acc_stats_users.php?parentid=', 'icon-trendsreport-middle.png').
			draw_frame('visitors', 'Visitors', 'acc_stats_visits.php?userid=', 'ad_tr32x32.png')
			: ''
		).
		draw_frame('transactions', make_str_translateable('Transactions'), 'acc_transactions.php?userid=', '').
		( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ?
			draw_frame('purchases', 'Purchases', 'acc_purchases.php?userid=', '').
			draw_frame('money_flow', 'Money Flow', 'acc_quick_stats.php?userid=', '').
			draw_frame('payouts', 'Payouts', 'acc_payouts.php?userid=', 'withdraw.svg').
			draw_frame('term_deposits', 'Deposits', 'acc_mngr_term_deposits.php?userid=', 'deposit.png').
			draw_frame('allchilds', 'Referrals', 'acc_mngr_users.php?all_childs=1&parentid=', 'referrals_32x32.png').
			draw_frame('jobs', 'Jobs Done', 'acc_jobs_done.php?userid=', 'regional_reps_512x512.png').
			draw_frame('clicks', 'Clicks', 'acc_stats_clicks.php?userid=', '').
			draw_frame('tickets_from', 'Tickets from '.$user->full_name(), 'acc_tickets.php?from_this_user=1&userid=', 'lifebuoy.png').
			draw_frame('emails', 'Emails sent to '.$user->full_name(), 'acc_emails.php?userid=', 'email_new.png')
			: ''
		)
		;
	?>
	<?php if ( defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet' ) { ?>
		<br>
		<form class="form-horizontal" method="post">
			<input type="hidden" name="send_message" value="1">
			<input type="hidden" name="userid" value="<?php echo $user->userid; ?>">
			<h2>Send Message to <?php echo $user->full_name(); ?>:</h2>
			<textarea class="form-control" rows="6" name="message" minlength="5" required="required"></textarea>
			<button class="btn-info btn" style="margin-top:10px;">Send Message</button>
		</form>
	<?php } ?>
	<?php
} ?>
<br>
<?php
require(DIR_COMMON_PHP.'box_message.php');
require(DIR_COMMON_PHP.'box_image_preview.php');
require_once(DIR_WS_INCLUDES.'box_password.php');
require_once(DIR_WS_INCLUDES.'box_last_logins.php');
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>
