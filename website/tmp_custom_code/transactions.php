<?php
require('../../includes/application_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$page_header = 'Transactions';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
?>

<div class="col-12 mx-3 my-3">
    <div class="content-block light-grey height-correct" style="width: 99%;">
        <table class="table mb-0" style="font-size: 12px;">
            <tbody>
                <tr data-toggle="_collapse" data-target="#row1">
                    <td class="text-white" style="border: none;"> 
                        <i class="bi bi-chevron-down text-white mr-2" onclick="show_hide_controls()"></i><?php echo make_str_translateable('Files'); ?>
                    </td>
                </tr>
                <tr id="row1" class="collapse content">
                    <td class="d-flex files">
                        <div class="col-md-2 mb-3 d-flex flex-column">
                            <select id="taxType" class="form-select custom-input" aria-placeholder="Type" onchange="show_hide_wait_sign(); widget_last_transactions_refresh();">
                                <option value="" selected hidden <?php echo make_str_translateable('Type', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="" class="text-success <?php echo make_str_translateable('All', 'string_to_translate">', '<'); ?>/option>
                                <option value="AF" <?php echo make_str_translateable('Deposit', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="PO" <?php echo make_str_translateable('Withdrawal', 'class="string_to_translate">', '<'); ?>/option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex flex-column">
                            <select id="taxTime" class="form-select custom-input" onchange="show_hide_wait_sign(); widget_last_transactions_refresh();">
                                <option value="" selected hidden <?php echo make_str_translateable('Time', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="" class="text-success <?php echo make_str_translateable('All', 'string_to_translate">', '<'); ?>/option>
                                <option value="31540000" <?php echo make_str_translateable('Past 1 year', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="7884000" <?php echo make_str_translateable('Past 90 days', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="2628000" <?php echo make_str_translateable('Past 30 days', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="864000" <?php echo make_str_translateable('Past 10 days', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="172800" <?php echo make_str_translateable('Yesterday', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="86400" <?php echo make_str_translateable('Today', 'class="string_to_translate">', '<'); ?>/option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex flex-column">
                            <select id="taxCurrency" class="form-select custom-input" onchange="show_hide_wait_sign(); widget_last_transactions_refresh();">
                                <option value="" selected hidden <?php echo make_str_translateable('Asset', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="" class="text-success <?php echo make_str_translateable('All', 'string_to_translate">', '<'); ?>/option>
                                <option value="btc" class="notranslate">BTC</option>
                                <option value="ltc" class="notranslate">LTC</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex flex-column">
                            <select id="taxStatus" class="form-select custom-input" onchange="show_hide_wait_sign(); widget_last_transactions_refresh();">
                                <option value="" selected hidden <?php echo make_str_translateable('Status', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="" class="text-success <?php echo make_str_translateable('All', 'string_to_translate">', '<'); ?>/option>
                                <option value="A" <?php echo make_str_translateable('Completed', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="P" <?php echo make_str_translateable('Procesed', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="D" <?php echo make_str_translateable('Canceled', 'class="string_to_translate">', '<'); ?>/option>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="col-12 mx-3" >
    <div class="content-block dark-grey height-correct" style="width: 99%;">
        <table class="table table-borderless table-hover text-white">
            <thead>
            <tr>
                <th scope="col" class="no-mobile"><?php echo make_str_translateable('ID'); ?></th>
                <th scope="col"><?php echo make_str_translateable('Type'); ?></th>
                <th scope="col"><?php echo make_str_translateable('Payment info'); ?></th>
                <th scope="col" class=" no-mobile"><?php echo make_str_translateable('Asset'); ?></th>
                <th scope="col"><?php echo make_str_translateable('Amount'); ?></th>
                <th scope="col" class=" no-mobile"><?php echo make_str_translateable('Date'); ?></th>
                <th scope="col" class=" no-mobile"><?php echo make_str_translateable('Status'); ?></th>
            </tr>
            </thead>
            <tbody id="last_transactions_table"></tbody>
        </table>
    </div>
</div>
<div style="display:none">
    <span id="status_complete"><?php echo make_str_translateable('Completed'); ?></span>
    <span id="status_declined"><?php echo make_str_translateable('Declined'); ?></span>
    <span id="status_pending"><?php echo make_str_translateable('Pending'); ?></span>

    <span id="type_withdrawal"><?php echo make_str_translateable('Withdrawal'); ?></span>
    <span id="type_deposit"><?php echo make_str_translateable('Deposit'); ?></span>
    <span id="type_fee"><?php echo make_str_translateable('Transaction fee'); ?></span>
</div>

<script type="text/javascript">

var number_of_widget_last_transactions_refreshed = 0;

var status_complete = $("#status_complete").html();
var status_declined = $("#status_declined").html();
var status_pending = $("#status_pending").html();

<?php 
$transacrion_row = '`
<tr>
    <td class="no-mobile notranslate">${c_transactionid}</td>
    <td>
        <i class="bi ${deposit_or_withdraw_arrow} notranslate"></i> ${c_type}
    </td>
    <td class="no-mobile notranslate">
        ${addr_to_show} <i class="bi bi-clipboard"></i>
    </td>
    <td class="no-desk notranslate">
        ${addr_to_show.substring(0, 6)}.... <i class="bi bi-clipboard"></i>
    </td>
    <td class="no-mobile notranslate" style="text-transform:uppercase;">${c_currency}</td>
    <td class="notranslate">${amount}</td>
    <td class="no-mobile">${local_time}</td>
    <td class="${status_class} no-mobile">${c_status}</td>
</tr>
`';
?>

function widget_last_transactions_refresh()
{
	try {
		var number_of_rows = 10;
		$.ajax({
			method: "POST",
			url: "/api/get_sorted_table",
			data: { 
                userid: "<?php echo $user_account->userid; ?>", 
                token: "<?php echo $user_account->psw_hash; ?>", 
                table_name: "transactions", 
                for_userid:"<?php echo $user_account->userid; ?>", 
                sort_column: "0", 
                sort_order: "DESC", 
                row_number: "0", 
                current_page_number: "0", 
                max_ros:(number_of_rows * 2), 
                max_records: "0", 
                transaction_type: $(`#taxType`).val(),
                oldest_secs_ago: $(`#taxTime`).val(),
                currency: $(`#taxCurrency`).val(),
                status: $(`#taxStatus`).val(),
            }
		})
		.done(function( ajax__result ) {
			try
			{
                show_hide_wait_sign(false);
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					var table_body = "";
					var weekday_options = { weekday: 'short'};
					var rows = 0;
					for (var i = 0; i < arr_ajax__result["values"]["table"].length; i++) {
						try {
							var d = new Date();
							d.setTime(arr_ajax__result["values"]["table"][i]["c_unix_created"] * 1000);
							var cur_date = new Date();
                            let local_time = `${d.getDate()}/${d.getMonth() + 1}/${d.getFullYear()} ${leading_zero(d.getHours(), 2)}:${leading_zero(d.getMinutes(), 2)}</span>`;
							var addr_to_show = "";
                            let c_type = get_transaction_description(arr_ajax__result["values"]["table"][i]["c_type"]);
							switch (arr_ajax__result["values"]["table"][i]["c_type"]) {
								case "PO" : 
									addr_to_show = arr_ajax__result["values"]["table"][i]["c_address_to_send"] ? arr_ajax__result["values"]["table"][i]["c_address_to_send"] : "";
                                    c_type = "Withdrawal";
								break;
								case "GA" :
								case "SA" :
									addr_to_show = arr_ajax__result["values"]["table"][i]["c_description"] ? arr_ajax__result["values"]["table"][i]["c_description"] : "";
								break;
								default :
									addr_to_show = arr_ajax__result["values"]["table"][i]["c_address_to_receive"] ? arr_ajax__result["values"]["table"][i]["c_address_to_receive"] : "";
							}
                            if ( !c_type.length ) {
                                if (arr_ajax__result["values"]["table"][i]["c_commission_as_number"] == 0) {
                                    c_type = arr_ajax__result["values"]["table"][i]["c_type"];
                                }
                                else {
                                    c_type = arr_ajax__result["values"]["table"][i]["c_commission_as_number"] > 0 ? "Deposit" : "Withdrawal";
                                }
                            }

                            let tp_locale = $("#type_" + c_type.toLowerCase()).html();
                            if (typeof tp_locale != "undefine" && tp_locale.length > 0) {
                                c_type = tp_locale;
                            }

                            let c_transactionid = arr_ajax__result["values"]["table"][i]["c_transactionid"];
                            let amount = Math.abs(arr_ajax__result["values"]["table"][i]["c_commission_as_number"]);
                            let c_status = status_complete;
                            let status_class = "text-success";
                            switch(arr_ajax__result["values"]["table"][i]["c_status"]) {
                                case "D":
                                    c_status = status_declined;
                                    status_class = "text-danger";
                                    break;
                                case "P":
                                    c_status = status_pending;
                                    status_class = "text-warning";
                                    break;
                            }
                            
                            let c_currency = arr_ajax__result["values"]["table"][i]["c_currency"];
                            let deposit_or_withdraw_arrow = arr_ajax__result["values"]["table"][i]["c_commission_as_number"] >= 0 ? "bi-arrow-down-left-circle-fill text-success" : "bi-arrow-up-right-circle-fill text-danger";
                            
							table_body = table_body + <?php echo $transacrion_row; ?>;
                            rows++;
							if (rows >= number_of_rows)
								break;
						}
						catch(error){}
					}
					$("#last_transactions_table").html(table_body);
				}
			}
			catch(error){
                show_hide_wait_sign(false);
                console.error(ajax__result + " get transactions" + error);
            }
		});
	}
	catch(error){console_log("get_sorted_table transactions: " + error);}
    
	setTimeout(function(){ 
		number_of_widget_last_transactions_refreshed++;
		if (number_of_widget_last_transactions_refreshed < 20)
			widget_last_transactions_refresh();
	}, 30000);
}

function show_hide_controls()
{
    if ( $("#row1").hasClass("show") ) {
        $("#row1").removeClass("show").addClass("collapse");
    }
    else {
        $("#row1").removeClass("collapse").addClass("show");
    }
}

$( document ).ready(function() {
    setTimeout(function(){ 
	    show_hide_wait_sign();
        widget_last_transactions_refresh();
    }, 100);
});

</script>

<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>