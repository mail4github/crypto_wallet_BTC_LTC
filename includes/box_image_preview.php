<!-- Image Preview -->  
<div class="modal fade" id="image_preview_box" role="dialog">
	<div class="modal-dialog" style="<?php echo (isset($image_preview_box_width)?'width:'.$image_preview_box_width:''); ?>">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h5 class="modal-title"><span id="image_preview_title"></span></h5>
			</div>
			<div class="modal-body" style="">
				<img border="0" src="/images/wait64x64.gif" alt="Click to close" class="img-responsive" style="margin-left:auto; margin-right:auto;" id="preview_image" onClick="hide_image_preview_box(); return false;">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script language="JavaScript"> 
function hide_image_preview_box()
{
	document.getElementById("preview_image").src = '/images/wait64x64.gif';
	$('#image_preview_box').modal('hide');
	return false;
}

function show_image_preview_box(image_file)
{
	$('#image_preview_box').modal('show');
	document.getElementById("preview_image").src = image_file;
}

</script>
