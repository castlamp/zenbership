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
$task = 'event_timeline-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Get event
$event = new event;
$data  = $event->get_event($_POST['event_id']);
// Check permissions
if ($data['data']['public'] != '1' && $data['data']['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
    echo "0+++Permission denied.";
    exit;

}
// Primary fields for main table
$table      = 'ppSD_event_timeline';
$primary    = array('');
$ignore     = array('id', 'edit', 'dud_same');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
if ($type == 'edit') {
    // Update the contact
    $update_set2 = substr($query_form['u2'], 1);
    $q           = $db->update("

		UPDATE `$table`

		SET $update_set2

		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

		LIMIT 1

	");
    $return_cell = 'close_popup';

} else {
    // Create the contact
    $insert_fields2 = substr($query_form['if2'], 1);
    $insert_values2 = substr($query_form['iv2'], 1);
    $last_id        = $db->insert("

		INSERT INTO `$table` ($insert_fields2)

		VALUES ($insert_values2)

	");
    $entry          = $event->timeline_item($last_id);
    $return_cell    = $admin->timeline_entry($entry);

}
$event->get_event($_POST['event_id'], '1');
$task                     = $db->end_task($task_id, '1');
$return                   = array();
$return['close_popup']    = '1';
$return['refresh_slider'] = '1';
if ($type == 'add') {
    $return['show_saved'] = 'Added Timeline Item';

} else {
    $return['show_saved'] = 'Updated Timeline Item';

}
echo "1+++" . json_encode($return);
exit;
