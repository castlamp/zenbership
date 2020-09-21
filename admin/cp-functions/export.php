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

ini_set('max_execution_time', 3000);

require "../sd-system/config.php";

$crit_id = $_REQUEST['crit_id'];
$act_id = $_REQUEST['act_id'];
$delimiter = $_REQUEST['delimiter'];

if (empty($crit_id)) {
    echo "No criteria submitted.";
    exit;
}

$admin    = new admin;
$task     = 'export';
$employee = $admin->check_employee($task);

$task_id  = $db->start_task($task, 'staff', $crit_id, $employee['username']);

$export = new export($crit_id, $act_id, $delimiter);

$task = $db->end_task($task_id, '1');
exit;
