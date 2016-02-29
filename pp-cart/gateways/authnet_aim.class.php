<?php

/**
 * Authorize.net AIM Integration
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
class gw_authnet_aim extends cart
{

    var $gateway_name = 'gw_authnet_aim';
    var $send_data;
    var $gateway_data;
    var $use_order_id;
    var $use_order;
    var $return;

    var $amount;
    var $billing;
    var $shipping;
    var $auth_only;

    /**
     * Required basics
     * credential1 = auth_net_login_id
     * credential2 = auth_net_trans_key
     */
    function __construct($price, $billing, $use_order_id = '', $shipping = '', $auth_only = '0')
    {
        // Vars
        $this->billing   = $billing;
        $this->shipping  = $shipping;
        $this->amount    = $price;
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
        // Basics
        $this->construct_basics();
    }

    /**
     * <----start:payment_methods------------------------
     */
    /**
     * Charge a credit card.
     * $price        : Price formatted in 0.00 format.
     * $this->billing    : $cart->use_order_card_info(CARD_ID,'1')
     *    [card_type]        : -
     *    [cc_number]        : XXXXXXXXXXXXXXXX
     *    [card_exp_yy]        : YY
     *    [card_exp_mm]        : MM
     *    [method]            :
     *    [gateway_id_1]        :
     *    [gateway_id_2]        :
     *    [name]            :
     *    [address_line_1]    :
     *    [address_line_2]    :
     *    [city]            :
     *    [state]            : XX
     *    [zip]            : XXXXX
     *    [country]            : XX
     */
    function charge()
    {
        // Auth only
        if ($this->auth_only == "1") {
            $this->send_data['x_method'] = 'AUTH_ONLY';
        } // Auth and get month
        else {
            $this->send_data['x_method'] = 'AUTH_CAPTURE';
        }
        $this->send_data['x_card_num'] = $this->billing['cc_number'];
        $this->send_data['x_exp_date'] = '20' . $this->billing['card_exp_yy'] . '-' . $this->billing['card_exp_mm'];
        if (!empty($this->billing['cvm'])) {
            $this->send_data['x_card_code'] = $this->billing['cvm'];
        } else {
            $this->send_data['x_card_code'] = '';
        }
        // Billing Details
        $this->build_billing($this->billing);
        $this->build_shipping($this->shipping);
        // Make the call
        $reply = $this->call_gateway();

        return $reply;
    }

    /**
     * eCheck Transaction
     */
    function echeck()
    {
        // Account Type
        switch ($this->billing['accttype']) {
            case 'Personal Checking':
                $accttype   = 'checking';
                $echecktype = 'PPD';
                break;
            case 'Business Checking':
                $accttype   = 'businessChecking';
                $echecktype = 'CCD';
                break;
            case 'Savings':
                $accttype   = 'savings';
                $echecktype = 'PPD';
        }
        // Billing Details
        $this->build_billing($this->billing);
        $this->build_shipping($this->shipping);
        // More stuff
        $this->send_data['x_method']         = 'ECHECK';
        $this->send_data['x_type']           = 'AUTH_CAPTURE';
        $this->send_data['x_bank_name']      = $this->billing['bank_name'];
        $this->send_data['x_bank_aba_code']  = $this->billing['bank_routing'];
        $this->send_data['x_bank_acct_name'] = $this->billing['bank_account_name'];
        $this->send_data['x_bank_acct_num']  = $this->billing['bank_account_number'];
        $this->send_data['x_bank_acct_type'] = $accttype;
        $this->send_data['x_echeck_type']    = $echecktype;
        // Make the call
        $reply = $this->call_gateway();

        return $reply;
    }

    /**
     * Refund an order
     * x_trans_id -> ppSD_cart_sessions.gateway_order_id
     */
    function refund()
    {
        $this->send_data['x_type']     = 'CREDIT';
        $this->send_data['x_trans_id'] = $this->use_order_id;
        $this->send_data['x_amount']   = $this->amount;
        $this->send_data['x_card_num'] = $this->billing['cc_number'];
        // Make the call
        $reply = $this->call_gateway();

        return $reply;
    }

    /**
     * ----end:payment_methods------------------------>
     */
    /**
     *    True for all Auth.net orders
     */
    function construct_basics()
    {
        $this->send_data['x_amount']         = $this->amount;
        $this->send_data['x_login']          = $this->gateway_data['credential1'];
        $this->send_data['x_tran_key']       = $this->gateway_data['credential2'];
        $this->send_data['x_version']        = '3.1';
        $this->send_data['x_delim_char']     = '|';
        $this->send_data['x_delim_data']     = 'TRUE';
        $this->send_data['x_url']            = 'FALSE';
        $this->send_data['x_relay_response'] = 'FALSE';
        $this->send_data['x_invoice_num']    = $this->use_order_id;
        $this->send_data['x_trans_id']       = $this->use_order_id;
    }

    /**
     * Build billing information
     */
    function build_billing()
    {
        $this->send_data['x_company']    = $this->billing['company_name'];
        $this->send_data['x_first_name'] = $this->billing['first_name'];
        $this->send_data['x_last_name']  = $this->billing['last_name'];
        $this->send_data['x_address']    = $this->billing['address_line_1'] . ' ' . $this->billing['address_line_2'];
        $this->send_data['x_city']       = $this->billing['city'];
        $this->send_data['x_state']      = $this->billing['state'];
        $this->send_data['x_zip']        = $this->billing['zip'];
        $this->send_data['x_country']    = $this->billing['country'];
        $this->send_data['x_phone']      = $this->billing['phone'];
        $this->send_data['x_email']      = $this->billing['email'];
        $this->send_data['x_cust_id']    = $this->billing['member_id'];
    }

    /**
     * Build shipping information
     */
    function build_shipping()
    {
        if (!empty($this->shipping)) {
            $this->send_data['x_ship_to_first_name'] = $this->shipping['first_name'];
            $this->send_data['x_ship_to_last_name']  = $this->shipping['last_name'];
            $this->send_data['x_ship_to_address']    = $this->shipping['address_line_1'] . ' ' . $this->shipping['address_line_2'];
            $this->send_data['x_ship_to_city']       = $this->shipping['city'];
            $this->send_data['x_ship_to_state']      = $this->shipping['state'];
            $this->send_data['x_ship_to_zip']        = $this->shipping['zip'];
            $this->send_data['x_ship_to_country']    = $this->shipping['country'];
            $this->send_data['x_ship_to_company']    = $this->shipping['company_name'];
        }
    }

    /**
     * Call a gateway
     */
    function call_gateway()
    {
        // Establist URL
        if ($this->gateway_data['test_mode'] == '1') {
            $url = 'https://test.authorize.net/gateway/transact.dll';
        } else {
            $url = 'https://secure.authorize.net/gateway/transact.dll';
        }
        // Prep the data
        $fields = '';
        foreach ($this->send_data as $key => $value) {
            $fields .= $key . '=' . urlencode($value) . "&";
        }
        $fields = rtrim($fields, "& ");
        // Make the cURL call
        $call = $this->curl_call($url, $fields);
        // Handle reply
        $reply = $this->handle_reply($call);

        return $reply;
    }

    /**
     * Handle a reply
     */
    function handle_reply($reply_data)
    {
        // Cut up reply
        $result = explode('|', $reply_data);
        // auth.net
        $return                 = array();
        $return['id']           = $this->gateway_name;
        $return['msg']          = $result['3'];
        $return['resp_code']    = $result['2'];
        $return['zen_order_id'] = $this->use_order_id;
        $return['order_id']     = $result['6'];
        $return['fee']          = '';
        // Approved!
        if ($result['0'] == "1") {
            $return['error'] = '0';
        } else {
            $return['error'] = '1';
        }

        return $return;
    }

}
