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
if (!empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $changes = array(
        'details' => $db->get_error('S038')
    );
    $temp    = new template('error', $changes);
    echo $temp;
    exit;
}
$cart         = new cart;
$billing_data = $cart->get_card($id);
$error        = 0;
if (empty($billing_data['id']) || $billing_data['salt'] != $_GET['s']) {
    $error = '1';
} else {
    if (!empty($billing_data['member_id'])) {
        $session = new session;
        $ses     = $session->check_session();
        if ($ses['member_id'] != $billing_data['member_id']) {
            $error = '1';
        }
    }
}
if ($error == '1') {
    $changes = array(
        'details' => $db->get_error('S038')
    );
    $temp    = new template('error', $changes);
    echo $temp;
    exit;
} else {
    $changes = array(
        'method_form'  => $method_form,
        'billing_form' => $billng_form,
    );
    $temp    = new template('cart_manage_card', $changes, '1');
    echo $temp;
    exit;
}

