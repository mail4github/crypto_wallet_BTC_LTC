<!-- Message box -->  
<div class="modal fade" id="message_box_box" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close close_dlg_btn" data-dismiss="modal"></button>
				<p class="modal-title"><b><span id="message_title"></span></b></p>
				
			</div>
			<div class="modal-body">
				<div class="alert " role="alert" id="alert_panel">
					<p id="message_body" style="padding-left:4px;"></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>

<script language="JavaScript">

var on_message_box_hide_func = '';
var on_message_box_hide_script_to_run = "";

function show_message_box_box(message_title, message, icon_number /* 0 - no icon, 1 - Ok icon, 2 - error icon */, hex_message, close_func, close_script_to_run) 
{
	try	{
		$("#message_title").html(message_title);
		if ( hex_message && hex_message.length > 0 )
			$("#message_body").html(hex_to_string(hex_message));
		else
			$("#message_body").html(message);

		<?php if ( defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true' ) { ?>
			if ($("#message_body").html().indexOf("string_to_translate") < 0 && $("#message_body").html().indexOf("notranslate") < 0 ) {
				$("#message_body").html(`<span class="string_to_translate">` + $("#message_body").html() + `</span>`);
			}
		<?php } ?>

		if ( typeof close_func !== "undefined" )
			on_message_box_hide_func = close_func;

		if ( typeof close_script_to_run !== "undefined" )
			on_message_box_hide_script_to_run = close_script_to_run;

		switch ( icon_number )
		{
			case 0: 
				$("#alert_panel").css("padding", "0px");
				$("#alert_panel").css("margin", "0px");
				$("#message_body").css("padding", "0px");
			break;
			case 2: 
				$("#alert_panel").addClass("alert-danger");
				$("#alert_panel").removeClass("alert-success");
			break;
			case "warning": 
				$("#alert_panel").addClass("alert-warning");
				$("#alert_panel").removeClass("alert-success");
			break;
			case "danger": 
				$("#alert_panel").addClass("alert-danger");
				$("#alert_panel").removeClass("alert-success");
			break;
			default:
				$("#alert_panel").addClass("alert-success");
				$("#alert_panel").removeClass("alert-danger");
		}
		$('#message_box_box').on('hide.bs.modal', function (e) {
			if ( on_message_box_hide_func.length > 0 )
				window[on_message_box_hide_func](); 
			on_message_box_hide_func = "";

			if ( on_message_box_hide_script_to_run.length > 0 )
				eval(on_message_box_hide_script_to_run); 
			on_message_box_hide_script_to_run = "";

			<?php global $on_message_box_hide_perform_java; echo (isset($on_message_box_hide_perform_java) && !empty($on_message_box_hide_perform_java)?$on_message_box_hide_perform_java:''); ?>
		})
		$('#message_box_box').modal('show');
	}
	catch(error){}	
	return false;	
}
</script>
<?php //
if ( empty($box_message) && !empty($_POST['box_message']) )
	$box_message = $_POST['box_message'];
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<script language="JavaScript">show_message_box_box("Error", `'.$box_message.'`, 2);</script>'."\r\n";
	else {
		echo '<script language="JavaScript">show_top_alert(`'.$box_message.'`, "'.(isset($box_message_color) && !empty($box_message_color)?$box_message_color:'alert-success').'");</script>'."\r\n";
	}
}
?>