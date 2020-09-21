<?php

/**
 * Add or edit a package.
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
 * EMPLOYEE AND TASK SETUP
 * Load configuration.
 * Determine action type (add/edit).
 * Establish the table and scope.
 * Check employee and permissions.
 * Begin the task.
 *
 * Required Changes:
 *
 * @param string $update_key If not "id" in the database.
 * @param string $table      Table name for the query.
 * @param string $scope      Scope of this update. Full list:
 *                           http://documentation.zenbership.com/Code-Notes/Scopes

 */
require "../sd-system/config.php";
if ($_POST['edit'] == '1') {
    $type       = 'edit';
    $update_id  = $_POST['id'];
    $update_key = 'id';

} else {
    $type       = 'add';
    $update_id  = '';
    $update_key = '';

}
$table       = 'ppSD_packages';
$scope       = 'packages';
$task        = $scope . '-' . $type;
$admin       = new admin;
$employee    = $admin->check_employee();
$permissions = new permissions($scope, $type, $update_id, $table);
$task_id     = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
/**
 * ACTION SETUP
 * Establish validation rules.
 * Establish permitted fields.
 * Assign additional fields to query.
 * Bind and execute the request.
 *
 * @param array $validate            List of validation rules.
 * @param array $validate_conditions List of rules only required
 *                                   if a certain field matches
 *                                   a certain value. Example:
 *          'email' => array('contact_method=phone','contact_method=mail')
 *                                   If "contact_method" is either "phone" or "mail", remove
 *              "email" from the $validate rules array.
 * @param array $permitted           List of permitted field names for
 *                                   the MySQL query. Essentially columns
 *                                   in the database.
 * @param array $add_data            List of fields that need to be added to
 *                                   the MySQL query but that were not
 *                                   submitted with the POSTed data.

 */
$validate                     = array();
$validate['name']             = array('nosymbols', 'required');
$validate['prorate_upgrades'] = array('required');
$validate_conditions          = array();
$permitted                    = array(
    'name',
    'prorate_upgrades'
);
$add_data                     = array();
$binding                      = new bind($table, $_POST, $permitted, $add_data, $validate, $validate_conditions, $type, $update_id, $update_key);
$task                         = $db->end_task($task_id, '1');
/**
 * ACTION RESULT
 * Prepare the return array.
 * Rebuild the table row.
 * Echo the JSON encoded return array.
 *
 * @param array $return
 *        show_saved => Message to display.
 *        close_popup => 1
 *        redirect_popup => array('page' => 'page_name', 'fields'=> 'field1=value1&field2=value2')
 *        append_table_row => Row data from $table->render_cell($data)
 *        update_row => Row data from $table->render_cell($data,'1');
 *        update_cells => array('cell_id' => 'cell_value', 'cell_id2' => 'cell_value2')
 *        refresh_slider => Refreshes current slider.
 *        load_slider => array('page' => 'page_name', 'subpage' => 'subpage_name' , 'id' => 'item_id')
 *        change_slider => subpage_id

 */
$history                  = new history($binding->return, '', '', '', '', '', $table);
$content                  = $history->final_content;
$table_format             = new table($scope, $table);
$return                   = array();
$return['redirect_popup'] = array(
    'page'   => 'package',
    'fields' => 'id=' . $binding->return,
);
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created';
    $return['close_popup']       = '1';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated';

}
echo "1+++" . json_encode($return);
exit;



