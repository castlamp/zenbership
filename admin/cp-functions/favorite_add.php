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
/**
 * If adding, ID is not used. "user_id" is sent.
 * If editing, ID is the id of the item.

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task  = 'favorite-' . $_POST['type'];
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Find
$check_exist = $admin->check_favorite($employee['id'], $_POST['mtype'], $_POST['id']);
// Primary fields for main table
$return = array();
if ($check_exist <= 0) {
    if ($_POST['mtype'] == 'member') {
        $member = new user;
        $data   = $member->get_user($_POST['id']);
        $mid    = $data['data']['id'];
        $mtype  = 'member';
        $ref    = $data['data']['username'];

    } else if ($_POST['mtype'] == 'contact') {
        $contact = new contact;
        $data    = $contact->get_contact($_POST['id']);
        $mid     = $data['data']['id'];
        $mtype   = 'contact';
        $ref     = $data['data']['last_name'] . ', ' . $data['data']['first_name'];

    } else if ($_POST['mtype'] == 'account') {
        $account = new account;
        $data    = $account->get_account($_POST['id']);
        $mid     = $data['id'];
        $mtype   = 'account';
        $ref     = $data['name'];

    } else {
        echo "0+++Item type is not supported.";
        exit;

    }
    if (empty($mid)) {
        echo "0+++Could not find selected item.";
        exit;

    }
    $q1                   = $db->insert("

        INSERT INTO `ppSD_favorites` (

            `date`,

            `user_id`,

            `user_type`,

            `owner`,

            `ref_name`

        )

        VALUES (

          '" . current_date() . "',

          '" . $db->mysql_clean($mid) . "',

          '" . $db->mysql_clean($mtype) . "',

          '" . $db->mysql_clean($employee['id']) . "',

          '" . $db->mysql_clean($ref) . "'

        )

    ");
    $return['show_saved'] = 'Added to favorites.';
    $return['append']     = array(
        'id'   => 'favorite_list',
        'data' => $admin->render_favorite($q1),
    );
    $return['image_src']  = array(
        'favorite-button-' . $mid => PP_URL . '/admin/imgs/icon-fav-on.png',
    );

} else {
    $del                    = $db->delete("

        DELETE FROM

            `ppSD_favorites`

        WHERE

            `user_id`='" . $db->mysql_clean($_POST['id']) . "' AND

            `owner`='" . $db->mysql_clean($employee['id']) . "'

        LIMIT 1

    ");
    $return['show_saved']   = 'Removed from favorites.';
    $return['remove_cells'] = array(
        'not_sure' => 'favorite-' . $_POST['id'],
    );
    $return['image_src']    = array(
        'favorite-button-' . $_POST['id'] => PP_URL . '/admin/imgs/icon-fav-off.png',
    );

}
$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;
