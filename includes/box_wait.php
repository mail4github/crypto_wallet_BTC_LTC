<!-- Wait Box -->  
<div class="modal fade" id="wait_box_box" role="dialog">
	<div class="modal-dialog" style="">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h2 class="modal-title"><span id="wait_title"></span></h2>
			</div>
			<div class="modal-body" >
				<div style="width:100%; height:50px;">
					<img src="/images/wait64x64.gif" width="32" height="32" border="0" alt="" style="position:absolute;margin:auto; top:0;left:0;right:0;bottom:0;" id="wait_box_image">
				</div>
				<div class="progress" style="display:none" id="wait_box_progress">
					<div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width:0%" id="wait_box_progress_value">
						<span class="sr-only">0% Complete</span>
					</div>
				</div>
				<p style="text-align:center; margin-top:40px;" id="wait_body"></p>
			</div>
			<div class="modal-footer" style="text-align:center;">
			</div>
		</div>
	</div>
</div>

<script language="JavaScript">

function hide_wait_box_box()
{
	$("#wait_box_progress").removeClass("active");
	$('#wait_box_box').modal('hide'); 
	return false;
}
 
function show_wait_box_box(message_title, message, show_progress_bar)
{
	if ( typeof(message_title) != 'undefined' )
		$("#wait_title").html(message_title);
	if ( typeof(message) != 'undefined' )
		$("#wait_body").html(message);
	if ( typeof(show_progress_bar) != 'undefined' && show_progress_bar ) {
		$("#wait_box_progress").show();
		$("#wait_box_progress").addClass("active");
		$("#wait_box_image").hide();
		$("#wait_box_progress_value").width("0%");
	}
	$('#wait_box_box').modal('show'); 
	return false;
}

function wait_box_set_progress(progress)
{
	$("#wait_box_progress_value").width(progress + "%");
}
</script>
