<?php
require('../../includes/application_top.php');
require_once(DIR_WS_CLASSES.'user.class.php');

$box_message = '';
$form_message = '';
$answer = '';
if ( !empty($_POST['form_submitted']) && !$user_account->disabled )	{
    
    if ( empty($answer) ) 
        $answer = $user_account->update($_POST['firstname'], $_POST['lastname'], $_POST['country']);
    
    if ( empty($answer) ) {
        $answer = $user_account->update_address($_POST['address'], '', '', '', $_POST['phone']);
    }
    if ( empty($answer) ) 
        $answer = $user_account->update_personal('', '', '', $_POST['education']); // $_POST['gender']
    
    if ( empty($answer) )
        $answer = $user_account->update_email($_POST['email']);

    if ( empty($answer) ) {
        //$box_message = 'Your account has been updated.';
        $user_account->refresh_data();
    }
    else {
        
        $user_account->email = $_POST['email'];
        $user_account->country = $_POST['country'];
        $user_account->gender = $_POST['gender'];
        //$user_account->married = $_POST['married'];
        //$user_account->has_children = $_POST['has_children'];
        $user_account->education = $_POST['education'];
        $user_account->phone = $_POST['phone'];
        $form_message = 'Error: '.$answer;
    }
}
if ( !empty($_POST['image_submitted']) && !$user_account->disabled )	{
    if (!empty($_POST['photo_data'])) {
        $answer = $user_account->set_photo('', bin2hex($_POST['photo_data']));
    }
    if ( empty($answer) ) 
        $answer = $user_account->update_personal($_POST['gender']);

    if ( empty($answer) ) {
        $user_account->refresh_data();
    }
    else {
        $form_message = 'Error: '.$answer;
    }
}

$page_header = 'Settings';
$page_title = $page_header;
$page_desc = $page_header;
require(DIR_WS_INCLUDES.'header.php');
?>

<div class="col-12 pl-5 d-flex">
    <div class="col-6">
        <div class="container content-block height-correct dark-grey p-4" style="min-height:480px;">
            <form method="post" name="user_frm" id="user_frm">
		        <input type="hidden" name="form_submitted" value="1">
                <div class="row">
                    <!-- First name -->
                    <div class="col-md-6 mb-3">
                        <label for="firstName" class="form-label"><?php echo make_str_translateable('First name'); ?><span style="color: #FF173D;">*</span></label>
                        <input type="text" class="form-control custom-input notranslate" name="firstname" id="firstName" placeholder="Joe" required style="color:#fff;" value="<?php echo $user_account->firstname; ?>">
                    </div>
                    <!-- Last name -->
                    <div class="col-md-6 mb-3">
                        <label for="lastName" class="form-label"><?php echo make_str_translateable('Last name'); ?><span style="color: #FF173D;">*</span></label>
                        <input type="text" class="form-control custom-input notranslate" name="lastname" id="lastName" placeholder="Doe" required style="color:#fff;" value="<?php echo $user_account->lastname; ?>">
                    </div>
                </div>
                <div class="row">
                    <!-- Phone -->
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label"><?php echo make_str_translateable('Phone'); ?><span style="color: #FF173D;">*</span></label>
                        <input type="tel" class="form-control custom-input notranslate" name="phone" id="phone" placeholder="+000-000-0000" style="color:#fff;" value="<?php echo $user_account->phone; ?>">
                    </div>
                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label"><?php echo make_str_translateable('Email'); ?><span style="color: #FF173D;">*</span></label>
                        <input type="email" class="form-control custom-input notranslate" name="email" id="email" placeholder="sample@mail.com" style="color:#fff;"  value="<?php echo $user_account->email; ?>">
                    </div>
                </div>
                <div class="row">
                    <!-- Country -->
                    <div class="col-md-6 mb-3 d-flex flex-column">
                        <label for="country" class="form-label"><?php echo make_str_translateable('Country'); ?><span style="color: #FF173D;">*</span></label>
                        <select name="country" id="country" class="form-select custom-input">
                        <?php
							if ( empty($_POST['country']) ) {
								$_POST['country'] = getCountryCodefromIP();
								if ( empty($_POST['country']) ) 
									echo '<option value="" SELECTED>Select</option>'."\r\n";
							}
							make_countries_file();
							if ( file_exists(DIR_WS_TEMP.'countries.txt') ) {
								$countries_text = file_get_contents(DIR_WS_TEMP.'countries.txt');
								$countries_tmp = preg_split('/$\R?^/m', $countries_text);
								foreach ($countries_tmp as $value) {
									if ( !empty($value) ) {
										$cntry_arr = explode("=", $value);
										echo '<option value="'.$cntry_arr[0].'" ';
										if ( $user_account->country == $cntry_arr[0] ) 
											echo ' SELECTED ';
										echo '>'.$cntry_arr[1].'</option>'."\r\n";
									}
								}
							}
						?>
                        </select>
                    </div>
                    <!-- Tax status -->
                    <div class="col-md-6 mb-3 d-flex flex-column">
                        <label for="taxStatus" class="form-label"><?php echo make_str_translateable('Tax status'); ?><span style="color: #FF173D;">*</span></label>
                        <select name="education" id="taxStatus" class="form-select custom-input">
                            <option value="NON_TAX" <?php echo (empty($user_account->education) || $user_account->education == 'NON_TAX' ? 'SELECTED' : ''); ?> <?php echo make_str_translateable('Tax free', 'class="string_to_translate">', '<'); ?>/option>
                            <option value="TAX" <?php echo ($user_account->education == 'TAX' ? 'SELECTED' : ''); ?> <?php echo make_str_translateable('Taxpayer', 'class="string_to_translate">', '<'); ?>/option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Address -->
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label"><?php echo make_str_translateable('Address'); ?><span style="color: #FF173D;">*</span></label>
                        <input type="text" class="form-control custom-input notranslate" name="address" id="address" placeholder="Nulla St. Mankato Mississippi 96522" style="color:#fff;" value="<?php echo $user_account->address; ?>">
                    </div>
                </div>
                    <!-- Save button -->
                    <div class="d-flex justify-content-center w-100 align-items-center">
                    <button type="submit" class="btn btn-success"><?php echo make_str_translateable('Save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-6 margin-correct-3">
        <div class="container content-block dark-grey p-4 height-correct" style="min-height:480px;">
            <div class="graph-header mt-1 mb-3">
                <h1><?php echo make_str_translateable('Scanned documents'); ?></h1>
            </div>
            <form method="post">
		        <input type="hidden" name="image_submitted" value="1">
                <input type="hidden" name="photo_data" id="photo_data" value="">
                
                <div class="row align-items-center mb-0">
                    <label for="documentType" class="ml-3 w-100 form-label"><?php echo make_str_translateable('Permitted documents'); ?><span class="text-danger">*</span></label>
                    <div class="col-md-6">
                        <div class="">
                            <select name="gender" id="documentType" class=" w-100 form-select custom-input">
                                <option value="I" <?php echo (empty($user_account->gender) || $user_account->gender == 'I' ? 'SELECTED' : ''); ?> <?php echo make_str_translateable('National ID', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="P" <?php echo ($user_account->gender == 'P' ? 'SELECTED' : ''); ?> <?php echo make_str_translateable('Passport', 'class="string_to_translate">', '<'); ?>/option>
                                <option value="D" <?php echo ($user_account->gender == 'D' ? 'SELECTED' : ''); ?> <?php echo make_str_translateable("Driver's License", 'class="string_to_translate">', '<'); ?>/option>
                            </select>
                            <div class="d-flex justify-content-end w-100 text-success smaller mt-3">
                                <p class="text-left w-100 mb-0"> &nbsp;</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-file mt-1">
                            <input type="file" class="custom-file-input" id="fileUpload">
                            <label class="custom-file-label notranslate" for="fileUpload">id_scan.jpg</label>
                        </div>
                    <div class="d-flex justify-content-end w-100 text-success smaller mt-3 mb-3">
                        <p class="text-left w-100 mb-0 notranslate">jpg, png, pdf, tiff, webp.</p>
                    </div>
                </div>
                <div class="d-flex justify-content-center mt-4 mb-3" style="width:100%;">
                    <div class="position-relative">
                        <img src="<?php echo (empty($user_account->photo) ? '/tmp_custom_code/images/scan-document-image.png' : $user_account->get_photo().'?tmp='.rand()); ?>" _src="" alt="Document preview" class="image position-relative" style="height:130px!important; width:auto!important;" id="photo_img">
                        <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 10px; right: 10px;">&times;</button>
                    </div>
                </div>
                <div class="d-flex justify-content-center w-100 align-items-center mt-5">
                    <button type="submit" class="btn btn-success"><?php echo make_str_translateable('Upload'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$( document ).ready(function() {
    <?php
    if (!empty($form_message)) {
        echo "\r\nshow_message_box_box(`Error`, `$form_message`, 2);\r\n";
    }
    ?>
    $("#fileUpload").on("change", function (ev) {
		var f = ev.target.files[0]
		var fr = new FileReader()

		fr.onload = function (ev2) {
			console.dir(ev2);
			$("#photo_img").attr("src", ev2.target.result);
            $("#photo_data").val(ev2.target.result);
		}
		fr.readAsDataURL(f)
	});
});
</script>


<?php
require(DIR_WS_INCLUDES.'footer.php');
require(DIR_COMMON_PHP.'box_message.php');
?>
</body>
</html>