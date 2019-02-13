<?php
class ApiController
{

    public function new_post()
    {

        $data = $_POST;

        $indexController = new IndexController();
        $savePost = $indexController->newPost($data);

        $this->renderPost($savePost);

        die();

    }

    private function renderPost($json)
    {
        $dataAll = json_decode($json);
        $data = $dataAll->data;
        ?>
        <div class="card single-post-card">
            <div class="card-body">
                <div class="card-title post-title">
                    <cite><?= htmlspecialchars($data->user) ?></cite>
                    <?php echo $data->date ?>
                    <br><br>
                    <?= htmlspecialchars($data->content) ?> </a>

                    <br>

                    <hr>
                </div>

                <div class="card-text">
                    <button class="btn btn-link" type="button" data-toggle="collapse"
                            data-target="#comments<?php echo $data->id ?>" aria-expanded="false"
                            aria-controls="collapseExample">
                        Show comments
                    </button>

                    <div class="collapse" id="comments<?php echo $data->id ?>">
                        <div class="card card-body">
                            <?php foreach ($data->comments as $comment): ?>
                                <p style="margin-left: 20px;">
                                    <cite><?= htmlspecialchars($comment->user) ?></cite>
                                    <?php echo $comment->date ?><br/>
                                    <?php echo $comment->content ?>
                                    <?php if ($data->admin === '1'): ?>
                                        <a href="<?php echo App::config('url') ?>admin/deleteComment/<?php echo $comment->id ?>">Delete
                                            Comment</a>
                                    <?php endif; ?>

                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <br>
                    <?php if (Session::getInstance()->isLoggedIn()): ?>
                        <a href="<?php echo App::config('url') ?>admin/like/<?php echo $data->id ?>">Like</a>
                    <?php endif; ?>

                    (<?php echo $data->likes ?> likes)

                    <a href="<?php echo App::config('url') ?>admin/report/<?php echo $data->id ?>">Report</a>
                    <?php echo $data->reports->reports ?>
                    <?php if (Session::getInstance()->getUser()->id == $data->userid || $data->admin === '1'): ?>
                        <a href="<?php echo App::config('url') ?>admin/hide/<?php echo $data->id ?>">Hide</a>
                        <?php if ($data->hidden === "1"): ?>
                            (hidden)
                        <?php endif; ?>
                    <?php endif; ?>
                    <br>
                    <hr>
                    Tags:
                    <?php foreach ($data->tags as $tag): ?>

                        <a href="<?php echo App::config('url') ?>admin/tagSearch/<?php echo $tag->id ?>">
                            <cite><?= htmlspecialchars($tag->content) ?></cite></a>

                        <?php if ($data->admin === '1'): ?>
                            <a href="<?php echo App::config('url') ?>admin/removeTag/<?php echo $tag->id . ',' . $data->id ?>">(remove
                                tag)</a> &nbsp;&nbsp;&nbsp;
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <hr>

                </div>
                <div class="see-more">
                    <a href="<?php echo App::config('url') ?>Index/view/<?= $data->id ?>"
                       class="btn btn-primary">See more</a
                </div>


            </div>
        </div>
        </div>

        <?php
    }

    public function like()
    {

        $id = $_POST['data'];
        $adminController = new AdminController();
        $likes = $adminController->like($id);

        $dataAll = json_decode($likes);
        $data = $dataAll->data;
       $likesNum= $data->likes;
       echo $likesNum;

       die();

    }
    public function new_comment($id){

        $content = $_POST['content'];
        $postId = $id;

        $dataArray= [
          'content'=>$content,
          'id' => $postId
        ];
        $adminController = new AdminController();
        $comment = $adminController->comment($dataArray);

        $this->render_comment($comment);

        die();

    }

    private function render_comment($json)
    {
        $dataAll = json_decode($json);
        $data = $dataAll->data->comments;
        $user =$dataAll->data->admin;
        $id = $dataAll->data->id;

        ?>

        <div class="card card-body" >
            <?php foreach ($data as $comment): ?>
                <p style="margin-left: 20px;">
                    <cite><?= htmlspecialchars($comment->user) ?></cite>
                    <?php echo $comment->date ?><br/>
                    <?php echo $comment->content ?>
                    <?php if ($user === '1'): ?>
                        <a href="<?php echo App::config('url') ?>admin/deleteComment/<?php echo $comment->id ?>">Delete
                            Comment</a>
                    <?php endif; ?>

                </p>
            <?php endforeach; ?>
            <?php if (Session::getInstance()->isLoggedIn()): ?>

                <form class="js-new-comment" method="post" action="<?php echo $id ?>">

                    <div class="form-group">
                        <label for="content">New comment</label>
                        <input id="content" name="content">
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>

                </form>
            <?php endif; ?>
        </div>
        <?php
    }


}