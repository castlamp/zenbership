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
$task = 'field-' . $type;
// Prepare insert/update
$table       = 'ppSD_fields';
$scope       = 'field';
$primary     = array('');
$ignore      = array('id', 'edit');
$return_cell = '';
// Check permissions and employee
$employee     = $admin->check_employee($task);
$task_id      = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$cell         = '';
$with_error = false;

$contactScope = 0;
$memberScope = 0;

$reserveFields = $db->get_array("DESCRIBE `ppSD_members`", "0", "2");

$fieldset = array();

$return       = array();
$table_format = new table($scope, $table);
if ($type == 'add') {
    foreach ($_POST['1'] as $field) {

        $check = trim(strtolower($field['display_name']));
        if (in_array($check, $reserveFields)) {
            $with_error .= ', ' . $check;
            continue;
        }

        $forid = strtolower(str_replace(' ', '_', $field['display_name']));
        $forid = preg_replace('/[^a-zA-Z0-9_]+/', '', $forid);
        $forid = substr($forid, 0, 60);

        $find = $db->get_array("
            SELECT id
            FROM `ppSD_fields`
            WHERE `id`='" . $db->mysql_clean($forid) . "'
            LIMIT 1
        ");
        if (! empty($find['id'])) {
            $id = substr($forid, 0, 8) . '_' . rand(1, 99999);
        } else {
            $id = $forid;
        }


        $fieldset[] = $id;

        if (empty($field['special_type'])) {
            $field['special_type'] = '';
        }
        if (empty($field['encrypt'])) {
            $field['encrypt'] = '';
        }
        if (empty($field['maxlength'])) {
            if ($field['type'] == 'textarea') { $field['maxlength'] = ''; }
            else { $field['maxlength'] = '255'; }
        }
        // 'text','textarea','radio','select','checkbox','attachment','section','multiselect','multicheckbox','linkert','date'
        if ($field['type'] == 'text') {
            $type = ' VARCHAR (' . $field['maxlength'] . ')';
        }
        else if ($field['type'] == 'textarea') {
            $type = ' MEDIUMTEXT';
        }
        else if ($field['type'] == 'select') {
            $type = ' VARCHAR( 50 )';
        }
        else if ($field['type'] == 'checkbox') {
            $type = ' TINYINT( 1 )';
        }
        else if ($field['type'] == 'date') {
            $type = ' DATE';
        }
        else {
            $type = ' VARCHAR( 50 )';
        }
        // Scopes
        if (!empty($field['index_member'])) {
            $field['scope_member'] = '1';
            $q1                    = $db->run_query("
                ALTER TABLE  `ppSD_member_data`
                ADD  `" . $db->mysql_cleans($id) . "` " . $type . "
            ");
        } else {
            $field['scope_member'] = '0';
        }
        if (!empty($field['index_contact'])) {
            $field['scope_contact'] = '1';
            $q1                     = $db->run_query("
                ALTER TABLE  `ppSD_contact_data`
                ADD  `" . $db->mysql_cleans($id) . "` " . $type . "
            ");
        } else {
            $field['scope_contact'] = '0';
        }
        if (!empty($field['index_event'])) {
            $field['scope_rsvp'] = '1';
            $q1                  = $db->run_query("
                ALTER TABLE  `ppSD_event_rsvp_data`
                ADD  `" . $db->mysql_cleans($id) . "` " . $type . "
            ");
        } else {
            $field['scope_rsvp'] = '0';
        }
        if (!empty($field['index_account'])) {
            $field['scope_account'] = '1';
            $q1                     = $db->run_query("
                ALTER TABLE  `ppSD_account_data`
                ADD  `" . $db->mysql_cleans($id) . "` " . $type . "
            ");
        } else {
            $field['scope_account'] = '0';

        }
        $style = '';
        if (!empty($field['width'])) {
            $style .= 'width:' . $field['width'] . 'px;';
        }
        if (!empty($field['height'])) {
            $style .= 'height:' . $field['height'] . 'px;';
        }
        if (!empty($field['label_position'])) {
            $pos = $field['label_position'];
        } else {
            $pos = 'left';
        }
        if (! empty($field['options'])) {
            $options = $field['options'];
        } else {
            $options = '';
        }
        $data = array(
            'id'             => $id,
            'display_name'   => $field['display_name'],
            'type'           => $field['type'],
            'special_type'   => $field['special_type'],
            'desc'           => $field['desc'],
            'label_position' => $pos,
            'maxlength'      => $field['maxlength'],
            'scope_account'  => $field['scope_account'],
            'scope_rsvp'     => $field['scope_rsvp'],
            'scope_contact'  => $field['scope_contact'],
            'scope_member'   => $field['scope_member'],
            'styling'        => $style,
            'encrypted'      => $field['encrypt'],
            'options'        => $options,
        );
        // Insert
        $query_form     = $admin->query_from_fields($data, $type, $ignore, $primary);
        $insert_fields1 = substr($query_form['if1'], 1);
        $insert_fields2 = substr($query_form['if2'], 1);
        $insert_values1 = substr($query_form['iv1'], 1);
        $insert_values2 = substr($query_form['iv2'], 1);
        $last_id        = $db->insert("
            INSERT INTO `$table` (`id`,$insert_fields2)
            VALUES ('" . $db->mysql_cleans($id) . "',$insert_values2)
	    ");
        $history        = new history($id, '', '', '', '', '', $table);
        $cell .= $table_format->render_cell($history->final_content);

        // Searchable?
        if ($field['search_member'] == '1') {
            $memberScope = 1;
            $optionMem = $db->get_option('additional_search_members');
            $optionMem .= ',' . $id;
            $db->update_option('additional_search_members', $optionMem);
        }

        if ($field['search_contact'] == '1') {
            $contactScope = 1;
            $optionMem = $db->get_option('additional_search_contacts');
            $optionMem .= ',' . $id;
            $db->update_option('additional_search_contacts', $optionMem);
        }

    }


    // fieldset details...
    if (! empty($_POST['create_fieldset'])) {

        $set_id = $db->insert("
            INSERT INTO `ppSD_fieldsets` (
                `name`,
                `desc`,
                `columns`,
                `static`,
                `owner`
            )
            VALUES (
                '" . $db->mysql_clean($_POST['fieldset_name']) . "',
                '" . $db->mysql_clean($_POST['fieldset_desc']) . "',
                '0',
                '2',
                '" . $db->mysql_clean($employee['id']) . "'
            )
        ");

        $up = 0;
        foreach ($fieldset as $aField) {
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
                  '" . $db->mysql_clean($aField) . "',
                  '" . $db->mysql_clean($up) . "',
                  '0',
                  '1'
                )
            ");
        }

        if ($memberScope == 1) {
            $in1 = $db->insert("
                INSERT INTO `ppSD_fieldsets_locations` (
                  `location`,
                  `order`,
                  `col`,
                  `fieldset_id`
                ) VALUES (
                  'member-add',
                  '0',
                  '2',
                  '" . $db->mysql_clean($set_id) . "'
                )
            ");
            $in1 = $db->insert("
                INSERT INTO `ppSD_fieldsets_locations` (
                  `location`,
                  `order`,
                  `col`,
                  `fieldset_id`
                ) VALUES (
                  'member-edit',
                  '0',
                  '2',
                  '" . $db->mysql_clean($set_id) . "'
                )
            ");
        }

        if ($contactScope == 1) {
            $in1 = $db->insert("
                INSERT INTO `ppSD_fieldsets_locations` (
                  `location`,
                  `order`,
                  `col`,
                  `fieldset_id`
                ) VALUES (
                  'contact-add',
                  '0',
                  '2',
                  '" . $db->mysql_clean($set_id) . "'
                )
            ");
            $in1 = $db->insert("
                INSERT INTO `ppSD_fieldsets_locations` (
                  `location`,
                  `order`,
                  `col`,
                  `fieldset_id`
                ) VALUES (
                  'contact-edit',
                  '0',
                  '2',
                  '" . $db->mysql_clean($set_id) . "'
                )
            ");
        }

    }


    $return['show_saved'] = 'Created Fields';

} else {
    $id = $_POST['id'];
    $cell .= '';
    $return['show_saved'] = 'Updated Fields';

}
$task                       = $db->end_task($task_id, '1');
$return['append_table_row'] = $cell;
$return['close_popup']      = '1';
if (! empty($with_error)) {
    $return['show_saved'] .= ' (with errors: one of more fields could not be created: ' . trim($with_error, ',') . ')';
}
echo "1+++" . json_encode($return);
exit;



