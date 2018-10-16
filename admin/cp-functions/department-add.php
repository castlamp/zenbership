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
 *

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'department-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Current departments
$depts      = explode(',', $db->get_option('departments'));
$final_list = '';
$cur        = 0;
foreach ($_POST['department'] as $current_name => $aDep) {
    $aDep = str_replace(',', '&#44;', $aDep);
    if (!empty($current_name)) {
        // Editing department name
        if ($aDep != $current_name) {
            $q = $db->update("

				UPDATE `ppSD_staff`

				SET `department`='" . $db->mysql_clean($aDep) . "'

				WHERE `department`='" . $db->mysql_clean($current_name) . "'

			");

        } // Delete department
        else if (empty($aDep)) {
            $q = $db->update("

				UPDATE `ppSD_staff`

				SET `department`=''

				WHERE `department`='" . $db->mysql_clean($current_name) . "'

			");

        }

    }
    if (!empty($aDep)) {
        $final_list .= ',' . $aDep;

    }
    $cur++;

}
$final_list            = ltrim($final_list, ',');
$q1                    = $db->update("

	UPDATE `ppSD_options`

	SET `value`='" . $db->mysql_clean($final_list) . "'

	WHERE `id`='departments'

	LIMIT 1

");
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Updated Departments';
echo "1+++" . json_encode($return);
exit;

