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
if ($_POST['edit'] == '1') {
    $type = 'edit';
} else {
    $type = 'add';
}
$task = 'member-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$user     = new user;

// Primary fields for main table
if ($type == 'edit') {
    $data      = $user->get_user($_POST['id']);
    $ownership = new ownership($data['data']['owner'], $data['data']['public']);
    if ($ownership->result != '1') {
        echo "0+++" . $ownership->reason;
        exit;
    }
    $make = $user->edit_member($_POST['id'], $_POST);
}
else {
    if ($_POST['notify'] != '1') {
        $skip_email = '1';

    } else {
        $skip_email = '0';

    }
    $mein = array(
        'member'      => $_POST,
        'content'     => '',
        'newsletters' => ''
    );
    $make = $user->create_member($mein, $skip_email, 'email_membership_created');
    // $add = add_history('member_added',$employee['id'],$_POST['id'],'2');
}

// Re-cache
$data                  = $user->get_user($_POST['id'], '', '1');
$content               = $data['data'];
$return                = array();
$table_format          = new table('member', 'ppSD_members');
$return                = array();
$return['close_popup'] = '1'; // For quick add
if ($type == 'add') {
    $return['load_slider']      = array(
        'page'    => 'member',
        'subpage' => 'view',
        'id'      => $content['id'],
    );
    $return['show_saved']       = 'Created';
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;

} else {
    $return['show_saved'] = 'Updated';
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
}
$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;



