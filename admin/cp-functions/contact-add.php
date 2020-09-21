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
$task = 'contact-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if (!empty($_POST['dud_quick_add'])) {
    $quick_add = '1';
    unset($_POST['dud_quick_add']);
} else {
    $quick_add = '0';
}
$contact = new contact;

// Primary fields for main table
$primary    = array('source', 'owner', 'created', 'account', 'type', 'email', 'email_pref', 'next_action', 'expected_value');
$ignore     = array('id', 'edit');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);


if ($type == 'edit') {
    // Ownership
    $data = $contact->get_contact($_POST['id']);

    $ownership = new ownership($data['data']['owner'], $data['data']['public']);
    if ($ownership->result != '1') {
        echo "0+++" . $ownership->reason;
        exit;
    }

    // Change a contact type...
    // Requires special handling.
    if ($data['type']['id'] != $_POST['type']) {
        $newType = $contact->changeType($_POST['id'], $_POST['type']);
        unset($_POST['type']);
    }

    // Update the contact
    //$update_set1 = substr($query_form['u1'],1);
    $update_set1 = $query_form['u1'];
    $update_set2 = substr($query_form['u2'], 1);
    $date        = current_date('contact_edit');
    $q           = $db->update("
		UPDATE `ppSD_contacts`
		SET `last_updated`='" . $date . "',`last_updated_by`='" . $employee['id'] . "'$update_set1
		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
		LIMIT 1
	");
    if (!empty($update_set2)) {
        $q1 = $db->update("
			UPDATE `ppSD_contact_data`
			SET $update_set2
			WHERE `contact_id`='" . $db->mysql_clean($_POST['id']) . "'
			LIMIT 1
		");
    }
    $id  = $_POST['id'];
    $add = $db->add_history('contact_staff_update', $employee['id'], $_POST['id'], '2', $_POST['id']);

} else {
    $idA = $contact->create($_POST);
    $id  = $idA['id'];

    /*

	// Create the contact

	//$insert_fields1 = substr($query_form['if1'],1);

	//$insert_fields2 = substr($query_form['if2'],1);

	//$insert_values1 = substr($query_form['iv1'],1);

	//$insert_values2 = substr($query_form['iv2'],1);

	$insert_fields1 = $query_form['if1'];

	$insert_fields2 = $query_form['if2'];

	$insert_values1 = $query_form['iv1'];

	$insert_values2 = $query_form['iv2'];



	// Account

	$acc = new account;

	$account = $acc->get_account($_POST['account']);

	if (empty($account['id'])) {

		$account = $acc->get_account('default');

	}

	$next_action_date = add_time_to_expires($account['contact_frequency']);



	$q = $db->insert("

		INSERT INTO `ppSD_contacts` (`id`,`status`,`created`,`last_action`,`last_updated`,`next_action`,`last_updated_by`$insert_fields1)

		VALUES ('" . $db->mysql_cleans($_POST['id']) . "','1','" . $db->mysql_cleans($_POST['created']) . "','" . current_date() . "','" . current_date() . "','$next_action_date','" . $employee['id'] . "'$insert_values1)

	");

	$q1 = $db->insert("

		INSERT INTO `ppSD_contact_data` (`contact_id`$insert_fields2)

		VALUES ('" . $db->mysql_cleans($_POST['id']) . "'$insert_values2)

	");

	*/

    // $add = add_history('contact_added',$employee['id'],$_POST['id'],'2');
}
// Re-cache
$data                  = $contact->get_contact($id, '1');
$content               = $data['data'];
$return                = array();
$table_format          = new table('contact', 'ppSD_contacts');
$return                = array();
$return['close_popup'] = '1'; // For quick add
if ($type == 'add') {
    if ($quick_add != '1') {
        $return['load_slider']      = array(
            'page'    => 'contact',
            'subpage' => 'view',
            'id'      => $content['id'],
        );
        $cell                       = $table_format->render_cell($content);
        $return['append_table_row'] = $cell;

    }
    $return['show_saved'] = 'Created';

} else {
    $return['show_saved'] = 'Updated';
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;

}
$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;



