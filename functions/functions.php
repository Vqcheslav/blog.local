<?php
$root = dirname(__DIR__);
require_once $root . "\dbconfig.php";

/* if not connection to  dbname=myblog, run init_db.php */
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    $init = $root . "\init_db.php";
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
function getCommentsByPostId($postid) {
    global $db, $error;
    try {
        $sql = "SELECT id, author, date, content FROM comments WHERE post_id = $postid;";
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
/* general functions */


/* functions for index.php */
function getPostsForIndex(){
    global $db, $error;
    try {
        $sql = "SELECT id, name, author, date, content, rating FROM posts;";
        $stmt = $db->query($sql);

        if ($stmt == false) {
            return false;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        if (count($rows) == 0 or $rows === false) {
            return false;
        }
        foreach ($rows as $post) {
            $post['name'] = mb_substr($post['name'], 0, 140);
            if (mb_strlen($post['name'], 'utf-8') > 100) {
                $post['name'] = $post['name'] . "&hellip;";
            }

            $post['content_small'] = mb_substr($post['content'], 0, 140);
            if (mb_strlen($post['content_small'], 'utf-8') > 120) {
                $post['content_small'] = $post['content_small'] . "&hellip;";
            }

            $post['content'] = mb_substr(nl2br($post['content']), 0, 320);
            if (mb_strlen($post['content'], 'utf-8') > 270) {
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
        $sql = "SELECT name, author, date, content, rating FROM posts WHERE id = $id;";
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

        $sql = "INSERT INTO comments (post_id, author, date, content) 
        VALUES($id, $author, $date, $content)";
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
        
        $login = $db->quote($login);
        if ($rating == 'up') {
            $sql = "UPDATE posts SET rating=rating+1 WHERE id=$postId";
            $db->exec($sql); 
        }
        if ($rating == 'down') {
            $sql = "UPDATE posts SET rating=rating-1 WHERE id=$postId";
            $db->exec($sql); 
        }
        $rating = $db->quote($rating);

        $sql = "INSERT INTO rating_posts (login, post_id, rating) 
        VALUES($login, $postId, $rating)";
        $db->exec($sql);

    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
function isUserChangesRating($login, $postId){
    global $db, $error;
    try {
        $login = $db->quote($login);

        $sql = "SELECT rating FROM rating_posts WHERE login=$login AND post_id=$postId";
        $stmt = $db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        } else {
            return $result['rating'];
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
/* functions for addpost.php */


/* functions for admin/ */
function deletePostById($id) {
    global $db, $error;
    
    try {  
        $id = clearInt($id);

        /* Удаляю пост */
        $sql = "DELETE FROM posts WHERE id = $id;";
        $db->exec($sql);

        /* Удаляю его картинку */
        //unlink("..\images\PostImgId$id.jpg");

        /* Удаляю все комментарии, связанные с постом */
        $sql = "DELETE FROM comments WHERE post_id = $id;";
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
function deleteCommentByIdAndPostId($deleteCommentId, $postId) {
    global $db, $error;
    $deleteCommentId = clearInt($deleteCommentId);
    $postId = clearInt($postId);
    try {
        $sql = "DELETE FROM comments WHERE id = $deleteCommentId AND post_id = $postId;";
        $db->exec($sql);
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
