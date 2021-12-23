<?php
$pathToPostService = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'PostService.php';
require $pathToPostService;
require 'FrontController.php';
class PostController {
    private $isSuperuser, $postService;
    public function __construct(){

        $this->postService = new PostService();
        $this->isSuperuser = FrontController::$isSuperuser;
    }
    public function getIndexPosts($numberOfPosts) {
        $posts = $this->postService->getPostsByNumber($numberOfPosts);
        return $posts;
    }
    public function getMoreTalkedPosts($numberOfPosts) {
        $moreTalkedPosts = $this->postService->getMoreTalkedPosts($numberOfPosts);
        return $moreTalkedPosts;
    }
    public function deletePostById ($id) {
        $deletePostId = clearInt($id);
        if (!empty($this->isSuperuser)) {
            if ($deletePostId !== '') {
                $this->postService->deletePostById($deletePostId);
                header("Location: /");
            }
        }
    }
    public function exitUser () {
        $_SESSION['user_id'] = false;
        setcookie('user_id', '0', 1);
        unset(FrontController::$isSuperuser);
        unset(FrontController::$sessionUserId);
        header("Location: /");
    }
}
?>