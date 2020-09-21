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

// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task = 'transaction-email';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Event Query
// $query_form = $admin->query_from_fields($_POST['order'],$type,$ignore,$primary);
$cart = new cart;
$order = $cart->get_order($_POST['id'], '0');

if (empty($order['billing']['email'])) {
    echo "0+++No email associated with the order.";
    exit;
} else {
    $cart->send_receipt($_POST['id']);
}

// Re-cache
$return       = array();
$return['show_saved'] = 'Transaction Receipt Sent to ' . $order['billing']['email'];
echo "1+++" . json_encode($return);
exit;
