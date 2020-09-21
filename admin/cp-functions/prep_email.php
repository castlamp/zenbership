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
 * Preps an email for sending.
 * Source: JS email()

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$perm     = 'email-' . $_POST['email_type'] . '-send';
$employee = $admin->check_employee($perm);
$task_id  = $db->start_task($perm, 'staff', $_POST['email_id'], $employee['username']);

// Standard email
if ($_POST['email_type'] == 'email') {

    $changes = array();
    $data    = $db->email_data($_POST);
    $reply   = new email($_POST['email_id'], $_POST['user_id'], $_POST['user_type'], $data, $changes, '');

    //if ($_POST['user_type'] == 'contact' && $_POST['update_activity'] == '1') {
    //    $contact = new contact;
    //    $go      = $contact->update_action($_POST['user_id']);
    //}

    /*
     if ($_POST['create_note'] == '1') {
        $this_note = array(
            'user_id' => $_POST['user_id'],
            'item_scope' => $_POST['user_type'],
            'name' => $_POST['subject'],
            'note' => $_POST['message'],
        );
        $note = new notes;
        $note_type = $note->get_label_from_code('emailout');
        $this_note['label'] = $note_type;
        $note->add_note($this_note);
    }
    */

    // Worked?
    if ($reply == 'Failed') {
        $task = $db->end_task($task_id, '0', 'Email failed.');
        echo "0+++E-mail failed.";
        exit;
    } else {
        $task = $db->end_task($task_id, '1');
        echo "1+++E-mail sent.";
        exit;
    }

}

else if ($_POST['email_type'] == 'targeted') {

    // user_type -> campaign
    // Save email reference
    // and prep for sending
    // by adding items to queue.
    $connect = new connect($_POST['email_id'], $_POST['criteria_id']);
    $add     = $connect->save_mass_email($_POST);
    $prep    = $connect->prepare_targeted_email();
    $task    = $db->end_task($task_id, '1');
    echo "1+++Added " . $connect->queued . " e-mail(s) to the outgoing queue.+++1";
    exit;

}
