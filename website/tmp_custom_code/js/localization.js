var language_checks = 0;
var hashes_calculated = false;
var selected_language = "";
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

var google_translate_initiated = false;

function show_languages_list()
{
    if ( !google_translate_initiated ) {
        init_google_translate();
        google_translate_initiated = true;
    }
}

function init_google_translate()
{
    $.getScript("//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit", function(){
        
        $("#google_translate_element").hide();
        
    });
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
				init_google_translate();
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


function change_language_automatically() {
	try	{
		var selectField = document.querySelector("#google_translate_element select");
		if (!selectField || !selectField.children || selectField.children.length < 1) {
			throw "empty list";
		}
		for (var i = 0; i < selectField.children.length; i++) {
			var option = selectField.children[i];
			// find desired langauge and change the former language of the hidden selection-field 
			if ( option.value == global_language ) {
				selectField.selectedIndex = i;
				// trigger change event afterwards to make google-lib translate this side
				selectField.dispatchEvent(new Event('change'));
				$("#google_translate_element").hide();
				break;
			}
		}
	}
	catch(error){
		setTimeout(function() { 
			change_language_automatically();
		}, 100);
	}
}

function change_language(language)
{
    var date = new Date();
    date.setTime(date.getTime() + (365*24*60*60*1000));
    document.cookie = `language=${language}; expires=${date.toUTCString()}; path=/`;
    global_language = language;
    
    init_google_translate();
    change_language_automatically();
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

$( document ).ready(function() {
    if ( global_language.length && global_language != "en") {
        collect_translated_strings();

        var string_to_translate_left = 0;
        if (typeof force_to_show_languages !== "undefined" && force_to_show_languages) {
            string_to_translate_left = 1;
        }
        else {
            $(".string_to_translate").each(function( index ) {
                string_to_translate_left++;
            });
        }	
        if ( 0 || string_to_translate_left > 0) {
            init_google_translate();
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
        
        setTimeout(() => {
            watch_for_not_translated_texts();
        }, 5000);
        
        setTimeout(() => {
            change_language_automatically();
        }, 500);
    }
});