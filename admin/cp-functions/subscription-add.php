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
 * If adding, ID is not used. "user_id" is sent.
 * If editing, ID is the id of the item.

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'subscription-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

// Primary fields for main table
$table = 'ppSD_subscriptions';

// Continue...
$cart         = new cart;
$user         = new user;

$card_id      = '';
$member_id    = '';
$member_type  = '';
$gateway_name = '';


// Adding a new subscription
if ($type != 'edit') {

    // Product checks
    $product = $cart->get_product($_POST['sub']['product']);

    if (empty($product['data']['id'])) {
        echo "0+++Product not found.";
        exit;
    } else {
        if ($product['data']['type'] == '1') {
            echo "0+++The selected product is not a subscription product.";
            exit;
        }
    }

    if ($_POST['member_type'] == 'new_user') {
        $member_type = 'member';
        $membettt = $user->get_user('', $_POST['user']['username']);
        if (! empty($membettt['data']['id'])) {
            $member_id = $membettt['data']['id'];
            $use_email = $membettt['data']['email'];
        } else {
            $data        = array(
                'member' => array(
                    'source'     => '10',
                    'account'    => 'NONMEM01',
                    'username'   => $_POST['user']['username'],
                    'first_name' => $_POST['user']['first_name'],
                    'last_name'  => $_POST['user']['last_name'],
                    'email'      => $_POST['user']['email'],
                ),
            );
            $mem = $user->create_member($data);

            if (empty($mem['member_id'])) {
                echo "0+++" . $mem['message'];
                exit;
            }

            $member_id = $mem['member_id'];
            $use_email = $_POST['user']['email'];
        }
    } else {
        $member = $user->get_user($_POST['sub']['member_id']);
        if (! empty($member['data']['id'])) {
            $member_id   = $member['data']['id'];
            $member_type = 'member';
            $use_email = $member['data']['email'];
        } else {
            echo "0+++Could not find member.";
            exit;
        }
    }

    // Credit card
    if ($_POST['card_type'] == 'new_card') {
        $_POST['cc']['email'] = $use_email;
        $addver       = $cart->add_card_with_verify($_POST['cc'], $member_id);
        $card_id      = $addver['0'];
        $gateway_name = $addver['1'];
    }

    else if ($_POST['card_type'] == 'existing_card') {
        $card = $cart->get_card($_POST['card_id']);
        if (!empty($card['id']) && $card['member_id'] == $member_id) {
            $card_id = $_POST['card_id'];
        } else {
            echo "0+++Credit card not found.";
            exit;
        }
    }

    else {
        // No card.
    }

}

// Establish some basics
$_POST['sub']['member_id']   = $member_id;
$_POST['sub']['member_type'] = $member_type;
$_POST['sub']['card_id']     = $card_id;
$primary                     = array('');
$ignore                      = array('id', 'edit');
$query_form                  = $admin->query_from_fields($_POST['sub'], $type, $ignore, $primary);

// Charge now?
if ($type != 'edit') {
    if ($_POST['skip_trial'] == '1') {
        $price = $product['data']['price'];
    } else {
        if ($product['data']['type'] == '3') {
            $price = $product['data']['trial_price'];
            $tf    = $product['data']['trial_period'];
        } else {
            $price = $product['data']['price'];
            $tf    = $product['data']['renew_timeframe'];
        }
    }
}

if (! empty($_POST['sub']['price'])) {
    $price = $_POST['sub']['price'];
}

// Next renewal: add time.
$_POST['sub']['next_renew'] = $_POST['sub']['next_renew'] . ' 00:00:00';

$subscription = new subscription;

if ($type == 'edit') {

    // New "next rewenal" date? Make the user's content
    // expire at the same time as the new date assuming
    // it belongs to the product associated with the
    // subscription.
    $subData = $subscription->get_subscription($_POST['id']);
    if (cutOffTime($subData['data']['next_renew']) != cutOffTime($_POST['sub']['next_renew'])) {
        $aProduct = $cart->get_product($subData['product']['id']);
        foreach ($aProduct['content'] as $grants_access_to) {
            if ($grants_access_to['type'] == 'content') {
                $res = $user->add_content_access($grants_access_to['grants_to'], $subData['data']['member_id'], '', $_POST['sub']['next_renew']);
            }
        }
    }

    // Update the contact
    $update_set1 = substr($query_form['u1'], 1);
    $update_set2 = substr($query_form['u2'], 1);

    $q           = $db->update("
		UPDATE `ppSD_subscriptions`
		SET $update_set2
		WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
		LIMIT 1
	");

    $added       = $_POST['id'];

} else {

    $add          = $subscription->create_subscription($product, '', $member_id, $card_id, '', '', $gateway_name, $member_type, $price, $_POST['sub']['next_renew']);
    $added        = $add['id'];

}

$task         = $db->end_task($task_id, '1');
$table        = 'ppSD_subscriptions';
$scope        = 'subscription';
$history      = new history($added, '', '', '', '', '', $table);
$content      = $history->final_content;
$table_format = new table($scope, $table);
$return       = array();

if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created';
    $return['load_slider']      = array(
        'id'      => $added,
        'page'    => 'subscription',
        'subpage' => 'view',
    );
} else {
    $cell                  = $table_format->render_cell($content, '1');
    $return['update_row']  = $cell;
    $return['show_saved']  = 'Updated';
    $return['close_popup'] = '1';
}

echo "1+++" . json_encode($return);
exit;
