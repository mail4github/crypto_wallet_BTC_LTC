<script type="text/javascript">
function pop_up_logins_table()
{
	show_message_box_box("Logins", "<div style='height:400px; overflow:auto; padding:0 10px 0 10px;' id='logins_table'><img src='/images/wait64x64.gif' style='width:32px; height:32px; border:none; margin:180px auto 0 auto; display:block;'><p style='text-align: center;'>Please wait...</p></div>", 0);
	try {
		$.ajax({
			method: "POST",
			url: "/api/get_sorted_table",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", table_name: "last_logins", for_userid:"<?php echo $user->userid; ?>", sort_column: "0", sort_order: "DESC", row_number: "0", current_page_number: "0", max_ros: "0", max_records: "20" }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				if ( arr_ajax__result["success"] ) {
					var table_body = "";
					var has_device_fingerprint = false;
					for (var i = 0; i < arr_ajax__result["values"]["table"].length; i++) {
						if (typeof arr_ajax__result["values"]["table"][i]["c_device_fingerprint"] !== "undefined" ) {
							has_device_fingerprint = true;
							break;
						}
					}
					for (var i = 0; i < arr_ajax__result["values"]["table"].length; i++) {
						var d = new Date();
						d.setTime(arr_ajax__result["values"]["table"][i]["c_login_unix_time"] * 1000);
						var local_time = d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear() + " " + leading_zero(d.getHours(), 2) + ":" + leading_zero(d.getMinutes(), 2);
						table_body = table_body + "<tr><td>" + local_time + "</td><td>" + arr_ajax__result["values"]["table"][i]["c_country"] + "</td><td>" + arr_ajax__result["values"]["table"][i]["c_ip"] + (has_device_fingerprint ? "<td>" + (typeof arr_ajax__result["values"]["table"][i]["c_device_fingerprint"] !== "undefined" ? arr_ajax__result["values"]["table"][i]["c_device_fingerprint"] : "") + "</td>" : "") + "</td><td>" + (typeof arr_ajax__result["values"]["table"][i]["c_device_legit"] !== "undefined" && arr_ajax__result["values"]["table"][i]["c_device_legit"] == "1" ? "<span class='glyphicon glyphicon-ok' aria-hidden=true style='color:#3be13b;'></span>" : "<span class='glyphicon glyphicon-question-sign' aria-hidden=true style='color:#edd2106e;'></span>") + "</td></tr>";

					}
					$("#logins_table").html("<table class='table table-striped'><tr><th>Date</th><th>Country</th><th>IP</th>" + (has_device_fingerprint ? "<th>Device</th><th></th>" : "") + "</tr>" + table_body + "</table>");
				}
			}
			catch(error){console.error(ajax__result + " get last_logins" + error);}
		});
	}
	catch(error){console_log("get_sorted_table last_logins: " + error);}
	return false;
}
</script>
<?php
require_once(DIR_COMMON_PHP.'box_message.php');
?>