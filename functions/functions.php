<?php
$dbconfig = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'dbconfig.php'));
require_once $dbconfig;

/* if not connection to  dbname=myblog, run init_db.php */
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    $init = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'init_db.php'));
    require_once $init;
}


/* general functions */
function clearInt($int) {
    return abs((int) $int);
}
function clearStr($str) {
    return trim(strip_tags($str));
}
function addAdmin($login, $fio, $password){
    global $db, $error;
    try {
        $login = $db->quote($login);
        $fio = $db->quote($fio);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $password = $db->quote($password);
        echo $password;

        $sql = "INSERT INTO users(login, fio, password, rights) 
                VALUES ($login, $fio, $password, 'superuser');";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();  
    }
}
function getCommentsByPostId($postId) {
    global $db, $error;
    $comments = [];
    try {
        $postId = clearInt($postId);
        $sql = "SELECT id, author, date, content, rating FROM comments WHERE post_id = $postId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getCommentsById($id) {
    global $db, $error;
    $comments = [];
    try {
        $id = clearInt($id);
        $sql = "SELECT post_id, author, date, content, rating FROM comments WHERE id = $id;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getLastPostId() {
    global $db, $error;
    $postId = '';
    try {
        $sql = "SELECT id FROM posts ORDER BY id DESC LIMIT 1;";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $postId = $result['id'];
        if (!empty($postId)) {
            return $postId;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function getTagsToPostById($postId) {
    global $db, $error;
    try {
        $postId = clearInt($postId);

        $sql = "SELECT id, tag FROM tag_posts WHERE post_id=$postId";
        $stmt = $db->query($sql);
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = $result;
        }
        if (!empty($tags)) {
            return $tags;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* general functions */


/* functions for index.php */
function getPostsForIndex(){
    global $db, $error;
    try {
        $sql = "SELECT id, name, author, date, content, rating FROM posts;"; // LIMIT 10
        $stmt = $db->query($sql);

        if ($stmt == false) {
            return false;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        if (empty($rows) or $rows === false) {
            return false;
        }
        foreach ($rows as $post) {
            $post['name'] = mb_substr($post['name'], 0, 140);
            if (mb_strlen($post['name'], 'utf-8') > 100) {
                $post['name'] = $post['name'] . "&hellip;";
            }

            $post['content_small'] = mb_substr($post['content'], 0, 200);
            if (mb_strlen($post['content_small'], 'utf-8') > 199) {
                $post['content_small'] = $post['content_small'] . "&hellip;";
            }

            $post['content'] = mb_substr(nl2br($post['content']), 0, 320);
            if (mb_strlen($post['content'], 'utf-8') > 318) {
                $post['content'] = $post['content'] . "&hellip;";
            }

            $post['name_small'] = mb_substr($post['name'], 0, 45);
            if (mb_strlen($post['name_small'], 'utf-8') > 40) {
                $post['name_small'] = $post['name_small'] . "&hellip;";
            }

            $post['author'] = " &copy; " . $post['author'];
            $post['date'] = date("d.m.Y",$post['date']) ." в ". date("H:i", $post['date']);
            $posts[] = $post;
        }
        return $posts;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        return false;
    }
    
}
/* functions for index.php */


/* functions for reg.php and login.php */
function isUser($login, $password) {
    global $db, $error;
    global $fio;

    $users = []; 
    try {
        $sql = "SELECT login, fio, password FROM users";
        $stmt = $db->query($sql);
    
        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $user;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    foreach ($users as $user) {
        if ($login == $user['login'] && password_verify($password, $user['password'])) {
            $fio = $user['fio'];
            return true;
        }
    }
    return false;
}
function getRightsByLogin($login) {
    global $db, $error;

    $users = []; 
    try {
        $login = $db->quote($login);
        $sql = "SELECT rights FROM users WHERE login = $login;";

        $stmt = $db->query($sql);
        $rights = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $rights['rights'];
}
function createUser($login, $fio, $password) {
    global $db, $error;
    try {
        $db->beginTransaction();

        if (!isLoginUnique($login)) {
            return false;
        }
        $login = $db->quote($login);
        $fio = $db->quote($fio);
        $password = $db->quote($password);

        $sql = "INSERT INTO users (login, fio, password, rights) 
        VALUES($login, $fio, $password, 'user');";
        $db->exec($sql);

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
    return true;
}
function isLoginUnique($login) {
    global $db, $error;
    $logins = [];
    try {
        $sql = "SELECT login FROM users;";
        $stmt = $db->query($sql);
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)){
            $logins[] = $data;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    foreach ($logins as $value) {
        if ($login == $value['login']) {
            return false; //если есть совпадения, то логин не является уникальным
        }
    }
    return true;
}
/* functions for reg.php and login.php */


/* functions for viewsinglepost.php */
function getPostForViewById($id) {
    global $db, $error;
    try {
        $id = clearInt($id);
        $sql = "SELECT id, name, author, date, content, rating FROM posts WHERE id = $id;";
        $stmt = $db->query($sql);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post['content'] = str_replace("<br />", "<p>", nl2br($post['content']));
        $post['date'] = date("d.m.Y",$post['date']) ." в ". date("H:i", $post['date']);
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
    return $post;
}
function insertComments($id, $commentAuthor, $commentDate, $commentContent) {
    global $db, $error;
    try {
        $db->beginTransaction();

        $author = $db->quote($commentAuthor);
        $date = $commentDate;
        $content = $db->quote($commentContent);
        $content = trim(strip_tags($content));

        $sql = "INSERT INTO comments (post_id, author, date, content, rating) 
        VALUES($id, $author, $date, $content, 0)";
        $db->exec($sql);

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
function changePostRating($rating, $postId, $login){
    global $db, $error;
    try {
        $rating = clearInt($rating);
        $login = $db->quote($login);

        if ($rating) {
            $rate = $db->quote($rating);
            $sql = "INSERT INTO rating_posts (login, post_id, rating) 
            VALUES($login, $postId, $rate)";
            $db->exec($sql);

            $sql = "SELECT rating FROM rating_posts WHERE post_id=$postId";
            $stmt = $db->query($sql);
            $summ = 0;
            while ($postRate = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $postRates[] = $postRate['rating'];
            }
            $countRatings =  count($postRates);
            for ($i = 0; $i <= $countRatings; $i++) {
                $summ += $postRates[$i];
                $postRating = $summ / $countRatings;
                $postRating = round($postRating, 1, PHP_ROUND_HALF_UP);
            }
            $sql = "UPDATE posts SET rating=$postRating WHERE id=$postId";
            $db->exec($sql); 
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function isUserChangesPostRating($login, $postId){
    global $db, $error;
    try {
        $login = $db->quote($login);

        $sql = "SELECT rating FROM rating_posts WHERE login=$login AND post_id=$postId";
        $stmt = $db->query($sql);
        if (!$stmt) {
            return false;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function countRatingsByPostId($id) {
    global $db, $error;
    try {
        $sql = "SELECT COUNT(*) as count FROM rating_posts WHERE post_id=$id";
        $stmt = $db->query($sql);
        if(!$stmt) {
            return 0;
        }
        $countRatings = $stmt->fetch(PDO::FETCH_ASSOC);
        return $countRatings['count'];
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function changeComRating($rating, $comId, $postId, $login){
    global $db, $error;
    try {
        $comId = clearInt($comId);
        $postId = clearInt($postId);
        $login = $db->quote($login);

        if ($rating === 'like') {
            $sql = "INSERT INTO rating_comments (login, com_id, post_id) 
            VALUES($login, $comId, $postId)";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating=rating+1 WHERE id=$comId";
            $db->exec($sql); 
        }
        if ($rating === 'unlike') {
            $sql = "DELETE FROM rating_comments WHERE com_id=$comId";
            $db->exec($sql);

            $sql = "UPDATE comments SET rating=rating-1 WHERE id=$comId";
            $db->exec($sql); 
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function isUserChangesComRating($login, $comId){
    global $db, $error;
    try {
        $login = $db->quote($login);

        $sql = "SELECT id FROM rating_comments WHERE login=$login AND com_id=$comId";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* functions for viewsinglepost.php */


/* functions for addpost.php */
function insertToPosts($name, $author, $content, $rating) {
    global $db, $error;
    $date = time();

    try {

        $name = $db->quote($name);
        $author = $db->quote($author);
        $content = $db->quote($content);
        $rating = clearInt($rating);

        $sql = "INSERT INTO posts (name, author, date, content, rating) 
        VALUES($name, $author, $date, $content, $rating);";

        $db->exec($sql);
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
function addTagsToPosts($tag) {
    global $db, $error;
    $date = time();

    try {

        $tag = $db->quote($tag);
        $postId = getLastPostId();
        $sql = "INSERT INTO tag_posts (tag, post_id) 
        VALUES($tag, $postId);";

        $db->exec($sql);
    } catch (PDOException $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
/* functions for addpost.php */


/* functions for cabinet.php */
function getLoginAndFioById($userId){
    global $db, $error;
    $userId = clearInt($userId);
    $login = '';
    $fio = '';
    try {
        $sql = "SELECT login, fio FROM users WHERE id = $userId;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        if($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function getPostsByFio($fio) {
    global $db, $error;
    try {
        $posts = [];
        $fio = $db->quote($fio);
        $sql = "SELECT id, name, date, content, rating FROM posts WHERE author = $fio;";
        $stmt = $db->query($sql);
        if(!$stmt) {
            return false;
        }
        while($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $post;
        }
        if(!$posts) {
            return false;
        } else {
            return $posts;
        }
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
function getCommentsByFio($fio) {
    global $db, $error;
    $comments = [];
    try {
        $fio = $db->quote($fio);
        $sql = "SELECT id, post_id, date, content, rating FROM comments WHERE author = $fio;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;
}
function getLikedPostsByLogin($login) {
    global $db, $error;
    $posts = [];
    try {
        $login = $db->quote($login);
        $sql = "SELECT id, post_id, rating FROM rating_posts WHERE login = $login;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $result;
        }
        if (empty($posts)) {
            return false;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $posts;   
}
function getLikedCommentsByLogin($login) {
    global $db, $error;
    $comments = [];
    try {
        $login = $db->quote($login);
        $sql = "SELECT id, post_id, com_id FROM rating_comments WHERE login = $login;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return false;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[$result['com_id']] = $result;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
    return $comments;   
}
/* functions for cabinet.php */


/* functions for search.php */
function searchPostsByTag($searchword) {
    global $db, $error;
    $results = [];
    try {
        $searchword = clearStr($searchword);
        $sql = "SELECT tag, post_id FROM tag_posts;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return null;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $result;
        }
        if (!empty($posts)) {
            foreach ($posts as $post) {
                if (strpos($post['tag'], $searchword) !== false) {
                    $results[] = $post['post_id'];
                }
            } 
        } else {
            return null;
        }
        if (!empty($results)) {
            return $results;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function searchPostsByNameAndAuthor($searchword) {
    global $db, $error;
    $posts = [];
    try {
        $searchword = clearStr($searchword);
        $sql = "SELECT id, name, author FROM posts;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return null;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = $result;
        }
        foreach ($posts as $post) {
            if (strpos($post['name'], $searchword) !== false) {
                $results[] = $post['id'];
            }
            if (strpos($post['author'], $searchword) !== false) {
                $results[] = $post['id'];
            }
        }
        if (!empty($results)) {
            return $results;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function searchUsersByFioAndLogin($searchword, $rights = 'user') {
    global $db, $error;
    $users = [];
    try {
        $searchword = clearStr($searchword);
        $sql = "SELECT id, login, fio, rights FROM users;";// LIMIT 30
        $stmt = $db->query($sql);
        if ($stmt == false) {
            return null;
        }
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $result;
        }
        $i = 0;
        foreach ($users as $user) {
            
            if (strpos($user['fio'], $searchword) !== false) {
                $results[$i]['id'] = $user['id'];
                $results[$i]['fio'] = $user['fio'];
                $results[$i]['rights'] = $user['rights'];
                if ($rights == 'superuser') { //поиск по логину только для администраторов
                    $results[$i]['login'] = $user['login'];
                }
            }
            if ($rights == 'superuser') { //поиск по логину только для администраторов
                if (strpos($user['login'], $searchword) !== false) {
                    $results[$i]['id'] = $user['id'];
                    $results[$i]['fio'] = $user['fio'];
                    $results[$i]['rights'] = $user['rights'];
                    $results[$i]['login'] = $user['login'];
                }
            }
            $i++;
        }
        if (!empty($results)) {
            return $results;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
/* functions for search.php */


/* functions for stab_db.php  */
function isNounForTag($string){
    $groups = ['а','ь'];
    $nouns = [];

    $string = mb_strtolower($string);
    $string = clearStr($string);
    $string = str_replace('.', ' ', $string);
    $words = explode(' ',$string);
    //print_r($words);
    foreach ($words as $w) {
        $lastSymbol = mb_substr($w, -1);
        
        foreach ($groups as $g) {
            if (mb_strlen($w) > 8) {
                if (mb_strtoupper($lastSymbol) === mb_strtoupper($g)) {
                    $word = "#" . $w;
                    $nouns[] = $word;
                    $nouns = array_unique($nouns);
                }
            }
        }
    }
    return $nouns;
}
/* functions for stab_db.php  */

/* functions for admin/ */
function deletePostById($id) {
    global $db, $error;
    
    try {  
        $id = clearInt($id);

        /* Удаляю пост */
        $sql = "DELETE FROM posts WHERE id = $id;";
        $db->exec($sql);
        
        /* Удаляю рэйтинг этого поста */
        $sql = "DELETE FROM rating_posts WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю его картинку */
        //unlink("..\images\PostImgId$id.jpg");

        /* Удаляю все комментарии, связанные с постом */
        $sql = "DELETE FROM comments WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю рэйтинг комментариев, связанных с постом  */
        $sql = "DELETE FROM rating_comments WHERE post_id = $id;";
        $db->exec($sql);

        /* Удаляю тэги, связанных с постом  */
        $sql = "DELETE FROM tag_posts WHERE post_id = $id;";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function connectToUsers() {
    global $db, $error;
    try {
        $sql = "SELECT id, login, fio, password, rights FROM users;";
        $stmt = $db->query($sql);
        while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $arr;
        }
        return $users;
    } catch (PDOException $e) {
        $error = $e->getMessage();
        return false;
    }
}
function deleteUserById($id) {
    global $db, $error;
    $id = clearInt($id);
    try {
        $sql = "DELETE FROM users WHERE id = $id;";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function deleteCommentById($deleteCommentId) {
    global $db, $error;
    $deleteCommentId = clearInt($deleteCommentId);
    try {
        /* Удаляю комментарий */
        $sql = "DELETE FROM comments WHERE id = $deleteCommentId;";
        $db->exec($sql);

        /* Удаляю рейтинг этого комментария*/
        $sql = "DELETE FROM rating_comments WHERE com_id = $deleteCommentId;";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
