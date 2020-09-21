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
$task = 'home_widgets-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$data     = $_POST;
unset($data['id']);
unset($data['edit']);
$user_list = array();
foreach ($data as $name => $widget) {
    if (!empty($widget['pin'])) {
        $ffields = array();
        if (!empty($widget['fields'])) {
            foreach ($widget['fields'] as $fld => $on) {
                $ffields[] = $fld;

            }

        }
        $user_list[] = $name;
        if (empty($widget['options'])) {
            $put_opts = '';

        } else {
            $final_options = array(
                'limit'      => $widget['options']['limit'],
                'graph'      => $widget['options']['graph'],
                'list'       => $widget['options']['list'],
                'unit'       => $widget['options']['timeframe']['unit'],
                'increments' => $widget['options']['timeframe']['number'],
                'fields'     => $ffields,
            );
            $put_opts      = serialize($final_options);

        }
        // Update the option
        $key = 'employee_widget-' . $employee['id'] . '-' . $name;
        $db->update_eav('employee', $key, $put_opts);

    }

}
$akey = 'homepage_widgets-' . $employee['id'];
$db->update_eav('employee', $akey, serialize($user_list));
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
$return['reload']      = '1';
echo "1+++" . json_encode($return);
exit;

