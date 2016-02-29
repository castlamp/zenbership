<?php

/**
 * Credit Card Addition Tool
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
// Required stuff
require "../admin/sd-system/config.php";
// Check for SSL
$ssl            = $db->force_ssl('1');
// Check a user's session
$session = new session;
$ses     = $session->check_session();
if ($ses['error'] == '1') {
    $session->reject('login', $ses['ecode']);
    exit;
} else {
    $decode  = array();
    $id      = '';
    $editing = '0';
    if (!empty($_GET['id'])) {
        $id   = $_GET['id'];
        $cart = new cart;
        $card = $cart->get_card($id);
        if (empty($card['id'])) {
            $session->reject('manage/credit_cards.php', 'S043');
            exit;
        } else if ($ses['member_id'] != $card['member_id']) {
            $session->reject('manage/credit_cards.php', 'S043');
            exit;
        } else {
            $decode            = array();
            $decode['billing'] = $card;
        }
        $editing = '1';
    }
    if (! empty($_GET['sub'])) {
        //$fsub = $_GET['sub'];
        //$fsubsalt = $_GET['subs'];
        //$session = new session;
        //$ses = $session->check_session();
        $subscription = new subscription;
        $sub          = $subscription->get_subscription($_GET['sub']);
    } else {
        $sub = array(
            'data' => '',
        );
    }
    // Generate Forms
    if ($editing == '1') {
        $f12          = new field('billing', '0', '', '', '', '', '1');
        $billing_form = $f12->generate_form('billing_form', $decode['billing']);
        $f1           = new field('billing', '0', '', '', '', '', '1');
        $method_form  = $f1->generate_form('payment_form', $decode);
    } else {

        $user = new user;
        $udata = $user->get_user($ses['member_id']);
        $f12          = new field('billing');
        $billing_form = $f12->generate_form('billing_form', array('billing'=>$udata['data']));
        $f1           = new field('billing');
        $method_form  = $f1->generate_form('payment_form');
        
    }
    $changes = array(
        'method_form'  => $method_form,
        'billing_form' => $billing_form,
        'subscription' => $sub['data'],
        'card_id'      => $id,
        'editing'      => $editing,
    );
    $temp    = new template('cart_add_card', $changes, '1');
    echo $temp;
    exit;

}

