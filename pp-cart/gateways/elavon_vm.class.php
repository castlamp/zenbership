<?php

/**
 * Elavon VritualMerchant Integration
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
class gw_elavon_vm extends cart
{

    var $gateway_name = 'gw_elavon_vm';
    var $send_data;
    var $gateway_data;
    var $use_order_id;
    var $use_order;
    var $return;
    var $doing;
    var $amount;
    var $billing;
    var $shipping;
    var $auth_only;
    var $basic_data;
    var $current_action;

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
        $this->amount    = $price; // In cents
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
        // Make call
        $this->construct_basics();
    }

    /**
     *    True for all orders
     */
    function construct_basics()
    {
        $this->basic_data['ssl_merchant_id']  = $this->gateway_data['credential1'];
        $this->basic_data['ssl_user_id']      = $this->gateway_data['credential2'];
        $this->basic_data['ssl_pin']          = $this->gateway_data['credential3'];
        $this->basic_data['ssl_amount']       = $this->amount;
        $this->basic_data['ssl_card_present'] = 'N';
    }

    function build_billing()
    {
        $this->basic_data['ssl_first_name']     = $this->billing['first_name'];
        $this->basic_data['ssl_last_name']      = $this->billing['last_name'];
        $this->basic_data['ssl_avs_address']    = $this->billing['address_line_1'];
        $this->basic_data['ssl_address2']       = $this->billing['address_line_2'];
        $this->basic_data['ssl_city']           = $this->billing['city'];
        $this->basic_data['ssl_state']          = $this->billing['state'];
        $this->basic_data['ssl_avs_zip']        = $this->billing['zip'];
        $this->basic_data['ssl_country']        = $this->billing['country'];
        $this->basic_data['ssl_phone']          = $this->billing['phone'];
        $this->basic_data['ssl_email']          = $this->billing['email'];
        $this->basic_data['ssl_customer_code']  = $this->billing['username'];
        $this->basic_data['ssl_invoice_number'] = $this->use_order_id;
    }

    function build_shipping()
    {
        $this->basic_data['ssl_ship_to_company']       = $this->shipping['company_name'];
        $this->basic_data['ssl_ship_to_first_name']    = $this->shipping['first_name'];
        $this->basic_data['ssl_ship_to_last_name']     = $this->shipping['last_name'];
        $this->basic_data['ssl_ship_to_address1']      = $this->shipping['address_line_1'];
        $this->basic_data['ssl_ship_to_address2']      = $this->shipping['address_line_2'];
        $this->basic_data['ssl_ship_to_city']          = $this->shipping['city'];
        $this->basic_data['ssl_ship_to_ship_to_state'] = $this->shipping['state'];
        $this->basic_data['ssl_ship_to_zip']           = $this->shipping['zip'];
        $this->basic_data['ssl_ship_to_country']       = $this->shipping['country'];
    }

    /**
     * Charge a credit card.
     */
    function charge()
    {
        $this->basic_data['ssl_salestax']         = '0.00';
        $this->basic_data['ssl_show_form']        = 'false';
        $this->basic_data['ssl_transaction_type'] = 'ccsale';
        $this->basic_data['ssl_card_number']      = $this->billing['card_number'];
        $this->basic_data['ssl_exp_date']         = $this->billing['card_exp_mm'] . $this->billing['card_exp_yy'];
        $this->build_billing();
        if (!empty($this->shipping)) {
            $this->build_shipping();
        }
        $reply = $this->call_gateway();

        return $reply;
    }

    /**
     * Refund an order
     */
    function refund()
    {
        $this->basic_data['ssl_transaction_type'] = 'ccreturn';
        $this->basic_data['ssl_txn_id']           = $this->use_order_id;
        $this->basic_data['ssl_amount']           = $this->amount;
        $reply                                    = $this->call_gateway();

        return $reply;
    }

    /**
     * Call a gateway
     */
    function call_gateway($more = '')
    {
        // Determine URL
        if ($this->gateway_data['test_mode'] == '1') {
            $url = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do';
        } else {
            $url = 'https://www.myvirtualmerchant.com/VirtualMerchant/processxml.do';
        }
        // Make the cURL call
        $this->build_string();
        $call = $this->curl_call($url, $this->send_data, '0');
        // Handle reply
        $reply = $this->handle_reply($call, $more);

        return $reply;
    }

    /**
     * Build String
     */
    function build_string()
    {
        $this->send_data = 'xmldata=<txn>';
        foreach ($this->basic_data as $key => $value) {
            $this->send_data .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $this->send_data .= '</txn>';

        return $this->send_data;
    }

    /**
     * Handle a reply
     */
    function handle_reply($reply_data, $more = '')
    {
        $return = array();
        $result = $this->split_xml("ssl_result", $reply_data);
        if ($result == '0') {
            $error_code      = $this->split_xml("ssl_approval_code", $reply_data);
            $message         = $this->split_xml("ssl_result_message", $reply_data);
            $return['error'] = '0';
        } else {
            $error_code = $this->split_xml("errorCode", $reply_data);
            if (empty($error_code)) {
                $error_code = $this->split_xml("ssl_result_message", $reply_data);
            } else {
                $message = $this->split_xml("errorMessage", $reply_data);
            }
            $return['error'] = '1';
        }
        $return['id']           = $this->gateway_name;
        $return['msg']          = $message;
        $return['resp_code']    = $error_code;
        $return['zen_order_id'] = $this->use_order_id;
        $return['order_id']     = $this->split_xml("ssl_txn_id", $reply_data);
        $return['fee']          = '';
        // More
        if (!empty($more)) {
            foreach ($more as $item) {
                $return[$item] = $reply_data[$item];
            }
        }

        return $return;
    }

}
