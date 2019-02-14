<?php

class Post
{
    private $id;

    private $content;

    private $user;

    private $date;

    private $likes;

    private $comments;

    private $userid;

    private $tags;

    private $reports;

    private $hidden;



    public function __construct($id, $content, $user, $date, $likes, $comments, $userid, $tags,$reports,$hidden)
    {
        $this->setId($id);
        $this->setContent($content);
        $this->setUser($user);
        $this->setDate($date);
        $this->setLikes($likes);
        $this->setComments($comments);
        $this->setUserid($userid);
        $this->setTags($tags);
        $this->setReports($reports);
        $this->setHidden($hidden);

    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __call($name, $arguments)
    {
        $function = substr($name, 0, 3);
        if ($function === 'set') {
            $this->__set(strtolower(substr($name, 3)), $arguments[0]);
            return $this;
        } else if ($function === 'get') {
            return $this->__get(strtolower(substr($name, 3)));
        }

        return $this;
    }

    public static function all($pageNumber)
    {
        if ($pageNumber == 0){

        }else{
            $pageNumber=($pageNumber-1)*5;
        }

        $list = [];
        $db = Db::connect();
        $statement = $db->prepare("select 
        a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date,a.user as userid,a.hidden, 
        count(c.id) as likes
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post 
        where a.date > ADDDATE(now(), INTERVAL -7 DAY) 
        group by a.id, a.content, concat(b.firstname, ' ', b.lastname), a.date 
        order by a.date desc limit 5 offset $pageNumber ");
        $statement->execute();
        foreach ($statement->fetchAll() as $post) {

            $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $comments = $statement->fetchAll();

            $statement = $db->prepare("select d.content, d.id from tags d inner join tag_relations e on d.id=e.tag_id where e.post_id=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $tags = $statement->fetchAll();

            $statement = $db->prepare("select count(post_id) as reports from report where post_id=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $reports = $statement->fetch();


            $list[] = new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, $post->userid, $tags,$reports,$post->hidden);

        }


        return $list;
    }


    public static function find($id)
    {
        $id = intval($id);
        $db = Db::connect();
        $statement = $db->prepare("select 
        a.id, a.content,a.hidden, concat(b.firstname, ' ', b.lastname) as user, a.date, a.user as userid, count(c.id) as likes 
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post 
         where a.id=:id");
        $statement->bindValue('id', $id);
        $statement->execute();
        $post = $statement->fetch();

        $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
        $statement->bindValue('id', $id);
        $statement->execute();
        $comments = $statement->fetchAll();

        $statement = $db->prepare("select d.content, d.id from tags d inner join tag_relations e on d.id=e.tag_id where e.post_id=:id ");
        $statement->bindValue('id', $id);
        $statement->execute();
        $tags = $statement->fetchAll();

        $statement = $db->prepare("select count(post_id) as reports from report where post_id=:id ");
        $statement->bindValue('id', $post->id);
        $statement->execute();
        $reports = $statement->fetch();

        return new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, $post->userid, $tags,$reports, $post->hidden);
    }

    public static function tags($id)
    {
        $id = intval($id);
        $db = Db::connect();
        $statement = $db->prepare("SELECT a.id,a.content,a.date,a.hidden, a.user as userid, (select count(id) FROM likes WHERE post =a.id ) as likes ,
                                            (select concat(firstname,' ',lastname) from user where id=a.user) as user
                                            from post a inner join tag_relations b on a.id=b.post_id  where b.tag_id=:id ");
        $statement->bindValue('id', $id);
        $statement->execute();

        foreach ($statement->fetchAll() as $post) {

            $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $comments = $statement->fetchAll();

            $statement = $db->prepare("select d.content, d.id from tags d inner join tag_relations e on d.id=e.tag_id where e.post_id=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $tags = $statement->fetchAll();

            $statement = $db->prepare("select count(post_id) as reports from report where post_id=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $reports = $statement->fetch();


            $list[] = new Post($post->id, $post->content, $post->user, $post->date, $post->likes, $comments, $post->userid, $tags,$reports,$post->hidden);

        }
        return $list;
    }

    public static function likes($id)
    {
        $likes = [];
        $id = intval($id);
        $db = Db::connect();
        $statement = $db->prepare("SELECT a.unique_likes, a.date, concat(b.firstname, ' ', b.lastname) as user from likes a inner join user b on a.user=b.id where a.post =:id ");
        $statement->bindValue('id', $id);
        $statement->execute();
        $likes=$statement->fetchAll();
        return $likes;
    }

    public static function countPosts(){
        $db = Db::connect();
        $statement = $db->prepare("SELECT count(id) as postCount from post ");
        $statement->execute();
        $postCount=$statement->fetch();
        return $postCount;

    }

    public static function comment($id)
    {
        $db = Db::connect();
        $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id and a.id= LAST_INSERT_ID() limit 1");
        $statement->bindValue('id', $id);
        $statement->execute();
        $comments = $statement->fetchAll();
        return $comments;

    }
}

