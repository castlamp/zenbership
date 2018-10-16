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
 *

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$type  = 'add';
$task  = 'refund-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Get order
$cart  = new cart;
$order = $cart->get_order($_POST['id'], '0');

if ($_POST['total'] > $order['pricing']['total']) {
    echo "0+++Refund amount cannot be greater than the order's total.";
    exit;

}
// Issue the refund
if ($_POST['issue_in_gateway'] == '1') {
    if (empty($order['data']['payment_gateway'])) {
        $issue_in_gateway = '0';
    } else {
        $gateway = $cart->get_gateways('', $order['data']['payment_gateway']);
        if ($gateway['0']['method_refund'] == '1' && !empty($order['data']['gateway_order_id'])) {
            $issue_in_gateway = '1';
        } else {
            $issue_in_gateway = '0';
        }
    }
} else {
    $issue_in_gateway = '0';

}
// Issue refund in gateway
if ($issue_in_gateway == '1') {
    $gdata = new $gateway['0']['code']($_POST['total'], '', $order['data']['gateway_order_id']);
    $check = $gdata->refund();
    if ($check['error'] == '1') {
        echo '0+++Could not issue refund in payment gateway: ' . $check['msg'] . ' (' . $check['resp_code'] . ')';
        exit;

    }

}
// Issue the refund in the database.
$q1 = $db->insert("

    INSERT INTO `ppSD_cart_refunds` (

        `date`,

        `total`,

        `reason`,

        `order_id`,

        `type`,

        `chargeback_fee`

    )

    VALUES (

      '" . current_date() . "',

      '" . $db->mysql_cleans($_POST['total']) . "',

      '" . $db->mysql_cleans($_POST['remarks']) . "',

      '" . $db->mysql_cleans($_POST['id']) . "',

      '" . $db->mysql_cleans($_POST['type']) . "',

      '" . $db->mysql_cleans($_POST['chargeback_fee']) . "'

    )

");
$q2 = $db->update("

    UPDATE `ppSD_cart_session_totals`

    SET

        `refunds`=(`refunds`+" . $db->mysql_cleans($_POST['total']) . "),

        `total`=(`total`-" . $db->mysql_cleans($_POST['total']) . ")

    WHERE `id`='" . $db->mysql_cleans($_POST['id']) . "'

    LIMIT 1

");
// Refunds
if ($_POST['type'] == '1') {
    $db->put_stats('refunds');
    $db->put_stats('refund_totals', $_POST['total']);

} else {
    $db->put_stats('chargebacks');
    $db->put_stats('chargeback_totals', $_POST['total']);

}
// Cancel subscriptions and/or invoices.
if ($_POST['cancel_subs'] == '1') {
    $skip = 0;
    if ($order['data']['member_type'] == 'member' && !empty($order['data']['member_id'])) {
        $user   = new user;
        $member = $user->get_user($order['data']['member_id']);

    } else if ($order['data']['member_type'] == 'contact' && !empty($order['data']['member_id'])) {
        $contact = new contact;
        $member  = $contact->get_contact($order['data']['member_id']);

    } else {
        $skip = 1;

    }
    if ($skip != '1') {
        if (!empty($member['subscriptions'])) {
            foreach ($member['subscriptions'] as $sub) {
                $subscription = new subscription;
                $subscription->cancel_subscription($sub['id'], $_POST['remarks']);

            }

        }

    }

}
// E-mail the user
if ($_POST['show_remarks'] == '1') {
    $fremarks = $_POST['remarks'];

} else {
    $fremarks = 'N/A';

}
$changes            = array(
    'total'   => place_currency($_POST['total']),
    'remarks' => $fremarks,
);
$changes['order']   = $order['data'];
$changes['pricing'] = $order['pricing'];
if ($order['data']['member_type'] == 'member') {
    $type = 'member';

} else {
    $type = 'contact';

}
$email = new email('', $order['data']['member_id'], $type, '', $changes, 'refund_issued');
// Re-cache
$cart->get_order($_POST['id'], '0', '1');
// Complete task
$return                   = array();
$return['close_popup']    = '1';
$return['show_saved']     = 'Order Refunded';
$return['refresh_slider'] = '1';
echo "1+++" . json_encode($return);
exit;

