<?php
/**
 *
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */
require "sd-system/config.php";
$version = $db->get_option('current_version');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html" lang="en" xml:lang="en">
<head>
    <title>Welcome to Zenbership... ahh!</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="author" content="Zenbership Membership Software"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/login.css" />
    <link type="text/css" rel="stylesheet" media="handheld, only screen and (max-device-width: 720px)" href="css/login_mobile.css" />
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <script src="js/jquery.js" type="text/javascript"></script>
</head>
<body>

<form action="login.php" method="post" id="login" onsubmit="return verifyLogin('login');">

    <div id="login_logo"><img src="imgs/login/logo.png" border="0" /></div>
    <div id="background"></div>
    <div id="foreground"></div>
    <div id="login_box">
        <div id="login_error" class="error_div marginbottom"<?php
        if (! empty($_GET['incode'])) {
            echo " style=\"display:block;\"";
        }
        ?>
            >
        <?php
            if (! empty($_GET['incode'])) {
                if ($_GET['incode'] == 'u01') {
                    echo 'Account unlocked.';
                }
                else if ($_GET['incode'] == 'u99') {
                    echo 'Account unlock failed.';
                }
            }
            ?></div>
        <div id="login_box_inner">
            <div id="login_pad">
                <div class="col50l">
                    <div class="field">
                        <label>Username</label>
                        <input type="text" name="username" autocorrect="off" autocapitalize="off" value="" class="home req" style="width:100%;"/>
                    </div>
                </div>
                <div class="col50r">
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" value="" class="home req" style="width:100%;"/>
                    </div>
                </div>
                <div class="clear"></div>
                <div class="field">
                    <div id="captcha_block" class="notice" style="display:none;">
                        <div class="pad20">
                            <center>
                                <img width="200" height="50" id="captchaput" class="imageout" src=""/>
                                <input type="text" name="captcha" value="" class="home" style="width:200px;"/>
                            </center>
                        </div>
                    </div>
                </div>
                <div id="remember">
                    <input type="checkbox" name="remember" value="1"/> Remember Me
                </div>
            </div>
        </div>
        <input type="submit" value="Login" class="save"/>
    </div>
    </div>
    </div>
    <div id="penguin"></div>
    <p class="links"><a href="http://documentation.zenbership.com/" target="_blank">Documentation</a> &#183; <a
            href="http://www.gnu.org/licenses/gpl-3.0.en.html"
            target="_blank">License</a><br/>v<?php echo $version; ?>
        &nbsp;&nbsp;&#183;&nbsp;&nbsp;&copy; <?php echo date('Y'); ?> <a href="http://www.castlamp.com/"
                                                                         target="_blank">Castlamp</a>.</p>
</form>

<?php
$mobile = check_mobile();
if (! $mobile) {
?>

<script type="text/javascript" src="js/jquery.bg_position.js"></script>
<script type="text/javascript">
    $(function () {
        $('#background').css({backgroundPosition: '0px 0px'});
        $('#background').animate({ backgroundPosition: "(-10000px -2000px)"}, 480000, 'linear');
        $('#foreground').css({backgroundPosition: '0px 0px'});
        $('#foreground').animate({ backgroundPosition: "(10000px -2000px)" }, 120000, 'linear');
    });
</script>
<!--<script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>-->
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>

<?php
}
?>

<script type="text/javascript" src="js/jquery.mousewheel.js"></script>
<script type="text/javascript">
    var boxes_checked = 0;
    var window_width = 0;
    var window_height = 0;
    var subtract = 143;
    var active_page = '';
    var active_act = '';
    var active_id = '';
</script>
<script type="text/javascript" src="js/admin.js"></script>
<script type="text/javascript" src="js/forms.js"></script>

</body>
</html>