<?php

/**
 * Authorize.net CIM Integration
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
class gw_authnet_cim extends cart
{

    var $gateway_name = 'gw_authnet_cim';
    var $send_data;
    var $gateway_data;
    var $use_order;
    var $return;

    var $amount;
    var $billing;
    var $shipping;
    var $auth_only;
    var $profile_id;
    var $profile_payment_id;

    public $show_error;

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
        // Make sure we have what we need
        // Profile ID
        if (empty($this->billing['gateway_id_1'])) {
            $data = $this->build_profile();
            if ($data['error'] == '1') {
                if ($data['resp_code'] == 'E00039') {
                    $cutup = explode(' ',$data['msg']);
                    $this->profile_id = $cutup['5'];
                } else {
                    $this->show_error = $data;
                    return $data;
                }
            } else {
                $this->profile_id = $data['customerProfileId'];
                $this->gateway_id_1 = $data['customerProfileId'];
            }
        } else {
            $this->profile_id = $this->billing['gateway_id_1'];
        }

        // Payment ID
        if (empty($this->billing['gateway_id_2'])) {
            $data = $this->build_payment_profile();

            if ($data['error'] == '1') {
                $this->show_error = $data;
                return $data;
            } else {
                $this->profile_payment_id = $data['customerPaymentProfileId'];
                $this->gateway_id_2 = $data['customerPaymentProfileId'];
            }
        } else {
            $this->profile_payment_id = $this->billing['gateway_id_2'];
        }
    }

    /**
     * <----start:payment_methods------------------------
     */
    /**
     * Charge a credit card.
     * $price        : Price formatted in 0.00 format.
     * $billing    : $cart->use_order_card_info(CARD_ID,'1')
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
        // Type
        if ($this->auth_only == "1") {
            $final_type = "profileTransAuthOnly";
        } else {
            $final_type = "profileTransAuthCapture";
        }
        // Charge the card
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<transaction>" .
            "<$final_type>" .
            "<amount>" . $this->amount . "</amount>" . // should include tax, shipping, and everything.
            "<customerProfileId>" . $this->profile_id . "</customerProfileId>" .
            "<customerPaymentProfileId>" . $this->profile_payment_id . "</customerPaymentProfileId>" .
            "<order>" .
            "<invoiceNumber>" . $this->use_order_id . "</invoiceNumber>" .
            "</order>" .
            "</$final_type>" .
            "</transaction>" .
            "</createCustomerProfileTransactionRequest>";
        // Make the call
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call);

        return $reply;
    }


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
     * Constructor always generates a profile and
     * payment ID, so we just return them here.
     */
    function build_token()
    {
        if (empty($this->profile_id)) {
            return array(
                'error' => '1',
                'msg' => 'Could not build profile ID.',
                'resp_code' => 'ZEN100',
            );
        }
        else if (empty($this->profile_payment_id)) {
            return array(
                'error' => '1',
                'msg' => (! empty($this->return['msg'])) ? $this->return['resp_code'] . ': ' . $this->return['msg'] : 'Could not validate credit card.',
                'resp_code' => (! empty($this->return['resp_code'])) ? $this->return['resp_code'] : 'ZEN101',
            );
        }
        else {
            return array(
                'error' => '0',
                'cust_id' => $this->profile_id,
                'gateway_id_2' => $this->profile_payment_id,
                'msg' => '',
                'resp_code' => '',
            );
        }
    }


    /**
     * Create a profile
     */
    function build_profile()
    {
        $rid = rand(1,9999999);
        $useid = substr(md5($this->billing['email']), 0, 10) . '-' . $rid;
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<profile>" .
            "<merchantCustomerId>" . $useid . "</merchantCustomerId>" .
            "<description>Zenbership Profile No. $rid</description>" .
            "<email>" . $this->billing['email'] . "</email>" .
            "</profile>" .
            "</createCustomerProfileRequest>";
        // Create it
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call, array('customerProfileId'));

        return $reply;
    }

    /**
     * Create a payment profile
     */
    function build_payment_profile()
    {
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<customerProfileId>" . $this->profile_id . "</customerProfileId>" .
            "<paymentProfile>" .
            "<billTo>" .
            "<firstName>" . $this->billing['first_name'] . "</firstName>" .
            "<lastName>" . $this->billing['last_name'] . "</lastName>" .
            "<company>" . $this->billing['company_name'] . "</company>" .
            "<address>" . $this->billing['address_line_1'] . " " . $this->billing['address_line_2'] . "</address>" .
            "<city>" . $this->billing['city'] . "</city>" .
            "<state>" . $this->billing['state'] . "</state>" .
            "<zip>" . $this->billing['zip'] . "</zip>" .
            "<country>" . $this->billing['country'] . "</country>" .
            "<phoneNumber>" . $this->billing['phone'] . "</phoneNumber>" .
            "</billTo>
            <payment>";
        if (!empty($this->billing['bank_account_name'])) {
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
            $content .= "<bankAccount>" .
                "<accountType>" . $accttype . "</accountType>" .
                "<routingNumber>" . $this->billing['bank_routing'] . "</routingNumber>" .
                "<accountNumber>" . $this->billing['bank_account_number'] . "</accountNumber>" .
                "<nameOnAccount>" . $this->billing['bank_account_name'] . "</nameOnAccount>" .
                "<echeckType>" . $echecktype . "</echeckType>" .
                "<bankName>" . $this->billing['bank_name'] . "</bankName>" .
                "</bankAccount>";
        } else {
            $content .= "<creditCard>" .
                "<cardNumber>" . $this->billing['cc_number'] . "</cardNumber>" .
                "<expirationDate>20" . $this->billing['card_exp_yy'] . "-" . $this->billing['card_exp_mm'] . "</expirationDate>" . // required format for API is YYYY-MM
                "</creditCard>" .
                "</payment>";
        }
        $content .= "</paymentProfile>" .
            "<validationMode>liveMode</validationMode>" .
            "</createCustomerPaymentProfileRequest>";

        // Create it
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call, array('customerPaymentProfileId'));

        return $reply;
    }

    /**
     * Update a customer profile
     * $this->billing comes in with the inforamtion
     *    you want to update, including 'gateway_id_1'
     *    as the customer profile ID stored in ppSD_cart_billing.
     */
    function update_user()
    {
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<updateCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<customerProfileId>" . $this->profile_id . "</customerProfileId>" .
            "<paymentProfile>" .
            "<billTo>" .
            "<firstName>" . $this->billing['first_name'] . "</firstName>" .
            "<lastName>" . $this->billing['last_name'] . "</lastName>" .
            "<company>" . $this->billing['company_name'] . "</company>" .
            "<address>" . $this->billing['address_line_1'] . " " . $this->billing['address_line_2'] . "</address>" .
            "<city>" . $this->billing['city'] . "</city>" .
            "<state>" . $this->billing['state'] . "</state>" .
            "<zip>" . $this->billing['zip'] . "</zip>" .
            "<country>" . $this->billing['country'] . "</country>" .
            "<phoneNumber>" . $this->billing['phone'] . "</phoneNumber>" .
            "</billTo>" .
            "<payment>" .
            "<creditCard>" .
            "<cardNumber>" . $this->billing['cc_number'] . "</cardNumber>" .
            "<expirationDate>20" . $this->billing['card_exp_yy'] . "-" . $this->billing['card_exp_mm'] . "</expirationDate>" . // required format for API is YYYY-MM
            "</creditCard>" .
            "</payment>" .
            "<customerPaymentProfileId>" . $this->profile_payment_id . "</customerPaymentProfileId>" .
            "</paymentProfile>" .
            "<validationMode>liveMode</validationMode>" .
            "</updateCustomerPaymentProfileRequest>";
        // Delete it
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call);

        return $reply;
    }

    /**
     * Delete a user
     * $this->billing only needs 'gateway_id_1'
     *    representing the customer profile ID
     *    to be deleted.
     */
    function delete_user()
    {
        // Prepare
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<deleteCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<customerProfileId>" . $this->profile_id . "</customerProfileId>" .
            "<customerPaymentProfileId>" . $this->profile_payment_id . "</customerPaymentProfileId>" .
            "</deleteCustomerPaymentProfileRequest>";
        // Delete it
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call);

        return $reply;
    }

    /**
     * Refund an order
     */
    function refund()
    {
        // Prepare
        $content =
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
            "<merchantAuthentication>" .
            "<name>" . $this->gateway_data['credential1'] . "</name>" .
            "<transactionKey>" . $this->gateway_data['credential2'] . "</transactionKey>" .
            "</merchantAuthentication>" .
            "<transaction>
                <profileTransRefund>
                    <amount>" . $this->amount . "</amount>
					<customerProfileId>" . $this->profile_id . "</customerProfileId>
					<customerPaymentProfileId>" . $this->profile_payment_id . "</customerPaymentProfileId>
					<transId>" . $this->use_order_id . "</transId>
				</profileTransRefund>
			</transaction>" .
            "</createCustomerProfileTransactionRequest>";
        // Delete it
        $call  = $this->call_gateway($content);
        $reply = $this->handle_reply($call);

        return $reply;
    }

    /**
     * ----end:payment_methods------------------------>
     */
    /**
     * Call a gateway
     */
    function call_gateway($send_data)
    {
        // Establist URL
        if ($this->gateway_data['test_mode'] == '1') {
            $url = 'https://apitest.authorize.net/xml/v1/request.api';
        } else {
            $url = 'https://api2.authorize.net/xml/v1/request.api';
        }
        // Prep the data
        $call = $this->curl_call($url, $send_data, '1');
        return $call;
    }

    /**
     * Handle a reply
     */
    function handle_reply($result, $return_with = '')
    {
        $approval = strtolower($this->split_xml("resultCode", $result));
        // Standard reply
        $direct = $this->split_xml("directResponse", $result);
        if (!empty($direct)) {
            $direct_reply       = explode(",", $direct);
            $return['order_id'] = $direct_reply['6'];
        } else {
            $return['order_id'] = '';
        }
        // Build reply array
        $return                       = array();
        $return['id']                 = $this->gateway_name;
        $return['msg']                = $this->split_xml("text", $result);
        $return['resp_code']          = $this->split_xml("code", $result);
        $return['fee']                = '';
        $return['zen_order_id']       = $this->use_order_id;
        $return['profile_id']         = $this->profile_id;
        $return['payment_profile_id'] = $this->profile_payment_id;
        // Order id?
        $directResponse = strtolower($this->split_xml("directResponse", $result));
        if (!empty($directResponse)) {
            $cutup              = explode(',', $directResponse);
            $return['order_id'] = $cutup['6'];
        }
        // Approved!
        if ($approval == "ok") {
            $return['error'] = '0';
        } else {
            $return['error'] = '1';
        }
        // Need more stuff?
        if (!empty($return_with)) {
            foreach ($return_with as $name) {
                $return[$name] = $this->split_xml($name, $result);
            }
        }

        $this->return = $return;

        return $return;
    }

}
