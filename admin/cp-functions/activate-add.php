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
$task = 'activate';
// Check permissions and employee
$admin    = new admin;
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$user     = new user;
$data     = $user->get_user($_POST['id']);

// Ownership
$permission = new ownership($data['data']['owner'], $data['data']['public']);
if ($permission->result != '1') {
    echo "0+++" . $permission->reason;
    exit;
}

$user->update_status($data['data']['id'], $_POST['status'], $_POST['reason'], $_POST['send_email']);
$task                   = $db->end_task($task_id, '1');
$data['data']['status'] = $_POST['status'];
$sf                     = new special_fields('member');
$sf->update_row($data['data']);
$status                 = $sf->process('status', $_POST['status']);
$return                 = array();
$return['close_popup']  = '1';
$return['update_cells'] = array('member-status-' . $data['data']['id'] => $status); // update_cells => array('cell_id' => 'cell_value', 'cell_id2' => 'cell_value2')
echo "1+++" . json_encode($return);
exit;

