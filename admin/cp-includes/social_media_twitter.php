<?php

// Get contacts and members assigned

// to this employee

$smedia = new socialmedia();


if (!empty($_GET['page']) && $_GET['page'] > 0) {
    $page = $_GET['page'];

} else {
    $page = '1';

}

$last = $page - 1;

$next = $page + 1;

$get_all = $smedia->get_tweet_feed($page);



?>



<div id="topblue" class="fonts small">
    <div class="holder">

        <div class="floatright" id="tb_right">

            <span><a href="index.php?l=social_media_twitter&page=<?php echo $last; ?>">&laquo; Previous</a></span>

            <span class="div">|</span>

            <span><a href="index.php?l=social_media_twitter&page=<?php echo $next; ?>">More &raquo;</a></span>

        </div>

        <div class="floatleft" id="tb_left">

            <span><b>See Who's Tweeting</b></span>

            <span class="div">|</span>

        <span id="innerLinks">

            <a href="null.php" onclick="return popup('twitter');">Manage Twitter Account</a>

        </span>

        </div>

        <div class="clear"></div>

    </div>
</div>


<div id="mainsection">
    <div class="pad24">


        <ul id="tweet_cloud">

            <?php

            // Loop those contacts and members,

            // and create an array for tweets.

            $together = '';

            $limit = 200;

            $cur = 0;

            while ($row = $get_all->fetch()) {
                $utype = $smedia->determine_user_type($row);
                $cur++;
                $twitter_username = $smedia->get_twitter_username($row['twitter']);
                $tweets           = $smedia->get_tweets($twitter_username, '2');
                if (!empty($tweets)) {
                    foreach ($tweets as $entry) {
                        $together .= '<li>' . $smedia->format_tweet($entry, $utype['id'], $utype['type']) . '</li>';

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

