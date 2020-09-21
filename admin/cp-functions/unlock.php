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

$task = 'history-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$user     = new user;
$data     = $user->get_user($_POST['id']);
// Ownership
$ownership = new ownership($data['data']['owner'], $data['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;

}
// Update
$user->unlock($_POST['id'], $_POST['ip']);

// Re-cache
$data                   = $user->get_user($_POST['id'], '', '1');
$content                = $data['data'];
$return                 = array();
$return['show_saved']   = 'Unlocked member.';
$return['remove_cells'] = array('lock_notice');
echo "1+++" . json_encode($return);
exit;



