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

$scope = 'product';
$task  = $scope . '-' . $type;
$hook_task = 'product_' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);

$task_id  = $db->start_task($hook_task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$cart    = new cart;
$table   = 'ppSD_products';
$primary = array('');
$ignore  = array('original_id_dud');



if ($type == 'edit') {
    $options = array(
        'skip_default' => '1',
        'edit' => '1',
    );
} else {
    $options = array(
        'skip_default' => '0',
        'edit' => '0',
    );
}
$rules = array(
    'id' => array('required','basicsymbols'),
    //'tagline' => array('required'),
    'name' => array('required','basicsymbols'),
    'min_per_cart' => array('numeric'),
    'max_per_cart' => array('numeric'),
    'physical' => array('bool'),
    'base_popularity' => array('numeric'),
    'price' => array('price'),
);
$validate = new ValidatorV2($_POST['product'], $rules, $options);
if ($validate->error_found == '1') {
    echo "0+++" . $validate->plain_english;
    exit;
}

// Check if ID exist
if ($type == 'add') {
    $find = $cart->get_product($_POST['id']);
    if ($find['error'] != '1') {
        echo "0+++Product ID already exists.";
        exit;
    }
}

// Physical?
if ($_POST['product']['physical'] == '1') {
    if (empty($_POST['product']['weight']) || $_POST['product']['weight'] <= 0) {
        $_POST['product']['weight'] = '0.01';
    }
}

$hold_timeframe = $_POST['product']['renew_timeframe'];

// Format timeframe
if ($_POST['product']['type'] == '1') {
    $_POST['product']['renew_timeframe'] = '';
    $_POST['product']['trial_price']     = '';
    $_POST['product']['trial_period']    = '';
    $_POST['product']['trial_repeat']    = '';

} // Subscription product
// product[renew_timeframe][threshold_month]
// product[renew_timeframe][threshold_year]
else if ($_POST['product']['type'] == '2') {
    $_POST['product']['renew_timeframe'] = $admin->construct_complex_timeframe($_POST['product']['renew_timeframe']);
    $_POST['product']['trial_price']     = '';
    $_POST['product']['trial_period']    = '';
    $_POST['product']['trial_repeat']    = '';

} // Trial
else if ($_POST['product']['type'] == '3') {
    if (empty($_POST['product']['trial_period']['number'])) {
        echo "0+++Input a trial period.";
        exit;

    }
    $_POST['product']['renew_timeframe'] = $admin->construct_complex_timeframe($_POST['product']['renew_timeframe']);
    $_POST['product']['trial_period']    = $admin->construct_timeframe($_POST['product']['trial_period']['number'], $_POST['product']['trial_period']['unit']);

}
// Threshold Date?
if (!empty($hold_timeframe['threshold_month']) && !empty($hold_timeframe['threshold_day'])) {
    $_POST['product']['threshold_date'] = $hold_timeframe['threshold_month'] . $hold_timeframe['threshold_day'];

}
// Terms
if ($_POST['terms']['existing'] == 'existing') {
    $_POST['product']['terms'] = $_POST['terms']['id'];

} else if ($_POST['terms']['existing'] == 'terms') {
    $term_data                 = array(
        'name'  => $_POST['terms']['name'],
        'terms' => $_POST['terms']['data'],
    );
    $add                       = $cart->add_terms($term_data);
    $_POST['product']['terms'] = $add;

} else {
    $_POST['product']['terms'] = '';

}
// Event Query
$query_form = $admin->query_from_fields($_POST['product'], $type, $ignore, $primary);
// ----------------------------
$return     = array();
$product_id = $_POST['product']['id'];

if ($type == 'edit') {

    // Update primary
    $update_set1 = substr($query_form['u2'], 1);
    $q           = $db->update("
		UPDATE `ppSD_products`
		SET $update_set1
		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
		LIMIT 1
	");

    // First we delete all of the items
    // belonging to this product
    $q1  = $db->delete("
        DELETE FROM `ppSD_products_options`
        WHERE `product_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $qa1 = $db->delete("
        DELETE FROM `ppSD_products_options_qty`
        WHERE `product_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $q2  = $db->delete("
        DELETE FROM `ppSD_products_tiers`
        WHERE `product_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $q3  = $db->delete("
        DELETE FROM `ppSD_access_granters`
        WHERE `item_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $q4  = $db->delete("
        DELETE FROM `ppSD_product_dependencies`
        WHERE `product_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");

    $q5  = $db->delete("
            DELETE FROM `ppSD_product_upsell`
            WHERE `product`='" . $db->mysql_clean($_POST['id']) . "'
        ");


    // Update forms if the ID changed.
    if ($_POST['id'] != $_POST['product']['id']) {
        $q5  = $db->update("
            UPDATE `ppSD_form_products`
            SET `product_id`='" . $db->mysql_clean($_POST['product']['id']) . "'
            WHERE `product_id`='" . $db->mysql_clean($_POST['id']) . "'
        ");
        $q5  = $db->update("
            UPDATE `ppSD_subscriptions`
            SET `product`='" . $db->mysql_clean($_POST['product']['id']) . "'
            WHERE `product`='" . $db->mysql_clean($_POST['id']) . "'
        ");
        /*
        $q5  = $db->update("
            UPDATE `ppSD_product_upsell`
            SET `product`='" . $db->mysql_clean($_POST['product']['id']) . "'
            WHERE `product`='" . $db->mysql_clean($_POST['id']) . "'
        ");
        */
    }

    /*

    // Next we re-create everything

    // according to the new data.

    if (! empty($_POST['option'])) {

        $cart->create_product_options($product_id,$_POST['option']);

    }

    if (! empty($_POST['tiers'])) {

        $cart->create_product_tiers($product_id,$_POST['tiers']);

    }

    if (! empty($_POST['content'])) {

        $admin->create_product_access_granting($product_id,$_POST['content']);

    }

    */
    // Update images
    if ($_POST['id'] != $_POST['product']['id']) {
        $q2 = $db->update("
            UPDATE `ppSD_uploads`
            SET `item_id`='" . $db->mysql_cleans($_POST['product']['id']) . "'
            WHERE `item_id`='" . $db->mysql_cleans($_POST['id']) . "'
        ");
        $q2 = $db->update("
            UPDATE `ppSD_cart_items_complete`
            SET `product_id`='" . $db->mysql_cleans($_POST['product']['id']) . "'
            WHERE `product_id`='" . $db->mysql_cleans($_POST['id']) . "'
        ");
        $q2 = $db->update("
            UPDATE `ppSD_cart_items`
            SET `product_id`='" . $db->mysql_cleans($_POST['product']['id']) . "'
            WHERE `product_id`='" . $db->mysql_cleans($_POST['id']) . "'
        ");
        $q2 = $db->update("
            UPDATE `ppSD_products_linked`
            SET `product_id`='" . $db->mysql_cleans($_POST['product']['id']) . "'
            WHERE `product_id`='" . $db->mysql_cleans($_POST['id']) . "'
        ");
    }
    $return['show_saved'] = 'Updated';

} else {

    // Main event entry
    $insert_fields1       = $query_form['if2'];
    $insert_values1       = $query_form['iv2'];
    $q                    = $db->insert("
		INSERT INTO `ppSD_products` (`owner`,`public`,`created`$insert_fields1)
		VALUES ('" . $db->mysql_cleans($employee['id']) . "','1','" . current_date() . "'$insert_values1)
	");
    $product_id           = $_POST['product']['id'];
    $return['show_saved'] = 'Created';

    // Different ID than the preset?
    if ($_POST['original_id_dud'] != $_POST['product']['id']) {
        $q1 = $db->update("
            UPDATE `ppSD_uploads`
            SET `item_id`='" . $db->mysql_clean($_POST['product']['id']) . "'
            WHERE `item_id`='" . $db->mysql_clean($_POST['original_id_dud']) . "'
        ");
        $q1 = $db->update("
            UPDATE `ppSD_product_upsell`
            SET `product`='" . $db->mysql_clean($_POST['product']['id']) . "'
            WHERE `product`='" . $db->mysql_clean($_POST['original_id_dud']) . "'
        ");
    }
}

// Product Options and Tiers
if (!empty($_POST['option'])) {
    $cart->create_product_options($product_id, $_POST['option']);

}
if (!empty($_POST['tiers'])) {
    $cart->create_product_tiers($product_id, $_POST['tiers']);

}
if (!empty($_POST['content'])) {
    $admin->create_product_access_granting($product_id, $_POST['content']);

}
if (!empty($_POST['upsell'])) {
    $admin->create_product_upsell($product_id, $_POST['upsell']);

}

// Dependencies...
$del = $db->delete("
    DELETE FROM `ppSD_product_dependencies`
    WHERE `product_id`='" . $db->mysql_clean($product_id) . "'
");
if (! empty($_POST['dependency']['form_id'])) {
    $options = array(
        'form_multi' => $_POST['dependency']['form_multi'],
    );
    $q9      = $db->insert("
        INSERT INTO `ppSD_product_dependencies` (
          `type`,
          `act_id`,
          `options`,
          `product_id`
        )
        VALUES (
          'form',
          '" . $db->mysql_clean($_POST['dependency']['form_id']) . "',
          '" . $db->mysql_clean(serialize($options)) . "',
          '" . $db->mysql_clean($product_id) . "'
        )
    ");
}
// Re-cache
$prod                  = $cart->get_product($product_id, '1');

// Complete the task
// and run the hooks
$indata = array(
    'original_id' => $_POST['original_id_dud'],
    'id' => $_POST['product']['id'],
    'data' => $_POST['product'],
);
$task = $db->end_task($task_id, '1', '', $hook_task, '', $indata);


$content               = $prod['data'];
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated';

}
echo "1+++" . json_encode($return);
exit;



