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
$admin = new admin;
$task  = 'event_duplicate';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$event    = new event;
$data     = $event->get_event($_POST['id']);
if ($employee['permissions']['admin'] != '1' && $data['data']['owner'] != $employee['id']) {
    echo "You are not permitted to duplicate this event.";
    exit;

} else {
    // Event
    $new_id = $event->duplicate($_POST['id'], $data['data']['name']);

}
$task = $db->end_task($task_id, '1');
// Return Cell
$history     = new history($new_id, '', '', '', '', '', 'ppSD_events');
//$return_cell = $history->{'table_cells'};

$table_format  = new table('event', 'ppSD_events');
$return_cell = $table_format->render_cell($history->final_content);

echo "1+++table_append+++" . $return_cell;
exit;



