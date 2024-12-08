<?php 
if ( empty($show_frame_width) )
	$show_frame_width = 900; 
if ( empty($show_frame_height) )
	$show_frame_height = 400; 
?>

<!-- Frame box -->  
<div class="modal fade" id="show_frame_box" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" id="btn_close_box">&times;</button>
				<h5 class="modal-title"><span id="frame_title"></span></h5>
			</div>
			<div class="modal-body">
				<iframe name="frame_body" id="frame_body" height="<?php echo $show_frame_height; ?>" width="100%" src="/acc_banner_code_wait.html" frameborder="0"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script language="JavaScript">
var on_show_frame_hide_func = '';
function hide_frame_box()
{
	$('#show_frame_box').modal('hide');
	
	if ( on_show_frame_hide_func.length > 0 )
		window[on_show_frame_hide_func](); 
	return false;
}
 
function show_frame_box(frame_title, frame_src, hide_close_button, width, height)
{
	document.getElementById("frame_title").innerHTML = frame_title;
	document.getElementById("frame_body").src = frame_src;
	var box_height = <?php echo $show_frame_height; ?>;
	if (typeof height != 'undefined')
		box_height = height;
	document.getElementById("frame_body").height = box_height;
	if ( hide_close_button )
		document.getElementById("btn_close_box").style.display="none";
	$('#show_frame_box').modal('show');
	return false;
}
</script>
