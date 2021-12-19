<?php
$start = microtime(true);
session_start();
@set_time_limit(6000);
require_once 'dbconfig.php';

$functions = 'functions' . DIRECTORY_SEPARATOR . 'functions.php';
require_once $functions;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    require_once "init_db.php";
}

$user = [
    "names" => [0 => "Василий", 1 => "Даниил", 2 => "Иван", 3 => "Павел", 4 => "Александр", 5 => "Алексей", 6 => "Давид", 
                7 => "Фёдор", 8 => 'Анатолий', 9 => "Вячеслав", 10 => "Кирилл", 11 => "Григорий", 12 => "Георгий"],

    "surnames" => [0 => "Бродский", 1 => "Васильев", 2 => "Пугачев", 3=> "Иванюк", 4 => "Житомирский", 5 => "Данилов", 6 => "Крупской", 
                    7 => "Павлов", 8 => 'Анатольев', 9 => "Вертеловский", 10 => "Кириллов", 11 => "Григорьев", 12 => "Георгиевский"]
]; 
$zags1 = [
    0 => 'Пушкиногорье -',
    1 => 'Полуостров Крым -',
    2 => 'Ночная Россия -',
    3 => 'Пизанская башня -',
    4 => 'Италия и Швейцария -',
    5 => 'В моём путешествии США -',
    6 => 'Беларусь -',
    7 => 'Я просто обожаю Испанию, ведь Испания -',
    8 => 'Сербия и Черногория -',
    9 => 'Франция -',
    10 => 'Страна разваливается. Канада -',
    11 => 'Солнечный Белиз -',
    12 => 'Таиланд удивил -',
]; 
$zags2 = [
    0 => 'это не только памятник историко-литературный',
    1 => 'это своеобразный ботанический и зоологический сад',
    2 => 'это замечательный памятник природы',
    3 => 'хоть глаз выколи!',
    4 => 'хоть бы что',
    5 => 'это было необычайное путешествие',
    6 => 'это приоритетный пункт назначения',
    7 => 'это место, где сбываются мечты',
    8 => 'это место не только для отдыха, но и мой дом',
    9 => 'это лучшее место, где я когда-либо бывал',
    10 => 'это не только крупнейший заповедник',
    11 => 'это худшее путешествие',
    12 => 'это моя рекомендация. Место обязательно к посещению',
];  
$texts = [
    0 => 'Путешествие - это как попадание в сказку, где всё необычно и не реально. Я люблю путешествовать, узнавать другие страны и города. Залог хорошего путешествия, это грамотная подготовка. Когда я куда-нибудь приезжаю, то стараюсь посмотреть все местные достопримечательности или просто красивые места. Всё это я подготавливаю заранее. Надо знать, что смотреть в первую очередь, как добраться до них, когда они открыты и т.д. Если подготовиться хорошо, то посмотреть и узнать можно гораздо больше и дешевле.',
    1 => 'Путешествовать должны все люди. Без путешествий жизнь становится скучной и серой. Я не понимаю тех людей, кто не хочет и не любит смотреть мир. Я ещё мало где был, но уверен, что успею за свою жизнь посмотреть много красивых стран и городов. Больше всего мне нравится путешествовать на автомобиле. Мы семьёй съездили уже в Крым, Великий Новгород, Псков, Карелию и Ярославль. Сейчас мы собираемся на Онежское озеро.',
    2 => 'Что может быть лучше путешествия? Я даже не могу себе представить такого. Я очень люблю путешествовать. Без разницы куда ездить, главное познавать не виденные ранее места. Когда я путешествую, то получаю потрясающие эмоции, заряжаюсь энергией, а также борюсь со скукой и рутиной. Кроме этого, путешествия позволяют мне развивать кругозор, узнавать много чего нового.',
    3 => 'В путешествиях я знакомлюсь с новой культурой, обычаями и образом жизни проживающих там людей. Например, я вижу, что в Париже местные жители могут часами сидеть в кафе и пить маленькую чашку кофе, во Вьетнаме все ездят на мотобайках, а в Китае по вечерам много людей выходит в парки, где поют и танцуют. Все эти особенности их жизни очень интересно наблюдать.',
    4 => 'Также мне нравятся случайные знакомства с людьми со всего мира. В Турции я познакомилась с немцами из Бремена, в Египте с девушкой из Польши, а в Голландии с бабушкой из Канады. Со всеми из них я приятно и интересно провела время, узнала много об их жизни и путешествиях, улучшила свой английский язык. Я до сих пор переписываюсь со всеми этими знакомыми, и мы мечтаем когда-нибудь встретиться в их стране или у меня в России.',
    5 => 'Я побывала уже во многих странах мира, но ещё больше осталось мест, где я не была. Больше всего я очень хочу побывать в Австралии, Японии, США и Кении. В России я хочу посетить Байкал и Камчатку. В этом году я отправляюсь в загадочный Израиль, а также мы с родителями будем отдыхать на Кипре. С большим нетерпением я жду предстоящие путешествия и открытия в новых странах.',
    6 => 'Я не часто куда-то путешествую, поэтому, когда родители объявили мне, что в июне мы поедем в Москву, я очень обрадовался. Я давно мечтал увидеть столицу нашей родины, посмотреть её главные достопримечательности. И вот на поезде мы прибыли на Ярославский вокзал, откуда на метро добрались до нашей гостиницы. Она находится на окраине города, но рядом со станцией метро, что позволяло нам быстро добираться до нужных мест.',
    7 => 'Первым делом, мы, конечно, отправились на Красную площадь. Посмотрели Кремль, красивейший собор Василия Блаженного, мавзолей Ленина, могилу неизвестного солдата, нулевой километр, захоронения известных людей и многое другое. Увидеть такие известные места в один день, это очень здорово, просто захватывает дух. Во второй день мы катались на кораблике по Москве-реке, гуляли по парку "Зарядье", посмотрели храм Христа Спасителя, съездили посмотреть район Москва-сити. На третий день у нас была куплена экскурсия в музей-заповедник Царицыно. Там мне также очень понравилось, правда, сил ходить уже не было, а там такая огромная территория. На следующий день мы ходили в музей изобразительных искусств имени Пушкина. Я не очень хотел его посещать, но моя мама большая любительница живописи и пропустить такой музей она не могла.',
    8 => 'Но на автомобиле сложно или практически невозможно посмотреть дальние страны. Тут без самолёта не обойтись. Когда я вырасту, я хочу совершить кругосветное путешествие, используя различные виды транспорта. Это моя самая большая мечта.',
    9 => 'Путешествие одно из самых любимых занятий большинства людей. А многие так любят путешествовать... Все просто, когда человек путешествует, он познает окружающий мир и самого себя. На земле очень много необычных уголков, красивых мест, которые заставляют пережить потрясающие эмоции, чувства.',
    10 => 'Во время путешествия наполняешься энергией, силой, положительными эмоциями. Начинаешь ощущать гармонию и тесную связь человека с природой. Удивительные страны, красивые пейзажи всегда манили романтиков. Многие писатели, музыканты, художники создавали произведения искусства после путешествий, которое наполняли их новыми ощущениями, меняли их взгляды на жизнь.',
    11 => 'Когда человек начинает путешествовать, он меняется, ведь на него оказывает влияние новые страны, города, люди, природа. Мир становится более интересным и разнообразным, появляются новые друзья.',
    12 => 'С давних времен люди не зная, что там за дальше, отправлялись в путешествие, их манила неизведанность, тайна, любопытство. И это было достаточно опасно, но несмотря на это, открывались новые города, страны, моря, океаны, материки. Сейчас современный человек знает многое, но отправляясь в путешествие, он по-прежнему открывает перед собой удивительный и неповторимый мир.',
];  

if (!empty($_GET['number'])) {
    $numberOfLoopIterations = clearInt($_GET['number']);
} else {
    $numberOfLoopIterations = 10;
}
if (!empty($_SESSION['user_id'])) {
    if (strpos($_SESSION['user_id'], RIGHTS_SUPERUSER) !== false) {
        $j = $db->lastInsertId() + 1;
        for ($i = $j; $i < $j + $numberOfLoopIterations; $i++) {
            try {
                $random1 = mt_rand(0, 12);
                $random2 = mt_rand(0, 12);
                $random3 = mt_rand(0, 12);
                $randomDate = mt_rand(100000, 2628000);
                $date = time() - $randomDate;

                $userId = uniqid('user');
                $userId = $db->quote($userId);
                $password = password_hash($i, PASSWORD_BCRYPT);
                $password = $db->quote($password);
                $email = "'$i@gmail.com'";
                $fio = $user['names'][$random2] . " " . $user['surnames'][$random3];
                $fio = $db->quote($fio);

                $sql = "INSERT INTO users (user_id, email, fio, pass_word, date_time, rights) 
                        VALUES($userId, $email, $fio, $password, $date, 'user');";

                if (!$db->exec($sql)) {
                    echo $sql;
                    $errors[] = "Пользователь с id= $i не  создан";
                    continue;
                }

                $zag = $zags1[$random1] . " " . $zags2[$random2];
                $zag = $db->quote($zag);

                $text = $texts[$random3] . "<br />
<br />" . $texts[$random2] . "<br />
<br />" . $texts[$random1];
                $tags = isNounForTag($text);
                $text = $db->quote($text);

                $sql = "INSERT INTO posts (zag, user_id, date_time, content, rating) 
                        VALUES($zag, $userId, $date, $text, 0);";

                if (!$db->exec($sql)) {
                    echo $sql;
                    $errors[] = "Пост от пользователя с id= $userId не создан";
                    continue;
                }
                $postId = $db->lastInsertId();
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        $tag = $db->quote($tag);

                        $sql = "INSERT INTO tag_posts (tag, post_id) 
                                VALUES($tag, $postId);";

                        if (!$db->exec($sql)) {
                            echo $sql;
                            $errors[] = "Тэг $tag к посту №$postId не создан";
                            continue;
                        }
                    }
                }
                for ($m = 0; $m <= $random3; $m++) {
                    $random4 = mt_rand($j, $j + $numberOfLoopIterations - 1);
                    $randomUser = $random4 . "@gmail.com";
                    $randomUser = getUserIdByEmail($randomUser);
                    if (is_null($randomUser)) {
                        continue;
                    }
                    $randomUser = $db->quote($randomUser);
                    $random5 = mt_rand(0, 5);
                    $random6 = mt_rand(0, 12);
                    $dateOfComment = mt_rand($date, time());
                    $commentContent = $texts[$random6];
                    $commentContent = $db->quote($commentContent);

                    if (!isUserChangesPostRating($randomUser, $postId)) {
                        changePostRating($randomUser, $postId, $random5);
                    }

                    $randomLike = mt_rand(0, 1000);
                    $sql = "INSERT INTO comments (post_id, user_id, date_time, content, rating) 
                            VALUES($postId, $randomUser, $dateOfComment, $commentContent, $randomLike);";

                    if (!$db->exec($sql)) {
                        echo $sql;
                        $errors[] = "Комментарий к посту №$postId от пользователя с id= $randomUser не создан";
                        continue;
                    }
                    if (!isUserChangedCommentRating($randomUser, $random4)) {
                        changeCommentRating('like', $random4, $postId, $randomUser);
                    }
                }
            } catch (PDOException $e) {
                $errors[] = $e->getMessage();
            }
        }
    } else {
        header("Location: login.php");
    }
} else {
    header("Location: login.php");
}

$year = date("Y", time());
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Stab - Просто блог</title>
    <link rel='stylesheet' href='css/general.css'>
    <link rel="shortcut icon" href="/images/logo.jpg" type="image/x-icon">
</head>
<body>
    <nav>
    <div class='top'>
    <div id="logo">
            <a class="logo" title="На главную" href='/'>
            <img id='imglogo' src='images/logo.jpg' alt='Лого'>
            <div id='namelogo'>Просто Блог</div>
            </а>
        </div>
        <div id='menu'>
            <ul class='menuList'>
                <li><a class='menuLink' href='/'>На главную</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='allwithoutmenu'>
    <div class='content'>
        <div id='desc'><p>
            <?php
                if (empty($errors)) {
                    if ($numberOfLoopIterations == 1) {
                        echo "Подключение к БД: успешно</p><p>Создан $numberOfLoopIterations новый пользователь, 
                        $numberOfLoopIterations новый пост и несколько(до 12) комментариев к каждому.<br>
                        Создание 100 постов занимает примерно 15 секунд.<br>
                        Время выполнения скрипта: " . round(microtime(true) - $start, 4) . " сек.";
                    } else {
                        echo "Подключение к БД: успешно</p><p>Создано $numberOfLoopIterations новых пользователей, 
                        $numberOfLoopIterations новых постов и несколько(до 12) комментариев к каждому.<br>
                        Создание 100 постов занимает примерно 15 секунд.<br>
                        Время выполнения скрипта: " . round(microtime(true) - $start, 4) . " сек.";
                    }
                } else {
                    foreach ($errors as $error) {
                        echo $error . "\n\r";
                    }
                }
            ?>
        </p></div>
        <div class='viewcomment'>
            <form action='stab_db.php' method='get'>
                <p>Введите нужное количество постов для создания:</p>
                    <input name='number' type='text' class='text' placeholder='Кол-во постов и пользователей'><p></p>
                <input type='submit' class='submit' value='Применить'>
            </form>
        </div>
    </div>
</div>
<footer>
    <p>Website by Вячеслав Бельский &copy; <?=$year?></p>
</footer>
</body>
</html>