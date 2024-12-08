<?php
if ( @$_GET['nofooter'] ) {
}
else {
	echo '</div>'; // row
?>
	<script type="text/javascript">
	<?php if ( $user_account->is_loggedin() && !@$do_not_refresh_balance) { ?>
		var refresh_balance_last_time_refreshed = 0;
		var refresh_balance_timer = 0;
		var currency_balances = [];
		var on_balance_received_arr_of_functions = [];
		var local_fiat_eachange_rate = 1;
		if (typeof fiat_eachange_rate != "undefined" && fiat_eachange_rate != null)
			local_fiat_eachange_rate = fiat_eachange_rate;
		
		var balance_data = null;

		function refresh_balance() 
		{
			clearTimeout(refresh_balance_timer);
			var d = new Date();
			refresh_balance_last_time_refreshed = d.getTime();
			try {
				$.ajax({
					method: "POST",
					url: "/api/balance",
					data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", add_available_funds:1, add_amount_in_usd:1 }
				})
				.done(function( ajax__result ) {
					var total_in_usd = 0;
					var all_currencies_balance_body = "";
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							currency_balances = [];
							balance_data = {};
							if (typeof global_crypto_currencies != "undefined" ) 
								global_crypto_currencies = {};
							for (i = 0; i < arr_ajax__result["values"].length; i++) { 
								currency_balances[arr_ajax__result["values"][i]["currency"]] = arr_ajax__result["values"][i]["amount"];

								balance_data[arr_ajax__result["values"][i]["currency"]] = {
									amount: arr_ajax__result["values"][i]["amount"], 
									exchange_rate: arr_ajax__result["values"][i]["exchange_rate"] /** fiat_eachange_rate*/,
									symbol: arr_ajax__result["values"][i]["symbol"], 
									description: arr_ajax__result["values"][i]["description"],
									crypto_name: arr_ajax__result["values"][i]["crypto_name"],
									digits: arr_ajax__result["values"][i]["digits"],
									available_funds: arr_ajax__result["values"][i]["available_funds"],
									amount_in_usd: arr_ajax__result["values"][i]["amount_in_usd"],
									last_address: arr_ajax__result["values"][i]["last_address"]
								};

								if (arr_ajax__result["values"][i]["currency"] == "usd"){
									$("#balance_label").show();
									var positive_color = "color:inherit";

									$(".balance1").each(function () {
										if ( $(this).attr("positive_color") && $(this).attr("positive_color").length > 0 )
											positive_color = $(this).attr("positive_color");
									});
									$(".balance1").html(currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], positive_color, "color:#FF0000", "<?php echo DOLLAR_SIGN_POSITION; ?>"));
								}
								$("." + arr_ajax__result["values"][i]["currency"] + "_balance").html(currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]));
								$("." + arr_ajax__result["values"][i]["currency"] + "_available_funds").html(currency_format(arr_ajax__result["values"][i]["available_funds"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]));
								$("." + arr_ajax__result["values"][i]["currency"] + "_plain_value").html(currency_format(arr_ajax__result["values"][i]["amount"], "", "", "", undefined, arr_ajax__result["values"][i]["digits"]));
								$("." + arr_ajax__result["values"][i]["currency"] + "_available_funds").css("opacity", "1");

								if ( arr_ajax__result["values"][i]["amount"] > 0 )
									$("." + arr_ajax__result["values"][i]["currency"] + "_row").show();
								
								all_currencies_balance_body = all_currencies_balance_body + "<p><span class=notranslate>" + arr_ajax__result["values"][i]["description"] + ":&nbsp; " + currency_format(arr_ajax__result["values"][i]["amount"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]) + (arr_ajax__result["values"][i]["amount"] > 0?"</span>, amount available to send: <span class=notranslate>" + currency_format(arr_ajax__result["values"][i]["available_funds"], arr_ajax__result["values"][i]["symbol"], "color:#008800", "color:#FF0000", undefined, arr_ajax__result["values"][i]["digits"]):"") + "</span></p>";
								
								if ( typeof window[arr_ajax__result["values"][i]["currency"] + "_available_funds"] != 'undefined' ) 
									window[arr_ajax__result["values"][i]["currency"] + "_available_funds"] = parseFloat(arr_ajax__result["values"][i]["available_funds"]).toFixed(arr_ajax__result["values"][i]["digits"]);
								
								$(".balance_usd_" + arr_ajax__result["values"][i]["currency"]).html(currency_format(Number(arr_ajax__result["values"][i]["amount_in_usd"]) * local_fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", "color:#008800", "color:#FF0000", undefined, <?php echo DOLLAR_DECIMALS; ?>));
								
								$(".available_in_usd_" + arr_ajax__result["values"][i]["currency"]).html(currency_format(Number(arr_ajax__result["values"][i]["available_in_usd"]) * local_fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", "color:#008800", "color:#FF0000", undefined, <?php echo DOLLAR_DECIMALS; ?>));
								$(".available_in_usd_plain_" + arr_ajax__result["values"][i]["currency"]).html(currency_format(Number(arr_ajax__result["values"][i]["available_in_usd"]) * local_fiat_eachange_rate, "<?php echo DOLLAR_SIGN; ?>", "", "", undefined, <?php echo DOLLAR_DECIMALS; ?>));

								total_in_usd = total_in_usd + Number(arr_ajax__result["values"][i]["available_in_usd"]) * local_fiat_eachange_rate;
								
								if (typeof global_crypto_currencies != "undefined") {
									global_crypto_currencies[arr_ajax__result["values"][i]["currency"]] = {
										amount: arr_ajax__result["values"][i]["amount"], 
										available_funds: arr_ajax__result["values"][i]["available_funds"],
										amount_in_usd: arr_ajax__result["values"][i]["amount_in_usd"] /** fiat_eachange_rate*/,
										exchange_rate: arr_ajax__result["values"][i]["exchange_rate"], 
										symbol: arr_ajax__result["values"][i]["symbol"], 
										description: arr_ajax__result["values"][i]["description"],
										crypto_name: arr_ajax__result["values"][i]["crypto_name"],
										digits: arr_ajax__result["values"][i]["digits"],
										logo: arr_ajax__result["values"][i]["logo"],
										blocks_explorer: arr_ajax__result["values"][i]["blocks_explorer"],
										transactions_explorer: arr_ajax__result["values"][i]["transactions_explorer"],
										pattern: arr_ajax__result["values"][i]["pattern"],
										min_cashout: arr_ajax__result["values"][i]["min_cashout"],
										max_cashout: arr_ajax__result["values"][i]["max_cashout"]
									};
								}
							}
							if ( typeof Fake_Android != 'undefined' ) {
								try	{
									if (typeof global_crypto_currencies != "undefined" && global_crypto_currencies != null) 
										Fake_Android.save_value("crypto_currencies", JSON.stringify(global_crypto_currencies));
								}
								catch(error){}
							}

							if (typeof on_balance_received === "function")
								on_balance_received(arr_ajax__result["values"]);
							for (j = 0; j < on_balance_received_arr_of_functions.length; j++) {
								on_balance_received_arr_of_functions[j](arr_ajax__result["values"]);
							}
						}
					}
					catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log(ajax__result + " --- " + error);':''); ?>}

					$(".balance_in_usd").html(currency_format(total_in_usd, "<?php echo DOLLAR_SIGN; ?>", "color:#008800", "color:#FF0000", undefined, <?php echo DOLLAR_DECIMALS; ?>));
					$(".balance_in_usd").css("opacity", "1");
					$("#all_currencies_balance_body").html(all_currencies_balance_body);

					refresh_balance_timer = setTimeout( refresh_balance, 120000 );
				});
			}
			catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log("1: " + error);':''); ?>}
		}

		var d = new Date();
		refresh_balance_last_time_refreshed = d.getTime();

		$(document).mousemove(function(){
			var d = new Date();
			if ( d.getTime() - refresh_balance_last_time_refreshed > 120000 ) {
				refresh_balance();
			}
		});
	<?php } ?>
	
	logo_container_hidden = 0;

	var wait_sign = document.getElementById('wait_sign');
	if ( wait_sign ) {
		wait_sign.style.display = "none";
	}

	s = get_cookie("<?php echo SITE_SHORTDOMAIN; ?>");
	if ( s.length == 0 ) {
		set_cookie("<?php echo SITE_SHORTDOMAIN; ?>", "1");
	}
	<?php 
	if (!defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION) {
		if ( !defined('COLLAPSE_HEADER_ON_LOGIN') || !COLLAPSE_HEADER_ON_LOGIN )
			echo '
			window.addEventListener("scroll", function(e){
				if ( $(window).width() > 992 ) {
					var currentScroll = document.body.scrollTop;
					if ( !currentScroll || typeof currentScroll == "undefined" ) {
						currentScroll = document.documentElement.scrollTop;
						if ( typeof currentScroll == "undefined" ) 
							currentScroll = 0;
					}
					'.(!defined('SHOW_TOP_BAR_ON_SCROLL') || !SHOW_TOP_BAR_ON_SCROLL ? '
						if (currentScroll > '.(LOGO_HEIGHT + MENU_HEIGHT).') {
							if ( !logo_container_hidden ) {
								$("#logo_container").toggle( "clip" );
								$(".small_scroll_logo").show();
								$(".on_scroll_is_visible").show();
								$(".on_scroll_is_hidden").hide();
								logo_container_hidden = 1;
							}
						}
						else {
							$("#logo_container").show();
							$(".small_scroll_logo").hide();
							$(".on_scroll_is_visible").hide();
							$(".on_scroll_is_hidden").show();
							logo_container_hidden = 0;
						}
					' : '' ).'
					$("#top_menu_navbar").addClass("navbar-fixed-top");
					$("#blank_div_for_fixed_menu").height('.MENU_HEIGHT.');
					document.getElementsByTagName("body")[0].style.margin = "0px 0px '.(isset($submenu_max_depth) && $submenu_max_depth > 0?$submenu_max_depth:FOOTER_HEIGHT).'px 0px";
					
				}
				else {
					$("#top_menu_navbar").removeClass("navbar-fixed-top");
					$("#blank_div_for_fixed_menu").height(0);
					document.getElementsByTagName("body")[0].style.margin = "0px 0px 0px 0px";
				}
			});
			';
		else
			echo '
			if ( $(window).width() > 992 ) {
				logo_container_hidden = 0;
				$("#logo_container").toggle( "clip" );
				$(".small_scroll_logo").show();
				logo_container_hidden = 1;
			}
			else {
				$("#top_menu_navbar").removeClass("navbar-fixed-top");
				$("#blank_div_for_fixed_menu").height(0);
				document.getElementsByTagName("body")[0].style.margin = "0px 0px 0px 0px";
			}
			';
	}
	?>
	s = get_cookie("refferer_domain");
	if ( s.length == 0 ) {
		var ref = "";
		var ref_parts = parseURL(document.referrer);
		if ( ref_parts && ref_parts.host.length > 0 )
			set_cookie("refferer_domain", ref_parts.host);
	}
	
	$(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();   
		$('[data-toggle="popover"]').popover();  
		<?php 
		if ( $user_account->is_loggedin() ) {
			if ( !isset($do_not_refresh_balance) || !$do_not_refresh_balance )
				echo '
				refresh_balance();
				';
		}	
		?>
	});
	</script>
	<?php
	if (file_exists(DIR_WS_TEMP_CUSTOM_CODE.'footer.php')) {
		include(DIR_WS_TEMP_CUSTOM_CODE.'footer.php');
	}
	else {
		if ( defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION ) {
			echo '
				<div class="row mobi_buttom_menu_bar">
					<div class="col-xs-3" style="margin:0;padding: 0; text-align:center;">
						<button class="btn btn-lg btn-default"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></button>
					</div>
					<div class="col-xs-3" style="margin:0;padding: 0; text-align:center;">
						<button class="btn btn-lg btn-default"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
					</div>
					<div class="col-xs-3" style="margin:0;padding: 0; text-align:center;">
						<button class="btn btn-lg btn-default"><span class="glyphicon glyphicon-send" aria-hidden="true"></span></button>
					</div>
					<div class="col-xs-3" style="margin:0;padding:0; text-align:center;">
						<button class="btn btn-lg btn-default"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span></button>
					</div>
				</div>
			
			';
		}
		else {
			// Google Analytics >>>>>>>>>> 
			if (defined('GOOGLE_ANALYTICS_CODE') && !$user_account->is_loggedin() ) echo GOOGLE_ANALYTICS_CODE;
			// <<<<<<<<< Google Analytics --->
			
			if ( !$mobile_device && defined('GOOGLE_BANNER_CODE_160x600') && GOOGLE_BANNER_CODE_160x600 != "") 
				echo '<div class="side_banner">'.GOOGLE_BANNER_CODE_160x600.'</div>'; 

			if ( !$mobile_device && defined('GOOGLE_BANNER_CODE_728x90') && GOOGLE_BANNER_CODE_728x90 != "") 
				echo '<table class="bottom_banner" cellspacing="0" cellpadding="0" border="0"><tr><td>'.GOOGLE_BANNER_CODE_728x90.'</td></tr></table>'; 
			
			echo '</div>'; // central_container
			
			if ( !@$hide_bottom_menu ) {
				print_footer_menu($user_account, $menu_items, LOGO_ON_LEFT, true); 
				print_footer_menu($user_account, $menu_items, LOGO_ON_LEFT, false);
			}
			for ($i = 1; $i <= MAX_PAGE_READ_TIME_FILES; $i++) {
				if ( is_file_variable_expired('last_page_read_time_'.$i, 0, MIN_SECONDS_TO_CONSIDER_DDOS) ) {
					update_file_variable('last_page_read_time_'.$i, microtime(true) - $time_start);
					break;
				}
			}
		}
	}
}
if ( !defined('THIS_IS_MOBI_VERSION') || !THIS_IS_MOBI_VERSION ) {
	echo '
	<div style="position:fixed; bottom:0; right:0; z-index:20000;">
		<div class="popover left visible_on_big_screen" style="background-color:#'.COLOR2LIGHT.'; position:relative; left:-20px; top:-20px; width:300px; height:auto;" id="list_of_alerts_box">
			<div class="arrow"></div>
			<div class="popover-content" id="list_of_alerts"></div>
		</div>
	</div>
	';
}
?>
<form action="" method="post" name="force_to_change_language"><input type="hidden" name="force_language" id="force_language" value=""><input type="hidden" name="no_translate_strings" value="yes"></form>

<script type="text/javascript">
<?php if ( $user_account->is_loggedin() || (defined('THIS_IS_MOBI_VERSION') && THIS_IS_MOBI_VERSION) ) { ?>
	<?php if ( (defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet') ) { ?>
		var address_to_check = "";
		var balance_on_address_to_check = 0;
		var processed_transactions = [];
		var number_of_requests_address_to_check = 0;

		function transactions_reveived(tx_arr, checked_address)
		{
			try
			{
				if (tx_arr)	{
					for (var i = 0; i < tx_arr.length; i++) {
						var transaction = tx_arr[i];
						var found_transfer_to_this_address = false;
						var amount = 0;
						for (var j = 0; j < transaction["outputs"].length; j++) {
							var output = transaction["outputs"][j];
							if ( output["address"] == address_to_check) {
								amount = output["value"];
								found_transfer_to_this_address = true;
								break;
							}
						}
						if (! found_transfer_to_this_address)
							continue;

						var transaction_found = false;
						for (var j = 0; j < processed_transactions.length; j++) {
							if ( transaction['hash'] == processed_transactions[j] ) {
								transaction_found = true;
								break;
							}
						}
						
						if ( !transaction_found ) {
							$.ajax({
								method: "POST",
								url: "/api/user_found_new_transaction",
								data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", tx_hash:transaction['hash'], address:checked_address }
							})
							.done(function( ajax__result ) {
								try
								{
									var arr_ajax__result = JSON.parse(ajax__result);
									if ( arr_ajax__result["success"] ) {

									}
								}
								catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log(ajax__result + " --- " + error);':''); ?>}
							});
						}
					}
				}
			}
			catch(error){}

			setTimeout(function(){ 
				number_of_requests_address_to_check++;
				if (number_of_requests_address_to_check < 20)
					request_address_to_check();
			}, 30000);
		}

		function address_balance_reveived(balance, checked_address)
		{
			try
			{
				if ( balance != balance_on_address_to_check && !isNaN(balance) && Number(balance) === balance )	{
					$.ajax({
						method: "POST",
						url: "/api/user_update_address_balance",
						data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", address:checked_address, balance:balance }
					})
					.done(function( ajax__result ) {
						try
						{
							var arr_ajax__result = JSON.parse(ajax__result);
							if ( arr_ajax__result["success"] ) {

							}
						}
						catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log(ajax__result + " --- " + error);':''); ?>}
					});
				}
			}
			catch(error){}

			setTimeout(function(){ 
				number_of_requests_address_to_check++;
				if (number_of_requests_address_to_check < 20)
					request_address_to_check();
			}, 30000);
		}

		function request_address_to_check()
		{
			$.ajax({
				method: "POST",
				url: "/api/user_get_address_to_check",
				data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", check_own_only: (typeof check_own_addr_only !== "undefined" ? check_own_addr_only : "0") }
			})
			.done(function( ajax__result ) {
				try
				{
					var arr_ajax__result = JSON.parse(ajax__result);
					if ( arr_ajax__result["success"] ) {
						address_to_check = arr_ajax__result["message"];
						if ( typeof arr_ajax__result["values"] != 'undefined' && typeof arr_ajax__result["values"]["processed_transactions"] != 'undefined' ) 
							processed_transactions = arr_ajax__result["values"]["processed_transactions"];
						var crypto_cl = new Crypto(address_to_check);
						if (crypto_cl) {
							if ( typeof arr_ajax__result["values"] != 'undefined' && typeof arr_ajax__result["values"]["get_balance"] != 'undefined' && arr_ajax__result["values"]["get_balance"] ) {
								balance_on_address_to_check = arr_ajax__result["values"]["current_balance"];
								crypto_cl.get_balance(address_to_check, address_balance_reveived, transactions_reveived(false));
							}
							else
								crypto_cl.get_list_of_transactions(address_to_check, transactions_reveived, transactions_reveived(false));
						}
					}
				}
				catch(error){<?php echo (defined('DEBUG_MODE')?'write_console_log(ajax__result + " --- " + error);':''); ?>}
			});
		}
	<?php } ?>

$(".user_thumbnail").error(function() {
    $(this).attr("src", "/<?php echo DIR_WS_WEBSITE_IMAGES_DIR; ?>no_photo_60x60boy.png");
});

$(".user_thumbnail_hidden").load(function() {
    $(this).show();
});

$(".user_thumbnail_hidden").error(function() {
    $(this).hide();
});

<?php } ?>;
$("body").on("click", function (e) {
	$('[data-toggle="popover"]').each(function () {
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $(".popover").has(e.target).length === 0) {
            $(this).popover('hide');
        }
    });
});

if (typeof is_loggedin == 'undefined' ) 
	var is_loggedin = 0;
is_loggedin = <?php echo ((int)$user_account->is_loggedin()); ?>;

<?php if ( !isset($add_locale_script) || $add_locale_script ) { ?>
	var language_checks = 0;
	var hashes_calculated = false;
	var selected_language = "";
	var script_filename = "<?php echo preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $_SERVER['PHP_SELF']))); ?>";
	var timer_collect_translated_strings = 0;

	function collect_translated_strings()
	{
		timer_collect_translated_strings = 0;
		var goog_language = $(".goog-te-combo").val();
		if ((typeof goog_language == "undefined" || goog_language == null || goog_language.length == 0)) {
			if (language_checks < 50) {
				// calculating hashes of not yet translated strings
				if (!hashes_calculated) {
					$(".string_to_translate").each(function( index ) {
						try	{
							var tag = $(this)[0].tagName.toLowerCase();
							if (tag == "input") {
								var str_val = $(this).attr("placeholder");
								var str_val2 = "";
								try	{
									str_val2 = jQuery(str_val).text();
								}
								catch(error){}
								
								if (str_val2.length > 0)
									str_val = str_val2;
							}
							else {
								var str_val = $(this).html();
								var str_val2 = "";
								try	{
									str_val2 = jQuery(str_val).text();
								}
								catch(error){}
								
								if (str_val2.length > 0)
									str_val = str_val2;
							}
							if (typeof str_val != "undefiend" && str_val != null && str_val.length > 0) {
								$(this).attr("_en_hash", md5(str_val));
							}
						}
						catch(e){
							console.log("not hashes_calculated exception: " + e);
						}
					});
					hashes_calculated = true;
				}
				timer_collect_translated_strings = setTimeout(() => {
					language_checks++;
					collect_translated_strings();
				}, 1000);
			}
			return false;
		}
		else 
			selected_language = goog_language;

		var googtrans = get_cookie('googtrans');
		var lang_val = $(".goog-te-combo").val();
		if (typeof lang_val == "undefined" || lang_val == null)
			lang_val = "";
		if (hashes_calculated && googtrans.length > 0 && lang_val.length > 0) {
			setTimeout(() => {
				var strings_arr = [];
				var str_val = "";
				$(".string_to_translate").each(function( index ) {
					try
					{
						var tag = $(this)[0].tagName.toLowerCase();
						if (tag == "input") {
							str_val = $(this).attr("placeholder");
							var str_val2 = "";
							try	{
								str_val2 = jQuery(str_val).text();
							}
							catch(error){}
							
							if (str_val2.length > 0)
								str_val = str_val2;
						}
						else {
							str_val = $(this).html();
							var str_val2 = "";
							try	{
								str_val2 = jQuery(str_val).text();
							}
							catch(error){}
							if (str_val2.length > 0)
								str_val = str_val2;
						}
						if (typeof str_val != "undefiend" && str_val != null && str_val.length > 0) {
							var en_hash = $(this).attr("_en_hash");
							if (typeof en_hash == "string" && en_hash.length > 0) {
								if ($(this).attr("_en_hash") != md5(str_val) && strings_arr.length < 100)
									strings_arr.push([$(this).attr("_en_hash"), md5(str_val), Base64.encode(str_val), str_val]);
							}
						}
					}
					catch(e){
						console.log("hashes_calculated exception: " + e);
					}
				});
				if (strings_arr.length > 0) {
					$.ajax({
						method: "POST",
						url: "/api/add_locale",
						data: { language: selected_language, strings: strings_arr, script_name:script_filename },
						cache: false
					})
					.done(function( ajax__result ) {
						try
						{
							var arr_ajax__result = JSON.parse(ajax__result);
							console.log("Locale added: " + str_val);
						}
						catch(error){}
					});
				}
			}, 5000);
		}
	}

	function watch_for_not_translated_texts()
	{
		// hide google spinning circle, which has class name starting with 'VIpgJd'
		$("div[class^='VIpgJd']").hide();
		
		var number_of_not_translated_strings = 0;
		var translated_strings_have_hashes = false;

		$(".string_to_translate").each(function( index ) {
			number_of_not_translated_strings++;
			if ( $(this).attr("_en_hash") && $(this).attr("_en_hash").length > 0 )
				translated_strings_have_hashes = true;
		});
		
		if (number_of_not_translated_strings > 0) {
			try	{
				if ( !$(".goog-te-combo").html() ) {
					$.getScript("//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit", function(){
						if ( typeof is_loggedin != 'undefined' && is_loggedin && typeof this_is_mobi_version != 'undefined' && this_is_mobi_version )
						{
							$("#google_translate_element").hide();
						}
					});
				}
				if (timer_collect_translated_strings == 0) {
					hashes_calculated = translated_strings_have_hashes;
					language_checks = 0;
					timer_collect_translated_strings = setTimeout(() => {
						collect_translated_strings();
					}, 1000);
				}
			}
			catch(e){
				console.log("exception inside watch_for_not_translated_texts: " + e);
			}
		}

		setTimeout(() => {
			watch_for_not_translated_texts();
		}, 2000);
	}

	collect_translated_strings();
<?php } ?>

$( document ).ready(function() {
	<?php if (defined('SHOW_GOOGLE_TRANSLATE') && SHOW_GOOGLE_TRANSLATE == 'true' && (!isset($add_locale_script) || $add_locale_script) ) { ?>
		var string_to_translate_left = 0;
		if (typeof force_to_show_languages !== "undefined" && force_to_show_languages) {
			string_to_translate_left = 1;
		}
		else {
			$(".string_to_translate").each(function( index ) {
				string_to_translate_left++;
			});
		}	
		if ( <?php echo ( defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true' ? '0' : '1' ); ?> || string_to_translate_left > 0) {
			$.getScript("//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit", function(){
				if (is_loggedin && typeof this_is_mobi_version != "undefined" && this_is_mobi_version)
				{
					$("#google_translate_element").hide();
				}
			});
		}

		var reload_page_on_language_selected = false;
		var get_google_ch_lang_event = setInterval(function () {
			var goog = $(".goog-te-combo").attr('class');
			if ( typeof goog != "undefined") {
				$(".goog-te-combo").click(function() {
					reload_page_on_language_selected = true;
				});
				$(".goog-te-combo").change(function() {
					if (reload_page_on_language_selected) {
						var goog_language = $(".goog-te-combo").val();
						$("#force_language").val(goog_language);
						document.force_to_change_language.submit();
					}
				});
				clearInterval(get_google_ch_lang_event);
			}
		}, 100);

		setInterval(function () {
			$(".skiptranslate").css("opacity", 0);
			$(".goog-te-gadget").css("opacity", 1);
		}, 1000);

		timer_collect_translated_strings = setTimeout(() => {
			collect_translated_strings();
		}, 1000);
		<?php if ( defined('SET_LANGUAGE_AUTOMATICALLY') && SET_LANGUAGE_AUTOMATICALLY == 'true' ) { ?>
			setTimeout(() => {
				watch_for_not_translated_texts();
			}, 5000);
		<?php } ?>	
	<?php } ?>

	<?php if ( $user_account->is_loggedin() ) { 
		?>;
		if (typeof get_list_of_alerts != "undefined" ) 
			get_list_of_alerts();
		if ( Math.random() > 0.9 ) {
			try {
				$.ajax({
					method: "POST",
					url: "/api/user_refresh_session_vars",
					data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>" }
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( arr_ajax__result["success"] ) {
							
						}
					}
					catch(error){}
				});
			}
			catch(error){}
		}
		
		var inputs = getElementsByClassName_PY(document.body, "user_thumbnail_hidden");
		for (var i=0; i < inputs.length; i++) {
			if ( inputs[i].complete )
				inputs[i].style.display = "";
			inputs[i].onloadstart = function () {
				inputs[i].style.display = "none";
			}
		}
		$(".select_to_copy").on("click", function (e) {
			if (typeof select_text_by_click === "function")
				select_text_by_click( $(this).attr("id") );
		});
		
		<?php if ( (defined('APP_FAMILY') && APP_FAMILY == 'bitcoin_wallet') ) { ?>
			// Check out for new transactions
			setTimeout(function() {
				request_address_to_check();
			}, (typeof check_own_addr_only !== "undefined" ? 2000 : 30000));
		<?php } ?>
		<?php 
		if ( $user_account->is_manager() ){
		?>
			try {
				$.ajax({
					method: "POST",
					url: "/api/user_number_of_open_tickets",
					data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>" }
				})
				.done(function( ajax__result ) {
					try
					{
						var arr_ajax__result = JSON.parse(ajax__result);
						if ( parseInt(arr_ajax__result["success"]) && parseInt(arr_ajax__result["values"]) ) {
							$("#tickets_label").show();
							$("#tickets_label").effect("pulsate", { times:10 }, 5000);
							$(".number_of_tickets").html( arr_ajax__result["values"] );
						}
					}
					catch(error){}
				});
			}
			catch(error){}	
		<?php 
		}
		?>
	<?php 
	}
	?>
	try {
		$.ajax({
			method: "POST",
			url: "/api/do_tasks",
			data: {}
		})
		.done(function( ajax__result ) {

		});
	}
	catch(error){}
	
	try {
		$.ajax({
			method: "POST",
			url: "/api/update_user_menu",
			data: { userid: "<?php echo $user_account->userid; ?>", token: "<?php echo $user_account->psw_hash; ?>", permissions: "<?php echo $user_account->permissions; ?>" }
		})
		.done(function( ajax__result ) {
			try
			{
				var arr_ajax__result = JSON.parse(ajax__result);
				arr_ajax__result = arr_ajax__result;
			}
			catch(error){}
		});
	}
	catch(error){}
	
	alert_info = decodeURIComponent(get_param_value("alert_info"));
	if (alert_info.length > 0) {
		alert_info = alert_info.replace(/</gi, "&lt;"); 
		$("#intro_top_alert").hide();
		show_top_alert(alert_info);
	}

	if (typeof on_this_page_loaded === "function")
		on_this_page_loaded();
	
	
});
</script>
