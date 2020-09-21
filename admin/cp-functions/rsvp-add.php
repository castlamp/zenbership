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
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'rsvp-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Editing?
if ($type == 'edit') {
    $primary    = array('status', 'type', 'primary_rsvp', 'arrived', 'arrived_date', 'email');
    $ignore     = array('id', 'edit', 'fields');
    $query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
    if (!empty($_POST['fields']['email'])) {
        $query_form['u1'] .= ",`bounce_notice`='1920-01-01 00:01:01'";
    }
    $up1 = $db->update("
        UPDATE `ppSD_event_rsvps`
        SET `email`='" . $db->mysql_clean($_POST['fields']['email']) . "'" . $query_form['u1'] . "
        WHERE `id`='" . $db->mysql_cleans($_POST['id']) . "'
        LIMIT 1
    ");
    $primary     = array('');
    $ignore      = array('email');
    $query_formA = $admin->query_from_fields($_POST['fields'], $type, $ignore, $primary);
    $up1         = $db->update("
        UPDATE `ppSD_event_rsvp_data`
        SET " . ltrim($query_formA['u2'], ',') . "
        WHERE `rsvp_id`='" . $db->mysql_cleans($_POST['id']) . "'
        LIMIT 1
    ");
    $rsvp_id     = $_POST['id'];

    // $return_cell = 'refresh';
} else {

    $event = new event;
    // Are we charging the user?
    // Charge card
    if ($_POST['status'] != '1' && $_POST['status'] != '3') {
        // -------------------------------
        //  Instant add order, with
        //  option to charge user.
        if ($_POST['payment_type'] == '1') {
            if (!empty($_POST['savings_code'])) {
                $savings_code = $_POST['savings_code'];
            } else {
                $savings_code = '';
            }
            $paid       = '1';
            $send_email = '1';
            // Build card array
            $card = array();
            if ($_POST['card_type'] == 'new_card') {
                $card              = $_POST['cc'];
                $card['card_id']   = '';
                $card['card_type'] = 'new_card';
            } else if ($_POST['card_type'] == 'existing_card') {
                $card['card_id']   = $_POST['card_id'];
                $card['card_type'] = 'existing_card';
            } else {
                $card['card_id']   = '';
                $card['card_type'] = 'no_card';
            }
            // Run an order
            $cart  = new cart;
            $order = $cart->run_order($_POST['product'], $card, $savings_code, $_POST['user_id']);
            if ($order['error'] == '1') {
                echo "0+++" . $order['error_details'];
                exit;
            }

            $order_id   = $cart->id;

        }

        // -------------------------------
        //  Invoice user.
        else {
            // Invoice Data Array
            $eData = $event->get_event($_POST['event_id']);
            if ($eData['data']['close_registration'] != '1920-01-01 00:01:01') {
                $use_date = $eData['data']['close_registration'];

            } else {
                $use_date = $eData['data']['starts'];

            }
            $use_data = array(
                'member_id'     => $_POST['user_id'],
                'member_type'   => 'member',
                'due_date'      => $use_date,
                'hourly'        => '',
                'shipping_name' => '',
                'shipping_rule' => '',
            );
            // Billing Data Array
            $bdata = $_POST['invoice'];
            // Totals array
            $subtotal = 0;
            $cart     = new cart;
            foreach ($_POST['product'] as $item => $qty) {
                if ($qty > 0) {
                    $price = $cart->get_product_price($item);
                    $subtotal += $price['price'] * $qty;

                }

            }
            $total = array(
                'due'      => $subtotal,
                'shipping' => '0.00',
                'tax'      => '0.00',
                'tax_rate' => '0',
                'credits'  => '0.00',
                'subtotal' => $subtotal,
            );
            // Shipping Array
            $shipping   = array();
            $invoice    = new invoice;
            $invoice_id = $invoice->create_invoice($use_data, $total, $bdata, $shipping, '0');
            // Add components
            $cart = new cart;
            foreach ($_POST['product'] as $item => $qty) {
                if ($qty > 0) {
                    $prod                          = $cart->get_product($item);
                    $price                         = $cart->get_product_price($item);
                    $prod['pricing']['qty']        = $qty;
                    $prod['pricing']['plain_unit'] = $price['price'];
                    // Taxable?
                    if ($prod['data']['tax_exempt'] == '1') {
                        $tax = '0';

                    } else {
                        $tax = '1';

                    }
                    // Add to invoice...
                    $comp = $invoice->add_component_product($invoice_id, $prod, $tax);

                }

            }
            $invoice->send_invoice($invoice_id, '1');
            $paid       = '0';
            $send_email = '0';

        }

    } else {

        $paid       = '1';
        $send_email = '1';
        $order_id   = $_POST['order_id'];

    }
    // ------------------
    //  Proceed forward, actually
    //  creating the RSVP.
    // Add the RSVPs
    // And send out all emails
    // in the process.
    $primary = $event->create_rsvp($_POST['event_id'], $_POST['fields'], $_POST['user_id'], $order_id, '0', '', $paid, $send_email);
    if (!empty($_POST['guest'])) {
        foreach ($_POST['guest'] as $anRSVP) {
            $secondary = $event->create_rsvp($_POST['event_id'], $anRSVP, '', $order_id, '1', $primary, $paid, $send_email);

        }

    }
    $rsvp_id = $primary;
    // Update RSVP ID on invoice
    // if we are dealing with an
    // invoice.
    if ($_POST['payment_type'] == '2') {
        $add_data = array(
            'rsvp_id' => $primary,
        );
        $invoice->update_invoice($invoice_id, $add_data);

    }

    // $return_cell = "refresh";
}
$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_event_rsvp';
$scope                 = 'event';
$event                 = new event;
$data                  = $event->get_rsvp($rsvp_id, '1');
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $return['show_saved'] = 'Added Attendee';

} else {
    $return['show_saved'] = 'Update Attendee';

}
$return['refresh_slider'] = '1';
echo "1+++" . json_encode($return);
exit;

