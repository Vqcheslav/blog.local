<nav>
    <div class='top'>
        <div id="logo">
            <a class="logo" title="На главную" href='/'>
                <img id='imglogo' src='images/logo.jpg' alt='Лого'>
                <div id='namelogo'>Просто Блог</div>
            </a>
        </div>
        <div id="menu">
            <ul class='menuList'>
                <?php
                    if (empty($sessionUserId)) {
                        echo "<li><a class='menuLink' href='login.php'>Войти</a></li>";
                    } else {
                        echo "<li><a class='menuLink' href='?exit'>Выйти</a></li>";
                        if (!empty($isSuperuser)) {
                            echo "<li><a class='menuLink' href='admin/admin.php'>Админка</a></li>";
                        }
                    }
                ?>
                <li><a class='menuLink' href='cabinet.php'>Мой профиль</a></li>
                <li><a class='menuLink' href='search.php'>Поиск</a></li>
                <li><a class='menuLink' href='addpost.php'>Создать новый пост</a></li>
            </ul>
        </div>
    </div>
</nav>