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
$invoice = new invoice();
if (! empty($_GET['id'])) {
    $data    = $invoice->check_invoice($_GET['id'], $_GET['h']);
    if (empty($data)) {
        $db->show_error_page('I006');
        exit;
    } else {
        $temp    = $invoice->generate_template($data['data']['id'], 'invoice_pay', '1');

        if ($db->get_option('invoice_allow_partial') == '1') {
            $payment_info = place_currency('<input type="text" name="amount" value="' . $data['totals']['due'] . '" style="width:100px;" />', '1');
            $temp         = str_replace('%payment_field%', $payment_info, $temp);
            echo $temp;
            exit;
        } else {
            $secure = $invoice->getSecureLink();
            header('Location: ' . $secure . '/pp-cart/invoice_add.php?id=' . urlencode($data['data']['id']) . '&hash=' . urlencode($data['data']['hash']) . '&amount=' . urlencode($data['totals']['due']));
            exit;
        }

    }
} else {
    $db->show_error_page('I006');
    exit;
}

