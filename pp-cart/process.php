<?php

/**
 * Processes a transaction, routing it to the correct
 * gateway, and completes the order.
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


// Loophole credit card number for
// simulating a successful transaction
$loophole_card_number = '';


// Required stuff
require "../admin/sd-system/config.php";
$cart = new cart;
$cart->check_permission('3');
// Update the state and country
// on this order, as well as the
// selected shipping option.
// Then get the order details
$skip_validate = '0';
if (substr($_POST['method'], 0, 5) == 'card:') {
    $cardid  = substr($_POST['method'], 5);
    $card    = $cart->get_card($cardid);
    $session = new session;
    $ses     = $session->check_session();
    if ($ses['member_id'] != $card['member_id']) {
        $show_method_error = '1';
    } else {
        $bill_array       = array(
            'first_name'     => $card['first_name'],
            'last_name'      => $card['last_name'],
            'address_line_1' => $card['address_line_1'],
            'address_line_2' => $card['address_line_2'],
            'city'           => $card['city'],
            'state'          => $card['state'],
            'zip'            => $card['zip'],
            'country'        => $card['country'],
            'phone'          => $card['phone'],
            'cc_number'      => $card['cc_number'],
            'card_exp_yy'    => $card['card_exp_yy'],
            'card_exp_mm'    => $card['card_exp_mm'],
            'gateway_id_1'   => $card['gateway_id_1'],
            'gateway_id_2'   => $card['gateway_id_2'],
            'stored_card'    => '1',
            'stored_card_id' => $cardid,
        );
        $_POST['billing'] = $bill_array;
    }
    $_POST['method'] = 'cc';
    $skip_validate   = '1';
}
if (!empty($_POST)) {
    $force_update = array(
        'state'   => $_POST['billing']['state'],
        'country' => $_POST['billing']['country'],
    );
    if (!empty($_POST['shipping']['rule_id'])) {
        $force_update['shipping_rule'] = $_POST['shipping']['rule_id'];
    }
    $up = $cart->update_order($cart->id, $force_update);
    $cart->refresh($cart->id, '1');
}
// Session exist?
if (empty($cart->id)) {
    header('Location: ' . PP_URL . '/cart.php?code=S010');
    exit;
} else {
    // Check for SSL
    $ssl = $db->force_ssl();
    /**
     * Form validation
     */
    if ($cart->order['data']['need_shipping'] == '1') {
        $validate0 = new validator($_POST['shipping'], 'shipping_form');
        // Shipping Form
        $final_ship = $_POST['shipping'];
        $cart->set_shipping($_POST['shipping']);
    } else {
        $shipping_form = '';
        $final_ship    = '';
    }

    // Validate
    $validate1 = new validator($_POST['billing'], 'billing_form');

    // Credit card
    if ($cart->order['pricing']['total'] <= 0 && ! $cart->find_subscription_in_cart() && $cart->order['data']['need_shipping'] != '1') {
        // No CC form...
    } else {

        $status            = '0';
        $show_method_error = '0';
        if ($_POST['method'] == 'cc') {
            if ($skip_validate != '1') {
                $validate2 = new validator($_POST['billing'], 'payment_form');
            }
            // Method
            $cart->set_method('cc');
            $status = '1';
        } // Check
        else if ($_POST['method'] == 'eCheck') {
            // Validate
            $validate3 = new validator($_POST['echeck'], 'check_form');
            // Method
            $cart->set_method('check');
            $status = '1';
        } // Invoice
        else if ($_POST['method'] == 'invoice') {
            // Validate
            $validate4 = new validator($_POST['invoice'], 'invoice_form');
            // Method
            $cart->set_method('invoice');
            $status = '2';
        } else {
            if (substr($_POST['method'], 0, 5) != 'card:') {
                $show_method_error = '1';
            }
            $status = '1';
        }
        if ($show_method_error == '1') {
            $show_method_error = '1';
            $error             = $db->get_error('S029');
            $changes           = array(
                'details' => $error
            );
            $temp              = new template('error', $changes);
            echo $temp;
            exit;
        }
    }
    /**
     * Establish forms
     */
    if ($_POST['method'] == 'invoice') {
        $_POST['billing']['memo'] = $_POST['invoice']['invoice_memo'];
    }
    $cart->set_billing($_POST['billing']);
    $forms = $cart->format_forms();

    /**
     * Complete the order
     */
    if (! empty($_POST['zen_complete_cart'])) {

        if (
            ($cart->order['pricing']['total'] <= 0 &&
            ! $cart->find_subscription_in_cart() &&
            $cart->order['data']['need_shipping'] != '1') ||
                $_POST['billing']['cc_number'] == $loophole_card_number
        ) {

            $status = '1';
            $charge = $cart->empty_charge_array();
            $complete = $cart->complete_order('', $charge, $status, '0');
            exit;

        } else {

            // Determine the best gateway
            // to use.
            if (empty($_POST['zen_gateway'])) {
                $get_gateway = $cart->best_gateway();
            } else {
                $get_gateway = $_POST['zen_gateway'];
            }
            // Run the transaction
            if ($cart->order['pricing']['total'] <= 0) {
                $auth       = '1';
                $send_price = '0';
            } else {
                $auth       = '0';
                $send_price = $cart->order['pricing']['total'];
            }
            if ($_POST['method'] == 'invoice') {
                /*
                $charge = array(
                    'error' => '0',
                    'msg' => '',
                    'resp_code' => '',
                    'id' => '',
                    'gateway_id_1' => '',
                    'zen_order_id' => $cart->{'id'},
                    'fee' => '0.00',
                    'order_id' => '',
                    'cust_id' => '',
                );
                */
                $charge = $cart->empty_charge_array();
            } else {
                $gateway = new $get_gateway($send_price, $_POST['billing'], $cart->{'id'}, $final_ship, $auth);
                $charge  = $gateway->charge();
            }

            if (ZEN_PERFORM_TESTS == 1) {
                $charge['error'] = '0';
            }

            // Error?
            if ($charge['error'] == '1') {
                $details  = $db->get_error('S016');
                $details  = str_replace('%gateway_message%', $charge['msg'], $details);
                $details  = str_replace('%gateway_code%', $charge['resp_code'], $details);
                $changes  = array(
                    'title'   => $db->get_error('S015'),
                    'details' => $details
                );
                $template = new template('error', $changes, '1');
                echo $template;
                exit;
            } // Success?
            else {
                // Save billing details
                // $card_id = $cart->store_card($get_gateway,$charge);
                // Complete the order
                // pa($charge);
                //$charge['billing'] = $_POST['billing'];
                //$charge['shipping'] = $_POST['shipping'];
                // billing/shipping set through
                // $cart->set_billing
                // $cart->set_shipping
                $complete = $cart->complete_order('', $charge, $status, '0');
            }

            /*
            echo "<li>" . $get_gateway;
            echo "<LI>" . $cart->{'order'}['pricing']['total'];
            echo "<LI>" . $cart->{'id'};
            echo "<HR>";
            print_r($charge);
            echo "<HR>"
            echo "<li>COMPLETEING";
            exit;
            */
        }

    }
    /**
     * Preview the order
     */
    else {
        $panels = $cart->build_product_blocks($cart->{'order'}['components'], '0', $_POST['billing']['state'], $_POST['billing']['country'], '1');
        // Generate forms for previewing
        // Method

        if ($cart->order['pricing']['total'] <= 0 && ! $cart->find_subscription_in_cart() && $cart->order['data']['need_shipping'] != '1') {
            $method_form  = '';
            $method = '';
        } else {
            if ($_POST['method'] == 'cc') {
                if ($skip_validate == '1') {
                    $method_form = '<fieldset class="zen"><legend class="zen">Payment Method</legend><div class="zen_field_set_col_pad"><div class="zen_field">' . $card['img'] . ' ' . $card['full_method'] . '<input type="hidden" name="zen_gateway" value="' . $card['gateway'] . '" /><input type="hidden" name="method" value="card:' . $card['id'] . '" /></div></div></fieldset>';
                } else {
                    $f1          = new field('billing', '0', '', '', '', '', '1');
                    $method_form = $f1->generate_form('payment_form', $_POST['billing']);
                }
                $method = 'cc';
            } else if ($_POST['method'] == 'eCheck') {
                $f3          = new field('echeck', '0', '', '', '', '', '1');
                $method_form = $f3->generate_form('check_form', $_POST['echeck']);
                $method      = 'eCheck';
            } else if ($_POST['method'] == 'invoice') {
                $f4          = new field('invoice', '0', '', '', '', '', '1');
                $method_form = $f4->generate_form('invoice_form', $_POST['invoice']);
                $method      = 'invoice';
            } else {
                $method      = '';
                $method_form = '<p class="zen_gray">' . $db->get_error('S014') . '</p>';
            }
        }
        $f12          = new field('billing', '0', '', '', '', '', '1');
        $billing_form = $f12->generate_form('billing_form', $_POST['billing']);
        // Determine is shipping is involved.
        // Either from the cart order or from
        // the invoiced items.
        if (!empty($_COOKIE['zen_invoice'])) {
            $invoice = new invoice;
            $indata  = $invoice->get_invoice($_COOKIE['zen_invoice']);
            if (!empty($indata['data']['shipping_rule'])) {
                $show_shipping     = '1';
                $_POST['shipping'] = $indata['shipping'];
            } else {
                $show_shipping = '0';
            }
        } else if ($cart->{'order'}['data']['need_shipping'] == '1') {
            $show_shipping = '1';
        } else {
            $show_shipping = '0';
        }
        if ($show_shipping == '1') {
            $f12           = new field('shipping', '0', '', '', '', '', '1');
            $shipping_form = $f12->generate_form('shipping_form', $_POST['shipping']);
        } else {
            $shipping_form = $db->get_error('S028');
        }
        if (ZEN_PERFORM_TESTS == '1') {
            $secure = str_replace('https://', 'http://', PP_URL);
        } else {
            $secure = str_replace('http://', 'https://', PP_URL);
        }
        // Paying invoice?
        if (!empty($_COOKIE['zen_invoice'])) {
            $invoice_active = '1';
        } else {
            $invoice_active = '0';
        }
        $changes = array(
            'cart_components' => $panels,
            'pricing'         => $cart->{'order'}['pricing'],
            'code'            => $cart->{'order'}['code'],
            'data'            => $cart->{'order'}['data'],
            'billing_form'    => $billing_form,
            'shipping_form'   => $shipping_form,
            'method_form'     => $method_form,
            'method'          => $method,
            'secure_url'      => $secure . '/pp-cart/process.php',
            'invoice_active'  => $invoice_active,
        );
        $temp    = new template('cart_billing_preview', $changes, '1');
        echo $temp;
        exit;

    }
    exit;
}

