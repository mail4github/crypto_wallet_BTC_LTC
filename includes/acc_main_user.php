<?php
if ( !$user_account->disabled && (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION ) )
	echo '	
	<script language="JavaScript">
		$.ajax({
			method: "POST",
			url: "/api/user_find_next_job",
			data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'", additional_conditions:"find_alerts" }
		})
		.done(function( ajax__result ) {
			//write_console_log(ajax__result);
		});
		
		$.ajax({
			method: "POST",
			url: "/api/user_change_rank_according_to_activity_points",
			data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'" }
		})
		.done(function( ajax__result ) {
			//write_console_log(ajax__result);
		});
	</script>
	';
$widgets = $user_account->get_list_of_widgets();
echo '
	<div class="row" style="'.(defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ? 'margin:0px;' : 'margin-top:20px;').'">
		<div class="col-sm-7">';
foreach ($widgets as $widget) {
	if ($widget->width == 2) {
		echo '<div id="widget_box_'.$widget->widgetid.'">';
		eval("?>" . $widget->code . "<?php ");
		echo '</div>';
		if (defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION )
			break;
	}
}
echo '
		</div>
		<div class="col-sm-5">';
if (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) {
	foreach ($widgets as $widget) {
		if ($widget->width < 2) {
			echo '<div class="box_type2" id="widget_box_'.$widget->widgetid.'">';
			eval("?>" . $widget->code . "<?php ");
			echo '</div>';
		}
	}
}
echo '
		</div>
	</div>';
if (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) {
	foreach ($widgets as $widget) {
		if ($widget->width == 3) {
			echo '<div id="widget_box_'.$widget->widgetid.'">';
			eval("?>" . $widget->code . "<?php ");
			echo '</div>';
		}
	}
}
?>
<script type="text/javascript">
var check_own_addr_only = "1";
</script>
