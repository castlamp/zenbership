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
$task = 'shop_tax-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Primary fields for main table
$primary    = array();
$ignore     = array('id', 'edit');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
if ($type == 'edit') {
    $update_set2 = substr($query_form['u2'], 1);
    $q           = $db->update("

		UPDATE `ppSD_tax_classes`

		SET $update_set2

		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

		LIMIT 1

	");
    $qin         = $_POST['id'];

} else {
    $insert_fields2 = $query_form['if2'];
    $insert_values2 = $query_form['iv2'];
    $qin            = $db->insert("

		INSERT INTO `ppSD_tax_classes` (`created`$insert_fields2)

		VALUES ('" . current_date() . "'$insert_values2)

	");

}
$table                 = 'ppSD_tax_classes';
$scope                 = 'tax_class';
$history               = new history($qin, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Tax Class';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Tax Class';

}
echo "1+++" . json_encode($return);
exit;
