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
$task     = 'field-' . $type;
$employee = $admin->check_employee($task);
// Prepare insert/update
$table = 'ppSD_fields';
$scope = 'field';
foreach ($_POST['field'] as $field_id => $field_data) {
    $task_id    = $db->start_task($task, 'staff', $field_id, $employee['username']);
    $field      = new field();
    $data       = $field->get_field($field_id);
    $table      = 'ppSD_fields';
    $primary    = array('');
    $ignore     = array('special_type');


    // ------------------------------
    // Searchable?
    $optionMem = $db->get_option('additional_search_members');
    $exp = explode(',', $optionMem);
    if ($field_data['search_member'] == '1') {
        if (! in_array($field_id, $exp)) {
            $exp[] = $field_id;
        }
    } else {
        if (in_array($field_id, $exp)) {
            if (($key = array_search($field_id, $exp)) !== false) {
                unset($exp[$key]);
            }
        }
    }
    $db->update_option('additional_search_members', implode($exp, ','));
    unset($field_data['search_member']);

    $optionCon = $db->get_option('additional_search_contacts');
    $exp = explode(',', $optionCon);
    if ($field_data['search_contact'] == '1') {
        if (! in_array($field_id, $exp)) {
            $exp[] = $field_id;
        }
    } else {
        if (in_array($field_id, $exp)) {
            if (($key = array_search($field_id, $exp)) !== false) {
                unset($exp[$key]);
            }
        }
    }
    $db->update_option('additional_search_contacts', implode($exp, ','));
    unset($field_data['search_contact']);
    // ------------------------------


    // SCOPE: Members
    // Adding to scope.
    if (empty($field_data['scope_account'])) {
        $field_data['scope_account'] = '0';
    }
    if (empty($field_data['scope_contact'])) {
        $field_data['scope_contact'] = '0';
    }
    if (empty($field_data['scope_member'])) {
        $field_data['scope_member'] = '0';
    }
    if (empty($field_data['scope_rsvp'])) {
        $field_data['scope_rsvp'] = '0';
    }

    $query_form = $admin->query_from_fields($field_data, $type, $ignore, $primary);

    $update     = $db->update("
        UPDATE `ppSD_fields`
        SET " . ltrim($query_form['u2'], ',') . ",`special_type`='" . $db->mysql_clean($field_data['special_type']) . "'
        WHERE `id`='" . $db->mysql_clean($field_id) . "'
        LIMIT 1
    ");
    $db->check_scope_change($field_data, $data);

    $task = $db->end_task($task_id, '1');

}

$history               = new history($field_id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$cell                  = $table_format->render_cell($content, '1');

$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Updated Field';
$return['update_row']  = $cell;

echo "1+++" . json_encode($return);
exit;



