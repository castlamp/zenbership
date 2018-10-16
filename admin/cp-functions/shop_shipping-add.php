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
$task = 'shop_shipping-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Primary fields for main table
$primary = array();
$ignore  = array('id', 'edit');
// Prepare the data for entry
$use_data = array(
    'name'     => $_POST['name'],
    'details'  => $_POST['details'],
    'priority' => $_POST['priority'],
    'cost'     => $_POST['cost'],
    'type'     => $_POST['type'],
);
if ($_POST['type'] == 'region') {
    $use_data['country'] = $_POST['region']['country'];
    $use_data['state']   = $_POST['region']['state'];
    $use_data['low']     = '';
    $use_data['high']    = '';
    $use_data['product'] = '';

} else if ($_POST['type'] == 'total') {
    $use_data['low']     = $_POST['total']['low'];
    $use_data['high']    = $_POST['total']['high'];
    $use_data['country'] = '';
    $use_data['state']   = '';
    $use_data['product'] = '';

} else if ($_POST['type'] == 'qty') {
    $use_data['low']     = $_POST['qty']['low'];
    $use_data['high']    = $_POST['qty']['high'];
    $use_data['country'] = '';
    $use_data['state']   = '';
    $use_data['product'] = '';

} else if ($_POST['type'] == 'product') {
    $use_data['product'] = $_POST['product'];
    $use_data['country'] = '';
    $use_data['state']   = '';
    $use_data['low']     = '';
    $use_data['high']    = '';

}
$query_form = $admin->query_from_fields($use_data, $type, $ignore, $primary);
if ($type == 'edit') {
    $update_set2 = substr($query_form['u2'], 1);
    $q           = $db->update("

		UPDATE `ppSD_shipping_rules`

		SET $update_set2

		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

		LIMIT 1

	");
    $qin         = $_POST['id'];

} else {
    $insert_fields2 = $query_form['if2'];
    $insert_values2 = $query_form['iv2'];
    $qin            = $db->insert("

		INSERT INTO `ppSD_shipping_rules` (`created`$insert_fields2)

		VALUES ('" . current_date() . "'$insert_values2)

	");

}
$scope                 = 'shipping';
$table                 = 'ppSD_shipping_rules';
$history               = new history($qin, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Shipping Rule';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Shipping Rule';

}
echo "1+++" . json_encode($return);
exit;

