<?php
$add_locale_script = false;
?>            
            <!-- footer -->                
            </div>
            <div class="footer text-center text-white py-3">
                <p class="mb-0">© 2024 Copyright Logo Design. All rights reserved.</p>
            </div>
        </main>
    </div>
    <!--script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $( document ).ready(function() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const toggleIcon = toggleBtn.querySelector('img'); // Иконка кнопки для десктопа
        const mobileMenuBtn = document.getElementById('mobile-menu-btn'); // Кнопка для мобильного меню
        const sidebarMenu = document.getElementById('sidebar-menu'); // Меню на мобильной версии

        // Обработчик клика для переключения меню на десктопе
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth > 768) {
                // Только на десктопе разрешаем сворачивать меню
                sidebar.classList.toggle('collapsed');
                
                // Обновляем иконку в зависимости от состояния
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.src = '/tmp_custom_code/images/show-sidebar-horiz.png'; // Иконка свернутого состояния
                } else {
                    toggleIcon.src = '/tmp_custom_code/images/hide-sidebar-horiz.png'; // Иконка развернутого состояния
                }
            }
        });

        // Обработчик клика для переключения меню на мобильной версии
        mobileMenuBtn.addEventListener('click', () => {
            sidebarMenu.classList.toggle('hide-menu'); // Переключаем класс для скрытия/показа меню
        });

        // Удаляем "no-transition" после загрузки страницы
        document.addEventListener('DOMContentLoaded', () => {
            sidebar.classList.remove('no-transition'); // Включаем анимацию
        });

        show_hide_wait_sign(false);

    });

    $(window).on('beforeunload', function(){
        show_hide_wait_sign();
    });
    
    var script_filename = "<?php echo preg_replace('/[^a-z_\-]/i', '', str_replace('.', '-', str_replace('/', '_', $_SERVER['PHP_SELF']))); ?>";
    var global_language = "<?php echo $_COOKIE['language']; ?>";

    </script>

    <script src="/tmp_custom_code/js/localization.js" type="text/javascript"></script>

</body>
</html>
