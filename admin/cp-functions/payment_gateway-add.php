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
$type  = 'edit';
$task  = 'payment_gateway-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// PCI Compliance
if ($_POST['active'] == '1') {
    if (empty($_POST['agree']) || $_POST['agree'] != '1') {
        echo "0+++You must agree to the PCI Compliance terms before proceeding.";
        exit;
    }
}

// Primary fields for main table
$table   = 'ppSD_payment_gateways';
$primary = array('');
$ignore  = array('id', 'edit', 'agree');

// Disabled? Never set
// as primary.
if ($_POST['active'] != '1') {
    $_POST['primary'] = '0';
}

// Clear previous primary gateway
// to avoid issues.
if ($_POST['primary'] == '1') {
    $q  = $db->update("
        UPDATE `$table`
        SET `primary`='0'
        WHERE 1
");
}

// Event Query
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

// ----------------------------
// Update the contact
$update_set2           = substr($query_form['u2'], 1);
$q                     = $db->update("
	UPDATE `$table`
	SET $update_set2
	WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
	LIMIT 1
");
$task                  = $db->end_task($task_id, '1');
$scope                 = 'payment_gateway';
$history               = new history($_POST['id'], '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
$cell                  = $table_format->render_cell($content, '1');
$return['show_saved']  = 'Updated Payment Gateway';
$return['update_row']  = $cell;
if ($_POST['active'] == '1') {
    $return['add_class'] = array(
        'id'    => 'td-cell-' . $_POST['id'],
        'class' => 'converted',
    );

} else {
    $return['remove_class'] = array(
        'id'    => 'td-cell-' . $_POST['id'],
        'class' => 'converted',
    );

}
echo "1+++" . json_encode($return);
exit;





