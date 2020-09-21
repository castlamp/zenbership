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
$task = 'widgets-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$primary    = array();
$ignore     = array('id', 'edit', 'options');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

if (!empty($_POST['options'])) {
    $opts = serialize($_POST['options']);

} else {
    $opts = '';

}
if ($type == 'edit') {

    $update_set2 = substr($query_form['u2'], 1);
    $q           = $db->update("
        UPDATE `ppSD_widgets`
        SET $update_set2
        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    // Update options
    foreach ((array)$_POST['options'] as $name => $value) {
        $db->update_option($name, $value);

    }
    $id = $_POST['id'];

} else {

    $id    = str_replace(' ', '_', $_POST['name']);
    $id    = preg_replace("/[^A-Za-z0-9_]/", '', $id);
    $id    = strtolower(substr($id, 0, 25));
    $count = $db->get_array("
        SELECT COUNT(*)
        FROM `ppSD_widgets`
        WHERE `id`='" . $db->mysql_clean($id) . "'
    ");
    if ($count['0'] > 0) {
        echo "0+++Duplicate widget ID. Please rename this widget and try again.";
        exit;

    } else {
        $if2 = substr($query_form['if2'], 1);
        $iv2 = substr($query_form['iv2'], 1);
        $q   = $db->insert("
            INSERT INTO `ppSD_widgets` (`active`,`id`,$if2)
            VALUES ('1','" . $id . "',$iv2)
        ");

    }

}

$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_widgets';
$scope                 = 'widget';
$history               = new history($id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Widget';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Widget';

}
echo "1+++" . json_encode($return);
exit;
