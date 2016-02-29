<?php

/**
 * Beanstream Integration
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
class gw_beanstream extends cart
{

    var $gateway_name = 'gw_beanstream';
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
        // Make sure we have what we need
        // Token
        if (empty($this->billing['gateway_id_1'])) {
            $data = $this->build_token();
            if ($data['error'] == '1') {
                return $data;
            } else {
                $this->token     = $data['customerCode'];
                $this->send_data = '';
            }
        } else {
            $this->token = $this->billing['gateway_id_1'];
        }
    }

    /**
     * <----start:payment_methods------------------------
     */
    /**
     *    True for all orders
     */
    function construct_basics()
    {
        if ($this->doing == 'order') {
            $this->send_data['merchant_id'] = $this->gateway_data['credential1'];
            $this->send_data['hashValue']   = $this->gateway_data['credential3'];
        } else {
            $this->send_data['merchantId']     = $this->gateway_data['credential1'];
            $this->send_data['passCode']       = $this->gateway_data['credential2'];
            $this->send_data['serviceVersion'] = '1.0';
            $this->send_data['responseFormat'] = 'QS';
            $this->send_data['trnLanguage']    = 'ENG';
        }
        $this->send_data['requestType'] = 'BACKEND';
    }

    function build_token()
    {
        // Task
        $this->doing = 'token';
        $this->construct_basics();
        // Data
        $this->send_data['operationType'] = 'N'; // N = New, M = Modify
        $this->send_data['status']        = 'A';
        //if (! empty($this->billing['accountNumber'])) {
        //	$this->send_data['accountNumber'] = $this->billing['accountNumber'];
        //	$this->send_data['bankAccountType'] = $this->billing['accttype'];
        //	$this->send_data['bankAccountHolder'] = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        //	if (! empty($this->billing['institutionNumber'])) {
        //		$this->send_data['institutionNumber'] = $this->billing['institutionNumber'];
        //		$this->send_data['branchNumber'] = $this->billing['branchNumber'];
        //	} else {
        //		$this->send_data['routingNumber'] = $this->billing['routingNumber'];
        //	}
        //} else {
        $this->send_data['trnCardOwner']   = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $this->send_data['trnCardNumber']  = $this->billing['cc_number'];
        $this->send_data['trnExpMonth']    = $this->billing['card_exp_mm'];
        $this->send_data['trnExpYear']     = $this->billing['card_exp_yy'];
        $this->send_data['trnCardCvd']     = $this->billing['cvv'];
        $this->send_data['cardValidation'] = '1';
        //}
        $this->send_data['ordName']         = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $this->send_data['ordAddress1']     = $this->billing['address_line_1'];
        $this->send_data['ordAddress2']     = $this->billing['address_line_2'];
        $this->send_data['ordCity']         = $this->billing['city'];
        $this->send_data['ordProvince']     = $this->billing['state'];
        $this->send_data['ordCountry']      = $this->billing['country'];
        $this->send_data['ordPostalCode']   = $this->billing['zip'];
        $this->send_data['ordEmailAddress'] = $this->billing['email'];
        $this->send_data['ordPhoneNumber']  = $this->billing['phone'];
        // Create it
        $call = $this->call_gateway(array('customerCode'));

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
        // Task
        $this->doing = 'token';
        $this->construct_basics();
        // Data
        $this->send_data['operationType']   = 'M'; // N = New, M = Modify
        $this->send_data['customerCode']    = $this->token;
        $this->send_data['trnCardOwner']    = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $this->send_data['trnCardNumber']   = $this->billing['cc_number'];
        $this->send_data['trnExpMonth']     = $this->billing['card_exp_mm'];
        $this->send_data['trnExpYear']      = $this->billing['card_exp_yy'];
        $this->send_data['trnCardCvd']      = $this->billing['cvv'];
        $this->send_data['cardValidation']  = '1';
        $this->send_data['ordName']         = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $this->send_data['ordAddress1']     = $this->billing['address_line_1'];
        $this->send_data['ordAddress2']     = $this->billing['address_line_2'];
        $this->send_data['ordCity']         = $this->billing['city'];
        $this->send_data['ordProvince']     = $this->billing['state'];
        $this->send_data['ordCountry']      = $this->billing['country'];
        $this->send_data['ordPostalCode']   = $this->billing['zip'];
        $this->send_data['ordEmailAddress'] = $this->billing['email'];
        $this->send_data['ordPhoneNumber']  = $this->billing['phone'];
        // Delete it
        $call = $this->call_gateway(array('customerCode'));

        // $reply = $this->handle_reply($call,array('customerCode'));
        return $call;
    }

    /**
     * Delete a user
     * $this->billing only needs 'gateway_id_1'
     *    representing the customer profile ID
     *    to be deleted.
     * 'status' => D -> This means disabled, not deleted.
     *    There is no method for deleting through the API.
     */
    function delete_user()
    {
        // Task
        $this->doing = 'token';
        $this->construct_basics();
        // Data
        $this->send_data['operationType'] = 'M'; // N = New, M = Modify
        $this->send_data['customerCode']  = $this->token;
        $this->send_data['status']        = 'D';
        // Delete it
        $this->doing = 'token';
        $call        = $this->call_gateway();

        //$reply = $this->handle_reply($call,array('customerCode'));
        return $call;
    }

    /**
     * Charge a credit card.
     * $price        : Price formatted in 0.00 format.
     * $this->billing    : $cart->use_order_card_info(CARD_ID,'1')
     *    [id]                : Card ID
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
        // Task
        $this->doing = 'order';
        $this->construct_basics();
        // Data
        $this->send_data['trnOrderNumber'] = $this->use_order_id;
        $this->send_data['trnAmount']      = $this->amount;
        $this->send_data['customerCode']   = $this->token;
        $this->send_data['trnType']        = 'P';
        // $this->send_data['hashValue'] = $this->billing[''];
        $reply = $this->call_gateway();

        return $reply;
    }

    /**
     * eCheck
     */
    function echeck()
    {
        // Task
        $this->doing = 'order';
        $this->construct_basics();
        // Data
        // Build request
        $this->send_data['trnType']        = 'D';
        $this->send_data['trnOrderNumber'] = $this->use_order_id;
        $this->send_data['trnAmount']      = $this->amount;
        //$this->send_data['customerCode'] = $this->token;
        // $this->send_data['customerCode'] = $this->token;
        $this->send_data['bankAccountType']   = $this->billing['accttype'];
        $this->send_data['bankAccountHolder'] = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        if (!empty($this->billing['institutionNumber'])) {
            $this->send_data['institutionNumber'] = $this->billing['institutionNumber'];
            $this->send_data['branchNumber']      = $this->billing['branchNumber'];
        } else {
            $this->send_data['routingNumber'] = $this->billing['bank_routing'];
        }
        $this->send_data['accountNumber'] = $this->billing['bank_account_number'];
        $reply                            = $this->call_gateway();

        return $reply;
    }

    /**
     * Refund an order
     */
    function refund()
    {
        // Task
        $this->doing = 'order';
        $this->construct_basics();
        // Data
        $this->send_data['trnType']   = 'R';
        $this->send_data['trnAmount'] = $this->amount;
        $this->send_data['adjId']     = $this->use_order_id;
        $reply                        = $this->call_gateway();

        return $reply;
    }

    /**
     * Call a gateway
     */
    function call_gateway($more = '')
    {
        // Establist URL
        if ($this->doing == 'token') {
            $url = 'https://www.beanstream.com/scripts/payment_profile.asp';
        } else {
            $url = 'https://www.beanstream.com/scripts/process_transaction.asp';
        }
        // Build the string
        $string = $this->build_string();
        // Make the cURL call
        $call = $this->curl_call($url, $string, '0');
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
            if ($name == 'hashValue') {
                $temp_string = rtrim($string, '&') . urlencode($value);
                $md5_hash    = md5($temp_string);
                $string .= 'hashValue=' . $md5_hash . '&';
            } else {
                $string .= urlencode($name) . '=' . urlencode($value) . '&';
            }
        }

        return rtrim($string, "& ");
    }

    function build_xml()
    {
        $string = '';
        foreach ($this->send_data as $name => $value) {
            $string .= '<' . $name . '>' . $value . '</' . $name . '>' . "\n";
        }

        return $string;
    }

    /**
     * Handle a reply
     * trnApproved        = 0 (Rejected)
     * trnId        = 0
     * trnOrderNumber    =
     * messageId        = (ERROR CODE)
     * messageText        = (ERROR DESCRIPTION)
     * errorType        =
     * errorFields        =
     */
    function handle_reply($reply_data, $more = '')
    {
        $result       = $this->deformatNVP($reply_data);
        $return       = array();
        $return['id'] = $this->gateway_name;
        if (!empty($result['responseMessage'])) {
            $return['msg'] = $result['responseMessage'];
        } else {
            $return['msg'] = $result['messageText'];
        }
        if (!empty($result['responseCode'])) {
            $return['resp_code'] = $result['responseCode'];
        } else {
            $return['resp_code'] = $result['messageId'];
        }
        $return['zen_order_id'] = $this->use_order_id;
        // Trans ID
        if (!empty($result['trnId'])) {
            $fid = $result['trnId'];
        } else {
            $fid = '';
        }
        $return['order_id'] = $fid;
        $return['fee']      = '';
        // More
        if (!empty($more)) {
            foreach ($more as $item) {
                $return[$item] = $result[$item];
            }
        }
        // Approved!
        if (!empty($result['trnApproved'])) {
            if ($result['trnApproved'] == "1") {
                $return['error'] = '0';
            } else {
                $return['error'] = '1';
            }
        } else {
            if ($result['responseCode'] == "1") {
                $return['error'] = '0';
            } else {
                $return['error'] = '1';
            }
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
