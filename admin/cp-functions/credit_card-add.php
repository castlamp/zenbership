<?php

/**
 * Add a credit card.
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
require "../sd-system/config.php";
$type        = 'add';
$update_id   = '';
$update_key  = '';
$table       = 'ppSD_cart_billing';
$scope       = 'credit_card';
$task        = $scope . '-' . $type;
$admin       = new admin;
$employee    = $admin->check_employee();
$permissions = new permissions($scope, $type, $update_id, $table);
$task_id     = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$cart        = new cart();
if (!empty($_POST['subscription_id'])) {
    $subs    = new subscription();
    $sub     = $subs->get_subscription($_POST['subscription_id']);
    $user_id = $sub['data']['member_id'];

} else {
    $user_id = $_POST['user_id'];

}

$add = $cart->add_card_with_verify($_POST['billing'], $user_id, $_POST['gateway']);
if (!empty($_POST['subscription_id'])) {
    $q1                       = $db->update("
        UPDATE `ppSD_subscriptions`
        SET `card_id`='" . $db->mysql_clean($add['0']) . "'
        WHERE `id`='" . $db->mysql_clean($_POST['subscription_id']) . "'
        LIMIT 1
    ");
    $return                   = array();
    $return['close_popup']    = '1';
    $return['refresh_slider'] = '1';
    $return['show_saved']     = 'Updated subscription\'s credit card.';

} else {
    $history                    = new history($add['0'], '', '', '', '', '', $table);
    $content                    = $history->final_content;
    $table_format               = new table($scope, $table);
    $return                     = array();
    $return['close_popup']      = '1';
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Added Credit Card';

}
$task = $db->end_task($task_id, '1');
echo "1+++" . json_encode($return);
exit;



