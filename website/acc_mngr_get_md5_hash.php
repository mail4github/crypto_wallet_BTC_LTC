<?php
require('../includes/application_top.php');

require(DIR_WS_INCLUDES.'account_common_top.php');

if (!empty($_GET['encrypt'])) {
	echo generate_json_answer(1, '', ['encrypted' => encrypt_decrypt('encrypt', $_POST['text'], $_POST['password'])]);
	exit;
}

if (!empty($_GET['decrypt'])) {
	echo generate_json_answer(1, '', ['decrypted' => bin2hex(encrypt_decrypt('decrypt', $_POST['text'], $_POST['password']))]);
	exit;
}


require_once(DIR_WS_INCLUDES.'box_protected_page.php');

$page_header = 'Calculate MD5 hash';
$page_title = $page_header.'. '.SITE_NAME;
$parent_page = 'acc_mngr_banners.php';
$page_desc = 'Edit Banner';
require(DIR_WS_INCLUDES.'header.php');

if ( !$protected_page_unlocked ) {
	echo get_protected_page_java_code();
	if ( !$display_in_short )
		require(DIR_WS_INCLUDES.'footer.php');
	require_once(DIR_WS_INCLUDES.'box_message.php');
	require_once(DIR_WS_INCLUDES.'box_password.php');
	exit;
}

?>

<p>
Text: <input type="text" class="form-control" id="text_for_md5" value="">
</p>
<p style="display: inline;">MD5 hash:</p> 
<p style="display: inline;"><b class="label label-primary" id="md5hash"></b></p>
<p style="margin:30px;">
<button class="btn btn-lg btn-default" onclick="$(`#md5hash`).html( md5( $(`#text_for_md5`).val() ) );"><span>  Calculate  </span></button>
</>

<h1>AES (aes-128-cbc) encrypt / decrypt</h1>

<style type="text/css">
label{margin-top:20px;}
</style>

<label>Original Text:</label>
<textarea class="form-control" id="orig_text" rows="5"></textarea>

<label>Password:</label>
<input type="password" class="form-control" id="password" rows="5"></textarea>

<p style="margin:30px;">
<button class="btn btn-lg btn-success" onclick="encrypt();">Encrypt</button>
</p>

<label>Encrypted:</label></strong>
<textarea class="form-control" id="encrypted_text" rows="5"></textarea>

<p style="margin:30px;">
<button class="btn btn-lg btn-success" onclick="decrypt();">Decrypt</button>
</p>

<label>Decrypted:</label>
<textarea class="form-control" id="decrypted_text" rows="5"></textarea>

<script>
// PROCESS
function encrypt()
{
	var myPassword = $("#password").val();
	if (myPassword.length == 0) {
		alert("Error: enter password");
		return false;
	}
	var myString = document.getElementById("orig_text").value;
	//$("#encrypted_text").val( encrypted = CryptoJS.AES.encrypt(myString, myPassword) );
	$.ajax({
		method: "POST",
		url: "?encrypt=1",
		data: {text:myString, password:myPassword}
	})
	.done(function( ajax__result ) {
		try {
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				$("#encrypted_text").val( arr_ajax__result["values"]["encrypted"] );
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Exception: " + error, 2);
		}
	})
	.fail(function() {
		show_message_box_box("Error", "Network failure", 2);
	});
}

function decrypt()
{
	var myPassword = $("#password").val();
	if (myPassword.length == 0) {
		alert("Error: enter password");
		return false;
	}
	var encrypted = $("#encrypted_text").val();
	//var decrypted = CryptoJS.AES.decrypt(encrypted, myPassword);
	//$("#decrypted_text").val( decrypted.toString(CryptoJS.enc.Utf8) );
	$.ajax({
		method: "POST",
		url: "?decrypt=1",
		data: {text:encrypted, password:myPassword}
	})
	.done(function( ajax__result ) {
		try {
			var arr_ajax__result = JSON.parse(ajax__result);
			if ( arr_ajax__result["success"] ) {
				$("#decrypted_text").val( hex_to_string(arr_ajax__result["values"]["decrypted"]) );
			}
			else {
				show_message_box_box("Error", arr_ajax__result["message"], 2);
			}
		}
		catch(error){
			show_message_box_box("Error", "Exception: " + error, 2);
		}
	})
	.fail(function() {
		show_message_box_box("Error", "Network failure", 2);
	});
}

$( document ).ready(function() {
	show_top_alert("This page sends plain, not encrypted, password through network!!!", "alert-danger");
});
</script>

<?php
require_once(DIR_WS_INCLUDES.'box_message.php');
require(DIR_WS_INCLUDES.'footer.php');
?>

</body>
</html>
