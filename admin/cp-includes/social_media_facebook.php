<?php

// Get contacts and members assigned

// to this employee

if (!empty($_GET['code'])) {
    $code = $_GET['code'];

} else {
    $code = '';

}

$smedia = new socialmedia('facebook', $code);


if (!empty($_GET['page']) && $_GET['page'] > 0) {
    $page = $_GET['page'];

} else {
    $page = '1';

}

$last = $page - 1;

$next = $page + 1;

?>



<div id="topblue" class="fonts small">
    <div class="holder">

        <div class="floatright" id="tb_right">

            <span><a href="index.php?l=social_media_facebook&page=<?php echo $last; ?>">&laquo; Previous</a></span>

            <span class="div">|</span>

            <span><a href="index.php?l=social_media_facebook&page=<?php echo $next; ?>">More &raquo;</a></span>

        </div>

        <div class="floatleft" id="tb_left">

            <span><b>Facebook</b></span>

            <span class="div">|</span>

        <span id="innerLinks">

            <a href="null.php" onclick="return popup('facebook');">Manage Facebook Account</a>

        </span>

        </div>

        <div class="clear"></div>

    </div>
</div>


<div id="mainsection">
    <div class="pad24">


        <ul id="tweet_cloud">

            <?php

            $together = '';

            $cur = 0;

            $smedia->fb_connect();

            $users = $smedia->get_facebook_users($page);

            while ($row = $users->fetch()) {
                $cur++;
                $utype = $smedia->determine_user_type($row);
                $fb_id = $smedia->fb_id($row['facebook']);
                if (!empty($fb_id)) {
                    $posts = $smedia->fb_graph($fb_id, 'posts', 'limit=4');
                    //pa($posts);
                    foreach ($posts->data as $aPost) {
                        // pa($aPost);
                        $together .= '<li>' . $smedia->format_fb_post($aPost, $utype['id'], $utype['type']) . '</li>';

                    }

                }

            }


            if ($cur == 0) {
                echo "<li class=\"weak\">Nothing to display.</li>";

            } else {
                echo $together;

            }

            ?>

        </ul>


    </div>
</div>

