<?php

require_once(DIR_WS_CLASSES.'stock.class.php');
require_once(DIR_WS_CLASSES.'bitcoin.pay_processor.class.php');

function write_signup_code($form_number, $action = '/signup.php', $button_caption = '')
{
	return '
	<form method="post" name="sbmt_frm'.$form_number.'" action="'.$action.'">
		<input type="hidden" name="parentid" value="">
		<input type="hidden" name="skip_captcha" value="1">
		<input type="hidden" name="firstname_default" value="">
		<input type="hidden" name="lastname_default" value="">
		<input type="hidden" name="email_default" value="">
		<style type="text/css" media="all">
			#signup_code_row .row .col-md-6{margin-bottom:20px;}
		</style>
		<div id="signup_code_row">
			<div class="row">
				<div class="col-md-6"><input type="text" name="firstname" maxlength="64" class="form-control" placeholder="First Name"></div>
				<div class="col-md-6"><input type="text" name="lastname" maxlength="64" class="form-control" placeholder="Last Name"></div>
			</div>
		</div>
		<center>
			<button class="btn btn-success btn-lg">'.(empty($button_caption) ? 'Sign Up...' : $button_caption).'</button><br>
			<span style="font-size:12px; padding-top:0px; margin-top:4px; padding-left:20px; ">Signup is free</span>
		</center>
	</form>
	';
}

if ( defined('SLIDER_IMAGE1') && SLIDER_IMAGE1 != '' ) {
	echo '
	<style type="text/css" media="all">
	@import "/css/css_slider.css";
	</style>
	<div>
		<input class="slider_progress" type=radio name="slider" id="slide1" checked style="display:none;" />
		'.(defined('SLIDER_IMAGE2') && SLIDER_IMAGE2 != ''?'<input class="slider_progress" type="radio" name="slider" id="slide2" style="display:none;">':'').'
		'.(defined('SLIDER_IMAGE3') && SLIDER_IMAGE3 != ''?'<input class="slider_progress" type="radio" name="slider" id="slide3" style="display:none;">':'').'
		'.(defined('SLIDER_IMAGE4') && SLIDER_IMAGE4 != ''?'<input class="slider_progress" type="radio" name="slider" id="slide4" style="display:none;">':'').'
		
		<div id="slides">
			<div id="overflow">
				<div class="inner">
					'.(defined('SLIDER_IMAGE1') && SLIDER_IMAGE1 != ''?'<a href="/signup.php"><article><img src="/'.DIR_WS_TEMP_IMAGES_NAME.SLIDER_IMAGE1.'" class="slider_image" style="display:none; border:none;"></article></a>':'').'
					'.(defined('SLIDER_IMAGE2') && SLIDER_IMAGE2 != ''?'<a href="/signup.php"><article><img src="/'.DIR_WS_TEMP_IMAGES_NAME.SLIDER_IMAGE2.'" class="slider_image" style="display:none; border:none;"></article></a>':'').'
					'.(defined('SLIDER_IMAGE3') && SLIDER_IMAGE3 != ''?'<a href="/signup.php"><article><img src="/'.DIR_WS_TEMP_IMAGES_NAME.SLIDER_IMAGE3.'" class="slider_image" style="display:none; border:none;"></article></a>':'').'
					'.(defined('SLIDER_IMAGE4') && SLIDER_IMAGE4 != ''?'<a href="/signup.php"><article><img src="/'.DIR_WS_TEMP_IMAGES_NAME.SLIDER_IMAGE4.'" class="slider_image" style="display:none; border:none;"></article></a>':'').'
					
				</div>
			</div>
		</div>
		'.(false?'
		<div id="slider_controls">
			'.(defined('SLIDER_IMAGE1') && SLIDER_IMAGE1 != ''?'<label for=slide1></label>':'').'
			'.(defined('SLIDER_IMAGE2') && SLIDER_IMAGE2 != ''?'<label for=slide2></label>':'').'
			'.(defined('SLIDER_IMAGE3') && SLIDER_IMAGE3 != ''?'<label for=slide3></label>':'').'
			'.(defined('SLIDER_IMAGE4') && SLIDER_IMAGE4 != ''?'<label for=slide4></label>':'').'
		</div> 
		<div id="active">
			'.(defined('SLIDER_IMAGE1') && SLIDER_IMAGE1 != ''?'<label for=slide1></label>':'').'
			'.(defined('SLIDER_IMAGE2') && SLIDER_IMAGE2 != ''?'<label for=slide2></label>':'').'
			'.(defined('SLIDER_IMAGE3') && SLIDER_IMAGE3 != ''?'<label for=slide3></label>':'').'
			'.(defined('SLIDER_IMAGE4') && SLIDER_IMAGE4 != ''?'<label for=slide4></label>':'').'

		</div>
		':'').'
	</div>
	<script language="JavaScript">
	var current_slider = 1;
	function show_next_slide()
	{
		$(".slider_progress").each(function( index ) {
			if ( index == current_slider ) {
				$(this).prop("checked", true);
				setTimeout("show_next_slide();", 7000);
			}
		});
		current_slider++;
	}
	$(".slider_image").show();
	setTimeout("show_next_slide();", 7000);
	</script>
	';
}
$p1 ='
'.(PARAGRAPH_HEADER_1 != '' || (isset($dont_read_design_data) && $dont_read_design_data) ?'<h1 '.(isset($dont_read_design_data) && $dont_read_design_data?'class="editable_item" id="paragraph_header_1_inline_edit"':'').'>'.$user_account->parse_common_params(PARAGRAPH_HEADER_1).'</h1>':'').'
	'.(PARAGRAPH_IMAGE_1 != 'none'?
		'<img src="'.DIR_WS_TEMP_IMAGES_NAME.PARAGRAPH_IMAGE_1.'" border="0" alt="" title="" class="first_page_image editable_image" id="paragraph_image_1_inline_edit">'
		:'').'
'.(isset($dont_read_design_data) && $dont_read_design_data?'<div class="editable_item" id="paragraph_text_1_inline_edit" style="min-height:20px;">':'').$user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", PARAGRAPH_TEXT_1)).(isset($dont_read_design_data) && $dont_read_design_data?'</div>':'');
$p2 ='
'.(PARAGRAPH_HEADER_2 != '' || (isset($dont_read_design_data) && $dont_read_design_data) ?'<h1 '.(isset($dont_read_design_data) && $dont_read_design_data?'class="editable_item" id="paragraph_header_2_inline_edit"':'').'>'.$user_account->parse_common_params(PARAGRAPH_HEADER_2).'</h1>':'').'
	'.(PARAGRAPH_IMAGE_2 != 'none'?'<img src="'.DIR_WS_TEMP_IMAGES_NAME.PARAGRAPH_IMAGE_2.'" border="0" alt="" title="" class="first_page_image editable_image" id="paragraph_image_2_inline_edit">':'').'
'.(isset($dont_read_design_data) && $dont_read_design_data?'<div class="editable_item" id="paragraph_text_2_inline_edit">':'').$user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", PARAGRAPH_TEXT_2)).(isset($dont_read_design_data) && $dont_read_design_data?'</div>':'');
$p3 ='
'.(PARAGRAPH_HEADER_3 != '' || (isset($dont_read_design_data) && $dont_read_design_data) ?'<h1 '.(isset($dont_read_design_data) && $dont_read_design_data?'class="editable_item" id="paragraph_header_3_inline_edit"':'').'>'.$user_account->parse_common_params(PARAGRAPH_HEADER_3).'</h1>':'').'
	'.(PARAGRAPH_IMAGE_3 != 'none'?'<img src="'.DIR_WS_TEMP_IMAGES_NAME.PARAGRAPH_IMAGE_3.'" border="0" alt="" title="" class="first_page_image editable_image" id="paragraph_image_3_inline_edit">':'').'
'.(isset($dont_read_design_data) && $dont_read_design_data?'<div class="editable_item" id="paragraph_text_3_inline_edit">':'').$user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", PARAGRAPH_TEXT_3)).(isset($dont_read_design_data) && $dont_read_design_data?'</div>':'');
$p4 ='
'.(PARAGRAPH_HEADER_4 != '' || (isset($dont_read_design_data) && $dont_read_design_data) ?'<h1 '.(isset($dont_read_design_data) && $dont_read_design_data?'class="editable_item" id="paragraph_header_4_inline_edit"':'').'>'.$user_account->parse_common_params(PARAGRAPH_HEADER_4).'</h1>':'').'
	'.(PARAGRAPH_IMAGE_4 != 'none'?'<img src="'.DIR_WS_TEMP_IMAGES_NAME.PARAGRAPH_IMAGE_4.'" border="0" alt="" title="" class="first_page_image editable_image" id="paragraph_image_4_inline_edit">':'').'
'.(isset($dont_read_design_data) && $dont_read_design_data?'<div class="editable_item" id="paragraph_text_4_inline_edit">':'').$user_account->parse_common_params(preg_replace("/<break\/>/", "\r\n", PARAGRAPH_TEXT_4)).(isset($dont_read_design_data) && $dont_read_design_data?'</div>':'');

$out_str = '';
$signup_table = '{$signup_table';
$signup_button_caption = 'Sign Up...';

if ( is_integer(strpos($p1.$p2.$p3.$p4, $signup_table)) ) {
	$signup_tab = get_text_between_tags($p1.$p2.$p3.$p4, $signup_table, '}');
	if ( is_integer(strpos($signup_tab, '$button_caption="')) )
		$signup_button_caption = get_text_between_tags($signup_tab, '$button_caption="', '"');
}

if ( (int)FIRST_PAGE_LAYOUT == 1 ) {
	$out_str = $out_str.$p1.$p2.$p3.$p4;
	$number_of_signup = substr_count($out_str, $signup_table);
	for ($i = 1; $i <= $number_of_signup; $i++) {
		$s2 = write_signup_code($i, '/signup.php', $signup_button_caption);
		delete_text_between_tags($out_str, $signup_table, '}', false, $s2);
	}
}
else {
	$out_str = $out_str.'
	<div class="row">
		<div class="col-md-6" style="vertical-align:top;">'.$p1.'</div>
		<div class="col-md-6" style="vertical-align:top;">'.$p2.'</div>
	</div>
	<div class="row">
		<div class="col-md-6" style="vertical-align:top;">'.$p3.'</div>
		<div class="col-md-6" style="vertical-align:top;">'.$p4.'</div>
	</div>';
	$number_of_signup = substr_count($out_str, $signup_table);
	for ($i = 1; $i <= $number_of_signup; $i++) {
		$s2 = write_signup_code($i, '/signup.php', $signup_button_caption);
		delete_text_between_tags($out_str, $signup_table, '}', false, $s2);
	}
}

$out_str = str_replace('{$signup_button}', '<center><form method="post" action="/signup.php"><button>Sign Up...</button></form></center>', $out_str);
$out_str = str_replace('{$payouts}', get_file_variable(VAR_PAYMENT_PROOFS_NAME), $out_str);
$out_str = $out_str.'
<style type="text/css" media="all">
#main_table thead tr th{border:none;}
</style>
<script language="JavaScript">
$("#main_table").addClass("table-striped");
</script>
';

$out_str = $user_account->parse_common_params($out_str);

if ( is_integer(strpos($out_str, '{$profit_calculator')) ) {
	$stocks_list = '';
	$calc_code = '';
	$stocks_list_file = DIR_WS_TEMP.'$$$calc_stocks_list.txt';
	if ( !file_exists($stocks_list_file) || time() - filemtime($stocks_list_file) > 60 * 60 /*|| defined('DEBUG_MODE')*/ ) {
		$tmp_stock = new Stock();
		$stocks = $tmp_stock->get_list('top_shares', 'top_shares', 20);
		foreach ( $stocks as $stock ) {
			$growth = pow($stock->stat_current_price / $stock->stat_price_30_day_ago, 1 / 30);
			if ( $growth > 1.02 )
				$growth = 1.02 + rand(1, 10) / 10000;
			$stocks_list = $stocks_list.'<option value="'.$stock->stat_current_price.'|'.
			( $growth ).
			'|'.($stock->dividend / $stock->dividend_frequency).'">'.$stock->name.'</option>';
		}
		//if ( !defined('DEBUG_MODE') ) 
			file_put_contents($stocks_list_file, $stocks_list);
	}
	else
		$stocks_list = file_get_contents($stocks_list_file);

	if ( !empty($stocks_list) ) {
		$calc_code = '
		<div class="paragraph" style="width:320px; padding:6px; margin:0px;
			background-color:#'.COLOR2LIGHT.';
		   -webkit-box-shadow: 4px 5px 6px 0px rgba(255,255,255,1)inset, -1px -1px 8px 0px #'.COLOR2DARK.' inset '.((int)IMAGES_HAVE_SHADOW == 1?', 0px 0px 6px #000000':'').';
			  -moz-box-shadow: 4px 5px 6px 0px rgba(255,255,255,1)inset, -1px -1px 8px 0px #'.COLOR2DARK.' inset '.((int)IMAGES_HAVE_SHADOW == 1?', 0px 0px 6px #000000':'').';
				   box-shadow: 4px 5px 6px 0px rgba(255,255,255,1)inset, -1px -1px 8px 0px #'.COLOR2DARK.' inset '.((int)IMAGES_HAVE_SHADOW == 1?', 0px 0px 6px #000000':'').';
		   border: solid 1px #'.COLOR2BASE.';
		   -webkit-border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'5':'0').'px;
			  -moz-border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'5':'0').'px;
				   border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'5':'0').'px;
		" >
			<span style="margin-left:10px;">
			Company:<br>
			</span>
			<select class="form-control" style="width:280px; margin-left:10px;" onchange="calculateTotal();" id="calc_company">
			'.$stocks_list.'
			</select>
			
			<table style="width:100%; margin-left:10px;" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><table width="130"></table></td><td width="100%"></td>
			</tr>
			<tr>
				<td style="vertical-align:middle; height:30px;" >Number of Shares:</td>
				<td style="vertical-align:middle;">
					<button class="btn btn-primary btn-xs button_inc_dec" onclick="return decrement_input_value(this.parentNode.getElementsByTagName(\'input\')[0]);" title="decrease quantity" style="color:#ff0000;">-</button>
					<input type="text" id="calc_quantity" class="form-control" style="width:50px; display:inline;" value="1" >
					<button class="btn btn-primary btn-xs button_inc_dec" onclick="return increment_input_value(this.parentNode.getElementsByTagName(\'input\')[0]);" title="increase quantity" style="color:#00ff00; ">+</button>
				</td>
			</tr>
			<tr>
				<td></td>
				<td style="vertical-align:middle;">
					<span style="font-size:8px;">(<span id="calc_price_per_share"></span>) per share</span>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:middle; height:30px;" >Period:</td>
				<td style="vertical-align:middle;">
					<select class="form-control" style="width:150px;" id="calc_period">
					<option value="10">10 Days</option>
					<option value="14">2 Weeks</option>
					<option value="30" selected>1 Month</option>
					<option value="60">2 Month</option>
					</select>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:10px;" colspan="2"></td>
			</tr>
			</table>
			<table style="width:100%; margin-left:0px; 
				background-color: #'.COLOR2BASE.';
		   -webkit-box-shadow: 1px 1px 6px 0px #'.COLOR2DARK.' inset, -1px -1px 8px 1px rgba(255,255,255,1)inset;
			  -moz-box-shadow: 1px 1px 6px 0px #'.COLOR2DARK.' inset, -1px -1px 8px 1px rgba(255,255,255,1)inset;
				   box-shadow: 1px 1px 6px 0px #'.COLOR2DARK.' inset, -1px -1px 8px 1px rgba(255,255,255,1)inset;
		   border: solid 1px #'.COLOR2BASE.';
		   -webkit-border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'4':'0').'px;
			  -moz-border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'4':'0').'px;
				   border-radius: '.((int)IMAGES_HAVE_ROUNDED_EDGES == 1?'4':'0').'px;

			" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><table width="140"></table></td><td><table width="60"></table></td><td width="100%"></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:30px; padding-left:10px;" >Investment:</td>
				<td style="vertical-align:bottom; text-align:right;"><b><span id="calc_investment"></span></b></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:25px; padding-left:10px;" >Dividend:</td>
				<td style="vertical-align:bottom; text-align:right;"><span id="calc_dividend"></span></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:25px; padding-left:10px;" >Price Growth:</td>
				<td style="vertical-align:bottom; text-align:right;"><span id="calc_price_growth"></span></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:10px; padding-left:0px;" colspan="3"><hr style="margin:0px; padding:0px;"></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:auto; padding-left:10px;" >Total Return:</td>
				<td style="vertical-align:bottom; text-align:right;"><b><span id="calc_total_return"></span></b></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:bottom; height:25px; padding-left:10px;" >ROI:</td>
				<td style="vertical-align:bottom; text-align:right;"><b><span id="calc_ROI"></span></b></td>
				<td></td>
			</tr>
			<tr>
				<td style="height:10px;" colspan="3"></td>
			</tr>
			</table>
		</div>
		<script language="JavaScript">
		function calculateTotal()
		{
			company_item = document.getElementById("calc_company");
			if ( company_item ) {
				var c_value = company_item.value;
				var share_price = parseFloat(c_value.substring(0, c_value.indexOf("|")));
				c_value = c_value.substring(c_value.indexOf("|") + 1);
				var share_growth = parseFloat(c_value.substring(0, c_value.indexOf("|")));
				var share_dividend = parseFloat(c_value.substring(c_value.indexOf("|") + 1));
				quantity_item = document.getElementById("calc_quantity");
				if ( quantity_item ) {
					var quantity = parseInt(quantity_item.value);
					period_item = document.getElementById("calc_period");
					if ( period_item ) {
						var period = parseInt(period_item.value);
						investment = share_price * quantity;
						document.getElementById("calc_investment").innerHTML = "'.DOLLAR_SIGN.'" + investment.toFixed(2);
						document.getElementById("calc_price_per_share").innerHTML = "'.DOLLAR_SIGN.'" + share_price.toFixed(2);
						total_dividend = share_dividend * quantity * period;
						document.getElementById("calc_dividend").innerHTML = "'.DOLLAR_SIGN.'" + total_dividend.toFixed(2);
						price_growth = share_price * Math.pow(share_growth, period) * quantity;
						document.getElementById("calc_price_growth").innerHTML = "'.DOLLAR_SIGN.'" + price_growth.toFixed(2);
						r = price_growth + total_dividend;
						document.getElementById("calc_total_return").innerHTML = "'.DOLLAR_SIGN.'" + r.toFixed(2);
						r = (price_growth + total_dividend) / investment * 100;
						document.getElementById("calc_ROI").innerHTML = r.toFixed(0) + "%";
					}
				}
			}
		}
		window.setInterval("calculateTotal();", 1000);
		</script>
		';
	}
	$out_str = str_replace('{$profit_calculator}', $calc_code, $out_str);
}
if ( is_integer(strpos($out_str, '{$statistics')) ) {
	$Days_online_name = 'Days online';
	$Total_accounts_name = 'Total accounts';
	$Deposited_name = 'Deposited';
	$Withdraw_name = 'Withdrew';
	$Newest_Member_name = 'Newest Member';
	$Last_deposit_name = 'Last deposit';
	
	$statistics_tag = get_text_between_tags($out_str, '{$statistics', '}');
	if ( is_integer(strpos($statistics_tag, '$Days_online_name="')) )
		$Days_online_name = get_text_between_tags($statistics_tag, '$Days_online_name="', '"');
	if ( is_integer(strpos($statistics_tag, '$Total_accounts_name="')) )
		$Total_accounts_name = get_text_between_tags($statistics_tag, '$Total_accounts_name="', '"');
	if ( is_integer(strpos($statistics_tag, '$Deposited_name="')) )
		$Deposited_name = get_text_between_tags($statistics_tag, '$Deposited_name="', '"');
	if ( is_integer(strpos($statistics_tag, '$Withdraw_name="')) )
		$Withdraw_name = get_text_between_tags($statistics_tag, '$Withdraw_name="', '"');
	if ( is_integer(strpos($statistics_tag, '$Newest_Member_name="')) )
		$Newest_Member_name = get_text_between_tags($statistics_tag, '$Newest_Member_name="', '"');
	if ( is_integer(strpos($statistics_tag, '$Last_deposit_name="')) )
		$Last_deposit_name = get_text_between_tags($statistics_tag, '$Last_deposit_name="', '"');
	$title_class = 'col-md-6';
	if ( is_integer(strpos($statistics_tag, '$title_class="')) )
		$title_class = get_text_between_tags($statistics_tag, '$title_class="', '"');
	$title_style = 'font-weight:bold; margin-top:5px; margin-bottom:5px;';
	if ( is_integer(strpos($statistics_tag, '$title_style="')) )
		$title_style = get_text_between_tags($statistics_tag, '$title_style="', '"');
	$data_class = 'col-md-6';
	if ( is_integer(strpos($statistics_tag, '$data_class="')) )
		$data_class = get_text_between_tags($statistics_tag, '$data_class="', '"');
	$data_style = '';
	if ( is_integer(strpos($statistics_tag, '$data_style="')) )
		$data_style = get_text_between_tags($statistics_tag, '$data_style="', '"');
	$statistics_file = DIR_WS_TEMP.'$$$site_statistics.txt';
	if ( !file_exists($statistics_file) || time() - filemtime($statistics_file) > 60 * 60 /*|| defined('DEBUG_MODE')*/) {
		$row = get_api_value('get_site_stats');
		if ( $row ) {
			$total_deposited = $user_account->get_site_purchases(730);
			$statistics = '	
			<div class="row">
				<div class="'.$title_class.'" style="'.$title_style.'">
					'.$Days_online_name.'
				</div>
				<div class="'.$data_class.'" style="'.$data_style.'">
					'.number_format($row['days_online'], 0).' ('.get_interval($row['days_online'] * 60 * 60 * 24 ).')
				</div>
			</div>
			'.($row['total_accounts'] > 1000?
				'<div class="row">
					<div class="'.$title_class.'" style="'.$title_style.'">
						'.$Total_accounts_name.'
					</div>
					<div class="'.$data_class.'" style="'.$data_style.'">
						'.number_format($row['total_accounts'], 0).'
					</div>
				</div>'
				:''
			).'
			'.($total_deposited > 5000?'
				<div class="row">
					<div class="'.$title_class.'" style="'.$title_style.'">
						'.$Deposited_name.'
					</div>
					<div class="'.$data_class.'" style="'.$data_style.'">
						'.currency_format($total_deposited).'
					</div>
				</div>'
				:''
			).'
			'.($row['total_withdraw'] > 1000?'
				<div class="row">
					<div class="'.$title_class.'" style="'.$title_style.'">
						'.$Withdraw_name.'
					</div>
					<div class="'.$data_class.'" style="'.$data_style.'">
						'.currency_format($row['total_withdraw']).'
					</div>
				</div>'
				:''
			).'
			<div class="row">
				<div class="'.$title_class.'" style="'.$title_style.'">
					'.$Newest_Member_name.'
				</div>
				<div class="'.$data_class.'" style="'.$data_style.'">
					'.$row['newest_member'].'
				</div>
			</div>
			<div class="row">
				<div class="'.$title_class.'" style="'.$title_style.'">
					'.$Last_deposit_name.'
				</div>
				<div class="'.$data_class.'" style="'.$data_style.'">
					'.currency_format($row['last_deposit']).' (<a href="/acc_wall.php?userid='.$row['userid'].'">'.ucfirst(strtolower($row['firstname'])).'</a>)
				</div>
			</div>
			';
			//if ( !defined('DEBUG_MODE') ) 
				file_put_contents($statistics_file, $statistics);
		}
	}
	$statistics = file_get_contents($statistics_file);
	delete_text_between_tags($out_str, '{$statistics', '}', false, $statistics);
}
if ( is_integer(strpos($out_str, '{$top_shares')) ) {
	$_1_col_name = WORD_MEANING_SHARE;
	$_2_col_name = 'Price';
	$_3_col_name = 'Growth';
	$NumberOfRows = 15;
	$top_shares_tag = get_text_between_tags($out_str, '{$top_shares', '}');
	if ( is_integer(strpos($top_shares_tag, '$1colName="')) )
		$_1_col_name = get_text_between_tags($top_shares_tag, '$1colName="', '"');
	if ( is_integer(strpos($top_shares_tag, '$2colName="')) )
		$_2_col_name = get_text_between_tags($top_shares_tag, '$2colName="', '"');
	if ( is_integer(strpos($top_shares_tag, '$3colName="')) )
		$_3_col_name = get_text_between_tags($top_shares_tag, '$3colName="', '"');
	if ( is_integer(strpos($top_shares_tag, '$NumberOfRows="')) )
		$NumberOfRows = get_text_between_tags($top_shares_tag, '$NumberOfRows="', '"');

	$top_shares_file = DIR_WS_TEMP.'$$$site_top_shares.txt';

	if ( !file_exists($top_shares_file) || time() - filemtime($top_shares_file) > 60 * 60 /*|| defined('DEBUG_MODE')*/) {
		$s = '';
		$j = 0;
		$tmp_stock = new Stock();
		$stocks = $tmp_stock->get_list('top_shares', 'top_shares', $NumberOfRows);
		if ( $stocks ) {
			foreach ( $stocks as $stock ) {
				if ( $j == 0 )
					$top_stockid = $stock->stockid;
				if ( $stock->stat_price_30_day_ago > 0 )
					$stock->stat_last_30_day_trend = $stock->stat_current_price / $stock->stat_price_30_day_ago;
				else
					$stock->stat_last_30_day_trend = 1.1;

				$s = $s.'
					<tr style="'.($j % 2 == 0?'background-color:#'.COLOR1LIGHT.';':'').' ">
						<td style="padding:0px; padding-right:2px;"><img class="user_image_on_share" style="width:40px; height:40px; " src="'.$stock->get_user_image_url().'" border="0" alt="'.$stock->name.'" ></td>
						<td style="vertical-align:middle; padding:2px; padding-right:10px; text-transform:capitalize; "><a href="/exch_share_info.php?stockid='.$stock->stockid.'">'.(strlen($stock->name) > 30?substr($stock->name, 0, 30).'...':$stock->name).'</a></td>
						<td style="vertical-align:middle; padding:2px; text-align:right;">'.currency_format($stock->stat_current_price).'</td>
						<td style="vertical-align:middle; padding:2px; padding-left:10px; text-align:right;"><span class="exchange_green">'.number_format(($stock->stat_last_30_day_trend - 1) * 100, 0).'%</span></td>
						<td style="vertical-align:middle; padding:2px; "><img src="/'.DIR_WS_WEBSITE_IMAGES_DIR.'arrow_up.png" border="0" alt="" width="16" height="14"></td>
					</tr>
					';
				$j++;
			}
			$s = '
			<a href="/exch_share_info.php?stockid='.$top_stockid.'"><img src="/'.DIR_WS_TEMP_NAME.$top_stockid.'_last_30_day_320x110.png" border="0" alt="" class="first_page_image " style="width:320px; height:110px;"></a><br>
			<br>
			<div id = "table_div">
			<table class="table" style="" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign="top" style="text-transform:uppercase; font-weight:bold;"></td>
				<td valign="top" style="text-transform:uppercase; font-weight:bold;">'.$_1_col_name.'</td>
				<td valign="top" style="text-transform:uppercase; font-weight:bold; text-align:right; padding-left:10px;">'.$_2_col_name.'</td>
				<td valign="top" style="text-transform:uppercase; font-weight:bold; text-align:right; padding-left:10px;">'.$_3_col_name.'</td>
				<td valign="top" style="text-transform:uppercase; font-weight:bold;"></td>
			</tr>
			'.$s.'
			</table>
			</div>';
			if ( /*!defined('DEBUG_MODE') &&*/ count($stocks) > 0 ) 
				file_put_contents($top_shares_file, $s);
		}
	}
	//if ( !defined('DEBUG_MODE') ) 
		$s = file_get_contents($top_shares_file);
	delete_text_between_tags($out_str, '{$top_shares', '}', false, $s);
}
if ( is_integer(strpos($out_str, '{$contest}')) ) {
	$s = file_get_contents(PREVIOUS_CONTEST_WINNERS_FILE);
	if ( empty($s) )
		$s = file_get_contents(CONTEST_USERS_FILE);
	$out_str = str_replace('{$contest}', $s, $out_str);
	require_once(DIR_COMMON_PHP.'box_show_frame.php');
}

if ( is_integer(strpos($out_str, '{$links_to_articles')) ) {
	$links_to_articles_tag = get_text_between_tags($out_str, '{$links_to_articles', '}');
	$number_of_links = 5;
	if ( is_integer(strpos($links_to_articles_tag, '$number_of_links="')) )
		$number_of_links = get_text_between_tags($links_to_articles_tag, '$number_of_links="', '"');
	$links_to_articles_file = DIR_WS_TEMP.'$$$links_to_articles.txt';
	if ( !file_exists($links_to_articles_file) || time() - filemtime($links_to_articles_file) > 60 * 60 /*|| defined('DEBUG_MODE')*/) {
		$out_text = get_api_value('get_links_to_articles', '', array('number_of_links' => $number_of_links));
		//if ( !defined('DEBUG_MODE') ) 
			file_put_contents($links_to_articles_file, $out_text);
	}
	else
		$out_text = file_get_contents($links_to_articles_file);
	delete_text_between_tags($out_str, '{$links_to_articles', '}', false, $out_text);
}
if ( is_integer(strpos($out_str, '{$shares_grid')) ) {
	$shares_grid_number = 1;
	while ( is_integer(strpos($out_str, '{$shares_grid')) ) {
		$shares_grid_tag = get_text_between_tags($out_str, '{$shares_grid', '}');
		$number_of_columns = 4;
		if ( is_integer(strpos($shares_grid_tag, '$number_of_columns="')) )
			$number_of_columns = get_text_between_tags($shares_grid_tag, '$number_of_columns="', '"');
		$number_of_rows = 1;
		if ( is_integer(strpos($shares_grid_tag, '$number_of_rows="')) )
			$number_of_rows = get_text_between_tags($shares_grid_tag, '$number_of_rows="', '"');
		$item_class = '';
		if ( is_integer(strpos($shares_grid_tag, '$item_class="')) )
			$item_class = get_text_between_tags($shares_grid_tag, '$item_class="', '"');
		$shares_grid_sort = 'ROI';
		if ( is_integer(strpos($shares_grid_tag, '$sort="')) )
			$shares_grid_sort = get_text_between_tags($shares_grid_tag, '$sort="', '"');
		$shares_grid_condition = 'only_rated_shares';
		if ( is_integer(strpos($shares_grid_tag, '$condition="')) )
			$shares_grid_condition = get_text_between_tags($shares_grid_tag, '$condition="', '"');
		$shares_grid_credit_rating = '';
		if ( is_integer(strpos($shares_grid_tag, '$credit_rating="')) )
			$shares_grid_credit_rating = get_text_between_tags($shares_grid_tag, '$credit_rating="', '"');
		$shares_grid = '';
		$grid_columns = round(12 / $number_of_columns);
		$shares_grid_file = DIR_WS_TEMP.'$$$shares_grid'.$shares_grid_number.'.txt';
		if ( !file_exists($shares_grid_file) || time() - filemtime($shares_grid_file) > 60 * 2 || defined('DEBUG_MODE') ) {
			$stock = new Stock();
			$shares_grid = $stock->generate_shares_grid($number_of_columns, $number_of_rows, $item_class, $shares_grid_sort, $shares_grid_condition, $shares_grid_credit_rating);
			if ( $shares_grid ) {
				//if ( !defined('DEBUG_MODE') ) 
					file_put_contents($shares_grid_file, $shares_grid);
			}
		}
		//if ( !defined('DEBUG_MODE') )
			$shares_grid = file_get_contents($shares_grid_file);
		delete_text_between_tags($out_str, '{$shares_grid', '}', false, $shares_grid);
		$shares_grid_number++;
	}
}
if ( is_integer(strpos($out_str, '{$currency_exchange')) ) {
	//if ( is_file_variable_expired('currency_exchange_on_first_page', 10) ) {
		$code_str = '';
		
		$crypto = new Bitcoin();
		$exch_rate_history_file = $crypto->crypto_symbol.'_exch_rate_history';
		$exch_rate_arr_tmp = json_decode(get_file_variable($exch_rate_history_file), 1);
		$exch_rate_arr = [];
		for ($i = count($exch_rate_arr_tmp) - 0; $i >= 0; $i--) {
			if ( count($exch_rate_arr) < 24 && $exch_rate_arr_tmp[$i] )
				array_unshift($exch_rate_arr, $exch_rate_arr_tmp[$i]);
		}
		$current_rate_scaled = $exch_rate_arr[count($exch_rate_arr) - 1]['rate'];

		$line_code = '';
		$prefix = DOLLAR_SIGN;
		$minVal = 0;
		$maxVal = 0;
		foreach ($exch_rate_arr as $row) {
			if ($minVal > $row['rate'] || empty($minVal))
				$minVal = $row['rate'];
			if ($maxVal < $row['rate'])
				$maxVal = $row['rate'];
			$line_code.= (!empty($line_code)?', ':'').'
				{x: new Date('.(empty($row['time'])?0:$row['time'] * 1000).'), y:'.(empty($row['rate'])?0:round($row['rate'], 2)).'}
			';
		}
		$minVal = floor($minVal * 0.95 / 100) * 100;
		$maxVal = floor($maxVal * 1.05 / 100) * 100;
		
		$code_str = $code_str.'
			<div class="row" style="">
				<div class="col-md-7" style="">
					<style type="text/css">
					@font-face {
						font-family: whitrabt;
						src: url("/'.DIR_WS_IMAGES_DIR.'whitrabt.woff") format("woff");
						font-weight: normal;
						font-style: normal;
					}
					
					.tick_strap{font-size:26px;}
					.tick_strap_description:first-letter{color:#ffff33;}
					.tick_strap_description{font-size: 20px; color:#ffff33; font-style:normal; text-transform:capitalize;}
					.tick_strap{padding-top:6px; padding-bottom:0px; margin-bottom:0px;}
					.tick_strap:first-letter{font-family:"Arial"; color:#ffffff; font-style:normal;}
					.tick_strap_row{
						background-color:transparent;
						padding:8px;  
						margin:0px;
						height:auto;
						border:none;
						-moz-box-shadow:none;
						-webkit-box-shadow:none;
						color:#B5D554; 
						font-family:"whitrabt"; 
						font-weight:normal; 
						font-style:normal; 
						text-shadow:1px 1px 1px #000000;
						text-transform:none;
					}
					</style>
					<div class="row tick_strap_row" style="">
						<div class="col-md-6" style="text-align:center;">
							<span class="tick_strap_description">'.$crypto->crypto_name.' rate:</span>
							<p class="tick_strap" id="last" style="color:#'.($current_rate_scaled > $exch_rate_arr[0]['rate']?'00ff00':'ff0000').';">
								<span class="glyphicon glyphicon-triangle-'.($current_rate_scaled > $exch_rate_arr[0]['rate']?'top':'bottom').'" aria-hidden="true" style="color:#'.($current_rate_scaled > $exch_rate_arr[0]['rate']?'00ff00':'ff0000').'; font-size:14px; top:-4px;"></span>
								'.currency_format($current_rate_scaled).'
							</p>
						</div>
						<div class="col-md-6" style="text-align:center;">
							<span class="tick_strap_description">24 hours ago:</span>
							<p class="tick_strap"><span id="since_24_hour">'.currency_format($exch_rate_arr[0]['rate']).'</span></p>
						</div>
					</div>
					<div class="row tick_strap_row" style="">
						<div class="col-md-6" style="text-align:center;">
							<button id="sell_btn" class="btn btn-success btn-lg" style="min-width:200px; box-shadow:1px 1px 4px #202020; text-transform:capitalize;" onclick="window.location=\'/login\'; return false;">Sell <span class="currency1_description">'.$crypto->crypto_name.'s</span>...</button>
						</div>
						<div class="col-md-6" style="text-align:center;">
							<button id="buy_btn" class="btn btn-warning btn-lg" style="min-width:200px; box-shadow: 1px 1px 4px #202020; text-transform:capitalize;" onclick="window.location=\'/login\'; return false;">Buy <span class="currency1_description">'.$crypto->crypto_name.'s</span>...</button>
						</div>
					</div>
				</div>
				<div class="col-md-5" style="background-color:transparent; padding-left: 0;">
					<div class="col-md-6" style="height:200px; width:100%;" id="exch_rate_history"></div>
				</div>
			</div>
			<script type="text/javascript" src="/javascript/canvasjs/source/canvasjs.js"></script>
			<script type="text/javascript">
			var exchange_graph_currency1 = "'.strtolower($crypto->crypto_symbol).'";
			var exchange_graph_currency2 = "usd";
			var cur1_description = "'.ucfirst(strtolower($crypto->crypto_name)).'s";
			var cur2_description = "US Dollars";
			var currency1_digits = 4;
			var currency2_digits = 2;
			var cur1_symbol = htmlDecode("'.$crypto->symbol.'");
			var cur2_symbol = htmlDecode("$");

			$("#exch_rate_history").width( $("#exch_rate_history").width() - 30 );
			var graph_js = new CanvasJS.Chart("exch_rate_history",
				{
					theme: "theme1",
					backgroundColor: "transparent",
					title:{
						fontSize: 12,
						text: "",
					},
					axisY: {
						interval: 250,
						minimum: '.$minVal.', 
						maximum: '.$maxVal.', 
						titleFontSize: 14,
						lineThickness: 1,
						lineColor: "#b5d554",
						includeZero: false,
						prefix: "'.$prefix.'",
						gridColor: "#007000",
						gridThickness: 1,     
						tickColor: "#b5d554",
						tickLength: 2,
						tickThickness: 1,
						interlacedColor: "transparent",
						labelFontColor: "#ffffff",
						labelFontSize: 10
					},
					axisX: {
						interval: 6, 
						intervalType:"hour", 
						valueFormatString:"HH:00",
						titleFontSize: 14,
						titleFontColor: "#ffffff",
						lineThickness: 1,
						lineColor: "#b5d554",
						gridColor: "#007000",
						gridThickness: 1,
						tickColor: "#b5d554",
						tickLength: 2,
						tickThickness: 1,
						labelFontColor: "#ffffff",
						labelFontSize: 10
					},
					data: [
						{type: "area",  fillOpacity: 0.2, lineThickness:2, indexLabelFontSize:12, markerType:"circle", markerSize:5, color:"#00ff00", showInLegend:false, name:"'.$crypto->crypto_name.' rate", toolTipContent:"{x}<br/> {name}, <strong>'.$prefix.'{y}</strong>", dataPoints:['.$line_code.']}
					]
				});
				graph_js.render();
			</script>
		';
		delete_text_between_tags($out_str, '{$currency_exchange', '}', false, $code_str);
	//}
}
if ( is_integer(strpos($out_str, '<this_site_is_for_sale>')) ) {
	$this_site_is_for_sale = false;
	$news = $user_account->is_news_avalable();
	if ( $news ) {
		$vars = get_text_between_tags($news['var_text1'], '<vars>', '</vars>');
		eval($vars);
		$this_site_is_for_sale = $site_for_sale == SITE_SHORTDOMAIN;
	}
	if ( $this_site_is_for_sale )
		delete_text_between_tags($out_str, '{$site_sale_price', '}', false, currency_format($price));
	else {
		delete_text_between_tags($out_str, '<this_site_is_for_sale>', '</this_site_is_for_sale>');
	}
}
if ( is_integer(strpos($out_str, '{$term_deposit')) ) {
	$term_deposit_tag = get_text_between_tags($out_str, '{$term_deposit', '}');
	$number_of_links = 5;
	if ( is_integer(strpos($term_deposit_tag, '$number_of_links="')) )
		$number_of_links = get_text_between_tags($term_deposit_tag, '$number_of_links="', '"');
	$term_deposit_file = DIR_WS_TEMP.'$$$term_deposit_on_first_page.txt';
	if ( !file_exists($term_deposit_file) || time() - filemtime($term_deposit_file) > 60 * 60 || defined('DEBUG_MODE')) {
		$bitcoin = new Bitcoin();
		$litecoin = new Litecoin();
		$out_text = '';
		$currencies = [];
		if ( !$display_in_short )
			$min_max_term_deposit = $user_account->get_min_max_term_deposit();
		else
			$min_max_term_deposit = array('usd' => ['12_months_rate' => 2], 'bitcoin' => ['12_months_rate' => 2], 'litecoin' => ['12_months_rate' => 2]);
		if ( $min_max_term_deposit['usd']['12_months_rate'] > 1 ) 
			$currencies[] = ['id' => 'usd', 'name' => 'USD', 'logo' => '/images/invest_70x70_h.png', 'symbol' => '$', 'precision' => 2, 'currency_code' => 'USD'];
		if ( $min_max_term_deposit['bitcoin']['12_months_rate'] > 1 ) 
			$currencies[] = ['id' => $bitcoin->crypto_name, 'name' => 'Bitcoin', 'logo' => '/images/bitcoin.png', 'symbol' => $bitcoin->symbol, 'precision' => 5, 'currency_code' => $bitcoin->crypto_symbol];
		if ( $min_max_term_deposit['litecoin']['12_months_rate'] > 1 ) 
			$currencies[] =['id' => $litecoin->crypto_name, 'name' => 'Litecoin', 'logo' => '/images/litecoin_100x100.png', 'symbol' => $litecoin->symbol, 'precision' => 3, 'currency_code' => $litecoin->crypto_symbol];
		$terms = [['id' => '1month', 'name' => '1 month', 'logo' => '', 'duration_in_weeks' => 4.34524], ['id' => '3months', 'name' => '3 months', 'logo' => '', 'duration_in_weeks' => 13.0357]/*, ['id' => '6months', 'name' => '6 months', 'logo' => '', 'duration_in_weeks' => 26.0715], ['id' => '1year', 'name' => '1 year', 'logo' => '', 'duration_in_weeks' => 52.1429], ['id' => 'indefinite', 'name' => 'Indefinite', 'logo' => '']*/];

		$out_text = $out_text.'
		<div style="display:inline-block;">
		';
		
		$i = 0;
		foreach ( $currencies as $value ) {
			$out_text = $out_text.'
			<table class="payment_option_table currency_table" cellspacing="0" cellpadding="0" border="0" id="currency_'.$i.'_bkg">
			<tr>
				<td style="vertical-align:top; padding-top:6px; height:10px;">
					<input type="radio" name="currency" id="currency_'.$i.'" class="" value="'.$value['id'].'" '.($i == 0?'checked':'').' onclick="td_select_whole_box(\'currency_table\', \'currency_'.$i.'\', \''.$value['symbol'].'\', undefined, \''.$value['precision'].'\'); return true;"> 
				</td>
				<td style="vertical-align:top; width:100%; cursor:pointer;" onclick="return td_select_whole_box(\'currency_table\', \'currency_'.$i.'\', \''.$value['symbol'].'\', undefined, \''.$value['precision'].'\');">
					<img src="'.(!empty($value['logo'])?$value['logo']:'/'.DIR_WS_WEBSITE_IMAGES_DIR.$value['id'].'.png').'" border="0" alt="'.$value['name'].'" title="'.$value['name'].'" style="width:32px; height:32px; margin-left:10px;">
					<span>'.$value['name'].'</span>
				</td>
			</tr>
			</table>
			';
			$i++;
		}
		$out_text = $out_text.'</div>';
		$out_text = $out_text.'
		<div style="display:flex;">
		';
		$i = 0;
		$min_max = array();
		$rates = array();
		foreach ( $terms as $value ) {
			$out_text = $out_text.'
			<table class="payment_option_table term_table" cellspacing="0" cellpadding="0" border="0" id="term_'.$i.'_bkg">
			<tr>
				<td style="vertical-align:top; padding-top:0px; height:10px;">
					<input type="radio" name="term" id="term_'.$i.'" class="" value="'.$value['id'].'" '.($i == 0?'checked':'').' onclick="td_select_whole_box(\'term_table\', \'term_'.$i.'\', undefined, '.$value['duration_in_weeks'].'); return true;"> 
				</td>
				<td style="vertical-align:top; width:100%; cursor:pointer; padding:2px 0 0 15px;" onclick="return td_select_whole_box(\'term_table\', \'term_'.$i.'\', undefined, '.$value['duration_in_weeks'].');">
					'.(!empty($value['logo'])?'<img src="'.$value['logo'].'" border="0" alt="'.$value['name'].'" title="'.$value['name'].'" style="width:32px; height:32px; margin-left:10px;">':'').'
					<span>'.$value['name'].'</span>
				</td>
			</tr>
			</table>
			';
			foreach ($currencies as $currency) {
				$min_max[$currency['id']] = $min_max[$currency['id']].(!empty($min_max[$currency['id']])?', ':$currency['id'].':{').'_'.$value['id'].'_min:"'.$min_max_term_deposit[strtolower($currency['name'])]['min'].'", _'.$value['id'].'_max:"'.($min_max_term_deposit[strtolower($currency['name'])]['max'] * $value['duration_in_weeks']).'"';
				$rates[$currency['id']] = $rates[$currency['id']].(!empty($rates[$currency['id']])?', ':$currency['id'].':{').'_'.$value['id'].':"'.$min_max_term_deposit[$currency['id']][round($value['duration_in_weeks'] / 4).'_months_rate'].'"';
			}
			$i++;
		}
		$out_text = $out_text.'
		</div>
		<h1>Interest Rate:&nbsp;&nbsp;<span style="color:#00a000;"><span id="interest_rate" class="notranslate">0</span>%</span> <span style="text-transform:none;">per annum</span></h1>
		<div class="row" style="margin:20px 0 0 0;">
			<div class="col-md-6" style="padding: 0;">
				<h2 style="padding-top:0px;">Amount of Deposit:</h2>
				<div class="inputGroupContainer" style="">
					<div class="input-group" style="width:200px;">
						<span class="input-group-addon"><span aria-hidden="true" class="receive_symbol">$</span></span>
						<input type="number" step="any" autocomplete="off" name="amount" id="amount" value="" class="form-control control_to_validate" placeholder="0.00" required="required" _validate_message="enter Amount of Deposit">
					</div>
					<span class="glyphicon form-control-feedback"></span>
					'.show_help('Minimum deposit <span class="receive_symbol">$</span><span class="min_deposit">0</span>, maximum deposit <span class="receive_symbol">$</span><span class="max_deposit">0</span>', '').'
				</div>
			</div>
			<div class="col-md-6" style="padding: 20px 0 0 0;">
				<h1>You receive:&nbsp;&nbsp;<span style="color:#00a000;"><span class="receive_symbol">$</span><span id="you_receive">0</span></span></h1>
				'.show_help('You will receive <span class="duration_in_weeks">0</span> weekly payments by <span class="receive_symbol">$</span><span id="you_receive_weekly">0</span>', '').'
			</div>
		</div>
		<p style="text-align:center; margin-top:20px;">
			<button class="btn btn-success btn-lg" onclick="window.location=\'/login\'; return false;">
				<div style="position:relative; width:100%; height:0px; z-index:10000;">
					<div style="position:absolute; width:100%; height:0px; text-align:center;">
						<img src="/images/wait_big3.gif" border="0" style="width:20px; height:20px; display:none;" id="form_submitted_btn_wait">
					</div>
				</div>
				<span id="form_submitted_btn_text">Make Deposit...</span>
			</button>
		</p>
		';
		$var_min_max = '';
		foreach($min_max as $min_max_item) {
			$var_min_max = $var_min_max.(!empty($var_min_max)?', 
			':'').$min_max_item.'}';
		}
		$var_rates = '';
		foreach($rates as $rates_item) {
			$var_rates = $var_rates.(!empty($var_rates)?', 
			':'').$rates_item.'}';
		}
		$out_text = $out_text.'
		<script type="text/javascript">
		var rates = {
			'.$var_rates.'
		};
		var min_max = {
			'.$var_min_max.'
			};
		var duration_in_weeks = '.$terms[0]['duration_in_weeks'].';
		var currency_precision = 2;
		
		var cur_interest_rate = 0;
		function td_select_whole_box(table_class, method_id, currency_symbol, a_duration_in_weeks, precision)
		{
			if ( typeof currency_symbol != "undefined" ) {
				$(".receive_symbol").html(currency_symbol);
			}
			if ( typeof a_duration_in_weeks != "undefined" ) {
				duration_in_weeks = a_duration_in_weeks;
			}
			if ( typeof precision != "undefined" )
				currency_precision = precision;
			
			$("#" + method_id).prop("checked", true);
			$("." + table_class).removeClass("payment_option_table_selected");
			$("#" + method_id + "_bkg").addClass("payment_option_table_selected");
			
			cur_interest_rate = (rates[$("input[name=\'currency\']:checked").val()]["_" + $("input[name=\'term\']:checked").val()] * 100 * '.DEPOSIT_BALANCE_MULTIPLIC.').toFixed(0);
			$("#interest_rate").html( cur_interest_rate - 100 );
			
			var cur = $("input[name=\'currency\']:checked").val();
			
			var trm = "_" + $("input[name=\'term\']:checked").val() + "_min";
			var min_deposit = min_max[cur][trm];
			if (duration_in_weeks)
				min_deposit = min_deposit * duration_in_weeks / 4.34524;

			var max_deposit = min_max[$("input[name=\'currency\']:checked").val()]["_" + $("input[name=\'term\']:checked").val() + "_max"];

			if (min_deposit > max_deposit)
				min_deposit = max_deposit * 0.9;
			$(".min_deposit").html(parseFloat(min_deposit).toFixed(currency_precision));
			$(".max_deposit").html(parseFloat(max_deposit).toFixed(currency_precision));

			$("#amount").attr( "min", min_deposit );
			$("#amount").attr( "max", max_deposit );
			$("#amount").attr("_validate_message", "enter Amount of Deposit between "+currency_format(min_deposit, $(".receive_symbol").html())+" and "+currency_format(max_deposit, $(".receive_symbol").html()));
				
			$(".duration_in_weeks").html( Math.round(duration_in_weeks) );
			
			if (typeof TD_calculateTotal === "function")
				TD_calculateTotal();
			return false;
		}

		function TD_calculateTotal()
		{
			tmp_interest_rate = 1 + ( (parseFloat(cur_interest_rate) / 100 - 1) * duration_in_weeks / 52.1429 );
			var receive = $("#amount").val()?parseFloat($("#amount").val()):0;
			receive = receive * tmp_interest_rate;
			$("#you_receive").html( receive.toFixed(currency_precision) );
			$("#you_receive_weekly").html( (receive / Math.round(duration_in_weeks) ).toFixed(currency_precision) );
		}

		$( document ).ready(function() {
			$(".control_to_validate").on("click mouseenter mouseleave mousedown mouseover active keyup ", function (e) {
				TD_calculateTotal();	
			});
			td_select_whole_box("currency_table", "currency_0", "$");
			td_select_whole_box("term_table", "term_0");
		});
		</script>
		';
		file_put_contents($term_deposit_file, $out_text);
	}
	else
		$out_text = file_get_contents($term_deposit_file);
	delete_text_between_tags($out_str, '{$term_deposit', '}', false, $out_text);
}

if ( is_integer(strpos($out_str, '{$include_php')) ) {
	$php_tag = get_text_between_tags($out_str, '{$include_php', '}');
	$php_file = get_text_between_tags($php_tag, '$file="', '"');
	delete_text_between_tags($out_str, '{$include_php', '}', false);
	include_once(DIR_WS_TEMP_CUSTOM_CODE.$php_file);
}

if ( is_integer(strpos($out_str, '{$include_html')) ) {
	while ( is_integer(strpos($out_str, '{$include_html')) ) {
		$html_text = '';
		$html_tag = get_text_between_tags($out_str, '{$include_html', '}');
		$html_file = get_text_between_tags($html_tag, '$file="', '"');
		if (file_exists(DIR_WS_TEMP_CUSTOM_CODE.$html_file)) {
			$html_text = file_get_contents(DIR_WS_TEMP_CUSTOM_CODE.$html_file);
			if ( !is_integer(strpos($html_tag, '$notranslate="yes"')) ) {
				$html_text = translate_str($html_text);
			}
		}
		else {
			$html_text = "<p style='color:#a00000'>file: $html_file does not exists</p>";
		}
		delete_text_between_tags($out_str, '{$include_html', '}', false, $html_text);
	}
}

echo $out_str;
		
?>