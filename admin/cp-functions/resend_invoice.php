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
 * Re-sends an invoice to a client.
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
 * @date        12/6/12 5:28 PM
 * @version     v1.0
 */
require "../sd-system/config.php";
$task     = 'invoice-email';
$admin    = new admin;
$employee = $admin->check_employee($task);
if (!empty($_POST['id'])) {
    $invoice = new invoice;
    $data    = $invoice->get_invoice($_POST['id'], '1');
    if ($employee['id'] != $data['data']['owner'] && $employee['permissions']['admin'] != '1' && $employee['data']['public'] != '1') {
        echo "0+++You cannot edit this invoice.";
        exit;

    } else {
        $send = $invoice->send_invoice($_POST['id']);
        echo "1+++Invoice sent.";
        exit;

    }

} else {
    echo "0+++Could not find invoice.";
    exit;

}
echo $data;
exit;



