<div id="footer" class="small">
    <div class="holder">

        <div class="floatleft" id="footer_left">

		<span>v<?php
            $ver = $db->get_option('current_version');
            echo $ver;
        ?>

        </span>

            <span>
                <?php echo format_date(current_date(), 'D, M jS, Y @ H:ia'); ?>
            </span>

            <?php

            $last_cron = $db->get_option('cron_last_run');
            $cron_alerts = $db->get_option('cron_alerts');

            if ($cron_alerts > 0) {
                ?>
                <span><a href="index.php?l=feed&filters[]=error||method||like||"><img src="imgs/icon-warning.png" width="16" height="16" border="1" class="iconLess"/><?php echo $cron_alerts; ?> new critical alert(s) detected.</a></span>
                <?php
            }
            ?>

            <a href="<?php echo PP_URL; ?>/admin/cp-cron/index.php" target="_blank">
            <?php
            if (empty($last_cron)) {
                ?>

                <span><img src="imgs/icon-warning-big.png" width="16" height="16" border="1" class="iconLess"/>Cron has not run</span>

            <?php

            } else {
                $dif = strtotime(current_date()) - strtotime($last_cron);
                if ($dif > 86400) {
                    ?>

                    <span><img src="imgs/icon-warning-big.png" width="16" height="16" border="1" class="iconLess"/>Cron has not run in over a day</span>

                <?php

                } else {
                    ?>

                    <span title="<?php echo 'Completed in ' . $db->get_option('cron_time') . ' seconds.'; ?>"><img
                            src="imgs/icon-save.png" width="16" height="16" border="1" class="icon"
                            title="Last ran <?php echo format_date($db->get_option('cron_last_run'), 'Y/m/d H:i:s'); ?>"/>Cron Job</span>

                <?php

                }

            }
            ?>
            </a>
            <a href="<?php echo PP_URL; ?>/admin/cp-cron/emailing.php" target="_blank">
            <?php

            $email_queue_last_sent = $db->get_option('email_queue_last_sent');

            if (empty($last_cron)) {
                ?>

                <span><img src="imgs/icon-warning-big.png" width="16" height="16" border="1" class="icon"/>E-mail queue has not been sent</span>

            <?php

            } else {
                $dif = strtotime(current_date()) - strtotime($email_queue_last_sent);
                if ($dif > 3600) {
                    ?>

                    <span><img src="imgs/icon-warning-big.png" width="16" height="16" border="1" class="icon"/>E-mail queue has not sent in over a day</span>

                <?php

                } else {
                    ?>

                    <span><img src="imgs/icon-save.png" width="16" height="16" border="1" class="icon"
                               title="<?php echo format_date($email_queue_last_sent, 'Y/m/d H:i:s'); ?>"/>E-mail Queue</span>

                <?php

                }

            }

            ?>
            </a>

        </div>

        <div class="floatright" id="footer_right">

            <span><a href="https://www.castlamp.com/" target="_blank">Castlamp</a></span>

            <span><a href="https://www.zenbership.com/Legal/License" target="_blank">License</a></span>

            <span><a href="null.php" onclick="return print();"><img src="imgs/icon-print.png" width="16" height="16" border="1" class="iconLess"/></a></span>
            
            <span><a href="http://documentation.zenbership.com/" target="_blank"><img src="imgs/icon-documentation.png" width="16" height="16" border="1" class="iconLess"/></a></span>

            <span><a href="#" target="_blank"><img src="imgs/icon-new-window.png" width="16" height="16" border="1" class="iconLess"/></a></span>
        </div>

        <div class="clear"></div>

    </div>
</div>

<div id="feed_bottom" onclick="return toggle_feed();">
    <div id="feed_bottom_inner">
        <img width="24" height="24" id="noticeImg" src="imgs/icon-feed-off.png" border="0" alt="Activity Feed" title="Activity Feed" />
    </div>
</div>

<!--
<script type="text/javascript">
    $(document).ready(function() {
        $.getJSON( "cp-functions/getAlerts.php", function(data) {
            console.log(data);
        });
    });
</script>


// Add a "markSeen" method.
// Add a "delete" method.
// Add a "delay" method.
-->

</body>
</html>
