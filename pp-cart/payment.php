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
require "../admin/sd-system/config.php";
$cart = new cart();
$cart->check_permission('3');

// No session!
if (empty($cart->id)) {

    header('Location: ' . PP_URL . '/cart.php?code=S010');
    exit;

} else {

    // Terms?
    $cart->check_terms();
    $cart->check_forms();

    // Session
    $session = new session;
    $ses     = $session->check_session();

    // Check for SSL
    $ssl            = $db->force_ssl('1');

    // Zero dollar cart?
    /*
    if ($cart->order['pricing']['total'] <= 0 && ! $cart->find_subscription_in_cart()) {
        $status = '1';
        $charge = $cart->empty_charge_array();
        $complete = $cart->complete_order('', $charge, $status, '0');
        exit;
    }
    */

    // proceed...
    $submitted_data = array();
    if (!empty($cart->order['data']['reg_session'])) {
        $form                       = new form($cart->{'order'}['data']['reg_session']);
        $submitted_data['billing']  = $form->assemble_data();
        $submitted_data['shipping'] = $form->assemble_data();
    }
    else if (!empty($_COOKIE['zen_invoice'])) {
        $exp                            = explode('|||', $_COOKIE['zen_invoice']);
        $invoice                        = new invoice;
        $idata                          = $invoice->get_invoice($exp['0']);
        $name                           = explode(' ', $idata['billing']['contact_name']);
        $idata['billing']['first_name'] = $name['0'];
        $idata['billing']['last_name']  = $name['1'];
        $submitted_data['billing']      = $idata['billing'];
        $submitted_data['shipping']     = $idata['shipping'];
    }
    else if (!empty($ses['member_id'])) {
        $user                       = new user;
        $udata                      = $user->get_user($ses['member_id']);
        $submitted_data['billing']  = $udata['data'];
        $submitted_data['shipping'] = $udata['data'];
    }

    // Get panels
    $small_panels = $cart->format_small_panels($cart->{'order'}['components']);

    // Require shipping?
    if ($cart->order['data']['need_shipping'] == '1') {
        $f3            = new field('shipping');
        $shipping_form = $f3->generate_form('shipping_form', $submitted_data);
    } else {
        $shipping_form = '';
    }

    // Billing Details
    $f12          = new field('billing');
    $billing_form = $f12->generate_form('billing_form', $submitted_data);

    // Get active gateways
    $methods = $cart->organize_gateways();

    // Credit Card
    $invoice_form = '';
    $payment_form = '';
    $check_form   = '';
    $initial_form = '';

    if ($cart->order['pricing']['total'] <= 0 && ! $cart->find_subscription_in_cart() && $cart->order['data']['need_shipping'] != '1') {

    } else {
        if ($methods['do_cc'] == '1') {
            $f1           = new field('billing');
            $initial_form = $f1->generate_form('payment_form');
        }
        else if ($methods['do_check'] == '1') {
            $f3           = new field('echeck');
            $initial_form = $f3->generate_form('check_form');
        }
        else if ($methods['invoice'] == '1') {
            $f4           = new field('invoice');
            $initial_form = $f4->generate_form('invoice_form');
        }
        else {
            $initial_form = '<p class="zen_gray">' . $this->get_error('S014') . '</p>';
        }
    }
    // Credit cards on file?
    if ($ses['error'] != '1') {
        $credit_cards = $cart->get_credit_cards($ses['member_id']);
    } else {
        $credit_cards = '';
    }
    if (ZEN_PERFORM_TESTS == '1') {
        $secure = str_replace('https://', 'http://', PP_URL);
    } else {
        $secure = str_replace('http://', 'https://', PP_URL);
    }
    // $secure = str_replace('http://','http://',PP_URL);
    // Paying invoice?
    if (!empty($_COOKIE['zen_invoice'])) {
        $invoice_active = '1';
    } else {
        $invoice_active = '0';
    }
    // Shipping options
    $shipping_options = $cart->get_flat_shipping($cart->order['data']['shipping_rule']);
    $changes          = array(
        'cart_components' => $small_panels,
        'pricing'         => $cart->order['pricing'],
        'data'            => $cart->order['data'],
        'code'            => $cart->order['code'],
        'method_form'     => $initial_form,
        'cards_on_file'   => $credit_cards,
        'billing_form'    => $billing_form,
        'shipping_form'   => $shipping_form,
        'ship_options'    => $shipping_options,
        'payment_methods' => $methods['method_list'],
        'cc_imgs'         => $methods['cc_imgs'],
        'secure_url'      => $secure . '/pp-cart/process.php',
        'invoice_active'  => $invoice_active,
    );
    $temp             = new template('cart_billing', $changes, '1');
    echo $temp;
    exit;

}

