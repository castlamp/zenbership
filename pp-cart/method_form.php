<?php

/**
 * Get a method form type
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

$cart = new cart();
if ($_POST['id'] == 'cc') {
    $f1           = new field('billing');
    $initial_form = $f1->generate_form('payment_form');
}
else if ($_POST['id'] == 'eCheck') {
    $f3           = new field('echeck');
    $initial_form = $f3->generate_form('check_form');
}
else if ($_POST['id'] == 'invoice') {
    $f4           = new field('invoice');
    $initial_form = $f4->generate_form('invoice_form');
}
else if ($_POST['id'] == 'paypal') {
    $paypal = new gw_paypal;
    $link = $paypal->checkout();
    echo "1+++redirect+++" . $link;
    exit;
}
else {
    echo "0+++Could not find payment method.";
    exit;
}

echo "1+++$initial_form";
exit;

