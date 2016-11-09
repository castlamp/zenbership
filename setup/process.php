<?php

//error_reporting(E_ALL);
ini_set('display_errors', 0);
error_reporting(0);

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
 * @license     GNU General Public License v3.0
 * @link        http://www.gnu.org/licenses/gpl.html
 * @date        2/25/13 2:55 PM
 * @version     v1.0
 * @project
 */

require "assets/functions.php";

$version = installed_version();

// ----------------------------

$path = str_replace('/setup','',dirname(__FILE__));
$exp = explode('/',$path);
$folder_name = array_pop($exp);
$base_path = implode('/',$exp);
$url = current_url();

// ----------------------------

if (! is_writable($path . '/admin/sd-system')) {
            echo "admin/sd-system not writable. Could not proceed.";
            exit;
}

// ----------------------------

$DBH = new PDO("mysql:host=" . $_POST['mysql']['host'] . ";dbname=" . $_POST['mysql']['db'], $_POST['mysql']['user'], $_POST['mysql']['pass']);


// ----------------------------

$domain = str_replace('www.','',$_SERVER['SERVER_NAME']);

$date_change = time() + ($_POST['hour_change'] * 3600);
$date = date('Y-m-d H:i:s',strtotime($date_change));

$site_name = $_POST['site_name'];

//$company_address = $_POST['company_address'];
$company_address = '';
$company_url = $_POST['company_url'];
$company_email = $_POST['company_email'];
$company_name = $_POST['company_name'];
$company_logo = $_POST['company_logo']; // http://www.castlamp.com/imgs/logo.png
$stat_opt_in = (! empty($_POST['stat_opt_in'])) ? 1: 0;
// $company_contact = $_POST['company_contact_line']; // <b>Phone:</b> 123-123-1234<br /><b>E-Mail:</b> info@castlamp.com<br /><b>Fax:</b> 999-123-1234<br /><b>Online:</b> http://www.castlamp.com/';
$company_contact = '';
$update_array = '';

// ----------------------------

require "../admin/cp-classes/db.class.php";
$db = new db;

$salt = uniqid() . md5(uniqid()) . md5(rand(100,999999999));
$salt1 = md5(uniqid() . md5(uniqid()) . md5(rand(100,999999999)));

$saltfile = "<?php\n";
$saltfile .= "define('SALT','$salt');\n";
$saltfile .= "define('SALT1','$salt1');\n";

if (is_writable($path . '/admin/sd-system')) {
    $step1 = '';
    $fh = fopen($path . '/admin/sd-system/salt.php', 'w');
    fwrite($fh, $saltfile);
    fclose($fh);
    define('SALT',$salt);
    define('SALT1',$salt1);
} else {
    echo "admin/sd-system not writable. Could not proceed.";
    exit;
}

$admin_pass_salt = $db->generate_salt();
$admin_pass_encoded = $db->encode_password($_POST['admin']['pass'],$admin_pass_salt);

$emp_name = $_POST['admin']['first_name'] . ' ' . $_POST['admin']['last_name'];
$emp_first_name = $_POST['admin']['first_name'];
$emp_last_name = $_POST['admin']['last_name'];

// ----------------------------

$use_url = $url;

require "mysql/create.php";
require "mysql/inserts.php";
foreach ($create as $item) {
    $STH = $DBH->prepare($item);
    $STH->execute();
}
foreach ($inserts as $item) {
    $STH = $DBH->prepare($item);
    $STH->execute();
}

// ----------------------------

//$use_url = str_replace('http://','//',$url);
//$use_url = str_replace('https://','//',$use_url);

$config = "<?php\n";
$config .= "define('PP_BASE_PATH','" . $base_path . "');\n";
$config .= "define('PP_PATH','" . $path . "');\n";
$config .= "define('PP_URL','" . $use_url . "');\n";
$config .= "define('PP_ADMINPATH','" . $path . "/admin');\n";
$config .= "define('PP_ADMIN','" . $use_url . "/admin');\n";
$config .= "define('PP_PREFIX','ppSD_');\n";
$config .= "define('PP_MYSQL_HOST','" . $_POST['mysql']['host'] . "');\n";
$config .= "define('PP_MYSQL_DB','" . $_POST['mysql']['db'] . "');\n";
$config .= "define('PP_MYSQL_USER','" . $_POST['mysql']['user'] . "');\n";
$config .= "define('PP_MYSQL_PASS','" . addslashes($_POST['mysql']['pass']) . "');\n";
//$config .= "define('COMPANY','" . $_POST['company_name'] . "');\n";
//$config .= "define('COMPANY_EMAIL','" . $_POST['company_email'] . "');\n";
//$config .= "define('COMPANY_URL','" . $_POST['company_url'] . "');\n";
//$config .= "define('ZEN_PERFORM_TESTS','0');\n";
$config .= "define('ZEN_SECRET_PHRASE','');\n";
$config .= "define('ZEN_HIDE_DEBUG_TIME', false);\n";
$config .= "define('DISABLE_CAPTCHA', false);\n";
$config .= "define('DISABLE_PERFORMANCE_BOOSTS', false);\n";

$config .= "require PP_ADMINPATH . \"/sd-system/loader.php\";";

if (is_writable($path . '/admin/sd-system')) {
    $step1 = '';
    $fh = fopen($path . '/admin/sd-system/config.php', 'w');
    fwrite($fh, $config);
    fclose($fh);
} else {
    $step1 = '<li>Create config.php (see right)</li>';
}

// ----------------------------
//   Secure key folders

@secure_folder($path . '/admin/exports');
@secure_folder($path . '/admin/attachments');

// ----------------------------
//   Delete unwanted files

@unlink($path . '/admin/sd-system/config.sample.php');
@unlink($path . '/admin/sd-system/salt.sample.php');
@unlink($path . '/admin/sd-system/license.sample.php');


// ----------------------------
//   Complete Process

include "assets/header.php";
?>

    <div class="col50l">

        <fieldset class="green">
            <legend>Remaining Steps</legend>

            <p class="desc">Your database structure was successfully established! You will now need to manually complete the following steps:</p>

            <ul class="form">
                <?php echo $step1; ?>
                <li>Delete the "setup" folder</li>
                <li>Set permissions on the "admin/sd-system" folder to "755".</li>
                <li>Set permissions on all files within the "admin/cp-cron" folder to "755".</li>
                <li>Create the cron jobs (commands to the right) from your website's control panel.</li>
            </ul>

        </fieldset>

    </div>
    <div class="col50r">

        <fieldset class="blue">
            <legend>config.php</legend>

            <?php
            if (empty($step1)) {
                echo "<p class=\"desc good\">Your config.php file was successfully created. No further action is required.</p>";
            } else {
            ?>
            <p class="desc bad">The installer was not able to create the config.php file for you. This file is vital to the functioning of the program. Please create the following file named <b>config.php</b> and place it within the "admin/sd-system" folder.</p>
            <textarea style="width:100%;height:600px;"><?php echo $config; ?></textarea>
            <?php
            }
            ?>
        </fieldset>

        <fieldset class="red">
            <legend>Cron Jobs</legend>
            <p class="desc">*/15 * * * * php <?php echo $path; ?>/admin/cp-cron/emailing.php</p>
            <p class="desc">0 */2 * * * php <?php echo $path; ?>/admin/cp-cron/index.php</p>
            <!--<p class="desc">0 0 */1 * * php <?php echo $path; ?>/admin/cp-cron/backup.php</p>-->
        </fieldset>

    </div>
    <div class="clear"></div>

</div>

<div class="submit">
    <input type="button" onclick="window.location='<?php echo $url . '/admin'; ?>';" value="Access Administrative Control Panel" />
</div>

</form>

<?php
include "assets/footer.php";
exit;
