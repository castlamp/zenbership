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
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'promo_code-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
/**
 * Checks and formatting

 */
// Find code
if ($type != 'edit') {
    $cart     = new cart;
    $get_code = $cart->get_savings_code($_POST['id']);
    if (!empty($get_code['id'])) {
        echo "0+++Code already exists.";
        exit;

    }

}
if ($_POST['date_start'] == '1920-01-01 00:01:01') {
    $_POST['date_start'] = '';

}
if ($_POST['date_end'] == '1920-01-01 00:01:01') {
    $_POST['date_end'] = '';

}
// Start/end date
if (!empty($_POST['date_end']) && !empty($_POST['date_start'])) {
    if ($_POST['date_end'] <= $_POST['date_start']) {
        echo "0+++End date must be after start date.";
        exit;

    }

}
if (!empty($_POST['date_end']) && $_POST['date_end'] <= current_date()) {
    echo "0+++End date must be in the future.";
    exit;

}
if ($_POST['type'] == 'percent_off') {
    if (empty($_POST['percent_off']) || !is_numeric($_POST['percent_off'])) {
        echo "0+++Valid discount required (E1).";
        exit;

    }
    $_POST['dollars_off']   = '';
    $_POST['flat_shipping'] = '';

} else if ($_POST['type'] == 'dollars_off') {
    if (empty($_POST['dollars_off']) || !is_numeric($_POST['dollars_off'])) {
        echo "0+++Valid discount required (E2).";
        exit;

    }
    $_POST['percent_off']   = '';
    $_POST['flat_shipping'] = '';

} else {
    if (empty($_POST['flat_shipping']) || !is_numeric($_POST['flat_shipping'])) {
        echo "0+++Valid discount required (E3).";
        exit;

    }
    $_POST['percent_off'] = '';
    $_POST['dollars_off'] = '';

}
// Products
$prods = '';
if (!empty($_POST['products'])) {
    foreach ($_POST['products'] as $aProd) {
        $prods .= ',' . $aProd;
    }
    $prods = ltrim($prods, ',');

}
// Primary fields for main table
$primary    = array();
$ignore     = array('edit', 'products');
$query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
if ($type == 'edit') {
    $update_set2 = $query_form['u2'];
    $q           = $db->update("

		UPDATE `ppSD_cart_coupon_codes`

		SET `products`='" . $db->mysql_clean($prods) . "'$update_set2

		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'

		LIMIT 1

	");
    //$return_cell = "";
    $id = $_POST['id'];

} else {
    $insert_fields2 = $query_form['if2'];
    $insert_values2 = $query_form['iv2'];
    $qin            = $db->insert("

		INSERT INTO `ppSD_cart_coupon_codes` (`created`,`public`,`products`,`owner`$insert_fields2)

		VALUES ('" . current_date() . "','1','" . $db->mysql_clean($prods) . "','" . $employee['id'] . "'$insert_values2)

	");

    //$history = new history($_POST['id'],'','','','','','ppSD_cart_coupon_codes');
    //$return_cell = $history->{'table_cells'};
}
$table                 = 'ppSD_cart_coupon_codes';
$scope                 = 'promo_code';
$history               = new history($_POST['id'], '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Promotional Code';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Promotional Code';

}
echo "1+++" . json_encode($return);
exit;
