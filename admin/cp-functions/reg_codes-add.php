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
// page
// display
// Load the basics
require "../sd-system/config.php";
// Check permissions and employee
$task     = 'reg_codes-add';
$admin    = new admin;
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$form     = new form('', '', $_POST['id']);
if ($form->formdata['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1') {
    echo "0+++You do not have permission to run this task.";
    exit;

}
$added = 0;
// Generate
if ($_POST['code_type'] == 'gen') {
    if (empty($_POST['qty']) || !is_numeric($_POST['qty'])) {
        $qty = 1;

    } else {
        $qty = $_POST['qty'];

    }
    $total = $qty;
    while ($total > 0) {
        $code = generate_id($_POST['format'], '29');
        $good = $form->add_code($_POST['id'], $code);
        if ($good) {
            $added++;

        }
        $total--;

    }

} // Email list
else {
    $list   = str_replace("\r\n", "\n", $_POST['codes']);
    $emails = explode("\n", $list);
    foreach ($emails as $anEmail) {
        $code = generate_id($_POST['format'], '29');
        $good = $form->add_code($_POST['id'], $code, trim($anEmail));
        if ($good) {
            $added++;

        }

    }

}
// Re-cache
$task                     = $db->end_task($task_id, '1');
$return                   = array();
$return['show_saved']     = 'Codes Issued.';
$return['redirect_popup'] = array(
    'page'   => 'form_codes',
    'fields' => 'form_id=' . $_POST['id'] . '&load_tab=1',
);
echo "1+++" . json_encode($return);
exit;

