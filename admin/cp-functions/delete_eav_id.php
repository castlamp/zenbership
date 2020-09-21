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
$task  = 'member-edit';
// Check permissions and employee
$employee  = $admin->check_employee($task);
$task_id   = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$return    = array();
$form_data = $db->get_form_submit($_POST['id']);
if ($form_data['user_type'] == 'contact') {
    $contact = new contact;
    $cdata   = $contact->get_contact($form_data['user_id']);

} else if ($form_data['user_type'] == 'rsvp') {

} else if ($form_data['user_type'] == 'member') {
    $user  = new user;
    $cdata = $user->get_user($form_data['user_id']);

}
$ownership = new ownership($cdata['data']['owner'], $cdata['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;

} else {
    $del  = $db->delete("

        DELETE FROM `ppSD_data_eav`

        WHERE `item_id`='" . $db->mysql_clean($_POST['id']) . "'

    ");
    $del1 = $db->delete("

        DELETE FROM `ppSD_form_submit`

        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

        LIMIT 1

    ");
    // li-fs[ID]
    // fs[ID]
    $return['remove_cells'] = array(
        'li-fs' . $_POST['id'],
        'fs' . $_POST['id'],
    );
    $return['show_saved']   = 'Deleted';
    $task                   = $db->end_task($task_id, '1');
    echo "1+++" . json_encode($return);
    exit;

}





