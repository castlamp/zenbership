<?php


/**
 * This is the same as "extend_next_action.php" but for multiple users are once.
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
$task = 'extend_next_action-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

$final_store_array = array();

if (empty($_POST['type'])) {
    $type = 'contact';
    $types = 'contacts';
} else {
    $type = 'member';
    $types = 'members';
}

$total = 0;
$updateCells = array();
$removeClass = array();

foreach ($_POST as $name => $value) {
    if ($name == 'id' || $name == 'ext' || $name == 'edit' || $name == 'type') {
        continue;
    } else {
        $total++;

        $task_id  = $db->start_task($task, 'staff', $name, $employee['username']);
        $extend = $db->update_next_action($name, $type);
        $task = $db->end_task($task_id, '1');

        $updateCells[$name . '-next_action'] = format_date($extend);
        $removeClass['td-cell-' . $name] = 'overdue';
    }
}

$return                = array();
$table_format          = new table('contact', 'ppSD_contacts');

$return['update_cells']   = $updateCells;
$return['remove_classes']   = $removeClass;
$return['show_saved'] = 'Extended next action date for ' . $total . ' ' . $types;

$task = $db->end_task($task_id, '1');

echo "1+++" . json_encode($return);
exit;
