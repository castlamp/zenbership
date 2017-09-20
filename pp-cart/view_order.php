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
 * Zenbership
 *
 * View an order after it
 * was placed.
 */
// Required stuff
require "../admin/sd-system/config.php";
if (!empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    show_view_error();
}
$cart = new cart($id, '0');
if (empty($cart->order['data']['id'])) {
    show_view_error();
    exit;
}
// Salt?
if ($cart->order['data']['salt'] != $_GET['s']) {
    show_view_error();
}

// Owned by a member?
/*
if (! empty($cart->order['data']['member_id']) && $cart->order['data']['member_type'] == 'member') {
    $session = new session;
    $ses     = $session->check_session();
    if ($ses['member_id'] != $cart->order['data']['member_id']) {
        show_view_error('S031');
    }
}
*/

$billing_data = $cart->order_card_info($cart->order['data']['card_id']);
// Shipping
if ($cart->order['data']['need_shipping'] == '1') {
    $f12           = new field('shipping', '0', '', '', '', '', '1');
    $shipping_form = $f12->generate_form('shipping_form', $cart->order['shipping_info']);
    // Shipped?
    if ($cart->order['shipping_info']['shipped'] == '1') {
        $status = $db->get_error('S032');
        $status = str_replace('%ship_date%', format_date($cart->order['shipping_info']['ship_date']), $status);
        $status = str_replace('%ship_provider%', $cart->order['shipping_info']['shipping_provider'], $status);
        // Tracking?
        if ($cart->order['shipping_info']['trackable'] == '1') {
            $tracking   = $db->get_error('S034');
            $track_link = $cart->tracking_link($cart->order['shipping_info']['shipping_number'], $cart->order['shipping_info']['shipping_provider']);
            $tracking   = str_replace('%tracking_number%', $cart->order['shipping_info']['shipping_number'], $tracking);
        } else {
            $tracking   = $db->get_error('S035');
            $track_link = $db->get_error('S037');
        }
    } else {
        $status     = $db->get_error('S033');
        $tracking   = $db->get_error('S035');
        $track_link = $db->get_error('S037');
    }
    $cart->{'shipping'}['tracking'] = $tracking;
    $cart->{'shipping'}['status']   = $status;
    $cart->{'shipping'}['link']     = $track_link;
} else {
    $shipping_form                  = $db->get_error('S028');
    $cart->{'shipping'}['tracking'] = $db->get_error('S035');
    $cart->{'shipping'}['status']   = $db->get_error('S033');
    $cart->{'shipping'}['link']     = $db->get_error('S037');
}
$f12           = new field('billing', '0', '', '', '', '', '1');
$billing_form  = $f12->generate_form('billing_form', $billing_data);
$show_products = $cart->build_product_blocks($cart->order['components'], '0', $cart->order['data']['state'], $cart->order['data']['country']);
$changes       = array(
    'data'            => $cart->order['data'],
    'cart_components' => $show_products,
    'billing'         => $billing_data,
    'billing_form'    => $billing_form,
    'shipping_form'   => $shipping_form,
    'shipping'        => $cart->{'shipping'},
    'pricing'         => $cart->order['pricing'],
);
$temp          = new template('cart_view_order', $changes, '1');
echo $temp;
exit;
/**
 * Show cart error.
 */
function show_view_error($code = 'S030')
{
    global $db;
    $changes = array(
        'details' => $db->get_error($code)
    );
    $temp    = new template('error', $changes);
    echo $temp;
    exit;
}


