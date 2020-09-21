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
 * Re-sends an invoice to a client.
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
 * @date        12/6/12 5:28 PM
 * @version     v1.0
 */
require "../sd-system/config.php";
$task     = 'invoice-email';
$admin    = new admin;
$employee = $admin->check_employee($task);
if (!empty($_POST['id'])) {
    $table     = 'ppSD_notes';
    $type      = 'edit';
    $update_id = $_POST['id'];
    $permitted = array('all');
    // Permissions and ownership
    $admin       = new admin;
    $employee    = $admin->check_employee();
    $permissions = new permissions('note', $type, $update_id, $table);
    if ($_POST['complete'] == '1') {
        $force_fields = array('complete' => '1', 'completed_on' => current_date(), 'completed_by' => $employee['id']);

    } else {
        $force_fields = array('complete' => '0', 'completed_on' => '', 'completed_by' => '');

    }
    $binding = new bind($table, $force_fields, $permitted, '', '', '', $type, $update_id, 'id');

}
$return                 = array();
$return['close_popup']  = '1';
$return['show_saved']   = 'Note Marked Complete';
$return['remove_cells'] = array('note-' . $_POST['id']);
echo "1+++" . json_encode($return);
exit;



