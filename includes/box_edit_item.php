<?php
echo '
<!-- Edit item Popup -->
'.generate_popup_code(
'edit_item', // popup_name
'
<div class="alert alert-success" style="display:none; margin-bottom:10px;" id="edit_item_alert"></div>
<h3 id="edit_item_name"></h3>
<div class="input-group" style="width:98%;" id="edit_item_group">
	<span class="input-group-addon" style="display:none;" id="edit_item_pre_addon"></span>
	<input type="text" step="any" autocomplete="off" class="form-control input-sm" name="edit_item_value" id="edit_item_value" value="" onBlur="" onKeyup="edit_item_keyup();" placeholder="">
	<span class="input-group-addon" style="display:none;" id="edit_item_post_addon"></span>
</div>
', // popup_body
'edit_item_ok();', // yes_js
'', // title
'<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Save', // button_yes_caption
'btn-danger', // button_cancel_class
'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel' // button_cancel_caption
).'
<!-- Edit HTML Popup -->
'.generate_popup_code(
'edit_HTML', // popup_name
'
<div id="resizable_html_inline_textarea" style="width:100%; height:450px; padding:6px; resize:both; overflow:auto; position: relative; background-color:#ffffff;">
	<textarea id="edit_html_inline_textarea" style="position:absolute; top:0; left:0; right:5px; bottom:5px;">></textarea>
</div>

<script src="/javascript/tinymce/js/tinymce/tinymce.min.js"></script>
<script>
tinymce.PluginManager.add("upload_image", function(editor, url) {
	editor.addButton("upload_image",
		{title       : "Upload Image",
		 image       : false,
		 icon		 : "image",
		 context	 : "insert",
		 onclick     : function() {  show_insert_image_to_html("Upload Image"); }});
});

tinymce.PluginManager.add("stylebuttons", function(editor, url) {
  ["pre", "p", "code", "h1", "h2", "h3", "h4", "h5", "h6"].forEach(function(name){
   editor.addButton("style-" + name, {
	   tooltip: "Toggle " + name,
		 text: name.toUpperCase(),
		 onClick: function() { editor.execCommand("mceToggleFormat", false, name); },
		 onPostRender: function() {
			 var self = this, setup = function() {
				 editor.formatter.formatChanged(name, function(state) {
					 self.active(state);
				 });
			 };
			 editor.formatter ? setup() : editor.on("init", setup);
		 }
	 })
  });
});

tinymce.init({
  height : "100%",
  resize: "both",
  selector: "#edit_html_inline_textarea",
  plugins: [
	"advlist autolink autosave lists charmap anchor hr link image",
	"searchreplace visualblocks visualchars code nonbreaking",
	"table contextmenu directionality textcolor paste fullpage textcolor colorpicker textpattern upload_image stylebuttons"
  ],
  statusbar : false,
  toolbar_items_size: "small",
  toolbar1: "bold italic forecolor backcolor| alignleft aligncenter alignright alignjustify | fontselect fontsizeselect | bullist numlist | table | upload_image | style-p style-h1 style-h2 style-h3 style-h4",
  menubar: "edit insert view format table tools",
  content_style: "",
  setup: function (ed) {
		ed.addMenuItem("upload_image", {
			text: "Upload Image",
			icon: "image",
			context: "insert",
			onclick: function () {
				show_insert_image_to_html("Upload Image");
			}
		});
	}
});
function resize_tinymce()
{ 
	// Main container
	var max = $(".mce-tinymce").css("border", "none").parent().outerHeight();
	// Menubar
	max += -$(".mce-menubar.mce-toolbar").outerHeight(); 
	// Toolbar
	max -= $(".mce-toolbar-grp").outerHeight(); 
	// Random fix lawl - why 1px? no one knows
	max -= 20;
	// Set the new height
	$(".mce-edit-area").height(max);
}
document.getElementById("resizable_html_inline_textarea").onmouseup = function(){ resize_tinymce(); };

</script>
', // popup_body
'edit_HTML_ok();', // yes_js
'<button type="button" id="edit_HTML_btn_top_yes" class="btn btn-success btn-xs" onClick="return edit_HTML_yes();" style="margin:0 20px 0 0; float:right; position:relative; top:-10px;">Save</button>', // title
'<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Save', // button_yes_caption
'btn-danger', // button_cancel_class
'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel' // button_cancel_caption
);
?>
<script language="JavaScript">
var now_edited = "";
var now_edited_rules = "";
var edit_item_on_ok = null;
var edit_item_on_error = null;
var timer_edit_HTML_box;
var edit_item_text_can_be_empty = false;

function show_edit_item_box(item_id, item_name, value, description, validation_rules, on_ok, on_error, input_type, pre_addon, placeholder, initial_value, post_addon, validation_pattern, item_title, yes_class, no_class, yes_caption, no_caption)
{
	now_edited = item_id;
	if (typeof validation_rules == 'undefined' ) 
		validation_rules = "";
	now_edited_rules = validation_rules;
	edit_item_on_ok = on_ok;
	edit_item_on_error = on_error;

	if (typeof value !== 'undefined' ) 
		value = hex_to_string(value);
	else {
		if ( $("#" + item_id).hasClass("editable_item_empty") ) {
			if (typeof initial_value !== 'undefined' ) 
				value = hex_to_string(initial_value);	
			else
				value = "";
		}
		else
			value = $("#" + item_id).html();
	}
	if (input_type == "number")
		value = convert_text_to_number(value);

	var s = "";
	if (typeof description !== 'undefined' ) 
		s = hex_to_string(description);
	if (s.length > 0) {
		$("#edit_item_alert").html(s);
		$("#edit_item_alert").show();
	}
	else 
		$("#edit_item_alert").hide();
	
	if (typeof input_type == 'undefined' ) 
		var input_type = "text";

	if (typeof validation_pattern != 'undefined' ) 
		$("#edit_item_value").attr("pattern", validation_pattern);

	if ( input_type == "html" )	{
		tinymce.activeEditor.execCommand("mceSetContent", false, value );
		$('#edit_HTML_box').on('hidden.bs.modal', function () {
			$(window).scrollTop( last_scrollTop );
		});
		$('#edit_HTML_box').on('show.bs.modal', function () {
			clearTimeout(timer_edit_HTML_box);
			timer_edit_HTML_box = setTimeout(function(){ resize_tinymce(); }, 1000);
		});
		
		if (typeof yes_caption !== "undefined") {
			$("#edit_HTML_btn_yes").html(yes_caption);
			$("#edit_HTML_btn_top_yes").html(yes_caption);
		}
		if (typeof no_caption !== "undefined")
			$("#edit_HTML_btn_no").html(no_caption);
		
		last_scrollTop = $(window).scrollTop();
		$(window).scrollTop( 0 );
		show_edit_HTML();
		resize_tinymce();
	}
	else {
		$("#edit_item_value").attr("type", input_type);

		if (typeof placeholder !== 'undefined' ) 
			s = hex_to_string(placeholder);
		else
			s = "";
		if (s.length > 0) {
			$("#edit_item_value").attr("placeholder", s);
		}
		else 
			$("#edit_item_value").attr("placeholder", "");
		
		if (typeof pre_addon !== 'undefined' ) 
			s = hex_to_string(pre_addon);
		else
			s = "";
		if (s.length > 0) {
			$("#edit_item_pre_addon").html(s);
			$("#edit_item_pre_addon").show();
		}
		else 
			$("#edit_item_pre_addon").hide();
		
		if (typeof post_addon !== 'undefined' ) 
			s = hex_to_string(post_addon);
		else
			s = "";
		if (s.length > 0) {
			$("#edit_item_post_addon").html(s);
			$("#edit_item_post_addon").show();
		}
		else 
			$("#edit_item_post_addon").hide();
		
		if (typeof item_name !== 'undefined' ) 
			$("#edit_item_name").html(hex_to_string(item_name));
		else
			$("#edit_item_name").html("");

		$("#edit_item_value").val(value);

		$("#edit_item_btn_yes").attr("class", "btn btn-success");
		if ( typeof yes_class !== "undefined" && yes_class != null )
			$("#edit_item_btn_yes").addClass(yes_class).removeClass("btn-success");
		
		if (typeof yes_caption !== "undefined")
			$("#edit_item_btn_yes").html(yes_caption);

		if (typeof no_caption !== "undefined")
			$("#edit_item_btn_no").html(no_caption);
		
		$("#edit_item_btn_no").attr("class", "btn btn-danger");
		if ( no_class != null ) {
			$("#edit_item_btn_no").removeClass("btn-danger");
			$("#edit_item_btn_no").addClass(no_class);
		}

		if (typeof item_title == 'undefined' ) 
			var item_title = "";

		show_edit_item(item_title);

		$("#edit_item_box").on("shown.bs.modal", function () {
			setTimeout(function() { $("#edit_item_value").focus(); }, 100);
		});
	}
	
	$("#edit_item_value").bind("keypress", function(e){
		if ( e.keyCode == 13 && $("#edit_item_box").hasClass("in") ) {
			if ( edit_item_ok() )
				hide_edit_item();
		}
	});

	return false;
}

function edit_item_keyup()
{
	if ( edit_item_validate().length > 0 )
		$("#edit_item_group").addClass("has-error");
	else
		$("#edit_item_group").removeClass("has-error");
}

function edit_item_validate(value)
{
	if (typeof value == 'undefined' ) 
		value = $("#edit_item_value").val();

	var rules = now_edited_rules.split("&");
	for (var i = 0, len = rules.length; i < len; i++) {
		rule = rules[i].split("=");
		switch(rule[0]) {
			case "min_len": 
			
			if (rule[1] == 0)
				edit_item_text_can_be_empty = true;
			
			if ( value.length < rule[1] ) {
				return "Minimum lenth is " + rule[1] + " symbols.";
			}
			break;
			case "max_len": 
			if ( value.length > rule[1] ) {
				return "Maximum lenth is " + rule[1] + " symbols.";
			}
			break;
			case "no_chars": 
			for (var j = 0, rule_len = rule[1].length; j < rule_len; j++) {
				if ( value.indexOf(rule[1][j]) >= 0 ) {
					return "Cannot contain symbol: \"" + rule[1][j] + "\"";
				}
			}
			break;
			case "min_val": 
			if ( parseFloat( value ) < rule[1] ) {
				return "Minimum value is " + rule[1] + ".";
			}
			break;
			case "max_val": 
			if ( parseFloat( value ) > rule[1] ) {
				return "Maximum value is " + rule[1] + ".";
			}
			break;
		}
	}
	return "";
}

function edit_item_ok()
{
	var validate_res = edit_item_validate();
	if (validate_res.length > 0) {
		show_message_box_box("Error", validate_res, 2);
		return false;
	}
	$("#" + now_edited).removeClass("editable_item_empty");
	if (typeof edit_item_on_ok !== 'undefined' ) {
		if ( edit_item_on_ok( $("#edit_item_value").val(), now_edited ) == "dont_close" )
			return false;
	}
	return true;
}

function edit_HTML_ok()
{
	var text = tinymce.activeEditor.getContent();
	text = get_text_between_tags(text, "<body>", "</body>").trim();

	var validate_res = edit_item_validate(text);
	if (validate_res.length > 0) {
		show_message_box_box("Error", validate_res, 2);
		return false;
	}

	if ( edit_item_text_can_be_empty || text.length > 0) {
		$("#" + now_edited).html( text );
		$("#" + now_edited).removeClass("editable_item_empty");
		if (typeof edit_item_on_ok !== 'undefined' ) {
			if ( edit_item_on_ok(text, now_edited) == "dont_close" )
				return false;
		}
		$("#edit_html_inline").hide();
	}
	else {
		show_message_box_box("Error", "Text cannot be empty", 2);
	}
}

</script>
<?php
require_once(DIR_COMMON_PHP.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
?>