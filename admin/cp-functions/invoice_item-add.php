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
if ($_POST['edit'] == '1') {
    $type = 'edit';
} else {
    $type = 'add';
}
$task = 'invoice_component-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Get the invoice.
$invoice      = new invoice;
$this_invoice = $invoice->get_invoice($_POST['invoice_id']);
// Invoice Components
$table    = 'ppSD_invoice_components';
$primary  = array('');
$ignore   = array('');
$subtotal = 0;
// Build entry data.
$q_data = array();
if (!empty($_POST['tax'])) {
    $q_data['tax'] = $_POST['tax'];
} else {
    $q_data['tax'] = '0';
}
if (!empty($_POST['notify_user'])) {
    $_POST['notify_user'] = '1';
} else {
    $_POST['notify_user'] = '0';
}
if ($_POST['type'] == 'credit') {
    $q_data['name']        = $_POST['credit']['name'];
    $q_data['description'] = $_POST['credit']['description'];
    $q_data['unit_price']  = $_POST['credit']['amount'];
    $q_data['qty']         = '1';
    $q_data['type']        = 'credit';
}
else if ($_POST['type'] == 'newproduct') {
    $type = 'add';
    $cart     = new cart();
    $proddata = array(
        'name'       => $_POST['newproduct']['name'],
        'tagline'    => $_POST['newproduct']['tagline'],
        'price'      => $_POST['newproduct']['price'],
        'type'       => '1',
        'physical'   => '0',
        'tax_exempt' => '0',
        'hide'       => '1',
        'owner'      => $employee['id'],
        'public'     => '0',
    );
    $prodid   = $cart->add_product($proddata);
    $q_data['product_id']  = $prodid;
    $q_data['name']        = $_POST['newproduct']['name'];
    $q_data['description'] = $_POST['newproduct']['tagline'];
    $q_data['qty']         = $_POST['newproduct']['qty'];
    $q_data['unit_price']  = $_POST['newproduct']['price'];

}
else if ($_POST['type'] == 'product') {
    $q_data['product_id'] = $_POST['product']['id'];
    $cart                 = new cart;
    $product              = $cart->get_product($_POST['product']['id']);
    if (empty($_POST['product']['qty'])) {
        $_POST['product']['qty'] = '1';
    }
    if (!empty($product['data']['id'])) {
        $q_data['name']        = $product['data']['name'];

        if (empty($_POST['product']['description'])) {
            $q_data['description'] = $_POST['product']['description'];
        } else {
            $q_data['description'] = $product['data']['tagline'];
        }
        $price                 = $cart->get_product_price($_POST['product']['id']);
        $subtotal += $_POST['product']['qty'] * $price['price'];
        $q_data['qty']        = $_POST['product']['qty'];
        $q_data['unit_price'] = $subtotal;
        $q_data['type']       = 'product';
    } else {
        echo "0+++Product does not exist.";
        exit;
    }

} else {
    $q_data['name']        = $_POST['time']['name'];
    $q_data['description'] = $_POST['time']['description'];
    $q_data['minutes']     = $_POST['time']['minutes'];
    $q_data['qty']         = '1';
    // Get hourly rate
    if ($this_invoice['data']['hourly'] > 0) {
        $hourly = $this_invoice['data']['hourly'];

    } else {
        $hourly = $db->get_option('invoice_hourly');

    }
    // Round up?
    if ($db->get_option('invoice_round_up') == '1') {
        $cost = ceil($_POST['time']['minutes'] / 60) * $hourly;

    } else {
        $cost = round(($_POST['time']['minutes'] / 60) * $hourly, 2);

    }
    $q_data['unit_price'] = $cost;
    $q_data['type']       = 'time';
}
$query_form = $admin->query_from_fields($q_data, $type, $ignore, $primary);
// Edit
if ($type == 'edit') {
    // Update
    $update = $db->update("
        UPDATE `ppSD_invoice_components`
        SET " . ltrim($query_form['u2'], ',') . "
        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $insert = $_POST['id'];
} // Add
else {
    // Insert
    $insert = $db->insert("
        INSERT INTO `ppSD_invoice_components` (`invoice_id`" . $query_form['if2'] . ")
        VALUES ('" . $db->mysql_clean($_POST['invoice_id']) . "'" . $query_form['iv2'] . ")
    ");
}
// Recalculate total
$invoice->recalculate_totals($_POST['invoice_id']);
// Re-cache - done above.
//$invoice->get_invoice($_POST['invoice_id'],'1');
// Notify Client?
if (!empty($_POST['notify_user'])) {
    $invoice->send_invoice($_POST['invoice_id'], '1');

}
$table                    = 'ppSD_invoice_components';
$scope                    = 'invoice';
$return                   = array();
$return['close_popup']    = '1';
$return['refresh_slider'] = '1';
echo "1+++" . json_encode($return);
exit;

