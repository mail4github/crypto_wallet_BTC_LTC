<?php
echo '
<div class="modal fade" id="buy_stocks" role="dialog">
	<div class="modal-dialog" style="">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" onclick = "return hide_buy_stocks();">&times;</button>
				<h2 class="modal-title" id="buy_stocks_title">Buying '.WORD_MEANING_SHARE.'s</h2><span class="notranslate" id="stock_name" style=""></span>
			</div>
			<div class="modal-body">
				<table width="100%" cellspacing="2" cellpadding="0" border="0">
				<tr>
					<td width="10%"></td>
					<td width="30%">
						Price: 
					</td>
					<td width="60%">
						<span style="color:#'.COLOR1DARK.'; font-size:26px;"><span id="buy_price" style="">0.00</span></span>
					</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td></td>
					<td>
						Quantity: 
					</td>
					<td>
						'.show_incremented_value('buy_quantity', 'buy_quantity').'
					</td>
				</tr>
				<tr>
					<td colspan="3" style=""><hr></td>
				</tr>
				<tr>
					<td></td>
					<td>
						Total: 
					</td>
					<td style="">
						<span style="color:#'.COLOR1DARK.'; font-size:26px;"><span id="stock_total" style="">0.00</span></span>
					</td>
				</tr>
				</table>
				<div class="alert alert-warning" id="buy_stocks_alert" style="display:none;"></div>
			</div>
			<div class="modal-footer" style="text-align:center;">
				<button class="btn btn-success" onclick = "buy_stocks_approved();" style="margin:0;" id="buy_stocks_btn_ok">Buy</button>
				<button class="btn btn-danger" data-dismiss="modal" onclick = "return hide_buy_stocks();" style="margin:0;" id="buy_stocks_btn_cancel">Cancel</button>
			</div>
		</div>
	</div>
</div>
';
require_once(DIR_WS_INCLUDES.'box_message.php');
?>
<script language="JavaScript">

var current_id = "";
var current_stockid = "";
var current_price = 0;
var current_quantity = 0;
var max_quantity = 1000000;
var min_quantity = 1;
var buy_stocks_ok_function;

function show_buy_stocks(element, stockid, price, quantity, name, title, dont_check_quantity, btn_ok, alert, alert_type, ok_function, btn_cancel, min_stocks)
{
	current_id = element.id;
	current_stockid = stockid;
	current_price = price;
	current_quantity = quantity;
	max_quantity = parseInt(quantity);
	if ( typeof min_stocks != "undefined" )
		min_quantity = min_stocks;

	if ( typeof title != "undefined" )
		$("#buy_stocks_title").html(title);	
	if ( typeof btn_ok != "undefined" )
		$("#buy_stocks_btn_ok").html(btn_ok);
	if ( typeof btn_cancel != "undefined" )
		$("#buy_stocks_btn_cancel").html(btn_cancel);
	if ( typeof alert != "undefined" ) {
		if ( typeof alert_type != "undefined" )
			$("#buy_stocks_alert").attr("class", "alert " + alert_type);
		else
			$("#buy_stocks_alert").attr("class", "alert alert-warning");
		$("#buy_stocks_alert").html(alert);
		$("#buy_stocks_alert").show();
	}
	else
		$("#buy_stocks_alert").hide();
	
	if (typeof ok_function === "function")
		buy_stocks_ok_function = ok_function;

	$('#buy_stocks').modal('show'); 
	$("#buy_price").html( currency_format(price / 100, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>") );
	$("#stock_total").html( currency_format(price / 100, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>") );
	$("#stock_name").html( name );
	$("#buy_quantity").val( "1" );
	
	if ( typeof dont_check_quantity == "undefined" || !dont_check_quantity ) {
		currentdate = new Date(); 
		$.ajax({
			method: "POST",
			url: "/api/check_stockid/" + string_to_hex(current_stockid),
			data: { token: md5("<?php echo get_api_token_seed();?>" + Math.round(currentdate.getTime() / 60000) ) }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					max_quantity = parseInt(arr_ajax__result["values"]["stat_shares_for_sale"]);
				}
			}
			catch(error){<?php echo (!empty($_COOKIE['debug'])?'alert(error);':''); ?>}
		});
	}
	return false;
}

function hide_buy_stocks()
{
	$("#buy_price").val("0.00");
	return false;
}

function buy_stocks_approved()
{
	$('#buy_stocks').modal('hide'); 
	<?php echo (!$user_account->is_loggedin()?'
	document.location = "/login.php";
	':'
	var stock_cost = $("#buy_price").val();
	var stock_quantity = $("#buy_quantity").val();

	if (typeof buy_stocks_ok_function === "function")
		buy_stocks_ok_function(current_stockid, stock_cost, stock_quantity);
	else {
		show_wait_box_box("Loading", "Please Wait...");
		$.ajax({
			method: "POST",
			url: "/api/stock_buy/" + current_stockid,
			data: { userid: "'.$user_account->userid.'", token: "'.$user_account->psw_hash.'", quantity: stock_quantity }
		})
		.done(function( ajax__result ) {
			try
			{
				hide_wait_box_box();
				hide_buy_stocks();
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					show_box_yesno_box("You have item(s) in your cart.<br><br> Do you want to proceed to checkout?", 
						"go_checkout_after_buy_stocks", 
						"Checkout", 
						"&nbsp;&nbsp;&nbsp;&nbsp;Yes&nbsp;&nbsp;&nbsp;&nbsp;", 
						"Continue Shopping",
						"/'.DIR_WS_WEBSITE_IMAGES_DIR.'icon_checkout_big.png"
						);
				}
				else {
					try
					{
						var error_message = get_text_between_tags(arr_ajax__result["message"], "Error:", "");
						if (!error_message || error_message.length == 0)
							error_message = arr_ajax__result["message"];
						show_message_box_box("Error", error_message, 2);
					}
					catch(error){
						show_message_box_box("Error", arr_ajax__result["message"], 2);
					}
				}

			}
			catch(error){'.(!empty($_COOKIE['debug'])?'alert("buy_stocks_approved: " + error);':'').'}
		});
		
	}
	'); ?>
	return false;
}

function calculate_stock_total()
{
	var doc_element = document.getElementById("buy_stocks");
	if ( !doc_element || doc_element.style.display == "none" )
		return false;
	var gross_total = 0;
	var price = parseFloat(current_price) / 100;
	if ( price < <?php echo SHARE_PRICE_MINIMUM; ?> )
		price = <?php echo SHARE_PRICE_MINIMUM; ?>;
	gross_total = price;
	
	var quantity = Math.round(Number($("#buy_quantity").val()));
	if ( quantity > max_quantity )
		quantity = max_quantity;
	if ( quantity < min_quantity )
		quantity = min_quantity;
	$("#buy_quantity").val(quantity);
	
	gross_total = gross_total * quantity;

	$("#stock_total").html(currency_format(gross_total, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, "<?php echo DOLLAR_SIGN_POSITION; ?>"));
}

function go_checkout_after_buy_stocks()
{
	document.location = "/acc_checkout.php";
}

window.setInterval("calculate_stock_total();", 1000);

</script>
