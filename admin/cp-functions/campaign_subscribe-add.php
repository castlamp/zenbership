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
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'campaign_subscribe-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['campaign_id'], $employee['username']);
// ----------------------------
if (empty($_POST['campaign_id'])) {
    echo "0+++Campaign ID is required.";
    exit;

}
// Get the campaign
$campaign = new campaign($_POST['campaign_id']);
$data     = $campaign->get_campaign();
if ($data['owner'] != $employee['id'] && $data['public'] != '1' && $employee['permissions']['admin'] != '1') {
    echo "0+++You don't have permission to alter this campaign.";
    exit;

}
if ($_POST['user_type'] == 'member') {
    $user = new user;
    $data = $user->get_user($_POST['member']['id']);
    if (empty($data['data']['id'])) {
        echo "0+++Could not find member.";
        exit;

    } else {
        $user_id   = $_POST['member']['id'];
        $user_type = 'member';

    }

} else if ($_POST['user_type'] == 'contact') {
    $contact = new contact;
    $data    = $contact->get_contact($_POST['contact']['id']);

    if (empty($data['data']['id'])) {
        echo "0+++Could not find contact.";
        exit;
    } else {
        $user_id   = $_POST['contact']['id'];
        $user_type = 'contact';
    }

} else {
    if (empty($_POST['new']['email']) || empty($_POST['new']['first_name']) || empty($_POST['new']['last_name'])) {
        echo "0+++E-mail, first name, and last name are all required.";
        exit;

    }
    $data      = array(
        'first_name' => $_POST['new']['first_name'],
        'last_name'  => $_POST['new']['last_name'],
        'email'      => $_POST['new']['email'],
        'owner'      => $employee['id'],
        'account'    => 'default',
    );
    $contact   = new contact;
    $add       = $contact->create($data);
    $user_id   = $add['id'];
    $user_type = 'contact';

}
$subscribe                  = $campaign->subscribe($user_id, $user_type, 'employee', $employee['id']);
$task                       = $db->end_task($task_id, '1');
$table                      = 'ppSD_campaign_subscriptions';
$history                    = new history($subscribe, '', '', '', '', '', $table);
$content                    = $history->final_content;
$table_format               = new table('campaign', $table);
$return                     = array();
$return['close_popup']      = '1';
$cell                       = $table_format->render_cell($content);
$return['append_table_row'] = $cell;
$return['show_saved']       = 'Added Subscription';

echo "1+++" . json_encode($return);
exit;
