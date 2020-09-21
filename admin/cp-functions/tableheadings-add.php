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
$task = 'tableheadings-' . $_POST['perm'] . '-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Build list
$list = '';
foreach ($_POST as $name => $value) {
    if ($name == 'ext' || $name == 'edit' || $name == 'perm') {

    } else {
        if ($name == 'id' && $value == 'x') {

        } else {
            $list .= ',' . $name;
        }
    }
}

$list = ltrim($list, ',');
// Update option
$opt_name = $_POST['perm'] . '_headings_' . $employee['id'];
$headings = $db->get_option($opt_name);
if (!empty($headings)) {
    $up = $db->update("
        UPDATE `ppSD_options`
        SET `value`='" . $db->mysql_clean($list) . "'
        WHERE `id`='" . $db->mysql_clean($opt_name) . "'
        LIMIT 1
    ");

} else {
    $up = $db->insert("
        INSERT INTO `ppSD_options` (`id`,`value`)
        VALUES ('" . $db->mysql_clean($opt_name) . "','" . $db->mysql_clean($list) . "')
    ");
}
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
$return['reload']      = '1';
echo "1+++" . json_encode($return);
exit;

