<?php

/**
 * Subscription Management
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
class subscription extends cart
{

    /**
     * Get the next renewal date, next chrage
     */
    function next_charge_info($get_subscription)
    {
        $next = $get_subscription['data']['transactions'] + 1;
        if ($get_subscription['data']['in_trial'] == '1') {
            if ($next > $get_subscription['product']['trial_repeat']) {
                $use           = 'standard';
                $correct_price = $get_subscription['product']['price'];
            } else {
                $use           = 'trial';
                $correct_price = $get_subscription['product']['trial_price'];
            }
        } else {
            $correct_price = $get_subscription['data']['price'];
        }
        return $correct_price;
    }


    /**
     * Renew a subscription.
     *
     * @param   string  $id             Subscription
     * @param   string  $charge_card    Just renew or charge and renew?
     *
     * @return bool
     */
    public function renew_subscription($id, $charge_card = '0')
    {
        // Get data
        $get_subscription = $this->get_subscription($id);

        // For products that no longer exist, etc.
        if ($get_subscription['error'] == '1') {
            return false;
        }

        $correct_price = $this->next_charge_info($get_subscription);
        $no_card = '0';
        $task_id = $this->start_task('subscription_renew', 'user', '', $get_subscription['data']['member_id']);

        // Is this subscription in spawned invoice mode?
        // If it has, and it is renewing again, it needs
        // to be canceled. We do that by marking the invoice
        // as dead. That will take care of the content access, etc.
        if (! empty($get_subscription['data']['spawned_invoice'])) {
            $invoice = new invoice;
            $invoice->mark_dead($get_subscription['data']['spawned_invoice']);
            return false;
        }

        // Charge card?
        if ($charge_card == '1' && $correct_price > 0) {
            if (empty($get_subscription['data']['gateway'])) {
                $gateway = $this->get_gateways('1', '');
            } else {
                $gateway = $this->get_gateways('', $get_subscription['data']['gateway']);
            }
            if ($gateway['0']['api'] == '1') {
                // Get potential penalty
                if ($get_subscription['data']['retry'] > 0) {
                    $penalty_cost = '0';
                    $penalty      = $this->get_penalty($get_subscription['data']['retry']);
                    if ($penalty['penalty_percent'] > 0) {
                        $penalty_cost += $correct_price * ($penalty['penalty_percent'] / 100);
                    }
                    if ($penalty['penalty_fixed'] > 0) {
                        $penalty_cost += $penalty['penalty_fixed'];
                    }
                    if ($penalty_cost > 0) {
                        $put = 'renewal_penalties';
                        $this->put_stats($put);
                        $put = 'renewal_penalty-income';
                        $this->put_stats($put, $penalty_cost);
                    }
                    $tot           = $correct_price + $penalty_cost;
                    //$correct_price = number_format($tot, 0, '', '');
                    //$penalty_cost  = number_format($penalty_cost, 0, '', '');
                    $correct_price = number_format($tot, 2, '.', '');
                    $penalty_cost  = number_format($penalty_cost, 2, '.', '');
                }
                // Attempt the charge
                $billing = $this->order_card_info($get_subscription['data']['card_id'], '1');

                // ---------- ERROR -------------------------
                if ($billing['error'] != '1') {

                    // Set up the components
                    // for the sale database
                    $cart          = new cart;
                    $c1            = array(
                        'product_id'      => $get_subscription['product']['id'],
                        'qty'             => '1',
                        'price'           => $correct_price,
                        'subscription_id' => $id
                    );
                    $components    = array($c1);
                    $using_gateway = new $gateway['0']['code']($correct_price, $billing);
                    $charge        = $using_gateway->charge($correct_price);

                    /**
                     * Transaction Failed
                     */
                    if ($charge['error'] == '1') {
                        $history = $this->add_history('subscription_failed', '2', $get_subscription['data']['member_id'], '', $id, '');
                        // $total_retries = $this->get_option('subscription_retries');
                        $put = 'renewals_failed';
                        $this->put_stats($put);
                        $put = 'renewals_failed_income-' . $get_subscription['product']['id'];
                        $this->put_stats($put, $correct_price);
                        $put = 'renewals_failed_income';
                        $this->put_stats($put, $correct_price);
                        $new_retry = $get_subscription['data']['retry'] + 1;
                        $penalty   = $this->get_penalty($new_retry);
                        // retry = 1: Means 1 fail, 0 retries.
                        // So if $total_retries == 3, retry needs to be on 4.
                        // Data in
                        $retry_spacing = $this->get_option('subscription_retry_spacing');
                        $next_retry    = add_time_to_expires($retry_spacing);
                        $indata = array(
                            'member_id'       => $get_subscription['data']['member_id'],
                            'member_type'     => $get_subscription['data']['member_type'],
                            'subscription_id' => $get_subscription['data']['id'],
                            'next_renew'      => $next_retry,
                            'order_id'        => '',
                        );
                        if ($penalty['cancel'] == '1') {
                            $error_code = $this->get_error('S013');
                            $cancel     = $this->cancel_subscription($id, $error_code);
                            // $task = $this->end_task($task_id, '0', '', 'subscription_cancel', '', $indata);
                        } else {
                            // Edit the subscription in the DB
                            $change_data = array(
                                'retry'      => $new_retry,
                                'next_renew' => $next_retry,
                                'price'      => $correct_price,
                            );

                            if ($new_retry == 1) {
                                $change_data['next_renew_keep'] = $get_subscription['data']['next_renew'];
                            }

                            $update      = $this->general_edit('ppSD_subscriptions', $change_data, $id, 'id');
                            // Email the user
                            $total_retries                                 = $this->get_option('subscription_retries');
                            $get_subscription['data']['next_renew']        = $next_retry;
                            $get_subscription['data']['renews']            = format_date($next_retry);
                            $get_subscription['data']['retry']             = $new_retry;
                            $get_subscription['data']['retries_remaining'] = $total_retries - $new_retry;
                            $changes                                       = array(
                                'subscription' => $get_subscription['data'],
                                'product'      => $get_subscription['product']
                            );
                            $email                                         = new email('', $get_subscription['data']['member_id'], $get_subscription['data']['member_type'], '', $changes, 'cart_subscription_failed');
                            // Database the failed sale
                            $addsale = $cart->database_sale($components, '9', $get_subscription['data']['member_id'], $charge, $charge['zen_order_id'], $get_subscription['data']['card_id']);
                            $task    = $this->end_task($task_id, '0', '', 'subscription_failed', '', $indata);
                        }
                    }

                    /**
                     * Transaction Approved
                     */
                    else {
                        $history = $this->add_history('subscription_renew', '2', $get_subscription['data']['member_id'], '', $id, '');
                        $put     = 'renewals';
                        $this->put_stats($put);
                        $put = 'renewals-' . $get_subscription['product']['id'];
                        $this->put_stats($put);
                        $put = 'renewal_income-' . $get_subscription['product']['id'];
                        $this->put_stats($put, $correct_price);
                        $put = 'renewal_income';
                        $this->put_stats($put, $correct_price);
                        $put = 'renewals_approved';
                        $this->put_stats($put);
                        $put = 'sales';
                        $this->put_stats($put);
                        $put = 'revenue';
                        $this->put_stats($put, $correct_price);
                        $put = 'sales-' . $get_subscription['data']['member_id'];
                        $this->put_stats($put);
                        $put = 'revenue-' . $get_subscription['data']['member_id'];
                        $this->put_stats($put, $correct_price);
                        if ($charge['fee'] > 0) {
                            $put = 'fees';
                            $this->put_stats($put, $charge['fee']);
                        }

                        // Database the sale.
                        $addsale = $cart->database_sale($components, '1', $get_subscription['data']['member_id'], $charge, $charge['zen_order_id'], $get_subscription['data']['card_id']);

                        // Complete the order
                        $process_renew = $this->process_renewal($get_subscription, $charge);

                        if ($get_subscription['data']['member_type'] == 'member' && ! empty($get_subscription['data']['member_id'])) {
                            $user = new user;
                            $update = $user->update_last_renewal($get_subscription['data']['member_id']);
                            /*
                            $q1 = $this->update("
                                UPDATE `ppSD_members`
                                SET `last_renewal`='" . current_date() . "'
                                WHERE `id`='" . $this->mysql_clean($get_subscription['data']['member_id']) . "'
                                LIMIT 1
                            ");
                            */
                        }

                        $q1 = $this->update("
                            UPDATE `ppSD_subscriptions`
                            SET `last_renewed`='" . current_date() . "',`advance_notice_sent`='0',`next_renew_keep`='1920-01-01 00:01:01'
                            WHERE `id`='" . $this->mysql_clean($get_subscription['data']['id']) . "'
                            LIMIT 1
                        ");

                        // Renew sub
                        $indata = array(
                            'member_id'       => $get_subscription['data']['member_id'],
                            'member_type'     => $get_subscription['data']['member_type'],
                            'subscription_id' => $get_subscription['data']['id'],
                            'order_id'        => $charge['zen_order_id'],
                        );
                        $task   = $this->end_task($task_id, '1', '', 'subscription_renew', '', $indata);
                    }

                } else {
                    $no_card = '1';
                }
            } else {
                $no_card = '1';
            }
            // No card on file...
            if ($no_card == '1') {
                $history    = $this->add_history('subscription_failed', '2', $get_subscription['data']['member_id'], '', $id, '');
                $nocard_opt = $this->get_option('sub_no_card_action');
                if ($nocard_opt == 'invoice') {
                    // Can not charge a non-api.
                    // E-Mail user an invoice.
                    // member_id	member_type
                    if ($get_subscription['data']['member_type'] == 'member') {
                        $user  = new user;
                        $mdata = $user->get_user($get_subscription['data']['member_id']);
                    } else {
                        $contact = new contact;
                        $mdata   = $contact->get_contact($get_subscription['data']['member_id']);
                    }
                    if (!empty($mdata['data']['owner'])) {
                        $owner = $mdata['data']['owner'];
                    } else {
                        $owner = '2';
                    }
                    $invoice = new invoice($owner);
                    // Main data for the invoice
                    $indata     = array(
                        'order_id'      => rand(900, 999999999),
                        'member_id'     => $get_subscription['data']['member_id'],
                        'member_type'   => $get_subscription['data']['member_type'],
                        'shipping_name' => '',
                        'shipping_rule' => '',
                    );
                    $totals     = array(
                        'due'      => $correct_price,
                        'subtotal' => $correct_price,
                        'paid'     => '0.00',
                        'credits'  => '0.00',
                        'shipping' => '0.00',
                        'tax'      => '0.00',
                        'tax_rate' => '0',
                    );
                    $shipping   = array();
                    $memo       = $this->get_error('S046');

                    $namePut = (! empty($mdata['data']['first_name'])) ? $mdata['data']['first_name'] : '';
                    $namePut .= (! empty($mdata['data']['last_name'])) ? ' ' . $mdata['data']['last_name'] : '';

                    $billing    = array(
                        'contact_name'   => $namePut,
                        'company_name'   => (! empty($mdata['data']['company_name'])) ? $mdata['data']['company_name'] : $namePut,
                        'address_line_1' => (! empty($mdata['data']['address_line_1'])) ? $mdata['data']['address_line_1'] : '',
                        'address_line_2' => (! empty($mdata['data']['address_line_2'])) ? $mdata['data']['address_line_2'] : '',
                        'city'           => (! empty($mdata['data']['city'])) ? $mdata['data']['city'] : '',
                        'state'          => (! empty($mdata['data']['state'])) ? $mdata['data']['state'] : '',
                        'zip'            => (! empty($mdata['data']['zip'])) ? $mdata['data']['zip'] : '',
                        'country'        => (! empty($mdata['data']['country'])) ? $mdata['data']['country'] : '',
                        'phone'          => (! empty($mdata['data']['phone'])) ? $mdata['data']['phone'] : '',
                        'email'          => (! empty($mdata['data']['email'])) ? $mdata['data']['email'] : '',
                        'website'        => (! empty($mdata['data']['url'])) ? $mdata['data']['url'] : '',
                        'memo'           => $memo,
                    );

                    $productA   = array(
                        'pricing' => array(
                            'plain_unit' => $get_subscription['product']['price'],
                            'qty'        => '1',
                        ),
                        'data'    => $get_subscription['product'],
                    );
                    $invoice_id = $invoice->create_invoice($indata, $totals, $billing, $shipping, $id);
                    $invoice->add_component_product($invoice_id, $productA, '0');
                    $invoice->send_invoice($invoice_id);

                    // Process renewal, but don't inform the user
                    // or extend content access.
                    $process_renew = $this->process_renewal($get_subscription, '', '1', $invoice_id);
                } else {
                    // Extend...
                    $new_retry     = $get_subscription['data']['retry'] + 1;
                    $total_retries = $this->get_option('subscription_retries');
                    if ($new_retry > $total_retries) {
                        $er = $this->get_error('S040');
                        $this->cancel_subscription($id, $er);
                    } else {
                        $retry_spacing = $this->get_option('subscription_retry_spacing');
                        $next_retry    = add_time_to_expires($retry_spacing);
                        $change_data   = array(
                            'retry'      => $new_retry,
                            'next_renew' => $next_retry,
                            'next_renew_keep' => $get_subscription['data']['next_renew'],
                        );
                        $update        = $this->general_edit('ppSD_subscriptions', $change_data, $id, 'id');
                        // E-mail...
                        $changes = array(
                            'subscription' => $get_subscription['data'],
                            'product'      => $get_subscription['product'],
                        );
                        $email   = new email('', $get_subscription['data']['member_id'], $get_subscription['data']['member_type'], '', $changes, 'cart_subscription_no_card');
                    }
                }
            }
        } /**
         * Just renewing, no need to
         * charge a card.
         */
        else {
            $c1            = array(
                'product_id'      => $get_subscription['product']['id'],
                'qty'             => '1',
                'price'           => $correct_price,
                'subscription_id' => $id
            );
            $components    = array($c1);
            $charge        = array(
                'zen_order_id' => 'N/A',
            );
            $addsale       = $this->database_sale($components, '1', $get_subscription['data']['member_id'], $charge, $charge['zen_order_id']);
            $process_renew = $this->process_renewal($get_subscription);
            // Data in
            $indata = array(
                'member_id'       => $get_subscription['data']['member_id'],
                'member_type'     => $get_subscription['data']['member_type'],
                'subscription_id' => $get_subscription['data']['id'],
                'order_id'        => $charge['zen_order_id'],
            );
            $task   = $this->end_task($task_id, '1', '', 'subscription_renew', '', $indata);

        }
    }


    function get_user_subscriptions($id)
    {
        $subs     = array();

        $STH = $this->run_query("
            SELECT
                ppSD_subscriptions.id
            FROM ppSD_subscriptions
            WHERE ppSD_subscriptions.member_id='" . $this->mysql_clean($id) . "'
            ORDER BY ppSD_subscriptions.status ASC, ppSD_subscriptions.next_renew ASC
        ");

        while ($row = $STH->fetch()) {
            $subs[] = $this->get_subscription($row['id']);
        }

        return $subs;
    }


    /**
     * Penalty
     */
    function get_penalty($num)
    {
        $pen = $this->get_array("
			SELECT *
			FROM `ppSD_subscription_reattempts`
			WHERE `fail_attempt`='$num'
			LIMIT 1
		");

        return $pen;
    }

    /**
     * Process renewal of a
     * subscription. To charge a subscription,
     * use $this->renew_subscription().
     * This is the end point of that.
     * $get_subscription is an array from
     * $this->get_subscription
     */
    function process_renewal($get_subscription, $gateway_reply = '', $skip_settings = '0', $invoice_spawn = false)
    {
        // Update the subscription
        // In trial?
        // Check total charges
        $change_data      = array();
        $new_transactions = $get_subscription['data']['transactions'] + 1;

        if ($get_subscription['data']['in_trial'] == '1') {
            if ($new_transactions >= $get_subscription['product']['trial_repeat']) {
                $correct_timeframe = $get_subscription['product']['renew_timeframe'];
            } else {
                $correct_timeframe = $get_subscription['product']['trial_period'];
            }
        } else {
            $correct_timeframe = $get_subscription['product']['renew_timeframe'];
        }

        $renew_type = $this->get_option('extend_type');
        if ($renew_type == 'expires') {
            if ($get_subscription['data']['next_renew_keep'] != '1920-01-01 00:01:01') {
                $dateA = $get_subscription['data']['next_renew_keep'];
            } else {
                $dateA = $get_subscription['data']['next_renew'];
            }
            $next_charge = add_time_to_expires($correct_timeframe, $dateA, $get_subscription['product']['threshold_date']);
        } else {
            $next_charge = add_time_to_expires($correct_timeframe, '', $get_subscription['product']['threshold_date']);
        }

        // Trial considerations
        if ($get_subscription['data']['in_trial'] == '1') {
            $change_data['trial_charge_number'] = $new_transactions;
            if ($new_transactions >= $get_subscription['product']['trial_repeat']) {
                $change_data['in_trial'] = '0';
            }
        }

        // Threshold date?
        //$product = $get_subscription['product'];
        //$next_charge = $this->apply_threshold($product['data']['threshold_date'],$product['data']['renew_timeframe'],$next_charge);
        $change_data['next_renew'] = $next_charge;
        $change_data['next_renew_keep']     = '1920-01-01 00:01:01';
        $change_data['retry']      = '0';
        $change_data['status']     = '1';
        $change_data['advance_notice_sent']      = '0';
        if (! empty($invoice_spawn)) {
            $change_data['spawned_invoice']     = $invoice_spawn;
        }
        $change_data['price']      = $this->next_charge_info($get_subscription);
        $update                    = $this->general_edit('ppSD_subscriptions', $change_data, $get_subscription['data']['id'], 'id');

        // Renew content access
        if ($skip_settings != '1') {

            if (!empty($get_subscription['data']['member_id']) && $get_subscription['data']['member_type'] == 'member') {

                // Content access
                $access = $this->apply_product_settings_to_user($get_subscription['product']['id'], $get_subscription['data']['member_id']);

                // Unlock and unsuspend the account.
                $user = new user;
                $unlock = $user->unlock($get_subscription['data']['member_id']);

            }

            // E-mail changes
            $get_subscription['data']['renews']     = format_date($next_charge);
            $get_subscription['data']['next_renew'] = $next_charge;
            $changes                                = array(
                'subscription' => $get_subscription['data'],
                'product'      => $get_subscription['product'],
                'order'        => $gateway_reply,
            );
            $email                                  = new email('', $get_subscription['data']['member_id'], $get_subscription['data']['member_type'], '', $changes, 'cart_subscription_renewed');

        }

    }

    /*
     * Moved directly to add_time_to_expires() function.
     *
    function apply_threshold($threshold_date,$renew_timeframe,$next_charge)
    {
        if (! empty($threshold_date) && substr($renew_timeframe,0,3) == '888') {
            $exp = explode(' ',current_date());
            $exp_date = explode('-',$exp['0']);
            $together = $exp_date['1'] . $exp_date['2'];
            if ($together >= $threshold_date) {
                $exp_renew = explode(' ',$next_charge);
                $exp_r1 = explode('-',$exp_renew['0']);
                $new_year = $exp_r1['0'] + 1;
                $next_charge = $new_year . '-' . $exp_r1['1'] . '-' . $exp_r1['2'] . ' ' . $exp_renew['1'];
            }
        }
        return $next_charge;
    }
    */
    /**
     * Check if a subscription exists.
     * Used for PayPal IPN's features
     */
    function find_subscription($id = '', $paypal_id = '')
    {
        if (!empty($paypal_id)) {
            $where = "`paypal_id`='" . $this->mysql_clean($paypal_id) . "'";
        } else {
            $where = "`id`='" . $this->mysql_clean($id) . "'";
        }
        $count = $this->get_array("
			SELECT `id` FROM `ppSD_subscriptions`
			WHERE $where
			LIMIT 1
		");

        return $count['id'];
    }


    function check_remaining($next, $timeframe, $value, $stop_date = '')
    {
        if (empty($stop_date)) {
            $stop_date = date('Y') . '-12-31 23:59:59';
        }
        $total = $value;
        $current_date = $next;
        $stop = 0;
        $charges = 1;
        $count = 0;
        while ($stop != 1) {
            $count++;
            if ($count > 1000) { break; }
            $current_date = add_time_to_expires($timeframe, $current_date);
            if ($current_date > $stop_date) {
                $stop = 1;
            } else {
                $total += $value;
                $charges++;
            }
        }
        return array(
            'total' => $total,
            'charges' => $charges,
        );
    }


    /**
     * Find a subscription. We only return
     * one because (1) it is only intended to
     * detect a subscription, and (2) if it does
     * and we are using PayPal, we need the data
     * to format the link + PayPal only allows
     * one subscription per order.
     */
    function find_subscription_product($force_session = '')
    {
        $session = $this->route_session($force_session);
        $q1      = $this->get_array("
			SELECT ppSD_products.* FROM `ppSD_cart_items`
			JOIN `ppSD_products`
			ON ppSD_products.id=ppSD_cart_items.product_id
			WHERE ppSD_products.type='2' OR ppSD_products.type='3' AND ppSD_cart_items.cart_session='" . $this->mysql_clean($session) . "'
		");

        return $q1;
    }

    /**
     * Create a subscription
     *
     * @param array  $product Product array from $cart->get_product
     * @param string $order_id
     */
    function create_subscription($product, $order_id = '', $member_id = '', $card_id = '', $paypal = '0', $paypal_id = '', $gateway = '', $member_type = 'member', $total = '', $force_next_renew = '')
    {
        $task_id = $this->start_task('subscription_add', 'user', '', $member_id);
        if (!empty($force_next_renew)) {
            $next_renew          = $force_next_renew;
            $in_trial            = '0';
            $trial_charge_number = '0';
        } else {
            // Renewal date
            if ($product['data']['type'] == '3') {
                $in_trial            = '1';
                $trial_charge_number = '1';
                $next_renew          = add_time_to_expires($product['data']['trial_period'], '', $product['data']['threshold_date']);
            } else {
                $in_trial            = '0';
                $trial_charge_number = '0';
                $next_renew          = add_time_to_expires($product['data']['renew_timeframe'], '', $product['data']['threshold_date']);
            }
        }

        // Threshold date?
        // $next_renew = $this->apply_threshold($product['data']['threshold_date'],$product['data']['renew_timeframe'],$next_renew);
        // Determine if card is present.
        if (empty($card_id)) {
            $dets = 'Created, but no credit card was associated with the subscription.';
        } else {
            $dets = 'Success';
        }

        // Add the subscription
        $format = $this->get_option('sub_id_format');
        $ifed   = generate_id($format, '22');
        $salt   = generate_id('random', '45');

        // Gateway
        if (is_array($gateway)) {
            $gateway_id = $gateway['id'];
        } else {
            $gateway_id = $gateway;
        }

        $q = $this->insert("
			INSERT INTO `ppSD_subscriptions` (
                `id`,
                `date`,
                `next_renew`,
                `price`,
                `card_id`,
                `retry`,
                `order_id`,
                `member_id`,
                `member_type`,
                `product`,
                `status`,
                `in_trial`,
                `trial_charge_number`,
                `paypal`,
                `paypal_id`,
                `gateway`,
                `salt`
			)
			VALUES (
			    '" . $this->mysql_clean($ifed) . "',
			    '" . current_date() . "',
			    '" . $next_renew . "',
			    '" . $total . "',
			    '" . $card_id . "',
			    '0',
			    '" . $this->mysql_clean($order_id) . "',
			    '" . $this->mysql_clean($member_id) . "',
			    '" . $this->mysql_clean($member_type) . "',
			    '" . $product['data']['id'] . "',
			    '1',
			    '$in_trial',
			    '$trial_charge_number',
			    '" . $this->mysql_clean($paypal) . "',
			    '" . $this->mysql_clean($paypal_id) . "',
			    '" . $this->mysql_clean($gateway_id) . "',
			    '" . $this->mysql_clean($salt) . "'
            )
		");
        $history = $this->add_history('subscription_created', '2', $member_id, '', $ifed, '');

        // Last renewal
        if ($member_type == 'member' && ! empty($member_id)) {
            $user = new user;
            $update = $user->update_last_renewal($member_id);
        }

        // E-mail the user
        $get_subscription = $this->get_subscription($ifed);
        $changes          = array(
            'subscription' => $get_subscription['data'],
            'product'      => $get_subscription['product']
        );
        $email            = new email('', $get_subscription['data']['member_id'], $get_subscription['data']['member_type'], '', $changes, 'cart_subscription_created');

        if (! empty($order_id)) {
            $put = 'subscriptions_created-' . $product['data']['id'];
            $this->put_stats($put);
            $put = 'subscriptions';
            $this->put_stats($put);
            $put = 'renewals_approved';
            $this->put_stats($put);
            $put = 'renewals-' . $get_subscription['product']['id'];
            $this->put_stats($put);
            $put = 'renewal_income-' . $get_subscription['product']['id'];
            $this->put_stats($put, $total);
            $put = 'renewal_income';
            $this->put_stats($put, $total);
            $task = $this->end_task($task_id, '1', '', 'subscription_add');
        }

        // Reply
        return array('id' => $ifed, 'error' => '0', 'error_details' => $dets);
    }


    /**
     * @param $id
     * @param string $reason
     */
    function cancel_subscription($id, $reason = '')
    {
        $task_id = $this->start_task('subscription_cancel', 'user', '', $sub['data']['member_id']);

        $q   = $this->update("
			UPDATE `ppSD_subscriptions`
			SET `status`='2',`cancel_date`='" . current_date() . "',`cancel_reason`='" . $this->mysql_clean($reason) . "'
			WHERE `id`='" . $this->mysql_clean($id) . "'
			LIMIT 1
		");

        $sub = $this->get_subscription($id, '', '', '', true);
        // Cancel access to content

        $remove_content_cancel_sub = $this->get_option('remove_content_cancel_sub');
        
        if ($remove_content_cancel_sub == '1') {
            if ($sub['data']['member_type'] == 'member' && !empty($sub['data']['member_id'])) {
                $cart    = new cart;
                $user    = new user;
                $product = $cart->get_product($sub['data']['product']);
                foreach ($product['content'] as $item) {
                    $user->remove_content_access($item['grants_to'], $sub['data']['member_id']);
                }
            }
        }

        $changes = array(
            'subscription' => $sub['data'],
            'product'      => $sub['product']
        );
        $email   = new email('', $sub['data']['member_id'], $sub['data']['member_type'], '', $changes, 'cart_subscription_canceled');
        $put     = 'subscriptions_canceled-' . $sub['data']['product'];
        $this->put_stats($put);
        $put = 'subscriptions_canceled';
        $this->put_stats($put);
        // Data
        $indata = array(
            'member_id'       => $sub['data']['member_id'],
            'member_type'     => $sub['data']['member_type'],
            'subscription_id' => $sub['data']['id'],
        );
        $task   = $this->end_task($task_id, '1', '', 'subscription_cancel', '', $indata);

        $history = $this->add_history('subscription_cancel', '2', $sub['data']['member_id'], '', $sub['data']['id'], '');

    }

    /**
     * Retrieve a subscription
     */
    function get_subscription($id = '', $order_id = '', $paypal = '0', $paypal_id = '', $skip_cancel = false)
    {
        if ($paypal == '1') {
            $where = "WHERE `paypal_id`='" . $this->mysql_clean($paypal_id) . "'";
        } else {
            $where = "WHERE `id`='" . $this->mysql_clean($id) . "'";
        }
        $sub    = $this->get_array("
			SELECT * FROM `ppSD_subscriptions`
			$where
			LIMIT 1
		");
        $return = array();
        if (!empty($sub['id'])) {
            // Sale history
            $charges     = array();
            $STH         = $this->run_query("
				SELECT `cart_session`
				FROM `ppSD_cart_items_complete`
				WHERE `subscription_id`='" . $this->mysql_clean($id) . "'
				ORDER BY `date` DESC
			");
            $total_trans = 0;
            while ($row = $STH->fetch()) {
                $this_order = $this->get_order($row['cart_session'], '0');
                $charges[]  = $this_order['data'];
                $total_trans++;
            }
            $sub['transactions'] = $total_trans;
            $return['charges']   = $charges;

            /*
            if (ZEN_PERFORM_TESTS) {
                $safe_url            = PP_URL;
            } else {
                $safe_url            = str_replace('http://', 'https://', PP_URL);
            }
            */
            $safe_url = $this->getSecureLink();

            $sub['update_link']  = $safe_url . '/pp-cart/manage_subscription.php?id=' . urlencode($sub['id']) . '&s=' . urlencode($sub['salt']);
            // Product
            $cart    = new cart;
            $product = $cart->get_product($sub['product']);
            if (!empty($product['data'])) {
                $return['product'] = $product['data'];
                $return['package'] = $product['package'];
            } else {
                if (! $skip_cancel) $this->cancel_subscription($id, 'Product no longer exists.');
                $return['error'] = '1';
            }
            // Find Card
            $sub['alert']      = '0';
            $sub['alert_info'] = '';
            $alerts            = '';
            if (! empty($sub['card_id'])) {
                $find_card = $cart->find_card($sub['card_id']);
                if ($find_card <= 0) {
                    $sub['alert'] = '1';
                    $alerts .= '<li>' . $this->get_error('S039') . '</li>';
                    $sub['card_id'] = '';
                    $this->remove_card($id);
                }
            }
            if ($sub['retry'] > 1) {
                $er           = $this->get_error('S040');
                // $remaining    =
                $er = str_replace('%retry%', $sub['retry'], $er);
                $sub['alert'] = '1';
                $alerts .= '<li>' . $er . '</li>';
            }
            if (!empty($alerts)) {
                $sub['alert_info'] = '<ul id="zen_subscription_alerts">' . $alerts . '</ul>';
            }
            // Some additional formatting
            if ($sub['status'] == '1') {
                if ($sub['in_trial'] == '1') {
                    $trial_remaining                = $product['data']['trial_repeat'] - $total_trans;
                    $sub['show_status']             = $this->get_error('S056');
                    $sub['trial_charges_remaining'] = $trial_remaining;
                    $sub['format_trial_timeframe']  = $product['data']['format_trial_timeframe'];
                } else {
                    $sub['show_status']             = $this->get_error('S055');
                    $sub['trial_charges_remaining'] = '0';
                    $total_remaining                = $product['data']['renew_max'] - $total_trans + $product['data']['trial_repeat'];
                    if ($total_remaining <= 0) {
                        $sub['remaining_charges'] = '&#8734;';
                    } else {
                        $sub['remaining_charges'] = $total_remaining;
                    }
                }
                $sub['format_timeframe'] = $product['data']['format_timeframe'];
                $sub['user_options']     = '';
                if (!empty($product['package'])) {
                    $sub['user_options'] = '<a href="null.php" onclick="return upgrade_sub(\'' . $sub['id'] . '\',\'' . $sub['salt'] . '\');">' . $this->get_error('S057') . '</a><br />';
                }
                $sub['user_options'] .= '<a href="null.php" onclick="return alter_subscription(\'' . $sub['id'] . '\',\'' . $sub['salt'] . '\',\'cancel\');">' . $this->get_error('S052') . '</a>';
            } else {
                $sub['remaining_charges']       = '0';
                $sub['trial_charges_remaining'] = '0';
                $sub['show_status']             = $this->get_error('S050');
                // $sub['user_options'] = '<a href="null.php" onclick="return alter_subscription(\'' . $sub['id'] . '\',\'' . $sub['salt'] . '\',\'activate\');">Re-Activate</a>';
                $sub['user_options'] = 'N/A';
            }
            // Format some stuff
            $sub['format_price']  = place_currency($sub['price']);
            $sub['started']       = format_date($sub['date']);
            $sub['renews']        = format_date($sub['next_renew']);
            $sub['date_canceled'] = format_date($sub['cancel_date']);
            $sub['next_renew_keep_format'] = format_date($sub['next_renew_keep']);
            $sub['last_renew_format'] = format_date($sub['last_renewed']);
            $return['data']       = $sub;
            // Next Price
            $next_price                          = $this->next_charge_info($return);
            $return['data']['next_price']        = $next_price;
            $return['data']['format_next_price'] = place_currency($next_price);
            $return['error']                     = '0';
        }

        return $return;
    }

    function remove_card($id)
    {
        $q1 = $this->update("
            UPDATE `ppSD_subscriptions`
            SET `card_id`=''
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }


    /**
     * @param $current_product Currently selected product.
     * @param $subscription    From $subscription->get_subscription
     */
    function format_upgrades($current_product, $subscription)
    {
        $list            = '';
        $next_is_upgrade = 0;
        foreach ($subscription['package']['items'] as $item) {
            $regular_price   = format_timeframe_full(place_currency($item['price']), $item['renew_timeframe'], '');
            $on_next_renewal = format_date($subscription['data']['next_renew']);
            $actual_price    = $regular_price . ' next billed on ' . $on_next_renewal;
            if ($next_is_upgrade == 1) {
                $price_to_upgrade = $this->price_to_upgrade($subscription, $item['price'], $item['renew_timeframe']);
                $price_to_upgrade = place_currency($price_to_upgrade);
            } else {
                $price_to_upgrade = place_currency('0.00');
            }
            // Current product on subscription?
            if ($item['Product_Id'] == $subscription['data']['product']) {
                $next_is_upgrade = 1;
                $class           = 'on';
                $text            = ' (Your current plan)';
                $link            = '';
            } else {
                $class = '';
                $text  = '';
                if ($next_is_upgrade == 1) {
                    $word = 'Upgrade';
                } else {
                    $word = 'Downgrade';
                }
                $confirm_message = $this->get_error('S065');
                $confirm_message = str_replace('%type%', strtolower($word), $confirm_message);
                $confirm_message = str_replace('%cost_today%', $price_to_upgrade, $confirm_message);
                $confirm_message = str_replace('%next_renew%', $on_next_renewal, $confirm_message);
                $confirm_message = str_replace('%next_cost%', $regular_price, $confirm_message);
                //$confirm_message = 'By clicking "OK" below, you confirm that you wish to ' . strtolower($word) . ' your plan at the cost of ' . $price_to_upgrade . ' (billed today). Your next regular renewal will be for ' . $actual_price;
                $link = '<div class="zen_float_right"><a href="null.php" onclick="return complete_sub_upgrade(\'' . $subscription['data']['id'] . '\',\'' . $subscription['data']['salt'] . '\',\'' . $item['Product_Id'] . '\',\'' . htmlentities($confirm_message) . '\');">' . $word . ' to this plan</a></div>';
            }
            // Upgrade or downgrade?
            if ($next_is_upgrade == 1) {


                $list .= '<li class="' . $class . '">' . $link;
                $list .= $item['name'] . $text . '<div class="sub_upgrade_details">';
                $list .= '<b>Regular Price:</b> ' . $actual_price . '<br />';
                if ($subscription['package']['prorate_upgrades'] == '1') {
                    $list .= '<b>Price Billed Today:</b> ' . place_currency($price_to_upgrade);
                }
                $list .= '</div></li>';


            } else {
                $price_to_upgrade = '0.00';
                $list .= '<li class="' . $class . '">' . $link . $item['name'] . $text . '<div class="sub_upgrade_details"><b>Regular Price:</b> ' . $actual_price . '</div></li>';
            }

            /*
            $changes = array(
                'type' => $word,
                'next_renew' => $on_next_renewal,
                'next_cost' => $actual_price,
                'link' => $link,
                'notice' => $text,
                'product' => $item,
                'class' => $class,
                'updown' => $text,
                'confirm_message' => $confirm_message,
                'cost_today' => place_currency($price_to_upgrade),
            );
            $list .= new template('popup_cart_alter_subscription_entry', $changes, '0');
            */
        }

        return $list;
    }

    /**
     * @param $subscription
     * @param $price
     */
    function price_to_upgrade($subscription, $price, $timeframe)
    {
        if ($subscription['data']['in_trial'] == '1') {
            $cost = '0.00';
        } else {
            $days_remaining = date_difference($subscription['data']['next_renew'], current_date(), '1', 'days', '1');
            $per_day        = $this->per_price_day($price, $timeframe);
            $cost           = $days_remaining * $per_day;
        }

        return $cost;
    }

    /**
     * @param $price
     * @param $timeframe
     *
     * @return float
     */
    function per_price_day($price, $timeframe)
    {
        $dif = timeframe_to_days($timeframe);

        return $price / $dif;
    }


    /**
     * Notify users of upcoming subscription charges.
     */
    function notifyUpcoming()
    {
        $subscription_advanced_notice = $this->get_option('subscription_advanced_notice');

        if (empty($subscription_advanced_notice) || $subscription_advanced_notice == '999') return false;

        $seven = date('Y-m-d', time() + ($subscription_advanced_notice * 86400));

        $q1S = $this->run_query("
            SELECT id
            FROM `ppSD_subscriptions`
            WHERE
              `status`='1' AND
              `next_renew` <= '" . $seven . "' AND
              advance_notice_sent != '1'
          ");
        while ($row = $q1S->fetch()) {

            // Schedule the email.
            $get_subscription = $this->get_subscription($row['id']);
            $card = $this->get_card($get_subscription['data']['card_id']);

            if (empty($card['id'])) {
                $card_status = $this->get_error('S070');
            } else {
                $card_status = $card['card_type'] . ' ending in ' . $card['last_four'];
            }

            $changes = array(
                'days_until_renewal'    => $subscription_advanced_notice,
                'subscription'          => $get_subscription['data'],
                'product'               => $get_subscription['product'],
                'card'                  => $card,
                'card_status'           => $card_status,
            );
            $email   = new email('', $get_subscription['data']['member_id'], $get_subscription['data']['member_type'], '', $changes, 'cart_subscription_advanced_notice');

            // Update the database.
            $q2 = $this->update("
                UPDATE ppSD_subscriptions
                SET advance_notice_sent = '1'
                WHERE `id`='" . $row['id'] . "'
                LIMIT 1
            ");

        }
    }


    /**
     * @param $id   Subscription ID
     * @param $plan New "plan", which is a product ID.
     */
    function updown_subscription($id, $plan)
    {
        $data    = $this->get_subscription($_GET['id']);

        if (empty($data['package'])) {
            return array(
                'error'   => '1',
                'details' => $this->get_error('S059'),
            );
        } else {
            if ($plan == $data['data']['product']) {
                return array(
                    'error'   => '1',
                    'details' => $this->get_error('S063'),
                );
            } else if (empty($data['data']['card_id'])) {
                return array(
                    'error'   => '1',
                    'details' => $this->get_error('S064'),
                );
            } else {
                // Check for an upgrade price.
                $cart = new cart();
                // Get the new product
                $product = $cart->get_product($plan);
                // Custom Action Build
                $indata = array(
                    'subscription_id' => $data['data']['id'],
                    'member_id'       => $data['data']['member_id'],
                    'member_type'     => $data['data']['member_type'],
                    'order_id'        => '',
                    'prorated'        => '0',
                    'prorate_cost'    => '',
                    'product_id'      => $product['data']['id'],
                );
                // Determine the prorated cost
                $upgrade_cost = $this->price_to_upgrade($data, $product['data']['price'], $product['data']['renew_timeframe']);
                $upgrade_cost = number_format($upgrade_cost, 2, '.', '');
                if ($upgrade_cost > 0) {
                    $indata['type'] = 'upgrade';
                    $finalize = 'subscription_upgrade';
                } else {
                    $indata['type'] = 'downgrade';
                    $finalize = 'subscription_downgrade';
                }

                $task_id = $this->start_task($finalize, 'user', '', $data['data']['member_id']);

                // Pro-rated?
                if ($data['package']['prorate_upgrades'] == '1') {
                    $blank = '0';
                    // Get the card on file
                    $card                   = $cart->get_card($data['data']['card_id']);
                    $card['stored_card_id'] = $data['data']['card_id'];
                    $cart->set_billing($card);
                    $last_four   = $card['cc_number'];
                    $card_method = $card['full_method'];
                    if ($upgrade_cost > 0) {
                        // Custom hooks
                        $indata['prorated']     = '1';
                        $indata['prorate_cost'] = $upgrade_cost;
                        // Create the pro-rated product
                        $product_data = array(
                            'name'          => 'Pro-rated Upgrade for subscription ID ' . $_GET['id'],
                            'price'         => $upgrade_cost,
                            'type'          => '1',
                            'physical'      => '0',
                            'hide'          => '1',
                            'hide_in_admin' => '1',
                            'owner'         => '2',
                            'public'        => '0',
                        );
                        $new_product  = $cart->add_product($product_data);
                        // Create the cart session
                        $cart_session = $cart->start_session($data['data']['member_id'], '1');
                        // Add the product to the cart.
                        $cart->add($new_product, '1', '', $data['data']['member_id'], '', $cart_session, '', '1', '1');
                        // Charge the card on file.
                        $gateway = new $card['gateway']($upgrade_cost, $card, $cart_session, '', '0');
                        $charge  = $gateway->charge();

                        // Error processing pro-rated amount
                        if ($charge['error'] == '1') {
                            $details = $this->get_error('S016');
                            $details = str_replace('%gateway_message%', $charge['msg'], $details);
                            $details = str_replace('%gateway_code%', $charge['resp_code'], $details);

                            $indata['success'] = '0';

                            return array(
                                'error'   => '1',
                                'details' => $details,
                            );
                        }

                        // Success?
                        else {
                            $indata['success'] = '1';

                            // Complete the order
                            $go = $cart->complete_order($cart_session, $charge, '1', '1');

                            // Update the new order to show
                            // up on the subscription
                            // transactions logs.
                            $update1 = $this->update("
                                UPDATE
                                    `ppSD_cart_items_complete`
                                SET
                                    `subscription_id`='" . $this->mysql_clean($id) . "'
                                WHERE
                                  `cart_session`='" . $this->mysql_clean($cart_session) . "'
                                LIMIT 1
                            ");
                        }
                    } else {
                        $blank = '1';
                    }
                } else {
                    $blank = '1';
                }

                if ($blank == '1') {
                    $cart_session = '';
                    $upgrade_cost = place_currency('0.00');
                    $last_four    = '';
                    $card_method  = '';
                }

                // Edit the subscription
                $update = $this->update("
                    UPDATE
                        `ppSD_subscriptions`
                    SET
                      `status`='1',
                      `price`='" . $this->mysql_clean($product['data']['price']) . "',
                      `product`='" . $this->mysql_clean($plan) . "'
                    WHERE
                      `id`='" . $this->mysql_clean($id) . "'
                    LIMIT 1
                ");

                // History
                if ($finalize == 'subscription_upgrade') {
                    $history = $this->add_history('subscription_upgrade', '2', $data['data']['member_id'], '', $data['data']['id'], '');
                } else {
                    $history = $this->add_history('subscription_downgrade', '2', $data['data']['member_id'], '', $data['data']['id'], '');
                }

                // Send the email
                $changes = array(
                    'subscription'   => $data['data'],
                    'product'        => $product['data'],
                    'order_price'    => $upgrade_cost,
                    'order_no'       => (! empty($cart_session)) ? $cart_session : '',
                    'card_last_four' => (! empty($last_four)) ? $last_four : '',
                    'card_method'    => (! empty($card_method)) ? $card_method : '',
                );
                $email   = new email('', $data['data']['member_id'], $data['data']['member_type'], '', $changes, 'cart_subscription_changed');

                // Hooks
                $indata['data'] = $changes;

                $task = $this->end_task($task_id, '0', '', $finalize, '', $indata);

                // Reply
                return array(
                    'error'   => '0',
                    'details' => $this->get_error('S062'),
                );
            }
        }
    }
}
