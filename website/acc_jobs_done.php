<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'job_sent.class.php');

if ( !empty($_GET['approval_status']) ) {
	if ( !empty($_GET['sentid']) ) {
		$job_sent = new Job_sent();
		if ( $job_sent->read_data($_GET['sentid']) ) {
			$job_sent->approve_job($_GET['approval_status'], 'Reward for job', '', true);
			echo 'approv success';
		}
	}
	exit;
}

$display_in_short = !empty($_GET['userid']) || !empty($_GET['ownerid']) /*|| !empty($_GET['skillid'])*/;
$_GET['userid'] = tep_sanitize_string($_GET['userid']);
$_GET['ownerid'] = tep_sanitize_string($_GET['ownerid']);
$_GET['skillid'] = tep_sanitize_string($_GET['skillid']);

$page_header = 'Waiting for Approval Jobs';
$page_title = $page_header;
$page_desc = 'List of done jobs.';
if ( $display_in_short )
	$_GET['noheader'] = 1;
require(DIR_WS_INCLUDES.'header.php');

include_once(DIR_COMMON_PHP.'print_sorted_table.php');

if ( !empty($_GET['userid']) )
	$tmp_userid = $_GET['userid'];
else
if ( !empty($_GET['ownerid']) )
	$tmp_userid = $_GET['ownerid'];
else 
	$tmp_userid = $user_account->userid;

if ( !$display_in_short )
	$rows_per_page = 5;
else
	$rows_per_page = 4;

$current_page_number = 1;
$total_rows = 0;

echo '
<form method="get" name="jobid_frm" class="row form-horizontal" style="margin-bottom:10px;">
	<input type="hidden" name="userid" value="'.$_GET['userid'].'">
	<input type="hidden" name="ownerid" value="'.$_GET['ownerid'].'">
	<label class="control-label col-md-3" for="">Jobs:</label>
	<div class="col-md-6">
		<select class="form-control" name="skillid" onChange="document.jobid_frm.submit();" style="">
			<option value="" '.(empty($_GET['skillid'])?'SELECTED':'').'>All</option>
';
$list_of_jobs = get_api_value('job_get_done_jobs_skills');
foreach ($list_of_jobs as $job_row)
	echo '<option value="'.$job_row['skillid'].'" '.($_GET['skillid'] == $job_row['skillid']?'SELECTED':'').'>'.$job_row['name'].'</option>';
echo '
		</select>
	</div>
	<div class="col-md-3"></div>
</form>
';

$row_eval = '
	if ( $row["c_result_type"] == "file" ) {
		$banner_preview_box_width = 86;
		$banner_preview_box_height = 66;
		$row["c_result_height"] = "100";
		$job_res = "";
		$s = $row["c_result_description"];
		if ( empty($s) )
			$s = $job->$row["c_result_image_width"].\'x\'.$row["c_result_image_height"];
		$image_size_arr = explode("\r", $s);

		$s = DIR_WS_JOB_RESULTS.JOB_RESULT_PREFIX.$row["c_sentid"]."_*.*";
		$folder = glob($s);
		foreach($folder as $file) {
			$dir = dirname($file)."/";
			$file_name = basename($file);

			$s = substr( $file, strpos($file, "'.IMAGE_SIZE_PREFIX.'") + strlen("'.IMAGE_SIZE_PREFIX.'") );
			$j = (int)substr( $s, 0, strpos($s, "_") );

			$result_image_width = (int)substr($image_size_arr[$j], 0, strpos($image_size_arr[$j], \'x\'));
			$result_image_height = (int)substr($image_size_arr[$j], strpos($image_size_arr[$j], \'x\') + 1);
			
			$job_res = $job_res.\'
			<div style="width:92px; height:75px; position:relative; display:inline-block;">
				<div style="width:100%; height:auto; position:relative; display:inline-block; text-align:center; class="description">
				\'.$image_size_arr[$j].\'
				</div>
				<script language="JavaScript">
				var img\'.$j.$row["c_sentid"].\' = "\'.DIR_WEBSITE_FOLDER.DIR_WS_JOB_RESULTS_DIR.basename($file).\'";
				</script>
				<div style="width:100%; height:80px; position:relative; display:inline-block; cursor:pointer;" onclick="return show_image_preview_box(img\'.$j.$row["c_sentid"].\');">
					<div class="wall_thumb_frame" style=""></div>
					<div style="width:\'.$banner_preview_box_width.\'px; height:\'.$banner_preview_box_height.\'px; border:none; background-color:transparent; overflow:hidden;">
					<img style="position:relative; left:\'.($result_image_width > $result_image_height?"1":ROUND(($banner_preview_box_width / 2) - $banner_preview_box_height/$result_image_height*$result_image_width/2 )).\'px; 
					top:\'.($result_image_width > $result_image_height?ROUND( ($banner_preview_box_height / 2) - $banner_preview_box_width/$result_image_width*$result_image_height/2):"1").\'px; 
					width:\'.($result_image_width > $result_image_height?$banner_preview_box_width:ROUND($banner_preview_box_height/$result_image_height*$result_image_width) ).\'px; 
					height:\'.($result_image_width > $result_image_height?ROUND($banner_preview_box_width/$result_image_width*$result_image_height):$banner_preview_box_width).\'px;" src="\'.DIR_WEBSITE_FOLDER.DIR_WS_JOB_RESULTS_DIR.basename($file).\'" border="0" alt=""></div>
				</div>
			</div>
			\';
		}
		$row["c_result"] = $job_res;
	}
	$row["c_reward"] = currency_format($row["c_reward"], "#ff0000");
';

$whole_header_html = '
	<tr>
		<td style="">
			<div class="row">
				<div class="col-md-1"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>Type</strong></a>#sort_label0#</div>
				<div class="col-md-2"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Started</strong></a>#sort_label1#</div>
				<div class="col-md-1"><a class=sorted_table_link href="javascript: RedrawWithSort(2);"><strong>Reward</strong></a>#sort_label2#</div>
				<div class="col-md-2"><a class=sorted_table_link href="javascript: RedrawWithSort(3);"><strong>Author</strong></a>#sort_label3#</div>
				<div class="col-md-3"><a class=sorted_table_link href="javascript: RedrawWithSort(4);"><strong>Status</strong></a>#sort_label4#</div>
				<div class="col-md-3"><a class=sorted_table_link href="javascript: RedrawWithSort(5);"><strong>Job Name</strong></a>#sort_label5#</div>
			</div>
		</td>
	</tr>
';
$whole_row_html = '
	<tr>
		<td style="">
			<div class="row">
				<div class="col-md-1" style="vertical-align:top; text-align:center;">
					#c_type#
				</div>
				<div class="col-md-2" style="vertical-align:top;">
					#c_date#
					<span class="description" style="color:#'.COLOR1DARK.'; padding:0px; margin:0px;">#c_time#<br>
					IP:#c_ip#
					</span>
				</div>
				<div class="col-md-1" style="vertical-align:top; text-align:right;">
					#c_reward#
				</div>
				<div class="col-md-2" style="vertical-align:top; ">
					<a href="/acc_viewuser.php?userid=#c_userid#" target="_blank">#c_author#</a>
				</div>
				<div class="col-md-3" style="vertical-align:top;">
					#c_job_status#
					<span id="status_description_#c_sentid#">#c_job_status_description#</span>
					<p class="description" style="color:#'.COLOR1DARK.'; padding-left:0px;">Done: #c_done#</p>
				</div>
				<div class="col-md-3" style="vertical-align:top; ">
					#c_description#
				</div>
			</div>
			<div style="width:100%; padding:2px; padding-top:0px; #c_show_result#">
				<span class="description" style="color:#'.COLOR1DARK.';"><b>Job Result:</b></span>
				<div style="float:right; margin:0px; height:0px;">
					<div style="position:relative; top:0px;">
						<span class="description" style="#c_approve_visible#">will be auto approved #c_auto_approve_in#.</span>
					</div>
				</div>
			</div>
			<div style="width:inherit; padding:2px; margin:0px; margin-bottom:4px; #c_show_result#">
				<div style="width:inherit; height:#c_result_height#px; padding:2px; border:1px solid #'.COLOR3BASE.'; 
					border-bottom-color: #'.COLOR3LIGHT.'; border-right-color: #'.COLOR3LIGHT.'; background-color:#'.PAGE_TEXT_COLOR_LIGHT.'; overflow:auto;" id="res_div_#c_sentid#">
					#c_result#
				</div>
				<div style="width:inherit; height:0px; padding:2px;">
					<button class="btn btn-xs btn-link" onclick="preview_text(\'#c_sentid#\'); return false;" style="margin:4px; #c_preview_btn_visible#" >Preview...</button>
					<button class="btn btn-xs btn-danger" onclick="return approve_job(\'#c_sentid#\', \'DC\');" style="#c_approve_visible# #c_show_decline# margin:4px; float:right;" 
						id="disapprove_btn_#c_sentid#"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Decline this Job</button>
					<button class="btn btn-xs btn-success" onclick="return approve_job(\'#c_sentid#\', \'AP\');" style="#c_approve_visible# #c_show_approve# margin:4px; float:right;" 
						id="approve_btn_#c_sentid#"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Approve this Job</button>
					<img src="/images/wait64x64.gif" style="display:none; float:right;" id="approve_wait_#c_sentid#" width="16" height="16" border="0" alt="">
				</div>
			</div>
		</td>
	</tr>
';
$table_tag = '<table class="table table-striped">';
$output_str = '';
if ( print_sorted_table(
		'admin_jobs_done', 
		$header, 
		array('by_userid' => $tmp_userid, 'display_in_short' => $display_in_short, 'get_userid' => $_GET['userid'], 'jobid' => $_GET['jobid'], 'skillid' => $_GET['skillid']), 
		'', //$row_html
		$rows_per_page, 
		$current_page_number, 
		$row_number, 
		$total_rows, 
		$output_str,
		'', 
		$table_tag, 
		'class=account_webpages_cell',
		'class=account_webpages_cell_description', 
		1, 
		'DESC', 
		$whole_row_html, 
		$whole_header_html,
		'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_Z...A.png" border="0" alt="">', // $sorted_column_label_mask
		'', // $not_sorted_column_label_mask
		true, // $read_page_nmb_from_cookie
		$row_eval
		)
	)
{
	echo '<div id=table_div>'.$output_str.'</div>
	<p>';
	paging($current_page_number, $total_rows, $rows_per_page, $row_number);
	echo '</p>';
}
else {
	if ( $display_in_short )
		echo '<h3>This user done no jobs.</h3><br><br>';
	else
		echo '<h3>No work that has been done by users for you.</h3><br><br>';
}

require(DIR_COMMON_PHP.'box_image_preview.php');
include(DIR_WS_INCLUDES.'box_send_email.php');
$show_frame_width = 600; 
$show_frame_height = 350; 
$show_frame_bg_color = COLOR3LIGHT;
require(DIR_COMMON_PHP.'box_show_frame.php');
require_once(DIR_COMMON_PHP.'box_message.php');
?>
<script language="JavaScript">
function approve_fail(sentid)
{
	show_hide_obj("approve_wait_" + sentid, 0);
	show_hide_obj("approve_btn_" + sentid, 1);
	show_hide_obj("disapprove_btn_" + sentid, 1);
}

function approve_job(sentid, approval_status)
{
	show_hide_obj("approve_btn_" + sentid, 0);
	show_hide_obj("disapprove_btn_" + sentid, 0);
	show_hide_obj("approve_wait_" + sentid, 1);
	
	new Ajax.Request("<?php echo $_SERVER['SCRIPT_NAME']; ?>",
		{
			method:"get",
			parameters: "approval_status=" + approval_status + "&sentid=" + sentid,
			onSuccess: function(transport){
				if ( transport.responseText.indexOf("approv success") >= 0 ) {
					show_hide_obj("approve_wait_" + sentid, 0);
					var img = document.getElementById("status_image_" + sentid);
					if ( img ) {
						if ( approval_status == "AP" )
							img.src = "/images/icon-ok-small.png";
						else
							img.src = "/images/icon-delete-small.png";
					}
					var img = document.getElementById("status_description_" + sentid);
					if ( img ) {
						if ( approval_status == "AP" )
							img.innerHTML = "Approved";
						else
							img.innerHTML = "Declined";
					}
				}
				else {
					alert(transport.responseText);
					approve_fail(sentid);
				}
			},
			onFailure: function(){
				approve_fail(sentid);
			}
		}
	);
	return false;
}
function preview_text(sentid)
{
	show_message_box_box("", "", 0, string_to_hex32($("#res_div_" + sentid).html()) );
	return false;
}
</script>
<?php
if ( !$display_in_short )
	require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
