<?php
require('../../includes/application_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$page_header = 'Deposit';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
?>

<div class="col-12 pl-5 d-flex">
    <div class="col-6">
        <div class="content-block light-grey py-2">
            <div class="amount-input">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <label for="amount" class="amount-label text-left mt-2"><?php echo make_str_translateable('Amount'); ?></label>
                    <div class="input-group justify-content-center">
                        <input type="text" id="amount" placeholder="0" class="amount-field" />
                        <div class="input-group-append mx-1">
                            <select class="currency-select" onchange="set_vars_when_select_method_changed($('.currency-select').val());">
                            <option value="btc" class="notranslate">BTC</option>
                            <option value="ltc" class="notranslate">LTC</option>
                            </select>
                        </div>
                        <button class="submit-btn mx-1" onclick="show_qrCode();"><?php echo make_str_translateable('Show out'); ?></button>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center align-items-center flex-column">
                <span class="launch_crypto_app" style="
                    min-width:100px; 
                    min-height:100px; 
                    max-width:100px; 
                    max-height:100px; 
                    box-shadow: rgb(122, 194, 49) 0px 0px 11px 7px; 
                    border: 4px solid rgb(255, 255, 255); 
                    background-color:#c1c1c1;
                    margin: 15px;
                    " id="qrcode_public"></span>
                <p class="wallet-info mt-3 mb-2">
                    <?php echo make_str_translateable('Wallet address'); ?>:
                    <span class="no-mobile notranslate" id="address" style="margin:0 1em 0 1em;">&nbsp; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b; &#x258b;</span><span class="no-desk notranslate" id="address_short"></span>
                    <i class="bi bi-files" style="cursor:pointer;" onclick="copy_address();" id="copy_address_btn"></i>
                </p>
                <textarea wrap="soft" style="max-height:1px; opacity:0;" id="address_textarea"></textarea>
            </div>
        </div>
    </div>
    <?php require_once('transactions_box.inc.php'); ?>
</div>

<script src="/javascript/QRCode.js" type="text/javascript"></script>

<script type="text/javascript">
function show_hide_transaction(transaction_id)
{
    if ( $("#row_" + transaction_id).hasClass("show") ) {
        $("#row_" + transaction_id).removeClass("show").addClass("collapse");
    }
    else {
        $("#row_" + transaction_id).removeClass("collapse").addClass("show");
    }
}

//var select_method_function = "set_vars_when_select_method_changed";
var dollar_sign = "$";
var currency_code = "USD";
var currency_name = "";
var invoice = "";
var crypto_address = "";

var stop_check = 0;
var payment_made = 0;
//var check_payment_timer = 0;

function set_vars_when_select_method_changed(crypto_code)
{
    currency_code = crypto_code;
    currency_name = crypto_code.toLowerCase();
    $("#address").val("\u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b \u258b");
    get_address();
}

function amount_changed()
{
    $("#amount_in_usd").html( currency_format( $("#amount").val() * currency_rate * fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", undefined, undefined, undefined, <?php echo DOLLAR_DECIMALS; ?>) );
    show_qrCode();
}

function show_qrCode()
{
    var cripto_ref = currency_name + ":" + $("#address").html() + ($("#amount").val() > 0 ? "?amount=" + $("#amount").val() : "");

    var keyValuePair = {
        "qrcode_public": cripto_ref
    };
    ninja.qrCode.showQrCode(keyValuePair, 3, "qr_canvas");
    
    $("#qr_canvas").css("width", "100%");
    $("#qr_canvas").css("height", "100%");
    $("#qr_canvas").css("max-width", "100px");
    $("#qr_canvas").css("max-height", "100px");
    
    return false;
}

function address_updated()
{
    crypto_address = $("#address").html();
    $("#address_textarea").val(crypto_address);
    show_qrCode();
}

function get_address(generate_new_address)
{
    if (typeof generate_new_address == "undefined" )
        generate_new_address = 0;
    show_hide_wait_sign();
    $.ajax({
        method: "POST",
        url: "/api/user_get_request_to_pay_cart",
        data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", add_funds: 1, payment_method:currency_name, make_post_request: false, pay_email:"none", cross_payoutid:0, total:$("#amount").val(), transaction_prefix: "<?php echo ADD_FUNDS_PREFIX; ?>", note:"", currency:currency_code, currency_symbol:dollar_sign, invoice_suffix:"", return_post_request_as_text:1, force_to_use_pay_email:1, ip:"<?php echo $_SERVER['REMOTE_ADDR']; ?>", return_data_as_array:1, generate_new_address:generate_new_address },
        cache: false
    })
    .done(function( ajax__result ) {
        try
        {
            var arr_ajax__result = JSON.parse(ajax__result);
            if ( arr_ajax__result["success"] && arr_ajax__result["values"]["invoice"].length > 0 ) {
                invoice = arr_ajax__result["values"]["invoice"];
                if ( typeof arr_ajax__result["values"]["pay_email"] == "undefined" || arr_ajax__result["values"]["pay_email"].length == 0 ) {
                    $.ajax({
                        method: "POST",
                        url: "/api/user_update_crypto_addr/",
                        data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", crypto_currency:currency_name, address:"none", private_address:"none", total_BTC:"", invoice: invoice }
                    })
                    .done(function( ajax__result ) {
                        try
                        {
                            var arr_ajax__result = JSON.parse(ajax__result);
                            if ( arr_ajax__result["success"] && arr_ajax__result["values"] ) {
                                $("#address").html( arr_ajax__result["values"]["pay_email"] );
                                address_updated();
                                show_hide_wait_sign(false);
                            }
                        }
                        catch(error){<?php echo (defined('DEBUG_MODE')?'alert(error + ", result: " + ajax__result);':''); ?>}
                    });
                }
                else {
                    $("#address").html( arr_ajax__result["values"]["pay_email"] );
                    $("#address_short").html( arr_ajax__result["values"]["pay_email"].substring(0, 6) + "..." );
                    address_updated();
                    show_hide_wait_sign(false);
                }
            }
            else {
                setTimeout(function() { 
                    get_address();
                }, 2000);
            }
        }
        catch(error){}
    });
}

function copy_address()
{
    if (copy_text_from_input("address_textarea")) {
        $("#copy_address_btn").css("color", "#7AC231");
        setTimeout(function() { 
            $("#copy_address_btn").css("color", "#b2b2c5");
        }, 2000);
    }
}

$( document ).ready(function() {
    setTimeout(function() { 
        set_vars_when_select_method_changed("btc");
    }, 200);
    
	//widget_last_transactions_refresh();
});

</script>

<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>