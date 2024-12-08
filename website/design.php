<?php
$dont_read_design_data = 1;
$get_list_of_common_params = true;
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');

require_once(DIR_WS_INCLUDES.'box_protected_page.php');

function write_design_window_header($id)
{
	return '
	<div class="panel_design notranslate" id="design_panel_'.$id.'" style="position:absolute; width:700px;">
		<div style="position:relative; height:0px; width:100%;">
			<div style="position:absolute; height:0px; width:0px; right:4px; top:-6px;">
				<a href="#" title="Close" 
					onMouseOver="document.images[\'design_panel_'.$id.'_box_button_close_small\'].src = \'/images/button_close_small_h.png\';"
					onMouseOut="document.images[\'design_panel_'.$id.'_box_button_close_small\'].src = \'/images/button_close_small.png\';"
					onClick = "return hide_design_panel(\'design_panel_'.$id.'\');"><img 
					src="/images/button_close_small.png" border="0" alt="Close" 
					name="design_panel_'.$id.'_box_button_close_small"
					style=""></a>
			</div>
			<div style="position:absolute; height:0px; width:0px; right:50px; top:10px;">
				<img src="/images/design_'.$id.'.png" width="48" height="48" border="0">
			</div>
		</div>
	';
}

function write_design_window_footer($id)
{
	return '
			<div align="center" style="margin-top:20px;">
				<button type="button" class="btn btn-primary" onclick = "save_design();">Ok</button>
				<button type="button" class="btn btn-danger" onclick = "return hide_design_panel(\'design_panel_'.$id.'\');">Cancel</button>
			</div>
			<div style="display:none; height:70px;" id="panel_'.$id.'_wait">
			<div style="position:absolute; left:50%; width:16px; margin-left:-8px; top:50%; height:16px; margin-top:-8px; ">
				<img src="/images/wait64x64.gif" width="16" height="16" border="0" alt="">
			</div>
		</div>
	</div>
	';
}
function write_design_upload_file($name, $value, $max_width = '')
{
	return '
	<div class="input-group input-group-sm" style="'.(!empty($max_width)?'max-width:'.$max_width.';':'').'">
		<input type="text" class="form-control" id="_input_'.$name.'" value="'.constant($value).'">
		<span class="input-group-btn">
			<form action="" enctype="multipart/form-data" method="post" style="display:inline-block;">
				<button class="btn btn-info btn-sm" id="upload_photo_'.$name.'" style="cursor:pointer; position:relative; top:-1px; left:-3px; border-top-right-radius:4px; border-bottom-right-radius:4px; border-top-left-radius:0px; border-bottom-left-radius:0px; "><span class="glyphicon glyphicon-upload" aria-hidden="true"></span></button>
				<img src="/images/wait64x64.gif" width="20" height="20" border="0" id="wait_image_upload_'.$name.'" alt="" style="position:relative; left:0px; top:6px; display:none; ">
				<input type="file" name="'.$name.'" size="1"
					style="cursor:pointer; font-size:10px; width:100px; height:20px; position:absolute; right:0px; top:0px; opacity:0; filter:alpha(opacity = 0);" 
					onchange="return upload_image(this.form, \'_'.$name.'\');" >
			</form>
		</span>
	</div>
	';
}

function write_width_and_color($width_name, $width_value, $color_name, $color_value)
{
	return '
	<table><tr>
		<td style="vertical-align:top;">'.show_incremented_value('', '_input_'.$width_name, $width_value, 140, '', '', '', '', 'any', '', '', '', '').'</td>
		<td style="padding:0 6px 0 8px;">px</td>
		<td style="vertical-align:top;"><input class="color form-control input-sm" style="" id="_color_'.$color_name.'" value="'.$color_value.'"></td>
	</tr></table>';
}

$use_html_window_in_design = defined('USE_HTML_WINDOW_IN_DESIGN') && USE_HTML_WINDOW_IN_DESIGN;
$tmp_dada_file = '$$$design_tmp.ini';
$dada_file = '$$$design.ini';

if ( !empty($_POST['restore_design']) ) {
	if ( !unlink(DIR_DATA.$tmp_dada_file) )
		echo 'Error: cannot copy file.';
	if ( !copy(DIR_DATA.$dada_file, DIR_DATA.$tmp_dada_file) )
		echo 'Error: cannot copy file.';
	exit;
}

if ( isset($_FILES['text_font_family']) ) {
	$uploaded_file = $_FILES['text_font_family'];
	$variable_name = 'TEXT_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}

if ( isset($_FILES['paragraph_image_1']) ) {
	$uploaded_file = $_FILES['paragraph_image_1'];
	$variable_name = 'PARAGRAPH_IMAGE_1';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['paragraph_image_2']) ) {
	$uploaded_file = $_FILES['paragraph_image_2'];
	$variable_name = 'PARAGRAPH_IMAGE_2';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['paragraph_image_3']) ) {
	$uploaded_file = $_FILES['paragraph_image_3'];
	$variable_name = 'PARAGRAPH_IMAGE_3';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['paragraph_image_4']) ) {
	$uploaded_file = $_FILES['paragraph_image_4'];
	$variable_name = 'PARAGRAPH_IMAGE_4';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['background_image']) ) {
	$uploaded_file = $_FILES['background_image'];
	$variable_name = 'BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['logo_background_image']) ) {
	$uploaded_file = $_FILES['logo_background_image'];
	$variable_name = 'LOGO_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:no-repeat; background-size: 100%;';
}
if ( isset($_FILES['menu_background_image']) ) {
	$uploaded_file = $_FILES['menu_background_image'];
	$variable_name = 'MENU_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['menu_btn_background_image']) ) {
	$uploaded_file = $_FILES['menu_btn_background_image'];
	$variable_name = 'MENU_BTN_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:no-repeat;';
}
if ( isset($_FILES['h1_background_image']) ) {
	$uploaded_file = $_FILES['h1_background_image'];
	$variable_name = 'H1_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['H2_background_image']) ) {
	$uploaded_file = $_FILES['H2_background_image'];
	$variable_name = 'H2_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['footer_background_image']) ) {
	$uploaded_file = $_FILES['footer_background_image'];
	$variable_name = 'FOOTER_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['slider_image1']) ) {
	$uploaded_file = $_FILES['slider_image1'];
	$variable_name = 'SLIDER_IMAGE1';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['slider_image2']) ) {
	$uploaded_file = $_FILES['slider_image2'];
	$variable_name = 'SLIDER_IMAGE2';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['slider_image3']) ) {
	$uploaded_file = $_FILES['slider_image3'];
	$variable_name = 'SLIDER_IMAGE3';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['slider_image4']) ) {
	$uploaded_file = $_FILES['slider_image4'];
	$variable_name = 'SLIDER_IMAGE4';
	$image_value = basename($uploaded_file['name']);
}
if ( isset($_FILES['h1_font_family']) ) {
	$uploaded_file = $_FILES['h1_font_family'];
	$variable_name = 'H1_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}
if ( isset($_FILES['h2_font_family']) ) {
	$uploaded_file = $_FILES['h2_font_family'];
	$variable_name = 'H2_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}
if ( isset($_FILES['logo_container_background_image']) ) {
	$uploaded_file = $_FILES['logo_container_background_image'];
	$variable_name = 'LOGO_CONTAINER_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x; background-position: center bottom;';
}
if ( isset($_FILES['box_type1_background_image']) ) {
	$uploaded_file = $_FILES['box_type1_background_image'];
	$variable_name = 'BOX_TYPE1_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat;';
}
if ( isset($_FILES['BOX_TYPE2_background_image']) ) {
	$uploaded_file = $_FILES['BOX_TYPE2_background_image'];
	$variable_name = 'BOX_TYPE2_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat;';
}
if ( isset($_FILES['box_type3_background_image']) ) {
	$uploaded_file = $_FILES['box_type3_background_image'];
	$variable_name = 'BOX_TYPE3_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat;';
}
if ( isset($_FILES['hr_background_image']) ) {
	$uploaded_file = $_FILES['hr_background_image'];
	$variable_name = 'HR_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:repeat-x;';
}
if ( isset($_FILES['box_type2_font_family']) ) {
	$uploaded_file = $_FILES['box_type2_font_family'];
	$variable_name = 'BOX_TYPE2_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}

if ( isset($_FILES['box_type3_font_family']) ) {
	$uploaded_file = $_FILES['box_type3_font_family'];
	$variable_name = 'BOX_TYPE3_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}

if ( isset($_FILES['site_icon']) ) {
	$uploaded_file = $_FILES['site_icon'];
	$variable_name = 'SITE_ICON';
	$image_value = '/'.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']);
}
if ( isset($_FILES['site_image']) ) {
	$uploaded_file = $_FILES['site_image'];
	$variable_name = 'SITE_IMAGE';
	$image_value = '/'.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']);
}
if ( isset($_FILES['bullets_background_image']) ) {
	$uploaded_file = $_FILES['bullets_background_image'];
	$variable_name = 'BULLETS_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:no-repeat;';
}
if ( isset($_FILES['checkboxes_background_image']) ) {
	$uploaded_file = $_FILES['checkboxes_background_image'];
	$variable_name = 'CHECKBOXES_BACKGROUND_IMAGE';
	$image_value = 'url('.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']).'); background-repeat:no-repeat;';
}
if ( isset($_FILES['upload_file']) ) {
	$uploaded_file = $_FILES['upload_file'];
	$variable_name = 'UPLOAD_FILE';
	$image_value = '/'.DIR_WEBSITE_FOLDER.DIR_WS_TEMP_IMAGES_NAME.basename($uploaded_file['name']);
}
if ( isset($_FILES['buttons_font_family']) ) {
	$uploaded_file = $_FILES['buttons_font_family'];
	$variable_name = 'BUTTONS_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}
if ( isset($_FILES['footer_font_family']) ) {
	$uploaded_file = $_FILES['footer_font_family'];
	$variable_name = 'FOOTER_FONT_FAMILY';
	$image_value = CUSTOM_FONT_PREFIX.' '.get_text_between_tags(basename($uploaded_file['name']), '', '.');
}
if ( isset($uploaded_file) ) {
	if ( $uploaded_file['error'] > 0 ) {
		switch ( $uploaded_file['error'] ) {
			case 1: 
			case 2: $box_message = 'Error: File too big.'; break;
			case 3: $box_message = 'Error: The uploaded file was only partially uploaded.'; break;
			case 4: $box_message = 'Error: No file was uploaded.'; break;
			default: $box_message = 'Error: #'.$uploaded_file['error']; break;
		}
	}
	else {
		$allowed = array(
			'jpeg',
			'jpg',
			'pjpeg',
			'png',
			'gif',
			'ico',
			'woff',
			'js',
			'php',
			'svg',
		);
		$valid_file = false;
		$extension = '';
		foreach ($allowed as $value) {
			if ( is_integer( strpos(strtolower($uploaded_file['name']), $value) ) ) {
				$valid_file = true;
				$extension = strtolower($value);
				break;
			}
		}
		if ( $valid_file ) {
			$target_file_path = DIR_WS_TEMP_IMAGES.basename($uploaded_file['name']);
			move_uploaded_file($uploaded_file['tmp_name'], $target_file_path);
			$s = file_get_contents(DIR_DATA.$tmp_dada_file);
			$strings = explode( "\r\n", $s );
			$param_has_been_found = 0;
			// update a variable with the name of uploaded file
			for ($i = 0; $i < count($strings); $i++) {
				$string = $strings[$i];
				$item_name = substr($string, 0, strpos($string, '='));
				$item_value = substr($string, strpos($string, '=') + 1);
				if ( $item_name == $variable_name ) {
					$strings[$i] = $item_name.'='.$image_value;
					$param_has_been_found = 1;
					break;
				}
			}
			if ( !$param_has_been_found )
				$strings[] = $variable_name.'='.$image_value;
			$outstr = '';
			foreach( $strings as $string )
				$outstr = $outstr.$string."\r\n"; 
			
			$result = file_put_contents(DIR_DATA.$tmp_dada_file, $outstr);

			// Save uploaded file to DB
			$s = file_get_contents($target_file_path);
			get_api_value('admin_save_file', '', array('file_name' => basename($uploaded_file['name']), 'file_body' => base64_encode($s)) );
		}
		else 
			$box_message = 'Error: Please upload valid file: '.make_enumeration($allowed);
	}
}

if ( !file_exists(DIR_DATA.$tmp_dada_file) ) {
	// restore design file from db
	$data = get_api_value('restore_file_from_db', '', array('file_name' => '$$$design.ini'));
	$data['file_body'] = base64_decode($data['file_body']);
	if ( $data && !empty($data['file_body']) )
		file_put_contents(DIR_DATA.$tmp_dada_file, $data['file_body']);
}
if ( file_exists(DIR_DATA.$tmp_dada_file) ) {
	$s = file_get_contents(DIR_DATA.$tmp_dada_file);
	$strings = explode( "\r\n", $s );
	foreach( $strings as $string ) {
		$item_name = substr($string, 0, strpos($string, '='));
		$item_value = substr($string, strpos($string, '=') + 1);
		define($item_name, $item_value);
	}
}

function save_design_to_file($file_name)
{
	$outstr = '';
	foreach ($_POST as $key => $value ) {
		if ( empty($key) || $key == 'save_design' )
			continue;
		$value = hex32_2bin($value);
		$value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
		$outstr = $outstr.$key.'='.$value."\r\n"; 
	}
	file_put_contents(DIR_DATA.$file_name, $outstr);
}

if ( !empty($_POST['save_tmp_design']) ) {
	save_design_to_file($tmp_dada_file);
	exit;
}

if ( !empty($_POST['save_design']) ) {
	copy(DIR_DATA.$tmp_dada_file, DIR_DATA.$dada_file);
	
	// Save design to DB
	$s = file_get_contents(DIR_DATA.$dada_file);
	get_api_value('admin_save_file', '', array('file_name' => basename(DIR_DATA.$dada_file), 'file_body' => base64_encode($s)) );

	exit;
}

$page_title = SITE_TITLE;
$page_description = SITE_DESCRIPTION;
$dont_show_banner = '1';
$parent_page = 'index.php';
$show_balance = 1;
require(DIR_WS_INCLUDES.'header.php');
?>
<style type="text/css">
.long_input{width:400px;}
.form-control{font-size: 12px; font-family: arial; font-weight:normal;}

</style>
<?php
if ( !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}

$mobile_device = 1;
include(DIR_WS_INCLUDES.'first_page_body.php');
?>
<script src="/javascript/ace-builds-master/ace.js" type="text/javascript" charset="utf-8"></script>
<ul class="ul">
<li>Li 1</li>
<li>Li 2.</li>
</ul>

<ul class="ol">
<li>Check box 1</li>
<li>Check box 2.</li>
</ul>

<hr>
<h1>H1 header <span style="font-size:12px;">(border-bottom: COLOR2DARK)</span> <b>bold</b></h1>
<h2>H2 header <b>bold</b></h2>
<h3>H3 header <b>bold</b></h3>
<h4>H4 header (color: COLOR1DARK) <b>bold</b></h4>
<h5>H5 header (color: COLOR3DARK) <b>bold</b></h5>
<h6>H6 header (color: COLOR1DARK) <b>bold</b></h6>

<label class="control-label" for="password">Input:</label>
<div class="row" style="">
		<div class="col-md-8" style="" >
			<input type="text" name="password" id="password" class="form-control" placeholder="hover: COLOR1BASE, active, border-color:COLOR3BASE">			
		</div>
</div>
<br>
<div class="row" style="margin-bottom:20px;">
	<div class="col-md-12" style="" >
		<table class="payment_option_table payment_option_table_selected" style="display:block;">
		<tr>
			<td style="">
				<img src="/images/visa_mc.png" border="0" style="margin-top:0px; margin-bottom:0px; margin-right:5px; cursor:pointer; width:50px; height:50px;">
				<span class="description" style="cursor:pointer;">COLOR1LIGHT</span>
			</td>
		</tr>
		</table>
	</div>
</div>
<?php
echo show_intro('/'.DIR_WS_WEBSITE_IMAGES_DIR.'sign_ok.png', 'This is example of image and example of TOP INTRO PANEL.', 'alert-info');
?>
<br>
<div class="row" style="">
	<div class="col-md-4" style="padding:4px;" >
		<div class="box_type1" style="height:400px;" >
			<h1>Example of H1</h1>
			<h2>Example of H2</h2>
			Example of <br>the class <br> box_type1<br><br>
			<p class="description">class="description" color: COLOR1DARK</p>
			<br><a href="#" class="write_new_topic">class: write_new_topic, color: COLOR3BASE</a>
			
		</div>
	</div>
	<div class="col-md-4" style="padding:4px;" >
		<div class="box_type2" style="height:400px;" >
			<h1>Example of H1</h1>
			<h2>Example of H2</h2>
			Example of <br>the class <br> box_type2<br><br><br><br>
		</div>	
	</div>
	<div class="col-md-4" style="padding:4px;" >
		<div class="box_type3" style="height:400px;" >
			<h1>Example of H1</h1>
			<h2>Example of H2</h2>
			Example of <br>the class <br> box_type3<br><br><br><br>
		</div>
	</div>
</div>

<br>
<button class="btn btn-primary" style="">btn-primary</button>
<button class="btn btn-primary btn-xs" style="">btn-primary btn-xs</button>
<br><br>
<button class="btn btn-success" style="">btn-success</button>
<button class="btn btn-success btn-xs" style="">btn-success btn-xs</button>
<br><br>
<button class="btn btn-info" style="">btn-info</button>
<button class="btn btn-info btn-xs" style="">btn-info btn-xs</button>
<br><br>
<button class="btn btn-warning" style="">btn-warning</button>
<button class="btn btn-warning btn-xs" style="">btn-warning btn-xs</button>
<br><br>
<button class="btn btn-danger" style="">btn-danger</button>
<button class="btn btn-danger btn-xs" style="">btn-danger btn-xs</button>
<button class="btn btn-danger btn-xs" style=""><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>


<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
if ( !empty($box_message) ) {
	if ( is_integer(strpos($box_message, 'Error:')) )
		echo '<SCRIPT language="JavaScript"> 
		show_message_box_box("Error", "'.$box_message.'", 2); 
		</SCRIPT> '."\r\n";
	else {
		echo '
		<SCRIPT language="JavaScript"> 
		show_message_box_box("Success", "'.$box_message.'", 1);
		</SCRIPT>
		'."\r\n";
	}
}
?>
<style type="text/css" media="all">

.panel_design{text-align:left; font-family:arial; font-size:12px; line-height:18px; font-weight:normal; color:#000000; 
padding:10px;
display:none; position:absolute; top:30px; right:-230px; width:200px; z-index:10000;
border: 1px solid #aaaaff;
-moz-border-radius: 5px; border-radius: 5px;
background: #ffffff;
-moz-box-shadow: 3px 3px 3px #ccc;
-webkit-box-shadow: 3px 3px 3px #ccc;
box-shadow: 3px 3px 3px #ccc;
z-index:10001;
}
.color{width:80px; margin-left:10px; font-size:12px; display:inline-block;}

.panel_design input{display: inline; width: auto; background: none; border: none; border-radius: 0; height: auto; border: 1px solid #e0e0e0;}
.panel_design img{max-width: initial;}

</style>

<script src="/javascript/jscolor/jscolor.js"></script>

<div id="design_panel_buttons" style="position:absolute; left:20px; top:20px; width:120px; height:400px; padding:7px;
-moz-border-radius:5px; border-radius:5px;
border:1px solid #888888;
background-image:url(/images/design_stick_panel_bkg.png); background-repeat:repeat; background-position:0px 0px;
z-index:10000;
">
	<img src="/images/design_background.png" width="48" height="48" border="0" title="Background" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_background');">
	<img src="/images/design_palette.png" width="48" height="48" border="0" title="Color Palette" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_palette');">
	<img src="/images/design_logo.png" width="48" height="48" border="0" title="Logo" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_logo');">
	<img src="/images/design_menu.png" width="48" height="48" border="0" title="Menu" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_menu');">
	<img src="/images/design_rectangle.png" width="48" height="48" border="0" title="Central Table" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_rectangle');">
	<img src="/images/design_H1.png" width="48" height="48" border="0" title="H1 tag" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_H1');">
	<img src="/images/design_H2.png" width="48" height="48" border="0" title="H2 tag" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_H2');">
	<img src="/images/design_footer.png" width="48" height="48" border="0" title="footer tag" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_footer');">
	<img src="/images/design_bullets.png" width="48" height="48" border="0" title="Bullets" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_bullets');">
	<img src="/images/design_content.png" width="48" height="48" border="0" title="Content" style="margin-bottom:4px; cursor:pointer;" onclick = "show_design_panel('design_panel_content');">
	<!--img src="/images/design_random.png" width="48" height="48" border="0" title="Pick Up Random Design" style="margin-bottom:4px; cursor:pointer;" onclick = "randomize_design();"-->
	<img src="/images/design_restore.png" width="48" height="48" border="0" title="Restore Site Design" style="margin-bottom:4px; cursor:pointer;" onclick = "if ( confirm('Original data will be restored. All changes will be lost. Do you want to proceed?')) restore_design();">
	<img src="/images/design_save.png" width="48" height="48" border="0" title="Save" style="margin-bottom:4px; cursor:pointer;" onclick = "if ( confirm('All changes will be saved. Do you want to proceed?')) save_design('save_design');">
</div>
<?php echo write_design_window_header('background'); ?>
	<div class="container" style="width:100%;">
		<ul class="nav nav-tabs" role="tablist" >
			<li role="presentation" class="active"><a href="#bckg" role="tab" id="bckg-tab" data-toggle="tab" aria-controls="bckg">Background</a></li>
			<li role="presentation"><a href="#buttons" role="tab" id="buttons-tab" data-toggle="tab" aria-controls="buttons">Buttons</a></li>
			<li role="presentation"><a href="#images" role="tab" id="images-tab" data-toggle="tab" aria-controls="images">Images</a></li>
			<li role="presentation"><a href="#inputs" role="tab" id="inputs-tab" data-toggle="tab" aria-controls="inputs">Input Boxes</a></li>
			<li role="presentation"><a href="#boxes" role="tab" id="boxes-tab" data-toggle="tab" aria-controls="boxes">Boxes</a></li>
			<li role="presentation"><a href="#boxes2" role="tab" id="boxes2-tab" data-toggle="tab" aria-controls="boxes2">Boxes # 2</a></li>
			<li role="presentation"><a href="#boxes3" role="tab" id="boxes3-tab" data-toggle="tab" aria-controls="boxes3">Boxes # 3</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane fade in active" id="bckg" aria-labelledBy="bckg-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						<input class="color form-control input-sm" id="background_color" name="background_color" style="width:80px;" value="<?php echo BACKGROUND_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Background Image:
					</td>
					<td>
						<?php echo write_design_upload_file('background_image', 'BACKGROUND_IMAGE'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Text Color:
					</td>
					<td>
						<?php echo write_width_and_color('text_font_size', TEXT_FONT_SIZE, 'page_text_color', PAGE_TEXT_COLOR); ?>
					</td>
				</tr>
				<tr>
					<td>
						Text Color on Dark Background:
					</td>
					<td>
						<input class="color form-control input-sm" id="text_color_light" name="text_color_light" style="width:80px;" value="<?php echo PAGE_TEXT_COLOR_LIGHT; ?>">
					</td>
				</tr>
				
				<tr>
					<td>
						Font Family:
					</td>
					<td>
						<?php echo write_design_upload_file('text_font_family', 'TEXT_FONT_FAMILY'); ?>
					</td>
				</tr>
				<tr>
					<td valign="top">Text Alignment:</td>
					<td>
						<input type="radio" name="text_alignment" id="_radio_text_alignment0" value="1" style="" <?php echo (int)TEXT_ALIGNMENT == 0?'checked':''; ?>>&nbsp;&nbsp;Left&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="text_alignment" id="_radio_text_alignment1" value="1" style="" <?php echo (int)TEXT_ALIGNMENT == 1?'checked':''; ?>>&nbsp;&nbsp;Justify&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="buttons" aria-labelledBy="buttons-tab">
				<table width="100%" border="0" style="border-collapse:separate; border-spacing: 4px 4px;" >
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td valign="top">Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_buttons_radius', BUTTONS_RADIUS, 140); ?>
					</td>
				</tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						Top: <input class="color form-control input-sm" id="_color_btn_bkg_color_top" style="width:80px;" value="<?php echo BTN_BKG_COLOR_TOP; ?>">&nbsp;&nbsp;
						Bottom: <input class="color form-control input-sm" id="_color_btn_bkg_color_bottom" style="width:80px;" value="<?php echo BTN_BKG_COLOR_BOTTOM; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Text Color:
					</td>
					<td>
						Active: <input class="color form-control input-sm" id="_color_btn_color_active" style="width:80px;" value="<?php echo BTN_COLOR_ACTIVE; ?>">&nbsp;&nbsp;
						Hover: <input class="color form-control input-sm" id="_color_btn_color_hover" style="width:80px;" value="<?php echo BTN_COLOR_HOVER; ?>">
					</td>
				</tr>
				<tr>
					<td valign="top"></td>
					<td>
						<input type="radio" name="buttons_gradient_bkg" id="_radio_buttons_gradient_bkg1" value="1" style="" <?php echo BUTTONS_GRADIENT_BKG == '1'?'checked':''; ?>>&nbsp;&nbsp;Gradient Fill&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" name="buttons_gradient_bkg" id="_radio_buttons_gradient_bkg0" value="1" style="" <?php echo BUTTONS_GRADIENT_BKG == '0'?'checked':''; ?>>&nbsp;&nbsp;Solid Fill&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						Buttons Outline:
					</td>
					<td>
						<?php echo write_width_and_color('buttons_outline_size', BUTTONS_OUTLINE_SIZE, 'buttons_outline', BUTTONS_OUTLINE); ?>
					</td>
				</tr>
				<tr>
					<td>
						Buttons Have Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td>
								<!--input type="checkbox" value="1" id="buttons_have_shadow" name="buttons_have_shadow" <?php echo BUTTONS_HAVE_SHADOW == '1'?'checked':''; ?> style="margin-right:10px;"-->
								<input type="checkbox" value="1" id="_checkbox_1|0^buttons_have_shadow" <?php echo BUTTONS_HAVE_SHADOW == '1'?'checked':''; ?> style="margin-right:10px;">
							</td>
							<td>
								<?php echo write_width_and_color('buttons_shadow', BUTTONS_SHADOW, 'buttons_shadow_color', BUTTONS_SHADOW_COLOR); ?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Buttons Text Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td>
								<input type="checkbox" value="1" id="buttons_text_have_shadow" name="buttons_text_have_shadow" <?php echo (defined('BUTTONS_TEXT_HAVE_SHADOW') ? (BUTTONS_TEXT_HAVE_SHADOW == '1' ? 'checked' : '') : ''); ?> style="margin-right:10px;">
							</td>
							<td>
								<?php echo write_width_and_color('buttons_text_shadow', (defined('BUTTONS_TEXT_SHADOW') ? BUTTONS_TEXT_SHADOW : ''), 'buttons_text_shadow_color', (defined('BUTTONS_TEXT_SHADOW_COLOR') ? BUTTONS_TEXT_SHADOW_COLOR : '')); ?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Font:
					</td>
					<td>
						<?php echo write_design_upload_file('buttons_font_family', 'BUTTONS_FONT_FAMILY'); ?>
					</td>
				</tr>
			</table>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="images" aria-labelledBy="images-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td valign="top">Border:</td>
					<td>
						<?php echo write_width_and_color('images_have_border', IMAGES_HAVE_BORDER, 'images_color', IMAGES_COLOR); ?>
						<input type="checkbox" value="1" id="_checkbox_1|0^images_have_bkg_color" <?php echo (int)IMAGES_HAVE_BKG_COLOR == 0?'':'checked'; ?>>&nbsp;&nbsp;Have background color: <input class="color form-control input-sm" id="_color_images_bkg_color" value="<?php echo IMAGES_BKG_COLOR; ?>">
						<br>
						<input type="checkbox" name="images_have_shadow" id="images_have_shadow" value="1" style="" <?php echo (int)IMAGES_HAVE_SHADOW == 1?'checked':''; ?>>&nbsp;&nbsp;Have Shadow <br>
						<!--input type="checkbox" name="images_have_rounded_edges" id="images_have_rounded_edges" value="1" style="" <?php echo (int)IMAGES_HAVE_ROUNDED_EDGES == 1?'checked':''; ?>>&nbsp;&nbsp;Have Rounded Edges<br-->
						Layout:&nbsp;&nbsp;&nbsp;
						<input type="radio" name="images_layout" id="images_layout0" value="0" style="" <?php echo (int)IMAGES_LAYOUT == 0?'checked':''; ?>>&nbsp;Left&nbsp;&nbsp;&nbsp;
						<input type="radio" name="images_layout" id="images_layout1" value="1" style="" <?php echo (int)IMAGES_LAYOUT == 1?'checked':''; ?>>&nbsp;Right&nbsp;&nbsp;&nbsp;
						<input type="radio" name="images_layout" id="images_layout2" value="2" style="" <?php echo (int)IMAGES_LAYOUT == 2?'checked':''; ?>>&nbsp;Top&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td valign="top">Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_images_have_rounded_edges', IMAGES_HAVE_ROUNDED_EDGES, 140); ?>
					</td>
				</tr>
				<tr>
					<td>Image in Info Panel:</td>
					<td>
						<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
						<tr>
							<td>Greyscale:</td><td><?php echo show_incremented_value('', '_input_image_in_info_pane_grayscale', IMAGE_IN_INFO_PANE_GRAYSCALE, 160); ?></td><td>%</td><td style="padding-left:20px;">color:</td><td style="width:100%; vertical-align:top;"><input class="color form-control input-sm" id="_color_image_in_info_pane_blend_color" value="<?php echo IMAGE_IN_INFO_PANE_BLEND_COLOR; ?>"></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
						<tr>
							<td>Opacity:</td><td><?php echo show_incremented_value('', '_input_image_in_info_pane_blend_opacity', IMAGE_IN_INFO_PANE_BLEND_OPACITY, 160); ?></td><td style="width:100%;">%</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>
			<div role="tabpanel" class="tab-pane fade" id="inputs" aria-labelledBy="inputs-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td valign="top">Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_input_boxes_radius', INPUT_BOXES_RADIUS, 140); ?>
					</td>
				</tr>
				<tr>
					<td>
						Input Background Color:
					</td>
					<td>
						<input class="color form-control input-sm" id="input_boxes_background_color" name="input_boxes_background_color" style="width:80px;" value="<?php echo INPUT_BOXES_BACKGROUND_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Input Boxes Outline:
					</td>
					<td>
						<?php echo write_width_and_color('input_boxes_have_outline', INPUT_BOXES_HAVE_OUTLINE, 'input_boxes_outline', INPUT_BOXES_OUTLINE); ?>
					</td>
				</tr>
				<tr>
					<td>
						Input Boxes Have Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr><td></td><td><table width="100"></table></td><td width="100%"></td></tr>
						<tr>
							<td>
								<input type="checkbox" value="1" id="input_boxes_have_shadow" name="input_boxes_have_shadow" <?php echo INPUT_BOXES_HAVE_SHADOW == '1'?'checked':''; ?> style="margin-right:10px;">
							</td>
							<td>
								<?php echo write_width_and_color('input_boxes_shadow', INPUT_BOXES_SHADOW, 'input_boxes_shadow_color', INPUT_BOXES_SHADOW_COLOR); ?>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade" id="boxes" aria-labelledBy="boxes-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_box_type1_radius', BOX_TYPE1_RADIUS, 140); ?>
					</td>
				</tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^box_type1_has_background_color" <?php echo (int)BOX_TYPE1_HAS_BACKGROUND_COLOR?'checked':''; ?> style="margin:0 10px 0 0px;">
						<input class="color form-control input-sm" id="_color_box_type1_bg_color" value="<?php echo BOX_TYPE1_BG_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Background Image:
					</td>
					<td>
						<?php echo write_design_upload_file('box_type1_background_image', 'BOX_TYPE1_BACKGROUND_IMAGE'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Outline:
					</td>
					<td>
						<?php echo write_width_and_color('box_type1_have_outline', BOX_TYPE1_HAVE_OUTLINE, 'box_type1_outline', BOX_TYPE1_OUTLINE); ?>
					</td>
				</tr>
				<tr>
					<td>
						Have Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td><input type="checkbox" value="1" id="_checkbox_1|0^box_type1_have_shadow" <?php echo (int)BOX_TYPE1_HAVE_SHADOW?'checked':''; ?> style="margin:0 10px 0 0px;"></td>
							<td style="width:100%;"><?php echo write_width_and_color('box_type1_shadow', BOX_TYPE1_SHADOW, 'box_type1_shadow_color', BOX_TYPE1_SHADOW_COLOR); ?></td>
							<td style="padding:0 6px 0 8px;">blur:</td>
							<td style=""><?php echo show_incremented_value('', '_input_box_type1_shadow_blur', BOX_TYPE1_SHADOW_BLUR, 130); ?></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="checkbox" value="1" id="_checkbox_1|0^top_info_pane_style_number1" <?php echo TOP_INFO_PANE_STYLE_NUMBER1 == '1'?'checked':''; ?>> top info panel has style of this box</td>
				</tr>
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade" id="boxes2" aria-labelledBy="boxes2-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_BOX_TYPE2_radius', BOX_TYPE2_RADIUS, 140); ?>
					</td>
				</tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^BOX_TYPE2_has_background_color" <?php echo (int)BOX_TYPE2_HAS_BACKGROUND_COLOR?'checked':''; ?> style="margin:0 10px 0 0px;">
						<input class="color form-control input-sm" id="_color_BOX_TYPE2_bg_color" value="<?php echo BOX_TYPE2_BG_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Background Image:
					</td>
					<td>
						<?php echo write_design_upload_file('BOX_TYPE2_background_image', 'BOX_TYPE2_BACKGROUND_IMAGE'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Outline:
					</td>
					<td>
						<?php echo write_width_and_color('box_type2_have_outline', BOX_TYPE2_HAVE_OUTLINE, 'box_type2_outline', BOX_TYPE2_OUTLINE); ?>
					</td>
				</tr>
				<tr>
					<td>
						Have Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td><input type="checkbox" value="1" id="_checkbox_1|0^box_type2_have_shadow" <?php echo (int)BOX_TYPE2_HAVE_SHADOW?'checked':''; ?> style="margin:0 10px 0 0px;"></td>
							<td style="width:100%;"><?php echo write_width_and_color('box_type2_shadow', BOX_TYPE2_SHADOW, 'box_type2_shadow_color', BOX_TYPE2_SHADOW_COLOR); ?></td>
							<td style="padding:0 6px 0 8px;">blur:</td>
							<td style=""><?php echo show_incremented_value('', '_input_box_type2_shadow_blur', BOX_TYPE2_SHADOW_BLUR, 130); ?></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Font Family:
					</td>
					<td>
						<?php echo write_design_upload_file('box_type2_font_family', 'BOX_TYPE2_FONT_FAMILY'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Font:
					</td>
					<td>
						<?php echo write_width_and_color('box_type2_font_size', BOX_TYPE2_FONT_SIZE, 'box_type2_color', BOX_TYPE2_COLOR); ?>
					</td>
				</tr>
				<tr>
					<td>
						Font Style:
					</td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_italic|normal^box_type2_font_style" <?php echo BOX_TYPE2_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="checkbox" value="1" id="_checkbox_bold|normal^box_type2_font_weight" <?php echo BOX_TYPE2_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				<tr>
					<td>
						Text Transform:
					</td>
					<td>
						<input type="radio" value="0" id="_radio_BOX_TYPE2_text_transform0" name="BOX_TYPE2_text_transform" <?php echo (int)BOX_TYPE2_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="_radio_BOX_TYPE2_text_transform1" name="BOX_TYPE2_text_transform" <?php echo (int)BOX_TYPE2_TEXT_TRANSFORM == 1?'checked':''; ?>> UPPERCASE&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="2" id="_radio_BOX_TYPE2_text_transform2" name="BOX_TYPE2_text_transform" <?php echo (int)BOX_TYPE2_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						Text Has Shadow:
					</td>
					<td>
						<table><tr>
							<td><input type="checkbox" value="1" id="_checkbox_1|0^box_type2_text_has_shadow" <?php echo BOX_TYPE2_TEXT_HAS_SHADOW == '1'?'checked':''; ?> style="margin:0 10px 0 0px;"></td>
							<td style="width:100%;"><?php echo write_width_and_color('box_type2_text_shadow', BOX_TYPE2_TEXT_SHADOW, 'box_type2_text_shadow_color', BOX_TYPE2_TEXT_SHADOW_COLOR); ?></td>
						</tr></table>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="checkbox" value="1" id="_checkbox_1|0^top_info_pane_style_number2" <?php echo TOP_INFO_PANE_STYLE_NUMBER2 == '1'?'checked':''; ?>> top info panel has style of this box</td>
				</tr>
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade" id="boxes3" aria-labelledBy="boxes3-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>Rounded Edges:</td>
					<td>
						<?php echo show_incremented_value('', '_input_BOX_TYPE3_radius', BOX_TYPE3_RADIUS, 140); ?>
					</td>
				</tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^BOX_TYPE3_has_background_color" <?php echo (int)BOX_TYPE3_HAS_BACKGROUND_COLOR?'checked':''; ?> style="margin:0 10px 0 0px;">
						<input class="color form-control input-sm" id="_color_BOX_TYPE3_bg_color" value="<?php echo BOX_TYPE3_BG_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Background Image:
					</td>
					<td>
						<?php echo write_design_upload_file('box_type3_background_image', 'BOX_TYPE3_BACKGROUND_IMAGE'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Outline:
					</td>
					<td>
						<?php echo write_width_and_color('box_type3_have_outline', BOX_TYPE3_HAVE_OUTLINE, 'box_type3_outline', BOX_TYPE3_OUTLINE); ?>
					</td>
				</tr>
				<tr>
					<td>
						Have Shadow:
					</td>
					<td>
						<table width="10" cellspacing="0" cellpadding="0" border="0">
						<tr>
							<td><input type="checkbox" value="1" id="_checkbox_1|0^box_type3_have_shadow" <?php echo (int)BOX_TYPE3_HAVE_SHADOW?'checked':''; ?> style="margin:0 10px 0 0px;"></td>
							<td style="width:100%;"><?php echo write_width_and_color('box_type3_shadow', BOX_TYPE3_SHADOW, 'box_type3_shadow_color', BOX_TYPE3_SHADOW_COLOR); ?></td>
							<td style="padding:0 6px 0 8px;">blur:</td>
							<td style=""><?php echo show_incremented_value('', '_input_box_type3_shadow_blur', BOX_TYPE3_SHADOW_BLUR, 130); ?></td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Font Family:
					</td>
					<td>
						<?php echo write_design_upload_file('box_type3_font_family', 'BOX_TYPE3_FONT_FAMILY'); ?>
					</td>
				</tr>
				<tr>
					<td>
						Font:
					</td>
					<td>
						<?php echo write_width_and_color('box_type3_font_size', BOX_TYPE3_FONT_SIZE, 'box_type3_color', BOX_TYPE3_COLOR); ?>
					</td>
				</tr>
				<tr>
					<td>
						Font Style:
					</td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_italic|normal^box_type3_font_style" <?php echo BOX_TYPE3_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="checkbox" value="1" id="_checkbox_bold|normal^box_type3_font_weight" <?php echo BOX_TYPE3_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				<tr>
					<td>
						Text Transform:
					</td>
					<td>
						<input type="radio" value="0" id="_radio_BOX_TYPE3_text_transform0" name="BOX_TYPE3_text_transform" <?php echo (int)BOX_TYPE3_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="_radio_BOX_TYPE3_text_transform1" name="BOX_TYPE3_text_transform" <?php echo (int)BOX_TYPE3_TEXT_TRANSFORM == 1?'checked':''; ?>> UPPERCASE&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="2" id="_radio_BOX_TYPE3_text_transform2" name="BOX_TYPE3_text_transform" <?php echo (int)BOX_TYPE3_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						Text Has Shadow:
					</td>
					<td>
						<table><tr>
							<td><input type="checkbox" value="1" id="_checkbox_1|0^box_type3_text_has_shadow" <?php echo BOX_TYPE3_TEXT_HAS_SHADOW == '1'?'checked':''; ?> style="margin:0 10px 0 0px;"></td>
							<td style="width:100%;"><?php echo write_width_and_color('box_type3_text_shadow', BOX_TYPE3_TEXT_SHADOW, 'box_type3_text_shadow_color', BOX_TYPE3_TEXT_SHADOW_COLOR); ?></td>
						</tr></table>
					</td>
				</tr>
				<tr>
					<td></td>
					<td><input type="checkbox" value="1" id="_checkbox_1|0^top_info_pane_style_number3" <?php echo TOP_INFO_PANE_STYLE_NUMBER3 == '1'?'checked':''; ?>> top info panel has style of this box</td>
				</tr>
				</table>
			</div>
		</div>
	</div>
<?php echo write_design_window_footer('background'); ?>
<?php echo write_design_window_header('palette'); ?>
		<table width="100" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr>
			<td>
				<input class="color form-control input-sm" id="palette_color_dark1" name="palette_color_dark1" value="<?php echo COLOR1DARK; ?>" title="buy_stocks, business plan, widget_prgress, my_shares, checkout, top_users, quick_stats, regional_reps">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_base1" name="palette_color_base1" value="<?php echo COLOR1BASE; ?>" title="box_messenger, local_time_date_next_hour, timeline, share_info, banners, transactions, quick_stats, ">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_light1" name="palette_color_light1" value="<?php echo COLOR1LIGHT; ?>" title="table, share, top_users, quick_stats, h1 > b, payment_option_table_selected">
			</td>
		</tr>
		<tr>
			<td>
				<input class="color form-control input-sm" id="palette_color_dark2" name="palette_color_dark2" value="<?php echo COLOR2DARK; ?>" title="profit calculator, MENU_BORDER, H1_BOTTOM_BORDER, H6, ">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_base2" name="palette_color_base2" value="<?php echo COLOR2BASE; ?>" title="profit calculator, share_info">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_light2" name="palette_color_light2" value="<?php echo COLOR2LIGHT; ?>" title="footer, messenger, business_plan, timeline, share_info">
			</td>
		</tr>
		<tr>
			<td>
				<input class="color form-control input-sm" id="palette_color_dark3" name="palette_color_dark3" value="<?php echo COLOR3DARK; ?>" title="li > a > .glyphicon, h2 > b, H5, .write_new_topic:hover, LOGO_SHADOW, merchant button, regional_reps, ">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_base3" name="palette_color_base3" value="<?php echo COLOR3BASE; ?>" title="timeline, wall, share_info, INPUT_BOXES_OUTLINE, write_new_topic, .currency_symbol_on_graph, .place_text_3, .place_3, .question_mark_in_info">
			</td>
			<td>
				<input class="color form-control input-sm" id="palette_color_light3" name="palette_color_light3" value="<?php echo COLOR3LIGHT; ?>" title="timeline, banners, top_users, jobs_done, h2 > b">
			</td>
		</tr>
		</table>
		
		<?php
		echo '<table align="center" width="400" cellspacing="0" cellpadding="0" border="0">
		<tr>';

		$color_number = 1;
		$i_first = 0;
		$number_of_colons = 6;
		$total_palettes = 0;
		$collors_java_arr = '';
		for ($i = $i_first; $i < 360; $i = $i + 10) {
			$hue = $i;
			if ( round($i / 10) % 2 == 0 )
				$angle = 40;
			else
				$angle = -40;
			$opposite_angle = 180;

			$colors = getRgbColorsByHsv($hue, 99, 30);
			$color_dark1 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue, 99, 99);
			$color_base1 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue, 10, 99);
			$color_light1 = $colors['red'].$colors['green'].$colors['blue'];
			
			$colors = getRgbColorsByHsv($hue, 40, 99);
			$color_dark2 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue, 20, 99);
			$color_base2 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue, 3, 99);
			$color_light2 = $colors['red'].$colors['green'].$colors['blue'];

			$colors = getRgbColorsByHsv($hue + $opposite_angle - $angle, 99, 30);
			$color_dark3 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue + $opposite_angle - $angle, 99, 99);
			$color_base3 = $colors['red'].$colors['green'].$colors['blue'];
			$colors = getRgbColorsByHsv($hue + $opposite_angle - $angle, 10, 99);
			$color_light3 = $colors['red'].$colors['green'].$colors['blue'];
			
			$collors_java_arr = $collors_java_arr.'
			palette_colors['.$total_palettes.'][0] = "'.$color_dark1.'"; palette_colors['.$total_palettes.'][1] = "'.$color_base1.'"; palette_colors['.$total_palettes.'][2] = "'.$color_light1.'";
			palette_colors['.$total_palettes.'][3] = "'.$color_dark2.'"; palette_colors['.$total_palettes.'][4] = "'.$color_base2.'"; palette_colors['.$total_palettes.'][5] = "'.$color_light2.'";
			palette_colors['.$total_palettes.'][6] = "'.$color_dark3.'"; palette_colors['.$total_palettes.'][7] = "'.$color_base3.'"; palette_colors['.$total_palettes.'][8] = "'.$color_light3.'";
			';

			echo '<td id=preview_color_theme_td'.$i.' ';
			echo ' class=select_designcolor_td ';
			echo '>
				<table border="0" style="border-collapse:separate; border-spacing: 4px 4px; background-color:#ffffff; width:auto; margin-bottom:10px; cursor:pointer;" 
				onMouseOver="this.style.backgroundColor = \'#d1d1ff\';" 
				onMouseOut="this.style.backgroundColor = \'#ffffff\';" 
				onClick = "change_tmp_palette(
					\''.$color_dark1.'\', \''.$color_base1.'\', \''.$color_light1.'\',
					\''.$color_dark2.'\', \''.$color_base2.'\', \''.$color_light2.'\',
					\''.$color_dark3.'\', \''.$color_base3.'\', \''.$color_light3.'\'
				);">
				<tr>
					<td width="10" height="10" bgcolor="#'.$color_dark1.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_base1.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_light1.'" style="border:1px #000000 solid;"></td>
				</tr>
				<tr>
					<td width="10" height="10" bgcolor="#'.$color_dark2.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_base2.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_light2.'" style="border:1px #000000 solid;"></td>
				</tr>
				<tr>
					<td width="10" height="10" bgcolor="#'.$color_dark3.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_base3.'" style="border:1px #000000 solid;"></td>
					<td width="10" bgcolor="#'.$color_light3.'" style="border:1px #000000 solid;"></td>
				</tr>
				</table>
			</td>'."\r\n";
			
			$color_number = $color_number + 1;
			if ( $color_number > $number_of_colons ) {
				$color_number = 1;
				echo '</tr>'."\r\n".'<tr>';
			}
			$total_palettes++;
		}
		echo '
		</tr>
		</table>';
		echo '<script language="JavaScript">
		var palette_colors = new Array();
		for (i = 0; i < '.$total_palettes.'; i++)
			palette_colors[i] = new Array(9);

		'.$collors_java_arr.'
		</script>
		';
		?>
<?php echo write_design_window_footer('palette'); ?>
<?php echo write_design_window_header('logo'); ?>
	<div class="container" style="width:100%;">
		<ul class="nav nav-tabs" role="tablist" >
			<li role="presentation" class="active"><a href="#logo" role="tab" id="logo-tab" data-toggle="tab" aria-controls="logo">Logo</a></li>
			<li role="presentation"><a href="#font" role="tab" id="font-tab" data-toggle="tab" aria-controls="font">Middle Texts</a></li>
			<li role="presentation"><a href="#righttx" role="tab" id="righttx-tab" data-toggle="tab" aria-controls="righttx">Right Texts</a></li>
			<li role="presentation"><a href="#logobackground" role="tab" id="logobackground-tab" data-toggle="tab" aria-controls="logobackground">Background</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane fade in active" id="logo" aria-labelledBy="logo-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>
						Height:
					</td>
					<td>
						<?php echo show_incremented_value('', '_input_logo_height', LOGO_HEIGHT, 160); ?>
					</td>
				</tr>
				<tr>
					<td>
						Position:
					</td>
					<td>
						<input type="radio" value="0" id="logo_on_left1" name="logo_on_left" <?php echo (int)LOGO_ON_LEFT == 1?'checked':''; ?>> Left&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="logo_on_left0" name="logo_on_left" <?php echo (int)LOGO_ON_LEFT == 0?'checked':''; ?>> Right
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^collapse_header_on_login" <?php echo COLLAPSE_HEADER_ON_LOGIN == 0?'':'checked'; ?>>  Collapse Header on Login
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^show_top_bar_on_scroll" <?php echo defined('SHOW_TOP_BAR_ON_SCROLL') && SHOW_TOP_BAR_ON_SCROLL == '1' ? 'checked' : ''; ?>> Show top bar on scroll
					</td>
				</tr>
				<tr>
					<td>
						Image:
					</td>
					<td>
						<?php echo write_design_upload_file('logo_background_image', 'LOGO_BACKGROUND_IMAGE'); ?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table style="width:100%;">
						<tr>
							<td style="padding:0 6px 0 0;">Width:</td>
							<td>
								<?php echo show_incremented_value('', '_input_logo_img_width', LOGO_IMG_WIDTH, 160); ?>
							</td>
							<td style="padding:0 0px 0 8px;">px</td>
							<td style="padding:0 6px 0 18px;">Height:</td>
							<td style="">
								<?php echo show_incremented_value('', '_input_logo_img_height', LOGO_IMG_HEIGHT, 160); ?>
							</td>
							<td style="width:100%; padding:0 0px 0 8px;">px</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade in" id="font" aria-labelledBy="font-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>Business Name:</td>
					<td>
						<input class="form-control" id="business_name" name="business_name" style="width:300px; display: inline;" value="<?php echo BUSINESS_NAME; ?>">
						<input type="checkbox" value="1" id="_checkbox_1|0^translate_business_name" <?php echo (defined('TRANSLATE_BUSINESS_NAME') && TRANSLATE_BUSINESS_NAME == '0' ? '' : 'checked'); ?>> <span>Translate</span>
					</td>
				</tr>
				<tr>
					<td>Font:</td>
					<td><input class="form-control" id="_input_business_name_font_family" style="width:100%;" value="<?php echo BUSINESS_NAME_FONT_FAMILY; ?>"></td>					
				</tr>
				<tr>
					<td>Alternative Font:</td><td><input class="form-control" id="_input_business_name_alternative_font" class="long_input" value="<?php echo BUSINESS_NAME_ALTERNATIVE_FONT; ?>"></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table style="width:100%;">
						<tr>
							<td>
								<?php echo show_incremented_value('', '_input_business_name_font_size', BUSINESS_NAME_FONT_SIZE, 160); ?>
							</td>
							<td style="padding:0 10px 0 10px;">px</td>
							<td style="min-width:120px; padding: 0 0 0 20px;">
								<input type="checkbox" value="1" id="_checkbox_italic|normal^business_name_font_style" <?php echo BUSINESS_NAME_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
							</td>
							<td  style="width:100%;">
								<input type="checkbox" value="1" id="_checkbox_bold|normal^business_name_font_weight" <?php echo BUSINESS_NAME_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>Site Slogan:</td>
					<td><input class="form-control" id="site_slogan" name="site_slogan" style="width:100%;" value="<?php echo SITE_SLOGAN; ?>"></td>
				</tr>
				<tr>
					<td>Font:</td>
					<td><input class="form-control" id="logo_font_family" name="logo_font_family" style="width:100%;" value="<?php echo LOGO_FONT_FAMILY; ?>"></td>
				</tr>
				<tr>
					<td>Alternative Font:</td><td><input class="form-control" id="_input_logo_alternative_font" class="long_input" value="<?php echo LOGO_ALTERNATIVE_FONT; ?>"></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<table style="width:100%;">
						<tr>
							<td>
								<?php echo show_incremented_value('', '_input_logo_font_size', LOGO_FONT_SIZE, 160); ?>
							</td>
							<td style="padding:0 10px 0 10px;">px</td>
							<td style="min-width:120px; padding: 0 0 0 20px;">
								<input type="checkbox" value="1" id="logo_font_italic" name="logo_font_italic" <?php echo LOGO_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
							</td>
							<td  style="width:100%;">
								<input type="checkbox" value="1" id="logo_font_bold" name="logo_font_bold" <?php echo LOGO_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						Text Transform:
					</td>
					<td>
						<input type="radio" value="0" id="logo_text_transform0" name="logo_text_transform" <?php echo (int)LOGO_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="logo_text_transform1" name="logo_text_transform" <?php echo (int)LOGO_TEXT_TRANSFORM == 1?'checked':''; ?>> Uppercase&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="2" id="logo_text_transform2" name="logo_text_transform" <?php echo (int)LOGO_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						First Part Color:
					</td>
					<td>
						<input type="radio" value="0" id="logo_font_first_letter_color_transparent_yes" name="logo_font_first_letter_color_transparent" <?php echo (int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'checked':''; ?>> Transparent&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="logo_font_first_letter_color_transparent_none" name="logo_font_first_letter_color_transparent" <?php echo (int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 0?'checked':''; ?>>
						<input class="color form-control" style="width:80px;" id="logo_font_first_letter_color" name="logo_font_first_letter_color" value="<?php echo LOGO_FONT_FIRST_LETTER_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Last Part Color:
					</td>
					<td>
						<input type="radio" value="0" id="logo_color_transparent_yes" name="logo_color_transparent" <?php echo (int)LOGO_COLOR_TRANSPARENT == 1?'checked':''; ?>> Transparent&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="logo_color_transparent_none" name="logo_color_transparent" <?php echo (int)LOGO_COLOR_TRANSPARENT == 0?'checked':''; ?>>
						<input class="color {onImmediateChange:'update_logo_preview(this);'} form-control " id="logo_color" name="logo_color" style="width:80px;" value="<?php echo LOGO_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>Has Shadow:</td>
					<td>
						<table style="width:100%;">
						<tr>
							<td style="padding:0 6px 0 0;"><input type="checkbox" value="1" id="logo_has_shadow" name="logo_has_shadow" <?php echo LOGO_HAS_SHADOW == '1'?'checked':''; ?>></td>
							<td><?php echo write_width_and_color('logo_shadow_width', LOGO_SHADOW_WIDTH, 'logo_shadow_color', LOGO_SHADOW_COLOR); ?></td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade in" id="righttx" aria-labelledBy="righttx-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>First Line:</td>
					<td>
						<div class="input-group input-group-sm" style="">
							<input class="form-control" id="_input_righttx1" name="righttx1" style="width:100%;" value="<?php echo RIGHTTX1; ?>">
							<span class="input-group-btn">
								<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_righttx1_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>Second Line:</td>
					<td>
						<div class="input-group input-group-sm" style="">
							<input class="form-control" id="_input_righttx2" name="righttx2" style="width:100%;" value="<?php echo RIGHTTX2; ?>">
							<span class="input-group-btn">
								<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_righttx2_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>First Line (looged in):</td>
					<td>
						<div class="input-group input-group-sm" style="">
							<input class="form-control" id="_input_righttx_logged1" name="righttx_logged1" style="width:100%;" value="<?php echo RIGHTTX_LOGGED1; ?>">
							<span class="input-group-btn">
								<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_righttx_logged1_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>Second Line (looged in):</td>
					<td>
						<div class="input-group input-group-sm" style="">
							<input class="form-control" id="_input_righttx_logged2" name="righttx_logged2" style="width:100%;" value="<?php echo RIGHTTX_LOGGED2; ?>">
							<span class="input-group-btn">
								<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_righttx_logged2_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<td>Width:</td>
					<td>
						<table style="width:100%;">
						<tr>
							<td>
								<?php echo show_incremented_value('', '_input_righttx_width', RIGHTTX_WIDTH, 160); ?>
							</td>
							<td style="padding:0 10px 0 10px; width:100%;">px</td>
						</tr>
						</table>

					</td>
				</tr>
				
				</table>
			</div>

			<div role="tabpanel" class="tab-pane fade in" id="logobackground" aria-labelledBy="logobackground-tab">
				<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
				<tr><td><table width="150"></table></td><td width="100%"></td></tr>
				<tr>
					<td>
						Background Color:
					</td>
					<td>
						<input type="radio" value="0" id="logo_has_background_color_none" name="logo_has_background_color" <?php echo (int)LOGO_HAS_BACKGROUND_COLOR == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="1" id="logo_has_background_color_yes" name="logo_has_background_color" <?php echo (int)LOGO_HAS_BACKGROUND_COLOR == 1?'checked':''; ?>>
						<input class="color form-control" style="width:80px;" id="logo_background_color" name="logo_background_color" value="<?php echo LOGO_BACKGROUND_COLOR; ?>">
					</td>
				</tr>
				<tr>
					<td>
						Background Image:
					</td>
					<td>
						<?php echo write_design_upload_file('logo_container_background_image', 'LOGO_CONTAINER_BACKGROUND_IMAGE'); ?>
					</td>
				</tr>

				<tr>
					<td>Additional code:</td>
					<td>
						<div class="input-group input-group-sm" style="">
							<input class="form-control" id="_input_logo_additional_code" name="logo_additional_code" style="width:100%;" value="<?php echo LOGO_ADDITIONAL_CODE; ?>">
							<span class="input-group-btn">
								<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_logo_additional_code_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
							</span>
						</div>
					</td>
				</tr>

				</table>
			</div>

		</div>
	</div>
<?php echo write_design_window_footer('logo'); ?>
<?php echo write_design_window_header('menu'); ?>

		<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr><td><table width="140"></table></td><td width="100%"></td></tr>
		<tr>
			<td>Height:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td><?php echo show_incremented_value('', '_input_menu_height', MENU_HEIGHT, 140); ?></td>
					<td style="padding:0 0px 0 8px;">px</td>
					<td style="padding:0 0px 0 40px; min-width: 130px;">Button's Width:</td>
					<td style="">
						<?php echo show_incremented_value('', '_input_menu_btn_width', MENU_BTN_WIDTH, 140); ?>
					</td>
					<td style="width:100%; padding:0 0px 0 8px;">px</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Style:
			</td>
			<td>
				<input type="radio" name="menu_style" id="menu_style1" value="1" style="margin-bottom:10px;" <?php echo MENU_STYLE == '1'?'checked':''; ?>> Text&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="menu_style" id="menu_style2" value="2" <?php echo MENU_STYLE == '2'?'checked':''; ?>> Buttons
			</td>
		</tr>
		<tr>
			<td>
				Float:
			</td>
			<td>
				<input type="radio" value="0" id="menu_float_left" name="menu_float" <?php echo MENU_FLOAT == 'left'?'checked':''; ?>> Left&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="menu_float_right" name="menu_float" <?php echo MENU_FLOAT == 'right'?'checked':''; ?>> Right
			</td>
		</tr>
		<tr>
			<td>
				Font Family:
			</td>
			<td>
				<?php echo write_design_upload_file('menu_font_family', 'MENU_FONT_FAMILY'); ?>
			</td>
		</tr>
		<tr>
			<td>
				Font Color:
			</td>
			<td>
				<input class="color form-control " id="menu_color" name="menu_color" style="width:80px;" value="<?php echo MENU_COLOR; ?>">
				&nbsp;&nbsp;Hover:&nbsp;<input class="color form-control" id="_color_menu_hover_color" style="width:80px;" value="<?php echo MENU_HOVER_COLOR; ?>">
				&nbsp;&nbsp;Pressed:&nbsp;<input class="color form-control" id="_color_menu_pressed_color" style="width:80px;" value="<?php echo MENU_PRESSED_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Font Size:
			</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td><?php echo show_incremented_value('', '_input_menu_font_size', MENU_FONT_SIZE, 150); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:120px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^menu_font_style" <?php echo MENU_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td  style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^menu_font_weight" <?php echo MENU_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Text Transform:
			</td>
			<td>
				<input type="radio" value="0" id="menu_text_transform0" name="menu_text_transform" <?php echo (int)MENU_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="menu_text_transform1" name="menu_text_transform" <?php echo (int)MENU_TEXT_TRANSFORM == 1?'checked':''; ?>> Uppercase&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="menu_text_transform2" name="menu_text_transform" <?php echo (int)MENU_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Text Has Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="menu_has_shadow" name="menu_has_shadow" <?php echo MENU_HAS_SHADOW == '1'?'checked':''; ?> style="margin-right:10px;">
					</td>
					<td>
						<?php echo write_width_and_color('menu_shadow', MENU_SHADOW, 'menu_shadow_color', MENU_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Dropdown Color:
			</td>
			<td>
				<input type="radio" value="0" id="_radio_items_have_background_color0" name="items_have_background_color" <?php echo (int)ITEMS_HAVE_BACKGROUND_COLOR == 0?'checked':''; ?>> Default&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="_radio_items_have_background_color1" name="items_have_background_color" <?php echo (int)ITEMS_HAVE_BACKGROUND_COLOR == 1?'checked':''; ?>>
				<input class="color form-control" style="width:80px;" id="_color_items_background_color" value="<?php echo ITEMS_BACKGROUND_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Dropdown Has Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^dropdown_menu_have_shadow" <?php echo DROPDOWN_MENU_HAVE_SHADOW == '1'?'checked':''; ?> style="margin-right:10px;">
					</td>
					<td>
						<?php echo write_width_and_color('dropdown_menu_shadow', DROPDOWN_MENU_SHADOW, 'dropdown_menu_shadow_color', DROPDOWN_MENU_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Menu Has Background Color:
			</td>
			<td>
				<input type="radio" value="0" id="menu_has_background_color_none" name="menu_has_background_color" <?php echo (int)MENU_HAS_BACKGROUND_COLOR == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="menu_has_background_color_yes" name="menu_has_background_color" <?php echo (int)MENU_HAS_BACKGROUND_COLOR == 1?'checked':''; ?>>
				Background and Buttons Color:
				<input class="color form-control" style="width:80px;" id="menu_background_color" name="menu_background_color" value="<?php echo MENU_BACKGROUND_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>Background Image:</td>
			<td><?php echo write_design_upload_file('menu_background_image', 'MENU_BACKGROUND_IMAGE'); ?></td>
		</tr>
		<tr>
			<td>Button Background Image:</td>
			<td><?php echo write_design_upload_file('menu_btn_background_image', 'MENU_BTN_BACKGROUND_IMAGE'); ?></td>
		</tr>
		<tr>
			<td>
				Has Border:
			</td>
			<td>
				<input type="radio" value="0" id="menu_bottom_border_style0" name="menu_bottom_border_style" <?php echo (int)MENU_BORDER_STYLE == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="menu_bottom_border_style1" name="menu_bottom_border_style" <?php echo (int)MENU_BORDER_STYLE == 1?'checked':''; ?>> Solid&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="menu_bottom_border_style2" name="menu_bottom_border_style" <?php echo (int)MENU_BORDER_STYLE == 2?'checked':''; ?>> Dashed&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="3" id="menu_bottom_border_style3" name="menu_bottom_border_style" <?php echo (int)MENU_BORDER_STYLE == 3?'checked':''; ?>> Dotted
			</td>
		</tr>
		<tr>
			<td valign="top"></td>
			<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td style="min-width:200px;">
						<input type="checkbox" name="menu_has_rounded_edges" id="menu_has_rounded_edges" value="1" style="" <?php echo MENU_HAS_ROUNDED_EDGES == '1'?'checked':''; ?>> 
						Rounded Edges
					</td>
					<td style="width:100%;">
						<input type="checkbox" name="menu_buttons_have_rounded_edges" id="menu_buttons_have_rounded_edges" value="1" style="" <?php echo MENU_BUTTONS_HAVE_ROUNDED_EDGES == '1'?'checked':''; ?>> 
						Rounded Buttons
					</td>
				</tr>
				</table>
			</td>
			</td>
		</tr>
		<tr>
			<td>
				Buttons Have Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="_checkbox_1|0^menu_buttons_have_shadow" <?php echo (int)MENU_BUTTONS_HAVE_SHADOW?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('menu_buttons_shadow', MENU_BUTTONS_SHADOW, 'menu_buttons_shadow_color', MENU_BUTTONS_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
<?php echo write_design_window_footer('menu'); ?>
<?php echo write_design_window_header('rectangle'); ?>

		<table width="100%" cellspacing="4" cellpadding="4" border="0">
		<tr><td><table width="150"></table></td><td width="100%"></td></tr>
		<tr>
			<td>
				Background Color:
			</td>
			<td>
				<input type="radio" value="0" id="rectangle_has_background_color_none" name="rectangle_has_background_color" <?php echo COLOR_TEXT_BKG == 'none'?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="rectangle_has_background_color_yes" name="rectangle_has_background_color" <?php echo COLOR_TEXT_BKG != 'none'?'checked':''; ?>> 
				<input class="color form-control" style="width:80px;" id="rectangle_background_color" name="rectangle_background_color" value="<?php echo COLOR_TEXT_BKG; ?>">
			</td>
		</tr>
		<tr>
			<td valign="top"></td>
			<td>
				<input type="checkbox" name="rectangle_has_border" id="rectangle_has_border" value="1" style="" <?php echo CENTRAL_TABLE_HAS_BORDER == '1'?'checked':''; ?>> 
				Has border
				<span class="description">(COLOR1BASE <div style="position:relative; top:2px; width:10px; height:10px; display:inline-block; background-color:#<?php echo COLOR1BASE; ?>; border:1px solid #000000;"></div> )</span>
			</td>
			
		</tr>
		<tr>
			<td valign="top"></td>
			<td>
				<input type="checkbox" name="rectangle_has_rounded_edges" id="rectangle_has_rounded_edges" value="1" style="" <?php echo CENTRAL_TABLE_HAS_ROUNDED_EDGES == '1'?'checked':''; ?>> 
				Rounded Edges
			</td>
		</tr>
		<tr>
			<td valign="top"></td>
			<td>
				<input type="checkbox" name="rectangle_has_shadow" id="rectangle_has_shadow" value="1" style="" <?php echo CENTRAL_TABLE_HAS_SHADOW == '1'?'checked':''; ?>> 
				Has Shadow
			</td>
		</tr>
		</table>

<?php echo write_design_window_footer('rectangle'); ?>
<?php echo write_design_window_header('H1'); ?>

		<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr><td><table width="150"></table></td><td width="100%"></td></tr>
		<tr>
			<td>Font Family:</td><td><?php echo write_design_upload_file('h1_font_family', 'H1_FONT_FAMILY', '450px'); ?></td>
		</tr>
		<tr>
			<td>Alternative Font:</td><td><input class="form-control" id="_input_h1_alternative_font" class="long_input" value="<?php echo H1_ALTERNATIVE_FONT; ?>"></td>
		</tr>
		<tr>
			<td>Color:</td><td><input class="color {onImmediateChange:'update_H1_preview(this);'} form-control " id="H1_color" name="H1_color" style="width:80px;" value="<?php echo H1_COLOR; ?>"></td>
		</tr>
		<tr>
			<td>Font Size:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="padding-top:0.6em;"><?php echo show_incremented_value('', '_input_h1_font_size', H1_FONT_SIZE, 150); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:80px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^h1_font_style" <?php echo H1_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^h1_font_weight" <?php echo H1_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>&nbsp; <input type="number" name="h1_font_weight_value" id="h1_font_weight_value" class="form-control" placeholder="" style="max-width:100px; display:inline;" value="<?php echo H1_FONT_WEIGHT_VALUE; ?>"> 
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				First Letter Color:
			</td>
			<td>
				<input class="color form-control" style="width:80px;" id="H1_font_first_letter_color" name="H1_font_first_letter_color" value="<?php echo H1_FONT_FIRST_LETTER_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Text Transform:
			</td>
			<td>
				<input type="radio" value="0" id="H1_text_transform0" name="H1_text_transform" <?php echo (int)H1_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H1_text_transform1" name="H1_text_transform" <?php echo (int)H1_TEXT_TRANSFORM == 1?'checked':''; ?>> Uppercase&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="H1_text_transform2" name="H1_text_transform" <?php echo (int)H1_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Has Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="H1_has_shadow" name="H1_has_shadow" <?php echo H1_HAS_SHADOW == '1'?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('h1_shadow', H1_SHADOW, 'h1_shadow_color', H1_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Background Color:
			</td>
			<td>
				<input type="radio" value="0" id="H1_has_background_color_none" name="H1_has_background_color" <?php echo (int)H1_HAS_BACKGROUND_COLOR == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H1_has_background_color_yes" name="H1_has_background_color" <?php echo (int)H1_HAS_BACKGROUND_COLOR == 1?'checked':''; ?>>
				<input class="color form-control" style="width:80px;" id="H1_background_color" name="H1_background_color" value="<?php echo H1_BACKGROUND_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Background Image:
			</td>
			<td>
				<?php echo write_design_upload_file('h1_background_image', 'H1_BACKGROUND_IMAGE', '100%'); ?>
			</td>
		</tr>
		<tr>
			<td>
				Has Bottom Border:<br>
				<span class="description">(COLOR2DARK  <div style="position:relative; top:2px; width:10px; height:10px; display:inline-block; background-color:#<?php echo COLOR2DARK; ?>; border:1px solid #000000;"></div> )</span>
			</td>
			<td>
				<input type="radio" value="0" id="H1_bottom_border_style0" name="H1_bottom_border_style" <?php echo (int)H1_BOTTOM_BORDER_STYLE == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H1_bottom_border_style1" name="H1_bottom_border_style" <?php echo (int)H1_BOTTOM_BORDER_STYLE == 1?'checked':''; ?>> Solid&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="H1_bottom_border_style2" name="H1_bottom_border_style" <?php echo (int)H1_BOTTOM_BORDER_STYLE == 2?'checked':''; ?>> Dashed&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="3" id="H1_bottom_border_style3" name="H1_bottom_border_style" <?php echo (int)H1_BOTTOM_BORDER_STYLE == 3?'checked':''; ?>> Dotted 
			</td>
		</tr>
		<tr>
			<td valign="top"></td>
			<td>
				<input type="checkbox" name="H1_rounded_edges" id="H1_rounded_edges" value="1" style="" <?php echo H1_HAS_ROUNDED_EDGES == '1'?'checked':''; ?>> 
				Rounded Edges
			</td>
		</tr>
		</table>

<?php echo write_design_window_footer('H1'); ?>
<?php echo write_design_window_header('H2'); ?>

		<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr><td><table width="150"></table></td><td width="100%"></td></tr>
		<tr>
			<td>Font Family:</td><td><?php echo write_design_upload_file('h2_font_family', 'H2_FONT_FAMILY', '450px'); ?></td>
		</tr>
		<tr>
			<td>Alternative Font:</td><td><input class="form-control" id="_input_h2_alternative_font" class="long_input" value="<?php echo H2_ALTERNATIVE_FONT; ?>"></td>
		</tr>
		<tr>
			<td>Color:</td><td><input class="color {onImmediateChange:'update_H2_preview(this);'} form-control " id="H2_color" name="H2_color" style="width:80px;" value="<?php echo H2_COLOR; ?>"></td>
		</tr>
		<tr>
			<td>Font Size:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="padding-top:0.6em;"><?php echo show_incremented_value('', '_input_h2_font_size', H2_FONT_SIZE, 150); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:80px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^h2_font_style" <?php echo H2_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^h2_font_weight" <?php echo H2_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>&nbsp; <input type="number" name="h2_font_weight_value" id="h2_font_weight_value" class="form-control" placeholder="" style="max-width:100px; display:inline;" value="<?php echo H2_FONT_WEIGHT_VALUE; ?>"> 
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				First Letter Color:
			</td>
			<td>
				<input class="color form-control" style="width:80px;" id="H2_font_first_letter_color" name="H2_font_first_letter_color" value="<?php echo H2_FONT_FIRST_LETTER_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Text Transform:
			</td>
			<td>
				<input type="radio" value="0" id="H2_text_transform0" name="H2_text_transform" <?php echo (int)H2_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H2_text_transform1" name="H2_text_transform" <?php echo (int)H2_TEXT_TRANSFORM == 1?'checked':''; ?>> Uppercase&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="H2_text_transform2" name="H2_text_transform" <?php echo (int)H2_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Has Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="H2_has_shadow" name="H2_has_shadow" <?php echo H2_HAS_SHADOW == '1'?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('h2_shadow', H2_SHADOW, 'h2_shadow_color', H2_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Background Color:
			</td>
			<td>
				<input type="radio" value="0" id="H2_has_background_color_none" name="H2_has_background_color" <?php echo (int)H2_HAS_BACKGROUND_COLOR == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H2_has_background_color_yes" name="H2_has_background_color" <?php echo (int)H2_HAS_BACKGROUND_COLOR == 1?'checked':''; ?>>
				<input class="color form-control" style="width:80px;" id="H2_background_color" name="H2_background_color" value="<?php echo H2_BACKGROUND_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Background Image:
			</td>
			<td>
				<?php echo write_design_upload_file('h2_background_image', 'H2_BACKGROUND_IMAGE', '100%'); ?>
			</td>
		</tr>
		<tr>
			<td>
				Has Bottom Border:<br>
				<span class="description">(COLOR2DARK  <div style="position:relative; top:2px; width:10px; height:10px; display:inline-block; background-color:#<?php echo COLOR2DARK; ?>; border:1px solid #000000;"></div> )</span>
			</td>
			<td>
				<input type="radio" value="0" id="H2_bottom_border_style0" name="H2_bottom_border_style" <?php echo (int)H2_BOTTOM_BORDER_STYLE == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="H2_bottom_border_style1" name="H2_bottom_border_style" <?php echo (int)H2_BOTTOM_BORDER_STYLE == 1?'checked':''; ?>> Solid&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="H2_bottom_border_style2" name="H2_bottom_border_style" <?php echo (int)H2_BOTTOM_BORDER_STYLE == 2?'checked':''; ?>> Dashed&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="3" id="H2_bottom_border_style3" name="H2_bottom_border_style" <?php echo (int)H2_BOTTOM_BORDER_STYLE == 3?'checked':''; ?>> Dotted 
			</td>
		</tr>
		</table>

<?php echo write_design_window_footer('H2'); ?>
<?php echo write_design_window_header('bullets'); ?>

		<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr><td><table width="120"></table></td><td width="100%"></td></tr>
		<tr>
			<td valign="top" colspan="2"><h3>Bullets</h3></td>
		</tr>
		<tr>
			<td valign="top">Style:</td>
			<td>
				<table width="410" cellspacing="0" cellpadding="0" border="0">
				<tr>
				<?php 
				$bullet_symbols = array('25A0', '274F', '2605', '2606', '272D', '2730', '2734', '2736', '25C6', '25CA', '25B6', '2713', '2714', '2716', '25CF', '279D', '27A8', '27B2', '27BD', '27BE', '25C9', '25EF', '2609', '25A3', '25D8', '273F', '2611', '2612', '261B', '279B', );
				$cols_per_row = 10;
				for ($i = 0; $i < count($bullet_symbols); $i++) {
					if ( $i % $cols_per_row == 0 && $i != 0 )
						echo '</tr><tr>';
					echo '
					<td>
						<input type="radio" name="bullets_style" id="bullets_style_'.$bullet_symbols[$i].'" value="'.$bullet_symbols[$i].'" 
						style="" '.(BULLETS_STYLE == $bullet_symbols[$i]?'checked':'').'>&nbsp;&#x'.$bullet_symbols[$i].';
					</td>';
				}
				?>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>Color:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="vertical-align: top; padding: 0 20px 0 0;"><input class="color form-control " id="bullets_color" name="bullets_color" style="width:80px;" value="<?php echo BULLETS_COLOR; ?>"></td>
					<td><?php echo show_incremented_value('', '_input_bullets_font_size', BULLETS_FONT_SIZE, 140); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:120px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^bullets_font_style" <?php echo BULLETS_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td  style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^bullets_font_weight" <?php echo BULLETS_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				</table>
				
			</td>
		</tr>
		<tr>
			<td>Has Shadow:</td>
			<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="bullets_has_shadow" name="bullets_has_shadow" <?php echo BULLETS_HAS_SHADOW == '1'?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('bullets_shadow', BULLETS_SHADOW, 'bullets_shadow_color', BULLETS_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Background Image:
			</td>
			<td>
				<?php echo write_design_upload_file('bullets_background_image', 'BULLETS_BACKGROUND_IMAGE', '100%'); ?>
			</td>
		</tr>

		<tr>
			<td valign="top" colspan="2"><h3>Check Boxes</h3></td>
		</tr>
		<tr>
			<td valign="top">Style:</td>
			<td>
				<table width="410" cellspacing="0" cellpadding="0" border="0">
				<tr>
				<?php 
				for ($i = 0; $i < count($bullet_symbols); $i++) {
					if ( $i % $cols_per_row == 0 && $i != 0 )
						echo '</tr><tr>';
					echo '
					<td>
						<input type="radio" name="checkboxes_style" id="checkboxes_style_'.$bullet_symbols[$i].'" value="'.$bullet_symbols[$i].'" 
						style="" '.(CHECKBOXES_STYLE == $bullet_symbols[$i]?'checked':'').'>&nbsp;&#x'.$bullet_symbols[$i].';
					</td>';
				}
				?>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>Color:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="vertical-align: top; padding: 0 20px 0 0;"><input class="color form-control " id="checkboxes_color" name="checkboxes_color" style="width:80px;" value="<?php echo CHECKBOXES_COLOR; ?>"></td>
					<td><?php echo show_incremented_value('', '_input_checkboxes_font_size', CHECKBOXES_FONT_SIZE, 140); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:120px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^checkboxes_font_style" <?php echo CHECKBOXES_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td  style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^checkboxes_font_weight" <?php echo CHECKBOXES_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				</table>
				
			</td>
		</tr>
		<tr>
			<td>Has Shadow:</td>
			<td>
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="checkboxes_has_shadow" name="checkboxes_has_shadow" <?php echo CHECKBOXES_HAS_SHADOW == '1'?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('checkboxes_shadow', CHECKBOXES_SHADOW, 'checkboxes_shadow_color', CHECKBOXES_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Background Image:
			</td>
			<td>
				<?php echo write_design_upload_file('checkboxes_background_image', 'CHECKBOXES_BACKGROUND_IMAGE', '100%'); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" colspan="2"><h3>Horizontal Line</h3></td>
		</tr>
		<tr>
			<td>Color:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td><input class="color form-control " id="HR_color" name="HR_color" style="width:80px;" value="<?php echo HR_COLOR; ?>"></td>
					<td>
						<input type="radio" value="1" id="HR_style1" name="HR_style" <?php echo HR_STYLE == 'solid'?'checked':''; ?>> Solid&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="2" id="HR_style2" name="HR_style" <?php echo HR_STYLE == 'dashed'?'checked':''; ?>> Dashed&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="radio" value="3" id="HR_style3" name="HR_style" <?php echo HR_STYLE == 'dotted'?'checked':''; ?>> Dotted
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>Background Image:</td>
			<td><?php echo write_design_upload_file('hr_background_image', 'HR_BACKGROUND_IMAGE', '100%'); ?></td>
		</tr>
		</table>

<?php echo write_design_window_footer('bullets'); ?>
<?php echo write_design_window_header('footer'); ?>

		<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
		<tr><td><table width="120"></table></td><td width="100%"></td></tr>
		<tr>
			<td>Font Family:</td><td><?php echo write_design_upload_file('footer_font_family', 'FOOTER_FONT_FAMILY', '450px'); ?></td>
		</tr>
		<tr>
			<td>Alternative Font:</td><td><input class="form-control" id="_input_footer_alternative_font" class="long_input" value="<?php echo FOOTER_ALTERNATIVE_FONT; ?>"></td>
		</tr>
		<tr>
			<td>Sections:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="vertical-align: top; padding: 0 20px 0 0;"><input class="color form-control " id="_input_fotr_section_color" name="fotr_section_color" style="width:80px;" value="<?php echo FOTR_SECTION_COLOR; ?>"></td>
					<td><?php echo show_incremented_value('', '_input_fotr_section_font_size', FOTR_SECTION_FONT_SIZE, 140); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:120px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^fotr_section_font_style" <?php echo FOTR_SECTION_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td  style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^fotr_section_font_weight" <?php echo FOTR_SECTION_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>Items:</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td style="vertical-align: top; padding: 0 20px 0 0;"><input class="color form-control " id="footer_color" name="footer_color" style="width:80px;" value="<?php echo FOOTER_COLOR; ?>"></td>
					<td><?php echo show_incremented_value('', '_input_footer_font_size', FOOTER_FONT_SIZE, 140); ?></td>
					<td style="padding:0 10px 0 10px;">px</td>
					<td style="min-width:120px; padding: 0 0 0 20px;">
						<input type="checkbox" value="1" id="_checkbox_italic|normal^footer_font_style" <?php echo FOOTER_FONT_STYLE == 'normal'?'':'checked'; ?>> <i>Italic</i>
					</td>
					<td  style="width:100%;">
						<input type="checkbox" value="1" id="_checkbox_bold|normal^footer_font_weight" <?php echo FOOTER_FONT_WEIGHT == 'normal'?'':'checked'; ?>> <b>Bold</b>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Text Transform:
			</td>
			<td>
				<input type="radio" value="0" id="footer_text_transform0" name="footer_text_transform" <?php echo (int)FOOTER_TEXT_TRANSFORM == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="footer_text_transform1" name="footer_text_transform" <?php echo (int)FOOTER_TEXT_TRANSFORM == 1?'checked':''; ?>> Uppercase&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="footer_text_transform2" name="footer_text_transform" <?php echo (int)FOOTER_TEXT_TRANSFORM == 2?'checked':''; ?>> Capitalize&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Text Align:
			</td>
			<td>
				<input type="radio" value="0" id="footer_text_align0" name="footer_text_align" <?php echo FOOTER_TEXT_ALIGN == 'left'?'checked':''; ?>> Left&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="footer_text_align1" name="footer_text_align" <?php echo FOOTER_TEXT_ALIGN == 'center'?'checked':''; ?>> Center&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="footer_text_align2" name="footer_text_align" <?php echo FOOTER_TEXT_ALIGN == 'right'?'checked':''; ?>> Right&nbsp;&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Has Shadow:
			</td>
			<td>
				<table width="10" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<input type="checkbox" value="1" id="footer_has_shadow" name="footer_has_shadow" <?php echo FOOTER_HAS_SHADOW == '1'?'checked':''; ?>>
					</td>
					<td>
						<?php echo write_width_and_color('footer_shadow', FOOTER_SHADOW, 'footer_shadow_color', FOOTER_SHADOW_COLOR); ?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				Background Color:
			</td>
			<td>
				<input type="radio" value="0" id="footer_has_background_color_none" name="footer_has_background_color" <?php echo (int)FOOTER_HAS_BACKGROUND_COLOR == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="footer_has_background_color_yes" name="footer_has_background_color" <?php echo (int)FOOTER_HAS_BACKGROUND_COLOR == 1?'checked':''; ?>>
				<input class="color form-control" style="width:80px;" id="footer_background_color" name="footer_background_color" value="<?php echo FOOTER_BACKGROUND_COLOR; ?>">
			</td>
		</tr>
		<tr>
			<td>
				Background Image:
			</td>
			<td>
				<?php echo write_design_upload_file('footer_background_image', 'FOOTER_BACKGROUND_IMAGE'); ?>
			</td>
		</tr>
		<tr>
			<td>
				Has Border:
			</td>
			<td>
				<input type="radio" value="0" id="_radio_footer_border_style0" name="footer_border_style" <?php echo (int)FOOTER_BORDER_STYLE == 0?'checked':''; ?>> None&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="1" id="_radio_footer_border_style1" name="footer_border_style" <?php echo (int)FOOTER_BORDER_STYLE == 1?'checked':''; ?>> Solid&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="2" id="_radio_footer_border_style2" name="footer_border_style" <?php echo (int)FOOTER_BORDER_STYLE == 2?'checked':''; ?>> Dashed&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" value="3" id="_radio_footer_border_style3" name="footer_border_style" <?php echo (int)FOOTER_BORDER_STYLE == 3?'checked':''; ?>> Dotted
			</td>
		</tr>
		<tr>
			<td>
				Height:
			</td>
			<td>
				<table style="width:100%;">
				<tr>
					<td><?php echo show_incremented_value('', '_input_footer_height', FOOTER_HEIGHT, 150); ?></td>
					<td style="padding:0 0px 0 8px;">px</td>
					<td style="width:100%;"></td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>Left Text:</td>
			<td>
				<div class="input-group input-group-sm" style="">
					<input class="form-control" id="_input_footer_text1" name="footer_text1" style="width:100%;" value="<?php echo FOOTER_TEXT1; ?>">
					<span class="input-group-btn">
						<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_footer_text1_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
					</span>
				</div>
			</td>
		</tr>
		<tr>
			<td>Right Text:</td>
			<td>
				<div class="input-group input-group-sm" style="">
					<input class="form-control" id="_input_footer_text2" name="footer_text2" style="width:100%;" value="<?php echo FOOTER_TEXT2; ?>">
					<span class="input-group-btn">
						<button class="btn btn-info btn-xs" onclick="show_inline_edit_box('_input_footer_text2_inline_edit');" title="Edit..."><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
					</span>
				</div>
			</td>
		</tr>
		</table>
<?php echo write_design_window_footer('footer'); ?>
<?php echo write_design_window_header('content'); ?>
<div class="container" style="width:100%;">
	<ul class="nav nav-tabs" role="tablist" >
		<li role="presentation" class="active"><a href="#site" role="tab" id="site-tab" data-toggle="tab" aria-controls="site">Site</a></li>
		<li role="presentation"><a href="#1stprgrph" role="tab" id="1stprgrph-tab" data-toggle="tab" aria-controls="1stprgrph">1st Paragraph</a></li>
		<li role="presentation"><a href="#2stprgrph" role="tab" id="2stprgrph-tab" data-toggle="tab" aria-controls="2stprgrph">2nd Paragraph</a></li>
		<li role="presentation"><a href="#3prgrph" role="tab" id="3prgrph-tab" data-toggle="tab" aria-controls="3prgrph">3rd Paragraph</a></li>
		<li role="presentation"><a href="#4prgrph" role="tab" id="4prgrph-tab" data-toggle="tab" aria-controls="4prgrph">4th Paragraph</a></li>
		<li role="presentation"><a href="#slider" role="tab" id="slider-tab" data-toggle="tab" aria-controls="slider">Slider</a></li>
	</ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane fade in active" id="site" aria-labelledBy="site-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr><td><table width="150"></table></td><td width="100%"></td></tr>
			<tr>
				<td style="vertical-align:top;">Site Description:</td>
				<td><textarea class="form-control" id="site_description" name="site_description" style="width:450px; height:75px;"><?php echo SITE_DESCRIPTION; ?></textarea></td>
			</tr>
			<tr>
				<td style="vertical-align:top;">Site Keywords:</td>
				<td><textarea class="form-control" id="site_keywords" name="site_keywords" style="width:450px; height:75px;"><?php echo SITE_KEYWORDS; ?></textarea></td>
			</tr>
			<tr>
				<td style="vertical-align:top;">Site Title:</td>
				<td><input class="form-control" id="site_title" name="site_title" style="width:450px;" value="<?php echo SITE_TITLE; ?>"></td>
			</tr>
			<tr>
				<td>Email:</td>
				<input type="hidden" name="_input_support_email_hex" id="_input_support_email_hex" value="<?php echo bin2hex(SUPPORT_EMAIL); ?>">
				<td><input class="form-control" id="_input_support_email" name="support_email" style="width:450px;" value="<?php echo SUPPORT_EMAIL; ?>" onchange="$(`#_input_support_email_hex`).val( string_to_hex($(`#_input_support_email`).val()) );"></td>
			</tr>
			<tr>
				<td style="vertical-align:top;">Phone:</td>
				<input type="hidden" name="_input_support_phone_hex" id="_input_support_phone_hex" value="<?php echo bin2hex(SUPPORT_PHONE); ?>">
				<td>
					<table width="100%">
						<tr>
							<td><textarea class="form-control" id="_text_support_phone" name="support_phone" style="width:250px; height:100px;" onchange="$(`#_input_support_phone_hex`).val( string_to_hex($(`#_text_support_phone`).val()) );"><?php echo SUPPORT_PHONE; ?></textarea></td>
							<td style="width:100%; vertical-align:top; padding-left:0.5em;"><?php echo show_help('For different countries use tags: {&lt;list of countries by comma>|&lt;phone number>}. An empty &quot;list of countries&quot; means default value. Example: {|+1 555 6666}{ru,by|+7 555 7777}'); ?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top;">Address:</td>
				<td>
					<table width="100%">
						<tr>
							<td><textarea class="form-control" id="_text_site_address" name="site_address" style="width:250px; height:130px;"><?php echo SITE_ADDRESS; ?></textarea></td>
							<td style="width:100%; vertical-align:top; padding-left:0.5em;"><?php echo show_help('Example: {|this is our english address}{ru,by|our Russian address}'); ?></td>
						</tr>
					</table>
				<!--td><textarea class="form-control" id="_text_site_address" name="site_address" style="width:450px; height:100px;"><?php echo SITE_ADDRESS; ?></textarea></td-->
			</tr>
			<tr>
				<td>Site Icon:</td>
				<td>
					<?php echo write_design_upload_file('site_icon', 'SITE_ICON', '100%'); ?>
				</td>
			</tr>
			
			<tr>
				<td>Site Image:</td>
				<td>
					<?php echo write_design_upload_file('site_image', 'SITE_IMAGE', '100%'); ?>
				</td>
			</tr>
			<tr>
				<td>First Page Layout:</td>
				<td>
					<input type="radio" name="first_page_layout" id="first_page_layout1" value="1" style="margin-bottom:10px;" <?php echo (int)FIRST_PAGE_LAYOUT == 1?'checked':''; ?>> List&nbsp;&nbsp;&nbsp;
					<input type="radio" name="first_page_layout" id="first_page_layout2" value="1" style="margin-bottom:10px;" <?php echo (int)FIRST_PAGE_LAYOUT == 2?'checked':''; ?>> 2x2 greed<br>
				</td>
			</tr>
			<tr>
				<td>
					Upload a file:
				</td>
				<td>
					<?php echo write_design_upload_file('upload_file', 'UPLOAD_FILE', '100%'); ?>
				</td>
			</tr>
			
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="1stprgrph" aria-labelledBy="1stprgrph-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr><td><table width="150"></table></td><td width="100%"></td></tr>
			<tr>
				<td>1st Paragraph Header:</td>
				<td><input class="form-control" id="paragraph_header_1" name="paragraph_header_1" style="width:450px;" value="<?php echo PARAGRAPH_HEADER_1; ?>"></td>
			</tr>
			<tr>
				<td  style="vertical-align:top;">1st Paragraph Text:</td>
				<td>
					<?php 
					$paragraph_text_number = 1;
					echo ($use_html_window_in_design?'<button class="btn btn-info" onclick="show_inline_edit_box(\'paragraph_text_'.$paragraph_text_number.'_inline_edit\');">Edit</button>':'').'
					<textarea class="form-control" id="paragraph_text_'.$paragraph_text_number.'" name="paragraph_text_'.$paragraph_text_number.'" style="width:100%; height:130px; '.($use_html_window_in_design?'display:none;':'').'">'.str_replace('<br>', "\n", constant('PARAGRAPH_TEXT_'.$paragraph_text_number)).'</textarea>';
					?>
				</td>
			</tr>
			<tr>
				<td>1st Paragraph Image:</td>
				<td>
					<?php echo write_design_upload_file('paragraph_image_1', 'PARAGRAPH_IMAGE_1', '80%'); ?>
				</td>
			</tr>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="2stprgrph" aria-labelledBy="2stprgrph-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr><td><table width="150"></table></td><td width="100%"></td></tr>
			<tr>
				<td>2nd Paragraph Header:</td>
				<td><input class="form-control" id="paragraph_header_2" name="paragraph_header_2" style="width:450px;" value="<?php echo PARAGRAPH_HEADER_2; ?>"></td>
			</tr>
			<tr>
				<td  style="vertical-align:top;">2nd Paragraph Text:</td>
				<td>
					<?php 
					$paragraph_text_number = 2;
					echo ($use_html_window_in_design?'<button class="btn btn-info" onclick="show_inline_edit_box(\'paragraph_text_'.$paragraph_text_number.'_inline_edit\');">Edit</button>':'').'
					<textarea class="form-control" id="paragraph_text_'.$paragraph_text_number.'" name="paragraph_text_'.$paragraph_text_number.'" style="width:100%; height:130px; '.($use_html_window_in_design?'display:none;':'').'">'.str_replace('<br>', "\n", constant('PARAGRAPH_TEXT_'.$paragraph_text_number)).'</textarea>';
					?>
				</td>
			</tr>
			<tr>
				<td>2nd Paragraph Image:</td>
				<td>
					<?php echo write_design_upload_file('paragraph_image_2', 'PARAGRAPH_IMAGE_2', '80%'); ?>
				</td>
			</tr>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="3prgrph" aria-labelledBy="3prgrph-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr><td><table width="150"></table></td><td width="100%"></td></tr>
			<tr>
				<td>3rd Paragraph Header:</td>
				<td><input class="form-control" id="paragraph_header_3" name="paragraph_header_3" style="width:450px;" value="<?php echo PARAGRAPH_HEADER_3; ?>"></td>
			</tr>
			<tr>
				<td  style="vertical-align:top;">3rd Paragraph Text:</td>
				<td>
					<?php 
					$paragraph_text_number = 3;
					echo ($use_html_window_in_design?'<button class="btn btn-info" onclick="show_inline_edit_box(\'paragraph_text_'.$paragraph_text_number.'_inline_edit\');">Edit</button>':'').'
					<textarea class="form-control" id="paragraph_text_'.$paragraph_text_number.'" name="paragraph_text_'.$paragraph_text_number.'" style="width:100%; height:130px; '.($use_html_window_in_design?'display:none;':'').'">'.str_replace('<br>', "\n", constant('PARAGRAPH_TEXT_'.$paragraph_text_number)).'</textarea>';
					?>
				</td>
			</tr>
			<tr>
				<td>3rd Paragraph Image:</td>
				<td>
					<?php echo write_design_upload_file('paragraph_image_3', 'PARAGRAPH_IMAGE_3', '80%'); ?>
				</td>
			</tr>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="4prgrph" aria-labelledBy="4prgrph-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr><td><table width="150"></table></td><td width="100%"></td></tr>
			<tr>
				<td>4th Paragraph Header:</td>
				<td><input class="form-control" id="paragraph_header_4" name="paragraph_header_4" style="width:450px;" value="<?php echo PARAGRAPH_HEADER_4; ?>"></td>
			</tr>
			<tr>
				<td  style="vertical-align:top;">4th Paragraph Text:</td>
				<td>
					<?php 
					$paragraph_text_number = 4;
					echo ($use_html_window_in_design?'<button class="btn btn-info" onclick="show_inline_edit_box(\'paragraph_text_'.$paragraph_text_number.'_inline_edit\');">Edit</button>':'').'
					<textarea class="form-control" id="paragraph_text_'.$paragraph_text_number.'" name="paragraph_text_'.$paragraph_text_number.'" style="width:100%; height:130px; '.($use_html_window_in_design?'display:none;':'').'">'.str_replace('<br>', "\n", constant('PARAGRAPH_TEXT_'.$paragraph_text_number)).'</textarea>';
					?>
				</td>
			</tr>
			<tr>
				<td>4th Paragraph Image:</td>
				<td>
					<?php echo write_design_upload_file('paragraph_image_4', 'PARAGRAPH_IMAGE_4', '80%'); ?>
				</td>
			</tr>
			</table>
		</div>
		<div role="tabpanel" class="tab-pane fade" id="slider" aria-labelledBy="slider-tab">
			<table width="100%" style="border-collapse:separate; border-spacing: 4px 4px;" border="0">
			<tr>
				<td width="10%">
					1st:
				</td>
				<td width="40%">
					<?php echo write_design_upload_file('slider_image1', 'SLIDER_IMAGE1', ''); ?>
				</td>
				<td width="10%">
					<span style="padding-left:30px;">2nd:
				</td>
				<td width="40%">
					<?php echo write_design_upload_file('slider_image2', 'SLIDER_IMAGE2', ''); ?>
				</td>
			</tr>
			<tr>
				<td>
					3rd:
				</td>
				<td>
					<?php echo write_design_upload_file('slider_image3', 'SLIDER_IMAGE3', ''); ?>
				</td>
				<td>
					<span style="padding-left:30px;">4th:&nbsp;&nbsp;
				</td>
				<td>
					<?php echo write_design_upload_file('slider_image4', 'SLIDER_IMAGE4', ''); ?>
				</td>
			</tr>
					
			</table>
		</div>
	</div>
</div>
<?php echo write_design_window_footer('content'); ?>

<div style="position:absolute; left:0px; top:0px; background-color:#ffffff; z-index:2000; display:none; border:1px solid #888888; border-radius:3px; -moz-border-radius:3px; background-image:none; background-color:#ffffff; padding:4px; box-shadow:2px 2px 1px #a0a0a0" id="edit_text_inline">
		<button class="btn btn-default btn-xs" onclick="save_inline_edit(); return false;" title="Save" style=""><img src="/images/icon-ok-big.png" border="0" style="margin:2px 2px 0px 6px; width:16px; height:16px;"></button>
		<button class="btn btn-default btn-xs" onclick="cancel_inline_edit(); return false;" title="Cancel" style=""><img src="/images/icon-delete-big.png" border="0" style="margin:2px 2px 0px 6px; width:16px; height:16px;"></button>
		<select class="" id="insert_common_param" onChange="insert_common_param();" style="/*position:absolute; top:5px; left:50px; width:100px;*/ display:inline;">
			<option value="" SELECTED>Placeholder</option>
			<?php 
			$common_params = $user_account->get_list_of_common_params();
			foreach($common_params as $key => $val)
				echo '<option value="{$'.$key.'}">{$'.$key.'}</option>';
			echo '
			<option value="{$profit_calculator}">{$profit_calculator}</option>
			<option value="{$signup_table}">{$signup_table}</option>
			<option value="{$signup_button}">{$signup_button}</option>
			<option value="{$payouts}">{$payouts}</option>';
			?>
		</select>
	<textarea id="edit_textarea" style="position:absolute; left:0px; top:30px; right:0px; bottom:0px; width:100%; z-index:2000; border:none; color:#000000;"></textarea>
</div>

<div style="position:absolute; left:0px; top:0px; background-color:transparent; background-image:url(/images/50percent_white_transparent.png); z-index:2000; display:none; padding:0px;" id="edit_image_inline"
>
	<A href="#" title="Close" onClick = "show_hide_obj('edit_image_inline', 0); return false;" class="close_button" style="position:absolute; right:0px; top:0px;"></A>
	<p style="padding:10px; padding-bottom:0px; margin-bottom:0px;">
	<input class="bordered_edit" id="edit_image_inline_name" style="width:150px;" value="">
	<form action="" enctype="multipart/form-data" method="post" style="display:inline-block;">
	<div style="width:100px; height:20px; position:relative; left:10px; top:0px; margin-left:0px; vertical-align:top; /*border:1px #00ff00 solid;*/ ">
		<button class="small_button2" id="upload_photo_inline_name" style="cursor:pointer; position:relative; top:-1px; ">Upload...</button>
		<img src="/images/wait64x64.gif" width="20" height="20" border="0" id="wait_image_upload_inline_name" alt="" style="position:relative; left:0px; top:6px; display:none; ">
		<input type="file" name="" id="edit_image_inline_upload" size="1" style="cursor:pointer; font-size:18px; width:120px; height:30px; position:absolute; right:0px; top:0px; opacity:0; filter:alpha(opacity = 0);" onchange="return upload_image(this.form, '_inline_name');" >
	</div>
	</form>
	</p>
	<p style="padding:10px; padding-top:0px; margin-top:0px;">
	<button class="small_button4" onclick = "save_inline_image();">Ok</button>
	<button class="small_button2" onclick = "show_hide_obj('edit_image_inline', 0);">Cancel</button>
	</p>
</div>

<div style="position:absolute; left:0px; top:0px; background-color:transparent; z-index:2000; display:none; border:1px dotted #00ff00; cursor:pointer;" 
	id="edit_text_indicator" title="Edit" onmouseout="this.style.display = 'none';" onclick="show_inline_edit_box();">
	<img src="/images/Modify16x16.png" border="0" style="position:absolute; top:2px; right:1px; width:16px; height:16px; <?php echo ($use_html_window_in_design?'display:none;':'') ?>">
	<img src="/images/banner_text.png" border="0" style="position:absolute; top:2px; right:1px; width:16px; height:16px; <?php echo ($use_html_window_in_design?'':'display:none;') ?>" onclick="//edit_html();">
</div>

<div style="position:absolute; left:0px; top:0px; background-color:transparent; z-index:2000; display:none; border:1px dotted #0000ff; cursor:pointer;" 
	id="edit_image_indicator" title="Edit" onmouseout="this.style.display = 'none';" onclick="show_inline_image_edit_box();">
	<img src="/images/Modify16x16.png" border="0" style="position:absolute; top:0px; right:1px;">
</div>

<script language="JavaScript">
var use_html_window_in_design = <?php echo (int)$use_html_window_in_design; ?>;

function move_design_panel_buttons()
{
	var scroll_left = 0;	
	var scroll_top = 0;
	if (document.documentElement && document.documentElement.scrollTop)
		scroll_top = document.documentElement.scrollTop;
	else 
	if (document.body && document.body.scrollTop)
		scroll_top = document.body.scrollTop;

	if (document.documentElement && document.documentElement.scrollLeft)
		scroll_left = document.documentElement.scrollLeft;
	else 
	if (document.body && document.body.scrollLeft)
		scroll_left = document.body.scrollLeft;
	
	var allElements = document.getElementsByTagName("*");
	for (var i = 0, n = allElements.length; i < n; ++i) {
		var el = allElements[i];
		if ( el.id.indexOf("design_panel_")  >= 0 && el.style.display != "none" ) { 
			if ( el.id == "design_panel_buttons" ) {
				el.style.left = (scroll_left + 20) + "px";
				el.style.top = (scroll_top + 20) + "px";	
			}
			else {
				el.style.left = (scroll_left + 80) + "px";
				el.style.top = (scroll_top + 20) + "px";	
			}
		}
	}
}

function show_design_panel(panel_name)
{
	var allElements = document.getElementsByTagName("*");
	for (var i = 0, n = allElements.length; i < n; ++i) {
		var el = allElements[i];
		if ( el.id.indexOf("design_panel_")  >= 0 && el.style.display != "none" && el.id != "design_panel_buttons" ) 
			show_hide_obj(el.id, 0);
	}

	var change_obj = document.getElementById(panel_name);
	if ( change_obj ) {
		change_obj.style.display = "inline"; 
	}
	return false;
}

function hide_design_panel(panel_name)
{
	show_hide_obj(panel_name, 0);
	return false;
}

function change_tmp_palette(color_dark1, color_base1, color_light1, color_dark2, color_base2, color_light2, color_dark3, color_base3, color_light3)
{
	change_obj = document.getElementById("palette_color_dark1"); if ( change_obj ) change_obj.color.fromString(color_dark1);
	change_obj = document.getElementById("palette_color_base1"); if ( change_obj ) change_obj.color.fromString(color_base1);
	change_obj = document.getElementById("palette_color_light1"); if ( change_obj ) change_obj.color.fromString(color_light1);

	change_obj = document.getElementById("palette_color_dark2"); if ( change_obj ) change_obj.color.fromString(color_dark2);
	change_obj = document.getElementById("palette_color_base2"); if ( change_obj ) change_obj.color.fromString(color_base2);
	change_obj = document.getElementById("palette_color_light2"); if ( change_obj ) change_obj.color.fromString(color_light2);

	change_obj = document.getElementById("palette_color_dark3"); if ( change_obj ) change_obj.color.fromString(color_dark3);
	change_obj = document.getElementById("palette_color_base3"); if ( change_obj ) change_obj.color.fromString(color_base3);
	change_obj = document.getElementById("palette_color_light3"); if ( change_obj ) change_obj.color.fromString(color_light3);

	change_obj = document.getElementById("logo_font_first_letter_color"); if ( change_obj ) change_obj.color.fromString(color_base3);
	change_obj = document.getElementById("logo_color"); if ( change_obj ) change_obj.color.fromString(color_dark3);

	change_obj = document.getElementById("background_color"); if ( change_obj ) change_obj.color.fromString(color_light1);
	change_obj = document.getElementById("H1_background_color"); if ( change_obj ) change_obj.color.fromString(color_base1);
	change_obj = document.getElementById("footer_background_color"); if ( change_obj ) change_obj.color.fromString(color_dark3);
	
}

function update_logo_preview() 
{
	var obj = document.getElementById("design_panel_logo");
	if ( obj && obj.style.display != "none" ) {
		logo = document.getElementById("logo_preview");
		if ( logo ) {
			logo.style.fontFamily = document.getElementById("logo_font_family").value;	
			logo.style.color = "#" + document.getElementById("logo_color").value;	
			if ( document.getElementById("logo_font_italic").checked )
				logo.style.fontStyle = "italic";
			else
				logo.style.fontStyle = "normal";
			if ( document.getElementById("logo_font_bold").checked )
				logo.style.fontWeight = "bold";
			else
				logo.style.fontWeight = "normal";
			first_letter = document.getElementById("logo_first_letter");
			if ( first_letter )
				first_letter.style.color = "#" + document.getElementById("logo_font_first_letter_color").value;	
		}
	}
}

function update_H1_preview() 
{
	var obj = document.getElementById("design_panel_H1");
	if ( obj && obj.style.display != "none" ) {
		H1 = document.getElementById("H1_preview");
		if ( H1 ) {
			H1.style.color = "#" + document.getElementById("H1_color").value;	
			first_letter = document.getElementById("H1_first_letter");
			if ( first_letter )
				first_letter.style.color = "#" + document.getElementById("H1_font_first_letter_color").value;	
			if ( document.getElementById("H1_has_background_color_none").checked )
				H1.style.backgroundColor = "transparent";
			else
				H1.style.backgroundColor = "#" + document.getElementById("H1_background_color").value;	

			if ( document.getElementById("H1_bottom_border_style0").checked )
				H1.style.borderBottom = "none";
			else
			if ( document.getElementById("H1_bottom_border_style1").checked )
				H1.style.borderBottom = "1px solid #<?php echo COLOR2DARK; ?>";
			else
			if ( document.getElementById("H1_bottom_border_style2").checked )
				H1.style.borderBottom = "1px dashed #<?php echo COLOR2DARK; ?>";
			else
			if ( document.getElementById("H1_bottom_border_style3").checked )
				H1.style.borderBottom = "1px dotted #<?php echo COLOR2DARK; ?>";

			if ( document.getElementById("H1_text_transform0").checked )
				H1.style.textTransform = "none";
			else
			if ( document.getElementById("H1_text_transform1").checked )
				H1.style.textTransform = "uppercase";
			else
			if ( document.getElementById("H1_text_transform2").checked )
				H1.style.textTransform = "capitalize";
		}
	}
}

function update_H2_preview() 
{
	var obj = document.getElementById("design_panel_H2");
	if ( obj && obj.style.display != "none" ) {
		H2 = document.getElementById("H2_preview");
		if ( H2 ) {
			H2.style.fontFamily = document.getElementById("H2_font_family").value;	
			H2.style.color = "#" + document.getElementById("H2_color").value;	
			H2.style.fontSize = document.getElementById("H2_font_size").value + "px";
			if ( document.getElementById("H2_font_italic").checked )
				H2.style.fontStyle = "italic";
			else
				H2.style.fontStyle = "normal";
			if ( document.getElementById("H2_font_bold").checked )
				H2.style.fontWeight = "bold";
			else
				H2.style.fontWeight = "normal";
			first_letter = document.getElementById("H2_first_letter");
			if ( first_letter )
				first_letter.style.color = "#" + document.getElementById("H2_font_first_letter_color").value;	
			if ( document.getElementById("H2_has_shadow").checked ) 
				H2.style.textShadow = document.getElementById("H2_shadow").value + "px " + document.getElementById("H2_shadow").value + "px 1px #" + document.getElementById("H2_shadow_color").value;
			else
				H2.style.textShadow = "none";
			if ( document.getElementById("H2_has_background_color_none").checked )
				H2.style.backgroundColor = "transparent";
			else
				H2.style.backgroundColor = "#" + document.getElementById("H2_background_color").value;	

			if ( document.getElementById("H2_bottom_border_style0").checked )
				H2.style.borderBottom = "none";
			else
			if ( document.getElementById("H2_bottom_border_style1").checked )
				H2.style.borderBottom = "1px solid #<?php echo COLOR2DARK; ?>";
			else
			if ( document.getElementById("H2_bottom_border_style2").checked )
				H2.style.borderBottom = "1px dashed #<?php echo COLOR2DARK; ?>";
			else
			if ( document.getElementById("H2_bottom_border_style3").checked )
				H2.style.borderBottom = "1px dotted #<?php echo COLOR2DARK; ?>";

			if ( document.getElementById("H2_text_transform0").checked )
				H2.style.textTransform = "none";
			else
			if ( document.getElementById("H2_text_transform1").checked )
				H2.style.textTransform = "uppercase";
			else
			if ( document.getElementById("H2_text_transform2").checked )
				H2.style.textTransform = "capitalize";
		}
	}
}

// Convert all applicable characters to HTML entities
function htmlentities(s)
{   
	s2 = s.replace( /[\u00A0-\u9999]/gim, function(i) {  return '&#' + i.charCodeAt(0) + ';'; });
	return s2;
}

function get_text_between_tags(inputStr, delimeterLeft, delimeterRight) 
{ 
	var posLeft = inputStr.indexOf(delimeterLeft); 
    if ( posLeft < 0 )
		return false; 
    posLeft = posLeft + delimeterLeft.length; 
    var posRight = inputStr.lastIndexOf(delimeterRight); 
	if ( posRight < 0 ) { 
		posRight = inputStr.length;
    } 
    return inputStr.substring(posLeft, posRight); 
}

function save_design(save_type, convert_string_ends_to_breaks)
{
	if (typeof save_type === 'undefined')
		save_type = "save_tmp_design";
	if (typeof convert_string_ends_to_breaks === 'undefined')
		convert_string_ends_to_breaks = !use_html_window_in_design;
	
	show_hide_obj("wait_sign", 1);
	try{
		var paramstr = "";
		paramstr = paramstr + "&BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("background_color").value);
		
		paramstr = paramstr + "&COLOR1DARK=" + string_to_hex32(document.getElementById("palette_color_dark1").value);
		paramstr = paramstr + "&COLOR1BASE=" + string_to_hex32(document.getElementById("palette_color_base1").value);
		paramstr = paramstr + "&COLOR1LIGHT=" + string_to_hex32(document.getElementById("palette_color_light1").value);
		
		paramstr = paramstr + "&COLOR2DARK=" + string_to_hex32(document.getElementById("palette_color_dark2").value);
		paramstr = paramstr + "&COLOR2BASE=" + string_to_hex32(document.getElementById("palette_color_base2").value);
		paramstr = paramstr + "&COLOR2LIGHT=" + string_to_hex32(document.getElementById("palette_color_light2").value);

		paramstr = paramstr + "&COLOR3DARK=" + string_to_hex32(document.getElementById("palette_color_dark3").value);
		paramstr = paramstr + "&COLOR3BASE=" + string_to_hex32(document.getElementById("palette_color_base3").value);
		paramstr = paramstr + "&COLOR3LIGHT=" + string_to_hex32(document.getElementById("palette_color_light3").value);
		
		if ( document.getElementById("rectangle_has_background_color_none").checked )
			paramstr = paramstr + "&COLOR_TEXT_BKG=" + string_to_hex32("none");
		else
			paramstr = paramstr + "&COLOR_TEXT_BKG=" + string_to_hex32("#" + document.getElementById("rectangle_background_color").value);
		paramstr = paramstr + "&PAGE_TEXT_COLOR_LIGHT=" + string_to_hex32(document.getElementById("text_color_light").value);
		
		if ( document.getElementById("buttons_text_have_shadow").checked )
			paramstr = paramstr + "&BUTTONS_TEXT_HAVE_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&BUTTONS_HAVE_SHADOW=" + string_to_hex32("0");
		
		if ( document.getElementById("images_have_shadow").checked )
			paramstr = paramstr + "&IMAGES_HAVE_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&IMAGES_HAVE_SHADOW=" + string_to_hex32("0");
		
		//if ( document.getElementById("images_have_rounded_edges").checked )
			//paramstr = paramstr + "&IMAGES_HAVE_ROUNDED_EDGES=" + string_to_hex32("1");
		//else
			//paramstr = paramstr + "&IMAGES_HAVE_ROUNDED_EDGES=" + string_to_hex32("0");

		if ( document.getElementById("images_layout0").checked )
			paramstr = paramstr + "&IMAGES_LAYOUT=" + string_to_hex32("0");
		else
		if ( document.getElementById("images_layout1").checked )
			paramstr = paramstr + "&IMAGES_LAYOUT=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&IMAGES_LAYOUT=" + string_to_hex32("2");
			
		paramstr = paramstr + "&INPUT_BOXES_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("input_boxes_background_color").value);
		
		if ( document.getElementById("input_boxes_have_shadow").checked )
			paramstr = paramstr + "&INPUT_BOXES_HAVE_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&INPUT_BOXES_HAVE_SHADOW=" + string_to_hex32("0");
		
		paramstr = paramstr + "&LOGO_FONT_FAMILY=" + string_to_hex32(document.getElementById("logo_font_family").value);
		paramstr = paramstr + "&LOGO_COLOR=" + string_to_hex32("#"+document.getElementById("logo_color").value);
		if ( document.getElementById("logo_color_transparent_yes").checked )
			paramstr = paramstr + "&LOGO_COLOR_TRANSPARENT=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_COLOR_TRANSPARENT=" + string_to_hex32("0");
		if ( document.getElementById("logo_font_italic").checked )
			paramstr = paramstr + "&LOGO_FONT_STYLE=" + string_to_hex32("italic");
		else
			paramstr = paramstr + "&LOGO_FONT_STYLE=" + string_to_hex32("normal");
		if ( document.getElementById("logo_font_bold").checked )
			paramstr = paramstr + "&LOGO_FONT_WEIGHT=" + string_to_hex32("bold");
		else
			paramstr = paramstr + "&LOGO_FONT_WEIGHT=" + string_to_hex32("normal");
		paramstr = paramstr + "&LOGO_FONT_FIRST_LETTER_COLOR=" + string_to_hex32("#"+document.getElementById("logo_font_first_letter_color").value);
		if ( document.getElementById("logo_font_first_letter_color_transparent_yes").checked )
			paramstr = paramstr + "&LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT=" + string_to_hex32("0");
		
		if ( document.getElementById("logo_has_shadow").checked )
			paramstr = paramstr + "&LOGO_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_HAS_SHADOW=" + string_to_hex32("0");
		
		if ( document.getElementById("logo_text_transform0").checked )
			paramstr = paramstr + "&LOGO_TEXT_TRANSFORM=" + string_to_hex32("0");
		else
		if ( document.getElementById("logo_text_transform1").checked )
			paramstr = paramstr + "&LOGO_TEXT_TRANSFORM=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_TEXT_TRANSFORM=" + string_to_hex32("2");
		
		if ( document.getElementById("logo_on_left1").checked )
			paramstr = paramstr + "&LOGO_ON_LEFT=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_ON_LEFT=" + string_to_hex32("0");

		if ( document.getElementById("logo_has_background_color_yes").checked )
			paramstr = paramstr + "&LOGO_HAS_BACKGROUND_COLOR=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&LOGO_HAS_BACKGROUND_COLOR=" + string_to_hex32("0");
		paramstr = paramstr + "&LOGO_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("logo_background_color").value);

		if ( document.getElementById("menu_style1").checked )
			paramstr = paramstr + "&MENU_STYLE=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_STYLE=" + string_to_hex32("2");
		
		if ( document.getElementById("menu_float_left").checked )
			paramstr = paramstr + "&MENU_FLOAT=" + string_to_hex32("left");
		else
			paramstr = paramstr + "&MENU_FLOAT=" + string_to_hex32("right");

		paramstr = paramstr + "&MENU_COLOR=" + string_to_hex32(document.getElementById("menu_color").value);
		
		if ( document.getElementById("menu_text_transform0").checked )
			paramstr = paramstr + "&MENU_TEXT_TRANSFORM=" + string_to_hex32("0");
		else
		if ( document.getElementById("menu_text_transform1").checked )
			paramstr = paramstr + "&MENU_TEXT_TRANSFORM=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_TEXT_TRANSFORM=" + string_to_hex32("2");
		if ( document.getElementById("menu_has_shadow").checked )
			paramstr = paramstr + "&MENU_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_HAS_SHADOW=" + string_to_hex32("0");
		if ( document.getElementById("menu_has_background_color_yes").checked )
			paramstr = paramstr + "&MENU_HAS_BACKGROUND_COLOR=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_HAS_BACKGROUND_COLOR=" + string_to_hex32("0");
		paramstr = paramstr + "&MENU_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("menu_background_color").value);
		
		if ( document.getElementById("menu_bottom_border_style0").checked )
			paramstr = paramstr + "&MENU_BORDER_STYLE=" + string_to_hex32("0");
		else
		if ( document.getElementById("menu_bottom_border_style1").checked )
			paramstr = paramstr + "&MENU_BORDER_STYLE=" + string_to_hex32("1");
		else
		if ( document.getElementById("menu_bottom_border_style2").checked )
			paramstr = paramstr + "&MENU_BORDER_STYLE=" + string_to_hex32("2");
		else
			paramstr = paramstr + "&MENU_BORDER_STYLE=" + string_to_hex32("3");

		if ( document.getElementById("menu_has_rounded_edges").checked )
			paramstr = paramstr + "&MENU_HAS_ROUNDED_EDGES=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_HAS_ROUNDED_EDGES=" + string_to_hex32("0");

		if ( document.getElementById("menu_buttons_have_rounded_edges").checked )
			paramstr = paramstr + "&MENU_BUTTONS_HAVE_ROUNDED_EDGES=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&MENU_BUTTONS_HAVE_ROUNDED_EDGES=" + string_to_hex32("0");

		if ( document.getElementById("rectangle_has_border").checked )
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_BORDER=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_BORDER=" + string_to_hex32("0");
		
		if ( document.getElementById("rectangle_has_rounded_edges").checked )
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_ROUNDED_EDGES=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_ROUNDED_EDGES=" + string_to_hex32("0");

		if ( document.getElementById("rectangle_has_shadow").checked )
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&CENTRAL_TABLE_HAS_SHADOW=" + string_to_hex32("0");

		paramstr = paramstr + "&H1_COLOR=" + string_to_hex32(document.getElementById("H1_color").value);
		
		paramstr = paramstr + "&H1_FONT_FIRST_LETTER_COLOR=" + string_to_hex32(document.getElementById("H1_font_first_letter_color").value);
		if ( document.getElementById("H1_text_transform0").checked )
			paramstr = paramstr + "&H1_TEXT_TRANSFORM=" + string_to_hex32("0");
		else
		if ( document.getElementById("H1_text_transform1").checked )
			paramstr = paramstr + "&H1_TEXT_TRANSFORM=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H1_TEXT_TRANSFORM=" + string_to_hex32("2");
		if ( document.getElementById("H1_has_shadow").checked )
			paramstr = paramstr + "&H1_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H1_HAS_SHADOW=" + string_to_hex32("0");
		if ( document.getElementById("H1_has_background_color_yes").checked )
			paramstr = paramstr + "&H1_HAS_BACKGROUND_COLOR=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H1_HAS_BACKGROUND_COLOR=" + string_to_hex32("0");
		paramstr = paramstr + "&H1_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("H1_background_color").value);
		
		if ( document.getElementById("H1_bottom_border_style0").checked )
			paramstr = paramstr + "&H1_BOTTOM_BORDER_STYLE=" + string_to_hex32("0");
		else
		if ( document.getElementById("H1_bottom_border_style1").checked )
			paramstr = paramstr + "&H1_BOTTOM_BORDER_STYLE=" + string_to_hex32("1");
		else
		if ( document.getElementById("H1_bottom_border_style2").checked )
			paramstr = paramstr + "&H1_BOTTOM_BORDER_STYLE=" + string_to_hex32("2");
		else
			paramstr = paramstr + "&H1_BOTTOM_BORDER_STYLE=" + string_to_hex32("3");

		if ( document.getElementById("H1_rounded_edges").checked )
			paramstr = paramstr + "&H1_HAS_ROUNDED_EDGES=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H1_HAS_ROUNDED_EDGES=" + string_to_hex32("0");

		paramstr = paramstr + "&H2_COLOR=" + string_to_hex32(document.getElementById("H2_color").value);
		
		paramstr = paramstr + "&H2_FONT_FIRST_LETTER_COLOR=" + string_to_hex32(document.getElementById("H2_font_first_letter_color").value);
		if ( document.getElementById("H2_text_transform0").checked )
			paramstr = paramstr + "&H2_TEXT_TRANSFORM=" + string_to_hex32("0");
		else
		if ( document.getElementById("H2_text_transform1").checked )
			paramstr = paramstr + "&H2_TEXT_TRANSFORM=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H2_TEXT_TRANSFORM=" + string_to_hex32("2");
		if ( document.getElementById("H2_has_shadow").checked )
			paramstr = paramstr + "&H2_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H2_HAS_SHADOW=" + string_to_hex32("0");
		if ( document.getElementById("H2_has_background_color_yes").checked )
			paramstr = paramstr + "&H2_HAS_BACKGROUND_COLOR=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&H2_HAS_BACKGROUND_COLOR=" + string_to_hex32("0");
		paramstr = paramstr + "&H2_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("H2_background_color").value);
		
		if ( document.getElementById("H2_bottom_border_style0").checked )
			paramstr = paramstr + "&H2_BOTTOM_BORDER_STYLE=" + string_to_hex32("0");
		else
		if ( document.getElementById("H2_bottom_border_style1").checked )
			paramstr = paramstr + "&H2_BOTTOM_BORDER_STYLE=" + string_to_hex32("1");
		else
		if ( document.getElementById("H2_bottom_border_style2").checked )
			paramstr = paramstr + "&H2_BOTTOM_BORDER_STYLE=" + string_to_hex32("2");
		else
			paramstr = paramstr + "&H2_BOTTOM_BORDER_STYLE=" + string_to_hex32("3");

		var lists = document.getElementsByTagName("input");
		for (var i = 0; i < lists.length; i++) {
			if ( lists[i].id.indexOf("bullets_style_") >= 0 && lists[i].checked ) {
				paramstr = paramstr + "&BULLETS_STYLE=" + string_to_hex32(lists[i].value);
				break;
			}
		}
		paramstr = paramstr + "&BULLETS_COLOR=" + string_to_hex32(document.getElementById("bullets_color").value);
		
		if ( document.getElementById("bullets_has_shadow").checked )
			paramstr = paramstr + "&BULLETS_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&BULLETS_HAS_SHADOW=" + string_to_hex32("0");
		
		var lists = document.getElementsByTagName("input");
		for (var i = 0; i < lists.length; i++) {
			if ( lists[i].id.indexOf("checkboxes_style_") >= 0 && lists[i].checked ) {
				paramstr = paramstr + "&CHECKBOXES_STYLE=" + string_to_hex32(lists[i].value);
				break;
			}
		}
		paramstr = paramstr + "&CHECKBOXES_COLOR=" + string_to_hex32(document.getElementById("checkboxes_color").value);
		
		if ( document.getElementById("checkboxes_has_shadow").checked )
			paramstr = paramstr + "&CHECKBOXES_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&CHECKBOXES_HAS_SHADOW=" + string_to_hex32("0");
		
		paramstr = paramstr + "&HR_COLOR=" + string_to_hex32(document.getElementById("HR_color").value);
		if ( document.getElementById("HR_style1").checked )
			paramstr = paramstr + "&HR_STYLE=" + string_to_hex32("solid");
		else
		if ( document.getElementById("HR_style2").checked )
			paramstr = paramstr + "&HR_STYLE=" + string_to_hex32("dashed");
		else
			paramstr = paramstr + "&HR_STYLE=" + string_to_hex32("dotted");

		paramstr = paramstr + "&FOOTER_COLOR=" + string_to_hex32(document.getElementById("footer_color").value);
		
		if ( document.getElementById("footer_text_transform0").checked )
			paramstr = paramstr + "&FOOTER_TEXT_TRANSFORM=" + string_to_hex32("0");
		else
		if ( document.getElementById("footer_text_transform1").checked )
			paramstr = paramstr + "&FOOTER_TEXT_TRANSFORM=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&FOOTER_TEXT_TRANSFORM=" + string_to_hex32("2");
		
		if ( document.getElementById("footer_text_align0").checked )
			paramstr = paramstr + "&FOOTER_TEXT_ALIGN=" + string_to_hex32("left");
		else
		if ( document.getElementById("footer_text_align1").checked )
			paramstr = paramstr + "&FOOTER_TEXT_ALIGN=" + string_to_hex32("center");
		else
			paramstr = paramstr + "&FOOTER_TEXT_ALIGN=" + string_to_hex32("right");

		if ( document.getElementById("footer_has_shadow").checked )
			paramstr = paramstr + "&FOOTER_HAS_SHADOW=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&FOOTER_HAS_SHADOW=" + string_to_hex32("0");
		if ( document.getElementById("footer_has_background_color_yes").checked )
			paramstr = paramstr + "&FOOTER_HAS_BACKGROUND_COLOR=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&FOOTER_HAS_BACKGROUND_COLOR=" + string_to_hex32("0");
		paramstr = paramstr + "&FOOTER_BACKGROUND_COLOR=" + string_to_hex32(document.getElementById("footer_background_color").value);
		
		paramstr = paramstr + "&BUSINESS_NAME=" + string_to_hex32(htmlentities(document.getElementById("business_name").value));
		paramstr = paramstr + "&SITE_SLOGAN=" + string_to_hex32(htmlentities(document.getElementById("site_slogan").value));
		paramstr = paramstr + "&SITE_DESCRIPTION=" + string_to_hex32(htmlentities(document.getElementById("site_description").value));
		paramstr = paramstr + "&SITE_KEYWORDS=" + string_to_hex32(htmlentities(document.getElementById("site_keywords").value));
		paramstr = paramstr + "&SITE_TITLE=" + string_to_hex32(htmlentities(document.getElementById("site_title").value));

		if ( document.getElementById("first_page_layout1").checked )
			paramstr = paramstr + "&FIRST_PAGE_LAYOUT=" + string_to_hex32("1");
		else
			paramstr = paramstr + "&FIRST_PAGE_LAYOUT=" + string_to_hex32("2");
		var paragraph_text_suffix = "";
		paramstr = paramstr + "&PARAGRAPH_HEADER_1=" + string_to_hex32(htmlentities(document.getElementById("paragraph_header_1").value));
		var s = htmlentities($("#paragraph_text_1" + paragraph_text_suffix).val());
		s = decodeURIComponent(escape(s));
		if (convert_string_ends_to_breaks) {
			while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<br>"); 
		}
		else
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
		paramstr = paramstr + "&PARAGRAPH_TEXT_1=" + string_to_hex32(s);
		
		paramstr = paramstr + "&PARAGRAPH_HEADER_2=" + string_to_hex32(htmlentities(document.getElementById("paragraph_header_2").value));
		var s = htmlentities($("#paragraph_text_2" + paragraph_text_suffix).val());
		if (convert_string_ends_to_breaks) {
			while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<br>"); 
		}
		else
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
		paramstr = paramstr + "&PARAGRAPH_TEXT_2=" + string_to_hex32(s);

		paramstr = paramstr + "&PARAGRAPH_HEADER_3=" + string_to_hex32(htmlentities(document.getElementById("paragraph_header_3").value));
		var s = htmlentities($("#paragraph_text_3" + paragraph_text_suffix).val());
		if (convert_string_ends_to_breaks) {
			while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<br>"); 
		}
		else
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
		paramstr = paramstr + "&PARAGRAPH_TEXT_3=" + string_to_hex32(s);
		
		paramstr = paramstr + "&PARAGRAPH_HEADER_4=" + string_to_hex32(htmlentities(document.getElementById("paragraph_header_4").value));
		var s = htmlentities($("#paragraph_text_4" + paragraph_text_suffix).val());
		if (convert_string_ends_to_breaks) {
			while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<br>"); 
		}
		else
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
		paramstr = paramstr + "&PARAGRAPH_TEXT_4=" + string_to_hex32(s);
		
		var elems = document.getElementsByTagName("input");
		for (var j=0; j < elems.length; j++) {
			if ( elems[j].id.indexOf("_radio_") >= 0 && elems[j].checked ) {
				var elem_name = get_text_between_tags(elems[j].id, "_radio_");
				var toogle_value = elem_name.charAt(elem_name.length - 1);
				var elem_name = elem_name.substr(0, elem_name.length - 1);
				paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(toogle_value);
			}
			if ( elems[j].id.indexOf("_color_") >= 0 ) {
				var elem_name = get_text_between_tags(elems[j].id, "_color_");
				paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(elems[j].value);
			}
			if ( elems[j].id.indexOf("_input_") >= 0 ) {
				var elem_name = get_text_between_tags(elems[j].id, "_input_");
				
				var s = htmlentities(elems[j].value); 
				if (convert_string_ends_to_breaks) {
					while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
					while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<br>"); 
				}
				else
					while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
				
				while ( s.indexOf('"') >= 0 ) s = s.replace('"', '`');

				paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(s);
			}
			if ( elems[j].id.indexOf("_checkbox_") >= 0 ) {
				var elem_name = get_text_between_tags(elems[j].id, "^");
				var checked_value = get_text_between_tags(elems[j].id, "_checkbox_", "|");
				var not_checked_value = get_text_between_tags(elems[j].id, "|", "^");
				if ( elems[j].checked ) {
					if ( $("#" + elem_name + "_value").length ) {
						checked_value = $("#" + elem_name + "_value").val();
						paramstr = paramstr + "&" + (elem_name + "_value").toUpperCase() + "=" + string_to_hex32(checked_value);
					}
					paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(checked_value);
				}
				else
					paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(not_checked_value);
			}


		}
		var elems = document.getElementsByTagName("textarea");
		for (var j=0; j < elems.length; j++) {
			if ( elems[j].id.indexOf("_text_") == 0 ) {
				var elem_name = get_text_between_tags(elems[j].id, "_text_");
				paramstr = paramstr + "&" + elem_name.toUpperCase() + "=" + string_to_hex32(htmlentities(elems[j].value));
			}
		}
		
		paramstr = save_type + '=1&' + paramstr;
		$.ajax({
			method: "POST",
			url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>",
			data: paramstr
		})
		.done(function( ajax__result ) {
			try
			{
				if ( ajax__result.indexOf("Error") >= 0 )
					show_message_box_box("Error", ajax__result, 2); 
				else
					window.location.reload();
				show_hide_obj("wait_sign", 0);
			}
			catch(error){}
		});

	}
	catch(error){
		console.error("Error: " + error);
	}
	return false;
}

function randomize_design()
{
	var palette_number = Math.floor(Math.random() * <?php echo $total_palettes; ?>);
	if ( palette_number >= <?php echo $total_palettes; ?> )
		palette_number = <?php echo $total_palettes; ?> - 1;
	change_tmp_palette(
		palette_colors[palette_number][0], palette_colors[palette_number][1], palette_colors[palette_number][2], 
		palette_colors[palette_number][3], palette_colors[palette_number][4], palette_colors[palette_number][5], 
		palette_colors[palette_number][6], palette_colors[palette_number][7], palette_colors[palette_number][8])
	
	if ( Math.random() > 0.6 )
		document.getElementById("logo_font_family").value = "Arial";
	else
	if ( Math.random() > 0.3 )
		document.getElementById("logo_font_family").value = "Times";
	else
		document.getElementById("logo_font_family").value = "Courier";
	
	document.getElementById("H2_font_family").value = document.getElementById("logo_font_family").value;
	
	if ( Math.random() > 0.5 ) {
		document.getElementById("background_color").value = palette_colors[palette_number][2];
		document.getElementById("rectangle_background_color").value = "ffffff";
	}
	else {
		document.getElementById("background_color").value = "ffffff";
		document.getElementById("rectangle_background_color").value = palette_colors[palette_number][5];
	}

	document.getElementById("logo_font_italic").checked = Math.random() > 0.5;
	document.getElementById("logo_font_bold").checked = Math.random() > 0.5;
	if ( Math.random() > 0.5 )
		document.getElementById("logo_color").value = document.getElementById("logo_font_first_letter_color").value;
	
	if ( Math.random() > 0.6 )
		document.getElementById("logo_text_transform0").checked = 1;
	else
	if ( Math.random() > 0.3 )
		document.getElementById("logo_text_transform1").checked = 1;
	else
		document.getElementById("logo_text_transform2").checked = 1;

	if ( Math.random() > 0.5 ) {
		document.getElementById("logo_on_left1").checked = 1;
		document.getElementById("menu_float_left").checked = 1;
	}
	else {
		document.getElementById("logo_on_left0").checked = 1;
		document.getElementById("menu_float_right").checked = 1;
	}

	if ( Math.random() > 0.5 )
		document.getElementById("rectangle_has_background_color_yes").checked = 1;
	else
		document.getElementById("rectangle_has_background_color_none").checked = 1;
	
	document.getElementById("rectangle_has_border").checked = Math.random() > 0.5;
	document.getElementById("rectangle_has_rounded_edges").checked = Math.random() > 0.5;
	document.getElementById("rectangle_has_shadow").checked = Math.random() > 0.5;

	
	if ( Math.random() > 0.5 ) {
		document.getElementById("menu_style1").checked = 1;
		document.getElementById("menu_color").value = "000000";
		document.getElementById("menu_text_transform2").checked = 1;
		document.getElementById("menu_has_shadow").checked = 1;
		document.getElementById("menu_background_color").value = palette_colors[palette_number][4];
	}
	else {
		document.getElementById("menu_style2").checked = 1;
		document.getElementById("menu_color").value = "ffffff";
		document.getElementById("menu_text_transform2").checked = 1;
		document.getElementById("menu_has_shadow").checked = 1;
		document.getElementById("menu_background_color").value = document.getElementById("rectangle_background_color").value;
	}
	if ( Math.random() > 0.5 )
		document.getElementById("menu_has_background_color_yes").checked = 1;
	else
		document.getElementById("menu_has_background_color_none").checked = 1;
	document.getElementById("menu_has_rounded_edges").checked = document.getElementById("rectangle_has_rounded_edges").checked;
	document.getElementById("menu_buttons_have_rounded_edges").checked = document.getElementById("rectangle_has_rounded_edges").checked;

	if ( document.getElementById("rectangle_has_border").checked && document.getElementById("menu_has_background_color_yes").checked )
		document.getElementById("menu_bottom_border_style1").checked = 1;
	else
		document.getElementById("menu_bottom_border_style0").checked = 1;
	
	document.getElementById("H1_rounded_edges").checked = document.getElementById("rectangle_has_rounded_edges").checked;
	if ( Math.random() > 0.5 ) {
		document.getElementById("H1_has_background_color_yes").checked = 1;
		if ( Math.random() > 0.5 )
			document.getElementById("H1_background_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H1_background_color").value = palette_colors[palette_number][6];
		document.getElementById("H1_color").value = "ffffff";
		if ( Math.random() > 0.5 )
			document.getElementById("H1_font_first_letter_color").value = palette_colors[palette_number][2];
		else
			document.getElementById("H1_font_first_letter_color").value = document.getElementById("H1_color").value;
		document.getElementById("H1_has_shadow").checked = 1;
		document.getElementById("H1_bottom_border_style0").checked = 1;
	}
	else {
		document.getElementById("H1_has_background_color_none").checked = 1;
		if ( Math.random() > 0.6 )
			document.getElementById("H1_color").value = "000000";
		else
		if ( Math.random() > 0.3 )
			document.getElementById("H1_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H1_color").value = palette_colors[palette_number][6];
		if ( Math.random() > 0.5 )
			document.getElementById("H1_font_first_letter_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H1_font_first_letter_color").value = document.getElementById("H1_color").value;
		document.getElementById("H1_has_shadow").checked = 0;
		if ( Math.random() > 0.75 )
			document.getElementById("H1_bottom_border_style0").checked = 1;
		else
		if ( Math.random() > 0.5 )
			document.getElementById("H1_bottom_border_style1").checked = 1;
		else
		if ( Math.random() > 0.25 )
			document.getElementById("H1_bottom_border_style2").checked = 1;
		else
			document.getElementById("H1_bottom_border_style3").checked = 1;
	}
	if ( Math.random() > 0.5 )
		document.getElementById("H1_text_transform1").checked = 1;
	else
		document.getElementById("H1_text_transform2").checked = 1;

	if ( Math.random() > 0.5 ) {
		document.getElementById("H2_has_background_color_yes").checked = 1;
		if ( Math.random() > 0.5 )
			document.getElementById("H2_background_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H2_background_color").value = palette_colors[palette_number][6];
		document.getElementById("H2_color").value = "ffffff";
		if ( Math.random() > 0.5 )
			document.getElementById("H2_font_first_letter_color").value = palette_colors[palette_number][2];
		else
			document.getElementById("H2_font_first_letter_color").value = document.getElementById("H2_color").value;
		document.getElementById("H2_has_shadow").checked = 1;
		document.getElementById("H2_shadow").value = "1";
		document.getElementById("H2_shadow_color").value = "000000";
		document.getElementById("H2_bottom_border_style0").checked = 1;
	}
	else {
		document.getElementById("H2_has_background_color_none").checked = 1;
		if ( Math.random() > 0.6 )
			document.getElementById("H2_color").value = "000000";
		else
		if ( Math.random() > 0.3 )
			document.getElementById("H2_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H2_color").value = palette_colors[palette_number][6];
		if ( Math.random() > 0.5 )
			document.getElementById("H2_font_first_letter_color").value = palette_colors[palette_number][0];
		else
			document.getElementById("H2_font_first_letter_color").value = document.getElementById("H2_color").value;
		document.getElementById("H2_has_shadow").checked = 0;
		if ( Math.random() > 0.75 )
			document.getElementById("H2_bottom_border_style0").checked = 1;
		else
		if ( Math.random() > 0.5 )
			document.getElementById("H2_bottom_border_style1").checked = 1;
		else
		if ( Math.random() > 0.25 )
			document.getElementById("H2_bottom_border_style2").checked = 1;
		else
			document.getElementById("H2_bottom_border_style3").checked = 1;
	}
	if ( Math.random() > 0.5 )
		document.getElementById("H2_text_transform1").checked = 1;
	else
		document.getElementById("H2_text_transform2").checked = 1;

	document.getElementById("H2_font_size").value = 8 + Math.floor(Math.random() * 8);
	document.getElementById("H2_font_italic").checked = Math.random() > 0.5;
	document.getElementById("H2_font_bold").checked = Math.random() > 0.5;

	if ( Math.random() > 0.5 ) {
		document.getElementById("footer_has_background_color_yes").checked = 1;
		document.getElementById("footer_color").value = "ffffff";
	
	}
	else {
		document.getElementById("footer_has_background_color_none").checked = 1;
		document.getElementById("footer_color").value = "000000";
	
	}
	if ( Math.random() > 0.5 )
		document.getElementById("footer_text_transform1").checked = 1;
	else
		document.getElementById("footer_text_transform2").checked = 1;

	if ( Math.random() > 0.6 )
		document.getElementById("footer_text_align0").checked = 1;
	else
	if ( Math.random() > 0.3 )
		document.getElementById("footer_text_align1").checked = 1;
	else
		document.getElementById("footer_text_align2").checked = 1;

	save_design();
}

function restore_design()
{
	show_hide_obj("wait_sign", 1);
	$.ajax({
		method: "POST",
		url: "<?php echo $_SERVER['SCRIPT_NAME']; ?>",
		data: "restore_design=1"
	})
	.done(function( ajax__result ) {
		try
		{
			if ( ajax__result.indexOf("Error") >= 0 )
				show_message_box_box("Error", ajax__result, 2); 
			else
				window.location.reload();
			show_hide_obj("wait_sign", 0);
		}
		catch(error){}
	});
	
}
function upload_image(upload_form, image_number)
{
	show_hide_obj("wait_image_upload" + image_number, 1);
	show_hide_obj("upload_photo" + image_number, 0);
	upload_form.submit();
}

window.setInterval("move_design_panel_buttons();", 100);
window.setInterval("update_logo_preview();", 100);
window.setInterval("update_H1_preview();", 100);
window.setInterval("update_H2_preview();", 100);

var current_edit_id = "";
var current_image_edit_id = "";
var save_edited_text = 0;
var save_in_progress = 0;
var edit_text_visible = 0;
var edit_html_is_shown = 0;
var cur_edit_text = "";

<?php if ($use_html_window_in_design) { ?>
	function show_inline_edit_box(_current_edit_id)
	{
		if ( save_in_progress )
			return false;
		$(".panel_design").hide();
		if ( typeof _current_edit_id != "undefined" )
			current_edit_id = _current_edit_id;
		id_in_edit_box = current_edit_id.substr(0, current_edit_id.indexOf("_inline_edit"));// + "_html";
		var s = $("#" + id_in_edit_box).val();
		if (!s || s.length <= 0) {
			show_inline_edit_box_text();
			return false;
		}
		ace_editor_html.getSession().setValue("", -1);
		while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", "\n"); 
		cur_edit_text = s;
		save_edited_text = 1;
		save_in_progress = 0;
		edit_html_is_shown = 1;
		
		show_edit_HTML();

		$("#edit_HTML_box").on("shown.bs.modal", function () {setTimeout(function() { 
			ace_editor_html.getSession().setValue(cur_edit_text, -1);
			ace_editor_html.renderer.updateFull();
			ace_editor_html.focus(); 
		}, 100);});

		$("#edit_text_indicator").hide();
	}
	function show_inline_edit_box_text()
	<?php 
}
else {
	?>
	function show_inline_edit_box()
	<?php
}
?>
{
	if ( save_in_progress || edit_html_is_shown )
		return false;
	save_edited_text = 1;
	show_hide_obj("edit_text_indicator", 0);

	var inline_edit_window = document.getElementById("edit_text_inline");
	if ( inline_edit_window ) {
		var object_to_edit = document.getElementById(current_edit_id);
		if ( object_to_edit ) {
			inline_edit_window.style.left = findObjectPosX(object_to_edit) + 0 + "px"; 
			inline_edit_window.style.top = findObjectPosY(object_to_edit) + 0 + "px";
			if (object_to_edit.getBoundingClientRect) {
				var rect = object_to_edit.getBoundingClientRect();
				var inline_textarea = document.getElementById("edit_textarea");
				if ( inline_textarea ) {
					var width = rect.right - rect.left;
					if (width < 400)
						width = 400;
					var height = rect.bottom - rect.top;
					if (height < 100)
						height = 100;
					inline_edit_window.style.width = width + "px";
					inline_edit_window.style.height = height + "px";
					id_in_edit_box = current_edit_id.substr(0, current_edit_id.indexOf("_inline_edit"));
					if ( document.getElementById(id_in_edit_box) ) {
						var s = document.getElementById(id_in_edit_box).value;
						while ( s.indexOf("<break/>") >= 0 ) s = s.replace("<break/>", ""); 
						inline_textarea.value = s;
					}
				}
            }
			inline_edit_window.style.display = "";
			setTimeout(function(){ edit_text_visible = 1; }, 1000);
		}
	}
}

function show_inline_image_edit_box()
{
	if ( save_in_progress )
		return false;
	save_edited_text = 1;
	show_hide_obj("edit_image_indicator", 0);

	var inline_edit_window = document.getElementById("edit_image_inline");
	if ( inline_edit_window ) {
		var object_to_edit = document.getElementById(current_image_edit_id);
		if ( object_to_edit ) {
			inline_edit_window.style.left = findObjectPosX(object_to_edit) + 0 + "px"; 
			inline_edit_window.style.top = findObjectPosY(object_to_edit) + 0 + "px";
			if (object_to_edit.getBoundingClientRect) {
				var rect = object_to_edit.getBoundingClientRect();
				w = rect.right - rect.left + 1;
				if ( w < 200 )
					w = 200;
				inline_edit_window.style.width = w + "px";
				h = rect.bottom - rect.top + 1;
				if ( h < 100 )
					h = 100;
				inline_edit_window.style.height = h + "px";
				id_in_edit_box = current_image_edit_id.substr(0, current_image_edit_id.indexOf("_inline_edit"));
				if ( document.getElementById(id_in_edit_box) ) {
					var inline_textarea = document.getElementById("edit_image_inline_name");
					if ( inline_textarea )
						inline_textarea.value = document.getElementById(id_in_edit_box).value;
				}
				var obj_item = document.getElementById("edit_image_inline_upload");
				if ( obj_item ) {
					obj_item.name = id_in_edit_box;
				}
            }
			inline_edit_window.style.display = "";
		}
	}
}

function cancel_inline_edit()
{
	if ( save_in_progress )
		return false;
	save_edited_text = 0; 
		
	var inline_edit_window = document.getElementById("edit_text_inline");
	if ( inline_edit_window ) {
		var object_to_edit = document.getElementById(current_edit_id);
		if ( object_to_edit ) {
			var inline_textarea = document.getElementById("edit_textarea");
			if ( inline_textarea ) {
				inline_textarea.value = object_to_edit.innerHTML;
			}
		}
	}
	show_hide_obj("edit_text_inline", 0);
	edit_text_visible = 0;
}

function save_inline_edit()
{
	if ( save_in_progress )
		return false;
	if ( save_edited_text ) {
		save_edited_text = 0;
		save_in_progress = 1;
		var inline_edit_window = document.getElementById("edit_text_inline");
		if ( inline_edit_window ) {
			var object_to_edit = document.getElementById(current_edit_id);
			if ( object_to_edit ) {
				var inline_textarea = document.getElementById("edit_textarea");
				if ( inline_textarea ) {
					id_in_edit_box = current_edit_id.substr(0, current_edit_id.indexOf("_inline_edit"));
					if ( document.getElementById(id_in_edit_box) ) {
						document.getElementById(id_in_edit_box).value = inline_textarea.value;
						save_design();
					}
				}
			}
		}
	}
	save_edited_text = 0;
	show_hide_obj("edit_text_inline", 0);
}

function edit_HTML_cancel()
{
	edit_html_is_shown = 0;
}

function edit_HTML_ok()
{
	edit_html_is_shown = 0;
	if ( save_in_progress )
		return false;
	if ( save_edited_text ) {
		save_edited_text = 0;
		save_in_progress = 1;
		id_in_edit_box = current_edit_id.substr(0, current_edit_id.indexOf("_inline_edit"));// + "_html";
		var s = ace_editor_html.getValue();
		if ( use_html_window_in_design ) {
			while ( s.indexOf("\n") >= 0 ) s = s.replace("\n", "<break/>");
			while ( s.indexOf("\r") >= 0 ) s = s.replace("\r", "");
		}
		$("#" + id_in_edit_box).val( s );
		s = $("#" + id_in_edit_box).val();
		save_design(undefined, false);
	}
	save_edited_text = 0;
	show_hide_obj("edit_text_inline", 0);
}
function save_inline_image()
{
	if ( save_in_progress )
		return false;
	if ( save_edited_text ) {
		save_edited_text = 0;
		save_in_progress = 1;
		var inline_edit_window = document.getElementById("edit_image_inline");
		if ( inline_edit_window ) {
			var object_to_edit = document.getElementById(current_image_edit_id);
			if ( object_to_edit ) {
				var inline_imagearea = document.getElementById("edit_image_inline_name");
				if ( inline_imagearea ) {
					id_in_edit_box = current_image_edit_id.substr(0, current_image_edit_id.indexOf("_inline_edit"));
					if ( document.getElementById(id_in_edit_box) ) {
						document.getElementById(id_in_edit_box).value = inline_imagearea.value;
						save_design();
					}
				}
			}
		}
	}
	save_edited_image = 0;
	show_hide_obj("edit_image_inline", 0);
}

function show_inline_indicator(id)
{
	if ( save_in_progress )
		return false;
	var inline_edit_window = document.getElementById("edit_text_inline");
	if ( inline_edit_window && inline_edit_window.style.display == "" )
		return false;
	var inline_edit_window = document.getElementById("edit_image_inline");
	if ( inline_edit_window && inline_edit_window.style.display == "" )
		return false;
	current_edit_id = id;
	var inline_indicator_window = document.getElementById("edit_text_indicator");
	if ( inline_indicator_window ) {
		var object_to_edit = document.getElementById(id);
		if ( object_to_edit ) {
			inline_indicator_window.style.left = findObjectPosX(object_to_edit) + 0 + "px"; 
			inline_indicator_window.style.top = findObjectPosY(object_to_edit) + 0 + "px";
			if (object_to_edit.getBoundingClientRect) {
				var rect = object_to_edit.getBoundingClientRect();
				inline_indicator_window.style.width = rect.right - rect.left + "px";
				inline_indicator_window.style.height = rect.bottom - rect.top + "px";
				
            }
			inline_indicator_window.style.display = "";
		}
	}
}

function show_image_inline_indicator(id)
{
	if ( save_in_progress )
		return false;
	var inline_edit_window = document.getElementById("edit_image_inline");
	if ( inline_edit_window && inline_edit_window.style.display == "" )
		return false;
	var inline_edit_window = document.getElementById("edit_text_inline");
	if ( inline_edit_window && inline_edit_window.style.display == "" )
		return false;
	current_image_edit_id = id;
	var inline_indicator_window = document.getElementById("edit_image_indicator");
	if ( inline_indicator_window ) {
		var object_to_edit = document.getElementById(id);
		if ( object_to_edit ) {
			inline_indicator_window.style.left = findObjectPosX(object_to_edit) + 0 + "px"; 
			inline_indicator_window.style.top = findObjectPosY(object_to_edit) + 0 + "px";
			if (object_to_edit.getBoundingClientRect) {
				var rect = object_to_edit.getBoundingClientRect();
				inline_indicator_window.style.width = rect.right - rect.left + "px";
				inline_indicator_window.style.height = rect.bottom - rect.top + "px";
				
            }
			inline_indicator_window.style.display = "";
		}
	}
}

function make_items_editable() 
{
	var inputs = getElementsByClassName_PY(document.body, "editable_item");
	for (var i=0; i < inputs.length; i++) {
		inputs_id = inputs[i].id;
		inputs[i].onmouseover = function () {
			show_inline_indicator(this.id);
		}
		inputs[i].onmouseout = function () {
			
		}
	}
	var inputs = getElementsByClassName_PY(document.body, "editable_image");
	for (var i=0; i < inputs.length; i++) {
		inputs_id = inputs[i].id;
		inputs[i].onmouseover = function () {
			show_image_inline_indicator(this.id);
		}
		inputs[i].onmouseout = function () {
			
		}
	}
}

function insert_common_param()
{
	var el = document.getElementById("insert_common_param");
	if ( el ) {
		var txtarea = document.getElementById("edit_textarea");
		if ( txtarea ) {
			text = el.value;
			var scrollPos = txtarea.scrollTop;
			var strPos = 0;
			var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
				"ff" : (document.selection ? "ie" : false ) );
			if (br == "ie") { 
				txtarea.focus();
				var range = document.selection.createRange();
				range.moveStart ('character', -txtarea.value.length);
				strPos = range.text.length;
			}
			else if (br == "ff") strPos = txtarea.selectionStart;

			var front = (txtarea.value).substring(0,strPos);  
			var back = (txtarea.value).substring(strPos,txtarea.value.length); 
			txtarea.value=front+text+back;
			strPos = strPos + text.length;
			if (br == "ie") { 
				txtarea.focus();
				var range = document.selection.createRange();
				range.moveStart ('character', -txtarea.value.length);
				range.moveStart ('character', strPos);
				range.moveEnd ('character', 0);
				range.select();
			}
			else if (br == "ff") {
				txtarea.selectionStart = strPos;
				txtarea.selectionEnd = strPos;
				txtarea.focus();
			}
			txtarea.scrollTop = scrollPos;
		}
	}
}

make_items_editable();

$(window).click(function() {
	if ( !$(event.target).closest('#edit_text_inline').length && edit_text_visible ) {
		cancel_inline_edit();
	}
});
</script>

<?php
echo '
<!-- Edit HTML Popup -->
'.generate_popup_code(
'edit_HTML', // popup_name
'
<div id="resizable_html" class="box_type1 notranslate" style="width:100%; height:400px; padding:5px; resize:both; overflow:auto; position:relative;">
	<div id="html" style="position:absolute; top:0; left:0; right:5px; bottom:5px;"></div>
</div>
<script>
	var ace_editor_html = ace.edit("html");
	ace_editor_html.setTheme("ace/theme/crimson_editor");
	ace_editor_html.getSession().setMode("ace/mode/html");
	ace_editor_html.getSession().setUseWrapMode(true);
	ace_editor_html.renderer.setShowGutter(false);
	ace_editor_html.setDisplayIndentGuides(false);
	ace_editor_html.setBehavioursEnabled(false);
	document.getElementById("resizable_html").onmouseup = function(){ 
		ace_editor_html.resize();
	};
</script>
', // popup_body
'edit_HTML_ok();', // yes_js
'<button type="button" class="btn btn-success btn-xs" onClick="return edit_HTML_yes();" style="margin:0 20px 0 0; float:right; position:relative; top:-10px;">Save</button>', // title
'<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Save', // button_yes_caption
'btn-danger', // button_cancel_class
'<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel', // button_cancel_caption
'edit_HTML_cancel();', // $cancel_js
'', // $focused_id
'width:80%;' // $modal_dialog_style
).'';

?>
</body>
</html>