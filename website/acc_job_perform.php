<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'job.class.php');
require_once(DIR_WS_CLASSES.'job_sent.class.php');

$time = time();
if ( !empty($_GET['jobid']) ) 
	$jobid = $_GET['jobid'];

if ( !empty($_POST['jobid']) ) 
	$jobid = $_POST['jobid'];

if ( !empty($_GET['sentid']) ) 
	$sentid = $_GET['sentid'];

if ( !empty($_POST['sentid']) ) 
	$sentid = $_POST['sentid'];

$cancel = $user_account->disabled;

if ( !$cancel && empty($sentid) && empty($jobid) )
	$sentid = get_api_value('job_find_next_job');

$sentid = (int)$sentid;

if ( !empty($sentid) ) {
	$job_sent = new Job_sent();
	if ( $job_sent->read_data($sentid) ) {
		$jobid = $job_sent->jobid;
	}
	else
		unset($job_sent);
}

$job_already_is_done = false;
$job = new Job();
if ( !empty($jobid) ) {
	if ( !$job->read_data($jobid) ) {
		$cancel = true;
	}
	else {
		if ( $job->disabled ) 
			$cancel = true;
		if ( !$cancel ) {
			$job_text = $job->get_job_text($user_account, $var_text1, $var_text2, $var_int1, $var_int2, $var_int3);
			$cancel = !$job->can_be_performed;
		}
		if ( !$cancel ) {
			$job_already_is_done = get_api_value('job_get_job_already_is_done', '', array('jobid' => $jobid));
		}
		if ( !$cancel && $job->max_user_to_send > 0 ) {
			$job_get_number_of_already_sent = get_api_value('job_get_number_of_already_sent', '', array('jobid' => $jobid), '', $user_account);
			if ( $job_get_number_of_already_sent >= $job->max_user_to_send)
				$cancel = true;
		}
		if ( !$cancel && !$user_account->check_skill_level_enough($job) ) {
			$cancel = true;
		}
		if ( !$cancel && ( $job->skillid == 'review' || $job->skillid == 'permlink' ) && $job->review_jobid > 0 ) {
			$var_int1 = 0;
			$user_account->parse_job_text($job->jobid, $job, '', $var_text1, $var_text2, $var_int1, $var_int2, $var_int3);
			if ($var_int1 == 0)
				$cancel = true;
		}
	}
}
else
	$cancel = true;
if ( empty($sentid) ) {
	$job_sent = new Job_sent();
	$job_sent->userid = $user_account->userid;
	$job_sent->jobid = $job->jobid;
	$sentid = $job_sent->search_unfinished_job(1); //'AND done IS NULL ');
	
	if ( $sentid ) {
		if ( !$job_sent->read_data($sentid) )
			unset($job_sent);
	}
	else
		unset($job_sent);
}

if ( !empty($_POST['start_do_job']) && !isset($job_sent) ) {
	setcookie('job_done'.$jobid, '1', time() + 3600*24*365, '/', '.'.SITE_SHORTDOMAIN);
	if ( !$cancel ) {
		$job_sent = new Job_sent();
		$result = $job_sent->start_do_job($job->jobid, $user_account->userid, '', $job_text, $var_text1, $var_text2, $var_int1, $var_int2, $var_int3);
		$sentid = $job_sent->search_unfinished_job();
		if ( $sentid ) {
			if ( !$job_sent->read_data($sentid) )
				unset($job_sent);
			else {
				if ( !empty($job->URL) ) {
					header('Location: '.$job->URL(), true, 301);
					exit;
				}
			}
		}
	}
}

if ( $_POST['job_skipped'] == '1' ) {
	if ( $job_sent )
		$job_sent->done_job(1);
	header('Location: /acc_job_perform.php', true, 301);
	exit;
}
else 
if ( $_POST['submit_result'] == '1' ) {
	if ( $job_sent ) {
		$approwal_status = 'WT';
		if ( $job_sent->get_job()->result_auto_approve && empty($job_sent->get_job()->result_postpon_auto_approval_for_hours) )
			$approwal_status = 'AP';
		switch ($job_sent->get_job()->result_type) {
			case 'experts_review':
				if ( $_POST['iq_test'] == 'no' || empty($_POST['iq_test']) )
					$approwal_status = 'DC';
				$_POST['result_text'] = $_POST['experts_mark'];
			break;
		}
		$box_message = $job_sent->done_job(0, $_POST['result_text'], $approwal_status);
		if ( empty($box_message) ) {
			if ( $approwal_status == 'DC' )
				$box_message = 'Error: Job has been declined.';
			else {
				$interval_arr = seconds_in_redable( $job_sent->get_job()->result_postpon_auto_approval_for_hours * 60*60 );
				$box_message = 'Job has been done. '.(!empty($job_sent->get_job()->result_postpon_auto_approval_for_hours)?'You will be rewarded in '.($interval_arr['months']>0?$interval_arr['months'].' months':($interval_arr['days']>0?$interval_arr['days'].' days':$interval_arr['hours'].' hours')):'').'.';
			}
			$message_box_hide_func = 'go_to_jobs';
		}
		else {
			if ( $job->result_type == 'answer' ) {
				$_POST['result_answer_error'] = $_POST['result_answer_error'] + 1;
				if ( $_POST['result_answer_error'] > 1 ) {
					$job_sent->done_job(0, '', 'DC');
					$message_box_hide_func = 'go_to_jobs';
				}
			}
		}
	}
	else {
		$box_message = 'Error: job already is done.';
		$message_box_hide_func = 'go_to_jobs';
	}
}
else 
if ( $_GET['create_and_done_job'] == '1' ) {
	if ( !$job_already_is_done ) {
		$job_sent = new Job_sent();
		$job_sent->start_do_job($job->jobid, $user_account->userid, '', $job_text, $var_text1, $var_text2, $var_int1, $var_int2, $var_int3);

		$sentid = $job_sent->search_unfinished_job();
		if ( $sentid ) {
			if ( $job_sent->read_data($sentid) ) {
				$job_sent->done_job(0, $_GET['result'], 'AP', true);
			}
		}
	}
	exit;
}

$page_header = $job->name;
$page_title = $page_header;
$page_desc = $page_header;

require(DIR_WS_INCLUDES.'header.php');

global $job_rating;
$_POST['result_text'] = tep_sanitize_string($_POST['result_text'], 0, true);
echo '<form action="" method="post" name="reload_form"></form>';
if ( !$cancel ) { 
	echo '
	'.(!empty($job->reward)?'
	<div class="row" style="padding:0 15px 0 15px;">
		<div class="col-md-8"></div>
		<div class="col-md-4 box_type2" style="text-align:right;">
			Reward for this job: <span style="color:#008800; font-size:18px; font-weight:bold;">'.currency_format($job->reward).'</span>
		</div>
	</div>
	':'');
}
if ( $cancel )
	echo '<h3 style="padding:6px;">Sorry, no more jobs for today.</h3>';
else
if ( $job->banned )
	echo '
	<h3 style="padding:6px;">This job is banned.</h3>
	<button style="margin:20px;" onclick="go_to_jobs();">Return...</button>
	';
else
if ( $job_already_is_done ) {
	//echo "job_already_is_done: $job_already_is_done<br>";
	if ( $job_sent && $job_sent->status == "WT") 
		echo '<h3 style="padding:6px;">
		<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'job_waiting.png" border=0 title="Job is waiting for approval">
		This job is waiting for approval.
		</h3>';
	else
		echo '<h3 style="padding:6px;">This job is already done.</h3>';
	echo '<button class="btn btn-info" style="margin:20px;" onclick="go_to_jobs();">Return...</button>';
}
else
if ( isset($job_sent) && $job_sent->status == "WT" && $job->repeat_times < 2 ) {
	echo '<h3 style="padding:6px;">
		<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'job_waiting.png" border=0 title="Job is waiting for approval">
		This job is waiting for approval.
		</h3>
		<button style="margin:20px;" onclick="go_to_jobs();">Return...</button>';
}
else {
	// Alow perform new job if previous job is not approved yet
	if ( $job->repeat_times > 1 &&
		(
			($job_already_is_done && isset($job_sent) && $job_sent->status == "WT") 
			|| (!$job_already_is_done && isset($job_sent) && $job_sent->status == "WT")
		)
		)
		unset($job_sent);
	
	echo '<div class="box_type1" style="margin-top:20px;">'.(isset($job_sent)?$job_sent->display_text:$job_text).'</div>';
	
	if ( file_exists(DIR_WS_INCLUDES.'skill_'.$job->skillid.'.php') ) {
		include(DIR_WS_INCLUDES.'skill_'.$job->skillid.'.php');
	}
	else {
		echo '
		<div class="_box_type2" style="margin-top:10px;">
			';
			$job_tagline = get_text_between_tags($job->job, '<job_tagline>', '</job_tagline>');
			switch ( $job->result_type ) {
				case 'report' :
					echo str_replace("\r\n", '<br>', $job->result_description); 
				break;
				case 'answer' :
					echo 'You have to give correct answer to the following question:<br><strong>'.$job->result_question.'</strong>'; 
				break;
				case 'text' :
					if ( !empty($job_tagline) )
						echo $job_tagline;
					else
						echo 'You have to submit text. '; 
					if ( $job->result_min_size > 0 )
						echo 'Minimum words: <strong>'.$job->result_min_size.'</strong>. '; 
					if ( $job->result_max_size > 0 )
						echo 'Maximum words: <strong>'.$job->result_max_size.'</strong>. '; 
					if ( $job->result_hint_mandatory_keywords ) {
						if ( !empty($job->result_mandatory_keywords) ) {
							$s = $job->result_mandatory_keywords;
							if ( is_integer(strpos($s, '<OR>')) ) {
								$keywords = explode('<OR>', $s); 
								$keyword = '';
								shuffle($keywords);
								foreach ($keywords as $word)
									if ( !empty($word) ) {
										$keyword = $word;
										break;
									}
							}
							if ( !empty($keyword) )
								echo '<br>Your text <strong>must contain</strong> the following words: <br><strong><font color="#008000">'.$keyword.'</font></strong>.<br>'; 
						}
					}
					if ( $job->result_hint_intolerable_keywords ) {
						if ( !empty($job->result_intolerable_keywords) )
							echo '<br>The following words are <strong>prohibited</strong>: <br><strong><font color="#880000">'.$job->result_intolerable_keywords.'</font></strong>.<br>'; 
					}
				break;
				case 'URL' :
					if ( !empty($job_tagline) )
						echo $job_tagline;
					else
						echo 'When you have done this job, please enter a Web Page&rsquo;s address (URL) in the input box below:';
				break;
				case 'experts_review':
					if ( !empty($job_tagline) )
						echo $job_tagline;
					else
						echo 'You have to give your opinion by selecting one of answers.';
				break;
			}
			echo '
		</div>';
		if ( !isset($job_sent) ) 
		{ 
			?>
			<div style="position:relative; height:60px;">
				<div class="exclamation_square"></div>
				<span class="exclamation_text">
					<?php 
					if ( !empty($job->cancel_unfinished_job_in) ) {
						echo 'This job must be performed within <strong>';
						if ( $job->cancel_unfinished_job_in <= 60 )
							echo '1 hour';
						if ( $job->cancel_unfinished_job_in > 60 && $job->cancel_unfinished_job_in < 60*24 )
							echo round($job->cancel_unfinished_job_in / 60).' hours';
						if ( $job->cancel_unfinished_job_in >= 60*24 && $job->cancel_unfinished_job_in < 60*24*2 )
							echo round($job->cancel_unfinished_job_in / 60 / 24).' day';
						if ( $job->cancel_unfinished_job_in > 60*24 && $job->cancel_unfinished_job_in < 60*24*7 )
							echo round($job->cancel_unfinished_job_in / 60 / 24).' days';
						if ( $job->cancel_unfinished_job_in >= 60*24*7 && $job->cancel_unfinished_job_in < 60*24*7*2 )
							echo round($job->cancel_unfinished_job_in / 60 / 24 / 7).' week';
						if ( $job->cancel_unfinished_job_in > 60*24*7 && $job->cancel_unfinished_job_in < 60*24*30 )
							echo round($job->cancel_unfinished_job_in / 60 / 24 / 7).' weeks';
						if ( $job->cancel_unfinished_job_in >= 60*24*30 )
							echo round($job->cancel_unfinished_job_in / 60 / 24 / 30).' month';
						echo '</strong>. ';
					}
					?>To start this job click on the <strong>&rdquo;Start This Job&rdquo;</strong> button.<br>
					Return to this page after the job is done, and enter the results.
				</span>
			</div>
			<div align="center">
				<form method="post" <?php if ( $user_account->is_loggedin() && !empty($job->URL) ) echo 'target="_blank"'; if (!$user_account->is_loggedin()) echo 'action="/login.php"'; ?> >
				<input type="hidden" name="start_do_job" value="1">
				<input type="hidden" name="jobid" value="<?php echo $job->jobid; ?>">
				<button name="start_job" onclick="window.setTimeout('window.location = window.location + \'&rnd=\' + Math.floor(Math.random()*10);', 3000);">Start This Job...</button><br>
				</form>
				<span class="description">You will be redirected to: <?php echo substr($job->URL(), 0, 150); ?></span>
			</div>
			<?php 
		} 
		else {
			if ( empty($_POST['result_answer_error']) )
				$_POST['result_answer_error'] = 0;
			echo '
			<form method="post" name="report_frm" class="form-horizontal" style="margin-top:0px;">
			<input type="hidden" name="submit_result" value="1">
			<input type="hidden" name="sentid" value="'.$job_sent->sentid.'">
			<input type="hidden" name="result_answer_error" value="'.$_POST['result_answer_error'].'">
			<div class="row" style="margin-top:0px; padding:0px 20px 0px 20px;">
			';
			switch ($job->result_type) {
				case 'report':
					echo '
					Please write a short report about your work:<br>
					<div style="padding-right:6px;">
					<textarea name="result_text" wrap="soft" style="width:100%; height:100px;" class="bordered_edit">'.$_POST['result_text'].'</textarea>
					</div>
					<!--SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
					<SCRIPT language="JavaScript">
						var frmvalidator = new Validator("report_frm", "report_btn");
						frmvalidator.addValidation("result_text", "minlen=10", "Please enter your report");
						
					</SCRIPT-->
					';
				break;
				case 'answer':
					echo '<strong>'.$job->result_question.(is_integer(strpos($job->result_question, '?'))?'':'?').'</strong><br>';
					echo '<table cellspacing="0" cellpadding="0" border="0" width="100%">
						';
					$arr = array(
						'" type="radio" name="result_text" value="'.crc32($job->result_wrong_answer1).'"></td><td width="100%">'.$job->result_wrong_answer1.'</td>',
						'" type="radio" name="result_text" value="'.crc32($job->result_right_answer).'"></td><td width="100%">'.$job->result_right_answer.'</td>',
						'" type="radio" name="result_text" value="'.crc32($job->result_wrong_answer2).'"></td><td width="100%">'.$job->result_wrong_answer2.'</td>',
					);
					shuffle($arr);
					$i = 1;
					foreach ($arr as $value) {
						echo '<tr><td style="vertical-align:middle; padding:4px; padding-right:10px;"><input id="answer_'.$i.$value.'</tr>';
						$i++;
					}
					echo '</table>
					<SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
					<SCRIPT language="JavaScript">
						var frmvalidator = new Validator("report_frm", "report_btn");
						frmvalidator.setAddnlValidationFunction("validate_answer");
						
						function validate_answer()
						{
							objValue = document.report_frm.result_text;
							var radioLength = objValue.length;
							if ( radioLength == undefined ) {
								if( objValue.checked )
									return true; 
							}
							for (var i = 0; i < radioLength; i++ ) {
								if ( objValue[i].checked )
									return true; 
							}
							alert("Please select correct answer.");
							return false;
						}
					</SCRIPT>
					';
				break;
				case 'text':
					echo '
					Please enter your text in the box below:<br>
					<textarea name="result_text" wrap="soft" style="width:100%; height:100px;" class="box_type1">'.$_POST['result_text'].'</textarea>
					
					<!--SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
					<SCRIPT language="JavaScript">
						var frmvalidator = new Validator("report_frm", "report_btn");
						frmvalidator.addValidation("result_text","req","Please enter your text");
					</script-->
					';
				break;
				case 'URL':
					echo '
					<div class="form-group has-feedback '.(is_integer(strpos($box_message, 'Error:'))?'has-error':'').'">
						<label class="control-label col-md-3" for="result_text">Please enter URL:</label>
						<div class="col-md-9 inputGroupContainer">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-globe" aria-hidden="true"></span></span>
								<input type="text" name="result_text" value="'.$_POST['result_text'].'" class="form-control" placeholder="URL (like: www.mypage.com)" required="required">
							</div>
						</div>
					</div>
					<!--SCRIPT language="JavaScript" src="/javascript/gen_validatorv2.js" type="text/javascript"></SCRIPT>
					<SCRIPT language="JavaScript">
						var frmvalidator = new Validator("report_frm", "report_btn");
						frmvalidator.addValidation("result_text","req","Please enter URL");
					</script-->
					';
				break;
				case 'experts_review':
					$s = get_text_between_tags($job->job, '<iq_test>', '</iq_test>');
					if (!empty($s)) {
						$s1 = get_text_between_tags($job->job, '<iq_test_question>', '</iq_test_question>');
						delete_text_between_tags($s, '<iq_test_question>', '</iq_test_question>');
						echo '<p><b>'.$s1.'</b></p>';
						
						$right_answer = get_text_between_tags($job->job, '<iq_test_right_answer>', '</iq_test_right_answer>');
						delete_text_between_tags($s, '<iq_test_right_answer>', '</iq_test_right_answer>');
						
						$strings = preg_split('/$\R?^/m', $s);
						shuffle($strings);
						$strings2 = array();
						$j = 1;
						foreach ( $strings as $string ) {
							if ( !empty($string) && is_integer(strpos($string, '==')) ) {
								$strings2[] = $string;
								$j++;
								if ($j > 4)
									break;
							}
						}
						$strings2[] = $right_answer;
						shuffle($strings2);
						$j = 1;
						foreach ( $strings2 as $string ) {
							$params = explode('==', $string);
							$params[0] = trim($params[0]);
							$params[1] = trim($params[1]);
							if ( !empty($params[1]) ) {
								$param_desc = tep_sanitize_string($params[1]);
								echo '<p><input type="radio" name="iq_test" value="'.$params[0].'">&nbsp;&nbsp;'.make_synonyms($params[1], 1, '', '', true).'</p>';
							}
							$j++;
						}
					}
					$s = get_text_between_tags($job->job, '<data_question>', '</data_question>');
					if (empty($s))
						$s = 'Please select one answer from below, which best describes your opinion:';
					echo '<b>'.$s.'</b>';
					echo '<table class="table table-borderless">';
					$s = get_text_between_tags($job->job, '<data>', '</data>');
					$strings = preg_split('/$\R?^/m', $s);
					shuffle($strings);
					$j = 1;
					foreach ( $strings as $string ) {
						$params = explode("|", $string);
						$params[0] = trim($params[0]);
						$params[1] = trim($params[1]);
						if ( !empty($params[1]) ) {
							$param_desc = tep_sanitize_string($params[1], 30);
							echo '
							<tr>
								<td><input type="radio" name="experts_mark" value="m'.$params[0].'|'.$j.'||'.$param_desc.'"></td>
								<td >
									<div style="max-height:64px; overflow:hidden;" id="omt'.$params[0].'">
										<div id="dmt'.$params[0].'">
											'.$params[1].'
										</div>
									</div>
									<div style="height:0px; width:100%; position:relative; top:-20px; display:none;" id="blmt'.$params[0].'">
										<div style="height:20px; width:100%; positioin: absolute; background-image:url(/'.DIR_WS_WEBSITE_IMAGES_DIR.'blurred_bottom_edge.png); background-repeat: repeat-x; "></div>
									</div>
									<a href="#" class="experts_review_more_text" style="display:none;" id="mt'.$params[0].'" onclick="$(\'#o\' + $(this).attr(\'id\')).css(\'height\', \'auto\'); $(\'#o\' + $(this).attr(\'id\')).css(\'max-height\', \'none\'); $(\'#o\' + $(this).attr(\'id\')).css(\'overflow\', \'visible\'); $(this).hide(); $(\'#bl\' + $(this).attr(\'id\')).hide(); return false;"><span class="glyphicon glyphicon-collapse-down" aria-hidden="true"></span>&nbsp;More...</a>

								</td>
							</tr>';
						}
						$j++;
					}
					echo '</table>
					<script type="text/javascript">
					$(".experts_review_more_text").each(function( index ) {
						if ( $("#d" + $(this).attr("id")).outerHeight() > 64 ) {
							$(this).show();
							$(\'#bl\' + $(this).attr(\'id\')).show();
						}
					});
					</script>
					';
				break;
			}
			
			if ( is_integer(strpos($box_message, 'Error:')) ) 
				echo '
				<div class="col-md-3"></div>
				<div class="col-md-9 error_message">
					'.$box_message.'
				</div>
				'; 
			echo '
			</div>
			<div align="center" style="margin-top:20px;">
				<button class="btn-lg btn-primary" name="report_btn" style="">Submit Result</button><br>
			</div>
			</form>
			<form method="post" class="form-horizontal" style="text-align:center;">
				<input type="hidden" name="job_skipped" value="1">
				<input type="hidden" name="sentid" value="'.$job_sent->sentid.'">
				<button class="btn btn-warning">Skip this job &nbsp;<span class="glyphicon glyphicon-forward" aria-hidden="true"></span></button>
			</form>
			';
		}
	}
}
?>

<script language="JavaScript">
function go_to_jobs()
{
	window.location.assign("/acc_job_perform.php");
	return false;
}

function select_by_click(click_object)
{
	if (document.selection){
		var range = document.body.createTextRange(); 
		range.moveToElementText(click_object);
		range.select();
	}
	else {
		if (window.getSelection) {
			var range = document.createRange();
			range.selectNode(click_object);
			window.getSelection().removeAllRanges();
			window.getSelection().addRange(range);
		}
	}
}

</script>

<?php
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	$box_message = str_replace("\r\n", ' ', $box_message);
	echo '<script language="JavaScript">
	on_message_box_hide_func = "'.$message_box_hide_func.'";
	';
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo 'show_message_box_box("Error", "'.$box_message.'", 2);
		'."\r\n";
	else
		echo 'show_message_box_box("Success", "'.$box_message.'", 1);
		'."\r\n";
	echo '</script>'."\r\n";
}
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
