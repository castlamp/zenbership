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

$task = 'fieldset-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if ($type == 'edit') {

    /*
    $static = array();
    $static_sets = $db->run_query("
        SELECT `id`
        FROM `ppSD_forms`
        WHERE `static`='1'
    ");
    $list_ignore = '';
    while ($row = $static_sets->fetch()) {
        $static[] = $row['id'];
        $list_ignore .= " AND `location`!='" . $row['id'] . "'";
    }
    $listStore = substr($list_ignore, 5);
    if (! empty($listStore)) {
        $list_ignore = ' AND (' . $listStore . ')';
    }
    */

    $del    = $db->delete("
        DELETE FROM `ppSD_fieldsets_fields`
        WHERE `fieldset`='" . $db->mysql_clean($_POST['id']) . "'
    ");

    $up     = $db->update("
        UPDATE
          `ppSD_fieldsets`
        SET
          `name`='" . $db->mysql_clean($_POST['data']['name']) . "',
          `desc`='" . $db->mysql_clean($_POST['data']['desc']) . "'
        WHERE
          `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");

    $set_id = $_POST['id'];

    $del = $db->delete("
        DELETE FROM
            `ppSD_fieldsets_locations`
        WHERE
            `fieldset_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    //            $list_ignore

} else {

    $set_id = $db->insert("
        INSERT INTO `ppSD_fieldsets` (
            `name`,
            `desc`,
            `columns`,
            `static`,
            `owner`
        )
        VALUES (
            '" . $db->mysql_clean($_POST['data']['name']) . "',
            '" . $db->mysql_clean($_POST['data']['desc']) . "',
            '0',
            '2',
            '" . $db->mysql_clean($employee['id']) . "'
        )
    ");
}

$up = 0;
foreach ($_POST['field'] as $name => $req) {
    $up++;
    $q2 = $db->insert("
        INSERT INTO `ppSD_fieldsets_fields` (
            `fieldset`,
            `field`,
            `order`,
            `req`,
            `column`
        )
        VALUES (
          '" . $db->mysql_clean($set_id) . "',
          '" . $db->mysql_clean($name) . "',
          '" . $db->mysql_clean($up) . "',
          '" . $db->mysql_clean($req) . "',
          '1'
        )
    ");
}


if (! empty($_POST['scope'])) {
    foreach ($_POST['scope'] as $scope => $yes) {
        $find = $db->get_array("
            SELECT * FROM ppSD_fieldsets_locations
            WHERE `location`='" . $db->mysql_clean($scope) . "' AND `fieldset_id`='" . $db->mysql_clean($set_id) . "'
        ");
        if (empty($find)) {
            $in1 = $db->insert("
            INSERT INTO `ppSD_fieldsets_locations` (
              `location`,
              `order`,
              `col`,
              `fieldset_id`
            ) VALUES (
              '" . $db->mysql_clean($scope) . "',
              '0',
              '2',
              '" . $db->mysql_clean($set_id) . "'
            )
        ");
        }
    }
}


$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_fieldsets';
$scope                 = 'fieldset';
$history               = new history($set_id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Fieldset';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Fieldset';

}
echo "1+++" . json_encode($return);
exit;

