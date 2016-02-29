<?php

/**
 * PayPal Integration
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
class gw_paypal extends cart
{

    var $gateway_name = 'gw_paypal';
    var $data;
    var $order;

    /**
     * Required basics
     */
    function __construct()
    {
        // Get gateway
        $q1         = $this->get_gateways('', $this->gateway_name);
        $this->data = $q1['0'];
        // Order
        $this->order = $this->get_order();
    }

    /**
     * Prepare for checkout
     */
    function checkout()
    {
        /*

                  // Format link
                $format_checkout_link = "https://www.paypal.com/cgi-bin/webscr?

                cmd=_xclick-subscriptions&business=" . urlencode($options['paypal_email']) . "&currency_code=" . urlencode($options['currency']) . "&shipping=$cart_shipping&no_shipping=1&item_number=" . urlencode($_COOKIE['ppSD_cart']) . "&a3=" . urlencode($final_price) . "&p3=$r_num&t3=$r_type&src=1&sra=1&image_url=https://www.jejouedupiano.com/images/logo-iptp-ogone.jpg&notify_url=" . urlencode($notify_url) . "&return=" . urlencode($return_url) . "&item_name=" . urlencode($final_name);
        */
        $format_checkout_link = $this->get_url();
        $format_checkout_link .= "?business=" . urlencode($this->data['credential1']);
        $format_checkout_link .= "&" . "item_name=" . urlencode($this->cart_name());
        $format_checkout_link .= "&" . "invoice=" . urlencode($_COOKIE['zen_cart']);
        $format_checkout_link .= "&" . "no_shipping=1";
        $format_checkout_link .= "&" . "no_note=1";
        $format_checkout_link .= "&" . "return=" . urlencode($this->return_url());
        $format_checkout_link .= "&" . "notify_url=" . urlencode($this->notify_url());
        $format_checkout_link .= "&" . "cbt=" . urlencode($this->get_option('paypal_return_text'));
        $format_checkout_link .= "&" . "custom=" . urlencode($this->order['data']['salt']);
        // Subscription?
        // Note that you can only send 1
        // subscription to PayPal at
        // at time.
        $sub_class    = new subscription;
        $subscription = $sub_class->find_subscription();
        if (!empty($subscription['id'])) {
            $format_checkout_link .= "&" . "cmd=_xclick-subscriptions";
            $paypal_subscription = $this->format_subscription($subscription);
            foreach ($paypal_subscription as $name => $value) {
                if (!empty($value)) {
                    $format_checkout_link .= "&" . "" . urlencode($name) . "=" . urlencode($value);
                }
            }
            $format_checkout_link .= "&" . "src=1";
            if (!empty($subscription['renew_max'])) {
                $format_checkout_link .= "&" . "srt=" . urlencode($subscription['renew_max']);
            }
        } else {
            $format_checkout_link .= "&" . "cmd=_xclick";
            $format_checkout_link .= "&" . "amount=" . urlencode($this->order['pricing']['subtotal']);
            $format_checkout_link .= "&" . "tax=" . urlencode($this->order['pricing']['tax']);
            $format_checkout_link .= "&" . "shipping=" . urlencode($this->order['pricing']['shipping']);
            $format_checkout_link .= "&" . "discount_amount=" . urlencode($this->order['pricing']['savings']);
            $format_checkout_link .= "&" . "currency_code=" . urlencode($this->get_option('currency'));
        }

        // echo $format_checkout_link;
        return $format_checkout_link;

    }

    /**
     * Format a subscription
     */
    function format_subscription($data)
    {
        $return = array();
        // Trial
        if ($data['type'] == '3') {
            $time_format  = format_timeframe($data['trial_period']);
            $return['a1'] = $data['trial_price'];
            $return['p1'] = $time_format['unit'];
            $return['t1'] = $time_format['unit_letter'];
        } else {
            $return['a1'] = '';
            $return['p1'] = '';
            $return['t1'] = '';
        }
        $time_format  = format_timeframe($data['renew_timeframe']);
        $return['a3'] = $data['price'];
        $return['p3'] = $time_format['unit'];
        $return['t3'] = $time_format['unit_letter'];

        return $return;
    }

    /**
     * Test mode?
     */
    function get_url()
    {
        if ($this->data['test_mode'] == '1') {
            return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            return 'https://www.paypal.com/cgi-bin/webscr';
        }
    }

    /**
     * Notify URL
     */
    function notify_url()
    {
        return PP_URL . '/pp-cart/gateways/paypal_ipn_listener.php';
    }

}
