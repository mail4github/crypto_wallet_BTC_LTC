<?php

$showDebugMessages = defined('DEBUG_MODE');
	
if ( ! $showDebugMessages )
	error_reporting(0);
else
	error_reporting(E_ALL);
	
require('../includes/application_top.php');

$res_text = '';

global $image_name_pref;
$image_name_pref = 'blog_cover_img_';

if (!empty(@$_GET['articleid'])) {
	$_GET['articleid'] = tep_sanitize_string($_GET['articleid'], 64);
	$article_arr = get_api_value('get_article', '', ['articleid' => $_GET['articleid'], 'count' => 1] );
	if ($article_arr && count($article_arr) > 0) {
		$last_articles_arr = get_api_value('get_sorted_table', '', ['table_name' => 'articles', 'sort_column' => 0, 'sort_order' => 'DESC', 'row_number' => 0, 'current_page_number' => 0, 'max_ros' => 3]);
		$page_header = '';
		$page_title = translate_text($article_arr[0]['title']);
		$page_description = translate_text(shorter_text(convert_html2text($article_arr[0]['article']), 500));
		$site_image = convert_data_url_to_image($article_arr[0]['image'], DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $image_name_pref.strtolower($_GET['articleid']));
		require(DIR_WS_INCLUDES.'header.php');
		?>
		<style type="text/css">
			.main_text{padding:0 3em;}
			.main_text p{margin: 2em 0 1em 0;}
			ol { counter-reset: item; }
			ol li { display: block; margin: -3em 0 4em 0em; }
			.row li::before{content: counter(item) ". "; counter-increment: item; color: #005000; position: relative; max-width: 0px; max-height: 0px; left: -1.3em; top: 1.3em; font-size: 200%; font-weight: bold;}
		</style>
		<div class="row">
			<div class="col-md-8 main_text">
				<h1 style="margin-bottom: 0em;" class="invisible_on_big_screen"><?php echo translate_str('<span class="string_to_translate" >'.$article_arr[0]['title'].'</span>'); ?></h1>
				<div style="width:100%; height:280px; margin-top: 2em; background-image:url('<?php echo convert_data_url_to_image($article_arr[0]['image'], DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $image_name_pref.strtolower($_GET['articleid'])); ?>'); background-repeat:no-repeat; background-position:center; background-size:100% auto; position:relative; border-radius: <?php echo round(BOX_TYPE2_RADIUS * 1.5); ?>px;" class=""></div>
				<?php 
					echo translate_str(str_replace('<p>', '<p class="string_to_translate">', html_entity_decode($article_arr[0]['article'])), 'string_to_translate">', '</p', 'notranslate">', '</p', 'string_to_translate"  >'); 
				?>
			</div>
			<div class="col-md-4">
				<h1 style="margin: 1em 0 1em 0;" class="visible_on_big_screen"><?php echo translate_str('<span class="string_to_translate" >'.$article_arr[0]['title'].'</span>'); ?></h1>
				<p style="margin: 0 0 4em 0;" ><?php echo translate_str('<span class="string_to_translate" >'.$article_arr[0]['c_date'].'</span>'); ?></p>
				<div class="box_type2">
					<?php echo translate_str('<h2 class="string_to_translate">Recent Articles</h2>'); ?>
					<ol>
					<?php 
						foreach ($last_articles_arr['table'] as $last_article) {
							if ($last_article['c_articleid'] != $_GET['articleid']) {
								echo '
								<div class="row">
									<li class="col-md-7">
										<a href="/-/'.$last_article['c_articleid'].'/"><p>'.translate_str('<span class="string_to_translate" >'.$last_article['c_title'].'</span>').'</p></a>
										<p class="description">'.translate_str('<span class="string_to_translate" >'.$last_article['c_date'].'</span>').'</p>
									</li>
									<div class="col-md-5">
										<a href="/-/'.$last_article['c_articleid'].'/"><img src="'.convert_data_url_to_image($last_article['c_image'], DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $image_name_pref.strtolower($last_article['c_articleid'])).'" border="0" alt="" style="width:100%; height:auto; max-width: 400px; margin: 0 auto 3em auto; display: block; border-radius: 6px;"></a>
									</div>
								</div>';
							}
						}
					?>
					</ol>
				</div>
			</div>
		</div>
		<?php
	}
}
else {
	$page_header = 'News Blog';
	$page_title = $page_header;
	require(DIR_WS_INCLUDES.'header.php');
	?>
	<style type="text/css">
		.blog_title{font-size:190%; font-weight:bold;}
		
		.title_not_hover{color:#ffffff; text-shadow:none;}
		.title_hover{color:#d0f9ff; text-shadow:none;_text-shadow: 1px 1px 0px #ffffff;}
		.blog_bkg_image{position:absolute;}
		.bkg_image_size{width:100%; height:280px; }
		.bkg_image_not_hover{opacity: 0.7;animation: fade_in 1s;}
		.bkg_image_hover{opacity: 0; animation: fade_out 1s;}
		.title_link,
		.title_link:hover{color:#ffffff; text-decoration: none;}

		@keyframes fade_in {
		  from {opacity: 0;}
		  to {opacity: 0.7;}
		}
		@keyframes fade_out {
		  from {opacity: 0.7;}
		  to {opacity: 0;}
		}
	</style>
	<?php

	//$blog_cashe = 'blog_cashe';
	//if ( true || is_file_variable_expired($blog_cashe, 1) || defined('DEBUG_MODE') ) {
		include_once(DIR_COMMON_PHP.'print_sorted_table.php');
		$rows_per_page = 4;
		$current_page_number = 1;
		$total_rows = 0;

		$row_eval = '
			global $image_name_pref;
			$row["c_title"] = shorter_text($row["c_title"], 70);
			$row["c_tagline"] = shorter_text(convert_html2text(hex2bin($row["c_article"])), 500);
			$color = "#C39BD3";
			switch ($row["c_articleid"][strlen($row["c_articleid"]) - 1]) {
				case 0:
					$color = "#7FB3D5";
				break;
				case 1:
					$color = "#85C1E9";
				break;
				case 2:
					$color = "#76D7C4";
				break;
				case 3:
					$color = "#73C6B6";
				break;
				case 4:
					$color = "#7DCEA0";
				break;
				case 5:
					$color = "#82E0AA";
				break;
				case 6:
					$color = "#F7DC6F";
				break;
				case 7:
					$color = "#F8C471";
				break;
				case 8:
					$color = "#F0B27A";
				break;
				case 9:
					$color = "#F0B27A";
				break;
				default:
					$color = "#C39BD3";
			}
			$row["c_bkg_color_first"] = adjustBrightness($color, -300);
			$row["c_bkg_color_second"] = $color;
			$row["c_image"] = convert_data_url_to_image($row["c_image"], DIR_WS_TEMP_ON_WEBSITE, "/".DIR_WS_TEMP_NAME, $image_name_pref.strtolower($row["c_articleid"]));
			
			$row["c_title_locale"] = replace_with_translated_text($row["c_title"], get_selected_language(), parse_script_name());
		';
		$whole_header_html = '';
		$whole_row_html = '
			<div class="col-md-6" style="position:relative; padding:0px;">
				<div class="box_type1" style="margin:20px; padding:'.round(BOX_TYPE2_RADIUS * 1.5).'px;">
					<div style="background-image:url(\'#c_image#\'); background-repeat: no-repeat; background-position: center; background-size: 100% auto; position:relative; border-radius: '.round(BOX_TYPE2_RADIUS * 1.5).'px;" class="bkg_image_size">
						<div id="blog_bkg_image_#c_articleid#" style="background: linear-gradient(90deg, #c_bkg_color_first# 0%, #c_bkg_color_second# 100%); border-radius:'.round(BOX_TYPE2_RADIUS * 1.5).'px;" class="blog_bkg_image bkg_image_size bkg_image_not_hover"></div>
						<div style="position:absolute; width:100%; height:15em; background: transparent; padding: 1em 3em 1em 2em; overflow: hidden;">
							<a href="/-/#c_articleid#/#c_title_locale#" class="title_link" onmouseover="$(\'#blog_title_#c_articleid#\').addClass(\'title_hover\').removeClass(\'title_not_hover\'); $(\'#blog_bkg_image_#c_articleid#\').addClass(\'bkg_image_hover\').removeClass(\'bkg_image_not_hover\')" onmouseout="$(\'#blog_title_#c_articleid#\').addClass(\'title_not_hover\').removeClass(\'title_hover\'); $(\'#blog_bkg_image_#c_articleid#\').addClass(\'bkg_image_not_hover\').removeClass(\'bkg_image_hover\')">
								<p id="blog_title_#c_articleid#" class="blog_title title_not_hover string_to_translate">#c_title#</p>
							</a>
						</div>
						<div style="width:100%; max-height:3em; position:absolute; bottom:1em; left:0; padding: 0em 2em; overflow: hidden;">
							<p style="color:#ffffff; font-size:65%; box-shadow: 2px 2px 2px rgb(0 0 0 / 50%); border-radius:0; text-align: right;" class="box_type3 string_to_translate">#c_date#</p>
						</div>
					</div>
					<div style="height:15em; position:relative; overflow:hidden;">
						<div style="width:100%; max-height:11em; position:absolute; bottom:4em; left:0; padding: 1em 2em; text-align: justify; overflow: hidden;">
							<p class="string_to_translate">#c_tagline#</p>
						</div>
						<div style="width:100%; max-height:3em; position:absolute; bottom:1em; left:0; padding: 1em 2em; overflow: hidden;">
							<a href="/-/#c_articleid#/#c_title_locale#" class="btn btn-link string_to_translate">Continue reading...</a>
						</div>
						<div style="width:100%; height:11em; position:absolute; bottom:4em; left:0; box-shadow: inset 0px -20px 15px -3px rgba(255,255,255, 1);"></div>
					</div>
				</div>
			</div>
		';
		$row_html = array();
		$table_tag = '';
		$output_str = '';
		
		if ( print_sorted_table(
				'articles', 
				'', // header
				NULL, //$additional_params
				$row_html, 
				$rows_per_page, 
				$current_page_number, 
				$row_number, 
				$total_rows, 
				$output_str,
				'', // $row_html_odd
				$table_tag, 
				'class=sorted_table_cell', // $td_html
				'', // $td_html_odd
				0, // $default_sort_column
				'DESC', // $default_sort_order
				$whole_row_html, 
				$whole_header_html,
				'', // $sorted_column_label_mask
				'', //$not_sorted_column_label_mask
				false, // $read_page_nmb_from_cookie
				$row_eval,
				NULL, // $data_array
				'', // $whole_footer_html
				false, // $always_use_default_sort 
				2, // $number_of_grid_columns
				'<div class="row">', // $grid_row_prefix
				'</div>', // $grid_row_suffix
				$sort_column,
				$sort_order
				)
			)
		{
			$res_text = $res_text.$output_str;
			$res_text = $res_text.'<p>';
			$res_text = $res_text.paging($current_page_number, $total_rows, $rows_per_page, $row_number, '/blog/#pagenmb#/', false, '', '', false);
			$res_text = $res_text.'</p>';
			
		}
		
		$res_text = $res_text.'
			</div>
		</div>
		';
		update_file_variable($blog_cashe, $res_text);
	//}
	//else {
		//$res_text = get_file_variable($blog_cashe);
		//if (defined('DEBUG_MODE')) echo "********Read from File: $blog_cashe *******<br>";
	//}
}
echo $res_text;

require(DIR_WS_INCLUDES.'footer.php');

?>

<SCRIPT LANGUAGE="JavaScript">

$( document ).ready(function() {
	$(".local_time_date").each(function( index ) {
		var d = new Date();
		d.setTime($(this).attr("unix_time") * 1000);
		$(this).html(d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear());
	});
	
});

window.onbeforeunload = confirmExit;
function confirmExit() {
	$("#wait_sign").show();
}
</SCRIPT>

</body>
</html>