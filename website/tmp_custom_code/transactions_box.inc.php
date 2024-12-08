<div class="col-6">
    <div class="content-block dark-grey" style="height: unset;">
        <div class="table-block mt-0">
            <table class="table table-striped">
                <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                    <div colspan="2" class="text-white">
                        <?php echo make_str_translateable('Recent transactions'); ?>
                    </div>
                    <div class="text-white">
                        <button class="btn view-all">
                        <?php echo make_str_translateable(' Show'); ?>
                        </button>
                    </div>
                </div>
                <tbody id="last_transactions_table"></tbody>
            </table>
        </div>
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
<tr data-toggle="_collapse" _data-target="#row_${c_transactionid}" class="accordion-toggle">
    <td class="text-muted">${local_time}</td>
    <td class="text-white position-relative" style="text-transform: capitalize;">
        <i class="bi bi-arrow-down-left-circle-fill position-absolute ${deposit_or_withdraw_arrow}" style="left: -7px;"></i> ${c_type}
    </td>
    <td class="text-white">${amount}</td>
    <td><i class="bi bi-chevron-down text-white" onclick="show_hide_transaction(${c_transactionid})"></i></td>
</tr>
<tr id="row_${c_transactionid}" class="collapse content">
    <td colspan="4">
        <div class="d-flex justify-content-between">
            <div class="d-flex flex-column text-left">
                <p>'.make_str_translateable('ID').':</p>
                <p>'.make_str_translateable('Payment info').':</p>
                <p>'.make_str_translateable('Asset').':</p>
                <p>'.make_str_translateable('Status').':</p>
            </div>
            <div class="d-flex flex-column text-right">
                <p>${c_transactionid}</p>
                <p class="no-mobile">${addr_to_show}</p>
                <p style="text-transform:uppercase;">${c_currency}</p>
                <p class="text-success">${c_status}</p>
            </div>
        </div>
    </td>
</tr>
`';
?>

function widget_last_transactions_refresh()
{
	try {
		var number_of_rows = 5;
		$.ajax({
			method: "POST",
			url: "/api/get_sorted_table",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", table_name: "transactions", for_userid:"<?php echo $user_account->userid; ?>", sort_column: "0", sort_order: "DESC", row_number: "0", current_page_number: "0", max_ros:(number_of_rows * 2), max_records: "0" }
		})
		.done(function( ajax__result ) {
			try
			{
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
							if (d.getDate() == cur_date.getDate() && d.getMonth() == cur_date.getMonth() && d.getFullYear() == cur_date.getFullYear() )
								var local_time = "today";
							else
							if (d >= new Date(Date.now() - 86400000))
								var local_time = "yesterday";
							else
								var local_time = d.getDate() + " " + monthNames[d.getMonth()] + ", " + (new Intl.DateTimeFormat('en-US', weekday_options).format(d)) + " <span class=description>" + d.getFullYear() + "</span>";
							local_time = local_time + "<br><span class=description>" + leading_zero(d.getHours(), 2) + ":" + leading_zero(d.getMinutes(), 2) + "</span>";

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
                            switch(arr_ajax__result["values"]["table"][i]["c_status"]) {
                                case "D":
                                    c_status = status_declined;
                                    break;
                                case "P":
                                    c_status = status_pending;
                                    break;
                            }
                            
                            let c_currency = arr_ajax__result["values"]["table"][i]["c_currency"];
                            let deposit_or_withdraw_arrow = arr_ajax__result["values"]["table"][i]["c_commission_as_number"] >= 0 ? "text-success rotate-arrow" : "text-danger";
                            
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
			catch(error){console.error(ajax__result + " get transactions" + error);}
		});
	}
	catch(error){console_log("get_sorted_table transactions: " + error);}
    
	setTimeout(function(){ 
		number_of_widget_last_transactions_refreshed++;
		if (number_of_widget_last_transactions_refreshed < 20)
			widget_last_transactions_refresh();
	}, 60000);
    
}

function show_hide_transaction(transaction_id)
{
    if ( $("#row_" + transaction_id).hasClass("show") ) {
        $("#row_" + transaction_id).removeClass("show").addClass("collapse");
    }
    else {
        $("#row_" + transaction_id).removeClass("collapse").addClass("show");
    }
}

$( document ).ready(function() {
	widget_last_transactions_refresh();
});

</script>