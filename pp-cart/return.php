<?php

/**
 * 3rd Party Gateway "Return to Site"
 * entry point. This presumes that any
 * "IPN" style code has already been
 * executed in the background before
 * the user reached this point.
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

$cart = new cart;
$salt = $cart->return_salt();

if ($_GET['in'] == $salt) {
    // complete_order
    // status
    $use_order = $cart->get_order($_COOKIE['zen_cart'], '1');
    // $dif = time() - strtotime($use_order['data']['return_time_out']);
    // Confirm code and confirm the user has been gone for
    // at least 15 seconds.
    if (
        $use_order['data']['return_code'] == $_GET['s'] &&
        (! empty($_COOKIE['zen_ret']) && $_COOKIE['zen_ret'] == $_GET['s'])
    ) {
        if ($use_order['data']['status'] == '1') {
            $cart->remove_cookies();
            header('Location: ' . $use_order['data']['url']);
            exit;
        } else {
            $cart->complete_order($use_order['data']['id']);
        }

    } else {
        echo "Error RET01 - unable to complete process.";
        exit;
    }
} else {
    echo "Error RET02 - unable to complete process.";
    exit;
}
