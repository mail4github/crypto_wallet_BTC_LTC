<?php
if ( $user_account->is_loggedin() ) {
	header('Location: /_cp_index');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Layout</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/tmp_custom_code/css/authorization.css">

    <script src="/javascript/pycommon.js" type="text/javascript"></script>
	  <script src="/javascript/Crypto.java.class.php" type="text/javascript"></script>
    <script src="/javascript/jquery.min.js"></script>
    <script src="/javascript/jquery-ui.min.js"></script>
    <script src="/javascript/bootstrap/js/bootstrap.min.js"></script>
    <script src="/javascript/fingerprint.js" type="text/javascript"></script>
</head>
<body class="d-flex align-items-center justify-content-center bg-reg">

    <div class="logo text-center mt-4 ">
        <img src="/tmp_custom_code/images/logo.png" alt="Logo">
        <span class="link-text notranslate">Logodesign</span>
    </div>
    <div class="container text-center">
        <div class="card mx-auto p-4" id="authorization">
            <h2 class="mb-4 text-white"><?php echo make_str_translateable($page_header); ?></h2>
            <div class="dropdown position-absolute" style="top:7px; right: 25px;">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" _onclick="show_languages_list()"></button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                    <a class="dropdown-item" href="#" onclick="change_language(`en`); return false;">English</a>
                    <a class="dropdown-item" href="#" onclick="change_language(`ru`); return false;">Русский</a>
                    <a class="dropdown-item" href="#" onclick="change_language(`es`); return false;">Español</a>
                </div>
        </div>

        <div class="google_translate google_translate__panel" id="google_translate_element"></div>
        <script>
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({}, "google_translate_element");
            $("#google_translate_element img").eq(0).remove();
            $("#google_translate_element span").eq(3).remove();
        }
		</script>