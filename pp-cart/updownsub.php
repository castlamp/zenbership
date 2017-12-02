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

$cart         = new cart;
$subscription = new subscription;

if (! empty($_GET['id'])) {
    $sub = $subscription->get_subscription($_GET['id']);
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

$updown = new subupdown($_GET['id'], $_GET['d']);

// getNewPrice
$url = PP_URL . '/pp-cart/manage_subscription.php?id=' . $sub['data']['id'] . '&s=' . $sub['data']['salt'];

if ($updown->error) {
    header('Location: ' . $url . '&code=S071');
    exit;
} else {
    $return = array(
        'show_saved' => 'Done. ' . $updown->charge['zen_order_id'],
        'refresh_slider' => '1',
    );

    header('Location: ' . $url . '&scode=S072');
    exit;
}
