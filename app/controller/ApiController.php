<?php
/**
 * Created by PhpStorm.
 * User: matej
 * Date: 11.02.19.
 * Time: 17:54
 */

class ApiController
{

    public function newPost()
    {
        $data = $_POST;
        $indexController = new IndexController();
        $savePost = $indexController->newPost($data);



        die();
    }
}