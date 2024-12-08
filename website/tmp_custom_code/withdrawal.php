<?php
require('../../includes/application_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$page_header = 'Withdrawal';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
?>

<div class="col-12 pl-5 d-flex">
    <div class="col-6">
        <div class="content-block light-grey py-2">
            <div class="amount-input" style="min-height:255px;">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <label for="address" class="amount-label text-left mt-2"><?php echo make_str_translateable('Send to Address'); ?>:</label>
                    <div class="input-group justify-content-center">
                        <input type="text" id="address" placeholder="" class="amount-field mx-1 address-field">
                    </div>
                    <label for="amount" class="amount-label text-left mt-2" style="margin-top:2em!important;"><?php echo make_str_translateable('Amount'); ?></label>
                    <div class="input-group justify-content-center">
                        <input type="number" id="amount" placeholder="0" class="amount-field" min="0.0001" max="2" value="0">
                        <div class="input-group-append mx-1">
                            <select class="currency-select notranslate" onchange="set_vars_when_select_method_changed($('.currency-select').val());">
                            <option value="btc notranslate">BTC</option>
                            <option value="ltc notranslate">LTC</option>
                            </select>
                        </div>
                        <button class="submit-btn mx-1" onclick="return validate_values();"><?php echo make_str_translateable('Sending'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once('transactions_box.inc.php'); ?>
</div>

<input type="hidden" id="password_hash" value="">
<input type="hidden" id="entered_verification_pin" value="">

<script type="text/javascript">

var currency_code = "btc";
var currency_name = "";

function set_vars_when_select_method_changed(crypto_code)
{
    currency_code = crypto_code;
    currency_name = crypto_code.toLowerCase();
}

function pulsate_item()
{
	$("#" + id_to_pulsate).effect("pulsate", { times:12 }, 15);
	setTimeout(function() { $("#" + id_to_pulsate).focus(); }, 100);
}

function validate_values()
{
	if ( $("#address").val().length == 0 ) {
		id_to_pulsate = "address";
		show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Please enter address'); ?>", 2, "", "pulsate_item");
		return false;
	}
	/*if ( current_pattern.length > 0 ) {
		var match_found = $("#address").val().match( current_pattern );
		if ( !match_found ) {
			id_to_pulsate = "address";
			show_message_box_box("Error", "Wrong address", 2, "", "pulsate_item");
			return false;
		}
	}*/
    let amount = 0;
    if ( document.getElementById("amount").value.length > 0 ) {
        amount = parseFloat($("#amount").val());
    }
    let max = parseFloat($("#amount").attr("max"));
    let min = parseFloat($("#amount").attr("min"));

	if ( amount > max || amount < min ) {
		id_to_pulsate = "amount";
		show_message_box_box("<?php echo make_str_translateable('Error'); ?>", "<?php echo make_str_translateable('Please enter correct amount:'); ?> minimum " + $("#amount").attr("min") + ", maximum: " + $("#amount").attr("max") + " ", 2, "", "pulsate_item");
		return false;
	}
	show_password("", 
        "request_for_withdraw();", 
        "password_hash", 
        "<?php echo make_str_translateable('Confirm Withdrawal'); ?>", 
        `<p style='text-align:center;'><?php echo make_str_translateable('Sending to address:'); ?><br><span class=description>${$("#address").val()}</span><span class=visible_on_big_screen><br>
        <b>${$("#amount").val()}</b> ${currency_code}<br><br><?php echo make_str_translateable('Enter your password to confirm transaction'); ?></span></p>`
    );
	return true;
}

function request_for_withdraw()
{
	show_wait_box_box("Please wait...");
	
	$.ajax({
		method: "POST",
		url: "/api/user_withdraw2/",
		data: { 
            userid: "<?php echo $user_account->userid; ?>",
            token: "<?php echo $user_account->psw_hash; ?>",
            amount: $("#amount").val(),
            pay_processor_email: $("#address").val(),
            currency:currency_code.toUpperCase(),
            entered_password:$("#password_hash").val(),
            priority: "normal"
        }
	})
	.done(function( ajax__result ) {
		try
		{
			hide_wait_box_box();
			hide_verification_pin();
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				if ( arr_ajax__result["values"].length > 0 ) {
					if (arr_ajax__result["values"].indexOf("<device_not_legit>") >= 0)
						show_verification_pin(undefined, document.getElementById("entered_verification_pin"), undefined, undefined, "request_for_withdraw");
					elseshow_message_box_box("Error", arr_ajax__result["values"], 2);
						
				}
				else
					show_message_box_box("Success", "Funds are queued to send", 1, "", "redirect_on_success");
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){
            console.error(ajax__result + " request_for_withdraw: " + error);
        }
	});
}

function redirect_on_success()
{
	window.location.assign("/_cp_transactions");
	return false;
}

$( document ).ready(function() {
    
});

</script>

<?php
require_once(DIR_WS_INCLUDES.'box_yes_no.php');
require_once(DIR_WS_INCLUDES.'box_password.php');
require_once(DIR_WS_INCLUDES.'box_qr_scanner.php');
require_once(DIR_COMMON_PHP.'box_wait.php');
require_once(DIR_WS_INCLUDES.'box_verification_code.php');

require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>