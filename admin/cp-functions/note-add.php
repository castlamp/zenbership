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
$task = 'note_' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Next action?
if (!empty($_POST['update_next_action'])) {
    $next_act = '1';
} else {
    $next_act = '0';
}
unset($_POST['update_next_action']);

if ($_POST['public'] == '3' && empty($_POST['for'])) {
    echo "0+++Select an employee for whom this note is for.";
    exit;
}
if ($_POST['encrypt'] == '1') {
    $_POST['note'] = encode($_POST['note']);
}

// Primary fields for main table
$table      = 'ppSD_notes';
$primary    = array('');
$ignore     = array('id', 'edit');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

if ($type == 'edit') {

    $data = new history($_POST['id'], '', '', '', '', '', $table);

    if ($data->final_content['public'] != '1' && $data->final_content['added_by'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
        echo "0+++Permission denied.";
        exit;
    }

    // Update the contact
    $update_set1 = substr($query_form['u1'], 1);
    $update_set2 = substr($query_form['u2'], 1);

    $q = $db->update("
		UPDATE `$table`
		SET $update_set2
		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
		LIMIT 1
	");

    $last_id = $_POST['id'];

} else {

    // Create the contact
    $insert_fields1 = substr($query_form['if1'], 1);
    $insert_fields2 = substr($query_form['if2'], 1);
    $insert_values1 = substr($query_form['iv1'], 1);
    $insert_values2 = substr($query_form['iv2'], 1);

    $in = $db->insert("
		INSERT INTO `$table` (`id`,`added_by`,$insert_fields2)
		VALUES ('" . $db->mysql_clean($_POST['id']) . "','" . $employee['id'] . "',$insert_values2)
	");

    $add = add_history('note', '2', $_POST['user_id'], '', $_POST['id'], '');

    $last_id = $_POST['id'];

    // Update next action?
    if ($next_act == '1') {
        if ($_POST['item_scope'] == 'contact') {
            $db->update_next_action($_POST['user_id'], 'contact');
        }
        else if ($_POST['item_scope'] == 'member') {
            $db->update_next_action($_POST['user_id'], 'member');
        }
    }
}

if ($_POST['item_scope'] == 'contact' && ! empty($_POST['advance_pipeline'])) {
    $contact = new contact;
    $contact->changePipeline($_POST['user_id'], $_POST['advance_pipeline']);
}

// Notify Employee of note.
if ($_POST['public'] == '3') {
    $note = new notes();
    $tag = $note->tag_employee($last_id, $_POST['for'], $employee['id']);
}


$indata = array(
    'id' => $last_id,
    'deadline' => $_POST['deadline'],
    'title' => $_POST['name'],
    'content' => $_POST['note'],
    'data' => $_POST,
    'label' => $_POST['label'],
);

$task = $db->end_task($task_id, '1', '', $task, '', $indata);

// Complete
$table                 = 'ppSD_notes';
$scope                 = '';
$history               = new history($last_id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();

$return['close_popup'] = '1';

if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Added Note';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Update Note';
}

echo "1+++" . json_encode($return);
exit;
