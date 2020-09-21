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
/**
 * If adding, ID is not used. "user_id" is sent.
 * If editing, ID is the id of the item.
 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task = 'change_label';

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

$upload = new uploads;
$file = $upload->get_upload($_POST['id']);

// Ownership
$ownership = new ownership($file['owner'], '0');
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;
}

// Update
$q1 = $db->update("
    UPDATE `ppSD_uploads`
    SET `label`='" . $db->mysql_clean($_POST['label_dud']) . "'
    WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
    LIMIT 1
");

// Reply
$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_uploads';
$table_format          = new table('uploads', $table);
$history               = new history($_POST['id'], '', '', '', '', '', $table);
$content               = $history->final_content;
$cell                  = $table_format->render_cell($content, '1');

$return                = array();
$return['update_row'] = $cell;
$return['show_saved'] = 'Updated';
$return['close_popup'] = '1';

echo "1+++" . json_encode($return);
exit;
