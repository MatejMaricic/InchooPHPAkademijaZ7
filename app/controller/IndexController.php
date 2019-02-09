<?php

class IndexController
{
    public function index()
    {
        $view = new View();
        $posts = Post::all();

        $view->render('index', [
            "posts" => $posts
        ]);
    }

    public function view($id = 0)
    {
        $view = new View();

        $view->render('view', [
            "post" => Post::find($id)
        ]);
    }

    public function newPost()
    {
        $data = $this->_validate($_POST);
        $tags = $data['tags'];
        $exception = "duplicate input";

        if ($data === false) {
            header('Location: ' . App::config('url'));
        } else {
            try{
                $connection = Db::connect();
                $sql = 'INSERT INTO post (content,user,hidden) VALUES (:content,:user,:hidden)';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue('content', $data['content']);
                $stmt->bindValue('user', Session::getInstance()->getUser()->id);
                $stmt->bindValue('hidden', 0);
                $stmt->execute();
            }catch (PDOException $exception){
                header('Location: ' . App::config('url'));
            }


            $tags = explode(',', $tags);

            try{
                foreach ($tags as $tag) {
                    trim($tag);
                    $sql = 'INSERT INTO tags (content) VALUES (:content)';
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue('content', $tag);
                    $stmt->execute();
                }

            }catch (PDOException $exception){
                header('Location: ' . App::config('url'));
            }


            try{

                $sql = 'SELECT * FROM post WHERE id=(SELECT MAX(id) FROM post) LIMIT 1';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $post = $stmt->fetch();

            }catch (PDOException $exception){
                header('Location: ' . App::config('url'));
            }



        $tagId = [];

            try{

                foreach ($tags as $tag) {

                    $sql = 'SELECT id FROM tags WHERE content=:tag LIMIT 1';
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue('tag', $tag);
                    $stmt->execute();
                    $tagId[] = $stmt->fetch();

                }

            }catch (PDOException $exception){
                header('Location: ' . App::config('url'));
            }



            try{


                foreach ($tagId as $tag) {

                    $sql = 'INSERT INTO tag_relations (tag_id, post_id) VALUES (:tag_id, :post_id)';
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue('tag_id', $tag->id);
                    $stmt->bindValue('post_id', $post->id);
                    $stmt->execute();
                }

            }catch (PDOException $exception){
                header('Location: ' . App::config('url'));
            }



            header('Location: ' . App::config('url'));
        }
    }

    /**
     * @param $data
     * @return array|bool
     */
    private function _validate($data)
    {
        $required = ['content'];

        //validate required keys
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                return false;
            }

            $data[$key] = trim((string)$data[$key]);
            if (empty($data[$key])) {
                return false;
            }
        }
        return $data;
    }
}