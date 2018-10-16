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
 * @license     GNU General Public License v3.0
 * @link        http://www.gnu.org/licenses/gpl.html
 * @date        3/4/13 1:51 AM
 * @version     v1.0
 * @project
 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'fb_post';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$return   = array();
$smedia   = new socialmedia();
$check    = $smedia->confirm_fb_setup();
$smedia->fb_connect();
if ($check['error'] == '1') {
    $good = array(
        'error'   => '1',
        'message' => $check['error_message'],
    );

} else {
    if (!empty($_POST['action'])) {
        $action = $_POST['action'];

    } else {
        $action = '';

    }
    if ($action == 'delete') {
        $good                   = $smedia->delete_status($_POST['id']);
        $return['remove_cells'] = array('fbpost-' . $_POST['id']);
        $return['show_saved']   = 'Status Deleted';

    } else {
        $good                     = $smedia->post_status($_POST['status']);
        $return['redirect_popup'] = array('page' => 'facebook');
        $return['show_saved']     = 'Status Posted';

    }

}
$task = $db->end_task($task_id, '1');
if ($good['error'] != '1') {
    echo "1+++" . json_encode($return);
    exit;

} else {
    echo "0+++" . $good['error_message'];
    exit;

}



