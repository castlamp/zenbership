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

$task = 'event_cancel';

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Get event
$event = new event;
$data  = $event->get_event($_POST['id']);


if ($_POST['inform_attendees'] == '1') {
    $attendees = $event->get_event_rsvps($_POST['id']);

    $changes = array();
    $changes['reason'] = $_POST['reason'];
    $changes['event'] = $data['data'];

    foreach ($attendees as $person) {
        $changes['rsvp'] = $person;

        $email = new email('', $person['id'], 'rsvp', '', $changes, 'event_canceled');
    }

}

$q = $db->update("
    UPDATE `ppSD_events`
    SET `status`='2'
    WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
    LIMIT 1
");

$task = $db->end_task($task_id, '1');

$return                   = array();
$return['close_popup']    = '1';
$return['close_slider']   = '1';
$return['show_saved']     = 'Event Has Been Canceled';
// $return['update_row']     = $table_format->render_cell($data, '1');

echo "1+++" . json_encode($return);
exit;
