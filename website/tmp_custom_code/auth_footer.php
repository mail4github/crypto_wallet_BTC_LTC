        </div>
      </div>
      <footer class="mt-4">
        <p class="text-white text-center notranslate">© 2024 PROJECT. All Rights Reserved.</p>
      </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<?php
require(DIR_COMMON_PHP.'box_message.php');
?>
<script>

var script_filename = "<?php echo preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $_SERVER['PHP_SELF']))); ?>";
var global_language = "<?php echo $_COOKIE['language']; ?>";

$( document ).ready(function() {

});
</script>

<script src="/tmp_custom_code/js/localization.js" type="text/javascript"></script>

</body>
</html>
