<?php
echo '
<script type="text/javascript">
if ( typeof show_hide_wait_sign == "undefined" ) {
	function show_hide_wait_sign(show)
	{
		if ( typeof show == "undefined" || show) {
			$("#wait_sign").show();
		}
		else {
			$("#wait_sign").hide();
		}
	}
}
var this_is_mobi_version = 0;
var show_top_alert_timerId = 0;
var it_is_mobile_device = 0;
var userid = "";
var password_session = "";
var is_loggedin = false;
var global_language = "en";
var global_language_name = "English";
var https = "https://";
if ( typeof fake_Android != "undefined" )
	var Fake_Android = new fake_Android();
else
	var Fake_Android = null;
var design_variables = {
BACKGROUND_COLOR :"'.BACKGROUND_COLOR.'",
COLOR1DARK :"'.COLOR1DARK.'",
COLOR1BASE :"'.COLOR1BASE.'",
COLOR1LIGHT :"'.COLOR1LIGHT.'",
COLOR2DARK :"'.COLOR2DARK.'",
COLOR2BASE :"'.COLOR2BASE.'",
COLOR2LIGHT :"'.COLOR2LIGHT.'",
COLOR3DARK :"'.COLOR3DARK.'",
COLOR3BASE :"'.COLOR3BASE.'",
COLOR3LIGHT :"'.COLOR3LIGHT.'",
COLOR_TEXT_BKG :"'.COLOR_TEXT_BKG.'",
PAGE_TEXT_COLOR_LIGHT :"'.PAGE_TEXT_COLOR_LIGHT.'",
BUTTONS_HAVE_SHADOW :"'.BUTTONS_HAVE_SHADOW.'",
IMAGES_LAYOUT :"'.IMAGES_LAYOUT.'",
INPUT_BOXES_BACKGROUND_COLOR :"'.INPUT_BOXES_BACKGROUND_COLOR.'",
INPUT_BOXES_HAVE_SHADOW :"'.INPUT_BOXES_HAVE_SHADOW.'",
LOGO_FONT_FAMILY :"'.LOGO_FONT_FAMILY.'",
LOGO_COLOR :"'.LOGO_COLOR.'",
LOGO_COLOR_TRANSPARENT :"'.LOGO_COLOR_TRANSPARENT.'",
LOGO_FONT_STYLE :"'.LOGO_FONT_STYLE.'",
LOGO_FONT_WEIGHT :"'.LOGO_FONT_WEIGHT.'",
LOGO_FONT_FIRST_LETTER_COLOR :"'.LOGO_FONT_FIRST_LETTER_COLOR.'",
LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT :"'.LOGO_FONT_FIRST_LETTER_COLOR_TRANSPARENT.'",
LOGO_HAS_SHADOW :"'.LOGO_HAS_SHADOW.'",
LOGO_TEXT_TRANSFORM :"'.LOGO_TEXT_TRANSFORM.'",
LOGO_ON_LEFT :"'.LOGO_ON_LEFT.'",
LOGO_HAS_BACKGROUND_COLOR :"'.LOGO_HAS_BACKGROUND_COLOR.'",
LOGO_BACKGROUND_COLOR :"'.LOGO_BACKGROUND_COLOR.'",
MENU_STYLE :"'.MENU_STYLE.'",
MENU_FLOAT :"'.MENU_FLOAT.'",
MENU_COLOR :"'.MENU_COLOR.'",
MENU_TEXT_TRANSFORM :"'.MENU_TEXT_TRANSFORM.'",
MENU_HAS_SHADOW :"'.MENU_HAS_SHADOW.'",
MENU_HAS_BACKGROUND_COLOR :"'.MENU_HAS_BACKGROUND_COLOR.'",
MENU_BACKGROUND_COLOR :"'.MENU_BACKGROUND_COLOR.'",
MENU_BORDER_STYLE :"'.MENU_BORDER_STYLE.'",
MENU_HAS_ROUNDED_EDGES :"'.MENU_HAS_ROUNDED_EDGES.'",
MENU_BUTTONS_HAVE_ROUNDED_EDGES :"'.MENU_BUTTONS_HAVE_ROUNDED_EDGES.'",
CENTRAL_TABLE_HAS_BORDER :"'.CENTRAL_TABLE_HAS_BORDER.'",
CENTRAL_TABLE_HAS_ROUNDED_EDGES :"'.CENTRAL_TABLE_HAS_ROUNDED_EDGES.'",
CENTRAL_TABLE_HAS_SHADOW :"'.CENTRAL_TABLE_HAS_SHADOW.'",
H1_COLOR :"'.H1_COLOR.'",
H1_FONT_FIRST_LETTER_COLOR :"'.H1_FONT_FIRST_LETTER_COLOR.'",
H1_TEXT_TRANSFORM :"'.H1_TEXT_TRANSFORM.'",
H1_HAS_SHADOW :"'.H1_HAS_SHADOW.'",
H1_HAS_BACKGROUND_COLOR :"'.H1_HAS_BACKGROUND_COLOR.'",
H1_BACKGROUND_COLOR :"'.H1_BACKGROUND_COLOR.'",
H1_BOTTOM_BORDER_STYLE :"'.H1_BOTTOM_BORDER_STYLE.'",
H1_HAS_ROUNDED_EDGES :"'.H1_HAS_ROUNDED_EDGES.'",
H2_COLOR :"'.H2_COLOR.'",
H2_FONT_FIRST_LETTER_COLOR :"'.H2_FONT_FIRST_LETTER_COLOR.'",
H2_TEXT_TRANSFORM :"'.H2_TEXT_TRANSFORM.'",
H2_HAS_SHADOW :"'.H2_HAS_SHADOW.'",
H2_HAS_BACKGROUND_COLOR :"'.H2_HAS_BACKGROUND_COLOR.'",
H2_BACKGROUND_COLOR :"'.H2_BACKGROUND_COLOR.'",
H2_BOTTOM_BORDER_STYLE :"'.H2_BOTTOM_BORDER_STYLE.'",
BULLETS_STYLE :"'.BULLETS_STYLE.'",
BULLETS_COLOR :"'.BULLETS_COLOR.'",
BULLETS_HAS_SHADOW :"'.BULLETS_HAS_SHADOW.'",
CHECKBOXES_STYLE :"'.CHECKBOXES_STYLE.'",
CHECKBOXES_COLOR :"'.CHECKBOXES_COLOR.'",
CHECKBOXES_HAS_SHADOW :"'.CHECKBOXES_HAS_SHADOW.'",
HR_COLOR :"'.HR_COLOR.'",
HR_STYLE :"'.HR_STYLE.'",
FOOTER_COLOR :"'.FOOTER_COLOR.'",
FOOTER_TEXT_TRANSFORM :"'.FOOTER_TEXT_TRANSFORM.'",
FOOTER_TEXT_ALIGN :"'.FOOTER_TEXT_ALIGN.'",
FOOTER_HAS_SHADOW :"'.FOOTER_HAS_SHADOW.'",
FOOTER_HAS_BACKGROUND_COLOR :"'.FOOTER_HAS_BACKGROUND_COLOR.'",
FOOTER_BACKGROUND_COLOR :"'.FOOTER_BACKGROUND_COLOR.'",
FIRST_PAGE_LAYOUT :"'.FIRST_PAGE_LAYOUT.'",
BACKGROUND_IMAGE :"'.BACKGROUND_IMAGE.'",
TEXT_FONT_SIZE :"'.TEXT_FONT_SIZE.'",
PAGE_TEXT_COLOR :"'.PAGE_TEXT_COLOR.'",
LIGHT :"'.LIGHT.'",
TEXT_FONT_FAMILY :"'.TEXT_FONT_FAMILY.'",
TEXT_ALIGNMENT :"'.TEXT_ALIGNMENT.'",
BUTTONS_RADIUS :"'.BUTTONS_RADIUS.'",
BTN_BKG_COLOR_TOP :"'.BTN_BKG_COLOR_TOP.'",
BTN_BKG_COLOR_BOTTOM :"'.BTN_BKG_COLOR_BOTTOM.'",
BTN_COLOR_ACTIVE :"'.BTN_COLOR_ACTIVE.'",
BTN_COLOR_HOVER :"'.BTN_COLOR_HOVER.'",
BUTTONS_GRADIENT_BKG :"'.BUTTONS_GRADIENT_BKG.'",
BUTTONS_OUTLINE_SIZE :"'.BUTTONS_OUTLINE_SIZE.'",
BUTTONS_OUTLINE :"'.BUTTONS_OUTLINE.'",
BUTTONS_SHADOW :"'.BUTTONS_SHADOW.'",
BUTTONS_SHADOW_COLOR :"'.BUTTONS_SHADOW_COLOR.'",
BUTTONS_TEXT_SHADOW :"'.BUTTONS_TEXT_SHADOW.'",
BUTTONS_TEXT_SHADOW_COLOR :"'.BUTTONS_TEXT_SHADOW_COLOR.'",
BUTTONS_FONT_FAMILY :"'.BUTTONS_FONT_FAMILY.'",
IMAGES_HAVE_BORDER :"'.IMAGES_HAVE_BORDER.'",
IMAGES_COLOR :"'.IMAGES_COLOR.'",
IMAGES_HAVE_BKG_COLOR :"'.IMAGES_HAVE_BKG_COLOR.'",
IMAGES_BKG_COLOR :"'.IMAGES_BKG_COLOR.'",
IMAGES_HAVE_SHADOW :"'.IMAGES_HAVE_SHADOW.'",
IMAGES_SHADOW :"'.(defined('IMAGES_SHADOW') ? IMAGES_SHADOW : '').'",
IMAGES_SHADOW_COLOR :"'.(defined('IMAGES_SHADOW_COLOR') ? IMAGES_SHADOW_COLOR : '').'",
IMAGES_SHADOW_BLUR :"'.(defined('IMAGES_SHADOW_BLUR') ? IMAGES_SHADOW_BLUR : '').'",
IMAGES_HAVE_ROUNDED_EDGES :"'.IMAGES_HAVE_ROUNDED_EDGES.'",
IMAGE_IN_INFO_PANE_GRAYSCALE :"'.IMAGE_IN_INFO_PANE_GRAYSCALE.'",
IMAGE_IN_INFO_PANE_BLEND_COLOR :"'.IMAGE_IN_INFO_PANE_BLEND_COLOR.'",
IMAGE_IN_INFO_PANE_BLEND_OPACITY :"'.IMAGE_IN_INFO_PANE_BLEND_OPACITY.'",
INPUT_BOXES_RADIUS :"'.INPUT_BOXES_RADIUS.'",
INPUT_BOXES_HAVE_OUTLINE :"'.INPUT_BOXES_HAVE_OUTLINE.'",
INPUT_BOXES_OUTLINE :"'.INPUT_BOXES_OUTLINE.'",
BOXES_OUTLINE :"'.BOXES_OUTLINE.'",
INPUT_BOXES_SHADOW :"'.INPUT_BOXES_SHADOW.'",
INPUT_BOXES_SHADOW_COLOR :"'.INPUT_BOXES_SHADOW_COLOR.'",
BOXES_SHADOW_COLOR :"'.BOXES_SHADOW_COLOR.'",
BOX_TYPE1_RADIUS :"'.BOX_TYPE1_RADIUS.'",
BOX_TYPE1_HAS_BACKGROUND_COLOR :"'.BOX_TYPE1_HAS_BACKGROUND_COLOR.'",
BOX_TYPE1_BG_COLOR :"'.BOX_TYPE1_BG_COLOR.'",
BOX_TYPE1_BACKGROUND_IMAGE :"'.BOX_TYPE1_BACKGROUND_IMAGE.'",
BOX_TYPE1_HAVE_OUTLINE :"'.BOX_TYPE1_HAVE_OUTLINE.'",
BOX_TYPE1_OUTLINE :"'.BOX_TYPE1_OUTLINE.'",
BOX_TYPE1_HAVE_SHADOW :"'.BOX_TYPE1_HAVE_SHADOW.'",
BOX_TYPE1_SHADOW :"'.BOX_TYPE1_SHADOW.'",
BOX_TYPE1_SHADOW_COLOR :"'.BOX_TYPE1_SHADOW_COLOR.'",
BOX_TYPE1_SHADOW_BLUR :"'.BOX_TYPE1_SHADOW_BLUR.'",
TOP_INFO_PANE_STYLE_NUMBER1 :"'.TOP_INFO_PANE_STYLE_NUMBER1.'",
BOX_TYPE2_RADIUS :"'.BOX_TYPE2_RADIUS.'",
BOX_TYPE2_HAS_BACKGROUND_COLOR :"'.BOX_TYPE2_HAS_BACKGROUND_COLOR.'",
BOX_TYPE2_BG_COLOR :"'.BOX_TYPE2_BG_COLOR.'",
BOX_TYPE2_BACKGROUND_IMAGE :"'.BOX_TYPE2_BACKGROUND_IMAGE.'",
BOX_TYPE2_HAVE_OUTLINE :"'.BOX_TYPE2_HAVE_OUTLINE.'",
BOX_TYPE2_OUTLINE :"'.BOX_TYPE2_OUTLINE.'",
BOX_TYPE2_HAVE_SHADOW :"'.BOX_TYPE2_HAVE_SHADOW.'",
BOX_TYPE2_SHADOW :"'.BOX_TYPE2_SHADOW.'",
BOX_TYPE2_SHADOW_COLOR :"'.BOX_TYPE2_SHADOW_COLOR.'",
BOX_TYPE2_SHADOW_BLUR :"'.BOX_TYPE2_SHADOW_BLUR.'",
BOX_TYPE2_FONT_FAMILY :"'.BOX_TYPE2_FONT_FAMILY.'",
BOX_TYPE2_FONT_SIZE :"'.BOX_TYPE2_FONT_SIZE.'",
BOX_TYPE2_COLOR :"'.BOX_TYPE2_COLOR.'",
BOX_TYPE2_FONT_STYLE :"'.BOX_TYPE2_FONT_STYLE.'",
BOX_TYPE2_FONT_WEIGHT :"'.BOX_TYPE2_FONT_WEIGHT.'",
BOX_TYPE2_TEXT_TRANSFORM :"'.BOX_TYPE2_TEXT_TRANSFORM.'",
BOX_TYPE2_TEXT_HAS_SHADOW :"'.BOX_TYPE2_TEXT_HAS_SHADOW.'",
BOX_TYPE2_TEXT_SHADOW :"'.BOX_TYPE2_TEXT_SHADOW.'",
BOX_TYPE2_TEXT_SHADOW_COLOR :"'.BOX_TYPE2_TEXT_SHADOW_COLOR.'",
TOP_INFO_PANE_STYLE_NUMBER2 :"'.TOP_INFO_PANE_STYLE_NUMBER2.'",
BOX_TYPE3_RADIUS :"'.BOX_TYPE3_RADIUS.'",
BOX_TYPE3_HAS_BACKGROUND_COLOR :"'.BOX_TYPE3_HAS_BACKGROUND_COLOR.'",
BOX_TYPE3_BG_COLOR :"'.BOX_TYPE3_BG_COLOR.'",
BOX_TYPE3_BACKGROUND_IMAGE :"'.BOX_TYPE3_BACKGROUND_IMAGE.'",
BOX_TYPE3_HAVE_OUTLINE :"'.BOX_TYPE3_HAVE_OUTLINE.'",
BOX_TYPE3_OUTLINE :"'.BOX_TYPE3_OUTLINE.'",
BOX_TYPE3_HAVE_SHADOW :"'.BOX_TYPE3_HAVE_SHADOW.'",
BOX_TYPE3_SHADOW :"'.BOX_TYPE3_SHADOW.'",
BOX_TYPE3_SHADOW_COLOR :"'.BOX_TYPE3_SHADOW_COLOR.'",
BOX_TYPE3_SHADOW_BLUR :"'.BOX_TYPE3_SHADOW_BLUR.'",
BOX_TYPE3_FONT_FAMILY :"'.BOX_TYPE3_FONT_FAMILY.'",
BOX_TYPE3_FONT_SIZE :"'.BOX_TYPE3_FONT_SIZE.'",
BOX_TYPE3_COLOR :"'.BOX_TYPE3_COLOR.'",
BOX_TYPE3_FONT_STYLE :"'.BOX_TYPE3_FONT_STYLE.'",
BOX_TYPE3_FONT_WEIGHT :"'.BOX_TYPE3_FONT_WEIGHT.'",
BOX_TYPE3_TEXT_TRANSFORM :"'.BOX_TYPE3_TEXT_TRANSFORM.'",
BOX_TYPE3_TEXT_HAS_SHADOW :"'.BOX_TYPE3_TEXT_HAS_SHADOW.'",
BOX_TYPE3_TEXT_SHADOW :"'.BOX_TYPE3_TEXT_SHADOW.'",
BOX_TYPE3_TEXT_SHADOW_COLOR :"'.BOX_TYPE3_TEXT_SHADOW_COLOR.'",
TOP_INFO_PANE_STYLE_NUMBER3 :"'.TOP_INFO_PANE_STYLE_NUMBER3.'",
DARK1 :"'.DARK1.'",
BASE1 :"'.BASE1.'",
LIGHT1 :"'.LIGHT1.'",
DARK2 :"'.DARK2.'",
BASE2 :"'.BASE2.'",
LIGHT2 :"'.LIGHT2.'",
DARK3 :"'.DARK3.'",
BASE3 :"'.BASE3.'",
LIGHT3 :"'.LIGHT3.'",
LOGO_HEIGHT :"'.LOGO_HEIGHT.'",
COLLAPSE_HEADER_ON_LOGIN :"'.COLLAPSE_HEADER_ON_LOGIN.'",
LOGO_IMG_WIDTH :"'.LOGO_IMG_WIDTH.'",
LOGO_IMG_HEIGHT :"'.LOGO_IMG_HEIGHT.'",
BUSINESS_NAME_FONT_FAMILY :"'.BUSINESS_NAME_FONT_FAMILY.'",
BUSINESS_NAME_FONT_SIZE :"'.BUSINESS_NAME_FONT_SIZE.'",
BUSINESS_NAME_FONT_STYLE :"'.BUSINESS_NAME_FONT_STYLE.'",
BUSINESS_NAME_FONT_WEIGHT :"'.BUSINESS_NAME_FONT_WEIGHT.'",
LOGO_FONT_SIZE :"'.LOGO_FONT_SIZE.'",
TRANSPARENT_YES :"'.TRANSPARENT_YES.'",
TRANSPARENT_NONE :"'.TRANSPARENT_NONE.'",
LOGO_SHADOW_WIDTH :"'.LOGO_SHADOW_WIDTH.'",
LOGO_SHADOW_COLOR :"'.LOGO_SHADOW_COLOR.'",
RIGHTTX1 :"'.RIGHTTX1.'",
RIGHTTX2 :"'.RIGHTTX2.'",
RIGHTTX_WIDTH :"'.RIGHTTX_WIDTH.'",
NONE :"'.NONE.'",
YES :"'.YES.'",
MENU_HEIGHT :"'.MENU_HEIGHT.'",
MENU_BTN_WIDTH :"'.MENU_BTN_WIDTH.'",
MENU_FONT_FAMILY :"'.MENU_FONT_FAMILY.'",
MENU_HOVER_COLOR :"'.MENU_HOVER_COLOR.'",
MENU_PRESSED_COLOR :"'.MENU_PRESSED_COLOR.'",
MENU_FONT_SIZE :"'.MENU_FONT_SIZE.'",
MENU_FONT_STYLE :"'.MENU_FONT_STYLE.'",
MENU_FONT_WEIGHT :"'.MENU_FONT_WEIGHT.'",
MENU_SHADOW :"'.MENU_SHADOW.'",
MENU_SHADOW_COLOR :"'.MENU_SHADOW_COLOR.'",
ITEMS_HAVE_BACKGROUND_COLOR :"'.ITEMS_HAVE_BACKGROUND_COLOR.'",
ITEMS_BACKGROUND_COLOR :"'.ITEMS_BACKGROUND_COLOR.'",
DROPDOWN_MENU_HAVE_SHADOW :"'.DROPDOWN_MENU_HAVE_SHADOW.'",
DROPDOWN_MENU_SHADOW :"'.DROPDOWN_MENU_SHADOW.'",
DROPDOWN_MENU_SHADOW_COLOR :"'.DROPDOWN_MENU_SHADOW_COLOR.'",
MENU_BACKGROUND_IMAGE :"'.MENU_BACKGROUND_IMAGE.'",
MENU_BTN_BACKGROUND_IMAGE :"'.MENU_BTN_BACKGROUND_IMAGE.'",
MENU_BUTTONS_HAVE_SHADOW :"'.MENU_BUTTONS_HAVE_SHADOW.'",
MENU_BUTTONS_SHADOW :"'.MENU_BUTTONS_SHADOW.'",
MENU_BUTTONS_SHADOW_COLOR :"'.MENU_BUTTONS_SHADOW_COLOR.'",
H1_FONT_FAMILY :"'.H1_FONT_FAMILY.'",
H1_FONT_SIZE :"'.H1_FONT_SIZE.'",
H1_FONT_STYLE :"'.H1_FONT_STYLE.'",
H1_FONT_WEIGHT :"'.H1_FONT_WEIGHT.'",
H1_SHADOW :"'.H1_SHADOW.'",
H1_SHADOW_COLOR :"'.H1_SHADOW_COLOR.'",
H1_BACKGROUND_IMAGE :"'.H1_BACKGROUND_IMAGE.'",
H2_FONT_FAMILY :"'.H2_FONT_FAMILY.'",
H2_FONT_SIZE :"'.H2_FONT_SIZE.'",
H2_FONT_STYLE :"'.H2_FONT_STYLE.'",
H2_FONT_WEIGHT :"'.H2_FONT_WEIGHT.'",
H2_SHADOW :"'.H2_SHADOW.'",
H2_SHADOW_COLOR :"'.H2_SHADOW_COLOR.'",
H2_BACKGROUND_IMAGE :"'.H2_BACKGROUND_IMAGE.'",
BULLETS_FONT_SIZE :"'.BULLETS_FONT_SIZE.'",
BULLETS_FONT_STYLE :"'.BULLETS_FONT_STYLE.'",
BULLETS_FONT_WEIGHT :"'.BULLETS_FONT_WEIGHT.'",
BULLETS_SHADOW :"'.BULLETS_SHADOW.'",
BULLETS_SHADOW_COLOR :"'.BULLETS_SHADOW_COLOR.'",
BULLETS_BACKGROUND_IMAGE :"'.BULLETS_BACKGROUND_IMAGE.'",
CHECKBOXES_FONT_SIZE :"'.CHECKBOXES_FONT_SIZE.'",
CHECKBOXES_FONT_STYLE :"'.CHECKBOXES_FONT_STYLE.'",
CHECKBOXES_FONT_WEIGHT :"'.CHECKBOXES_FONT_WEIGHT.'",
CHECKBOXES_SHADOW :"'.CHECKBOXES_SHADOW.'",
CHECKBOXES_SHADOW_COLOR :"'.CHECKBOXES_SHADOW_COLOR.'",
CHECKBOXES_BACKGROUND_IMAGE :"'.CHECKBOXES_BACKGROUND_IMAGE.'",
HR_BACKGROUND_IMAGE :"'.HR_BACKGROUND_IMAGE.'",
FOOTER_FONT_FAMILY :"'.FOOTER_FONT_FAMILY.'",
FOTR_SECTION_COLOR :"'.FOTR_SECTION_COLOR.'",
FOTR_SECTION_FONT_SIZE :"'.FOTR_SECTION_FONT_SIZE.'",
FOTR_SECTION_FONT_STYLE :"'.FOTR_SECTION_FONT_STYLE.'",
FOTR_SECTION_FONT_WEIGHT :"'.FOTR_SECTION_FONT_WEIGHT.'",
FOOTER_FONT_SIZE :"'.FOOTER_FONT_SIZE.'",
FOOTER_FONT_STYLE :"'.FOOTER_FONT_STYLE.'",
FOOTER_FONT_WEIGHT :"'.FOOTER_FONT_WEIGHT.'",
FOOTER_SHADOW :"'.FOOTER_SHADOW.'",
FOOTER_SHADOW_COLOR :"'.FOOTER_SHADOW_COLOR.'",
FOOTER_BACKGROUND_IMAGE :"'.FOOTER_BACKGROUND_IMAGE.'",
FOOTER_BORDER_STYLE :"'.FOOTER_BORDER_STYLE.'",
FOOTER_HEIGHT :"'.FOOTER_HEIGHT.'"
};
if (typeof user_is_loggedin == "undefined" ) {
	function user_is_loggedin()
	{
		return '.($user_account->is_loggedin() ? '1' : '0').';
	}
}
</script>';
?>