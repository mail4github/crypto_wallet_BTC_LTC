<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$offers = array(
	array(
		'permissionid' => 'RGREPC',
		'image' => '/images/regional_reps_512x512.png',
		'position' => 'Regional Sales Representative',
		'description' => 'The Regional Representative plays a critical role for our organization. As our Regional Sales Representative, you will be responsible for the sales of our products in your area.',
		'duties' => '<ul>
						<li>Perform formal sales presentations.</li>
						<li>Answer the pre-sale questions.</li>
						<li>Maintain a solid and professional working relationship with customers.</li>
					</ul>',
	),
	array(
		'permissionid' => 'MRKMNC',
		'image' => '/images/marketing-icon300x300.png',
		'position' => 'Marketing Manager',
		'description' => 'Internet marketing manager deals with social networking and search engine optimization to maintain a client base and seek new clients.</p>',
		'duties' => '<ul>
						<li>Creating and implementing online marketing strategies to increase sales.</li>
						<li>Maximize creative opportunities afforded by the breadth of social marketing in order to attract customers.</li>
						<li>Design and development advertising.</li>
					</ul>',
		'salary' => 'up to $2000',
	),
);

if ( !empty($_POST['apply']) ) {
	if ( !$user_account->is_loggedin() ) {
		echo generate_answer(0, 'Error: please login.');
		exit;
	}
	$found = false;
	foreach ( $offers as $offer ) {
		if ( $offer['permissionid'] == $_POST['apply'] ) {
			$found = true;
			break;
		}
	}
	if ( !$found ) {
		echo generate_answer(0, 'Error');
		exit;
	}
	$message = $user_account->add_permission($_POST['apply']);
	if ( !empty($message) )
		echo generate_answer(0, $message);
	else
		echo generate_answer(1, 'Congratulations!!! You have been approved. In a short time you will receive a message with further instructions.');
	sleep(5);
	exit;
}

$page_header = 'Job Opportunities';
$page_title = $page_header;
require(DIR_WS_INCLUDES.'header.php');

echo '
'.(!empty($_GET['apply']) && $user_account->is_loggedin()?
	'
	<SCRIPT language="JavaScript">
	$( document ).ready(function() {
		show_wait_box_box("Applying. Please wait...");

		$.ajax({
			method: "POST",
			url: "'.$_SERVER['SCRIPT_NAME'].'",
			data: { apply: "'.tep_sanitize_string($_GET['apply'], 20).'" }
		})
		.done(function( ajax__result ) {
			try
			{
				hide_wait_box_box();
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					show_message_box_box("Approved", arr_ajax__result["message"], 1, "", "go_to_home");
				}
				else
					show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
			catch(error){'.(!empty($_COOKIE['debug'])?'alert(error);':'').'}
		});
	});
	
	function go_to_home()
	{
		window.location.assign("/");
	}

	</SCRIPT> 
	'
	:'
	'
).'
<div class="row box_type2" style="padding:10px 0 10px 0; margin:10px 5px 10px 15px;">
	<div class="col-md-6">
		<h2>Join the team that is changing the world!</h2>
		<p>We are doing innovations that are never been done before. What we are doing together has a big impact on society in all kinds of ways. We are looking for team members who share our belief that with innovative technology and with revolutionary thinking we can build our prosperity.</p>
		<p>We are always searching for dynamic individuals to join our team. View our available positions, and apply now.</p>
		<br><br>
	</div>
	<div class="col-md-6">
		'.(defined('SITE_IMAGE') && SITE_IMAGE != ''?'<img src="'.SITE_IMAGE.'" class="img-responsive" alt="" style="display:inline-block; margin:0 auto 0 auto;">':'').'
	</div>
</div>
';
$number_of_columns = 2;
$grid_columns = round(12 / $number_of_columns);
$col_count = 0;
$offers_grid = '
<div class="row offer_row">
';
foreach ( $offers as $offer ) {
	$offers_grid = $offers_grid.'
	<div class="col-md-'.$grid_columns.'" style="">	
		<div class="row">
			<div class="col-md-2" style="">
				<div style="max-width:100px; margin:0 auto 0 auto;">
					<img src="'.$offer['image'].'" class="img-responsive" alt="" style="display:inline-block; margin:0 auto 0 auto; ">
				</div>	
			</div>
			<div class="col-md-10" style="">
				<h2>'.$offer['position'].'</h2>
				<p><strong>Position Summary</strong></p>
				<p>'.$offer['description'].'</p>
				<p><strong>Major Duties and Responsibilities</strong></p>
				'.$offer['duties'].'
				'.(!empty($offer['salary'])?'<p><b>Salary: '.$offer['salary'].'</b></p>':'').'
			</div>
		</div>
		<div style="width:100%; height:50px;">
		</div>
		<div style="width:100%; height:40px; text-align:center; position:absolute; bottom:10px; left:0;">
			<button class="btn btn-info" style="display:block; margin:0 auto 0 auto;" onclick="signup_for_job(\''.$offer['permissionid'].'\'); return false;">Apply Now...</button>
		</div>
	</div>
	';
	$col_count++;
	if ($col_count >= $number_of_columns ) {
		$offers_grid = $offers_grid.'</div><div class="row offer_row">';
		$col_count = 0;
	}
}
$offers_grid = $offers_grid.'</div>';
echo $offers_grid;
echo '
<SCRIPT language="JavaScript"> 

if ( $(window).width() > 750 && navigator.userAgent.indexOf("Opera") < 0 )
	$(".offer_row").addClass("row-eq-height");

function signup_for_job(job_id)
{
'.(!$user_account->is_loggedin()?
	'
	location.assign("/signup");
	'
	:'
	location.assign("/careers.php?apply=" + job_id);
	'
	).'
}
</SCRIPT> 
';
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_wait.php');
require_once(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>
