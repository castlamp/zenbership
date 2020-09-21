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
// page
// display
// Load the basics
require "../sd-system/config.php";
$admin   = new admin;
$task_id = $db->start_task('login', 'staff');
// Get a staff account and check for
// a lock on the account.
$staff = $admin->get_employee($_POST['username']);
if ($staff['error'] == '1') {
    echo "0+++" . $staff['error_details'];
    exit;
}

// Continue.
if (!empty($staff['username'])) {

    // Captcha requirement?
    $check = $db->check_captcha($staff['username'], 'staff', $_POST['captcha']);
    if ($check == '2') {
        $id   = $db->issue_captcha($staff['username'], 'staff');
        $url  = PP_ADMIN . "/cp-functions/generate_captcha.php?c=" . $id;
        $task = $db->end_task($task_id, '0');
        echo "0+++captcha_in+++$url";
        exit;
    }

    // No captcha requirement
    $check_pass = $db->check_password($_POST['password'], $staff['salt'], $staff['password']);

    if ($check_pass == '1') {

        $lock = $admin->unlock_account($staff['username']);
        if (!empty($_POST['remember'])) {
            $rem = '1';
        } else {
            $rem = '0';
        }
        $get = $admin->create_session($_POST['username'], $rem);

        $task = $db->end_task($task_id, '1');

        echo "1+++redirect+++" . PP_ADMIN;
        exit;

    } else {

        $this_attempt = $staff['login_attempts'] + 1;

        if ($this_attempt > "10") {
            $lock   = $admin->lock_account($staff['username']);
            $error  = 'Too many failed login attempts: account locked for 10 minutes.';
            $action = '';
        } else if ($this_attempt == "3") {
            $id     = $db->issue_captcha($staff['username'], 'staff');
            $url    = PP_ADMIN . "/cp-functions/generate_captcha.php?c=" . $id;
            $error  = 'captcha';
            $action = $url;
        } else {
            $error  = 'Incorrect credentials.';
            $action = 'captcha_remove';
        }

        $query4 = $db->update("
   			UPDATE `ppSD_staff`
   			SET `login_attempts`='$this_attempt'
   			WHERE `username`='" . $db->mysql_clean($staff['username']) . "'
   			LIMIT 1
   		");

        $task   = $db->end_task($task_id, '0', $error);

        echo "0+++$error+++$action";
        exit;

    }

} else {

    echo "0+++Incorrect credentials.";
    exit;

}





