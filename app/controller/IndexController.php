<?php

class IndexController
{
    public function index()
    {
        $posts = Post::all();
        $user = false;
        if ((Session::getInstance()->isLoggedIn())){
            $user = User::userData(Session::getInstance()->getUser()->id);
        }
        $view = new View();
        $view->render('index', [
            "posts" => $posts,
            "user" => $user
            ]);

    }

    public function view($id = 0)
    {
        $view = new View();
        $user = User::userData(Session::getInstance()->getUser()->id);
        $view->render('view', [
            "post" => Post::find($id),
            "likes" => Post::likes($id),
            "user" => $user
        ]);
    }

    public function newPost()
    {
        $data = $this->_validate($_POST);
        $tags = $data['tags'];
        $exception = "duplicate input";

        if ($data === false) {
            return json_encode( array('status'=> false, 'msg'=>'can not add'));
        } else {
            try{
                $connection = Db::connect();
                $sql = 'INSERT INTO post (content,user,hidden) VALUES (:content,:user,:hidden)';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue('content', $data['content']);
                $stmt->bindValue('user', Session::getInstance()->getUser()->id);
                $stmt->bindValue('hidden', 0);
                $stmt->execute();
                $id =  $connection->lastInsertId();
            }catch (PDOException $exception){

            }


            $tags = explode(',', $tags);


                foreach ($tags as $tag) {
                    try{

                    trim($tag);
                    $sql = 'INSERT INTO tags (content) VALUES (:content)';
                    $stmt = $connection->prepare($sql);
                    $stmt->bindValue('content', $tag);
                    $stmt->execute();

                    }catch (PDOException $exception){

                    }
                }




            try{

                $sql = 'SELECT * FROM post WHERE id=(SELECT MAX(id) FROM post) LIMIT 1';
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                $post = $stmt->fetch();

            }catch (PDOException $exception){

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

            }


            $newEntry = Post::find( $id);
            $user = User::userData(Session::getInstance()->getUser()->id);
            $dataReturn = array(
                'id' => $newEntry->getId(),
                'content' => $newEntry->getContent(),
                'date' => $newEntry->getDate(),
                'user' => $newEntry->getUser(),
                'likes' => $newEntry->getLikes(),
                'comments' => $newEntry->geComments(),
                'userid' => $newEntry->getUserId(),
                'tags' => $newEntry->getTags(),
                'reports' => $newEntry->getReports(),
                'hidden' => $newEntry->getHidden(),
                'admin' => $user->getAdmin()


            );
            return json_encode( array('status'=> true, 'data'=> $dataReturn ) );
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