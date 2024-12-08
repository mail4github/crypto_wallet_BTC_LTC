<?php
if ( ! $user_account->is_loggedin() && ! is_integer(strpos($_SERVER['REQUEST_URI'], 'login')) ) {
    header('Location: /login');
	exit;
}

if ( ! is_integer(strpos($_SERVER['REQUEST_URI'], '_cp_')) ) {
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
    
    <script src="/javascript/pycommon.js" type="text/javascript"></script>
	<script src="/javascript/Crypto.java.class.php" type="text/javascript"></script>
    <script src="/javascript/jquery.min.js"></script>
    <script src="/javascript/jquery-ui.min.js"></script>
    <script src="/javascript/bootstrap/js/bootstrap.min.js"></script>
    <link href="/css/translateelement.css" rel="stylesheet">
	<link rel="preload" href="/javascript/bootstrap/fonts/glyphicons-halflings-regular.woff" as="font" type="font/woff"-->
    <link rel="stylesheet" href="/tmp_custom_code/css/main.css">
    
</head>
<body>
    <div align="center" id="wait_box" style="position: fixed; top: 40%; left: 50%; z-index: 10000; width: 0px; display:none;">
        <div style="width:1px; position:absolute; left:50%; top:50%;">
            <svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:50px; height:50px; margin:-25px -25px; display:inline-block;">
                <circle fill="none" stroke="#7AC231" stroke-width="2" cx="50" cy="50" r="38" style="opacity:0.9;"></circle>
                <circle fill="#7AC231" stroke="#000" stroke-width="3" cx="13" cy="50" r="8" transform="rotate(0 0 0)">
                    <animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 48" to="360 50 52" repeatCount="indefinite"></animateTransform>
                </circle>
            </svg>
        </div>
    </div>
    
    <div class="d-flex">
        <button id="mobile-menu-btn" class="mobile-menu-btn">
            <img src="/tmp_custom_code/images/menu.png" alt="Menu">
        </button>
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar d-flex flex-column align-items-center collapsed no-transition">
            <div class="d-flex justify-content-between mr-auto menu-language">
                <div class="d-flex justify-content-center align-items-center no-desk">
                    <a href="/_cp_deposit" class="btn btn-primary mx-3 no-laptop"><?php echo make_str_translateable('Deposit'); ?></a>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`en`); return false;">English</a>
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`ru`); return false;">Русский</a>
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`es`); return false;">Español</a>
                        </div>
                    </div>
                </div>
                <div class="logo">
                    <a href="#" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/logo.png" alt="Logo">
                        <span class="link-text notranslate">Logodesign</span>
                    </a>
                </div>
            </div>
            <ul id="sidebar-menu" class="nav flex-column mt-4 hide-menu">
                <li class="nav-item menu <?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'index')) ? 'active' : ''); ?>">
                    <a href="/_cp_index" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/dashboard<?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'index')) ? '_white' : ''); ?>.png" class="mx-3">
                        <span class="link-text"><?php echo (isset($page_header) ? make_str_translateable($page_header) : ''); ?></a></span>
                    </a>
                </li>
                <li class="nav-item menu <?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'deposit')) ? 'active' : ''); ?>">
                    <a href="/_cp_deposit" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/deposit<?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'deposit')) ? '_white' : ''); ?>.png" class="mx-3">
                        <span class="link-text"><?php echo make_str_translateable('Deposit'); ?></span>
                    </a>
                </li>
                <li class="nav-item menu <?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'withdrawal')) ? 'active' : ''); ?>">
                    <a href="/_cp_withdrawal" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/withdrawal<?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'withdrawal')) ? '_white' : ''); ?>.png" class="mx-3">
                        <span class="link-text"><?php echo make_str_translateable('Withdrawal'); ?></span>
                    </a>
                </li>
                <li class="nav-item menu <?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'transactions')) ? 'active' : ''); ?>">
                    <a href="/_cp_transactions" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/transactions<?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'transactions')) ? '_white' : ''); ?>.png" class="mx-3">
                        <span class="link-text"><?php echo make_str_translateable('Transactions'); ?></span>
                    </a>
                </li>
                <li class="nav-item menu <?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'settings')) ? 'active' : ''); ?>">
                    <a href="/_cp_settings" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/settings-svgrepo<?php echo (is_integer(strpos($_SERVER['REQUEST_URI'], 'settings')) ? '_white' : ''); ?>.png" class="mx-3">
                        <span class="link-text"><?php echo make_str_translateable('Settings'); ?></span>
                    </a>
                </li>
                <li class="nav-item menu">
                    <a href="/_cp_logout" class="nav-link d-flex align-items-center">
                        <img src="/tmp_custom_code/images/logout-arrows.png" class="mx-3">
                        <span class="link-text"><?php echo make_str_translateable('Logout'); ?></span>
                    </a>
                </li>
            </ul>
            <button id="toggle-btn" class="btn menu-btn mt-auto mb-4 d-flex align-items-center">
                <p class="minimized mb-0 mr-2"><?php echo make_str_translateable('Minimized menu'); ?></p>
                <img src="/tmp_custom_code/images/hide-sidebar-horiz.png">
            </button>
        </nav>
        <!-- Main Content -->
        <main class="flex-grow-1 pt-0 px-3">
            <div class="row">
                <div class="header d-flex align-items-center justify-content-between px-4 py-3 mb-4 no-laptop">
                    <div class="dashboard-title">
                        <h1 class="m-0"><?php echo (isset($page_header) ? make_str_translateable($page_header) : ''); ?></h1>
                    </div>
                    <div class="d-flex align-items-center">
                        <img src="/tmp_custom_code/images/account.png" alt="Client Icon" class="client-icon mr-3">
                        
                        <div class="client-info text-left mr-3">
                            <p class="mb-0 font-weight-bold text-white notranslate"><?php echo ($user_account->is_loggedin() ? $user_account->full_name() : ''); ?></p>
                            <p class="mb-0 text-danger"><img src="/tmp_custom_code/images/shield.png" class="mr-2"><?php echo make_str_translateable('Account not verified'); ?></p>
                        </div>
                        
                        <a href="/_cp_deposit" class="btn btn-primary mx-3"><?php echo make_str_translateable('Deposit'); ?></a>
                        
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`en`); return false;">English</a>
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`ru`); return false;">Русский</a>
                            <a class="dropdown-item notranslate" href="#" onclick="change_language(`es`); return false;">Español</a>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function show_hide_wait_sign(show)
                {
                    if ( typeof show == "undefined" || show) {
                        $("#wait_box").show();
                        $("main").css("filter", "blur(2px)");
                    }
                    else {
                        $("#wait_box").hide();
                        $("main").css("filter", "none");
                    }
                }
                show_hide_wait_sign();
                </script>
                
                <div class="google_translate google_translate__panel" id="google_translate_element"></div>
                <script>
                function googleTranslateElementInit() {
                    new google.translate.TranslateElement({}, "google_translate_element");
                    $("#google_translate_element img").eq(0).remove();
                    $("#google_translate_element span").eq(3).remove();
                }
                </script>
