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
$cart = new cart;

// Validate form
$validate1 = new validator($_POST['billing'], 'billing_form');
$validate2 = new validator($_POST['billing'], 'payment_form');

// Session?
$member_id = '';
$session   = new session();
$ses       = $session->check_session();
if ($ses['error'] != '1') {
    $member_id = $ses['member_id'];
} else {
    $member_id = '';
}

// Editing?
if (!empty($_POST['id'])) {
    $card = $cart->get_card($_POST['id']);
    if (empty($card['id'])) {
        $session->reject('manage/credit_cards.php', 'S043');
        exit;
    } else if ($ses['member_id'] != $card['member_id']) {
        $session->reject('manage/credit_cards.php', 'S043');
        exit;
    }
    $scode = 'S044';
} else {
    $scode = 'S045';
}

// Get primary gateway
$found   = '';
$local   = '1';
$gateway = $cart->get_gateways();
foreach ($gateway as $aGateway) {
    if ($aGateway['api'] == '1') {
        if ($aGateway['local_card_storage'] != '1') {
            $local    = '0';
            $gateway  = new $aGateway['code']('0', $_POST['billing'], '', '', '1');
            $gate_rep = $gateway->add_card();
            if ($gate_rep['error'] == '1') {
                $details  = $db->get_error('S041');
                $details  = str_replace('%gateway_message%', $gate_rep['msg'], $details);
                $details  = str_replace('%gateway_code%', $gate_rep['resp_code'], $details);
                $changes  = array(
                    'details' => $details
                );
                $template = new template('error', $changes, '1');
                echo $template;
                exit;
            } else {
                $_POST['billing']['gateway']      = $aGateway['code'];
                $_POST['billing']['gateway_id_1'] = $gate_rep['gateway_id_1'];
                if (!empty($gate_rep['gateway_id_2'])) {
                    $_POST['billing']['gateway_id_2'] = $gate_rep['gateway_id_2'];
                }
            }
        }
        break;
    }
}

if (!empty($member_id)) {
    $_POST['billing']['member_id'] = $member_id;
} else {
    $_POST['billing']['member_id'] = '';
}

// Add card to DB, and assign to
// subscription is possible.
if ($local == '1') {
    $add = $cart->add_card($_POST['billing'], '1');
} else {
    $add = $cart->add_card($_POST['billing'], '0');
}

// Subscription?
if (!empty($_POST['sub'])) {
    $subscription = new subscription;
    $sub          = $subscription->get_subscription($_POST['sub']);
    // $sub['data']['member_id'] == $member_id &&
    if ($_POST['salt'] == $sub['data']['salt']) {
        $final_sub = $_POST['sub'];
    } else {
        $final_sub = '';
    }
    if (! empty($sub['data']['member_id'])) {
        $member_id = $sub['data']['member_id'];
    }
} else {
    $final_sub = '';
}

$secure = str_replace('http://', 'https://', PP_URL);



// $update_subs = $db->get_option('update_subs_card_update');
// ! empty($update_subs) &&
if (! empty($member_id)) {
    $q = $db->update("
        UPDATE
            `ppSD_subscriptions`
        SET
            `card_id`='" . $db->mysql_clean($add) . "'
        WHERE
            `member_id`='" . $db->mysql_clean($member_id) . "' AND
            `member_type`='member'
    ");
}
else if (! empty($final_sub)) {
    $q = $db->update("
		UPDATE `ppSD_subscriptions`
		SET `card_id`='" . $db->mysql_clean($add) . "'
		WHERE `id`='" . $db->mysql_clean($final_sub) . "'
		LIMIT 1
	");
}

if (! empty($final_sub)) {
    header('Location:' . $secure . '/pp-cart/manage_subscription.php?id=' . $_POST['sub'] . '&s=' . $_POST['salt'] . '&scode=' . $scode);
    exit;
} else {
    header('Location:' . $secure . '/manage/credit_cards.php?scode=' . $scode);
    exit;
}

