<?php


/**
 * Create a hook
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
$task = 'hook';
if ($_POST['edit'] == '1') {
    $type = 'edit';
} else {
    $type = 'add';
}

// Check permissions and employee
$admin    = new admin;
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Type
// PHP Code Exlcusion
if ($_POST['type'] == '1') {
    
    if (! file_exists($_POST['path'])) {
        echo "0+++Could not find the PHP file: " . $_POST['path'];
        exit;
    }
    $data = $_POST['path'];
    
}

// E-Mail
else if ($_POST['type'] == '2') {

    $data = serialize($_POST['data']);

}

// MySQL Command
else if ($_POST['type'] == '3') {
    
    if (! empty($_POST['data']['db_name'])) {
        try {
            $dbh = new PDO('mysql:host=' . $_POST['data']['db_host'] . ';dbname=' . $_POST['data']['db_name'], $_POST['data']['db_user'], $_POST['data']['db_pass']);
        } catch (PDOException $e) {
            echo "0+++Could not connect to database.";
            exit;
        }
        $_POST['data']['db_host'] = encode($_POST['data']['db_host']);
        $_POST['data']['db_name'] = encode($_POST['data']['db_name']);
        $_POST['data']['db_user'] = encode($_POST['data']['db_user']);
        $_POST['data']['db_pass'] = encode($_POST['data']['db_pass']);
    }
    $data = serialize($_POST['data']);
    
}

// Outside Connection
else if ($_POST['type'] == '5') {
    
    $data = serialize($_POST['data']);
    
}

// Outside Connection
else if ($_POST['type'] == '6') {

    $data = serialize($_POST['data']);

}

// Data
$primary    = array();
$ignore     = array(
    'id',
    'edit',
    'data',
    'path',
    'product',
    'form',
    'specific_form_dud',
    'specific_form',
    'specific_prod',
    'specific_prod_dud',
);
if ($_POST['trigger_type'] == '1') {
    $_POST['specific_trigger'] = $_POST['specific_prod'];
}
else if ($_POST['trigger_type'] == '2') {
    $_POST['specific_trigger'] = $_POST['specific_form'];
}
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);

// Edit?
if ($_POST['edit'] == '1') {

    $insert = $db->insert("
        UPDATE
            `ppSD_custom_actions`
        SET
            `data`='" . $db->mysql_clean($data) . "'
            " . $query_form['u2'] . "
        WHERE
            `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $id = $_POST['id'];

}

// Create
else {

    $id = $db->insert("
        INSERT INTO `ppSD_custom_actions` (
            `owner`,
            `created`,
            `data`
            " . $query_form['if2'] . "
        )
        VALUES (
            '" . $employee['id'] . "',
            '" . current_date() . "',
            '" . $db->mysql_cleans($data) . "'
            " . $query_form['iv2'] . "
        )
    ");

}

// End Task
$end_task = $db->end_task($task_id, '1');

// Return/Reply
$table_format           = new table('hook', 'ppSD_custom_actions');
$history                = new history($id, '', '', '', '', '', 'ppSD_custom_actions');
$content                = $history->final_content;
$return                 = array();
$return['close_popup']  = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
} else {
    $cell                       = $table_format->render_cell($content, '1');
    $return['update_row']       = $cell;
}
    
echo "1+++" . json_encode($return);
exit;
