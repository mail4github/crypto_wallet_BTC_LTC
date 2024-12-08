<?php
$defined_constants = get_defined_constants();
$loaded_fonts = array();
foreach ($defined_constants as $key=>$value) 
	if ( is_integer(strpos($value, CUSTOM_FONT_PREFIX.' ')) ) {
		if ( is_integer(strpos($value, ',')) )
			$value = get_text_between_tags($value, '', ',');
		$font_name = get_text_between_tags($value, CUSTOM_FONT_PREFIX.' ');
		if ( !empty($font_name) && array_search($font_name, $loaded_fonts) === false ) {
			echo '
			@font-face {
				font-family: "'.$value.'";
				src: url("/'.DIR_WS_TEMP_IMAGES_NAME.$font_name.'.woff") format("woff");
				font-weight: normal;
				font-style: normal;
			}
			@font-face {
				font-family: '.$font_name.';
				src: url("/'.DIR_WS_TEMP_IMAGES_NAME.$font_name.'.woff") format("woff");
				font-weight: normal;
				font-style: normal;
			}
			';
			$loaded_fonts[] = $font_name;
		}
	}
global $submenu_max_depth;
$footer_height = isset($submenu_max_depth) && $submenu_max_depth > 0 ? $submenu_max_depth : FOOTER_HEIGHT;
echo '
html {
  position: relative;
  min-height: 100%;
}

body{
	top: 0px !important;
	background-color:#'.BACKGROUND_COLOR.';
	color:#'.PAGE_TEXT_COLOR.'; 
	background-image:'.check_image_path(BACKGROUND_IMAGE).';
	font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	margin-bottom: '.$footer_height.'px;
	text-align:'.((int)TEXT_ALIGNMENT?'justify':'left').';
}

.body_text{
	color:#'.PAGE_TEXT_COLOR.'; 
	font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	font-size:'.TEXT_FONT_SIZE.'px;
	border:none;
	text-shadow:none;
	height:auto;
	line-height:1.1;
	font-style:normal;
	padding:0px;
	margin:0px;
}

body > .container {
  padding: '.LOGO_HEIGHT.'px 15px 0;
}

.container .text-muted {
  margin: 20px 0;
}

.row-eq-height {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display:         flex;
}

.navbar-inverse{
	background-color:'.((int)MENU_HAS_BACKGROUND_COLOR == 1?'#'.MENU_BACKGROUND_COLOR:'transparent').';
	background-image:'.(check_image_path(LOGO_CONTAINER_BACKGROUND_IMAGE)).';
	border:'.((int)MENU_BORDER_STYLE == 0?'none':((int)MENU_BORDER_STYLE == 1?'1px solid #'.COLOR2DARK:((int)MENU_BORDER_STYLE == 2?'1px dashed #'.COLOR2DARK:'1px dotted #'.COLOR2DARK))).';
	font-family: "'.MENU_FONT_FAMILY.'", verdana, arial;
	font-size: '.MENU_FONT_SIZE.'px;
	font-weight: '.MENU_FONT_WEIGHT.';
	font-style: '.MENU_FONT_STYLE.';
	text-transform:'.((int)MENU_TEXT_TRANSFORM == 0?'none':((int)MENU_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	/*z-index:20000;*/
}

.navbar-header {
	background-color:'.((int)MENU_HAS_BACKGROUND_COLOR == 1?'#'.MENU_BACKGROUND_COLOR:'transparent').';
	background-image:'.(check_image_path(LOGO_CONTAINER_BACKGROUND_IMAGE)).'; 
	border:'.((int)MENU_BORDER_STYLE == 0?'none':((int)MENU_BORDER_STYLE == 1?'1px solid #'.COLOR2DARK:((int)MENU_BORDER_STYLE == 2?'1px dashed #'.COLOR2DARK:'1px dotted #'.COLOR2DARK))).';
	-moz-border-radius:'.(MENU_HAS_ROUNDED_EDGES=='1'?'5px':'0px').'; border-radius:'.(MENU_HAS_ROUNDED_EDGES=='1'?'5px':'0px').';
	}

.navbar-inverse .navbar-nav>li>a { color: #'.MENU_COLOR.'; min-width:'.MENU_BTN_WIDTH.'px;}

li > a > .glyphicon {color: #'.COLOR3DARK.';}

/* Menu panel on hover */
.navbar-inverse .navbar-nav>.active>a:hover,.navbar-inverse .navbar-nav>li>a:hover, .navbar-inverse .navbar-nav>li>a:focus 
{ 
	background-color: '.((int)MENU_STYLE == 1 || (int)MENU_HAS_BACKGROUND_COLOR != 1?'transparent':(is_color_light(MENU_BACKGROUND_COLOR)?adjustBrightness(MENU_BACKGROUND_COLOR, -20):adjustBrightness(MENU_BACKGROUND_COLOR, 20))).';
	-moz-border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').'; border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').'; 
	color:#'.MENU_HOVER_COLOR.';
}

.navbar-inverse .navbar-nav>.active>a,.navbar-inverse .navbar-nav>.open>a,.navbar-inverse .navbar-nav>.open>a, .navbar-inverse .navbar-nav>.open>a:hover,.navbar-inverse .navbar-nav>.open>a, .navbar-inverse .navbar-nav>.open>a:hover, 
.navbar-inverse .navbar-nav>.open>a:focus 
{ 
	background-color:'.(
		(int)MENU_HAS_BACKGROUND_COLOR != 1?
		'transparent'
		:(
			(int)ITEMS_HAVE_BACKGROUND_COLOR == 1?
				'#'.ITEMS_BACKGROUND_COLOR
				:(is_color_light(MENU_BACKGROUND_COLOR)?adjustBrightness(MENU_BACKGROUND_COLOR, -100):adjustBrightness(MENU_BACKGROUND_COLOR, 220))
		)
	).';
	-moz-border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').'; 	border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').';
	text-shadow:none;
	'.((int)MENU_STYLE == 1?'':'border:1px solid '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).';').'

	
}
/* Clicked drop-down button */
.navbar-inverse .navbar-nav>.active>a,.navbar-inverse .navbar-nav>.open>a, .navbar-inverse .navbar-nav>.open>a:hover, .navbar-inverse .navbar-nav>.open>a:focus { 
	color:#'.MENU_PRESSED_COLOR.'; 
}

.btn-menu:hover { 
	color:#'.MENU_HOVER_COLOR.'; 
}

.btn-menu:link { 
	color:#'.MENU_COLOR.'; 
}

.navbar-collapse.in,
.dropdown-menu { 
	background-color: '.
	(
			(int)ITEMS_HAVE_BACKGROUND_COLOR == 1?
				'#'.ITEMS_BACKGROUND_COLOR
				:((int)MENU_HAS_BACKGROUND_COLOR == 1?
					(is_color_light(MENU_BACKGROUND_COLOR)?adjustBrightness(MENU_BACKGROUND_COLOR, -100):adjustBrightness(MENU_BACKGROUND_COLOR, 220))
					:'#'.MENU_BACKGROUND_COLOR
				)
		
	).';
}
.navbar-collapse{background-image:'.(check_image_path(MENU_BACKGROUND_IMAGE)).';}

.dropdown-menu>li>a { 
	color:'.((int)MENU_HAS_BACKGROUND_COLOR != 1?
		'#'.MENU_COLOR
		:(is_color_light(MENU_BACKGROUND_COLOR)?
			adjustBrightness(MENU_BACKGROUND_COLOR, 220)
			:adjustBrightness(MENU_BACKGROUND_COLOR, -220)
		)
	).'; 
	text-shadow:'.(DROPDOWN_MENU_HAVE_SHADOW?DROPDOWN_MENU_SHADOW.'px '.DROPDOWN_MENU_SHADOW.'px '.(DROPDOWN_MENU_SHADOW == 0?1:abs(DROPDOWN_MENU_SHADOW) - 1).'px #'.DROPDOWN_MENU_SHADOW_COLOR:'none').';
}

.dropdown-menu { font-size: '.(MENU_FONT_SIZE + 1).'px; }

.dropdown-menu>li>a:hover, 
.dropdown-menu>li>a:focus { 
	'.((int)MENU_HAS_BACKGROUND_COLOR == 1?'color: #'.MENU_COLOR.'; background-color: #'.MENU_BACKGROUND_COLOR.';':'color: '.(is_color_light(MENU_BACKGROUND_COLOR)?adjustBrightness(MENU_BACKGROUND_COLOR, 220):adjustBrightness(MENU_BACKGROUND_COLOR, -220)).'; background-color: '.(is_color_light(MENU_BACKGROUND_COLOR)?adjustBrightness(MENU_BACKGROUND_COLOR, -180):adjustBrightness(MENU_BACKGROUND_COLOR, 180)).';').'
	text-shadow:none;
}

'.((int)MENU_HAS_SHADOW == 1?'.navbar-inverse .navbar-nav>li>a {text-shadow: '.MENU_SHADOW.'px '.MENU_SHADOW.'px '.(MENU_SHADOW == 0?1:abs(MENU_SHADOW) - 1).'px #'.MENU_SHADOW_COLOR.';}':'').'

.logo_text_last,
.logo_text_first{
	font-family:"'.BUSINESS_NAME_FONT_FAMILY.'", verdana, arial;
	font-size:'.BUSINESS_NAME_FONT_SIZE.'px; 
	line-height:'.round(BUSINESS_NAME_FONT_SIZE * 0.8).'px; 
	font-weight:'.BUSINESS_NAME_FONT_WEIGHT.'; font-style:'.BUSINESS_NAME_FONT_STYLE.'; 
	color:'.((int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'transparent':LOGO_FONT_FIRST_LETTER_COLOR).'; 
	padding-top:0px; margin-top:10px; 
	text-shadow:'.((int)LOGO_HAS_SHADOW == 1 && (int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT != 1 && (int)LOGO_COLOR_TRANSPARENT != 1?LOGO_SHADOW_WIDTH.'px '.LOGO_SHADOW_WIDTH.'px '.(LOGO_SHADOW_WIDTH == 0?1:abs(LOGO_SHADOW_WIDTH) - 1).'px '.LOGO_SHADOW_COLOR:'none').';
	background-image:none;
	text-transform:'.((int)LOGO_TEXT_TRANSFORM == 0?'none':((int)LOGO_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
}
.logo_text_last{color:'.((int)LOGO_COLOR_TRANSPARENT == 1?'transparent':LOGO_COLOR).'; }

.top_slogan a,
.top_slogan{
	font-family:"'.LOGO_FONT_FAMILY.'", verdana, arial; 
	font-size:'.round(LOGO_FONT_SIZE).'px; 
	line-height:'.round(LOGO_FONT_SIZE).'px; 
	font-weight:normal; font-style:'.LOGO_FONT_STYLE.'; 
	color:'.((int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'transparent':LOGO_FONT_FIRST_LETTER_COLOR).'; 
	text-shadow:'.((int)LOGO_HAS_SHADOW == 1 && (int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT != 1 && (int)LOGO_COLOR_TRANSPARENT != 1?LOGO_SHADOW_WIDTH.'px '.LOGO_SHADOW_WIDTH.'px '.(LOGO_SHADOW_WIDTH == 0?1:abs(LOGO_SHADOW_WIDTH) - 1).'px '.LOGO_SHADOW_COLOR:'none').';
	background-image:none;
	text-transform:'.((int)LOGO_TEXT_TRANSFORM == 0?'none':((int)LOGO_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	padding-top:0px; margin-top:'.round(LOGO_FONT_SIZE * 0.2).'px; 
}

.top_slogan a{border-bottom:dotted 1px '.((int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'transparent':LOGO_FONT_FIRST_LETTER_COLOR).';}
.top_slogan a:hover{text-decoration:none;}

.navbar-brand{background-image:none; padding:0px;}

#logo_container{border:none; background-color:'.((int)LOGO_HAS_BACKGROUND_COLOR?'#'.LOGO_BACKGROUND_COLOR:'transparent').'; background-image:none;  }

label{color:#'.PAGE_TEXT_COLOR.'; font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; }

.navbar-inverse .navbar-toggle .icon-bar {
  background-color: #'.BTN_COLOR_ACTIVE.';
  box-shadow:'.(defined('BUTTONS_TEXT_SHADOW_COLOR') && defined('BUTTONS_TEXT_HAVE_SHADOW') && defined('BUTTONS_TEXT_SHADOW') ? 
	( (int)BUTTONS_TEXT_HAVE_SHADOW ? 
		BUTTONS_TEXT_SHADOW.'px '.BUTTONS_TEXT_SHADOW.'px #'.BUTTONS_TEXT_SHADOW_COLOR
		:'none')
	:'-1px 0px #000000'
	).';
}

.navbar-toggle,
.btn-danger,
.btn-warning,
.btn-info,
.btn-success,
.btn-primary
{
	font-family:"'.BUTTONS_FONT_FAMILY.'", verdana, arial; 
	-moz-box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW > 0?BUTTONS_SHADOW:'6').'px #'.BUTTONS_SHADOW_COLOR:'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').'
	;
	-webkit-box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW>0?BUTTONS_SHADOW:'6').'px #'.BUTTONS_SHADOW_COLOR:'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').'
	;
	box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW>0?BUTTONS_SHADOW:'6').'px #'.BUTTONS_SHADOW_COLOR:'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').'
	;
	-moz-border-radius:'.BUTTONS_RADIUS.'px; border-radius:'.BUTTONS_RADIUS.'px;
	margin-bottom:6px;
	text-shadow:'.(defined('BUTTONS_TEXT_HAVE_SHADOW') && defined('BUTTONS_TEXT_SHADOW') ?
		((int)BUTTONS_TEXT_HAVE_SHADOW ? BUTTONS_TEXT_SHADOW.'px '.BUTTONS_TEXT_SHADOW.'px '.(BUTTONS_TEXT_SHADOW - 1 >= 0 ? BUTTONS_TEXT_SHADOW - 1 : 0).'px #444444' : 'none')
	:'-1px 0px #000000'
	).';
}

.btn-primary{color:#'.BTN_COLOR_ACTIVE.'; 
text-shadow:'.(defined('BUTTONS_TEXT_SHADOW_COLOR') && defined('BUTTONS_TEXT_HAVE_SHADOW') && defined('BUTTONS_TEXT_SHADOW') ? 
	((int)BUTTONS_TEXT_HAVE_SHADOW?BUTTONS_TEXT_SHADOW.'px '.BUTTONS_TEXT_SHADOW.'px '.(BUTTONS_TEXT_SHADOW - 1 >= 0?BUTTONS_TEXT_SHADOW - 1:0).'px #'.BUTTONS_TEXT_SHADOW_COLOR:'none')
	:'-1px 0px #000000'
	).';
}

.navbar-toggle,
.top_form > .top_texts form button,
.top_form > form > button{
	-moz-box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW>0?BUTTONS_SHADOW:'6').'px '.(is_color_light(LOGO_BACKGROUND_COLOR)?adjustBrightness(LOGO_BACKGROUND_COLOR, 20):adjustBrightness(LOGO_BACKGROUND_COLOR, -20)):'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').';
	-webkit-box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW>0?BUTTONS_SHADOW:'6').'px '.(is_color_light(LOGO_BACKGROUND_COLOR)?adjustBrightness(LOGO_BACKGROUND_COLOR, 20):adjustBrightness(LOGO_BACKGROUND_COLOR, -20)):'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').';
	box-shadow:'.(BUTTONS_HAVE_SHADOW=='1'?''.BUTTONS_SHADOW.'px '.BUTTONS_SHADOW.'px '.(BUTTONS_SHADOW>0?BUTTONS_SHADOW:'6').'px '.(is_color_light(LOGO_BACKGROUND_COLOR)?adjustBrightness(LOGO_BACKGROUND_COLOR, 20):adjustBrightness(LOGO_BACKGROUND_COLOR, -20)):'none').'
	'.((int)BUTTONS_GRADIENT_BKG == 1?', inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2)':'').';
}

.navbar-toggle:hover,
.btn-danger:hover,
.btn-warning:hover,
.btn-info:hover,
.btn-success:hover,
.btn-primary:hover {
	color:#'.BTN_COLOR_HOVER.';
}

.navbar-toggle,
.btn-primary {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid #'.BUTTONS_OUTLINE.';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #'.BTN_BKG_COLOR_TOP.'), color-stop(1, #'.BTN_BKG_COLOR_BOTTOM.') );
	background:-moz-linear-gradient( center top, #'.BTN_BKG_COLOR_TOP.' 5%, #'.BTN_BKG_COLOR_BOTTOM.' 100% );':'').'
	background-color:#'.BTN_BKG_COLOR_TOP.';
}
.navbar-toggle:hover,
.btn-primary:hover {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid '.adjustBrightness(BUTTONS_OUTLINE, -20).';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #'.BTN_BKG_COLOR_BOTTOM.'), color-stop(1, #'.BTN_BKG_COLOR_TOP.') );
	background:-moz-linear-gradient( center top, #'.BTN_BKG_COLOR_BOTTOM.' 5%, #'.BTN_BKG_COLOR_TOP.' 100% );':'').'
	background-color:#'.BTN_BKG_COLOR_BOTTOM.';
}

.btn-success {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid '.adjustBrightness('5cb85c', -30).';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #5cb85c), color-stop(1, '.adjustBrightness('5cb85c', -20).') );
	background:-moz-linear-gradient( center top, #5cb85c 5%, '.adjustBrightness('5cb85c', -20).' 100% );':'').'
	background-color:#5cb85c;
}
.btn-success:hover {
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness('5cb85c', -20).'), color-stop(1, #5cb85c) );
	background:-moz-linear-gradient( center top, '.adjustBrightness('5cb85c', -20).' 5%, #5cb85c 100% );':'').'
	background-color:'.adjustBrightness('5cb85c', -20).';
}

.btn-info {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid '.adjustBrightness('5bc0de', -30).';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #5bc0de), color-stop(1, '.adjustBrightness('5bc0de', -20).') );
	background:-moz-linear-gradient( center top, #5bc0de 5%, '.adjustBrightness('5bc0de', -20).' 100% );':'').'
	background-color:#5bc0de;
}
.btn-info:hover {
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness('5bc0de', -20).'), color-stop(1, #5bc0de) );
	background:-moz-linear-gradient( center top, '.adjustBrightness('5bc0de', -20).' 5%, #5bc0de 100% );':'').'
	background-color:'.adjustBrightness('5bc0de', -20).';
}

.btn-warning {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid '.adjustBrightness('f0ad4e', -30).';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #f0ad4e), color-stop(1, '.adjustBrightness('f0ad4e', -20).') );
	background:-moz-linear-gradient( center top, #f0ad4e 5%, '.adjustBrightness('f0ad4e', -20).' 100% );':'').'
	background-color:#f0ad4e;
}
.btn-warning:hover {
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness('f0ad4e', -20).'), color-stop(1, #f0ad4e) );
	background:-moz-linear-gradient( center top, '.adjustBrightness('f0ad4e', -20).' 5%, #f0ad4e 100% );':'').'
	background-color:'.adjustBrightness('f0ad4e', -20).';
}

.btn-danger {
	border:'.BUTTONS_OUTLINE_SIZE.'px solid '.adjustBrightness('d9534f', -30).';
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #d9534f), color-stop(1, '.adjustBrightness('d9534f', -20).') );
	background:-moz-linear-gradient( center top, #d9534f 5%, '.adjustBrightness('d9534f', -20).' 100% );':'').'
	background-color:#d9534f;
}
.btn-danger:hover {
	'.((int)BUTTONS_GRADIENT_BKG?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness('d9534f', -20).'), color-stop(1, #d9534f) );
	background:-moz-linear-gradient( center top, '.adjustBrightness('d9534f', -20).' 5%, #d9534f 100% );':'').'
	background-color:'.adjustBrightness('d9534f', -20).';
}

.btn-menu
{
	color: #'.MENU_COLOR.'; 
	font-family: "'.MENU_FONT_FAMILY.'", verdana, arial;
	font-size: '.MENU_FONT_SIZE.'px;
	font-weight: '.MENU_FONT_WEIGHT.';
	font-style: '.MENU_FONT_STYLE.';
	text-transform:'.((int)MENU_TEXT_TRANSFORM == 0?'none':((int)MENU_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	'.((int)MENU_HAS_SHADOW == 1?'text-shadow: '.MENU_SHADOW.'px '.MENU_SHADOW.'px '.(MENU_SHADOW == 0?1:abs(MENU_SHADOW) - 1).'px #'.MENU_SHADOW_COLOR.';':'').'

	margin-top:'.round((MENU_HEIGHT - 34) / 2 ).'px; 
	background-image:'.(check_image_path(MENU_BTN_BACKGROUND_IMAGE)).';
	background-color:#'.MENU_BACKGROUND_COLOR.';
	box-shadow: inset 1px 0 1px 0px rgba(255,255,255,0.2), inset -1px 0 1px 0px rgba(255,255,255,0.2), inset 0px -1px 0px rgba(255,255,255,0.1), inset 0px 1px 0px rgba(255,255,255,0.2);
	border:1px solid '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).';
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness(MENU_BACKGROUND_COLOR, 20).'), color-stop(1, '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).') );
	background:-moz-linear-gradient( center top, '.adjustBrightness(MENU_BACKGROUND_COLOR, 20).' 5%, '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).' 100% );
	-moz-border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').'; 	border-radius:'.(MENU_BUTTONS_HAVE_ROUNDED_EDGES=='1'?'5px':'0px').';
}
.btn-menu:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).'), color-stop(1, '.adjustBrightness(MENU_BACKGROUND_COLOR, 20).') );
	background:-moz-linear-gradient( center top, '.adjustBrightness(MENU_BACKGROUND_COLOR, -20).' 5%, '.adjustBrightness(MENU_BACKGROUND_COLOR, 20).' 100% );
}

'.(MENU_FLOAT == 'left'?'':'ul > .btn-group,').'
ul > .btn-menu
{margin-left:2px; margin-right:2px;}

#navbar > ul > .btn-group {}

#navbar > ul > .btn-group a,
#navbar > ul > .btn-group button,
#navbar > ul > a
{box-shadow:'.((int)MENU_BUTTONS_HAVE_SHADOW ? MENU_BUTTONS_SHADOW.'px '.MENU_BUTTONS_SHADOW.'px '.(MENU_BUTTONS_SHADOW > 0?MENU_BUTTONS_SHADOW:'6').'px  #'.MENU_BUTTONS_SHADOW_COLOR:'none').';}

.first_page_image{
	margin:10px;
	'.((int)IMAGES_LAYOUT == 0?'margin-right:20px;':'').'
	'.
	(
		(int)FIRST_PAGE_LAYOUT == 1? /* list */
			'height:auto; '.((int)IMAGES_LAYOUT == 0?'float:left; width:30%;':((int)IMAGES_LAYOUT == 1?'float:right; width:30%;':'margin-left:auto; margin-right:auto; display:block; width:80%; ')).' '
		: /* 2x2 grid */ 
		( 
			(int)IMAGES_LAYOUT == 0 /* Left */ || (int)IMAGES_LAYOUT == 1 /* Right */ ? 
				'width:50%; height:auto; float:'.((int)IMAGES_LAYOUT == 0?'left; margin-left:0px;':'right; margin-right:0px;').' '
				:'width:80%; height:auto; margin-left:auto; margin-right:auto; display:block;
' /* Top */
		)
	).'
	'.((int)IMAGES_HAVE_BORDER > 0 ? 'border: '.IMAGES_HAVE_BORDER.'px solid #'.IMAGES_COLOR.'; padding:2px;' : '').'
	-moz-box-shadow:'.((int)IMAGES_HAVE_SHADOW == 1 && defined('IMAGES_SHADOW') ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
	-webkit-box-shadow:'.((int)IMAGES_HAVE_SHADOW == 1 && defined('IMAGES_SHADOW') ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
	box-shadow:'.((int)IMAGES_HAVE_SHADOW == 1 && defined('IMAGES_SHADOW') ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
	-moz-border-radius:'.((int)IMAGES_HAVE_ROUNDED_EDGES > 0 ? IMAGES_HAVE_ROUNDED_EDGES.'px':'none').';
	border-radius:'.((int)IMAGES_HAVE_ROUNDED_EDGES >0 ? IMAGES_HAVE_ROUNDED_EDGES.'px':'none').';
	background-color:'.((int)IMAGES_HAVE_BKG_COLOR == 1 ? '#'.IMAGES_BKG_COLOR:'transparent').'; 
}

.central_container{
	background-color:'.COLOR_TEXT_BKG.'; 
	background-image:none;
	border:'.(CENTRAL_TABLE_HAS_BORDER=='1'?'1px solid #'.COLOR1BASE:'none').';
	-moz-border-radius:'.(CENTRAL_TABLE_HAS_ROUNDED_EDGES=='1'?'5px':'0px').'; border-radius:'.(CENTRAL_TABLE_HAS_ROUNDED_EDGES=='1'?'5px':'0px').';
	-moz-box-shadow:'.(CENTRAL_TABLE_HAS_SHADOW=='1'?'0px 0px 6px #000000':'none').';
	-webkit-box-shadow:'.(CENTRAL_TABLE_HAS_SHADOW=='1'?'0px 0px 6px #000000':'none').';
	box-shadow:'.(CENTRAL_TABLE_HAS_SHADOW=='1'?'0px 0px 6px #000000':'none').';
}

.small_center_cantainer{max-width:800px;}
.right_info_box{margin:0px; padding:0px 0px 0px 5px;}
.invisible_on_big_screen{display:none;}
.invisible_object{display:none;}
.visible_on_big_screen{}
.block_visible_on_big_screen{display:block;}
.large_title{line-height:normal; font-family:"'.H1_FONT_FAMILY.'", verdana, arial; font-weight:'.H1_FONT_WEIGHT.'; font-style:'.H1_FONT_STYLE.'; text-shadow:'.((int)H1_HAS_SHADOW == 1?H1_SHADOW.'px '.H1_SHADOW.'px 1px #'.H1_SHADOW_COLOR:'none').';
text-transform:'.((int)H1_TEXT_TRANSFORM == 0?'none':((int)H1_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
}

.col-pad-1, .col-pad-2, .col-pad-3, .col-pad-4, .col-pad-5, .col-pad-6, .col-pad-7, .col-pad-8, .col-pad-9, .col-pad-10, .col-pad-11, .col-pad-12 {
  position: relative;
  min-height: 1px;
  padding-right: 15px;
  padding-left: 15px;
}

.footer_copyright{padding: 0 2em 0 2em;}

#google_translate_element{position:absolute; background-color:transparent; z-index:2000; margin:0; width:auto; overflow:hidden; top:unset; bottom:10px; right:10px; max-height:28px !important;}

@media (min-width: 580px) {
  .col-pad-1, .col-pad-2, .col-pad-3, .col-pad-4, .col-pad-5, .col-pad-6, .col-pad-7, .col-pad-8, .col-pad-9, .col-pad-10, .col-pad-11, .col-pad-12 {
    float: left;
  }
  .col-pad-12 {
    width: 100%;
  }
  .col-pad-11 {
    width: 91.66666667%;
  }
  .col-pad-10 {
    width: 83.33333333%;
  }
  .col-pad-9 {
    width: 75%;
  }
  .col-pad-8 {
    width: 66.66666667%;
  }
  .col-pad-7 {
    width: 58.33333333%;
  }
  .col-pad-6 {
    width: 50%;
  }
  .col-pad-5 {
    width: 41.66666667%;
  }
  .col-pad-4 {
    width: 33.33333333%;
  }
  .col-pad-3 {
    width: 25%;
  }
  .col-pad-2 {
    width: 16.66666667%;
  }
  .col-pad-1 {
    width: 8.33333333%;
  }
  .col-pad-pull-12 {
    right: 100%;
  }
}

@media (max-width: 767px) {
	h1{font-size: '.round(H1_FONT_SIZE * 0.8).'px;}
	h1:first-letter{font-size: '.round(H1_FONT_SIZE * 0.8).'px;}
	h2{font-size: '.round(H2_FONT_SIZE * 0.8).'px;}
	h2:first-letter{font-size: '.round(H2_FONT_SIZE * 0.8).'px;}
	body{font-size:'.round(TEXT_FONT_SIZE * 0.8).'px;}
	.edit_sorted_table_header_text{font-size:'.round(TEXT_FONT_SIZE * 0.6).'px;}
	.form-control{font-size:'.round(TEXT_FONT_SIZE * 1.2 * 0.8).'px; line-height:'.round(TEXT_FONT_SIZE * 1.5 * 0.8).'px; }
	.top_form{display:none;}
	.small_center_cantainer{width:100%;}
	.collapsed_logo{display:inline; height:50px;}
	.navbar-brand{background-image:'.check_image_path(LOGO_BACKGROUND_IMAGE).'; width:'.round(LOGO_IMG_WIDTH / LOGO_IMG_HEIGHT * 50).'px; height:50px;}
	#logo_container{display:none;}
	#blank_div_for_fixed_menu{height:'.(MENU_HEIGHT - 50).'px;}
	footer{display:none;}
	.side_banner,.bottom_banner{display:none;}
	.menu_button{float:left; height:auto; }
	.main_page_1st_graph{height:200px !important;}
	#graph_container_wait{display:none; width:0px; height:0px; position:relative; left:50%; top:-100px;}
	
	.btn-group-justified {display:inline-box; width: auto; table-layout:inherit; border-collapse: separate;}
	.btn-group-justified > .btn,
	.btn-group-justified > .btn-group {display: inline; float:left; width:auto; box-sizing:content-box;}
	
	.btn-group-justified > .btn-group .btn {width: auto;}
	.btn-group-justified > .btn-group .dropdown-menu {left: auto;}
	.currency_symbol_on_graph{font-size:26px;}
	.scroll_table > th,
	.scroll_table > td{min-width:50px;}
	.scroll_table4 > th,
	.scroll_table4 > td{min-width:50px;}
	.scroll_table5 > th,
	.scroll_table5 > td{min-width:55px;}
	.code_edit{font-size:'.round(TEXT_FONT_SIZE * 0.6).'px;}
	.description{font-size:'.round(TEXT_FONT_SIZE * 0.5).'px;}
	.news_date{min-width:50px;}
	.col-height{}
	.payment_option_table{width:100%;}
	.right_info_box{display:none;}
	.limits_cell{height:auto; border-bottom:none;}
	.deposit_cell{padding:0px;}
	.chat_td_text{font-size:10px;}
	.invisible_on_big_screen{display:block;}
	.visible_on_big_screen{display:none;}
	.block_visible_on_big_screen{display:none;}
	.box_type2{font-size:'.round(BOX_TYPE2_FONT_SIZE * 0.8).'px;}
	.box_type3{font-size:'.round(BOX_TYPE3_FONT_SIZE * 0.8).'px;}
	.large_font{font-size:12px;}
	.extra_large_font{font-size:22px;  line-height:22px;}
	.footer_copyright,.footer a,.footer_text a,.footer_text{font-size:'.round(FOOTER_FONT_SIZE * 1.2).'px;}
	.btn-link{font-size:15px;}
	.collapsed_balance{}
	#share_price_right_part,
	#share_price_left_part{text-align:center;}
	.large_title{font-size:40px;}
	
	.dropdown-menu>li>a,
	.navbar-collapse,
	.navbar-collapse.in,
	.dropdown-menu,
	.navbar-default .navbar-nav .open .dropdown-menu > li > a,
	.navbar-default .navbar-nav .open .dropdown-menu > li > a:hover,
	.navbar-default .navbar-nav .open .dropdown-menu > li > a:focus,
	.navbar-inverse .navbar-nav>li>a,
	.navbar-inverse .navbar-nav>li>a:hover, 
	.navbar-inverse .navbar-nav>li>a:focus,
	.navbar-inverse .navbar-nav>.active>a,
	.navbar-inverse .navbar-nav>.active>a:hover,
	.navbar-inverse .navbar-nav>.open>a, 
	.navbar-inverse .navbar-nav>.open>a:hover,
	.navbar-inverse .navbar-nav>.open>a:focus 
	{ 
		background-color:#ffffff;
		text-shadow:none;
		color:#000000;
		background-image:none;
	}
	.navbar-inverse .navbar-nav>li>a {font-weight:bold;}
}
@media (min-width: 768px) and (max-width: 991px) {
	h1{font-size: '.round(H1_FONT_SIZE * 0.9).'px;}
	h1:first-letter{font-size: '.round(H1_FONT_SIZE * 0.9).'px;}
	h2{font-size: '.round(H2_FONT_SIZE * 0.9).'px;}
	h2:first-letter{font-size: '.round(H2_FONT_SIZE * 0.9).'px;}
	body{font-size:'.round(TEXT_FONT_SIZE * 0.9).'px;}
	.edit_sorted_table_header_text{font-size:'.round(TEXT_FONT_SIZE * 0.7).'px;}
	.form-control{font-size:'.round(TEXT_FONT_SIZE * 1.2 * 0.85).'px; line-height:'.round(TEXT_FONT_SIZE * 1.5 * 0.85).'px;}
	.top_form{display:none;}
	.small_center_cantainer{width:90%;}
	.menu_button{float:left;}
	#logo_container{display:none;}
	.collapsed_logo{display:none;}
	.navbar-brand{width:'.LOGO_IMG_WIDTH.'px; height:'.LOGO_HEIGHT.'px;}
	#blank_div_for_fixed_menu{height:'.(LOGO_HEIGHT + MENU_HEIGHT - 50).'px;}
	.side_banner,.bottom_banner{display:none;}
	#footer_container{display:none;}
	.main_page_1st_graph{height:300px !important;}
	#graph_container_wait{display:none; width:0px; height:0px; position:relative; left:50%; top:-150px;}
	#main_page_1st_graph_buttons_justified{display:none;}
	#main_page_1st_graph_buttons_not_justified{display:;}
	.currency_symbol_on_graph{font-size:30px;}
	.scroll_table > th,
	.scroll_table > td{width:50px;}
	.scroll_table4 > th,
	.scroll_table4 > td{min-width:40px;}
	.scroll_table5 > th,
	.scroll_table5 > td{min-width:90px;}
	.code_edit{font-size:'.round(TEXT_FONT_SIZE * 0.7).'px;}
	.description{font-size:'.round(TEXT_FONT_SIZE * 0.6).'px;}
	.news_date{min-width:150px;}
	.col-height {min-height:250px;}
	.payment_option_table{width:200px;} 
	.right_info_box{padding-left:0px;}
	.limits_cell{height:auto; border-bottom:none;}
	.deposit_cell{padding:0px;}
	.chat_td_text{font-size:10px;}
	.large_font{font-size:20px;}
	.extra_large_font{font-size:40px;  line-height:40px;}
	.box_type2{font-size:'.round(BOX_TYPE2_FONT_SIZE * 0.9).'px;}
	.box_type3{font-size:'.round(BOX_TYPE3_FONT_SIZE * 0.9).'px;}
	.footer_copyright,.footer a,.footer_text a,.footer_text{font-size:'.round(FOOTER_FONT_SIZE * 1.1).'px;}
	.btn-link{font-size:16px;}
	.collapsed_balance{display:none}
	/*.invisible_on_big_screen{display:block;}
	.visible_on_big_screen{display:none;}*/
	.block_visible_on_big_screen{display:none;}
	#share_price_right_part,
	#share_price_left_part{text-align:center;}
	.large_title{font-size:50px;}
}
@media (min-width: 992px) and (max-width: 1199px) {
	h1{font-size: '.round(H1_FONT_SIZE * 1).'px;}
	h1:first-letter{font-size: '.round(H1_FONT_SIZE * 1).'px;}
	h2{font-size: '.round(H2_FONT_SIZE * 1).'px;}
	h2:first-letter{font-size: '.round(H2_FONT_SIZE * 1).'px;}
	body{font-size:'.round(TEXT_FONT_SIZE * 1).'px;}
	.edit_sorted_table_header_text{font-size:'.round(TEXT_FONT_SIZE * 0.8).'px;}
	.form-control{font-size:'.round(TEXT_FONT_SIZE * 1.2 * 0.9).'px; line-height:'.round(TEXT_FONT_SIZE * 1.5 * 0.9).'px;}
	.small_center_cantainer{width:80%;}
	.menu_button{'.(MENU_FLOAT == 'left'?'':'float:right;').' height:'.MENU_HEIGHT.'px; }
	.collapsed_logo{display:none;}
	.navbar-brand{width:'.LOGO_IMG_WIDTH.'px; height:'.LOGO_HEIGHT.'px;}
	#blank_div_for_fixed_menu{height:'.(LOGO_HEIGHT + MENU_HEIGHT - 50).'px;}
	#footer_container{display:none;}
	.main_page_1st_graph{height:400px !important;}
	#graph_container_wait{display:none; width:0px; height:0px; position:relative; left:50%; top:-200px;}
	#main_page_1st_graph_buttons_justified{display:;}
	#main_page_1st_graph_buttons_not_justified{display:none;}
	.currency_symbol_on_graph{font-size:32px;}
	.scroll_table > th,
	.scroll_table > td{min-width:40px;}
	.scroll_table4 > th,
	.scroll_table4 > td{min-width:40px;}
	.scroll_table5 > th,
	.scroll_table5 > td{min-width:120px;}
	.code_edit{font-size:'.round(TEXT_FONT_SIZE * 0.8).'px;}
	.description{font-size:'.round(TEXT_FONT_SIZE * 0.7).'px;}
	.mobile_balance{display:none;}
	.news_date{min-width:100px;}
	.col-height {min-height:250px;}
	.payment_option_table{width:200px;} 
	.limits_cell{height:100px !important; border-bottom:1px solid #ddd;}
	.deposit_cell{padding-right:60px;}
	.chat_td_text{font-size:11px;}
	.large_font{font-size:24px;}
	.extra_large_font{font-size:44px;  line-height:45px;}
	.box_type2{font-size:'.round(BOX_TYPE2_FONT_SIZE * 1).'px;}
	.box_type3{font-size:'.round(BOX_TYPE3_FONT_SIZE * 1).'px;}
	.footer_copyright,.footer a,.footer_text a,.footer_text{font-size:'.round(FOOTER_FONT_SIZE * 1.0).'px;}
	.btn-link{font-size:18px;}
	.collapsed_balance{display:none}
	.side_banner{display:none;}
	#share_price_left_part{text-align:right;}
	#share_price_right_part{text-align:left;}
	.timeline_column{min-width:300px;}
	.large_title{font-size:60px;}
}
@media (min-width: 1200px) {
	h1{font-size: '.H1_FONT_SIZE.'px;}
	h1:first-letter{font-size: '.H1_FONT_SIZE.'px;}
	h2{font-size: '.round(H2_FONT_SIZE * 1).'px;}
	h2:first-letter{font-size: '.round(H2_FONT_SIZE * 1).'px;}
	body{font-size:'.TEXT_FONT_SIZE.'px;}
	.edit_sorted_table_header_text{font-size:'.round(TEXT_FONT_SIZE * 0.8).'px;}
	.form-control{font-size:'.round(TEXT_FONT_SIZE * 1.2).'px; line-height:'.round(TEXT_FONT_SIZE * 1.5).'px;}
	label{font-size:'.TEXT_FONT_SIZE.'px; line-height:'.round(TEXT_FONT_SIZE * 1.4).'px;}
	.small_center_cantainer{width:70%;}
	.menu_button{'.(MENU_FLOAT == 'left'?'':'float:right;').' height:'.MENU_HEIGHT.'px;}
	.collapsed_logo{display:none;}
	.navbar-brand{width:'.LOGO_IMG_WIDTH.'px; height:'.LOGO_HEIGHT.'px;}
	#blank_div_for_fixed_menu{height:'.(LOGO_HEIGHT + MENU_HEIGHT - 50).'px;}
	#footer_container{display:none;}
	.main_page_1st_graph{height:400px !important;}
	#graph_container_wait{display:none; width:0px; height:0px; position:relative; left:50%; top:-200px;}
	#main_page_1st_graph_buttons_justified{display:;}
	#main_page_1st_graph_buttons_not_justified{display:none;}
	.currency_symbol_on_graph{font-size:36px;}
	.scroll_table > th,
	.scroll_table > td{min-width:80px;}
	.scroll_table4 > th,
	.scroll_table4 > td{min-width:60px;}
	.scroll_table5 > th,
	.scroll_table5 > td{min-width:150px;}
	.code_edit{font-size:'.round(TEXT_FONT_SIZE * 0.9).'px;}
	.description{font-size:'.round(TEXT_FONT_SIZE * 0.8).'px;}
	.mobile_balance{display:none;}
	.news_date{min-width:150px;}
	.col-height {min-height:250px;}
	.payment_option_table{width:200px;} 
	.limits_cell{height:100px !important; border-bottom:1px solid #ddd;}
	.deposit_cell{padding-right:100px;}
	.chat_td_text{font-size:12px;}
	.box_type2{font-size:'.round(BOX_TYPE2_FONT_SIZE * 1).'px;}
	.box_type3{font-size:'.round(BOX_TYPE3_FONT_SIZE * 1).'px;}
	.large_font{font-size:24px;}
	.extra_large_font{font-size:44px; line-height:45px;}
	.footer_copyright,.footer a,.footer_text a,.footer_text{font-size:'.round(FOOTER_FONT_SIZE * 1).'px;}
	.collapsed_balance{display:none}
	#share_price_left_part{text-align:right;}
	#share_price_right_part{text-align:left;}
	.timeline_column{min-width:300px;}
	.large_title{font-size:70px;}
	#google_translate_element{top:0px; bottom:unset; right:20px; max-height:30px !important;}
}

@media (min-width: 1200px) and (max-width: 1599px) {
	.side_banner{display:none;}
}

@media (min-width: 1600px) {
	
}

#submit_frm h1,
h1{color:#'.H1_COLOR.'; 
	font-family:"'.H1_FONT_FAMILY.'", verdana, arial;
	font-weight:'.(!defined('H1_FONT_WEIGHT_VALUE') ? H1_FONT_WEIGHT.';' : 'bold; font-variation-settings: "wght" '.H1_FONT_WEIGHT_VALUE.';').'
	
	font-style:'.H1_FONT_STYLE.'; 
	background-color:'.(H1_HAS_BACKGROUND_COLOR == '1'?'#'.H1_BACKGROUND_COLOR:'transparent').'; 
	background-image:'.check_image_path(H1_BACKGROUND_IMAGE).';
	
	padding-left:0px; padding-top:6px; 
	margin-left:2px; margin-right:2px; margin-top:'.round(H1_FONT_SIZE * 0.2).'px; margin-bottom:'.round(H1_FONT_SIZE * 0.4).'px; 
	text-shadow:'.((int)H1_HAS_SHADOW == 1?H1_SHADOW.'px '.H1_SHADOW.'px 1px #'.H1_SHADOW_COLOR:'none').';
	height:auto;
	/*line-height:'.round(H1_FONT_SIZE + 1).'px;*/
	line-height: 1.5em;
	text-transform:'.((int)H1_TEXT_TRANSFORM == 0?'none':((int)H1_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';

	border-bottom:'.((int)H1_BOTTOM_BORDER_STYLE == 0?'none':((int)H1_BOTTOM_BORDER_STYLE == 1?'1px solid #'.COLOR2DARK:((int)H1_BOTTOM_BORDER_STYLE == 2?'1px dashed #'.COLOR2DARK:'1px dotted #'.COLOR2DARK))).';
	-moz-border-radius:'.(H1_HAS_ROUNDED_EDGES=='1'?'5px':'none').'; border-radius:'.(H1_HAS_ROUNDED_EDGES=='1'?'5px':'none').';
}
h1:first-letter{color:#'.H1_FONT_FIRST_LETTER_COLOR.'; }
h1 > b {font-weight:'.H1_FONT_WEIGHT.'; color:#'.(is_color_light(H1_COLOR)?COLOR1LIGHT:COLOR1DARK).'; }

#submit_frm h1{margin-left:2px; margin-right:0px;}

h2{color:#'.H2_COLOR.'; 
	font-family:"'.H2_FONT_FAMILY.'", verdana, arial; 
	font-weight:'.(!defined('H2_FONT_WEIGHT_VALUE') ? H2_FONT_WEIGHT.';' : 'bold; font-variation-settings: "wght" '.H2_FONT_WEIGHT_VALUE.';').'
	font-style:'.H2_FONT_STYLE.'; 
	background-color:'.(H2_HAS_BACKGROUND_COLOR == '1'?'#'.H2_BACKGROUND_COLOR:'transparent').'; 
	background-image:'.check_image_path(H2_BACKGROUND_IMAGE).';
	padding-left:0px; padding-top:6px; 
	margin-left:2px; margin-right:2px; margin-top:'.round(H2_FONT_SIZE * 1).'px; margin-bottom:'.round(H2_FONT_SIZE * 0.8).'px; 
	text-shadow:'.((int)H2_HAS_SHADOW == 1?H2_SHADOW.'px '.H2_SHADOW.'px 1px #'.H2_SHADOW_COLOR:'none').';
	height:auto; 
	/*line-height:'.round(H2_FONT_SIZE * 1.5).'px;*/
	line-height: 1.5em;
	text-transform:'.((int)H2_TEXT_TRANSFORM == 0?'none':((int)H2_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	border-bottom:'.((int)H2_BOTTOM_BORDER_STYLE == 0?'none':((int)H2_BOTTOM_BORDER_STYLE == 1?'1px solid #'.COLOR2DARK:((int)H2_BOTTOM_BORDER_STYLE == 2?'1px dashed #'.COLOR2DARK:'1px dotted #'.COLOR2DARK))).';
}
h2:first-letter{color:#'.H2_FONT_FIRST_LETTER_COLOR.'; }
h2 > b {font-weight:'.H2_FONT_WEIGHT.'; color:#'.(is_color_light(H2_COLOR)?COLOR3LIGHT:COLOR3DARK).'; }

#question_and_answer h2{margin-top:20px; color:#'.H2_COLOR.';}
#question_and_answer h2:first-letter{color:#'.H2_FONT_FIRST_LETTER_COLOR.'; font-size:'.H2_FONT_SIZE.'px; }

h3{font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	font-size:'.round(H2_FONT_SIZE * 0.8).'px; 
	color:#'.H2_COLOR.'; 
	font-weight:normal;
	padding-top:10px; padding-bottom:10px; 
	margin-top:0px; margin-bottom:0px;
	text-align:left;
	text-shadow:'.((int)H2_HAS_SHADOW == 1?H2_SHADOW.'px '.H2_SHADOW.'px 1px #'.H2_SHADOW_COLOR:'none').';
}

h4{color:#'.H2_COLOR.'; 
	font-family:"'.H2_FONT_FAMILY.'", verdana, arial; 
	font-weight:'.H2_FONT_WEIGHT.'; 
	text-transform:none; 
	font-size:'.round(H2_FONT_SIZE * 0.8).'px; 
	font-style: italic;
}
h5{color:#'.COLOR3DARK.'; text-transform:none; 
	font-weight:normal;
	font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	font-size:'.(round(H2_FONT_SIZE * 0.5) >= TEXT_FONT_SIZE?round(H2_FONT_SIZE * 0.5):TEXT_FONT_SIZE).'px;
	background-image:none;
	padding-left:30px; padding-top:5px; margin-top:10px; 
	height:auto; 
}
h6{color:#'.COLOR2DARK.'; text-transform:none; 
	font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	font-weight:normal; 
	font-size:'.(round(H2_FONT_SIZE * 0.5) >= TEXT_FONT_SIZE?round(H2_FONT_SIZE * 0.5):TEXT_FONT_SIZE).'px;
	background-image:none;
	padding-left:30px; padding-top:5px; margin-top:10px; 
	height:auto; 
}

.box_type1{
	background-color:'.(BOX_TYPE1_HAS_BACKGROUND_COLOR == '1'?'#'.BOX_TYPE1_BG_COLOR:'transparent').'; 
	background-image:'.check_image_path(BOX_TYPE1_BACKGROUND_IMAGE).';
	padding:4px 14px 10px 14px;
	margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:4px; 
	height:auto;
	border:'.((int)BOX_TYPE1_HAVE_OUTLINE == 0?'none':BOX_TYPE1_HAVE_OUTLINE.'px solid #'.BOX_TYPE1_OUTLINE).';
	-moz-border-radius:'.BOX_TYPE1_RADIUS.'px; border-radius:'.BOX_TYPE1_RADIUS.'px;
	-moz-box-shadow:'.((int)BOX_TYPE1_HAVE_SHADOW == 1?BOX_TYPE1_SHADOW.'px '.BOX_TYPE1_SHADOW.'px '.(defined('BOX_TYPE1_SHADOW_BLUR') && strlen(BOX_TYPE1_SHADOW_BLUR) > 0?BOX_TYPE1_SHADOW_BLUR:'1').'px #'.BOX_TYPE1_SHADOW_COLOR:'none').';
	-webkit-box-shadow:'.((int)BOX_TYPE1_HAVE_SHADOW == 1?BOX_TYPE1_SHADOW.'px '.BOX_TYPE1_SHADOW.'px '.(defined('BOX_TYPE1_SHADOW_BLUR') && strlen(BOX_TYPE1_SHADOW_BLUR) > 0?BOX_TYPE1_SHADOW_BLUR:'1').'px #'.BOX_TYPE1_SHADOW_COLOR:'none').';
}

.box_type2{
	
	'.(BOX_TYPE2_BACKGROUND_IMAGE == '' || BOX_TYPE2_BACKGROUND_IMAGE == 'none' && defined('BOX_TYPE2_BG_COLOR2') ? (BOX_TYPE2_HAS_BACKGROUND_COLOR == '1'?'
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #'.BOX_TYPE2_BG_COLOR.'), color-stop(1, #'.BOX_TYPE2_BG_COLOR2.') );
	background:-moz-linear-gradient( center top, #'.BOX_TYPE2_BG_COLOR.' 5%, #'.BOX_TYPE2_BG_COLOR2.' 100% );
	background:-webkit-linear-gradient( center top, #'.BOX_TYPE2_BG_COLOR.' 5%, #'.BOX_TYPE2_BG_COLOR2.' 100% );
    background:-o-linear-gradient( center top, #'.BOX_TYPE2_BG_COLOR.' 5%, #'.BOX_TYPE2_BG_COLOR2.' 100% );
    background:-ms-linear-gradient( center top, #'.BOX_TYPE2_BG_COLOR.' 5%, #'.BOX_TYPE2_BG_COLOR2.' 100% );
    background:linear-gradient( center top, #'.BOX_TYPE2_BG_COLOR.' 5%, #'.BOX_TYPE2_BG_COLOR2.' 100% );

	':'background:transparent;'):'background-image:'.check_image_path(BOX_TYPE2_BACKGROUND_IMAGE).';').'
	
	background-color:'.(BOX_TYPE2_HAS_BACKGROUND_COLOR == '1'?'#'.BOX_TYPE2_BG_COLOR:'transparent').'; 
	padding:4px 14px 10px 14px;
	margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:4px; 
	height:auto;
	border:'.((int)BOX_TYPE2_HAVE_OUTLINE == 0?'none':BOX_TYPE2_HAVE_OUTLINE.'px solid #'.BOX_TYPE2_OUTLINE).';
	-moz-border-radius:'.BOX_TYPE2_RADIUS.'px; border-radius:'.BOX_TYPE2_RADIUS.'px;
	-moz-box-shadow:'.((int)BOX_TYPE2_HAVE_SHADOW == 1?BOX_TYPE2_SHADOW.'px '.BOX_TYPE2_SHADOW.'px '.(defined('BOX_TYPE2_SHADOW_BLUR') && strlen(BOX_TYPE2_SHADOW_BLUR) > 0?BOX_TYPE2_SHADOW_BLUR:'1').'px #'.BOX_TYPE2_SHADOW_COLOR:'none').';
	-webkit-box-shadow:'.((int)BOX_TYPE2_HAVE_SHADOW == 1?BOX_TYPE2_SHADOW.'px '.BOX_TYPE2_SHADOW.'px '.(defined('BOX_TYPE2_SHADOW_BLUR') && strlen(BOX_TYPE2_SHADOW_BLUR) > 0?BOX_TYPE2_SHADOW_BLUR:'1').'px #'.BOX_TYPE2_SHADOW_COLOR:'none').';
	
	color:#'.BOX_TYPE2_COLOR.'; 
	
	font-family:"'.BOX_TYPE2_FONT_FAMILY.'", verdana, arial; 
	font-weight:'.BOX_TYPE2_FONT_WEIGHT.'; 
	font-style:'.BOX_TYPE2_FONT_STYLE.'; 
	text-shadow:'.((int)BOX_TYPE2_TEXT_HAS_SHADOW == 1?BOX_TYPE2_TEXT_SHADOW.'px '.BOX_TYPE2_TEXT_SHADOW.'px 1px #'.BOX_TYPE2_TEXT_SHADOW_COLOR:'none').';
	text-transform:'.((int)BOX_TYPE2_TEXT_TRANSFORM == 0?'none':((int)BOX_TYPE2_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
}
.box_type2 h1,
.box_type2 h2,
.box_type2 h3,
.box_type2 h4,
.box_type2 h5,
.box_type2 h6
{color:#'.BOX_TYPE2_COLOR.';  
text-shadow:'.((int)BOX_TYPE2_TEXT_HAS_SHADOW == 1?BOX_TYPE2_TEXT_SHADOW.'px '.BOX_TYPE2_TEXT_SHADOW.'px 1px #'.BOX_TYPE2_TEXT_SHADOW_COLOR:'none').';
}
.box_type2 h1:first-letter,
.box_type2 h2:first-letter
{color:#'.BOX_TYPE2_COLOR.';}

.box_type3{
	'.(BOX_TYPE3_BACKGROUND_IMAGE == '' || BOX_TYPE3_BACKGROUND_IMAGE == 'none' && defined('BOX_TYPE2_BG_COLOR2') ? (BOX_TYPE3_HAS_BACKGROUND_COLOR == '1'?'background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #'.BOX_TYPE3_BG_COLOR.'), color-stop(1, #'.BOX_TYPE3_BG_COLOR2.') );
	background:-moz-linear-gradient( center top, #'.BOX_TYPE3_BG_COLOR.' 5%, #'.BOX_TYPE3_BG_COLOR2.' 100% );
	background:-webkit-linear-gradient( center top, #'.BOX_TYPE3_BG_COLOR.' 5%, #'.BOX_TYPE3_BG_COLOR2.' 100% );
    background:-o-linear-gradient( center top, #'.BOX_TYPE3_BG_COLOR.' 5%, #'.BOX_TYPE3_BG_COLOR2.' 100% );
    background:-ms-linear-gradient( center top, #'.BOX_TYPE3_BG_COLOR.' 5%, #'.BOX_TYPE3_BG_COLOR2.' 100% );
    background:linear-gradient( center top, #'.BOX_TYPE3_BG_COLOR.' 5%, #'.BOX_TYPE3_BG_COLOR2.' 100% );

	':'background:transparent;'):'background-image:'.check_image_path(BOX_TYPE3_BACKGROUND_IMAGE).';').'
	background-color:'.(BOX_TYPE3_HAS_BACKGROUND_COLOR == '1'?'#'.BOX_TYPE3_BG_COLOR:'transparent').'; 
	background-clip:border-box; background-origin:border-box;
	padding:4px 14px 10px 14px;
	margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:4px; 
	height:auto;
	border:'.((int)BOX_TYPE3_HAVE_OUTLINE == 0?'none':BOX_TYPE3_HAVE_OUTLINE.'px solid #'.BOX_TYPE3_OUTLINE).';
	-moz-border-radius:'.BOX_TYPE3_RADIUS.'px; border-radius:'.BOX_TYPE3_RADIUS.'px;
	-moz-box-shadow:'.((int)BOX_TYPE3_HAVE_SHADOW == 1?BOX_TYPE3_SHADOW.'px '.BOX_TYPE3_SHADOW.'px '.(defined('BOX_TYPE3_SHADOW_BLUR') && strlen(BOX_TYPE3_SHADOW_BLUR) > 0?BOX_TYPE3_SHADOW_BLUR:'1').'px #'.BOX_TYPE3_SHADOW_COLOR:'none').';
	-webkit-box-shadow:'.((int)BOX_TYPE3_HAVE_SHADOW == 1?BOX_TYPE3_SHADOW.'px '.BOX_TYPE3_SHADOW.'px '.(defined('BOX_TYPE3_SHADOW_BLUR') && strlen(BOX_TYPE3_SHADOW_BLUR) > 0?BOX_TYPE3_SHADOW_BLUR:'1').'px #'.BOX_TYPE3_SHADOW_COLOR:'none').';
	
	color:#'.BOX_TYPE3_COLOR.'; 
	font-family:"'.BOX_TYPE3_FONT_FAMILY.'", verdana, arial; 
	font-weight:'.BOX_TYPE3_FONT_WEIGHT.'; 
	font-style:'.BOX_TYPE3_FONT_STYLE.'; 
	text-shadow:'.((int)BOX_TYPE3_TEXT_HAS_SHADOW == 1?BOX_TYPE3_TEXT_SHADOW.'px '.BOX_TYPE3_TEXT_SHADOW.'px 1px #'.BOX_TYPE3_TEXT_SHADOW_COLOR:'none').';
	text-transform:'.((int)BOX_TYPE3_TEXT_TRANSFORM == 0?'none':((int)BOX_TYPE3_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
}

.box_type3 h1,
.box_type3 h2,
.box_type3 h3,
.box_type3 h4,
.box_type3 h5,
.box_type3 h6,
.box_type3 p
{
	color:#'.BOX_TYPE3_COLOR.';  
	text-shadow:'.((int)BOX_TYPE3_TEXT_HAS_SHADOW == 1?BOX_TYPE3_TEXT_SHADOW.'px '.BOX_TYPE3_TEXT_SHADOW.'px 1px #'.BOX_TYPE3_TEXT_SHADOW_COLOR:'none').';
}
.box_type3 h1:first-letter,
.box_type3 h2:first-letter
{color:#'.BOX_TYPE3_COLOR.';}

.footer_copyright,
.footer a,
.footer_text a,
.footer_text{
	font-family:"'.FOOTER_FONT_FAMILY.'", verdana, arial;
	font-weight:'.FOOTER_FONT_WEIGHT.'; font-style:'.FOOTER_FONT_STYLE.'; 
	text-align:left; 
	text-shadow:'.(FOOTER_HAS_SHADOW == '1'?FOOTER_SHADOW.'px '.FOOTER_SHADOW.'px 1px #'.FOOTER_SHADOW_COLOR:'none').';
	text-transform:'.(FOOTER_TEXT_TRANSFORM == '0' ? 'none' : (FOOTER_TEXT_TRANSFORM == '1' ? 'uppercase' : 'capitalize')).';
	padding-right:0px; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px; 
}

.footer a,
.footer_copyright,
.footer_text a,
.footer_text a:link,
.footer_text a:visited,
.footer_text a:active,
.footer_text{color:#'.FOOTER_COLOR.'; line-height:normal; padding-top:5px; padding-bottom:5px; }

.footer_text a:hover{color:#'.FOOTER_COLOR.'; }

.footer_text b{font-size:'.(defined('FOTR_SECTION_FONT_SIZE')?FOTR_SECTION_FONT_SIZE:round(FOOTER_FONT_SIZE * 1.4)).'px; line-height:'.(defined('FOTR_SECTION_FONT_SIZE')?round(FOTR_SECTION_FONT_SIZE * 2.5):round(FOOTER_FONT_SIZE * 2.5)).'px; color:'.(defined('FOTR_SECTION_COLOR')?'#'.FOTR_SECTION_COLOR:(is_color_light(FOOTER_COLOR)?adjustBrightness(FOOTER_COLOR, 20):adjustBrightness(FOOTER_COLOR, -20))).'; '.(defined('FOTR_SECTION_FONT_STYLE')?' font-style:'.FOTR_SECTION_FONT_STYLE.'; ':'').(defined('FOTR_SECTION_FONT_WEIGHT')?' font-weight:'.FOTR_SECTION_FONT_WEIGHT.'; ':'').' }

#footer_container,
.footer,
.footer_center{
	background-color:'.((int)FOOTER_HAS_BACKGROUND_COLOR == 1?'#'.FOOTER_BACKGROUND_COLOR:'transparent').'; 
	background-image:'.check_image_path(FOOTER_BACKGROUND_IMAGE).';
	height:auto;
}
.footer{
	border-top:'.((int)FOOTER_BORDER_STYLE == 0?'none':((int)FOOTER_BORDER_STYLE == 1?'1px solid ':((int)FOOTER_BORDER_STYLE == 2?'1px dashed ':'1px dotted '))).(is_color_light(FOOTER_BACKGROUND_COLOR)?adjustBrightness(FOOTER_BACKGROUND_COLOR, 20):adjustBrightness(FOOTER_BACKGROUND_COLOR, -20)).';
}

.footer_copyright{
	border-top:'.((int)FOOTER_BORDER_STYLE == 0?'none':((int)FOOTER_BORDER_STYLE == 1?'1px solid ':((int)FOOTER_BORDER_STYLE == 2?'1px dashed ':'1px dotted '))).(is_color_light(FOOTER_BACKGROUND_COLOR)?adjustBrightness(FOOTER_BACKGROUND_COLOR, -20):adjustBrightness(FOOTER_BACKGROUND_COLOR, 20)).';
	vertical-align:top;
}

.container > .table{
	border-bottom:'.((int)FOOTER_BORDER_STYLE == 0?'none':((int)FOOTER_BORDER_STYLE == 1?'1px solid ':((int)FOOTER_BORDER_STYLE == 2?'1px dashed ':'1px dotted '))).(is_color_light(FOOTER_BACKGROUND_COLOR)?adjustBrightness(FOOTER_BACKGROUND_COLOR, 20):adjustBrightness(FOOTER_BACKGROUND_COLOR, -20)).';
	margin:0px; 
	}

#footer_container{padding:20px; padding-bottom:30px;}

.footer {
  position: absolute;
  bottom: -'.$footer_height.'px;
  width: 100%;
}

.footer > .container {
  padding-right: 15px;
  padding-left: 15px;
  background-image:'.(check_image_path(LOGO_BACKGROUND_IMAGE)).'; background-repeat:no-repeat; background-position: '.(FOOTER_TEXT_ALIGN == 'right' ? 'right' : 'left').' bottom; background-size:auto '.round(FOOTER_FONT_SIZE * 1.7).'px;
}

.footer_copyright{text-align:'.FOOTER_TEXT_ALIGN.'; color:'.(is_color_light(FOOTER_COLOR)?adjustBrightness(FOOTER_COLOR, -30):adjustBrightness(FOOTER_COLOR, 30)).';}
.footer_text,
.footer_table{text-align:'.FOOTER_TEXT_ALIGN.';}

.carousel-inner > .item > img,
.carousel-inner > .item > a > img { width: 70%; margin: auto;}

.col-md-3,
.col-md-4,
.col-md-6,
.col-md-12
{padding-left:30px; padding-right:20px; vertical-align:top;}

.form-control{
	background-color:#'.(defined('INPUT_BOXES_BACKGROUND_COLOR')?INPUT_BOXES_BACKGROUND_COLOR:COLOR_TEXT_BKG).';
	border-color:#'.(defined('INPUT_BOXES_OUTLINE')?INPUT_BOXES_OUTLINE:COLOR3BASE).';
	'.(defined('INPUT_BOXES_HAVE_OUTLINE')?'border-width:'.INPUT_BOXES_HAVE_OUTLINE.'px;':'').'
	moz-border-radius:'.INPUT_BOXES_RADIUS.'px'.'; border-radius:'.INPUT_BOXES_RADIUS.'px'.'; 
	font-family:"'.TEXT_FONT_FAMILY.'", verdana, arial; 
	'.(defined('INPUT_BOXES_HAVE_SHADOW')?
		(
		'box-shadow:'.((int)INPUT_BOXES_HAVE_SHADOW?INPUT_BOXES_SHADOW.'px '.INPUT_BOXES_SHADOW.'px 6px #'.INPUT_BOXES_SHADOW_COLOR:'none').';
		-moz-box-shadow:'.((int)INPUT_BOXES_HAVE_SHADOW?INPUT_BOXES_SHADOW.'px '.INPUT_BOXES_SHADOW.'px 6px #'.INPUT_BOXES_SHADOW_COLOR:'none').';
		-webkit-box-shadow:'.((int)INPUT_BOXES_HAVE_SHADOW?INPUT_BOXES_SHADOW.'px '.INPUT_BOXES_SHADOW.'px 6px #'.INPUT_BOXES_SHADOW_COLOR:'none').';'
		)
		:'').'
}
.form-control:focus,
.form-control:active{border-color:#'.COLOR3BASE.';}
.form-control:hover{border-color:#'.COLOR1BASE.';}

.transparent_table > tr > td,
.transparent_table > thead > tr > td,
.transparent_table > tbody > tr > td
{border:none; padding:2px;}

.top_form{'.((int)LOGO_ON_LEFT == 1?'float:right;':'float:left;').'}

.top_texts a,
.top_texts{
	color:'.(LOGO_COLOR).'; 
	text-shadow:'.((int)LOGO_HAS_SHADOW == 1 ? LOGO_SHADOW_WIDTH.'px '.LOGO_SHADOW_WIDTH.'px '.(LOGO_SHADOW_WIDTH == 0?1:abs(LOGO_SHADOW_WIDTH) - 1).'px '.LOGO_SHADOW_COLOR:'none').'; 
	line-height:'.round((LOGO_FONT_SIZE) *1.2).'px; 
	padding:20px; padding-top:'.round((LOGO_HEIGHT - LOGO_FONT_SIZE) / 2).'px; 
	vertical-align:top;
	font-weight:'.LOGO_FONT_WEIGHT.'; font-style:'.LOGO_FONT_STYLE.'; 
}
.top_texts_username{font-size:14px; }

.top_texts a:hover{color: '.(is_color_light(LOGO_COLOR)?adjustBrightness(LOGO_COLOR, 30):adjustBrightness(LOGO_COLOR, -30)).'; text-decoration:none;}

.top_texts a strong,
.top_texts a b,
.top_texts strong,
.top_texts b{color:'.(LOGO_FONT_FIRST_LETTER_COLOR).'; }

.top_balance_line{}
.top_balance{padding-left:4px; padding-right:4px; background-color:'.((int)LOGO_HAS_BACKGROUND_COLOR == 1?'transparent':'#'.COLOR1DARK).';}

.main_box_inside_desc,
.description{line-height:normal; font-weight:normal; padding-top:0px; /*padding-right:10px;*/ margin-top:0px; text-align:left; text-transform:none; margin-bottom:0px;}
.description a{color:inherit;}
.payment_option_table_selected .description{font-weight:bold; color:#'.PAGE_TEXT_COLOR.'; }

.more_info_div_small,
.more_info_div{position:relative; top:-24px; width:0px; height:0px; display:inline-block; }
.more_info_div_small{top:0px;}
.more_info_hint{z-index:300; text-align:left;}
.more_info_btn{z-index:200;}

.more_info_btn{
background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_question25x25.png); background-repeat: no-repeat; background-position: 0px 0px;
width:25px; height:25px;
display:inline-block; 
position:relative; top:9px;
}
.more_info_btn:hover{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_question25x25_h.png);}

.more_info_hint_small,
.more_info_hint{
color:#'.(PAGE_TEXT_COLOR).';
display:none; position:absolute; top:30px; right:-230px; width:200px; z-index:10000;
border: 1px solid #'.(COLOR1DARK).';
-moz-border-radius: 5px; border-radius: 5px;
padding: 10px 12px;
background: '.((COLOR_TEXT_BKG != 'none'?COLOR_TEXT_BKG:'#ffffff')).';
-moz-box-shadow: 3px 3px 3px #ccc;
-webkit-box-shadow: 3px 3px 3px #ccc;
box-shadow: 3px 3px 3px #ccc;
}

.more_info_hint_small{top:20px; color:#000000; text-align:left; font-size:11px; line-height:14px; width:150px; right:-180px; text-shadow:none; }

.more_info_btn_small{
background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_question16x16.png); background-repeat: no-repeat; background-position: 0px 0px;
width:16px; height:16px;
display:inline-block; 
position:relative; top:0px;
}
.more_info_btn_small:hover{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_question16x16_h.png);}

.close_button{width:12px; height:12px; display:inline-block; background-repeat: no-repeat; background-position: 0px 0px;}
.close_button:link,
.close_button:visited,
.close_button:active{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_close_small.png);}
.close_button:hover{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'button_close_small_h.png);}

.modify_button{width:16px; height:16px; display:inline-block; background-repeat: no-repeat; background-position: 0px 0px;}
.modify_button:link,
.modify_button:visited,
.modify_button:active{background-image:url(/'.(DIR_WS_IMAGES_DIR).'Modify16x16.png);}
.modify_button:hover{background-image:url(/'.(DIR_WS_IMAGES_DIR).'Modify16x16.png);}

.reply_button{width:27px; height:27px; display:inline-block; background-repeat: no-repeat; background-position: 0px 0px;}
.reply_button:link,
.reply_button:visited,
.reply_button:active{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'topic_comment_reply.png);}
.reply_button:hover{background-image:url(/'.(DIR_WS_WEBSITE_IMAGES_DIR).'topic_comment_reply.png);}

.write_new_topic{color:#'.(COLOR3BASE).';}
.write_new_topic:link,
.write_new_topic:visited,
.write_new_topic:active{text-decoration:none;}
.write_new_topic:hover{text-decoration:none; color:#'.(COLOR3DARK).';}

.topick_text_popover .popover {position: relative; display: block; margin: 0px; width:100%; max-width: 1024px; z-index: 1;}

.top_photo{position:absolute; width:60px; height:60px; left:323px; top:4px; }
.top_photo_frame{position:absolute; width:110px; height:110px; left:320px; top:2px; }
.top_photo_frame{background: url(/'.DIR_WS_IMAGES_DIR.'photo_frame.png) no-repeat; background-position: 0px 0px;}

.wall_frame{background: url(/'.DIR_WS_IMAGES_DIR.'wall_frame60x60.png) no-repeat; background-position: 0px 0px;}
.wall_frame{position:absolute; width:74px; height:74px; left:0px; top:0px; }
.wall_photo{position:absolute; width:60px; height:60px; left:4px; top:4px; }

.wall_poster_thumb_photo{position:absolute; width:50px; height:50px; left:3px; top:3px; }

.wall_poster_thumb_frame{position:absolute; width:62px; height:62px; left:0px; top:0px; }
.wall_poster_thumb_frame{background: url(/'.DIR_WS_IMAGES_DIR.'wall_frame50x50.png) no-repeat; background-position: 0px 0px;}

.wall_thumb_frame{position:absolute; width:94px; height:74px; left:0px; top:0px; }
.wall_thumb_frame{background: url(/'.DIR_WS_IMAGES_DIR.'wall_frame80x60.png) no-repeat; background-position: 0px 0px;}

.wall_thumb_photo{position:absolute; width:80px; height:60px; left:4px; top:4px; }

.side_banner{position:fixed; top:'.(LOGO_IMG_HEIGHT + 20).'px; right:40px; z-index:10000; width:auto; overflow:hidden;}

.bottom_banner{margin-left:auto; margin-right:auto; margin-top:80px; margin-bottom:5px; width:728px;}

.error_message{font-family:arial; color:#ff0000; font-size:11px; font-weight:none; text-align:left; padding-top:0px; padding-bottom:0px; padding-left:4px; margin-top:0px; margin-bottom:0px; }

.right_ilustration{border:none; float:right; padding-left:20px; padding-right:20px; padding-bottom:20px; -webkit-box-sizing:content-box; -moz-box-sizing:content-box; box-sizing:content-box;}
.left_ilustration{border:none; float:left; padding-left:20px; padding-right:20px; padding-bottom:20px; -webkit-box-sizing:content-box; -moz-box-sizing:content-box; box-sizing:content-box;}

.payment_method_table{width:100%; margin:0px; min-height:120px;}
.payment_option_table{display:inline; float:left; margin:0px; padding:6px; height:auto; border:0px; border-spacing:0px; 
moz-border-radius:6px; border-radius:6px; background-color:transparent;}
.payment_option_table_selected{background-color:#'.COLOR1LIGHT.';}


.bordered_edit{max-width:300px;}

@font-face {
	font-family: "whitrabt";
	src: url("/images/whitrabt.woff");
}

.tick_strap{padding-top:6px; padding-bottom:0px; margin-bottom:0px;}
.tick_strap:first-letter{font-family:"Arial"; color:#ffffff; font-style:normal;}

.box_buy_row > td{color:#006600;}
.box_sell_row > td{color:#C06000;}

.currency_symbol_on_graph{width:auto; height:0px; position:relative; top:25px; left:55px; z-index:3; color:#'.COLOR3BASE.'; font-style:bold; text-shadow: 1px 1px 1px #444444;}

.news_date{border-right:1px solid #'.COLOR1BASE.'; padding-right:6px; vertical-align:top; text-align:right;}

.editable_item{padding:2px; border:1px dashed #80ff80; -moz-border-radius:4px; border-radius:4px; box-shadow:0 0 6px #b0b0b0;}
.editable_item:hover{border:1px solid #'.COLOR1BASE.'; cursor:pointer; box-shadow:0 0 6px #808080; -moz-box-shadow:0 0 6px #808080; -webkit-box-shadow:0 0 6px #808080;}
.editable_item_empty{color:#808080; border:1px dotted #FF8080;}
.editable_item_empty,
.editable_item_empty:first-letter{color:#808080;}
.editable_has_error{border:1px solid #FF0000;}
		
.editable_item ul,
.ul,
.ol{padding-left:10px; padding-right:10px; padding-top:0px; padding-bottom:5px; list-style-type: none;}

.editable_item ul li,
.ul li{text-indent:-'.(BULLETS_FONT_SIZE + 6).'px; margin-left:'.round(BULLETS_FONT_SIZE * 0.5).'px; 
padding-left:4px; 
padding-top:'.round(BULLETS_FONT_SIZE * 0.5).'px; 
padding-bottom:'.round(BULLETS_FONT_SIZE * 0.8).'px; 
text-align:justify;}

.editable_item ul li:before,
.ul li:before{color:#'.BULLETS_COLOR.'; font-size:'.BULLETS_FONT_SIZE.'px; 
	text-shadow:'.((int)BULLETS_HAS_SHADOW == 1?BULLETS_SHADOW.'px '.BULLETS_SHADOW.'px 0px #'.BULLETS_SHADOW_COLOR:'none').';
	font-weight:'.BULLETS_FONT_WEIGHT.'; font-style:'.BULLETS_FONT_STYLE.'; 
	'.(BULLETS_BACKGROUND_IMAGE == 'none'?'content:"\\'.(BULLETS_STYLE).'"; 
	background-image:none; padding-right:8px;':'background-image:'.check_image_path(BULLETS_BACKGROUND_IMAGE).'; background-repeat:no-repeat; background-position: top left; content:""; padding-right:'.(BULLETS_FONT_SIZE + 6).'px; ').'
}

.ol li{text-indent:-'.(BULLETS_FONT_SIZE + 6).'px; margin-left:'.round(BULLETS_FONT_SIZE * 0.5).'px; padding-left:4px; padding-bottom:'.round(BULLETS_FONT_SIZE * 0.4).'px; text-align:justify;}
.ol li:before{color:#'.CHECKBOXES_COLOR.'; font-size:'.CHECKBOXES_FONT_SIZE.'px; 
	text-shadow:'.((int)CHECKBOXES_HAS_SHADOW == 1?CHECKBOXES_SHADOW.'px '.CHECKBOXES_SHADOW.'px 0px #'.CHECKBOXES_SHADOW_COLOR:'none').';
	font-weight:'.CHECKBOXES_FONT_WEIGHT.'; font-style:'.CHECKBOXES_FONT_STYLE.'; 
	'.(CHECKBOXES_BACKGROUND_IMAGE == 'none'?'content:"\\'.(CHECKBOXES_STYLE).'"; 
	background-image:none; padding-right:8px;':'background-image:'.check_image_path(CHECKBOXES_BACKGROUND_IMAGE).'; background-repeat:no-repeat; background-position: top left; content:""; padding-right:'.(CHECKBOXES_FONT_SIZE + 6).'px; ').'
}

.banner_textarea{width:100%; border: 1px solid #000000; border-color:#9FB8D4;} 
.banner_code_text{font-weight:normal; background-color:#ffffff; text-align:left; font-family:monospace;}

.account_textarea,
.submit_frm_table_3d,
.banner_table,
.quick_stats_table,
.iframe_page,
.submit_frm_table{width:100%; margin-left:30px;}
.limits_cell{margin:0px; padding:0px; }
.deposit_cell{border:none; text-align:right; padding-top:0px;}

.chat_td_text{vertical-align:top;}
.chat_image{
	margin:4px;
	'.((int)IMAGES_HAVE_BORDER > 0?'border: '.IMAGES_HAVE_BORDER.'px solid #'.IMAGES_COLOR.'; padding:1px;':'').'
	-moz-box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1 ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR : 'none').';
	-webkit-box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1?IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
	box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1?IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
	-moz-border-radius:'.( (int)IMAGES_HAVE_ROUNDED_EDGES >0?IMAGES_HAVE_ROUNDED_EDGES.'px':'none').'; 
	border-radius:'.((int)IMAGES_HAVE_ROUNDED_EDGES >0?IMAGES_HAVE_ROUNDED_EDGES.'px':'none').';
	background-color:'.((int)IMAGES_HAVE_BKG_COLOR == 1?'#'.IMAGES_BKG_COLOR:'transparent').'; 
}
.small_scroll_logo{background-image:'.check_image_path(LOGO_BACKGROUND_IMAGE).'; width:'.round(LOGO_IMG_WIDTH / LOGO_IMG_HEIGHT * MENU_HEIGHT).'px; height:'.MENU_HEIGHT.'px; display:none;}
hr{border-color:#'.HR_COLOR.';
'.(!defined('HR_BACKGROUND_IMAGE') || HR_BACKGROUND_IMAGE == '' || HR_BACKGROUND_IMAGE == 'none'?'background-image:none; border-style:'.HR_STYLE.'; ':'background-image:'.check_image_path(HR_BACKGROUND_IMAGE).'; height:10px; border:none;').'
margin-left:2px; margin-right:2px; }
.exchange_big{text-transform:none; text-align:left; color:#000000; font-family:arial; font-size:20px; line-height:26px; font-weight:bold;}
.exchange_green{color:#008000}
.place_text_1,
.place_text_2,
.place_text_3
{
	font-family:"'.LOGO_FONT_FAMILY.'", verdana, arial; 
	font-weight:bold; font-style:normal; 
	text-shadow:none;
	background-image:none;
	text-transform:'.((int)LOGO_TEXT_TRANSFORM == 0?'none':((int)LOGO_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	background-color:transparent;
}
.place_1,
.place_2,
.place_3
{
	font-family:"'.LOGO_FONT_FAMILY.'", verdana, arial; 
	font-weight:bold; font-style:normal; 
	text-shadow:'.(LOGO_HAS_SHADOW == '1'?LOGO_SHADOW_WIDTH.'px '.LOGO_SHADOW_WIDTH.'px 1px #'.COLOR3DARK:'none').';
	background-image:none;
	text-transform:'.((int)LOGO_TEXT_TRANSFORM == 0?'none':((int)LOGO_TEXT_TRANSFORM == 1?'uppercase':'capitalize')).';
	background-color:transparent;
	width:50px;
}
.place_text_1{font-size:16px; line-height:20px; color:#'.COLOR1BASE.';}
.place_text_2{font-size:14px; line-height:18px; color:#'.COLOR1DARK.';}
.place_text_3{font-size:12px; line-height:16px; color:#'.COLOR3BASE.';}

.place_1{font-size:70px; line-height:80px; color:#'.COLOR1BASE.';}
.place_2{font-size:60px; line-height:70px; color:#'.COLOR1DARK.';}
.place_3{font-size:50px; line-height:60px; color:#'.COLOR3BASE.';}

.div_place_1,
.div_place_2,
.div_place_3
{width:100px; height:100px; background-color:transparent; text-align:center; padding-top:0px; -moz-border-radius:20px; border-radius:20px;}
.question_mark_in_info{font-size:30px; font-weight:bold; color:#'.COLOR3BASE.'; text-shadow:1px 1px 1px #'.COLOR1DARK.';}

.goog-te-banner-frame.skiptranslate {display: none !important;} 

.goog-te-menu-value > span { 
	text-shadow:'.((int)LOGO_HAS_SHADOW == 1 && (int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT != 1 && (int)LOGO_COLOR_TRANSPARENT != 1?LOGO_SHADOW_WIDTH.'px '.LOGO_SHADOW_WIDTH.'px '.(LOGO_SHADOW_WIDTH == 0?1:abs(LOGO_SHADOW_WIDTH) - 1).'px '.LOGO_SHADOW_COLOR:'none').'; color:'.((int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'transparent':LOGO_FONT_FIRST_LETTER_COLOR).';
	color:'.((int)LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT == 1?'transparent':LOGO_FONT_FIRST_LETTER_COLOR).'; 
	text-align:center;
	
 }
.goog-te-menu-value:hover{text-decoration:none;}
.goog-te-gadget > .goog-te-gadget-simple{background-color:transparent; border:none;}
/*.google_translate_top_panel{position:absolute; background-color:transparent; top:0px; right:20px; z-index:20000; margin:0; width:auto; max-height:30px !important; overflow:hidden;}
.google_translate_bottom_panel{position:absolute; background-color:transparent; bottom:10px; right:10px; z-index:2; margin:0; width:auto; max-height:28px !important; overflow:hidden;}*/

.table-borderless tbody tr td, .table-borderless tbody tr th, .table-borderless thead tr th {border: none;}

.job_type_td_h,
.job_type_td{display:inline; float:left; cursor:pointer; 
	font-size:9px; line-height:10px; font-weight:normal; color:#000000; text-align:center; vertical-align:top;
	width:110px; height:100px; 
	padding-left:0px; padding-right:0px; padding-top:0px; padding-bottom:0px; 
	margin-bottom:4px; margin-top:0px; margin-left:0px; margin-right:0px;
	background-image:url(/'.DIR_WS_WEBSITE_IMAGES_DIR.'job_type_bkg.png); background-repeat: no-repeat; background-position: 0px 0px;
	text-shadow:none;
}
.job_type_td_h,
.job_type_td:hover{background-image:url(/'.DIR_WS_WEBSITE_IMAGES_DIR.'job_type_bkg_h.png); background-repeat: no-repeat; background-position: 0px 0px;
	color:#ffffff; text-shadow: 1px 1px 1px #000000;
}

.job_type_td_h a:link,
.job_type_td_h a:visited,
.job_type_td_h a:active{color:#ffffff; text-decoration:none;}
.job_type_td_h a:hover{color:#ffffff; text-decoration:underline;}

.code_edit{font-family:Courier;}

.user_image_on_share{border-radius:50%; width:80px; height:80px; margin:4px; margin-bottom:10px; 
'.((int)IMAGES_HAVE_BORDER > 0?'border: '.IMAGES_HAVE_BORDER.'px solid #'.IMAGES_COLOR.'; padding:2px;':'').'
-moz-box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1 ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
-webkit-box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1?IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';
box-shadow:'.( defined('IMAGES_SHADOW') && (int)IMAGES_HAVE_SHADOW == 1 ? IMAGES_SHADOW.'px '.IMAGES_SHADOW.'px '.IMAGES_SHADOW_BLUR.'px #'.IMAGES_SHADOW_COLOR:'none').';}

.shares_grid_item{position:relative; cursor:pointer;}
.shares_grid_h1{line-height:normal;}
.shares_grid_h2{line-height:normal;}
.shares_grid_h3{line-height:normal;}
.shares_grid_h4{line-height:normal;}
.shares_grid_price{font-size:'.H1_FONT_SIZE.'px; text-align:center; color:#'.H1_COLOR.'; 
font-family:"'.H1_FONT_FAMILY.'", verdana, arial; 
font-weight:'.H1_FONT_WEIGHT.'; font-style:'.H1_FONT_STYLE.'; text-shadow:'.((int)H1_HAS_SHADOW == 1?H1_SHADOW.'px '.H1_SHADOW.'px 1px #'.H1_SHADOW_COLOR:'none').';}

.select_to_copy{cursor:pointer; border-bottom:1px dashed #'.COLOR1DARK.'}

.table-striped > tbody > tr:nth-of-type(2n+1) {
    background-color: #'.BOX_TYPE2_BG_COLOR/*COLOR2LIGHT*/.';
}
.table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th 
{
    border-top: 1px solid #'.BOX_TYPE2_OUTLINE.';
}
.btn-sm{-moz-border-radius:'.round(BUTTONS_RADIUS * 0.3).'px; border-radius:'.round(BUTTONS_RADIUS * 0.3).'px;}

.close_dlg_btn::before {
  content: "\00d7";
}
.currency_btn{background-color:transparent; border: 1px solid #'.COLOR3DARK.'; border-radius:4px; font-size:80%; margin:0 1pt 0 0;}

.modal-title{
	text-transform: uppercase;
	font-weight:bold;
}

.top_explanation_img{width:64px; height:64px; margin:6px; filter:grayscale(100%);}
.top_explanation_frame{position:absolute; width:64px; height:64px; background-color:#09827C; left:14px; top:4px; opacity:0.2;}

#goog-gt-tt{opacity:0; max-width:1px; max-height:1px; overflow:hidden;}

.goog-tooltip {
    display: none !important;
}
.goog-tooltip:hover {
    display: none !important;
}
.goog-text-highlight {
    background-color: transparent !important;
    border: none !important; 
    box-shadow: none !important;
}

';
if ( defined('DIR_WS_TEMP_CUSTOM_CODE') && file_exists(DIR_WS_TEMP_CUSTOM_CODE.'add_site_design.inc.php') )
	require_once(DIR_WS_TEMP_CUSTOM_CODE.'add_site_design.inc.php');

?>
