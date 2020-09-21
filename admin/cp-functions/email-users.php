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
$task = 'email-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

$final_store_array = array();

if ($_POST['id'] == 'members') {
    $table = 'ppSD_members';
    $type = 'member';
} else {
    $table = 'ppSD_contacts';
    $type = 'contact';
}

foreach ($_POST as $name => $value) {
    if ($name == 'id' || $name == 'ext' || $name == 'edit') {
        continue;
    } else {
        $final_store_array['id'][] = array(
            'table' => $table,
            'eq'    => '=',
            'value' => $name,
            'range' => '',
        );
    }
}

$criteria = new criteria();
$id = $criteria->create($final_store_array, 'Email users', '0', 'or', $type, 'email', '0', '');

$return = array();

$return['show_saved'] = 'Loading Email';

$return['load_slider'] = array(
    'page'    => 'connect',
    'subpage' => '',
    'id'      => $id,
);

echo "1+++" . json_encode($return);
exit;





