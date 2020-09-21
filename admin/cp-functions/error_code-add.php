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
$task  = 'shop_shipping-edit';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Primary fields for main table
$primary               = array();
$ignore                = array('id', 'edit');
$query_form            = $admin->query_from_fields($_POST, 'edit', $ignore, $primary);
$update_set2           = substr($query_form['u2'], 1);
$q                     = $db->update("

	UPDATE `ppSD_error_codes`

	SET $update_set2

	WHERE `code`='" . $db->mysql_clean($_POST['id']) . "'

	LIMIT 1

");
$error_id              = $db->get_error_id($_POST['id']);
$history               = new history($error_id, '', '', '', '', '', 'ppSD_error_codes');
$content               = $history->final_content;
$scope                 = 'language';
$table_format          = new table($scope, 'ppSD_error_codes');
$cell                  = $table_format->render_cell($content, '1');
$return                = array();
$return['close_popup'] = '1';
$return['update_row']  = $cell;
$return['show_saved']  = 'Update Language Information';
echo "1+++" . json_encode($return);
exit;



