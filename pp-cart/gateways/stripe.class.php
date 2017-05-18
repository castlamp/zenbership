<?php

/**
 * Stripe Integration
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
class gw_stripe extends cart
{

    var $gateway_name = 'gw_stripe';
    var $send_data;
    var $gateway_data;
    var $use_order_id;
    var $use_order;
    var $return;
    public $required_fields;

    var $amount;
    var $token;

    /**
     * Required basics
     * credential1 = auth_net_login_id
     * credential2 = auth_net_trans_key
     */
    function __construct($price, $billing = '', $use_order_id = '', $shipping = '', $auth_only = '0')
    {
        // Vars
        $this->billing   = $billing;
        $this->shipping  = $shipping;

        if ($price <= 0) {
            $price = '0.50';
        }

        $this->amount    = ($price * 100);
        $this->auth_only = $auth_only;
        // Order
        if (empty($use_order_id)) {
            $this->use_order_id = $this->generate_cart_id();
        } else {
            $this->use_order_id = $use_order_id;
        }
        // Get gateway
        $q1                 = $this->get_gateways('', $this->gateway_name);
        $this->gateway_data = $q1['0'];

        // Set required
        $this->set_required_fields();
    }


    /**
     * List of required fields for a customer
     * profile token.
     */
    function set_required_fields()
    {
        $this->required_fields = array(
            'number',
            'exp_month',
            'exp_year',
            'address_zip',
        );
    }


    /**
     * <----start:payment_methods------------------------
     */

    /**
     * Charge a credit card.
     */
    function charge()
    {
        // Establish a token
        if (empty($this->billing['gateway_id_1'])) {
            $data = $this->build_token();
            if ($data['error'] == '1') {
                return $data;
            } else {
                $this->token = $data['cust_id'];
            }
        } else {
            $this->token = $this->billing['gateway_id_1'];
        }
        // Free Trials?
        if ($this->amount > 0) {
            // Proceed
            $fields = 'amount=' . $this->amount;
            $fields .= '&currency=' . $this->get_option('currency');
            $fields .= '&customer=' . $this->token;
            $fields .= '&description=' . $this->use_order_id;
            $call = $this->call_gateway($fields, 'charges');
            return $call;
        } else {
            $return = $this->empty_charge_array();
            $return['cust_id'] = $this->token;
            $return['gateway_id_1'] = $this->token;
            $return['id'] = $this->gateway_name;
            return $return;
        }
    }

    /**
     * Refund an order
     * $this->use_order_id -> ppSD_cart_sessions.gateway_order_id
     */
    function refund()
    {
        // Proceed
        $fields    = 'amount=' . $this->amount;
        $send_name = 'charges/' . $this->use_order_id . '/refund';
        $call      = $this->call_gateway($fields, $send_name);

        return $call;
    }

    /**
     * Update a customer profile
     * $this->billing comes in with the inforamtion
     *    you want to update, including 'gateway_id_1'
     *    as the customer profile ID stored in ppSD_cart_billing.
     */
    function update_user()
    {
        // Proceed
        $fields = 'card[number]=' . $this->billing['cc_number'];
        $fields .= '&card[exp_month]=' . $this->billing['card_exp_mm'];
        $fields .= '&card[exp_year]=' . '20' . $this->billing['card_exp_yy'];
        if (!empty($this->billing['cvv'])) {
            $fields .= '&card[cvc]=' . $this->billing['cvv'];
        }
        $fields .= '&card[name]=' . $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $fields .= '&card[address_line1]=' . $this->billing['address_line_1'];
        $fields .= '&card[address_line2]=' . $this->billing['address_line_2'];
        $fields .= '&card[address_state]=' . $this->billing['state'];
        $fields .= '&card[address_zip]=' . $this->billing['zip'];
        $fields .= '&card[address_country]=' . $this->billing['country'];
        // Update it
        $send_name = 'customers/' . $this->billing['gateway_id_1'];
        $call      = $this->call_gateway($fields, $send_name);

        return $call;
    }

    /**
     * Delete a user
     * $this->billing only needs 'gateway_id_1'
     *    representing the customer profile ID
     *    to be deleted.
     */
    function delete_user()
    {
        // Proceed
        $fields = 'id=' . $this->billing['gateway_id_1'];
        // Update it
        $send_name = 'customers/' . $this->billing['gateway_id_1'];
        $call      = $this->call_gateway($fields, $send_name, 'DELETE');

        return $call;
    }

    /**
     * ----end:payment_methods------------------------>
     */
    /**
     * Add credit card
     */
    function add_card()
    {
        $data = $this->build_token();

        if ($data['error'] == '1') {
            return $data;
        } else {
            return array('error' => '0', 'gateway_id_1' => $data['cust_id']);
        }
    }

    /**
     * Build a token
     */
    function build_token()
    {
        $fields = 'card[number]=' . $this->billing['cc_number'];
        $fields .= '&card[exp_month]=' . $this->billing['card_exp_mm'];
        $fields .= '&card[exp_year]=' . '20' . $this->billing['card_exp_yy'];
        if (!empty($this->billing['cvv'])) {
            $fields .= '&card[cvc]=' . $this->billing['cvv'];
        }
        $fields .= '&card[name]=' . $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $fields .= '&card[address_line1]=' . $this->billing['address_line_1'];
        $fields .= '&card[address_line2]=' . $this->billing['address_line_2'];
        $fields .= '&card[address_state]=' . $this->billing['state'];
        $fields .= '&card[address_zip]=' . $this->billing['zip'];
        $fields .= '&card[address_country]=' . $this->billing['country'];
        $call = $this->call_gateway($fields, 'customers');

        return $call;
    }

    /**
     * Call a gateway
     */
    function call_gateway($send_data, $reason = 'charge', $cus_req = '')
    {
        // Establist URL
        $url         = 'https://api.stripe.com/v1/' . $reason;
        $credentials = $this->gateway_data['credential1'] . ':';
        // Make the cURL call
        $call = $this->curl_call($url, $send_data, '0', $credentials, $cus_req);
        // Handle reply
        $reply = $this->handle_reply($call, 'id');
        return $reply;
    }

    /**
     * Handle a reply
     */
    function handle_reply($reply_data, $more = '')
    {
        $json   = json_decode($reply_data);
        $return = array();
        if (!empty($json->{'error'})) {
            $return['error']     = '1';
            $return['msg']       = $json->error->{'message'};
            $return['resp_code'] = $json->error->{'type'};
            if (!empty($json->error->{'code'})) {
                $return['resp_code'] .= ':' . $json->error->{'code'};
            }
            if (!empty($json->error->{'param'})) {
                $return['resp_code'] .= ':' . $json->error->{'param'};
            }
        } else {
            $return['error']     = '0';
            $return['msg']       = '';
            $return['resp_code'] = '';
        }
        $return['id'] = $this->gateway_name;
        if (empty($this->token) && !empty($json->{'id'})) {
            $this->token = $json->{'id'};
        }
        $return['gateway_id_1'] = $this->token;
        $return['zen_order_id'] = $this->use_order_id;
        if (!empty($json->{'id'})) {
            $return['order_id'] = $json->{'id'};
        }
        if (!empty($json->{'fee'})) {
            $return['fee'] = $json->{'fee'} / 100;
        }
        if (!empty($more)) {
            if (!empty($json->{$more})) {
                if ($more == 'id') {
                    $return['cust_id'] = $json->{$more};
                } else {
                    $return[$more] = $json->{$more};
                }
            }
        }
        return $return;
    }

}
