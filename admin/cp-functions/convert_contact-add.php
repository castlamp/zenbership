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
$task = 'contact-convert';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Ownership
$contact   = new contact;
$data      = $contact->get_contact($_POST['id']);
$ownership = new ownership($data['data']['owner'], $data['data']['public']);
if ($ownership->result != '1') {
    echo "0+++" . $ownership->reason;
    exit;
}
// ------
$final_id = '';
if ($_POST['member_type'] == 'new') {
    if (empty($_POST['username'])) {
        echo "0+++A username is required.";
        exit;
    } else {
        $user  = new user;
        $check = $user->confirm_username($_POST['username']);
        if ($check['error'] == '1') {
            echo "0+++" . $check['details'];
            exit;
        } else {
            // Notify new member?
            if ($_POST['notify'] == '1') {
                $skipemail = '0';
            } else {
                $skipemail = '1';
            }
            // Create member
            unset($data['data']['id']);
            unset($data['data']['0']);
            unset($data['data']['created']);
            $mdata                       = array();
            $mdata['content']            = '';
            $mdata['member']             = $data['data'];
            $mdata['member']['username'] = $_POST['username'];
            $mdata['member']['member_type']     = $_POST['type'];
            $mdata['member']['joined']   = current_date();
            $mdata['member']['status']   = "A";
            $create                      = $user->create_member($mdata, $skipemail); // , $_POST['template']
            $final_id                    = $create['member']['data']['id'];
        }

    }

} // Existing
else if ($_POST['member_type'] == 'existing') {
    if (!empty($_POST['user_id'])) {
        $user = new user;
        $mem  = $user->get_user($_POST['user_id']);
        if (!empty($mem['data']['id'])) {
            $final_id = $mem['data']['id'];
        } else {
            echo "0+++That member does not exist.";
            exit;
        }
    } else {
        echo "0+++An existing member is required.";
        exit;
    }
}


// Turn this into a function or class...............

$q1 = $db->update("
    UPDATE `ppSD_notes`
    SET `user_id`='" . $db->mysql_clean($final_id) . "',`item_scope`='member'
    WHERE `user_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q2 = $db->update("
    UPDATE `ppSD_uploads`
    SET `item_id`='" . $db->mysql_clean($final_id) . "',`type`='member'
    WHERE `item_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q3 = $db->update("
    UPDATE `ppSD_invoices`
    SET `member_id`='" . $db->mysql_clean($final_id) . "',`type`='member'
    WHERE `member_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q4 = $db->update("
    UPDATE `ppSD_subscriptions`
    SET `member_id`='" . $db->mysql_clean($final_id) . "',`type`='member'
    WHERE `member_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q5 = $db->update("
    UPDATE `ppSD_cart_billing`
    SET `member_id`='" . $db->mysql_clean($final_id) . "'
    WHERE `member_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q6 = $db->update("
    UPDATE `ppSD_cart_sessions`
    SET `member_id`='" . $db->mysql_clean($final_id) . "',`type`='member'
    WHERE `member_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q7 = $db->update("
    UPDATE `ppSD_notes`
    SET `user_id`='" . $db->mysql_clean($final_id) . "',`scope`='member'
    WHERE `user_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q8 = $db->update("
    UPDATE `ppSD_saved_emails`
    SET `user_id`='" . $db->mysql_clean($final_id) . "',`user_type`='member'
    WHERE `user_id`='" . $db->mysql_clean($_POST['id']) . "'
");
$q9 = $db->update("
    UPDATE `ppSD_saved_sms`
    SET `user_id`='" . $db->mysql_clean($final_id) . "',`user_type`='member'
    WHERE `user_id`='" . $db->mysql_clean($_POST['id']) . "'
");


// ------
if (empty($_POST['dud_type'])) {
    $value = '0';
} else if ($_POST['dud_type'] == 'order_id') {
    if (empty($_POST['order_id'])) {
        echo "0+++Input an order number.";
        exit;
    }
    $cart  = new cart;
    $order = $cart->get_order($_POST['order_id']);
    if ($order['data']['status'] != '1') {
        echo "0+++Order must be finalized.";
        exit;
    }
    $value = $order['pricing']['total'];

} else if ($_POST['dud_type'] == 'input') {
    $value = $_POST['actual_value'];
} else {
    $value = '0';
}
// ------
// Convert Contact
if (! empty($data['data']['created'])) {
    $created = $data['data']['created'];
} else {
    $created = current_date();
}

$data       = array(
    'owner'           => $data['data']['owner'],
    'user_id'         => $final_id,
    'date'            => $_POST['conversion_date'],
    'created'         => $created,
    'actual_value'    => $value,
    'estimated_value' => $data['data']['expected_value'],
);
$convert_id = $contact->convert($_POST['id'], $data);

$data                  = $contact->get_contact($_POST['id'], '1');
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';

if (! empty($_POST['user_id'])) {
    $return['load_slider'] = array(
        'page'    => 'member',
        'subpage' => 'view',
        'id'      => $final_id,
    );
}

$return['add_class']   = array(
    'id'    => 'td-cell-' . $_POST['id'],
    'class' => 'converted',
);

$return['show_saved']  = 'Completed Conversion';

echo "1+++" . json_encode($return);
exit;

