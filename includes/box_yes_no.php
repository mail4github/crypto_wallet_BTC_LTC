<!-- Yes No box -->  
<div class="modal fade" id="box_yesno_box" role="dialog">
	<div class="modal-dialog" style="">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" id="btn_close_box">&times;</button>
				<h2 class="modal-title"><span id="box_yesno_title"></span></h2>
			</div>
			<div class="modal-body" >
				<table class="" style="width:100%;">
					<tr>
						<td style="text-align:right; padding:20px; display:none;" id="box_yesno_left_icon_td">
							<img src="" border="0" style="width:60px; height:60px;" id="box_yesno_left_icon">
						</td>
						<td style="padding:0 10px;">
							<span id="box_yesno_message" style=""></span>
						</td>
					</tr>
				</table>
			</div>
			<div class="modal-footer" style="text-align:center;">
				<button type="submit" class="btn btn-success" id="box_yesno_btn_yes" onClick="return box_yesno_yes();"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button>
				<button type="button" class="btn btn-link" data-dismiss="modal" id="box_yesno_btn_no"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
			</div>
		</div>
	</div>
</div>

<script language="JavaScript">

var on_box_yesno_yes_func = "";

function box_yesno_yes()
{
	$('#box_yesno_box').modal('hide'); 
	if ( on_box_yesno_yes_func.length > 0 )
		window[on_box_yesno_yes_func](); 
	return false;
}

function show_box_yesno_box(box_text, yes_func, box_title, btn_yes_caption, btn_no_caption, icon, yes_class, no_class, hex_message)
{
	try	{
		if ( hex_message && hex_message.length > 0 )
			$("#box_yesno_message").html(hex_to_string(hex_message));
		else
			$("#box_yesno_message").html(box_text);

		on_box_yesno_yes_func = yes_func;
			
		if ( box_title != null )
			$("#box_yesno_title").html(box_title);
		
		if ( btn_yes_caption != null )
			$("#box_yesno_btn_yes").html(btn_yes_caption);
		
		if ( btn_no_caption != null )
			$("#box_yesno_btn_no").html(btn_no_caption);
		if ( icon != null && $(window).width() > 992 ) {
			$("#box_yesno_left_icon").attr("src", icon);
			$("#box_yesno_left_icon_td").show();
		}
		$("#box_yesno_btn_yes").attr("class", "btn btn-success");
		if ( yes_class != null )
			$("#box_yesno_btn_yes").addClass(yes_class).removeClass("btn-success");
		$("#box_yesno_btn_no").attr("class", "btn btn-link");
		if ( no_class != null ) {
			$("#box_yesno_btn_no").removeClass("btn-link");
			$("#box_yesno_btn_no").addClass(no_class);
		}
		$('#box_yesno_box').modal('show'); 
	}
	catch(error){}	
	return false;
}
</script>
