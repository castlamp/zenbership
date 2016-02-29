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
require "../admin/sd-system/config.php";
$user = new user;
if (!empty($_GET['c']) && !empty($_GET['u'])) {
    $task_id = $db->start_task('activate', 'user', '', $_GET['u']);
    // Get user
    $check = $user->check_activation_code($_GET['c'], $_GET['u']);
    // Correct code?
    if ($check == '1') {
        // Activate
        $confirm = $user->confirm_email($_GET['u']);

        $indata = array(
            'member_id' => $_GET['u'],
        );
        $task = $db->end_task($task_id, '1', '', 'activate', $_GET['u'], $indata);

        // Display template
        $changes = array();
        // $task    = $db->end_task($task_id, '1', '', 'activate');
        $temp    = new template('email_confirmed', $changes, '1');
        echo $temp;
        exit;
    } else {
        $task = $db->end_task($task_id, '0', '', 'activate');
        // Display template
        $changes = array(
            'details' => $db->get_error('L014')
        );
        $temp    = new template('error', $changes, '1');
        echo $temp;
        exit;
    }
}