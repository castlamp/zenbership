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
$task  = 'send_form';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Check form
$this_form = new form('', '', $_POST['id']);
$form_data = $this_form->get_form($_POST['id']);
if ($form_data['error'] == '1') {
    echo "0+++Form does not exist.";
    exit;

}
// Type
if ($_POST['type'] == 'contact') {
    $contact = new contact;
    $gdata   = $contact->get_contact($_POST['mid']);
    $type    = 'contact';
    $hash    = 'contact_request';

} else {
    $user  = new user;
    $gdata = $user->get_user($_POST['mid']);
    $type  = 'member';
    $hash  = $user->build_confirmation_hash($_POST['mid']);

}
// Ownership checks
$ownership = new ownership($gdata['data']['owner'], $gdata['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;

}
if (empty($_POST['reason'])) {
    $_POST['reason'] = 'N/A';

}
// Link
$link = PP_URL . '/register.php?id=' . $_POST['id'] . '&mid=' . $_POST['mid'] . '&s=' . $hash;
// Email
$data                  = array();
if (! empty($_POST['email'])) {
    $data['to'] = $_POST['email'];
}
$changes               = array(
    'link'     => $link,
    'reason'   => $_POST['reason'],
    'employee' => $employee,
    'form'     => $form_data,
);
$email                 = new email('', $_POST['mid'], $type, $data, $changes, 'form_request');
$return                = array();
$return['close_popup'] = '1';
$return['show_saved']  = 'Form request sent.';
$task                  = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;



