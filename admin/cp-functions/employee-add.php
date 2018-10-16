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
$task = 'employee-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$table      = 'ppSD_staff';
$primary    = array('');
$ignore     = array('password', 'repeat_pwd', 'id', 'edit');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

// ----------------------------
if ($type == 'edit') {
    $data = new history($_POST['id'], '', '', '', '', '', $table);
    if ($data->{'final_content'}['id'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
        echo "0+++Permission denied.";
        exit;
    }

    if (!empty($_POST['password'])) {
        $salt       = $db->generate_salt();
        $pass       = $db->encode_password($_POST['password'], $salt);
        $pass_query = "`password`='" . $db->mysql_cleans($pass) . "',`salt`='" . $db->mysql_cleans($salt) . "',";
    } else {
        $pass_query = '';
    }

    $q1 = $db->insert("
		UPDATE `ppSD_staff`
		SET " . $pass_query . ltrim($query_form['u2'],',') . "
		WHERE `id`='" . $db->mysql_cleans($_POST['id']) . "'
		LIMIT 1
	");
    $id = $_POST['id'];

} else {
    $salt = $db->generate_salt();
    $pass = $db->encode_password($_POST['password'], $salt);
    $id   = $db->insert("

		INSERT INTO `ppSD_staff` (`password`,`salt`" . $query_form['if2'] . ")

		VALUES ('" . $db->mysql_cleans($pass) . "','" . $db->mysql_cleans($salt) . "'" . $query_form['iv2'] . ")

	");

}
$task         = $db->end_task($task_id, '1');
$scope        = 'staff';
$table        = 'ppSD_staff';
$history      = new history($id, '', '', '', '', '', $table);
$content      = $history->final_content;
$table_format = new table($scope, $table);
$return       = array();
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Staff Member';
    $return['load_slider']      = array(
        'id'      => $id,
        'page'    => 'employee',
        'subpage' => 'view',
    );

} else {
    $cell                     = $table_format->render_cell($content, '1');
    $return['update_row']     = $cell;
    $return['refresh_slider'] = '1';
    $return['show_saved']     = 'Updated Staff Member';

}
echo "1+++" . json_encode($return);
exit;

