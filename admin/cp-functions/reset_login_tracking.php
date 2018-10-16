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

// page
// display
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';
} else {
    $type = 'add';
}
$task = 'history-' . $type;
// Check permissions and employee
$employee  = $admin->check_employee($task);
$task_id   = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

$user      = new user;
$data      = $user->get_user($_POST['id']);

$ownership = new ownership($data['data']['owner'], $data['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;
}

$db->run_query("
    UPDATE ppSD_sessions
    SET ended='" . current_date() . "', ended_by='2'
    WHERE member_id='" . $db->mysql_clean($_POST['id']) . "'
");

$db->run_query("
    UPDATE ppSD_logins
    SET ip = CONCAT(ip, 'x')
    WHERE member_id='" . $db->mysql_clean($_POST['id']) . "'
    ORDER BY `date` DESC
    LIMIT 5
");

$return               = array();
$return['show_saved'] = 'Login-related sessions have been closed and reset.';
$task                 = $db->end_task($task_id, '1');

echo "1+++" . json_encode($return);
exit;



