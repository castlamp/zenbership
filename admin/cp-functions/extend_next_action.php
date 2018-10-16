<?php

/**
 * This is the same as "extend-date.php" but only for one user at a time.
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
$task  = 'contact-next_action';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Ownership
if (empty($_POST['type'])) {
    echo "0+++No type submitted.";
    exit;

}
if ($_POST['type'] == 'member') {
    $member = new user;
    $data   = $member->get_user($_POST['id']);
    $type   = 'member';

} else {
    $contact = new contact;
    $data    = $contact->get_contact($_POST['id']);
    $type    = 'contact';

}
if (empty($data['data']['id'])) {
    echo "0+++Contact does not exist.";
    exit;

}
$ownership = new ownership($data['data']['owner'], $data['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;

}

// ------
$extend = $db->update_next_action($_POST['id'], $type);
$format = format_date($extend);

// ------
// Re-cache
if ($_POST['type'] == 'member') {
    $data = $user->get_user($_POST['id'], '1');
} else {
    $data = $contact->get_contact($_POST['id'], '1');
}

$task                     = $db->end_task($task_id, '1');
$return                   = array();
$return['refresh_slider'] = '1';
$return['update_cells']   = array(
    $_POST['id'] . '-next_action' => $format,
);
$return['remove_class']   = array(
    'id'    => 'td-cell-' . $_POST['id'],
    'class' => 'converted',
);
$return['show_saved']     = 'Date updated to ' . $format;

echo "1+++" . json_encode($return);
exit;

