<?php

class Session
{
    private static $instance;
    private function __construct()
    {
        session_start();
    }

    public function login($user)
    {
        
        $_SESSION['is_logged_in'] = $user;
    }

    public function logout()
    {
        unset($_SESSION['is_logged_in']);
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['is_logged_in']) ? true : false;
    }
    
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public  function getUser(){
       return isset($_SESSION['is_logged_in']) ? $_SESSION['is_logged_in'] : false;
    }

    public function check($id)
    {
        $post = Post::find($id);
        $check = false;
        $userid = Session::getInstance()->getUser()->id;
        $reports = $post->getReports()->reports;
        $hidden = $post->getHidden();
        $postuserId = $post->getUserid();

        if ($userid !== $postuserId && $reports === '5') {
            $check = false;
        } else {
            if ($userid !== $postuserId && $hidden === '1') {
                $check = false;
            } else {
                $check = true;
            }
        }

        return $check;
    }


}