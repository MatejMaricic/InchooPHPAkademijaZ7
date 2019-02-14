<?php
/**
 * Created by PhpStorm.
 * User: matej
 * Date: 14.02.19.
 * Time: 00:10
 */

class TimelineController
{
    public function Timeline($id)
    {
        $data = Post::find($id);
        $comments = $data->getComments();

        $likes =Post::likes($id);

        $timelineContent = $this->saveInArray($comments, $likes);
        ksort($timelineContent);

        $view = new View();
        $view->render('timeline', [
            "timelineContent" => $timelineContent,
            "post" => $data
        ]);
    }

    public function saveInArray($comments, $likes)
    {
        $data = [];
        foreach ($comments as $comment)
        {
            $data[$comment->date] = $comment-> user ." commented : " .$comment->content . " at " . $comment->date ;
        }

        foreach ($likes as $like){
            $data [$like->date] = $like->user . " liked this post " . " at " . $like->date;
        }
        return $data;

    }
}