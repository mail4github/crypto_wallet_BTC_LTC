<?php
echo '
<style type="text/css">
.box_type1 .row,
.box_type2 .row,
.box_type3 .row{padding:4px;}
#right_align{text-align:right;}
</style>

<!-- Profit calculator Popup -->
'.generate_popup_code(
'profit_calc_popup', // popup_name
'
<div class="box_type1">
	<div class="row">
		<div class="col-xs-4" style="vertical-align:middle;">
			Number of '.WORD_MEANING_SHARE.'s:<br>
			<span class="description">(<span id="calc_price_per_share"></span>) per '.WORD_MEANING_SHARE.'</span>
		</div>
		<div class="col-xs-8" style="vertical-align:middle;">
			'.show_incremented_value('calc_quantity', 'calc_quantity', '1', 160, '', '', '', '', 'any', 'calculate_profit_calculator();', 'calculate_profit_calculator();', 'calculate_profit_calculator();').'
		</div>
	</div>
	<div class="row">
		<div class="col-xs-4" style="vertical-align:middle;">Period:</div>
		<div class="col-xs-8" style="vertical-align:middle;">
			<select class="form-control input-sm" style="width:150px;" id="calc_period" onchange="calculate_profit_calculator();">
				<option value="10">10 Days</option>
				<option value="14">2 Weeks</option>
				<option value="30" selected>1 Month</option>
				<option value="60">2 Month</option>
				<option value="182">6 Month</option>
				<option value="365">1 Year</option>
			</select>
		</div>
	</div>
</div>
<div class="box_type3">
	<div class="row">
		<div class="col-xs-4" style="text-transform:capitalize;">Investment:</div><div class="col-xs-3"><div id="right_align"><b><span id="calc_investment"></span></b></div></div><div class="col-xs-5"></div>
	</div>
	<div class="row">
		<div class="col-xs-4" style="text-transform:capitalize;">'.WORD_MEANING_DIVIDEND.':</div><div class="col-xs-3"><div id="right_align"><b><span id="calc_dividend"></span></b></div></div><div class="col-xs-5"></div>
	</div>
	<div class="row">
		<div class="col-xs-4" style="text-transform:capitalize;">Price Growth:</div><div class="col-xs-3"><div id="right_align"><b><span id="calc_price_growth"></span></b></div></div><div class="col-xs-5"></div>
	</div>
</div>
<div class="box_type2">
	<div class="row">
		<div class="col-xs-4" style="text-transform:capitalize;">Total Return:</div><div class="col-xs-3"><div id="right_align"><b><span id="calc_total_return"></span></b></div></div><div class="col-xs-5"></div>
	</div>
	<div class="row">
		<div class="col-xs-4" style="text-transform:capitalize;">ROI:</div><div class="col-xs-3"><div id="right_align"><b><span id="calc_ROI"></span></b></div></div><div class="col-xs-5"></div>
	</div>
</div>
<div class="row alert alert-warning" style="margin:0 0 4px 0;">
	<div class="col-xs-2">
		<span class="glyphicon glyphicon-info-sign" aria-hidden="true" style="font-size:30px; "></span>
	</div>
	<div class="col-xs-10">
		These calculations are for reference purposes only. All figures are estimates only and are not guaranteed any profit.
	</div>
</div>
', // popup_body
'', // yes_js
'', // title
'', // button_yes_caption
'btn-info', // button_cancel_class
'Close' // button_cancel_caption
).'

<script language="JavaScript">
var current_calc_data = "";

function show_profit_calculator(element, calc_data, company_name)
{
	current_calc_data = calc_data;
	show_profit_calc_popup("Calculate estimated profit of " + company_name);
	calculate_profit_calculator();
	return false;
}

function hide_profit_calculator()
{
	$("#profit_calc_popup").hide();
	return false;
}

function calculate_profit_calculator()
{
	var c_value = current_calc_data;
	var share_price = parseFloat(c_value.substring(0, c_value.indexOf("|")));
	c_value = c_value.substring(c_value.indexOf("|") + 1);
	var share_growth = parseFloat(c_value.substring(0, c_value.indexOf("|")));
	var share_dividend = parseFloat(c_value.substring(c_value.indexOf("|") + 1));
	var quantity = parseInt($("#calc_quantity").val());
	var period = parseInt($("#calc_period").val());
	investment = share_price * quantity;
	$("#calc_investment").html( currency_format(investment, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	$("#calc_price_per_share").html( currency_format(share_price, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	total_dividend = share_dividend * quantity * period;
	if (total_dividend > 1000)
		$("#calc_dividend").html( "> " + currency_format(1000, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	else
		$("#calc_dividend").html( currency_format(total_dividend, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	price_growth = share_price * Math.pow(share_growth, period) * quantity;
	if (price_growth > 1000)
		$("#calc_price_growth").html( "> " + currency_format(1000, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	else
		$("#calc_price_growth").html( currency_format(price_growth, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	r = price_growth + total_dividend;
	if (r > 1000)
		$("#calc_total_return").html( "> " + currency_format(1000, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	else
		$("#calc_total_return").html( currency_format(r, "'.DOLLAR_SIGN.'", undefined, undefined, "'.DOLLAR_SIGN_POSITION.'") );
	r = ((price_growth + total_dividend) / investment - 1) * 100;
	if (r > 1000)
		$("#calc_ROI").html( "> 1000%" );
	else
		$("#calc_ROI").html( r.toFixed(0) + "%" );
}

</script>
';
?>