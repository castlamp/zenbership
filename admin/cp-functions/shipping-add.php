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
$type  = 'edit';
$task  = 'transaction-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Get order
$cart  = new cart;
$order = $cart->get_order($_POST['id'], '0');
// Primary fields for main table
$primary    = array();
$ignore     = array('id', 'edit');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
$update     = ltrim($query_form['u2'], ',');
$q1         = $db->update("

    UPDATE `ppSD_shipping`

    SET `ship_date`='" . current_date() . "',$update

    WHERE `cart_session`='" . $db->mysql_cleans($_POST['id']) . "'

    LIMIT 1

");
// Email User?
if ($order['shipping_info']['shipped'] != '1' && $_POST['shipped'] == '1') {
    if ($_POST['trackable'] == '1') {
        $track_link = $cart->tracking_link($_POST['shipping_number'], $_POST['shipping_provider']);

    } else {
        $track_link = $db->get_error('S035');

    }
    $show_products       = $cart->build_product_blocks($order['components'], '0', $order['data']['state'], $order['data']['country']);
    $changes             = array(
        'shipping_provider' => $_POST['shipping_provider'],
        'shipping_number'   => $_POST['shipping_number'],
        'remarks'           => $_POST['remarks'],
        'tracking_link'     => $track_link,
        'products'          => $show_products,
        'shipping_address'  => $order['shipping_info']['format_address'],
    );
    $changes['shipping'] = $order['shipping_info'];
    $changes['order']    = $order['data'];
    $changes['pricing']  = $order['pricing'];
    if ($order['data']['member_type'] == 'member') {
        $type = 'member';

    } else {
        $type = 'contact';

    }
    $email = new email('', $order['data']['member_id'], $type, '', $changes, 'order_shipped');

}
// Re-cache
$cart->get_order($_POST['id'], '0', '1');
// Complete task
$task                     = $db->end_task($task_id, '1');
$return                   = array();
$return['close_popup']    = '1';
$return['show_saved']     = 'Updated Shipping';
$return['refresh_slider'] = '1';
echo "1+++" . json_encode($return);
exit;

