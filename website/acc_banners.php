<?php
require('../includes/application_top.php');
if ( empty($_GET['type']) )
	$_GET['type'] = 'I';
require(DIR_WS_INCLUDES.'account_common_top.php');
require_once(DIR_WS_CLASSES.'banner.class.php');
$page_header = 'Invite Users';
$page_title = $page_header;
$page_desc = 'List of all available banners and other creatives';
require(DIR_WS_INCLUDES.'header.php');
include_once(DIR_COMMON_PHP.'print_sorted_table.php');
$rows_per_page = 8;
$banner_preview_box_sizeX = 160;
$banner_preview_box_sizeY = 80;

$_GET['type'] = tep_sanitize_string($_GET['type'], 1);

$row_eval = '
global $user_account;
switch ($row["c_ad_type"]) {
	case "H" :
		$row["c_preview"] = "<iframe style=\'height:800px; width:1600px; transform:scale(0.1); transform-origin:0px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0></iframe><div style=\'position:relative; min-width: 1600px; min-height: 800px; background-color:transparent; top:-800px; left:0px;\'></div>";//convert_html2text($row["c_html_text"]);
	break;
	case "E" :
		$row["c_preview"] = "<p style=\'margin-top:auto; white-space:pre-line;\' class=\'ad_description_sm\' >".make_synonyms(convert_html2text($row["c_html_text"])."<paragraph/>", 3, "", "")."</p>";
	break;
	case "R" :
		switch ($row["c_size"]) {
			case "text" :
				$row["c_preview"] = "<iframe style=\'height:150px; width:300px; transform:scale(0.53); transform-origin:0px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0></iframe><div style=\'position:relative; in-width:300px; min-height: 150px; background-color:transparent; top:-150px; left:0px;\'></div>";//convert_html2text($row["c_html_text"]);
			break;
			case "120x60":
			case "300x100":
			case "300x250":
			case "468x60" :
			case "728x90" :
				$preview_width = round($row["c_banner_width"] / 1);
				$preview_height = round($row["c_banner_height"] / 1);
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(160/$row["c_banner_width"])."); transform-origin:0px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
			case "600x600": // 45px
				$preview_width = $row["c_banner_width"];
				$preview_height = $row["c_banner_height"];
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(80/$row["c_banner_height"])."); transform-origin:45px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
			case "300x600": // 70px
				$preview_width = $row["c_banner_width"];
				$preview_height = $row["c_banner_height"];
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(80/$row["c_banner_height"])."); transform-origin:70px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
			case "160x600": // 80px
				$preview_width = $row["c_banner_width"];
				$preview_height = $row["c_banner_height"];
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(80/$row["c_banner_height"])."); transform-origin:80px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
			case "120x600": // 80px
				$preview_width = $row["c_banner_width"];
				$preview_height = $row["c_banner_height"];
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(80/$row["c_banner_height"])."); transform-origin:83px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
			case "120x240": // 80px
				$preview_width = $row["c_banner_width"];
				$preview_height = $row["c_banner_height"];
				$row["c_preview"] = "<iframe style=\'height:".$preview_height."px; width:".$preview_width."px; transform:scale(".(80/$row["c_banner_height"])."); transform-origin:90px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0 scrolling=no></iframe><div style=\'position:relative; min-width:".$preview_width."px; min-height:".$preview_height."px; background-color:transparent; top:-".$preview_height."px; left:0px;\'></div>";
			break;
		}
	break;
	case "T" :
		$row["c_preview"] = "<iframe style=\'height:150px; width:280px; transform:scale(0.57); transform-origin:0px 0px;\' src=\'/services/acc_get_banner_code.php?as_is=1&banner=".$row["c_id"]."&user=".$user_account->userid."\' frameborder=0></iframe><div style=\'position:relative; min-width:300px; min-height: 150px; background-color:transparent; top:-150px; left:0px;\'></div>";//convert_html2text($row["c_html_text"]);
	break;
}
';
$whole_header_html = '
	<tr><td>
		<div class="row">
			<!--div class="col-md-3"><a class=sorted_table_link href="javascript: RedrawWithSort(0);"><strong>Clicks</strong></a>#sort_label0#</div-->
			<div class="col-md-4"><a class=sorted_table_link href="javascript: RedrawWithSort(1);"><strong>Size</strong></a>#sort_label1#</div>
			<div class="col-md-4"></div>
			<div class="col-md-4"></div>
		</div>
	</td></tr>
';
$row_html = array();
$whole_row_html = '
	<tr><td>
		<div class="row">
			<!--div class="col-md-3" style="padding-top:20px;">
				last 7 days clicks: #c_stat_clicks#
			</div-->
			<div class="col-md-4" style="padding-top:20px;">
				#c_size#
			</div>
			<div class="col-md-4" style="padding-bottom:20px;">
				<div style="width:'.$banner_preview_box_sizeX.'px; height:'.$banner_preview_box_sizeY.'px; border:1px #'.COLOR1BASE.' solid; background-color:#'.COLOR3LIGHT.'; overflow:hidden; cursor:pointer; margin:auto;" onclick="return preview_banner(\'#c_banner_big_preview#\', \'#c_ad_type#\');">
					#c_preview#
				</div>
			</div>
			<div class="col-md-4" style="padding-top:20px; text-align:center;">
				<button class="btn btn-sm btn-success"  onclick="show_hide_banner_code(\'#c_id#\');" style="margin-left:20px; padding-left:20px; padding-right:20px;" id="button_#c_id#">Get Banner Code</button>
			</div>
		</div>
		<div class="row" id="banner_#c_id#" style="display:none; padding:20px;">
			<iframe frameborder="0" class="banner_textarea" src="/acc_banner_code_wait.html" id="frame_#c_id#"></iframe>
			<table class="text-success" style="margin-top:-10px;">
				<tr>
					<td style="vertical-align:top;">
						<h1 style="margin-right:10px;"><span class="glyphicon glyphicon-question-sign text-success" aria-hidden="true"></span></h1>
					</td>
					<td style="vertical-align: middle;">
						<span class="description">Copy and Paste this code into your web page to get the banner to be displayed.</span>
					</td>
				</tr>
			</table>
		</div>
	</td></tr>
';
$output_str = '';
$common_params = $user_account->get_list_of_common_params();
echo show_intro('', '
	<p>Invite users and get revenue from them. We pay <b>'.number_format(AFFILIATE_COMMISSION * 100, 0).'%</b> on each purchase from your referrals.</p>
	<style type="text/css" media="all">.payout_providers_logos{width:64px;height:64px;margin-right:10px;}</style>
	'.(!empty($common_params['payout_providers_logos']) ? '<p><b>We pay through:</b></p><p>'.$common_params['payout_providers_logos'].'</p>' : '' ).'
	<p>Below you can see texts and banners to attract new users. It&rsquo;s good idea to place this information on blogs, forums, and traffic exchange sites.</p>
	<p>If you don&rsquo;t know how to insert banner code into web page or blog please take look at the <a href="/acc_faqs.php#banner">FAQs</a> web page.</p>
	<p>Your URL to invite referrals:<br><strong style="cursor:pointer; border-bottom: 1px dashed;" id="ref_link" onclick="select_text_by_click(\'ref_link\');">'.$user_account->get_general_aff_link().'</strong></p>
	<p>Use this link to forward visitors from the "Drive Traffic" web sites.</p>
	', 'alert-info');

print_sorted_table(
	'banners', 
	$header, 
	array('banner_preview_box_sizeX' => $banner_preview_box_sizeX, 'banner_preview_box_sizeY' => $banner_preview_box_sizeY, 'type' => $_GET['type']),
	$row_html, 
	$rows_per_page, 
	$current_page_number, 
	$row_number, 
	$total_rows, 
	$output_str,
	'', 
	'<table class="table table-striped table-hover" cellspacing="0" cellpadding="0" border="0" style="">', //$table_tag
	'',
	'', 
	1, 
	'DESC', 
	$whole_row_html, 
	$whole_header_html,
	'&nbsp;<img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'sort_#sort_order_text_name#.png" border="0" alt="">', // $sorted_column_label_mask
	'', // $not_sorted_column_label_mask
	true, // $read_page_nmb_from_cookie
	$row_eval
);

echo '
<div class="box_type1" >
	<ul id="myTab" class="nav nav-tabs" role="tablist" >
		<li role="presentation" class="'.($_GET['type'] == 'I'?'active':'').'"><a role="tab" href="?type=I">Images</a></li>
		<!--li role="presentation" class="'.($_GET['type'] == 'H'?'active':'').'"><a role="tab" href="?type=H">HTML Texts</a></li>
		<li role="presentation" class="'.($_GET['type'] == 'T'?'active':'').'"><a role="tab" href="?type=T">Text Ads</a></li>
		<li role="presentation" class="'.($_GET['type'] == 'E'?'active':'').'"><a role="tab" href="?type=E">Emails</a></li>
		<li role="presentation" class="'.($_GET['type'] == 'R'?'active':'').'"><a role="tab" href="?type=R">Banner Rotators</a></li-->
	</ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane fade in active">
			<div id="table_div">'.(empty($output_str)?'<br><br><br><br><br><br>':$output_str).'</div>
		</div>
	</div>
</div>';
paging($current_page_number, $total_rows, $rows_per_page, $row_number);
?>

<div style="position:absolute; width:0ps; height:8px; background-color:#<?php echo COLOR3LIGHT; ?>; margin:0px; padding:0px;" id="filler"></div>
<SCRIPT language="JavaScript">

function show_hide_banner_code(banner_id)
{
	var banner_button = document.getElementById('button_' + banner_id);
	var banner_frame = document.getElementById('banner_' + banner_id);
	if ( banner_frame ) {
		if ( banner_frame.style.display == "" ) {
			banner_frame.style.display = "none";
			if ( banner_button )
				banner_button.innerHTML = 'Get Banner Code';
		}
		else {
			banner_frame.style.display = "";
			if ( banner_button )
				banner_button.innerHTML = 'Hide Banner Code';
			
			var frame_obj = document.getElementById('frame_' + banner_id);
			if ( frame_obj && frame_obj.src.indexOf('wait') > 0 ) {
				frame_obj.src = '<?php echo '/'.DIR_WS_SERVICES_DIR; ?>acc_get_banner_code.php?user=<?php echo $user_account->userid; ?>&banner=' + banner_id;
			}
		}
	}
}

function preview_banner(banner_source, banner_type)
{
	switch (banner_type) {
		case "E":
		case "H":
		case "R":
		case "T":
			show_frame_box("", banner_source);
		break;
		case "I":
			show_image_preview_box(banner_source);
		break;
	}
}
</script>

<?php
require(DIR_COMMON_PHP.'box_image_preview.php');
$show_frame_width = 700; 
$show_frame_height = 400; 
require(DIR_COMMON_PHP.'box_show_frame.php');
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
