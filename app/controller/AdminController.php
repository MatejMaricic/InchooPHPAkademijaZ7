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
        $statement = $db->prepare("insert into user (firstname,lastname,email,pass) values (:firstname,:lastname,:email,:pass)");
        $statement->bindValue('firstname', Request::post("firstname"));
        $statement->bindValue('lastname', Request::post("lastname"));
        $statement->bindValue('email', Request::post("email"));
        $statement->bindValue('pass', password_hash(Request::post("pass"), PASSWORD_DEFAULT));
        $statement->execute();

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
        $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING );
        $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING );
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );

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
            $statement->bindValue('id',Session::getInstance()->getUser()->id);

            $statement->execute();
        }
        $view = new View();
        $view->render('edit', ["message" => $msg]);
    }

    public function updatePass()
    {
        $new_pass = filter_input(INPUT_POST, 'new_pass', FILTER_SANITIZE_STRING );
        $new_pass_conf = filter_input(INPUT_POST, 'new_pass_conf', FILTER_SANITIZE_STRING );

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
        $db = Db::connect();
        $statement = $db->prepare("SELECT * FROM post");


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

        $view = new View();

        $view->render('view', [
            "post" => Post::find($post)
        ]);

    }


    public function like($post)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into likes (post,user) values (:post,:user)");
        $statement->bindValue('post', $post);
        $statement->bindValue('user', Session::getInstance()->getUser()->id);
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
        $view = new View();
        $view->render('index', [
            "posts" => $posts
        ]);
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