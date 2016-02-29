<?php

/**
 * Cart ajax functions.
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
$cart = new cart;
/**
 * Add to cart
 */
if ($_GET['act'] == 'add_to_cart') {
    if (!empty($_GET['option'])) {
        $opts = $_GET['option'];
    } else {
        $opts = '';
    }
    $add     = $cart->add($_GET['id'], $_GET['qty'], $opts);
    if ($add['error'] != '1') {
        $product = $cart->get_product($_GET['id']);
        // Upsell?
        if (empty($_GET['quick'])) {
            $upsell     = $cart->get_upsell_products($_GET['id'], 'popup');
            $all_upsell = '';
            if (!empty($upsell)) {
                foreach ($upsell as $item) {
                    $in_cart = $cart->find_product_in_cart($item['data']['id']);
                    if ($in_cart['found'] != '1') {
                        $upsell_link = $cart->upsell_link($item);
                        $changes_up  = array(
                            'product' => $item['data'],
                            'link'    => $upsell_link,
                        );
                        $temp        = new template('popup_cart_upsell', $changes_up, '0');
                        $all_upsell .= $temp;
                    }
                }
            }
            if (empty($all_upsell)) {
                $template = 'popup_cart_added';
            } else {
                $template = 'popup_cart_added_withupsell';
            }
            // Reply
            $changes = array(
                'data'   => $product['data'],
                'qty'    => $_GET['qty'],
                'upsell' => $all_upsell,
            );
            $temp    = new template($template, $changes, '0');
            echo "1+++message+++" . $temp;
            exit;
        } // Upsell product add!
        else {
            echo "1+++nothing+++";
            exit;
        }
    } else {
        echo "0+++" . $add['error_details'];
        exit;
    }
} /**
 * Apply savings code
 */
else if ($_GET['act'] == 'add_code') {
    $apply = $cart->apply_savings_code($_GET['coupon']);
    if ($apply['error'] == '1') {
        echo "0";
        echo "+++";
        echo $apply['error_details'];
        exit;
    } else {
        redo_order_totals();
    }
} /**
 * Delete a credit card
 */
else if ($_GET['act'] == 'delete_card') {
    $apply = $cart->delete_card($_GET['id'], $_GET['salt']);
    if ($apply['error'] == '1') {
        echo "0";
        echo "+++";
        echo $apply['error_details'];
        exit;
    } else {

        echo "1+++remove+++card_" . $_GET['id'];
        exit;
    }
} /**
 * Remove savings code
 */
else if ($_GET['act'] == 'remove_code') {
    $remove = $cart->remove_savings_code();
    redo_order_totals();
} /**
 * Update shipping
 */
else if ($_GET['act'] == 'update_shipping') {
    $remove = $cart->update_shipping($_GET['rule']);
    redo_order_totals();
} /**
 * Get region popup
 */
else if ($_GET['act'] == 'set_region') {
    $fields  = new field;
    $country = $fields->render_field('country', $cart->{'order'}['data']['country']);
    $state   = $fields->render_field('state', $cart->{'order'}['data']['state']);
    $changes = array(
        'state_field'   => $state['0'],
        'country_field' => $country['0']
    );
    $temp    = new template('popup_cart_set_region', $changes, '0');
    echo '1+++' . $temp;
    exit;
} /**
 * Change a subscriptions's status
 */
else if ($_GET['act'] == 'upgrade_sub') {
    $subscription = new subscription();
    $data         = $subscription->get_subscription($_GET['id']);
    if ($data['data']['salt'] != $_GET['salt']) {
        echo "0+++" . $db->get_error('S060');
        exit;
    } else {
        if (empty($data['package'])) {
            echo "0+++" . $db->get_error('S059');
            exit;
        } else {
            if ($data['package']['prorate_upgrades'] == '1') {
                $prorate = '<p class="zen_attention">' . $db->get_error('S058') . '</p>';
            } else {
                $prorate = '';
            }
            $options = $subscription->format_upgrades($_GET['id'], $data);
            $changes = array(
                'id'      => $_GET['id'],
                'salt'    => $_GET['salt'],
                'plans'   => $options,
                'prorate' => $prorate,
            );
            $temp    = new template('popup_cart_upgrade_subscription', $changes, '0');
            echo '1+++' . $temp;
            exit;
        }
    }

} /**
 * Complete the subscription change
 */
else if ($_GET['act'] == 'upgrade_sub_complete') {
    $subscription = new subscription();
    $data         = $subscription->get_subscription($_GET['id']);
    if ($data['data']['salt'] != $_GET['salt']) {
        echo "0+++" . $db->get_error('S060');
        exit;
    } else {
        $up = $subscription->updown_subscription($_GET['id'], $_GET['plan']);
        if ($up['error'] == '1') {
            echo "0+++" . $up['details'];
            exit;
        } else {
            echo "1+++" . $up['details'];
            exit;
        }
    }
} /**
 * Change a subscriptions's status
 */
else if ($_GET['act'] == 'cancel_sub') {
    $changes = array(
        'id'   => $_GET['id'],
        'salt' => $_GET['salt'],
        'type' => $_GET['type'],
    );
    $temp    = new template('popup_cart_alter_subscription', $changes, '0');
    echo '1+++' . $temp;
    exit;
} /**
 * Confirm and complete process
 */
else if ($_GET['act'] == 'confirm_cancel') {
    $subscription = new subscription;
    $get          = $subscription->get_subscription($_GET['id']);
    if ($get['data']['salt'] == $_GET['salt']) {
        $subscription->cancel_subscription($_GET['id'], 'Canceled by user online.');
        echo "1+++" . $db->get_error('S061');
        exit;
    } else {
        $error = $db->get_error('S053');
        echo "0+++" . $error;
        exit;
    }
} /**
 * Update region
 */
else if ($_GET['act'] == 'update_region') {
    $up_data = array(
        'state'   => $_GET['state'],
        'country' => $_GET['country'],
    );
    $update  = $cart->update_order($cart->{'id'}, $up_data);
    // Get new totals
    $get_order = $cart->get_order($cart->{'id'}, '1');
    redo_order_totals();
    exit;
}
/**
 * Function to recalculate and
 * send back the new cart totals.
 */
function redo_order_totals()
{
    global $cart;
    $get_order = $cart->get_order($cart->{'id'}, '1');
    echo '1';
    echo '+++update+++';
    echo 'zen_totals_total:' . $get_order['pricing']['format_total'];
    echo '||zen_totals_tax:' . $get_order['pricing']['format_tax'];
    echo '||zen_totals_savings:' . $get_order['pricing']['format_savings'];
    echo '||zen_totals_shipping:' . $get_order['pricing']['format_shipping'];
    exit;
}

