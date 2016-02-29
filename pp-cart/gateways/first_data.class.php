<?php

/**
 * First Data Integration
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
class gw_first_data extends cart
{

    var $gateway_name = 'gw_first_data';
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
    }

    /**
     * <----start:payment_methods------------------------
     */
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
        $this->send_data = '<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">';
        $this->send_data .= '	<v1:Transaction>';
        $this->send_data .= '		<v1:CreditCardTxType>';
        if ($this->auth_only == '1') {
            $this->send_data .= '			<v1:Type>preAuth</v1:Type>';
            $this->amount = '0.00';
        } else {
            $this->send_data .= '			<v1:Type>sale</v1:Type>';
        }
        $this->send_data .= '		</v1:CreditCardTxType>';
        $this->send_data .= '		<v1:CreditCardData>';
        $this->send_data .= '			<v1:CardNumber>' . $this->billing['cc_number'] . '</v1:CardNumber>';
        $this->send_data .= '			<v1:ExpMonth>' . $this->billing['card_exp_mm'] . '</v1:ExpMonth>';
        $this->send_data .= '			<v1:ExpYear>' . $this->billing['card_exp_yy'] . '</v1:ExpYear>';
        $this->send_data .= '		</v1:CreditCardData>';
        $this->send_data .= '		<v1:Payment>';
        $this->send_data .= '			<v1:ChargeTotal>' . $this->amount . '</v1:ChargeTotal>';
        $this->send_data .= '		</v1:Payment>';
        $this->send_data .= '		<v1:TransactionDetails>';
        $this->send_data .= '			<v1:InvoiceNumber></v1:InvoiceNumber>';
        $this->send_data .= '		</v1:TransactionDetails>';
        $this->send_data .= '		<v1:Billing>';
        if (!empty($this->billing['member_id'])) {
            $this->send_data .= '			<v1:CustomerID>' . $this->billing['member_id'] . '</v1:CustomerID>';
        }
        $this->send_data .= '			<v1:Name>' . $this->billing['first_name'] . ' ' . $this->billing['last_name'] . '</v1:Name>';
        $this->send_data .= '			<v1:Company>' . $this->billing['company_name'] . '</v1:Company>';
        $this->send_data .= '			<v1:Address1>' . $this->billing['address_line_1'] . '</v1:Address1>';
        $this->send_data .= '			<v1:Address2>' . $this->billing['address_line_2'] . '</v1:Address2>';
        $this->send_data .= '			<v1:City>' . $this->billing['city'] . '</v1:City>';
        $this->send_data .= '			<v1:State>' . $this->billing['state'] . '</v1:State>';
        $this->send_data .= '			<v1:Zip>' . $this->billing['zip'] . '</v1:Zip>';
        $this->send_data .= '			<v1:Country>' . $this->billing['country'] . '</v1:Country>';
        $this->send_data .= '			<v1:Phone>' . $this->billing['phone'] . '</v1:Phone>';
        $this->send_data .= '		</v1:Billing>';
        /*
        if (! empty($this->shipping)) {
            $this->send_data .= '		<v1:Shipping>';
            $this->send_data .= '			<v1:Name>' . $this->shipping['first_name'] . ' ' . $this->shipping['last_name'] . '</v1:Name>';
            $this->send_data .= '			<v1:Company>' . $this->shipping['company_name'] . '</v1:Company>';
            $this->send_data .= '			<v1:Address1>' . $this->shipping['address_line_1'] . '</v1:Address1>';
            $this->send_data .= '			<v1:Address2>' . $this->shipping['address_line_2'] . '</v1:Address2>';
            $this->send_data .= '			<v1:City>' . $this->shipping['city'] . '</v1:City>';
            $this->send_data .= '			<v1:State>' . $this->shipping['state'] . '</v1:State>';
            $this->send_data .= '			<v1:Zip>' . $this->shipping['zip'] . '</v1:Zip>';
            $this->send_data .= '			<v1:Country>' . $this->shipping['country'] . '</v1:Country>';
            $this->send_data .= '			<v1:Phone>' . $this->shipping['phone'] . '</v1:Phone>';
            $this->send_data .= '		</v1:Shipping>';
        }
        */
        $this->send_data .= '	</v1:Transaction>';
        $this->send_data .= '</fdggwsapi:FDGGWSApiOrderRequest>';
        $reply = $this->call_gateway();

        return $reply;
    }

    /* PAGE 26
    Need "TransArmor" for tokenized off-site CC storage.
    */
    /**
     * eCheck
     */
    function echeck()
    {
        // Account Type
        switch ($this->billing['accttype']) {
            case 'Personal Checking':
                $accttype = 'PC';
                break;
            case 'Business Checking':
                $accttype = 'PC';
                break;
            case 'Savings':
                $accttype = 'PS';
        }
        // Build request
        $this->send_data = '<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">';
        $this->send_data .= '	<v1:Transaction>';
        $this->send_data .= '		<v1:TeleCheckTxType>';
        $this->send_data .= '			<v1:Type>sale</v1:Type>';
        $this->send_data .= '		</v1:TeleCheckTxType>';
        $this->send_data .= '		<v1:TeleCheckData>';
        $this->send_data .= '			<v1:CheckNumber>' . $this->billing['check_no'] . '</v1:CheckNumber>';
        $this->send_data .= '			<v1:AccountType>' . $accttype . '</v1:AccountType>';
        $this->send_data .= '			<v1:AccountNumber>' . $this->billing['bank_account_number'] . '</v1:AccountNumber>';
        $this->send_data .= '			<v1:RoutingNumber>' . $this->billing['bank_routing'] . '</v1:RoutingNumber>';
        $this->send_data .= '			<v1:DrivingLicenseNumber>' . $this->billing['driver_license_no'] . '</v1:DrivingLicenseNumber>';
        $this->send_data .= '			<v1:DrivingLicenseState>' . $this->billing['driver_license_state'] . '</v1:DrivingLicenseState>';
        $this->send_data .= '		</v1:TeleCheckData>';
        $this->send_data .= '		<v1:Payment>';
        $this->send_data .= '			<v1:ChargeTotal>' . $this->amount . '</v1:ChargeTotal>';
        $this->send_data .= '		</v1:Payment>';
        $this->send_data .= '	</v1:Transaction>';
        $this->send_data .= '</fdggwsapi:FDGGWSApiOrderRequest>';
        $reply = $this->call_gateway();

        return $reply;
    }

    /* Page 25
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1= "http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi= "http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi"> <v1:Transaction> <v1:TeleCheckTxType>...</v1:TeleCheckTxType> <v1:TeleCheckData>...</v1:TeleCheckData> <v1:Payment>...</v1:Payment> <v1:TransactionDetails>...</v1:TransactionDetails> <v1:Billing>...</v1:Billing> <v1:Shipping>...</v1:Shipping> </v1:Transaction> </fdggwsapi:FDGGWSApiOrderRequest>
    */
    /**
     * Refund an order
     */
    function refund()
    {
        $this->send_data = '<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">';
        $this->send_data .= '	<v1:Transaction>';
        $this->send_data .= '		<v1:CreditCardTxType>';
        $this->send_data .= '			<v1:Type>return</v1:Type>';
        $this->send_data .= '		</v1:CreditCardTxType>';
        $this->send_data .= '		<v1:Payment>';
        $this->send_data .= '			<v1:ChargeTotal>' . $this->amount . '</v1:ChargeTotal>';
        $this->send_data .= '		</v1:Payment>';
        $this->send_data .= '		<v1:TransactionDetails>';
        $this->send_data .= '			<v1:OrderId>' . $this->use_order_id . '</v1:OrderId>';
        $this->send_data .= '		</v1:TransactionDetails>';
        $this->send_data .= '	</v1:Transaction>';
        $this->send_data .= '</fdggwsapi:FDGGWSApiOrderRequest>';
        $reply = $this->call_gateway();

        return $reply;
    }

    /* Page 22
<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1= "http://secure.linkpt.net/fdggwsapi/schemas_us/v1" xmlns:fdggwsapi= "http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi"> <v1:Transaction> <v1:CreditCardTxType> <v1:Type>return</v1:Type> </v1:CreditCardTxType> <v1:Payment> <v1:ChargeTotal>19.95</v1:ChargeTotal> </v1:Payment> <v1:TransactionDetails> <v1:OrderId> 62e3b5df-2911-4e89-8356-1e49302b1807 </v1:OrderId> </v1:TransactionDetails> </v1:Transaction> </fdggwsapi:FDGGWSApiOrderRequest>
    */
    /**
     * Call a gateway
     */
    function call_gateway($more = '')
    {
        // Establist URL
        if ($this->gateway_data['test_mode'] == '1') {
            $url = 'https://ws.merchanttest.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl';
        } else {
            $url = 'https://ws.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl';
        }
        /**
         * http://www.firstdata.com/downloads/marketing-merchant/fdgg-web-service-api.pdf
         * Set some gateway stuff up
         *
         * $this->gateway_data['credential1'] => UserID: This is the store ID without the 'WS' (STORE_ID) '._.1'.
         *            -> On file WS<store_ID>._.1.auth.txt
         * $this->gateway_data['credential2'] => Password.
         *            -> On file WS<store_ID>._.1.auth.txt
         * $this->gateway_data['credential3'] =>
         *            -> On file WS<store_ID>._.1.key.pw.txt
         */
        // SOAP Envelop
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">';
        $xml .= '	<SOAP-ENV:Header />';
        $xml .= '		<SOAP-ENV:Body>';
        $xml .= $this->send_data;
        $xml .= '		</SOAP-ENV:Body>';
        $xml .= '</SOAP-ENV:Envelope>';
        // Credentials and stuff...
        $userid    = 'WS' . $this->gateway_data['credential1'] . '._.1';
        $user_pass = $userid . ':' . $this->gateway_data['credential2'];
        $pem_file  = PP_PATH . '/pp-cart/gateways/first_data/' . $userid . '.pem';
        $key_file  = PP_PATH . '/pp-cart/gateways/first_data/' . $userid . '.key';
        // Make the cURL call
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $user_pass);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
        curl_setopt($ch, CURLOPT_SSLKEY, $key_file);
        curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->gateway_data['credential3']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $call = curl_exec($ch);
        curl_close($ch);
        // Handle reply
        $reply = $this->handle_reply($call, $more);

        return $reply;
    }

    /**
     * Handle a reply
     */
    function handle_reply($reply_data, $more = '')
    {
        $emsg                   = $this->split_xml("fdggwsapi:ErrorMessage", $reply_data);
        $ecode                  = explode(':', $emsg);
        $return                 = array();
        $return['id']           = $this->gateway_name;
        $return['msg']          = $emsg;
        $return['resp_code']    = $ecode['0'];
        $return['zen_order_id'] = $this->use_order_id;
        $return['order_id']     = $this->split_xml("fdggwsapi:OrderId", $reply_data);
        $return['fee']          = '';
        // More
        if (!empty($more)) {
            foreach ($more as $item) {
                $return[$item] = $return[$item];
            }
        }
        // Approved!
        $success = $this->split_xml("fdggwsapi:TransactionResult", $reply_data);
        if ($success == "APPROVED") {
            $return['error'] = '0';
        } else {
            $return['error'] = '1';
        }

        return $return;
    }

}
