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
$task = 'invoice_dead';
// Check permissions and employee
$admin    = new admin;
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$invoice  = new invoice;
$data     = $invoice->get_invoice($_POST['id']);
if ($data['data']['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
    echo "0+++You cannot alter this invoice.";
    exit;

} else {
    $invoice->mark_dead($_POST['id']);
    $invoice->get_invoice($_POST['id'], '1');

}
$task = $db->end_task($task_id, '1');
echo "1+++" . $_POST['id'];
exit;



