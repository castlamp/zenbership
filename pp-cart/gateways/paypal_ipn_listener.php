<?php

/**
 * PayPal IPN Listener
 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup
 * Send notify_url with transaction.
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
// Basics
require "../../admin/sd-system/config.php";
$cart    = new cart();
$gateway = $cart->get_gateways('', 'gw_paypal');
if ($gateway['0']['test_mode'] == '1') {
    $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
} else {
    $url = 'https://www.paypal.com/cgi-bin/webscr';
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=' . urlencode('_notify-validate');
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
//https://www.sandbox.paypal.com/cgi-bin/webscr
//https://www.paypal.com/cgi-bin/webscr
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
$res = curl_exec($ch);
curl_close($ch);
// payer_id
// payer_email
// custom
// mc_fee => fee of transaction
// payment_status: Completed
// https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_IPNandPDTVariables
// assign posted variables to local variables
/*
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$custom_pass = $_POST['custom'];
$txn_id = $_POST['txn_id'];
$txn_type = $_POST['txn_type']; // subscr_signup | subscr_cancel | subscr_modify | subscr_payment | subscr_failed | subscr_eot
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
*/
$msg = '';
foreach ($_POST as $name => $value) {
    $msg .= $name . ': ' . $value . "\n";
}

if (strcmp($res, "VERIFIED") == 0 || strcmp($res, "verified") == 0) {
    // Only transfer if successful
    if ($_POST['payment_status'] == 'Completed') {
        $check_salt = $cart->confirm_salt($_POST['invoice'], $_POST['custom']);
        if ($check_salt == '1') {
            if ($_POST['txn_type'] == 'subscr_cancel') {
                $sub_class = new subscription;
                $sub       = $sub_class->get_subscription('', $_POST['invoice'], '1', $_POST['subscr_id']);
                $cancel    = $sub_class->cancel_subscription($sub['id'], 'Canceled from PayPal.');

            } else {
                $billing_array = array(
                    'email'   => $_POST['payer_email'],
                    'method'  => 'PayPal',
                    'gateway' => 'gw_paypal',
                    'name'    => $_POST['first_name'] . ' ' . $_POST['last_name']
                );
                $card_add      = $cart->add_card($billing_array, '0');
                if ($_POST['txn_type'] == 'subscr_payment') {
                    $sub_class = new subscription;
                    $find      = $sub_class->find_subscription('', $_POST['subscr_id']);
                    if (!empty($find['id'])) {
                        $renew = $sub_class->renew_subscription($find['id']);
                    } else {
                        $subscription = $cart->find_subscription_product($_POST['invoice']);
                        if (!empty($subscription['id'])) {
                            $getorder = $cart->get_order($_POST['invoice'], '1');
                            $create   = $sub_class->create_subscription($subscription, $_POST['invoice'], $getorder['data']['member_id'], $card_add, '1', $_POST['subscr_id'], 'gw_paypal');
                        }
                    }
                }
                $gateway_info = array(
                    'id'        => 'gw_paypal',
                    'resp_code' => '',
                    'order_no'  => $_POST['txn_id'],
                    'fee'       => $_POST['payment_fee'],
                    'msg'       => $_POST['payment_status'],
                );
                // $card_add
                $cart->complete_order($_POST['invoice'], $gateway_info, '1', '1');

            }
            echo "Success";
            exit;
        } else {
            exit;
        }
    }
    // check the payment_status is Completed
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process payment
    echo "Valid.";
    exit;
} else if (strcmp($res, "INVALID") == 0 || strcmp($res, "invalid") == 0) {
    echo "Invalid.";
    exit;
}
