<?php

class AdminController
{
    public function login()
    {


        $view = new View();
        $view->render('login', [
            "message" => ""
        ]);
    }

    public function editor()
    {


        $view = new View();
        $view->render('edit', [
            "message" => ""
        ]);
    }

    public function registration()
    {
        $view = new View();
        $view->render('registration', ["message" => ""]);

    }

    public function tags()
    {
        $view = new View();
        $view->render('tags', ["message" => ""]);

    }

    public function register()
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into user (firstname,lastname,email,pass,admin) values (:firstname,:lastname,:email,:pass,:admin)");
        $statement->bindValue('firstname', Request::post("firstname"));
        $statement->bindValue('lastname', Request::post("lastname"));
        $statement->bindValue('email', Request::post("email"));
        $statement->bindValue('pass', password_hash(Request::post("pass"), PASSWORD_DEFAULT));
        $statement->bindValue('admin', 0);
        $statement->execute();

        $statement = $db->prepare('SELECT id FROM user WHERE id=(SELECT MAX(id) FROM user) LIMIT 1');
        $statement->execute();
        $id = $statement->fetch();

        if ($id->id == 1) {
            $statement = $db->prepare('UPDATE user set admin=:admin  LIMIT 1');
            $statement->bindValue('admin', 1);
            $statement->execute();
        }

        Session::getInstance()->logout();
        $view = new View();
        $view->render('login', ["message" => ""]);

    }

    public function updateImage()

    {
        $data = $_POST;
        $uploadImage = $this->uploadImage($data);
        $imageName = ($uploadImage) ? $uploadImage : null;

        $db = Db::connect();
        $statement = $db->prepare("UPDATE user SET image =:image where id = :id ");
        $statement->bindValue('id', Request::post("post_id"));
        $statement->bindValue('image', $imageName);
        $statement->execute();

        header('Location: ' . App::config('url') . 'admin/editor');
    }

    public function updateInfo()
    {
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
        $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if ($firstname === '' || $lastname === '' || $email === '') {
            $check = false;
            $msg = "Polja moraju biti ispunjena";
        } else {
            $check = true;
            $msg = "Podaci uspješno updateani";
        }

        if ($check === true) {
            $db = Db::connect();
            $statement = $db->prepare("UPDATE user set firstname = :firstname, lastname = :lastname, email=:email where id=:id LIMIT 1");
            $statement->bindValue('firstname', $firstname);
            $statement->bindValue('email', $email);
            $statement->bindValue('lastname', $lastname);
            $statement->bindValue('id', Session::getInstance()->getUser()->id);

            $statement->execute();
        }
        $view = new View();
        $view->render('edit', ["message" => $msg]);
    }

    public function updatePass()
    {
        $new_pass = filter_input(INPUT_POST, 'new_pass', FILTER_SANITIZE_STRING);
        $new_pass_conf = filter_input(INPUT_POST, 'new_pass_conf', FILTER_SANITIZE_STRING);

        if ($new_pass !== '' && $new_pass === $new_pass_conf) {
            $msg = "Password uspješno updatean";
            $db = Db::connect();
            $statement = $db->prepare("UPDATE user set  pass=:pass where id=:id LIMIT 1");
            $statement->bindValue('pass', password_hash($new_pass, PASSWORD_DEFAULT));
            $statement->bindValue('id', Session::getInstance()->getUser()->id);

            $statement->execute();

            $view = new View();
            $view->render('edit', ["message" => $msg]);
        } else {
            $msg = "Krivi unos";
            $view = new View();
            $view->render('edit', ["message" => $msg]);
        }

    }

    public function tagSearch($id)
    {
        $view = new View();

        $view->render('tags', [
            "posts" => Post::tags($id)]);

    }


    public function delete($post)
    {

        $db = Db::connect();
        $db->beginTransaction();
        $statement = $db->prepare("delete from comment where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from likes where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from post where id=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from tag_relations where post_id=:post");
        $statement->bindValue('post', $post);
        $statement->execute();


        $db->commit();

        $this->index();

    }

    public function comment($post)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into comment (post,user, content) values (:post,:user,:content)");
        $statement->bindValue('post', $post);
        $statement->bindValue('user', Session::getInstance()->getUser()->id);
        $statement->bindValue('content', Request::post("content"));
        $statement->execute();

        $user = false;
        if ((Session::getInstance()->isLoggedIn())) {
            $user = User::userData(Session::getInstance()->getUser()->id);
        }

        $view = new View();
        $view->render('view', [
            "post" => Post::find($post),
            "likes" => Post::likes($post),
            "user" => $user
        ]);

    }


    public function like($post)
    {

        $id = Session::getInstance()->getUser()->id;
        $unique_likes = $post . "-" . $id;
        try {
            $db = Db::connect();
            $statement = $db->prepare("insert into likes (post,user,unique_likes) values (:post,:user,:unique_likes)");
            $statement->bindValue('post', $post);
            $statement->bindValue('user', Session::getInstance()->getUser()->id);
            $statement->bindValue('unique_likes', $unique_likes);
            $statement->execute();

        } catch (PDOException $exception) {

        }
        $this->index();

    }

    public function report($post)
    {

        $id = Session::getInstance()->getUser()->id;
        $unique_report = $post . "-" . $id;

        try {
            $db = Db::connect();
            $statement = $db->prepare("insert into report (user_id,post_id,unique_report) values (:user_id,:post_id,:unique_report)");
            $statement->bindValue('post_id', $post);
            $statement->bindValue('user_id', Session::getInstance()->getUser()->id);
            $statement->bindValue('unique_report', $unique_report);
            $statement->execute();

        } catch (PDOException $exception) {

        }
        $this->index();
    }

    public function hide($post)
    {
        $val = 1;
        $db = Db::connect();
        $statement = $db->prepare("update post set hidden=:hidden where id=:post_id");
        $statement->bindValue('post_id', $post);
        $statement->bindValue('hidden', $val);
        $statement->execute();

        $this->index();

    }


    public function authorize()
    {
//nedostaju kontrole
        $db = Db::connect();
        $statement = $db->prepare("select id, concat(firstname, ' ', lastname) as name, pass from user where email=:email");
        $statement->bindValue('email', Request::post("email"));
        $statement->execute();


        if ($statement->rowCount() > 0) {
            $user = $statement->fetch();
            if (password_verify(Request::post("password"), $user->pass)) {

                unset($user->pass);

                Session::getInstance()->login($user);

                $this->index();
            } else {
                $view = new View();
                $view->render('login', ["message" => "Neispravna kombinacija korisničko ime i lozinka"]);
            }
        } else {
            $view = new View();
            $view->render('login', ["message" => "Neispravan email"]);
        }


    }

    public function logout()
    {

        Session::getInstance()->logout();
        $this->index();
    }

    public function json()
    {

        $posts = Post::all();
        //print_r($posts);
        echo json_encode($posts);
    }

    public function index()
    {

        $posts = Post::all();
        $user = false;
        if ((Session::getInstance()->isLoggedIn())) {
            $user = User::userData(Session::getInstance()->getUser()->id);
        }
        $view = new View();
        $view->render('index', [
            "posts" => $posts,
            "user" => $user

        ]);
    }

    public function deleteComment($id)
    {
        $db = Db::connect();
        $statement = $db->prepare("delete from comment where id=:id");
        $statement->bindValue('id', $id);
        $statement->execute();

        $this->index();
    }

    public function deleteLike($id)
    {
        $id = explode(',', $id);
        $post_id= $id[0];
        $unique_like= $id[1];

        $db = Db::connect();
        $statement = $db->prepare("delete from likes where post=:post_id and unique_likes=:unique_like");
        $statement->bindValue('post_id', $post_id);
        $statement->bindValue('unique_like', $unique_like);
        $statement->execute();

        $this->index();
    }

    public function removeTag($id)
    {
        $id = explode(',', $id);
        $tag_id= $id[0];
        $post_id= $id[1];

        $db = Db::connect();
        $statement = $db->prepare("delete from tag_relations where tag_id=:tag_id and post_id=:post_id");
        $statement->bindValue('tag_id', $tag_id);
        $statement->bindValue('post_id', $post_id);
        $statement->execute();

        $this->index();
    }


    function bulkinsert()
    {
        $db = Db::connect();
        for ($i = 0; $i < 1000; $i++) {

            $statement = $db->prepare("insert into post (content,user) values ('DDDD $i',1)");
            $statement->execute();

            $id = $db->lastInsertId();

            for ($j = 0; $j < 10; $j++) {

                $statement = $db->prepare("insert into comment (content,user,post) values ('CCCCC $i',1,$id)");
                $statement->execute();


            }

        }


    }

    private function uploadImage($data)
    {
        $target_dir = App::config('uploads_folder');
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = false;

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["image"]["tmp_name"]);

        $uploadOk = ($check) ? true : $uploadOk;

        if ($imageFileType != "jpg" && $imageFileType != "jpeg") {
            $uploadOk = false;
        }

        if (!$uploadOk) {
            return false;
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                return basename($_FILES["image"]["name"]);
            } else {
                return false;
            }
        }
    }


}