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
 * Create Event
 * From admin

 */
// Load the basics
require "../sd-system/config.php";
$task = 'event_reminders';
// Check permissions and employee
$admin    = new admin;
$employee = $admin->check_employee($task);
// EVENTS
$event     = new event;
$data      = $event->get_event($_POST['id']);
$ownership = new ownership($data['data']['owner'], $data['data']['public']);
if ($ownership->result != '1') {
    echo "0+++You cannot alter this event.";
    exit;

}
$task_id = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if (!empty($_POST['new']['timeframe']['number'])) {
    $timeframe = $admin->construct_timeframe($_POST['new']['timeframe']['number'], $_POST['new']['timeframe']['unit']);
    if ($_POST['new']['when'] == 'before') {
        $tfsec    = timeframe_to_seconds($timeframe);
        $sub      = strtotime($data['data']['starts']) - $tfsec;
        $use_date = date('Y-m-d H:i:s', $sub);
        if ($use_date <= current_date()) {
            echo "0+++Cannot create reminder message: scheduled date would be in the past ($use_date).";
            exit;
        }
    } else {
        $tfsec    = timeframe_to_seconds($timeframe);
        $sub      = strtotime($data['data']['ends']) + $tfsec;
        $use_date = date('Y-m-d H:i:s', $sub);
        if ($use_date <= current_date()) {
            echo "0+++Cannot create followup message: date is before today.";
            exit;
        }
    }
    if ($_POST['new']['sms'] == '1' && empty($_POST['new']['custom_message'])) {
        echo "0+++A message is required for SMS messages.";
        exit;

    }
    $q1 = $db->insert("

        INSERT INTO `ppSD_event_reminders` (

          `event_id`,

          `send_date`,

          `timeframe`,

          `when`,

          `sms`,

          `template_id`,

          `custom_message`

        )

        VALUES (

          '" . $db->mysql_clean($_POST['id']) . "',

          '" . $db->mysql_clean($use_date) . "',

          '" . $db->mysql_clean($timeframe) . "',

          '" . $db->mysql_clean($_POST['new']['when']) . "',

          '" . $db->mysql_clean($_POST['new']['sms']) . "',

          '" . $db->mysql_clean($_POST['new']['template_id']) . "',

          '" . $db->mysql_clean($_POST['new']['custom_message']) . "'

        )

    ");

}

if (! empty($_POST['existing'])) {
    foreach ($_POST['existing'] as $theId => $id) {
        if (!empty($id['delete']) && $id['delete'] == '1') {
            $del = $db->delete("
                DELETE FROM `ppSD_event_reminders`
                WHERE `id`='" . $db->mysql_clean($theId) . "'
                LIMIT 1
            ");

        } else {
            $check = $id['send_date'] . ' 00:00:00';
            if ($check < current_date()) {
                echo "0+++Date cannot be before today.";
                exit;

            }
            if ($id['sms'] == '1' && empty($id['custom_message'])) {
                echo "0+++A message is required for SMS messages.";
                exit;

            }
            $up = $db->update("
            UPDATE `ppSD_event_reminders`
            SET
                `sms`='" . $db->mysql_clean($id['sms']) . "',
                `template_id`='" . $db->mysql_clean($id['template_id']) . "',
                `send_date`='" . $db->mysql_clean($id['send_date']) . "',
                `custom_message`='" . $db->mysql_clean($id['custom_message']) . "'
            WHERE `id`='" . $db->mysql_clean($theId) . "'
            LIMIT 1
        ");

        }

    }
}

$task                     = $db->end_task($task_id, '1');
$return                   = array();
$return['show_saved']     = 'Updated reminders.';
$return['redirect_popup'] = array(
    'page'   => 'event_reminders',
    'fields' => 'id=' . $_POST['event_id'],
);
echo "1+++" . json_encode($return);
exit;

