<?php
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'Frequently Asked Questions';
$page_title = $page_header;
$show_top_images = false;
require(DIR_WS_INCLUDES.'header.php');
echo '
<script src="/javascript/QRCode.js" type="text/javascript"></script>
<div class="panel-group" id="accordion">';
if ( $user_account->is_loggedin() || is_file_variable_expired('faqs', 60) || (!empty($_COOKIE['googtrans']) && $_COOKIE['googtrans'] != '/auto/en') || defined('DEBUG_MODE') ) {
	$out_str = '';
	$j = 1;
	$last_subject_title = '';
	$faqs = get_api_value('get_faqs', '', '', '', $user_account);
	foreach ( $faqs as $row ) {
		if ($last_subject_title != $row['title'] ) {
			$last_subject_title = $row['title'];
			$out_str = $out_str.translate_str('<h2 style="margin-top:20px;" class="string_to_translate">'.$row['title'].'</h2>');
		}
		$out_str =  $out_str.
		'<div class="panel panel-default">
		  <div class="panel-heading">
			<h3 class="panel-title">
			  '.translate_str('<a data-toggle="collapse" data-parent="#accordion" href="#'.(empty($row['anchor_name'])?'collapse'.$j.'':$row['anchor_name']).'" class="string_to_translate">'.make_synonyms($row['question'], 3, '', '', true).'</a>').'
			</h3>
		  </div>
		  <div id="'.(empty($row['anchor_name'])?'collapse'.$j.'':$row['anchor_name']).'" class="panel-collapse collapse">
			<div class="panel-body">'.translate_str('<span class="string_to_translate" >'.make_synonyms($row['answer'], 3, '', '', true).'</span>').'</div>
		  </div>
		</div>'."\r\n";
		$j++;
	}
	$common_params = $user_account->get_list_of_common_params(false);
	foreach($common_params as $code => $value)
		$out_str = str_ireplace( '{$'.$code.'}', $value, $out_str );
	
	foreach($common_params as $code => $value)
		$out_str = str_ireplace( '{$'.$code.'}', $value, $out_str );
	
	if ( !$user_account->is_loggedin() && (empty($_COOKIE['googtrans']) || $_COOKIE['googtrans'] == '/auto/en') )
		update_file_variable('faqs', $out_str);
	echo $out_str;
}
else {
	echo get_file_variable('faqs');
}
echo '</div>';
require(DIR_WS_INCLUDES.'footer.php');
?>
<script type="text/javascript">
	var finish_translation_timer = 0;
	
	var there_is_need_to_translate = false;
	$(".string_to_translate").each(function( index ) {
		var str_val = $(this).text();
		if (str_val.length > 0)
			there_is_need_to_translate = true;
	});
	if (there_is_need_to_translate) {
		$(".collapse").each(function() {
			$(this).css("display", "block");
		});
	}
	$(document).ready(function () {
		if(location.hash != null && location.hash != ""){
			$('.collapse').removeClass('in');
			$(location.hash + '.collapse').collapse('show');
			setTimeout(function() { $(window).scrollTop($(window).scrollTop() - <?php echo LOGO_IMG_HEIGHT; ?>); }, 500);
		}
		var checked_finish_translation_times = 0;
		finish_translation_timer = setInterval(function() { // Make delay to give the translator time to finish translation
			var googtrans = get_cookie('googtrans');
			var lang_val = $(".goog-te-combo").val();
			if (typeof lang_val == "undefined" || lang_val == null)
				lang_val = "";
			if ( googtrans.length == 0 || lang_val.length > 0 || checked_finish_translation_times > 100) {
				clearInterval(finish_translation_timer);
				setTimeout(function() { 
					$(".collapse").each(function () {
						$(this).css("display", "");
					});
				}, 100);
			}
			checked_finish_translation_times++;
		}, 100);
	});
</script>
</body>
</html>