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
$task  = 'options-edit';

// Check permissions and employee
$employee = $admin->check_employee($task);
if ($employee['permissions']['admin'] != '1') {
    echo "0+++No permissions.";
    exit;
}

$task_id = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
foreach ($_POST as $key => $value) {
    if ($key != 'id' && $key != 'edit') {
        $opt_type = $db->option_type($key);
        if ($opt_type == 'timeframe') {
            $value = $admin->construct_timeframe($value['number'], $value['unit']);
        }
        $q1 = $db->update_option($key, $value);
    }
}

$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Options Updated';
echo "1+++" . json_encode($return);
exit;



