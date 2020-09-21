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
$task = 'assign_contact-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Ownership
$contact = new contact;
$data    = $contact->get_contact($_POST['id']);
if ($data['data']['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
    echo "0+++You cannot re-assign this contact.";
    exit;

}
// Assign Contact
$q1 = $db->update("

    UPDATE

        `ppSD_contacts`

    SET

        `source`='" . $db->mysql_clean($_POST['source']) . "',

        `account`='" . $db->mysql_clean($_POST['account']) . "',

        `expected_value`='" . $db->mysql_clean($_POST['expected_value']) . "',

        `public`='" . $db->mysql_clean($_POST['public']) . "'

    WHERE

        `id`='" . $db->mysql_clean($_POST['id']) . "'

    LIMIT 1

");
// Email Employee or push it
// to his/her homepage.
$contact->assign($_POST['id'], $_POST['owner']);
// Re-cache
$data                  = $contact->get_contact($_POST['id'], '1');
$content               = $data['data'];
$table_format          = new table('contact', 'ppSD_contacts');
$return                = array();
$return['close_popup'] = '1';
$cell                  = $table_format->render_cell($content, '1');
$return['update_row']  = $cell;
$return['show_saved']  = 'Contact Assigned';
$task                  = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;



