<?php
$it_is_flat_login = 1;
require(DIR_WS_INCLUDES.'box_login.php');
$page_header = 'Login to account';
require('auth_header.php');
?>
          
<div class="form-row">
    <div class="form-group col-md-6">
        <label for="email"><?php echo make_str_translateable('Email'); ?><span style="color: #FF002B;">*</span></label>
        <input type="text" class="form-control" name="email" id="login_email" placeholder="example@yourmail.com" value="<?php echo @$_GET['email']; ?>">
    </div>
    <div class="form-group col-md-6">
        <label for="email"><?php echo make_str_translateable('Password'); ?><span style="color: #FF002B;">*</span></label>
        <input type="password" class="form-control" name="password" id="login_password" placeholder="t5ee4yhd3sAjgr" required>
        <span id="togglePassword">
            <img src="/tmp_custom_code/images/authorization/eye-close.png" width="25" height="25" alt="Toggle Password" id="eyeIcon">
        </span>
    </div>
</div>
<div class="form-group d-flex flex-column align-items-center">
  <button type="submit" class="btn btn-success btn-block d-flex justify-content-center align-items-center mt-0" onclick="login();"><?php echo make_str_translateable('Login'); ?></button>
</div>
<div class="d-flex justify-content-between text-center mt-5">
    <a class="text-success" href="/_cp_registration"><?php echo make_str_translateable('Create an account'); ?></a>
    <p class="text-white"><?php echo make_str_translateable('Forgot your password?'); ?></p>
</div>
<p class="w-100 d-flex justify-content-center mt-4"><?php echo make_str_translateable('Back to website'); ?></p>

<?php
echo get_login_script($it_is_flat_login);

if ( !empty($_POST['its_login']) && !$user_account->is_loggedin() ) {
	$_SESSION['numb_of_logins'] = $_SESSION['numb_of_logins'] + 1;
	?>
	<script type="text/javascript">
	show_message_box_box("Error", translate_str("<span class=string_to_translate>That email address and password combination is incorrect.<br>Please try again.</span><br><br>"), 2);
	</script>
	<?php
}

?>

<div align="center" id="wait_box" style="position:fixed; top: 40%; left: 50%; z-index: 10000; width: 0px; display:none;">
    <div style="width:1px; position:absolute; left:50%; top:50%;">
        <svg version="1.1" id="L3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 0 0" xml:space="preserve" style="width:50px; height:50px; margin:-25px -25px; display:inline-block;">
            <circle fill="none" stroke="#7AC231" stroke-width="2" cx="50" cy="50" r="38" style="opacity:0.9;"></circle>
            <circle fill="#7AC231" stroke="#000" stroke-width="3" cx="13" cy="50" r="8" transform="rotate(0 0 0)">
                <animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 48" to="360 50 52" repeatCount="indefinite"></animateTransform>
            </circle>
        </svg>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('togglePassword').addEventListener('click', function () {
      var passwordField = document.getElementById('login_password');
      var eyeIcon = document.getElementById('eyeIcon');
  
      if (passwordField.type === 'password') {
          passwordField.type = 'text';
          eyeIcon.src = '/tmp_custom_code/images/authorization/eye-open.png'; 
      } 
      else {
          passwordField.type = 'password';
          eyeIcon.src = '/tmp_custom_code/images/authorization/eye-close.png';
      }
  });
  /*
  document.getElementById('togglePassword2').addEventListener('click', function () {
      var passwordField = document.getElementById('password2');
      var eyeIcon = document.getElementById('eyeIcon2');

      if (passwordField.type === 'text') {
          passwordField.type = 'password'; 
          eyeIcon.src = 'images/authorization/eye-close.png';
      } else {
          passwordField.type = 'text'; 
          eyeIcon.src = 'images/authorization/eye-open.png'; 
      }
  });
  */
});


function show_hide_wait_sign(show)
{
    if ( typeof show == "undefined" || show) {
        $("#wait_box").show();
        $(".container").css("filter", "blur(2px)");
    }
    else {
        $("#wait_box").hide();
        $(".container").css("filter", "none");
    }
}

</script>

<?php
require('auth_footer.php');
?>
</body>
</html>
