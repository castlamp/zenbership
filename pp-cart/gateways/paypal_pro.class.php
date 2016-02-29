<?php

/**
 * PayPal Payments Pro Integration
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
class gw_paypal_pro extends cart
{

    var $gateway_name = 'gw_paypal_pro';
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
        // Slow PayPal?
        $q171 = $this->run_query("
			SET session wait_timeout=300;
		");
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
        // Check billing format
        if ($this->billing['card_type'] == 'Mastercard') {
            $this->billing['card_type'] = 'MasterCard';
        }
        // We do not have a card on
        // file for this user.
        if (empty($this->billing['gateway_id_1'])) {

            if ($this->amount <= 0) {
                $this->send_data['PAYMENTACTION']  = 'Authorization'; // authorization
            } else {
                $this->send_data['PAYMENTACTION']  = 'Sale'; // authorization
            }
            $this->send_data['METHOD']         = 'DoDirectPayment'; // DoCapture

            $this->send_data['CREDITCARDTYPE'] = $this->billing['card_type'];
            $this->send_data['ACCT']           = $this->billing['cc_number'];
            $this->send_data['EXPDATE']        = $this->billing['card_exp_mm'] . '20' . $this->billing['card_exp_yy'];
            $this->send_data['EMAIL']          = $this->billing['email'];
            $this->send_data['FIRSTNAME']      = $this->billing['first_name'];
            $this->send_data['LASTNAME']       = $this->billing['last_name'];
            $this->send_data['STREET']         = $this->billing['address_line_1'] . ' ' . $this->billing['address_line_2'];
            $this->send_data['CITY']           = $this->billing['city'];
            $this->send_data['STATE']          = $this->billing['state'];
            $this->send_data['ZIP']            = $this->billing['zip'];
            $this->send_data['PHONENUM']       = $this->billing['phone'];

            if (!empty($this->billing['cvv'])) {
                $this->send_data['CVV2'] = $this->billing['cvv'];
            }
            
            $reply = $this->call_gateway(array('TRANSACTIONID'));
        } // Previous transaction ID on file,
        // use that to charge the card.
        // Note that we need to store the new
        // transaction ID... they expire after
        // a year.
        else {
            $this->send_data['METHOD']        = 'DoReferenceTransaction';
            $this->send_data['REFERENCEID']   = $this->billing['gateway_id_1'];
            $this->send_data['PAYMENTACTION'] = 'Sale';
            $reply                            = $this->call_gateway(array('TRANSACTIONID'));
            // Store new ID
            if ($reply['error'] != '1') {
                $q171 = $this->update("
					UPDATE `ppSD_cart_billing`
					SET `gateway_id_1`='" . $this->mysql_clean($reply['TRANSACTIONID']) . "'
					WHERE `id`='" . $this->mysql_clean($this->billing['id']) . "'
					LIMIT 1
				");
            }
        }

        return $reply;
    }

    /**
     *    True for all orders
     */
    function construct_basics()
    {
        $this->send_data['VERSION']   = '3.0';
        $this->send_data['USER']      = $this->gateway_data['credential1'];
        $this->send_data['PWD']       = $this->gateway_data['credential2'];
        $this->send_data['SIGNATURE'] = $this->gateway_data['credential3'];
        $this->send_data['INVNUM']    = $this->use_order_id;
        $this->send_data['ITEMAMT']   = $this->amount;
        $this->send_data['AMT']       = $this->amount;
        // $this->send_data['CANCELURL'] = PP_URL . '/cart.php';
        $this->send_data['COUNTRYCODE']  = 'US';
        $this->send_data['CURRENCYCODE'] = $this->get_option('currency');
    }

    /**
     * Call a gateway
     */
    function call_gateway($more = '')
    {
        // Establist URL
        if ($this->gateway_data['test_mode'] == '1') {
            $url = 'https://api-3t.sandbox.paypal.com/nvp';
        } else {
            $url = 'https://api-3t.paypal.com/nvp';
        }
        // To string
        $fields = $this->build_string();
        // Make the cURL call
        $call = $this->curl_call($url, $fields);
        // Handle reply
        $reply = $this->handle_reply($call, $more);

        return $reply;
    }

    /**
     * Build a string
     */
    function build_string()
    {
        $string = '';
        foreach ($this->send_data as $name => $value) {
            $string .= urlencode($name) . '=' . urlencode($value) . '&';
        }

        return rtrim($string, "& ");
    }

    /**
     * Handle a reply
     */
    function handle_reply($reply_data, $more = '')
    {
        // Cut up reply
        $result = $this->deformatNVP($reply_data);
        $ack    = strtoupper($result['ACK']);
        // auth.net
        $return                 = array();
        $return['id']           = $this->gateway_name;
        $return['msg']          = $ack;
        $return['resp_code']    = '';
        $return['zen_order_id'] = $this->use_order_id;
        $return['order_id']     = $result['CORRELATIONID'];
        $return['fee']          = '';
        // More
        if (!empty($more)) {
            foreach ($more as $item) {
                $return[$item] = $result[$item];
            }
        }
        // Approved!
        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            $return['error'] = '0';
        } else {
            $return['error']     = '1';
            $return['msg']       = $result['L_SHORTMESSAGE0'] . ': ' . $result['L_LONGMESSAGE0'];
            $return['resp_code'] = $result['L_ERRORCODE0'];
        }

        return $return;
    }

    /**
     * Deformat returned name-value pairs
     */
    function deformatNVP($nvpstr)
    {
        $intial   = 0;
        $nvpArray = array();
        while (strlen($nvpstr)) {
            $keypos                       = strpos($nvpstr, '=');
            $valuepos                     = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
            $keyval                       = substr($nvpstr, $intial, $keypos);
            $valval                       = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr                       = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }

        return $nvpArray;
    }

}
