<?php


/**
 * Send an SMS.
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
// Check permissions and employee
$perm     = 'sms-send';
$employee = $admin->check_employee($perm);
$task_id  = $db->start_task($perm, 'staff', $_POST['user_id'], $employee['username']);
$sms      = new sms;
$reply    = $sms->prep_sms($_POST['user_id'], $_POST['user_type'], $_POST['message'], $_POST['mediaUrl']);
if ($reply != '1') {
    $task = $db->end_task($task_id, '0', 'SMS failed.');
    echo "0+++SMS failed.";
    exit;
} else {
    $task = $db->end_task($task_id, '1');
    echo "1+++SMS sent.";
    exit;
}
