<script type="text/javascript" src="/javascript/canvasjs/source/canvasjs.js"></script>
<?php
require_once(DIR_WS_INCLUDES.'box_edit_item.php');

function get_add_line_btn_code($section, $tag, $edit_stage = false, $number_of_columns = 4)
{
	$add_td = '';
	for ($i = 0; $i < $number_of_columns - 1; $i++)
		$add_td = $add_td.'<td></td>';
	return $edit_stage?'<tr><td style="width:100%;"><button class="btn btn-success btn-xs" style="margin:0;" onclick="show_edit_item_box(\''.$section.'\', string_to_hex32(\'Name of new '.$tag.':\'), \'\', \'\', \'min_len=2&no_chars=<;\', line_added); return false;"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> Add Line...</button></td>'.$add_td.'</tr>':'';
}

function get_business_plan_code($business_plan_xml, $edit_stage = false)
{
	return '
	<style type="text/css">
		.currency_td{text-align: right;}
		.info td,
		.success td{font-weight:bold;}
		.currency_td .btn-danger{position:relative; left:6px; top:-2px;}
		'.($edit_stage?'
		
		':'').'
	</style>
	<h2>Start-up Summary</h2>
	<table class="table table-striped">
	<thead><tr class="success"><td>Start-up Expenses</td><td>&nbsp;</td></tr></thead>
	<tbody id="bp_startup_expenses_rows"></tbody>
	'.get_add_line_btn_code('start_up_expenses', 'Start-up Expense', $edit_stage, 2).'
	<tr class="warning"><td>Total Start-up Expenses</td><td class="currency_td" id="total_startup_expenses"></td></tr>
	<tr class="success"><td>Start-up Assets</td><td>&nbsp;</td></tr>
	<tbody id="bp_startup_assets_rows"></tbody>
	'.get_add_line_btn_code('start_up_assets', 'Start-up Asset', $edit_stage, 2).'
	<tr class="warning"><td>Total Start-up Assets</td><td class="currency_td" id="total_startup_assets"></td></tr>
	</table>
	<h2>Sales Forecast</h2>
	<table class="table table-striped">
		<tr class="success"><td></td><td class="currency_td">Year 1</td><td class="currency_td">Year 2</td><td class="currency_td">Year 3</td></tr>
		<tbody id="bp_sales_rows"></tbody>
		'.get_add_line_btn_code('sales', 'Sale', $edit_stage).'
		<tr class="warning"><td>Total Sales</td><td class="currency_td" id="total_sales_y1"></td><td class="currency_td" id="total_sales_y2"></td><td class="currency_td" id="total_sales_y3"></td></tr>
	</table>
	<h2>Personnel Plan</h2>
	<table class="table table-striped">
		<tr class="success"><td></td><td class="currency_td">Year 1</td><td class="currency_td">Year 2</td><td class="currency_td">Year 3</td></tr>
		<tr class="success"><td>General and Administrative Personnel</td><td></td><td></td><td></td></tr>
		<tbody id="bp_administrative_personnel_rows"></tbody>
		'.get_add_line_btn_code('administrative_personnel', 'Administrative Position', $edit_stage).'
		<tr class="success"><td>Production Personnel</td><td></td><td></td><td></td></tr>
		<tbody id="bp_production_personnel_rows"></tbody>
		'.get_add_line_btn_code('production_personnel', 'Production Position', $edit_stage).'
		<tr class="success"><td>Sales and Marketing Personnel</td><td></td><td></td><td></td></tr>
		<tbody id="bp_marketing_personnel_rows"></tbody>
		'.get_add_line_btn_code('marketing_personnel', 'Marketing Position', $edit_stage).'
		<tr class="warning"><td>Total Payroll</td><td class="currency_td" id="total_payroll_y1"></td><td class="currency_td" id="total_payroll_y2"></td><td class="currency_td" id="total_payroll_y3"></td></tr>
	</table>
	<h2>Financial Plan</h2>
	<table class="table table-striped">
		<tr class="success"><td></td><td class="currency_td">Year 1</td><td class="currency_td">Year 2</td><td class="currency_td">Year 3</td></tr>
		<tr class="success"><td>Profit and Loss</td><td></td><td></td><td></td></tr>
		<tr><td>Sales</td><td class="currency_td" id="_total_sales_y1"></td><td class="currency_td" id="_total_sales_y2"></td><td class="currency_td" id="_total_sales_y3"></td></tr>
		<tr><td>Production Payroll</td><td class="currency_td" id="_total_payroll_y1"></td><td class="currency_td" id="_total_payroll_y2"></td><td class="currency_td" id="_total_payroll_y3"></td></tr>
		<tr class="success"><td>Sales and Marketing Expenses</td><td></td><td></td><td></td></tr>
		<tr><td>Sales and Marketing Payroll</td><td class="currency_td" id="marketing_payroll_y1"></td><td class="currency_td" id="marketing_payroll_y2"></td><td class="currency_td" id="marketing_payroll_y3"></td></tr>
		<tbody id="bp_marketing_expenses_rows"></tbody>
		'.get_add_line_btn_code('marketing_expenses', 'Sales or Marketing Expense', $edit_stage).'
		<tr class="warning"><td>Total sales and marketing expenses</td><td class="currency_td" id="marketing_expenses_y1"></td><td class="currency_td" id="marketing_expenses_y2"></td><td class="currency_td" id="marketing_expenses_y3"></td></tr>
		<tr class="success"><td>General and Administrative Expenses</td><td></td><td></td><td></td></tr>
		<tr><td>General and Administrative Payroll</td><td class="currency_td" id="administrative_payroll_y1"></td><td class="currency_td" id="administrative_payroll_y2"></td><td class="currency_td" id="administrative_payroll_y3"></td></tr>
		<tbody id="bp_administrative_expenses_rows"></tbody>
		'.get_add_line_btn_code('administrative_expenses', 'Administrative Expense', $edit_stage).'
		<tr class="warning"><td>Total general and administrative expenses</td><td class="currency_td" id="administrative_expenses_y1"></td><td class="currency_td" id="administrative_expenses_y2"></td><td class="currency_td" id="administrative_expenses_y3"></td></tr>
		<tr class="success"><td>Other Expenses</td><td></td><td></td><td></td></tr>
		<tbody id="bp_other_expenses_rows"></tbody>
		'.get_add_line_btn_code('other_expenses', 'Other Expense', $edit_stage).'
		<tr class="warning"><td>Total other expenses</td><td class="currency_td" id="other_expenses_y1"></td><td class="currency_td" id="other_expenses_y2"></td><td class="currency_td" id="other_expenses_y3"></td></tr>

		<tr class="warning"><td>Total Operating Expenses</td><td class="currency_td" id="total_operating_expenses_y1"></td><td class="currency_td" id="total_operating_expenses_y2"></td><td class="currency_td" id="total_operating_expenses_y3"></td></tr>
		
		<tr class="info"><td>Net Profit</td><td class="currency_td" id="net_profit_y1" id="net_profit_y1"></td><td class="currency_td" id="net_profit_y2"></td><td class="currency_td" id="net_profit_y3"></td></tr>
	</table>
	<div id="profit_chart_container" style="width:100%;height:400px;margin:0px;"></div>
	
	<script type="text/javascript">
		function get_remove_line_button(section, tag_text)
		{
			return '.($edit_stage?
				'\'<div style="position:relative; width:100%; height:0;"><div style="position:absolute; width:0; height:0; right:0;"><button class="btn btn-danger btn-xs" onclick="remove_line_from_business_plan(\\\'\' + section + \'\\\', \\\'\' + tag_text + \'\\\'); return false;" title="Remove line"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div></div>\''
				:'""'
			).'
			;
		}
		function get_business_plan_edit_item_code(section, item, value_name, input_type)
		{
			return '.($edit_stage?
				'\'bp_section="\' + section + \'" bp_item="\' + item + \'" bp_value_name="\' + value_name + \'" onclick="show_edit_item_box(this.id, \\\''.bin2hex('').'\\\', undefined, \\\''.bin2hex('').'\\\', \\\'min_len=1&no_chars=<;\\\', change_line_in_business_plan, undefined, \\\'\' + input_type + \'\\\');"\''
				:'""'
			).'
			;
		}
		function get_business_plan_edit_item_class()
		{
			return '.($edit_stage?
				'"editable_item"'
				:'""'
			).'
			;
		}
		'.($edit_stage?'
			function add_line_to_business_plan(section, id, tag) {
				if ( !$(xml).find("smart_contract") ) {
					$(xml).append($("<smart_contract></smart_contract>"));
				}
				if ( !$(xml).find("smart_contract").find("business_plan") || !$(xml).find("smart_contract").find("business_plan")[0] ) {
					$(xml).find("smart_contract").append($("<business_plan><start_up_expenses></start_up_expenses><start_up_assets></start_up_assets><sales></sales><administrative_personnel></administrative_personnel><production_personnel></production_personnel><marketing_personnel></marketing_personnel><marketing_expenses></marketing_expenses><administrative_expenses></administrative_expenses><other_expenses></other_expenses></business_plan>"));
				}
				sect = $(xml).find("smart_contract").find("business_plan").find(section);
				if ( !sect || !sect.length )
					$(xml).find("smart_contract").find("business_plan").append($("<"+section+"></"+section+">"));
				$(xml).find("smart_contract").find("business_plan").find(section).append($("<i" + id + "><tag>" + tag + "</tag><value>0</value><year_1>0</year_1><year_2>0</year_2><year_3>0</year_3></i" + id + ">"));
			}

			function line_added(tag, section)
			{
				//var id = tag.replace(" ", "_");
				var id = md5(tag);
				add_line_to_business_plan(section, id, tag);
				parse_and_fill_in_table(xml);
				business_plan_edited = true;
			}
			
			function remove_line_from_business_plan(section, tag) {
				$(xml).find("smart_contract").find(section).find(tag).remove();
				parse_and_fill_in_table(xml);
				business_plan_edited = true;
			}
			
			function change_line_in_business_plan(value, item_id)
			{
				var section = $("#" + item_id).attr("bp_section");
				var item = $("#" + item_id).attr("bp_item");
				var value_name = $("#" + item_id).attr("bp_value_name");
				$(xml).find("smart_contract").find("business_plan").find(section).find(item).find(value_name).text(value);
				parse_and_fill_in_table(xml);
				business_plan_edited = true;
			}
			
			function get_business_plan_xml_text()
			{ 
				var xmlString = undefined;
				if ( window.ActiveXObject )
					xmlString = xml[0].xml;
				if ( xmlString === undefined ) {
					var oSerializer = new XMLSerializer();
					xmlString = oSerializer.serializeToString(xml[0]);
				}
				xmlString = get_text_between_tags(xmlString, "<business_plan", "</business_plan>");
				xmlString = get_text_between_tags(xmlString, ">", "");
				xmlString = "<business_plan>" + xmlString + "</business_plan>";
				return xmlString;
			}
			'
			:''
		).'
		var business_plan = hex_to_string("'.$business_plan_xml.'");
		var business_plan_edited = false;
		var ar_dataPoints = "";
		business_plan = business_plan.replace(/<([^\s]*) xml[^>]*>/gi, "<$1>");
		var xmlDoc = $.parseXML(business_plan);
		var xml = $(xmlDoc);
		
		parse_and_fill_in_table( xml );
		
		var profit_chart = new CanvasJS.Chart("profit_chart_container",
		{
			title:{
				text: "Yearly Profit"
			},
			axisY: {
				lineThickness: 1,
				lineColor: "#'.COLOR1LIGHT.'",
				includeZero: false,
				prefix: "'.DOLLAR_SIGN.'",
				gridColor: "#'.COLOR1LIGHT.'",
				gridThickness: 1,     
				tickColor: "#'.COLOR1LIGHT.'",
				tickLength: 2,
				tickThickness: 1,
				interlacedColor: "#'.COLOR2LIGHT.'",
				labelFontColor: "#'.COLOR1DARK.'"
			},
			axisX: {
				lineThickness: 1,
				lineColor: "#'.COLOR1LIGHT.'",
				valueFormatString: "DD-MMM",
				gridColor: "#'.COLOR1LIGHT.'" ,
				gridThickness: 1,
				tickColor: "#'.COLOR1LIGHT.'",
				tickLength: 2,
				tickThickness: 1,
				labelFontColor: "#'.COLOR1DARK.'"
			},
			data: [
				{
					type: "column", 
					fillOpacity: .5,
					yValueFormatString: "'.DOLLAR_SIGN.'#,###",
					dataPoints: ar_dataPoints
				}								
			]
		});
		profit_chart.render();
	</script>
	';
}
?>
<script type="text/javascript">

function parse_and_fill_in_table( xml ) {
	var smart_contract = xml.find( "smart_contract" );
	var total_startup_expenses = 0;
	var bp_startup_expenses_rows = '';
	var section = "";
	smart_contract.find("business_plan").find("start_up_expenses").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_startup_expenses_rows = bp_startup_expenses_rows + '<tr><td><span class="'+get_business_plan_edit_item_class()+'" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td">' + get_remove_line_button(section, $(j)[0].nodeName) + '<span class="'+get_business_plan_edit_item_class()+'" id="item_value_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "value", "number") + ' >' + currency_format(Number($(j).find("value").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_startup_expenses = total_startup_expenses + Number($(j).find("value").text());
			}
		});
	});
	$("#bp_startup_expenses_rows").html(bp_startup_expenses_rows);
	$("#total_startup_expenses").html(currency_format(Number(total_startup_expenses), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	
	var total_startup_assets = 0;
	var bp_startup_assets_rows = '';
	smart_contract.find("business_plan").find("start_up_assets").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_startup_assets_rows = bp_startup_assets_rows + '<tr><td><span class="'+get_business_plan_edit_item_class()+'" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td">' + get_remove_line_button(section, $(j)[0].nodeName) + '<span class="'+get_business_plan_edit_item_class()+'" id="item_value_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "value", "number") + ' >' + currency_format(Number($(j).find("value").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_startup_assets = total_startup_assets + Number($(j).find("value").text());
			}
		});
	});
	$("#bp_startup_assets_rows").html(bp_startup_assets_rows);
	$("#total_startup_assets").html(currency_format(Number(total_startup_assets), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var total_sales_y1 = 0;
	var total_sales_y2 = 0;
	var total_sales_y3 = 0;
	var bp_sales_rows = '';
	smart_contract.find("business_plan").find("sales").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_sales_rows = bp_sales_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_sales_y1 = total_sales_y1 + Number($(j).find("year_1").text());
				total_sales_y2 = total_sales_y2 + Number($(j).find("year_2").text());
				total_sales_y3 = total_sales_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_sales_rows").html(bp_sales_rows);
	$("#total_sales_y1").html(currency_format(Number(total_sales_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_sales_y2").html(currency_format(Number(total_sales_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_sales_y3").html(currency_format(Number(total_sales_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	$("#_total_sales_y1").html(currency_format(Number(total_sales_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#_total_sales_y2").html(currency_format(Number(total_sales_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#_total_sales_y3").html(currency_format(Number(total_sales_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var total_payroll_y1 = 0;
	var total_payroll_y2 = 0;
	var total_payroll_y3 = 0;

	var administrative_payroll_y1 = 0;
	var administrative_payroll_y2 = 0;
	var administrative_payroll_y3 = 0;
	var bp_administrative_personnel_rows = '';
	smart_contract.find("business_plan").find("administrative_personnel").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_administrative_personnel_rows = bp_administrative_personnel_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_payroll_y1 = total_payroll_y1 + Number($(j).find("year_1").text());
				total_payroll_y2 = total_payroll_y2 + Number($(j).find("year_2").text());
				total_payroll_y3 = total_payroll_y3 + Number($(j).find("year_3").text());

				administrative_payroll_y1 = administrative_payroll_y1 + Number($(j).find("year_1").text());
				administrative_payroll_y2 = administrative_payroll_y2 + Number($(j).find("year_2").text());
				administrative_payroll_y3 = administrative_payroll_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_administrative_personnel_rows").html(bp_administrative_personnel_rows);
	$("#administrative_payroll_y1").html(currency_format(Number(administrative_payroll_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#administrative_payroll_y2").html(currency_format(Number(administrative_payroll_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#administrative_payroll_y3").html(currency_format(Number(administrative_payroll_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var production_payroll_y1 = 0;
	var production_payroll_y2 = 0;
	var production_payroll_y3 = 0;
	var bp_production_personnel_rows = '';
	smart_contract.find("business_plan").find("production_personnel").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_production_personnel_rows = bp_production_personnel_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_payroll_y1 = total_payroll_y1 + Number($(j).find("year_1").text());
				total_payroll_y2 = total_payroll_y2 + Number($(j).find("year_2").text());
				total_payroll_y3 = total_payroll_y3 + Number($(j).find("year_3").text());

				production_payroll_y1 = production_payroll_y1 + Number($(j).find("year_1").text());
				production_payroll_y2 = production_payroll_y2 + Number($(j).find("year_2").text());
				production_payroll_y3 = production_payroll_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_production_personnel_rows").html(bp_production_personnel_rows);
	$("#production_payroll_y1").html(currency_format(Number(production_payroll_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#production_payroll_y2").html(currency_format(Number(production_payroll_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#production_payroll_y3").html(currency_format(Number(production_payroll_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var marketing_payroll_y1 = 0;
	var marketing_payroll_y2 = 0;
	var marketing_payroll_y3 = 0;
	var bp_marketing_personnel_rows = '';
	smart_contract.find("business_plan").find("marketing_personnel").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_marketing_personnel_rows = bp_marketing_personnel_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				total_payroll_y1 = total_payroll_y1 + Number($(j).find("year_1").text());
				total_payroll_y2 = total_payroll_y2 + Number($(j).find("year_2").text());
				total_payroll_y3 = total_payroll_y3 + Number($(j).find("year_3").text());

				marketing_payroll_y1 = marketing_payroll_y1 + Number($(j).find("year_1").text());
				marketing_payroll_y2 = marketing_payroll_y2 + Number($(j).find("year_2").text());
				marketing_payroll_y3 = marketing_payroll_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_marketing_personnel_rows").html(bp_marketing_personnel_rows);
	$("#marketing_payroll_y1").html(currency_format(Number(marketing_payroll_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#marketing_payroll_y2").html(currency_format(Number(marketing_payroll_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#marketing_payroll_y3").html(currency_format(Number(marketing_payroll_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));


	$("#total_payroll_y1").html(currency_format(Number(total_payroll_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_payroll_y2").html(currency_format(Number(total_payroll_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_payroll_y3").html(currency_format(Number(total_payroll_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	$("#_total_payroll_y1").html(currency_format(Number(total_payroll_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#_total_payroll_y2").html(currency_format(Number(total_payroll_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#_total_payroll_y3").html(currency_format(Number(total_payroll_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var marketing_expenses_y1 = 0;
	var marketing_expenses_y2 = 0;
	var marketing_expenses_y3 = 0;
	var bp_marketing_expenses_rows = '';
	smart_contract.find("business_plan").find("marketing_expenses").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_marketing_expenses_rows = bp_marketing_expenses_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				
				marketing_expenses_y1 = marketing_expenses_y1 + Number($(j).find("year_1").text());
				marketing_expenses_y2 = marketing_expenses_y2 + Number($(j).find("year_2").text());
				marketing_expenses_y3 = marketing_expenses_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_marketing_expenses_rows").html(bp_marketing_expenses_rows);
	$("#marketing_expenses_y1").html(currency_format(Number(marketing_expenses_y1) + marketing_payroll_y1, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#marketing_expenses_y2").html(currency_format(Number(marketing_expenses_y2) + marketing_payroll_y2, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#marketing_expenses_y3").html(currency_format(Number(marketing_expenses_y3) + marketing_payroll_y3, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var administrative_expenses_y1 = 0;
	var administrative_expenses_y2 = 0;
	var administrative_expenses_y3 = 0;
	var bp_administrative_expenses_rows = '';
	smart_contract.find("business_plan").find("administrative_expenses").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_administrative_expenses_rows = bp_administrative_expenses_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				
				administrative_expenses_y1 = administrative_expenses_y1 + Number($(j).find("year_1").text());
				administrative_expenses_y2 = administrative_expenses_y2 + Number($(j).find("year_2").text());
				administrative_expenses_y3 = administrative_expenses_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_administrative_expenses_rows").html(bp_administrative_expenses_rows);
	$("#administrative_expenses_y1").html(currency_format(Number(administrative_expenses_y1) + administrative_payroll_y1, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#administrative_expenses_y2").html(currency_format(Number(administrative_expenses_y2) + administrative_payroll_y2, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#administrative_expenses_y3").html(currency_format(Number(administrative_expenses_y3) + administrative_payroll_y3, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var other_expenses_y1 = 0;
	var other_expenses_y2 = 0;
	var other_expenses_y3 = 0;
	var bp_other_expenses_rows = '';
	smart_contract.find("business_plan").find("other_expenses").each(function() {
		section = $(this)[0].nodeName;
		$(this).children().each(function(i, j) {
			if ( $(j).find("tag").text().length > 0 ) {
				bp_other_expenses_rows = bp_other_expenses_rows + '<tr><td><span class="'+get_business_plan_edit_item_class() + '" id="item_tag_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "tag", "text") + ' >' + $(j).find("tag").text() + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y1_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_1", "number") + ' >' + currency_format(Number($(j).find("year_1").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td"><span class="' + get_business_plan_edit_item_class()+'" id="item_value_y2_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_2", "number") + ' >' + currency_format(Number($(j).find("year_2").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td><td class="currency_td">'+get_remove_line_button(section, $(j)[0].nodeName) +'<span class="' + get_business_plan_edit_item_class()+'" id="item_value_y3_' + i + section + '" ' + get_business_plan_edit_item_code(section, $(j)[0].nodeName, "year_3", "number") + ' >' + currency_format(Number($(j).find("year_3").text()), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0) + '</span></td></tr>';
				
				other_expenses_y1 = other_expenses_y1 + Number($(j).find("year_1").text());
				other_expenses_y2 = other_expenses_y2 + Number($(j).find("year_2").text());
				other_expenses_y3 = other_expenses_y3 + Number($(j).find("year_3").text());
			}
		});
	});
	$("#bp_other_expenses_rows").html(bp_other_expenses_rows);
	$("#other_expenses_y1").html(currency_format(Number(other_expenses_y1), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#other_expenses_y2").html(currency_format(Number(other_expenses_y2), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#other_expenses_y3").html(currency_format(Number(other_expenses_y3), "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	$("#total_operating_expenses_y1").html(currency_format(marketing_expenses_y1 + marketing_payroll_y1 + administrative_expenses_y1 + administrative_payroll_y1 + other_expenses_y1, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_operating_expenses_y2").html(currency_format(marketing_expenses_y2 + marketing_payroll_y2 + administrative_expenses_y2 + administrative_payroll_y2 + other_expenses_y2, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#total_operating_expenses_y3").html(currency_format(marketing_expenses_y3 + marketing_payroll_y3 + administrative_expenses_y3 + administrative_payroll_y3 + other_expenses_y3, "<?php echo DOLLAR_SIGN;?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	var net_profit_y1 = total_sales_y1 - total_startup_expenses - (marketing_expenses_y1 + marketing_payroll_y1 + administrative_expenses_y1 + administrative_payroll_y1 + other_expenses_y1);
	var net_profit_y2 = total_sales_y2 - (marketing_expenses_y2 + marketing_payroll_y2 + administrative_expenses_y2 + administrative_payroll_y2 + other_expenses_y2);
	var net_profit_y3 = total_sales_y3 - (marketing_expenses_y3 + marketing_payroll_y3 + administrative_expenses_y3 + administrative_payroll_y3 + other_expenses_y3);

	$("#net_profit_y1").html(currency_format(Number(net_profit_y1), "<?php echo DOLLAR_SIGN;?>", "color:#008800;", "color:#ff0000;", "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#net_profit_y2").html(currency_format(Number(net_profit_y2), "<?php echo DOLLAR_SIGN;?>", "color:#008800;", "color:#ff0000;", "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));
	$("#net_profit_y3").html(currency_format(Number(net_profit_y3), "<?php echo DOLLAR_SIGN;?>", "color:#008800;", "color:#ff0000;", "<?php echo DOLLAR_SIGN_POSITION; ?>", 0));

	ar_dataPoints = [{"y":net_profit_y1,"label":"Year 1","color":(net_profit_y1 >= 0?"#5ca33f":"#ff0000")},{"y":net_profit_y2,"label":"Year 2","color":(net_profit_y2 >= 0?"#5ca33f":"#ff0000")},{"y":net_profit_y3,"label":"Year 3","color":(net_profit_y3 >= 0?"#5ca33f":"#ff0000")}];
	
	if (profit_chart) {
		profit_chart.options.data[0].dataPoints[0].y = net_profit_y1;
		profit_chart.options.data[0].dataPoints[0].color = (net_profit_y1 >= 0?"#5ca33f":"#ff0000"); 
		profit_chart.options.data[0].dataPoints[1].y = net_profit_y2; 
		profit_chart.options.data[0].dataPoints[1].color = (net_profit_y2 >= 0?"#5ca33f":"#ff0000"); 
		profit_chart.options.data[0].dataPoints[2].y = net_profit_y3; 
		profit_chart.options.data[0].dataPoints[2].color = (net_profit_y3 >= 0?"#5ca33f":"#ff0000");
		profit_chart.render();
	}

}
</script>
