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
$admin = new admin;
$type  = 'edit';
$task  = 'update_staff_account-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Check current password
$check_pass = $db->check_password($_POST['current_password'], $employee['salt'], $employee['password']);
if ($check_pass != '1') {
    echo "0+++Current password is incorrect.";
    exit;

}
// New passwords match?
$where_add = '';
if (!empty($_POST['new_password'])) {
    if ($_POST['new_password'] != $_POST['repeat_password']) {
        echo "0+++New passwords didn't match.";
        exit;

    }
    $user  = new user;
    $check = $user->check_pwd_strength($_POST['new_password'], 'number');
    if ($check <= $db->get_option('required_password_strength')) {
        echo "0+++New password is not strong enough.";
        exit;

    }
    $salt      = $db->generate_salt();
    $pass      = $db->encode_password($_POST['new_password'], $salt);
    $where_add = ",`password`='" . $db->mysql_cleans($pass) . "',`salt`='" . $db->mysql_cleans($salt) . "'";

}
// Primary fields for main table
$primary          = array();
$ignore           = array('id', 'edit', 'current_password', 'new_password', 'repeat_password');
$query_form       = $admin->query_from_fields($_POST, $type, $ignore, $primary);
$query_form['u2'] = $where_add . $query_form['u2'];
if (!empty($query_form['u2'])) {
    $q1 = $db->update("

        UPDATE `ppSD_staff`

        SET " . ltrim($query_form['u2'], ',') . "

        WHERE `id`='" . $db->mysql_clean($employee['id']) . "'

        LIMIT 1

    ");

}
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Account Updated';
echo "1+++" . json_encode($return);
exit;



