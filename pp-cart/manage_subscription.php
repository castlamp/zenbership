<?php

/**
 * View an order after it was placed.
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
// Required stuff
require "../admin/sd-system/config.php";
$cart         = new cart;
$subscription = new subscription;
if (! empty($_GET['id'])) {
    $sub          = $subscription->get_subscription($_GET['id']);
    if (! empty($sub['data'])) {
        if (empty($_GET['s']) || $sub['data']['salt'] != $_GET['s']) {
            $db->show_error_page('S042');
            exit;
        }
    } else {
        $db->show_error_page('S042');
        exit;
    }
} else {
    $db->show_error_page('S042');
    exit;
}
$nocard = 0;
if (empty($sub['data']['card_id'])) {
    $nocard = 1;
} else {
    $billing_data = $cart->get_card($sub['data']['card_id']);
    if (empty($billing_data['id'])) {
        $nocard = 1;
    }
}
if ($nocard == '1') {
    $secure       = str_replace('http://', 'https://', PP_URL);
    $billing_data = array(
        'full_method' => '<a href="' . $secure . '/pp-cart/add_card.php?sub=' . $sub['data']['id'] . '&subs=' . $sub['data']['salt'] . '">' . $db->get_error('G001') . '</a>',
        'img'         => '',
        'id'          => '',
        'user_delete_link' => '',
    );
}
$history = '';
foreach ($sub['charges'] as $charge) {
    $order   = $cart->get_order($charge['id'], '0');
    $changes = array(
        'data'    => $order['data'],
        'pricing' => $order['pricing'],
    );
    $history .= new template('manage_billing_history_entry', $changes, '0');
}

$changes = array(
    'data'    => $sub['data'],
    'product' => $sub['product'],
    'billing' => $billing_data,
    'history' => $history,
);
$temp    = new template('cart_manage_subscription', $changes, '1');
echo $temp;
exit;


