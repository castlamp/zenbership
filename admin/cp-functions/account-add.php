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
$task = 'account-' . $type;

// Quick add?
if (!empty($_POST['dud_quick_add'])) {
    $quick_add             = '1';
    $_POST['company_name'] = $_POST['name'];
} else {
    $quick_add = '0';
}
unset($_POST['dud_quick_add']);


if (empty($_POST['name'])) {
    $_POST['name'] = $_POST['company_name'];
}

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$table      = 'ppSD_accounts';
$primary    = array('master_user', 'name', 'created', 'source', 'status', 'owner');
$ignore     = array('id', 'edit', 'contact_frequency'); // 'owner'
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

// ----------------------------
$timeframe = $admin->construct_timeframe($_POST['contact_frequency']['number'], $_POST['contact_frequency']['unit']);

// ----------------------------
if ($type == 'edit') {
    $data = new history($_POST['id'], '', '', '', '', '', $table);

    /*
    if ($data->{'final_content'}['public'] != '1' && $data->{'final_content'}['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
        echo "0+++Permission denied.";
        exit;
    }
    */

    $ownership = new ownership($data->final_content['owner'], $data->final_content['public']);
    if ($ownership->result != '1') {
        echo "0+++" . $ownership->reason;
        exit;
    }

    // `name`='" . $db->mysql_cleans($_POST['name']) . "',
    // Update the contact
    //$update_set1 = substr($query_form['u1'],1);

    $update_set1 = $query_form['u1'];
    $update_set2 = substr($query_form['u2'], 1);

    $q = $db->update("
		UPDATE
		    `ppSD_accounts`
		SET
            `contact_frequency`='" . $db->mysql_cleans($timeframe) . "',
            `last_updated`='" . current_date() . "',
            `start_page`='" . $db->mysql_cleans($_POST['start_page']) . "',
            `last_updated_by`=" . $employee['id'] . "$update_set1
		WHERE
		    `id`='" . $db->mysql_cleans($_POST['id']) . "'
		LIMIT 1
	");

    $q = $db->update("
		UPDATE
		    `ppSD_account_data`
		SET
            $update_set2
		WHERE
		    `account_id`='" . $db->mysql_cleans($_POST['id']) . "'
		LIMIT 1
	");

    $add = $db->add_history('account_updated', $employee['id'], $_POST['id'], '3', $_POST['id']);

    // Reassigned?
    if (! empty($_POST['owner']) && $data->final_content['owner'] != $_POST['owner']) {
        $account = new account;
        $account->reassign($_POST['id'], $_POST['owner']);
    }

} else {

    $insert_fields1 = $query_form['if1'];
    $insert_fields2 = $query_form['if2'];
    $insert_values1 = $query_form['iv1'];
    $insert_values2 = $query_form['iv2'];
    // Create the contact
    $last_id = $db->insert("
		INSERT INTO `$table` (
            `id`,
            `last_updated`,
            `last_updated_by`,
            `contact_frequency`
            $insert_fields1
		)
		VALUES (
            '" . $db->mysql_cleans($_POST['id']) . "',
            '" . current_date() . "',
            '" . $employee['id'] . "',
            '" . $db->mysql_cleans($timeframe) . "'
            $insert_values1
		)

	");
    $next    = $db->insert("
		INSERT INTO `ppSD_account_data` (`account_id`$insert_fields2)
		VALUES ('" . $db->mysql_cleans($_POST['id']) . "'$insert_values2)
	");
    $add     = $db->add_history('account_created', $employee['id'], $_POST['id'], '3', $_POST['id']);

}

// Re-cache
$account      = new account;
$content      = $account->get_account($_POST['id'], '1');
$return       = array();
$table_format = new table('account', 'ppSD_accounts');
$return       = array();

if ($type == 'add') {
    if ($quick_add == '1') {
        $return['show_saved']  = 'Created';
        $return['close_popup'] = '1';
    } else {
        $return['load_slider']      = array(
            'page'    => 'account',
            'subpage' => 'view',
            'id'      => $content['id'],
        );
        $return['show_saved']       = 'Created';
        $cell                       = $table_format->render_cell($content);
        $return['append_table_row'] = $cell;
    }
} else {
    $return['show_saved'] = 'Updated';
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
}

$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;
