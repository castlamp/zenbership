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

// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task  = 'cancel_subscription';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$sub      = new subscription;
$data     = $sub->get_subscription($_POST['id']);
if ($data['data']['member_type'] == 'member') {
    $user      = new user;
    $user_data = $user->get_user($data['data']['member_id']);

} else {
    $contact   = new contact;
    $user_data = $contact->get_contact($data['data']['member_id']);

}
if ($user_data['data']['owner'] != $employee['id'] && $user_data['data']['public'] != '1' && $employee['permissions']['admin'] != '1') {
    echo "0+++You cannot alter a subscription for a " . $data['data']['member_type'] . " you do not own.";
    exit;

} else {

    if ($data['data']['status'] == '1') {
        /*
        $q1 = $db->update("
            UPDATE `ppSD_subscriptions`
            SET `status`='2',`cancel_date`='" . current_date() . "'
            WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
            LIMIT 1
        ");
        */
        $sub->cancel_subscription($_POST['id']);
    } else {
        $q1 = $db->update("
            UPDATE `ppSD_subscriptions`
            SET `status`='1',`cancel_date`=''
            WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
            LIMIT 1
        ");

    }

}
$data = $sub->get_subscription($_POST['id'], '1');
echo "1+++Saved";
exit;



