<?php


/**
 * Merge Two Contacts Into One
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
$type = 'merge';
$task = 'contact-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
//$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

$contact = new contact;

if (! empty($_POST['id']) && ! empty($_POST['merge_into'])) {

    $get = $contact->merge($_POST['id'], $_POST['merge_into']);

} else {
    $return                = array();
    $return['show_saved']  = 'Could not merge contacts. Primary contact and contacts to merge are both required.';
    exit;
}

// Re-cache.
$data                  = $contact->get_contact($_POST['id'], '1');

// Send back information.
$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Contacts Merged.';

//$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;
