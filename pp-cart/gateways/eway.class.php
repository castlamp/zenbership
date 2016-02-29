<?php


/**
 * eWay Integration
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
class gw_eway extends cart
{

    var $gateway_name = 'gw_eway';
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
        $this->amount    = $price * 100; // In cents
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
                $this->token     = $data['CreateCustomerResult'];
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
        $this->basic_data = "
		<soap:Header>
			<eWAYHeader xmlns=\"https://www.eway.com.au/gateway/managedpayment\">
				<eWAYCustomerID>" . $this->gateway_data['credential1'] . "</eWAYCustomerID>
				<Username>" . $this->gateway_data['credential2'] . "</Username>
				<Password>" . $this->gateway_data['credential3'] . "</Password>
			</eWAYHeader>
		</soap:Header>
		";

    }

    function build_token()
    {
        // Task
        $this->doing = 'token';
        $this->construct_basics();
        // Data
        $data              = array();
        $data['Title']     = 'Mr.';
        $data['FirstName'] = $this->billing['first_name'];
        $data['LastName']  = $this->billing['last_name'];
        $data['Address']   = $this->billing['address_line_1'];
        if (!empty($this->billing['address_line_2'])) {
            $data['Address'] .= ' ' . $this->billing['address_line_2'];
        }
        $data['Suburb']   = $this->billing['city'];
        $data['State']    = $this->billing['state'];
        $data['PostCode'] = $this->billing['zip'];
        // Country must be valid two character country code.
        $data['Country']     = strtolower($this->billing['country']);
        $data['Phone']       = $this->billing['phone'];
        $data['Email']       = $this->billing['email'];
        $data['CustomerRef'] = $this->billing['username'];
        //$data['Company'] = '';
        //$data['fax'] = '';
        //$data['JobDesc'] = '';
        //$data['Comments'] = '';
        //$data['URL'] = '';
        // Credit Card
        $data['CCNameOnCard']  = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $data['CCNumber']      = $this->billing['cc_number'];
        $data['CCExpiryYear']  = $this->billing['card_exp_yy'];
        $data['CCExpiryMonth'] = $this->billing['card_exp_mm'];
        $data['CVN']           = $this->billing['cvv'];
        // Basic XML
        $content = $this->basic_data;
        $content .= "
			<soap:Body>
				<CreateCustomer xmlns=\"https://www.eway.com.au/gateway/managedpayment\">";
        $content .= $this->build_xml($data);
        $content .= "
				</CreateCustomer>
			</soap:Body>
		";
        // Create it
        $this->current_action = 'CreateCustomer';
        $call                 = $this->call_gateway($content, array('CreateCustomerResult'));
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
        $this->construct_basics();
        // Data
        $data                      = array();
        $data['managedCustomerID'] = $this->token;
        $data['Title']             = 'Mr.';
        $data['FirstName']         = $this->billing['first_name'];
        $data['LastName']          = $this->billing['last_name'];
        $data['Address']           = $this->billing['address_line_1'];
        if (!empty($this->billing['address_line_2'])) {
            $data['Address'] .= ' ' . $this->billing['address_line_2'];
        }
        $data['Suburb']   = $this->billing['city'];
        $data['State']    = $this->billing['state'];
        $data['PostCode'] = $this->billing['zip'];
        // Country must be valid two character country code.
        $data['Country']     = strtolower($this->billing['country']);
        $data['Phone']       = $this->billing['phone'];
        $data['Email']       = $this->billing['email'];
        $data['CustomerRef'] = $this->billing['username'];
        //$data['Company'] = '';
        //$data['fax'] = '';
        //$data['JobDesc'] = '';
        //$data['Comments'] = '';
        //$data['URL'] = '';
        // Credit Card
        $data['CCNameOnCard']  = $this->billing['first_name'] . ' ' . $this->billing['last_name'];
        $data['CCNumber']      = $this->billing['cc_number'];
        $data['CCExpiryYear']  = $this->billing['card_exp_yy'];
        $data['CCExpiryMonth'] = $this->billing['card_exp_mm'];
        $data['CVN']           = $this->billing['cvv'];
        // Basic XML
        $content = $this->basic_data;
        $content .= "
			<soap:Body>
				<UpdateCustomer xmlns=\"https://www.eway.com.au/gateway/managedpayment\">";
        $content .= $this->build_xml($data);
        $content .= "
				</UpdateCustomer>
			</soap:Body>
		";
        $this->current_action = 'UpdateCustomer';
        $call                 = $this->call_gateway($content);

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
        //$this->construct_basics();
        //$call = $this->call_gateway();
        //return $call;
    }

    /**
     * Charge a credit card.
     */
    function charge()
    {
        $this->construct_basics();
        // Data
        $data                       = array();
        $data['managedCustomerID']  = $this->token;
        $data['amount']             = $this->amount;
        $data['invoiceReference']   = $this->use_order_id;
        $data['invoiceDescription'] = 'Zenbership order';
        // Basic XML
        $content = $this->basic_data;
        $content .= "
			<soap:Body>
				<ProcessPayment xmlns=\"https://www.eway.com.au/gateway/managedpayment\">";
        $content .= $this->build_xml($data);
        $content .= "
				</ProcessPayment>
			</soap:Body>
		";
        $this->current_action = 'ProcessPayment';
        $reply                = $this->call_gateway($content);

        return $reply;
    }

    /**
     * eCheck
     */
    function echeck()
    {
        //$this->construct_basics();
        //$reply = $this->call_gateway();
        //return $reply;
    }

    /**
     * Refund an order
     */
    function refund()
    {
        // $this->construct_basics();
        $data                 = "
		<ewaygateway>
			<ewayCustomerID>" . $this->token . "</ewayCustomerID>
			<ewayRefundPassword>" . $this->gateway_data['credential4'] . "</ewayRefundPassword>
			<ewayTotalAmount>" . $this->amount . "</ewayTotalAmount>
			<ewayOriginalTrxnNumber>" . $this->use_order_id . "</ewayOriginalTrxnNumber>
			<ewayCardExpiryMonth>>" . $this->billing['card_exp_mm'] . "</ewayCardExpiryMonth>
			<ewayCardExpiryYear>" . $this->billing['card_exp_yy'] . "></ewayCardExpiryYear>
		</ewaygateway>
		";
        $this->current_action = 'refund';
        $reply                = $this->call_gateway($data);

        return $reply;
    }

    /**
     * Call a gateway
     */
    function call_gateway($send_data, $more = '')
    {
        if ($this->current_action == 'refund') {
            $url        = 'https://www.eway.com.au/gateway/xmlpaymentrefund.asp';
            $final_data = $send_data;
        } else {
            if ($this->gateway_data['test_mode'] == '1') {
                $url = 'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedCreditCardPayment.asmx';
            } else {
                $url = 'https://www.eway.com.au/gateway/ManagedPaymentService/managedCreditCardPayment.asmx';
            }
            $final_data = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
            $final_data .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">";
            $final_data .= $send_data;
            $final_data .= "</soap:Envelope>";
            $headers     = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: \"https://www.eway.com.au/gateway/managedpayment/" . $this->current_action . "\"",
                "Content-length: " . strlen($final_data),
            );
            $credentials = $this->gateway_data['credential2'] . ':' . $this->gateway_data['credential3'];
        }
        //echo "<textarea rows=20 cols=100>$final_data</textarea>";
        $credentials = $this->gateway_data['credential2'] . ':' . $this->gateway_data['credential3'];

        //echo "SENDING:";
        //echo "<textarea rows=20 cols=100>$final_data</textarea>";

        // Make the cURL call
        $headers = '';
        $call = $this->curl_call($url, $final_data, '1', $credentials, '', $headers);
        // Handle reply
        $reply = $this->handle_reply($call, $more);

        return $reply;
    }

    /**
     * Build XML
     */
    function build_xml($data)
    {
        $string = '';
        foreach ($data as $name => $value) {
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

        $status = $this->split_xml('ewayTrxnStatus', $reply_data);
        $fault = $this->split_xml('faultstring', $reply_data);

        if (empty($status) || $status == 'True') {
            $status = 'True';
        } else {
            $status = 'False';
        }
        $fid        = $this->split_xml('ewayTrxnNumber', $reply_data);
        $error_dets = $this->split_xml('ewayTrxnError', $reply_data);
        $managedCustomerID = $this->split_xml('managedCustomerID', $reply_data);

        // This is for XML errors mostly.
        if (! empty($fault)) {
            $status = 'False';
            $error_dets = $this->split_xml('faultstring', $reply_data);
        }

        //echo "<hr>REPLY:";
        //echo "$status / $fid / $error_dets";
        //echo "<textarea rows=20 cols=100>$reply_data</textarea>";
        //echo "<HR><HR><HR>";

        $return                 = array();
        $return['id']           = $this->gateway_name;
        $return['msg']          = $error_dets;
        $return['resp_code']    = '';
        $return['zen_order_id'] = $this->use_order_id;
        $return['order_id']     = $fid;
        $return['fee']          = '';

        // Customer Token
        if (empty($this->token) && ! empty($managedCustomerID)) {
            $this->token = $managedCustomerID;
            $return['gateway_id_1'] = $managedCustomerID;
        } else {
            $return['gateway_id_1'] = $this->token;
        }

        // More
        if (!empty($more)) {
            foreach ($more as $item) {
                $return[$item] = $this->split_xml($item, $reply_data);
            }
        }
        if ($status == "True") {
            $return['error'] = '0';
        } else {
            $return['error'] = '1';
        }

        return $return;
    }

}
