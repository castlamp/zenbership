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
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'invoice_payment-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Get the invoice.
$invoice      = new invoice;
$this_invoice = $invoice->get_invoice($_POST['invoice_id']);
$ownership    = new ownership($this_invoice['data']['owner'], '0');
if ($ownership->result != '1') {
    echo "0+++You cannot alter this invoice.";
    exit;

}
$id = $_POST['id'];
if ($type == 'add') {

    $cart = new cart();
    $cart->empty_cart();

    if (empty($_POST['order_id'])) {
        $_POST['order_id'] = $cart->generate_cart_id();
    } else {
        $found = $cart->order_exists($_POST['order_id']);
        if ($found) {
            echo "0+++Order ID (reference no) already exists. Please select a different one.";
            exit;
        }
    }

    $new_status = $invoice->apply_payment($_POST['invoice_id'], $_POST['paid'], $_POST['order_id'], $_POST['date']);
    $id         = $new_status['id'];
    $history    = new history($id, '', '', '', '', '', 'ppSD_invoice_payments');
    $return_row = $history->table_cells;

    // -----------------------------

    $proddata      = array(
        'name'          => 'Invoice Payment for Invoice ' . $_POST['invoice_id'],
        'type'          => '1',
        'physical'      => '0',
        'tax_exempt'    => '0',
        'price'         => $_POST['paid'],
        'hide'          => '1',
        'hide_in_admin' => '1',
        'owner'         => $employee['id'],
        'public'        => '0',
    );
    $prodid        = $cart->add_product($proddata);

    $start_session = $cart->start_session($this_invoice['data']['member_id'], '1', $_POST['order_id']);

    $updata        = array(
        'invoice_id' => $_POST['invoice_id'],
        'date' => $_POST['date'],
    );
    $cart->update_order($_POST['order_id'], $updata);

    $add      = $cart->add($prodid, '1', '', $this_invoice['data']['member_id'], '', $_POST['order_id'], '', '1', '1');

    $charge   = array(
        'id'        => '',
        'order_id'  => '',
        'resp_code' => '',
        'msg'       => '',
        'fee'       => '',
    );
    $complete = $cart->complete_order($_POST['order_id'], $charge, '1', '1', $_POST['date']);

    // date
    $updata        = array(
        'date' => $_POST['date'],
        'date_completed' => $_POST['date'],
    );

    $cart->update_order($_POST['order_id'], $updata);



} else {

    /*

    $q1 = $db->update("

        UPDATE `ppSD_invoice_payments`

        SET

            `paid`='" . $db->mysql_clean($_POST['paid']) . "',

            `order_id`='" . $db->mysql_clean($_POST['order_id']) . "',

            `date`='" . $db->mysql_clean($_POST['date']) . "'

        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

        LIMIT 1

    ");



    $new_status = $invoice->recalculate_totals($_POST['invoice_id']);

    $invoice->get_invoice($_POST['invoice_id'],'1'); // Recache



    $return_row = 'close_popup';

    */

}
if ($new_status['status'] != '1') {
    $invoice->send_invoice($_POST['invoice_id'], '1');

}
$task                  = $db->end_task($task_id, '1');
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $return['show_saved'] = 'Add Payment';

} else {
    $return['show_saved'] = 'Updated Payment';

}
$return['refresh_slider'] = '1';
echo "1+++" . json_encode($return);
exit;

