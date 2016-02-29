<?php

/**
 * Update a cart order.
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
// Load the basics
require "../admin/sd-system/config.php";
$cart = new cart();
/**
 * Permissions:
 * 0 : None
 * 1 : View Catalog
 * 2 : View Cart
 * 3 : Checkout
 */
$cart->check_permission('2');
/**
 * Update cart contents
 */
$notice  = '';
$snotice = '';
foreach ($cart->order['components'] as $product) {
    $qty_new = $_POST['qty'][$product['id']];
    // No cheating on invoices
    if (!empty($_COOKIE['zen_invoice'])) {
        $data = explode('|||', $_COOKIE['zen_invoice']);
        if ($data['1'] == $product['id'] && $qty_new <= 0) {
            $db->delete_cookie('zen_invoice');
            $snotice = 'I017';
        } else if ($qty_new > 1) {
            $qty_new = '1';
            $notice  = 'I016';
        }
    }
    if ($qty_new > 0) {
        $check = $cart->check_addition($product['data']['id'], $qty_new, '', '1');
        if ($check['error'] == '1') {
            $db->show_error_page($check['code'], $check['changes']);
            exit;
        } else {
            $q = $db->update("
                UPDATE
                    `ppSD_cart_items`
                SET
                    `qty`='" . $db->mysql_clean($qty_new) . "'
                WHERE
                    `id`='" . $db->mysql_clean($product['id']) . "' AND
                    `cart_session`='" . $db->mysql_clean($cart->{'id'}) . "'
                LIMIT 1
            ");
        }
    } else {
        $q = $db->delete("
            DELETE FROM
                `ppSD_cart_items`
            WHERE
                `id`='" . $db->mysql_clean($product['id']) . "' AND
                `cart_session`='" . $db->mysql_clean($cart->{'id'}) . "'
            LIMIT 1
        ");
    }
}
/**
 * Reset savings code.
 * Required due to "minimum
 * cart total" savings codes.
 */
if (!empty($cart->{'order'}['data']['code'])) {
    $recalc = $cart->calculate_order_total();
    $put    = $cart->apply_savings_code($cart->order['data']['code']);
    if ($put['error'] == '1') {
        $cart->remove_savings_code();
    }
}
header('Location: ' . PP_URL . '/cart.php?code=' . $notice . '&scode=' . $snotice);
exit;

