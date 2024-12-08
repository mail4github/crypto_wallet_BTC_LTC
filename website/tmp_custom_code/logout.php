<?php
require('../../includes/application_top.php');

$user_account->logout();

header('Location: /_cp_authorization');

?>
