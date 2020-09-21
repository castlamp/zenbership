<?php


/**
 *
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
class cart extends db
{

    public $id;
    public $order;
    public $shipping;
    public $billing;
    public $method;
    public $subtotal;
    public $state;
    public $country;
    public $zip;


    function __construct($force_session = '', $active = '1', $force_update_fields = '')
    {

        if (!empty($force_session)) {
            $order_id = $this->route_session($force_session);
            // Set session ID
            $this->id = $order_id;
            // Get order
            $this->order = $this->get_order($order_id, $active);
        } else {
            $order_id = $this->check_session();
            if (!empty($order_id)) {
                $this->id = $order_id;
                // Forced update?
                // Used for checkout process
                // state, country, shipping
                if (!empty($force_update_fields)) {
                    $this->update_order($order_id, $force_update_fields);
                }
                $this->add_tracking();
                // Get the order
                $this->order = $this->get_order($order_id, $active);
            }
        }
    }


    public function getId()
    {
        return $this->id;
    }

    /**
     * Adds tracking to the cart session for better
     * promotions and better turn arounds.
     */
    function add_tracking()
    {

        if (!strpos($_SERVER['SCRIPT_NAME'], 'admin')) {
            $q1 = $this->insert("
            INSERT INTO `ppSD_cart_tracking` (`cart_session`,`page`,`query`,`date`)
            VALUES (
                '" . $this->mysql_clean($this->id) . "',
                '" . $this->mysql_clean($_SERVER['SCRIPT_NAME']) . "',
                '" . $this->mysql_clean($_SERVER['QUERY_STRING']) . "',
                '" . current_date() . "'
            )
        ");
        }
    }


    public function updateOrderUserType($id, $userType)
    {
        return $this->run_query("
            UPDATE ppSD_cart_sessions
            SET `member_type`='" . $this->mysql_clean($userType) . "'
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }


    /**
     * Gets last activity on a cart session
     */
    function get_last_activity($id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_cart_tracking`
            WHERE `cart_session`='" . $this->mysql_clean($id) . "'
            ORDER BY `date` DESC
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            return array(
                'error' => '0',
                'page' => $q1['page'],
                'query' => $q1['query'],
                'format_page' => PP_URL . $q1['page'] . '?' . $q1['query'],
                'date' => $q1['date'],
                'format_date' => format_date($q1['date'])
            );
        } else {
            return array('error' => '1', 'error_details' => 'No activity.');
        }
    }


    /**
     * Refresh Cart
     */
    function refresh($force_session, $active)
    {

        if (!empty($force_session)) {
            $order_id = $force_session;
            // Set session ID
            $this->id = $order_id;
            // Get order
            $this->order = $this->get_order($order_id, $active);
        } else {
            $order_id = $this->check_session();
            if (!empty($order_id)) {
                $this->order = $this->get_order($order_id, $active);
                $this->id = $order_id;
            }
        }
    }


    /**
     * Set some stuff.
     */
    function set_id($id)
    {
        $this->id = $id;
    }


    function set_billing($data)
    {
        $this->billing = $data;
    }


    function set_shipping($data)
    {
        $this->shipping = $data;
    }


    function set_method($data)
    {
        $this->method = $data;
    }


    /**
     * Generate a cart ID
     */
    function generate_cart_id()
    {
        $format = $this->get_option('order_id_format');
        if (!empty($format)) {
            $final_id = generate_id($format, '14');
        } else {
            $final_id = generate_id('nnnn-nnnnn-nnnn', '14');
        }
        return $final_id;
    }


    function get_package($package_id)
    {

        $q1 = $this->get_array("
            SELECT
                *
            FROM
                `ppSD_packages`
            WHERE
                `id`='" . $this->mysql_clean($package_id) . "'
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            $q1['error'] = '0';
            $q1['items'] = $this->get_package_items($package_id);
        } else {
            $q1['error'] = '1';
            $q1['items'] = array();
        }
        return $q1;
    }


    function check_item_in_package($package_id, $product_id)
    {

        $checking = $this->get_array("
            SELECT
                COUNT(*)
            FROM
                `ppSD_products_linked`
            WHERE
                `package_id`='" . $this->mysql_clean($package_id) . "' AND
                `product_id`='" . $this->mysql_clean($product_id) . "'
        ");
        return $checking['0'];
    }


    function find_item_package($product_id)
    {

        $checking = $this->get_array("
            SELECT
                `package_id`
            FROM
                `ppSD_products_linked`
            WHERE
                `product_id`='" . $this->mysql_clean($product_id) . "'
            LIMIT 1
        ");
        if (!empty($checking['package_id'])) {
            $check = $this->get_package($checking['package_id']);
        } else {
            $check = array();
        }
        return $check;
    }


    function get_package_items($package_id)
    {

        $q1 = $this->run_query("
            SELECT
                ppSD_products_linked.id,
                ppSD_products.name,
                ppSD_products.price,
                ppSD_products.renew_timeframe,
                ppSD_products.id AS Product_Id
            FROM
                `ppSD_products_linked`
            JOIN
                `ppSD_products`
            ON
                ppSD_products.id=ppSD_products_linked.product_id
            WHERE
                ppSD_products_linked.package_id='" . $this->mysql_clean($package_id) . "'
            ORDER BY
                ppSD_products.price ASC
        ");
        $items = array();
        while ($row = $q1->fetch()) {
            $row['format_price'] = place_currency($row['price']);
            $items[] = $row;
        }
        return $items;
    }


    /**
     * Check if the user can see the
     * current location cart location.
     * 0: none
     * 1: view_catalog
     * 2: view_cart
     * 3: checkout
     *
     * @param $level Int    Where the user currently is.
     */
    function check_permission($level = '0')
    {
        // Check scope
        $cart_scope = $this->get_option('cart_login_req');
        if ( $level >= $cart_scope && $cart_scope != '0' ) {
            // Check a user's session
            $session = new session;
            $ses = $session->check_session();
            if ($ses['error'] == '1') {
                // Render the template
                $changes = array(
                    'title' => $this->get_error('S017'),
                    'details' => $this->get_error('S020')
                );
                $template = new template('error', $changes, '1');
                echo $template;
                exit;
            }
        }
    }


    /**
     * Database a sale.
     * This simulates the add to cart/
     * checkout experience without
     * having to do that stuff. Used
     * for subscriptions and
     * adding sales from the admin CP.
     */
    function database_sale($components, $status = '1', $member_id = '', $gateway = '', $id = '', $card_id = '', $state = '', $country = '')
    {

        if (empty($id)) {
            $id = $this->generate_cart_id();
        }

        // For rejected transactions.
        if (empty($gateway['order_id'])) {
            $gateway['order_id'] = '';
        }

        if (empty($gateway['fee'])) {
            $gateway['fee'] = '0.00';
        }

        $gen_salt = generate_id('random', '25');
        $date = current_date();
        $q1 = $this->insert("
            INSERT INTO `ppSD_cart_sessions` (`id`,`date`,`last_activity`,`date_completed`,`status`,`member_id`,`payment_gateway`,`gateway_order_id`,`gateway_resp_code`,`gateway_msg`,`salt`,`card_id`,`state`,`country`)
            VALUES ('" . $this->mysql_clean($id) . "','" . $date . "','" . $date . "','" . $date . "','" . $this->mysql_clean($status) . "','" . $this->mysql_clean($member_id) . "','" . $this->mysql_clean($gateway['id']) . "','" . $this->mysql_clean($gateway['order_id']) . "','" . $this->mysql_clean($gateway['resp_code']) . "','" . $this->mysql_clean($gateway['msg']) . "','" . $this->mysql_clean($gen_salt) . "','" . $card_id . "','','')
        ");

        $full_total = 0;
        foreach ($components as $anItem) {
            if (empty($anItem['qty'])) {
                $anItem['qty'] = 1;
            }
            $q2 = $this->insert("
                INSERT INTO `ppSD_cart_items_complete` (`cart_session`,`product_id`,`qty`,`unit_price`,`subscription_id`,`status`,`date`)
                VALUES ('" . $this->mysql_clean($id) . "','" . $this->mysql_clean($anItem['product_id']) . "','" . $this->mysql_clean($anItem['qty']) . "','" . $this->mysql_clean($anItem['price']) . "','" . $this->mysql_clean($anItem['subscription_id']) . "','" . $this->mysql_clean($status) . "','" . $date . "')
            ");
            $full_total += $anItem['price'] * $anItem['qty'];
            $put = 'product_sale-' . $anItem['product_id'];
            $this->put_stats($put, $anItem['qty']);
            $put = 'product_income-' . $anItem['product_id'];
            $this->put_stats($put, $full_total);
        }

        $q3 = $this->insert("
            INSERT INTO `ppSD_cart_session_totals` (`id`,`total`,`gateway_fees`,`subtotal`,`subtotal_nosave`,`shipping`,`tax`,`savings`)
            VALUES ('" . $this->mysql_clean($id) . "','$full_total','" . $gateway['fee'] . "','$full_total','$full_total','','','')
        ");

        return $id;

    }



    function setMember($member_id, $force_session = '')
    {
        $final_id = (empty($force_session)) ? $this->id : $force_session;

        $this->run_query("
            UPDATE ppSD_cart_sessions
            SET member_id='" . $this->mysql_clean($member_id) . "'
            WHERE id='" . $this->mysql_clean($final_id) . "'
            LIMIT 1
        ");

        $this->order = $this->get_order($final_id, '1');
    }

    /**
     * Start cart session (x)
     * $skip_cookie -> Only '1' if we are only
     * databasing a new order
     */
    function start_session($member_id = '', $skip_cookie = '0', $force_id = '')
    {

        if (empty($force_id)) {
            $final_id = $this->generate_cart_id();
        } else {
            $final_id = $force_id;
        }
        // Force a member ID?
        if (!empty($member_id)) {
            $use_member = $member_id;
        } else {
            $session = new session;
            $ses = $session->check_session();
            $use_member = $ses['member_id'];
        }
        if (!empty($use_member)) {
            $user = new user;
            $data = $user->get_user($use_member, '', '0');
            if (!empty($data['data']['state'])) {
                $state = $data['data']['state'];
            } else {
                $state = '';
            }
            if (!empty($data['data']['country'])) {
                $country = $data['data']['country'];
            } else {
                $country = '';
            }
        } else {
            $state = '';
            $country = '';
        }
        // Create entry
        $gen_salt = generate_id('random', '25');
        $q = $this->insert("
            INSERT INTO `ppSD_cart_sessions` (`id`,`date`,`last_activity`,`status`,`member_id`,`ip`,`state`,`country`,`salt`)
            VALUES ('$final_id','" . current_date() . "','" . current_date() . "','0','" . $this->mysql_clean($use_member) . "','" . $this->mysql_clean(get_ip()) . "','" . $this->mysql_clean($state) . "','" . $this->mysql_clean($country) . "','$gen_salt')
        ");
        $this->id = $final_id;
        // Cookie?
        if ($skip_cookie != '1') {
            $this->create_cookie('zen_cart', $final_id, '31536000');
        }
        $this->add_tracking();
        return $final_id;
    }


    /**
     * check cart session (x)
     */
    function check_session()
    {
        if (empty($_COOKIE['zen_cart'])) {
            $this->start_session();
        } else {
            $q1 = $this->get_array("
                SELECT `id`
                FROM `ppSD_cart_sessions`
                WHERE `id`='" . $this->mysql_clean($_COOKIE['zen_cart']) . "'
            ");
            if (!empty($q1['id'])) {
                $this->id = $q1['id'];
                return $q1['id'];
            } else {
                $this->delete_cookie('zen_cart');
                return '0';
            }
        }
    }


    /**
     * check cart session (x)
     */
    function update_return($force_session, $path)
    {

        // Route the session
        if (!empty($path)) {
            $session = $this->route_session($force_session, '1');
            $q = $this->run_query("
                UPDATE `ppSD_cart_sessions`
                SET `return_path`='" . $this->mysql_clean($path) . "'
                WHERE `id`='" . $this->mysql_clean($session) . "'
                LIMIT 1
            ");
        }
    }


    /**
     * Checkout
     */
    function checkout()
    {

        header('Location: ' . PP_URL . '/pp-cart/checkout.php');
        exit;
    }


    /**
     * Update the gateway
     * being used.
     */
    function update_order_gateway($id)
    {
        $q = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `payment_gateway`='" . $this->mysql_clean($id) . "'
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
    }


    /**
     * Get gateways
     */
    function get_gateways($primary = '0', $id = '', $apis = '0')
    {

        $where = '';
        if ($primary == '1') {
            $where .= " AND `primary`='1'";
        }
        if (!empty($id)) {
            $where .= " AND `code`='" . $this->mysql_clean($id) . "'";
        }
        if ($apis == '1') {
            $where .= " AND `api`='1'";
        }
        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_payment_gateways`
            WHERE `active`='1' $where
            ORDER BY `primary` DESC
        ");
        $gateways = array();
        while ($row = $STH->fetch()) {
            $row['credential1'] = trim($row['credential1']);
            $row['credential2'] = trim($row['credential2']);
            $row['credential3'] = trim($row['credential3']);
            $gateways[] = $row;
        }
        return $gateways;
    }


    /**
     * Get payment methods
     */
    function get_methods()
    {

        $methods = array();
        $methods['visa'] = '0';
        $methods['mastercard'] = '0';
        $methods['discover'] = '0';
        $methods['amex'] = '0';
        $methods['echeck'] = '0';
        $methods['paypal'] = '0';
        $methods['paypal_express'] = '0';
        if (!empty($_COOKIE['zen_invoice'])) {
            $methods['invoice'] = '0';
        } else {
            $methods['invoice'] = $this->get_option('allow_invoicing');
        }
        $gateways = $this->get_gateways();
        foreach ($gateways as $aGateway) {
            if ($aGateway['api'] == '1') {
                if ($aGateway['method_cc_visa'] == '1' && empty($methods['visa'])) {
                    $methods['visa'] = $aGateway['code'];
                }
                if ($aGateway['method_cc_amex'] == '1' && empty($methods['amex'])) {
                    $methods['amex'] = $aGateway['code'];
                }
                if ($aGateway['method_cc_mc'] == '1' && empty($methods['mastercard'])) {
                    $methods['mastercard'] = $aGateway['code'];
                }
                if ($aGateway['method_cc_discover'] == '1' && empty($methods['discover'])) {
                    $methods['discover'] = $aGateway['code'];
                }
                if ($aGateway['method_check'] == '1' && empty($methods['echeck'])) {
                    $methods['echeck'] = $aGateway['code'];
                }
            } else {
                if ($aGateway['code'] == 'gw_paypal') {
                    $methods['paypal'] = 'gw_paypal';
                } else if ($aGateway['code'] == 'gw_paypal_express') {
                    $methods['paypal_express'] = 'gw_paypal_express';
                } else {
                    $methods[$aGateway['name']] = $aGateway['code'];
                }
            }
        }
        return $methods;
    }


    /**
     * Get a list of all payment methods
     */
    function organize_gateways()
    {

        $imgs = '';
        $cc = '';
        $visa = '0';
        $mastercard = '0';
        $discover = '0';
        $amex = '0';
        $do_cc = '0';
        $do_check = '0';
        $do_invoice = '0';
        $do_paypal = '0';
        $all_methods = '';
        $methods = $this->get_methods();
        // Get Theme!
        $theme = $this->get_theme();
        $total = 0;
        foreach ($methods as $name => $gateway) {
            if ($name == 'visa' && !empty($gateway)) {
                $total++;
                $do_cc = '1';
                $visa = '1';
                $cc .= '<img src="' . $theme['url'] . '/imgs/icon-visa.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($name == 'mastercard' && !empty($gateway)) {
                $total++;
                $do_cc = '1';
                $mastercard = '1';
                $cc .= '<img src="' . $theme['url'] . '/imgs/icon-mastercard.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($name == 'discover' && !empty($gateway)) {
                $total++;
                $do_cc = '1';
                $discover = '1';
                $cc .= '<img src="' . $theme['url'] . '/imgs/icon-discover.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($name == 'amex' && !empty($gateway)) {
                $total++;
                $do_cc = '1';
                $amex = '1';
                $cc .= '<img src="' . $theme['url'] . '/imgs/icon-amex.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($name == 'echeck' && !empty($gateway)) {
                $total++;
                $do_check = '1';
                $all_methods .= '<li><input type="radio" name="method" class="method" value="eCheck" onclick="return deactivate_billing(\'0\');" /> eCheck</li>';
            } else if ($name == 'paypal' && !empty($gateway)) {
                $total++;
                $do_paypal = '1';
                $all_methods .= '<li><input type="radio" name="method" class="method" value="paypal" onclick="return deactivate_billing(\'0\');" /> PayPal</li>';
            } else if ($name == 'invoice' && !empty($gateway)) {
                $total++;
                $do_invoice = '1';
                $all_methods .= '<li><input type="radio" name="method" class="method" value="invoice" onclick="return deactivate_billing(\'0\');" /> Request Invoice</li>';
            } else {
                if (!empty($gateway)) {
                    $all_methods .= '<li><input type="radio" name="method" class="method" value="' . $gateway . '" onclick="return deactivate_billing(\'0\');" /> ' . $name . '</li>';
                }
            }
        }
        if ($total == 1) {
            $all_methods = str_replace('value="invoice"', 'value="invoice" checked="checked"', $all_methods);
        }
        if (!empty($cc)) {
            $all_methods = '<li><input type="radio" name="method" class="method" value="cc" checked="checked" onclick="return deactivate_billing(\'0\');" /> Credit Card</li>' . $all_methods;
        }
        $data = array();
        $data['visa'] = $visa;
        $data['mastercard'] = $mastercard;
        $data['discover'] = $discover;
        $data['amex'] = $amex;
        $data['paypal'] = $do_paypal;
        $data['invoice'] = $do_invoice;
        $data['do_cc'] = $do_cc;
        $data['do_check'] = $do_check;
        $data['cc_imgs'] = $cc;
        $data['method_list'] = $all_methods;
        return $data;
    }


    /**
     * Determine the best gateway to use
     * Set billing through $this->set_billing($_POST['billing'])
     */
    function best_gateway()
    {
        // Credit card?
        if (!empty($this->billing['cc_number'])) {
            $type = get_cc_type($this->billing['cc_number']);
            $method = 'cc';
            if ($type['0'] == 'Visa') {
                $method_name = "`method_cc_visa`='1'";
            } else if ($type['0'] == 'Mastercard') {
                $method_name = "`method_cc_mc`='1'";
            } else if ($type['0'] == 'Amex') {
                $method_name = "`method_cc_amex`='1'";
            } else if ($type['0'] == 'Discover') {
                $method_name = "`method_cc_discover`='1'";
            } else {
                $method_name = " `primary`='1'";
            }
        } // eCheck?
        else if (!empty($this->billing['bank_account_number'])) {
            $method = "`method_check`='1'";
        } // Invoice?
        else {
            return '0';
        }
        $q1 = $this->get_array("
            SELECT `code`
            FROM `ppSD_payment_gateways`
            WHERE $method_name AND `active`='1'
            ORDER BY `primary` DESC, `fee_percent` ASC
            LIMIT 1
        ");
        return $q1['code'];
    }


    /**
     * Determine what session ID to use
     * when working with a cart.
     */
    function route_session($force_session = '', $add_session = '0', $force_member_id = '')
    {

        // Make sure there is a session
        if (!empty($force_session)) {
            $session = $force_session;
        } else {
            $session = $this->check_session();
            if (empty($session) && $add_session == '1') {
                $session = $this->start_session($force_member_id);
            }
        }
        return $session;
    }


    /**
     * Add a product to cart. (x)
     * $options -> reflects product options as
     * established in ppSD_products_options
     * $force_single -> Makes sure qty is
     * always '1' for the product being added.
     */
    function add($product, $qty = '1', $options = '', $member_id = '', $savings_code = '', $force_session = '', $type = '', $force_single = '0', $force_add = '0')
    {

        // Route the session
        $session = $this->route_session($force_session, '1', $member_id);
        // Quantity
        if ($qty <= 0 || empty($qty)) {
            $qty = '1';
        }
        // Additional checks
        if (empty($force_add)) {
            $check = $this->check_addition($product, $qty, $options);
        } else {
            $check = array();
            $check['error'] = '0';
        }
        if ($check['error'] == '1') {
            return $check;
        } else {

            // Start Task
            $indata = array(
                'product' => $product,
                'qty' => $qty,
                'cart_id' => $session,
            );
            $task_id = $this->start_task('cart_add', 'user', '', $member_id, '', $indata);

            // Exists in cart?
            $in_cart = $this->find_product_in_cart($product, $options, $session);

            if ($in_cart['found'] == '1') {
                // Format option where
                $option_where = $this->format_option_where($options, 'where');
                // Proceed
                if ($force_single == '1') {
                    $new_qty = '1';
                } else {
                    $new_qty = $in_cart['qty'] + $qty;
                }
                $qa = $this->update("
                    UPDATE `ppSD_cart_items`
                    SET `qty`='" . $this->mysql_clean($new_qty) . "'
                    WHERE $option_where`cart_session`='" . $session . "' AND `product_id`='" . $this->mysql_clean($product) . "'
                    LIMIT 1
                ");
            } else {
                // Format option where
                $option_where = $this->format_option_where($options, 'insert');
                // Add it
                $qa = $this->insert("
                    INSERT INTO `ppSD_cart_items` (" . $option_where['0'] . "`cart_session`,`product_id`,`qty`,`date`)
                    VALUES (" . $option_where['1'] . "'" . $this->mysql_clean($session) . "','" . $this->mysql_clean($product) . "','" . $this->mysql_clean($qty) . "','" . current_date() . "')
                ");
            }
            $put = 'added_to_cart';
            $this->put_stats($put);

            // Savings code
            if (!empty($savings_code)) {

            }

            $task    = $this->end_task($task_id, '1', '', 'cart_add', '', $indata);

            return array('error' => '0', 'error_details' => '', 'code' => '', 'session' => $session);
        }
    }


    /**
     * Check if a "add to cart" request
     * can be processed
     */
    function check_addition($id, $qty, $options = '', $force_qty = '0')
    {
        if (!empty($id)) {
            $product = $this->get_product($id);
            // Member's Only
            if ($product['data']['hide'] == '1') {
                $dets = $this->get_error('S067');
                return array(
                    'error' => '1',
                    'error_details' => $dets,
                    'code' => 'S067',
                    'changes' => array(),
                );
            }
            // Member's Only
            if ($product['data']['members_only'] == '1' && empty($this->order['data']['reg_session'])) {
                $ses = new session();
                $session = $ses->check_session();
                if ($session['error'] == '1' && empty($this->order['member_id'])) {
                    $dets = $this->get_error('S051');
                    return array(
                        'error' => '1',
                        'error_details' => $dets,
                        'code' => 'S051',
                        'changes' => array(),
                    );
                }
            }
            // Stock
            if ($product['data']['physical'] == '1') {
                $use_stock = '0';
                $opt_qty = $this->check_stock($id, $options);
                if (!empty($opt_qty['qty'])) {
                    $use_stock = $opt_qty['qty'];
                } else {
                    $use_stock = 99999;
                }
                // Proceed
                $new_stock = $use_stock - $qty;
                if ($new_stock < 0) {
                    $dets = $this->get_error('S001');
                    return array(
                        'error' => '1',
                        'error_details ' => $dets,
                        'code' => 'S001',
                        'changes' => array(),
                    );
                }
            }
            // Allow multiple
            if ($product['data']['max_per_cart'] != '0') {
                $in_cart = $this->find_product_in_cart($id, $options);
                if ($force_qty == '1') {
                    $combine = $qty;
                } else {
                    $combine = $qty + $in_cart['qty'];
                }
                if ($combine > $product['data']['max_per_cart']) {
                    $dets = $this->get_error('S002');
                    $dets = str_replace('%number%', $product['data']['max_per_cart'], $dets);
                    return array(
                        'error' => '1',
                        'error_details' => $dets,
                        'code' => 'S002',
                        'changes' => array(
                            'number' => $product['data']['max_per_cart'],
                        ),
                    );
                }
            }
            // Require minimum?
            if ($product['data']['min_per_cart'] != '0') {
                $in_cart = $this->find_product_in_cart($id, $options);
                if ($force_qty == '1') {
                    $combine = $qty;
                } else {
                    $combine = $qty + $in_cart['qty'];
                }
                if ($combine < $product['data']['min_per_cart']) {
                    $dets = $this->get_error('S066');
                    $dets = str_replace('%minimum%', $product['data']['min_per_cart'], $dets);
                    return array(
                        'error' => '1',
                        'error_details' => $dets,
                        'code' => 'S066',
                        'changes' => array(
                            'minimum' =>  $product['data']['min_per_cart'],
                        ),
                    );
                }
            }
            return array('error' => '0', 'error_details' => '');
        }
    }


    /**
     * Get a product's name from the
     * product's ID.
     * @param $id Product ID.
     * @return string
     */
    function get_product_name($id)
    {
        $q13 = $this->get_array("
            SELECT `name`
            FROM `ppSD_products`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q13['name'])) {
            return $q13['name'];
        } else {
            return '';
        }
    }


    /**
     * Check option stat
     */
    function check_stock($id, $options = '')
    {

        $option_where = $this->format_option_where($options);
        $q = $this->get_array("
            SELECT *
            FROM `ppSD_products_options_qty`
            WHERE $option_where`product_id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q['id'])) {
            return $q;
        } else {
            return '';
        }
    }


    /**
     * Format option where
     */
    function format_option_where($options, $type = 'where')
    {
        if ($type == 'where') {
            if (!empty($options)) {
                $add_where = '';
                foreach ($options as $name => $value) {
                    $name_use = 'option' . $name;
                    $add_where .= "`" . $this->mysql_cleans($name_use) . "`='" . $this->mysql_cleans($value) . "' AND ";
                }
                return $add_where;
            } else {
                return '';
            }
        }
        else if ($type == 'update') {
            if (!empty($options)) {
                $add_update = '';
                foreach ($options as $name => $value) {
                    $name_use = 'option' . $name;
                    $add_update .= "`" . $this->mysql_cleans($name_use) . "`='" . $this->mysql_cleans($value) . "',";
                }
                return $add_update;
            } else {
                return '';
            }
        }
        else if ($type == 'insert') {
            if (!empty($options)) {
                $i1 = '';
                $i2 = '';
                foreach ($options as $name => $value) {
                    $name_use = 'option' . $name;
                    $i1 .= "`" . $this->mysql_cleans($name_use) . "`,";
                    $i2 .= "'" . $this->mysql_clean($value) . "',";
                }
                return array($i1, $i2);
            } else {
                return array('', '');
            }
        }
    }


    /**
     * Update an item's stock
     */
    function update_stock($product_id, $remove = '1', $options = '')
    {

        if (!empty($options)) {

        } else {
            /*
            $q = $this->update("
                UPDATE `ppSD_products`
                SET `stock`=(stock-" . $this->mysql_clean($remove) . ")
                WHERE `id`='" . $this->mysql_clean($product_id) . "'
                LIMIT 1
            ");
            */
        }
    }


    /**
     * Add a product
     */
    function add_product($data, $associated_id = '')
    {
        // Generate a query
        $ignore = array('created', 'id');
        $primary = array();
        // Pre-insert checks
        if (!empty($associated_id)) {
            $data['associated_id'] = $associated_id;
        }
        $admin = new admin;
        $query_form = $admin->query_from_fields($data, 'add', $ignore, $primary);
        $insert_fields1 = $query_form['if2'];
        $insert_values1 = $query_form['iv2'];
        // Insert product
        if (! empty($data['id'])) {
            $id = $data['id'];
        } else {
            $id = generate_id($this->get_option('product_id_format'));
        }

        if (empty($data['max_per_cart'])) {
            $data['max_per_cart'] = 999999;
        }

        if (empty($data['min_per_cart'])) {
            $data['min_per_cart'] = 1;
        }

        $q1 = $this->insert("
            INSERT INTO `ppSD_products` (`id`,`created`$insert_fields1)
            VALUES ('" . $this->mysql_clean($id) . "','" . current_date() . "'$insert_values1)
        ");
        return $id;
    }


    function edit_product($id, $data)
    {

        $ignore = array('stock');
        $primary = array();
        $admin = new admin;
        $query_form = $admin->query_from_fields($data, 'edit', $ignore, $primary);
        $up = $this->update("
            UPDATE `ppSD_products`
            SET " . ltrim($query_form['u2'], ',') . "
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");


    }


    function order_exists($id)
    {
        $order = $this->get_array("
            SELECT `id`
            FROM ppSD_cart_sessions
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (! empty($order['id'])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Compiles all of the information
     * on an order and returns it
     * as an organized array.
     */
    function get_order($force_session = '', $active_order = '1', $recache = '0')
    {
        // Route the session
        $id = $this->route_session($force_session);
        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $sale = $cache['data'];
        } else {
            if ($active_order == '1') {
                $table = 'ppSD_cart_items';
            } else {
                $table = 'ppSD_cart_items_complete';
            }
            $sale = array();
            $components = array();
            // Basic details
            $sale['data'] = $this->order_basics($id);

            if (empty($sale['data']['id'])) {


            } else {

                $this->state = $sale['data']['state'];
                $this->country = $sale['data']['country'];
                if (! empty($sale['data']['zip'])) {
                    $this->zip = $sale['data']['zip'];
                }
                if ($sale['data']['date_completed'] == '1920-01-01 00:01:01') {
                    $sale['data']['format_date'] = format_date($sale['data']['date']);
                } else {
                    $sale['data']['format_date'] = format_date($sale['data']['date_completed']);
                }

                // Individual Components
                // Comes back as $products['data'] and $products['pricing']
                $get_totals = $this->calculate_order_total($id, $active_order);
                $order_subtotal = $get_totals['subtotal'];
                $savings = $get_totals['savings'];
                $shipping = $get_totals['shipping'];
                $weight = $get_totals['weight'];
                $total_items = $get_totals['total_items'];
                $total_physical_items = $get_totals['total_physical_items'];
                $total_tax = $get_totals['total_tax'];
                $order_total = $get_totals['order_total'];
                $total_products = $get_totals['total_products'];
                /*
                $products = $this->get_products_in_cart($id,$active_order,$sale['data']['state'],$sale['data']['country']);
                foreach ($products as $aProd) {
                    $order_subtotal += $aProd['pricing']['subtotal'];
                    $savings += $aProd['pricing']['savings'];
                    $weight += $aProd['data']['weight'];
                    $total_items += $aProd['pricing']['qty'];
                    if ($aProd['data']['physical'] == '1') {
                        $total_physical_items += $aProd['pricing']['qty'];
                    }
                    $total_tax += $aProd['pricing']['tax'];
                    $order_total += $aProd['pricing']['total'] + $aProd['pricing']['tax'];
                    $total_products++;
                }
                */
                //$sale['total'] = $order_total;
                //$sale['subtotal'] = $order_subtotal;
                //$sale['savings'] = $savings;
                $sale['components'] = $get_totals['products'];
                $sale['data']['weight'] = $weight;
                $sale['data']['total_items'] = $total_items; // Combined QTY
                $sale['data']['total_products'] = $total_products; // Total individual products
                $sale['data']['total_physical_items'] = $total_physical_items; // Total individual products
                if ($sale['data']['status'] == '1') {
                    $sale['data']['show_status'] = $this->get_error('Z001');
                } else if ($sale['data']['status'] == '2') {
                    $sale['data']['show_status'] = $this->get_error('Z002');
                } else if ($sale['data']['status'] == '3') {
                    $sale['data']['show_status'] = $this->get_error('Z003');
                } else if ($sale['data']['status'] == '4') {
                    $sale['data']['show_status'] = $this->get_error('Z004');
                } else if ($sale['data']['status'] == '9') {
                    $er = $this->get_error('Z005');
                    $er = str_replace('%reason%', $sale['data']['gateway_msg'], $er);
                    $sale['data']['show_status'] = $er;
                } else {
                    $sale['data']['show_status'] = 'Unfinished';
                }
                $sale['data']['url'] = PP_URL . '/pp-cart/view_order.php?id=' . $sale['data']['id'] . '&s=' . $sale['data']['salt'];
                if ($this->get_option('use_qcodes') == '1') {
                    $sale['data']['qrcode'] = '<img src="' . PP_URL . '/pp-functions/qrcode.php?url=' . urlencode($sale['data']['url']) . '" border="0" alt="QRCode" title="QRCode" class="zen_qrcode" />';
                } else {
                    $sale['data']['qrcode'] = '';
                }
                // Order
                if (!empty($sale['data']['code'])) {
                    $code = $this->get_savings_code($sale['data']['code']);
                    //if ($code['dollars_off'] > 0) {
                    //    $savings = $code['dollars_off'];
                    //    $order_total -= $savings;
                    //}
                } else {
                    $code = array('type' => '', 'id' => '');
                }
                $sale['code'] = $code;
                // Need shipping?
                if ($active_order == '1') {
                    $phys = $this->get_array("
                        SELECT ppSD_cart_items.id
                        FROM `ppSD_cart_items`
                        JOIN `ppSD_products`
                        ON ppSD_cart_items.product_id=ppSD_products.id
                        WHERE (ppSD_products.physical='1' OR ppSD_products.physical='2') AND ppSD_cart_items.cart_session='" . $this->id . "'
                    ");
                    if (!empty($phys['id'])) {
                        $sale['data']['need_shipping'] = '1';
                    } else {
                        $sale['data']['need_shipping'] = '0';
                    }
                    $sale['billing'] = '';
                } else {
                    $sale['data']['need_shipping'] = '0';
                    // Credit Card
                    $card = $this->get_card($sale['data']['card_id']);

                    if (empty($card['id'])) {
                        if (! empty($sale['data']['member_id'])) {
                            if ($sale['data']['member_type'] == 'member') {
                                $user = new user;
                                $got = $user->get_user($sale['data']['member_id']);
                            } else {
                                $contact = new contact;
                                $got = $contact->get_contact($sale['data']['member_id']);
                            }
                            $sale['billing'] = $got['data'];
                        }
                    } else {
                        $sale['billing'] = $card;
                    }
                }
                /*
                foreach ($products as $row) {
                    if ($row['qty'] == 0) { $row['qty'] = 1; }
                    $order_total += $row['qty'] * $row['amount'];
                    $row['total'] = $order_total;
                    $components[] = $row;
                }
                $q1 = $this->run_query("SELECT * FROM `$table` WHERE `order_id`='" . $this->mysql_clean($id) . "'");
                while ($row = @ $STH->fetch($q1)) {
                    if ($row['qty'] == 0) { $row['qty'] = 1; }
                    $order_total += $row['qty'] * $row['amount'];
                    $row['total'] = $order_total;
                    $components[] = $row;
                }
                */
                // Shipping Information
                $physical = $this->find_physical($id);
                if ($physical > 0) {
                    if ($active_order == '1') {
                        $rule = $this->get_shipping_rule($sale['data']['shipping_rule']);
                        $sale['shipping_info'] = $rule;
                        $shipping += $rule['cost'];
                        $order_total += $rule['cost'];
                    } else {
                        $get_shipping = $this->get_array("
                            SELECT *
                            FROM `ppSD_shipping`
                            WHERE `cart_session`='" . $this->mysql_clean($id) . "'
                            LIMIT 1
                        ");
                        $sale['shipping_info'] = $get_shipping;
                        $sale['shipping_info']['format_address'] = format_address(
                            $get_shipping['address_line_1'],
                            $get_shipping['address_line_2'],
                            $get_shipping['city'],
                            $get_shipping['state'],
                            $get_shipping['zip'],
                            $get_shipping['country']
                        );
                        $sale['data']['need_shipping'] = '1';
                    }
                } else {
                    $sale['shipping_info'] = '';
                }
                // Refunds
                $refund_total = '';
                $refund_components = array();
                $STH = $this->run_query("SELECT * FROM `ppSD_cart_refunds` WHERE `order_id`='" . $this->mysql_clean($id) . "'");
                while ($row = $STH->fetch()) {
                    $row['total'] = $row['total'];
                    $refund_components[] = $row;
                }
                $sale['refunds'] = $refund_components;
                $sale['data']['refunded_money'] = $refund_total;
                // Shipping
                if ($total_physical_items <= 0) {
                    $shipping = 0;
                } else if ($code['type'] == 'shipping') {
                    $shipping = $code['flat_shipping'];
                }
                // Order Total
                $temp_total = $order_subtotal - $savings + $shipping;
                // Tax
                if ($active_order == '1') {
                    $tax_info = array();
                    $sale['tax'] = $total_tax;
                }
                //  $sale['tax_rate'] = $tax_info['tax_rate'];
                // Credit Card
                /*
                if ($get_card == '1') {
                    $card = $this->order_card_info($sale['data']['card_id']);
                    $sale['card'] = $card;
                }
                */
                // Pricing totals
                if ($active_order == '1') {
                    $pricing = array(
                        'total' => $order_total,
                        'subtotal' => $order_subtotal,
                        'subtotal_nosave' => $order_subtotal - $savings,
                        'savings' => $savings,
                        'tax' => $total_tax,
                        'refunds' => '0',
                        'tax_rate' => '',
                        'gateway_fees' => '',
                        'shipping' => $shipping
                    );
                } else {
                    $pricing = $this->get_pricing_totals($id);
                }
                $formatted_prices = $this->format_pricing($pricing);


                $sale['pricing'] = array_merge((array)$pricing, (array)$formatted_prices);

                // Cache if completed.
                if ($active_order != '1') {
                    $cache = $this->add_cache($id, $sale);
                }

            }

        }
        return $sale;
    }


    /**
     * Format a mail tracking link.
     * USPS / FedEx / UPS / OnTrac
     */
    function tracking_link($ship_no, $ship_provider)
    {

        $link = '';
        $title = $this->get_error('S036');
        if ($ship_provider == 'USPS') {
            $link = '<a href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'FedEx') {
            $link = '<a href="http://www.fedex.com/Tracking?action=track&tracknumbers=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'UPS') {
            $link = '<a href="http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'DHL') {
            $link = '<a href="http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'OnTrac') {
            $link = '<a href="http://www.ontrac.com/trackingdetail.asp?tracking=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'LaserShip') {
            $link = '<a href="http://www.lasership.com/track.php?track_number_input=' . urlencode($ship_no) . '">' . $title . '</a>';
        } else if ($ship_provider == 'Canada Post') {
            $link = '<a href="http://www.canadapost.ca/personal">' . $title . '</a>';
        } else if ($ship_provider == 'Australia Post') {
            $link = '<a href="http://auspost.com.au/track/">' . $title . '</a>';
        } else if ($ship_provider == 'New Zealand Post') {
            $link = '<a href="http://www.nzpost.co.nz/tools/tracking">' . $title . '</a>';
        } else if ($ship_provider == 'Royal Mail') {
            $link = '<a href="http://track2.royalmail.com/portal/rm/trackresults">' . $title . '</a>';
        } else {
            $link = $this->get_error('S037');
        }
        return $link;
    }


    /**
     * Format pricing array
     */
    function format_pricing($array)
    {

        $format = array();
        foreach ( (array)$array as $name => $value) {
            if (empty($value)) {
                $value = '0.00';
            }
            if ($name == 'id') {
                continue;
            } else if ($name == 'tax_rate') {
                $format['format_' . $name] = $value . '%';
            } else {
                $format['format_' . $name] = place_currency($value);
            }
        }
        return $format;
    }


    /**
     * Get Shipping
     */
    function get_shipping($id)
    {

        $ship = $this->get_array("
            SELECT * FROM `ppSD_shipping`
            WHERE `cart_session`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (empty($ship)) {
            $ship = array(
                'name' => ''
            );
        }
        return $ship;
    }


    /**
     * Get totals
     */
    function get_pricing_totals($order_id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_cart_session_totals`
            WHERE `id`='" . $this->mysql_clean($order_id) . "'
            LIMIT 1
        ");
        return $q1;
    }


    /**
     * Order basics
     */
    function order_basics($forced_session = '')
    {

        // Route the session
        $id = $this->route_session($forced_session);
        $q = $this->get_array("SELECT * FROM `ppSD_cart_sessions` WHERE `id`='" . $this->mysql_clean($id) . "' LIMIT 1");
        return $q;
    }


    /**
     * Return URL
     */
    function return_url()
    {

        $get_session = $this->order_basics();
        if (!empty($get_session['return_path'])) {
            $basic = PP_URL . '/' . $get_session['return_path'];
        } else {
            $basic = PP_URL . '/pp-cart/return.php?in=' . $this->return_salt() . '&s=' . $this->update_return_code();
        }
        return $basic;
    }

    function return_salt()
    {
        return md5($_COOKIE['zen_cart'] . date('Y-m') . SALT);
    }

    function update_return_code()
    {
        $code = md5(uniqid() . time() . $this->id . SALT);
        $up = $this->update("
            UPDATE
                `ppSD_cart_sessions`
            SET
                `return_code`='" . $code . "',
                `return_time_out`='" . current_date() . "'
            WHERE
                `id`='" . $this->id . "'
            LIMIT 1
        ");
        $this->create_cookie('zen_ret', $code);
        return $code;
    }

    /**
     * Cart name
     */
    function cart_name()
    {

        return COMPANY . ' Shopping Cart';
    }


    /**
     * Get a shipping rule.
     */
    function get_shipping_rule($id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_shipping_rules`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1;
    }


    /**
     * Determine shipping on an order.
     * $basics => ppSD_cart_sessions data
     * $weight => calculated when looping products in cart
     */
    function determine_shipping($weight, $qty, $product, $cart_total)
    {

        $shipping_cost = '0';
        $satisfied = '0';
        $shipping_name = '';
        // NEEDS TO BE:
        //  ACTIVE ORDER: loop shipping rules
        //  Non-active, take info from ppSD_cart_session
        //    and proceed to the ppSD_shipping_rules data.
        $current_shipping = 0;
        $satisfied = '';
        $shipping_cost = 0;
        $shipping_desctipn = '';
        // First determine the generic shipping
        // cost, before special stuff.
        // Now see is a shipping rule applies.
        $STH = $this->run_query("SELECT * FROM `ppSD_shipping_rules` ORDER BY `priority` ASC");
        while ($row = $STH->fetch()) {
            if ($row['type'] == "weight") {
                if (! empty($row['low']) && ! empty($row['high'])) {
                    if ($weight >= $row['low'] && $weight <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (empty($row['low']) && ! empty($row['high'])) {
                    if ($weight <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (! empty($row['low']) && empty($row['high'])) {
                    if ($weight >= $row['low']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
            }
            else if ($row['type'] == "region") {
                if (! empty($row['zip'])) {
                    $zips = explode(',', $row['zip']);
                    if (in_array($this->zip, $zips)) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (! empty($row['state']) && ! empty($row['country'])) {
                    if ($row['state'] == $this->state && $row['country'] == $this->country) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (! empty($row['country']) && empty($row['state'])) {
                    if ($row['country'] == $this->country) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (empty($row['country']) && ! empty($row['state'])) {
                    if ($row['state'] == $this->state) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
            } else if ($row['type'] == "qty") {
                if (! empty($row['low']) && ! empty($row['high'])) {
                    if ($qty >= $row['low'] && $qty <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (empty($row['low']) && ! empty($row['high'])) {
                    if ($qty <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (! empty($row['low']) && empty($row['high'])) {
                    if ($qty >= $row['low']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
            }
            // This doesn't work, we don't know
            // cart total.
            else if ($row['type'] == "total") {
                if (! empty($row['low']) && ! empty($row['high'])) {
                    if ($cart_total >= $row['low'] && $cart_total <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (empty($row['low']) && ! empty($row['high'])) {
                    if ($cart_total <= $row['high']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
                else if (! empty($row['low'])) {
                    if ($cart_total >= $row['low']) {
                        $satisfied = $row['id'];
                        $shipping_cost = $row['cost'];
                        $shipping_desctipn = $row['name'];
                        break;
                    }
                }
            }
            else if ($row['type'] == "product") {
                $found = $this->find_product_in_cart($product);
                if ($found['found'] == 1) {
                    $satisfied = $row['id'];
                    $shipping_cost = $row['cost'];
                    $shipping_desctipn = $row['name'];
                    break;
                }
            }
            else if ($row['type'] == "flat") {
                continue;
            }
        }
        $shipping = array(
            'total' => $shipping_cost,
            'id' => $satisfied,
            'name' => $shipping_name,
        );
        return $shipping;
    }


    /**
     * Determine tax on a product.
     */
    function determine_tax($product_total, $product_physical)
    {

        $scope = $this->get_option('tax_scope');
        if ($scope == 'none') {
            return array('error' => '0', 'tax' => '0.00', 'tax_rate' => '0', 'tbd' => '0');
        } else {
            $total_cart_tax = 0;
            $tax_rate = '0';
            // Proceed?
            $go = '0';
            if ($product_physical >= '1') {
                if ($scope == 'physical' || $scope == 'both') {
                    $go = '1';
                }
            } else {
                if ($scope == 'digital' || $scope == 'both') {
                    $go = '1';
                }
            }
            if ($go == '1') {
                $tax_on_item = $this->tax_on_item($product_total, $product_physical);
                $total_cart_tax = $tax_on_item['0'];
                $tax_rate = $tax_on_item['1'];
            }
            // No specific tax classes found,
            // use generic region-based.
            //if ($found_class != '1' && $found_class != '2') {
            //  $total_cart_tax += ($cart_subtotal-$cart_savings) * ($use_class['percent'] * .01);
            //}
            return array('error' => '0', 'tax' => $total_cart_tax, 'tax_rate' => $tax_rate, 'tbd' => '0');
        }
    }


    /**
     * Calculate tax on an item.
     * $product is an array.
     * $tax_class is an ID
     */
    function tax_on_item($item_total, $physical, $country = '', $state = '')
    {
        $tax = '0';
        $rate = '0';
        $STH = $this->run_query("SELECT * FROM `ppSD_tax_classes` ORDER BY zips DESC, state DESC");
        while ($row = $STH->fetch()) {
            $check_area = $this->check_applicable_area($row);
            if ($check_area == '1') {
                if ($physical == '1') {
                    $rate = $row['percent_physical'];
                    $intax = $item_total * ($row['percent_physical'] / 100);
                } else {
                    $rate = $row['percent_digital'];
                    $intax = $item_total * ($row['percent_digital'] / 100);
                }
                $tax = number_format($intax, 2, '.', '');
                return array($tax, $rate);
            }
        }
        return array($tax, $rate);
    }


    /**
     * Check is a user's area matches
     * a tax class's requirements.
     */
    function check_applicable_area($tax_class)
    {
        $country_north_america = $this->countries_na();
        $country_europe = $this->countries_europe();
        $country_oceania = $this->countries_aussieland();
        $country_south_america = $this->countries_sa();
        $country_asia = $this->countries_asia();
        $country_africa = $this->countries_africa();

        // Does the user match an area?
        // Everyone gets charged tax?
        if ($tax_class['country'] == 'all') {
            return "1";
        } else {
            // Consider continent-wide rules
            if ($tax_class['country'] == "North America" && in_array($this->country, $country_north_america)) {
                return "1";
            } else if ($tax_class['country'] == "Europe" && in_array($this->country, $country_europe)) {
                return "1";
            } else if ($tax_class['country'] == "Oceania" && in_array($this->country, $country_oceania)) {
                return "1";
            } else if ($tax_class['country'] == "South America" && in_array($this->country, $country_south_america)) {
                return "1";
            } else if ($tax_class['country'] == "Asia" && in_array($this->country, $country_asia)) {
                return "1";
            } else if ($tax_class['country'] == "Africa" && in_array($this->country, $country_africa)) {
                return "1";
            } // Consider state-specific rules
            else {
                if(! empty($tax_class['zips'])) {
                    $zips = explode(',', $tax_class['zips']);
                    if (in_array($this->zip, $zips)) {
                        return "1";
                    }
                } else {
                    if(empty($tax_class['state'])) {
                        if( ! empty($tax_class['country']) && $tax_class['country'] == $this->country) {
                            return "1";
                        }
                    } else {
                        if( ! empty($tax_class['state']) && $tax_class['state'] == $this->state) {
                            return "1";
                        } else {
                            return "0";
                        }
                    }
                }
            }
        }
    }


    /**
     * Gets a tax class.
     */
    function get_tax_class($id)
    {
        $tax_class = $this->get_array("
            SELECT *
            FROM `ppSD_tax_classes`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $tax_class;
    }


    /**
     * Presumes that the code
     * has been applied successfully to an
     * order, so no further checks are
     * required on that front.
     */
    function check_applicable_savings($product, $product_total, $code = '', $cart_id = '')
    {

        if (empty($code) && empty($cart_id)) {
            return '0';
        } else {
            // Determine the code and get
            // the code's information
            if (empty($code)) {
                $get_code = $this->get_order_savings_code($cart_id);
            } else {
                $get_code = $code;
            }
            $code_details = $this->get_savings_code($get_code);
            // Correct product?
            $proceed = '1';
            if (! empty($code_details['products'])) {
                // $prods = unserialize($code_details['products']);
                $prods = explode(',', $code_details['products']);
                if (in_array($product['data']['id'], (array)$prods)) {
                    $proceed = '1';
                } else {
                    $proceed = '0';
                }
            }
            if ($proceed == '1') {
                if ($code_details['dollars_off'] != '0.00') {
                    return $code_details['dollars_off'];
                } else {
                    $off = $product_total * ($code_details['percent_off'] / 100);
                    return number_format($off, 2, '.', '');
                }
            } else {
                return '0';
            }
        }
    }


    /**
     * Determine which savings code
     * has been applied to an order.
     */
    function get_order_savings_code($cart_id)
    {

        $q = $this->get_array("SELECT `code` FROM `ppSD_cart_sessions` WHERE `id`='" . $this->mysql_clean($cart_id) . "'");
        return $q['code'];
    }


    /**
     * Determine which savings code
     * has been applied to an order.
     */
    function get_savings_code($code)
    {

        // Get code
        $q = $this->get_array("SELECT * FROM `ppSD_cart_coupon_codes` WHERE `id`='" . $this->mysql_clean($code) . "'");
        return $q;
    }


    /**
     * Determine how many times a
     * savings code has been used.
     */
    function get_savings_code_usages($code, $member_id = '')
    {

        // Usages
        $where_add = '';
        if (!empty($username)) {
            $where_add = " AND `member_id`='" . $this->mysql_clean($member_id) . "'";
        }
        $q1 = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_cart_coupon_codes_used`
            WHERE `code`='" . $this->mysql_clean($code) . "'$where_add
        ");
        return $q1['0'];
    }


    /**
     * Apply a savings code to an order.
     */
    function apply_savings_code($savings_code, $force_id = '')
    {

        $code = $this->get_savings_code($savings_code);
        if (empty($code['id']) || empty($savings_code)) {
            $err = $this->get_error('S026');
            return array('error' => '1', 'error_details' => $err, 'code' => 'S026');
        }
        if (! empty($force_id)) {
            $cart_id = $force_id;
        } else {
            $cart_id = $this->id;
        }
        $username = $this->order['data']['member_id'];
        // Start Date
        if ($code['date_start'] != "1920-01-01 00:01:01" && current_date() < $code['date_start']) {
            $err = $this->get_error('S005');
            return array('error' => '1', 'error_details' => $err, 'code' => 'S005');
        }
        // End date
        if ($code['date_end'] != "1920-01-01 00:01:01" && current_date() >= $code['date_end']) {
            $err = $this->get_error('S004');
            return array('error' => '1', 'error_details' => $err, 'code' => 'S004');
        }
        // cart_minimum
        if ($code['cart_minimum'] > 0 && $this->subtotal < $code['cart_minimum']) {
            $err = $this->get_error('S027');
            $min = place_currency($code['cart_minimum']);
            $err = str_replace('%minimum%', $min, $err);
            return array('error' => '1', 'error_details' => $err, 'code' => 'S027');
        }
        // User
        if ($code['current_customers_only'] == "1" && empty($this->order['data']['member_id'])) {
            $session = new session;
            $ses = $session->check_session();
            if ($ses['error'] == '1') {
                $err = $this->get_error('S003');
                return array('error' => '1', 'error_details' => $err, 'code' => 'S003');
            }
        }
        $usages = $this->get_savings_code_usages($savings_code);
        if ($usages >= $code['max_use_overall'] && $code['max_use_overall'] != '0') {
            $err = $this->get_error('S006');
            return array('error' => '1', 'error_details' => $err, 'code' => 'S006');
        }
        if (!empty($username)) {
            $user_usages = $this->get_savings_code_usages($savings_code, $username);
            if ($user_usages >= $code['max_use_per_user'] && $code['max_use_per_user'] != "0") {
                $err = $this->get_error('S007');
                return array('error' => '1', 'error_details' => $err, 'code' => 'S007');
            }
        }
        if (!empty($code['products'])) {
            $found_prod = '0';
            $explode_products = explode(',', $code['products']);
            foreach ($explode_products as $can_be_used_on) {
                $find_product = $this->find_product_in_cart($can_be_used_on);
                if ($find_product['found'] > 0) {
                    $found_prod = "1";
                }
            }
            if ($found_prod != "1") {
                $err = $this->get_error('S008');
                return array('error' => '1', 'error_details' => $err, 'code' => 'S008');
            }
        }
        // All is good in the world:
        // add the code to the order.
        $query10 = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `code`='" . $this->mysql_clean($code['id']) . "'
            WHERE `id`='" . $this->mysql_clean($cart_id) . "'
            LIMIT 1
        ");
        return array('error' => '0', 'error_details' => '', 'code' => '');

    }


    /**
     * Remove a savings code to an order.
     */
    function remove_savings_code()
    {

        $query10 = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `code`=''
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");

    }


    /**
     * Get all products in an active
     * cart session.
     */
    function get_products_in_cart($force_session = '', $active_order = '1')
    {

        $id = $this->route_session($force_session);
        if ($active_order == '1') {
            $table = 'ppSD_cart_items';
        } else {
            $table = 'ppSD_cart_items_complete';
        }
        $all_products = array();
        $STHAA = $this->run_query("
            SELECT *
            FROM `$table`
            WHERE `cart_session`='" . $this->mysql_clean($id) . "'
        ");
        while ($row = $STHAA->fetch()) {

            $array_out = array();
            $final_price = '';
            $product = $this->get_product($row['product_id']);
            if ($row['qty'] == '0') {
                $row['qty'] = '1';
            }

            // Take into account price adjustments
            if (! empty($row['option1'])) {
                $dets = $this->get_product_option_details($row['product_id'], $row['option1']);
                if (! empty($dets['price_adjust'])) {
                    $product['data']['price'] += $dets['price_adjust'];
                }
            }

            // Trial pricing
            $tier_price = '';
            $tier_discount = '';
            if ($active_order == '1') {
                if ($product['data']['type'] == '3') {
                    $unit_price = $product['data']['trial_price'];
                } // Consider volume pricing
                // as well as product discount
                // specials, which are volumn
                // tiers starting at 0 qty.
                else {
                    $up = $this->determine_unit_price($product, $row['qty']);
                    if ($up['0'] != $product['data']['price']) {
                        $unit_price = $up['0'];
                        $tier_price = $up['0'];
                        $tier_discount = $up['1'];
                    } else {
                        $unit_price = $up['0'];
                    }
                }
            } else {
                $unit_price = $row['unit_price']; // + $row['savings']
            }
            // Check for applicable savings codes
            $total = $row['qty'] * $unit_price;
            $savings = $this->check_applicable_savings($product, $total, '', $id);
            $hold_for_tax = $total;
            $final_price = $total - $savings;
            $format_price = $this->format_product_price($product, $tier_price, $tier_discount);
            // Shipping
            if ($product['data']['physical'] == '1' || $product['data']['physical'] == '2') {
                $tot_weight = $row['qty'] * $product['data']['weight'];
                $ship_dets = $this->determine_shipping($tot_weight, $row['qty'], $product['data']['id'], $total);
            } else {
                $ship_dets = array(
                    'total' => '0'
                );
            }
            // Product tax
            if ($product['data']['tax_exempt'] != '1') {
                $tax_item = $this->determine_tax($hold_for_tax, $product['data']['physical']);
            } else {
                $tax_item = array('tax' => '', 'tax_rate' => '');
            }
            // Option Data
            $sync_id = $product['data']['sync_id'];
            $option_data = '';
            $selected_option = array();
            if (! empty($row['option1'])) {
                $STH = $this->run_query("
                    SELECT * FROM `ppSD_products_options`
                    WHERE `product_id`='" . $this->mysql_clean($row['product_id']) . "'
                ");
                $option_data = '';
                while ($rowA = $STH->fetch()) {
                    $check = 'option' . $rowA['option_no'];
                    if (!empty($row[$check])) {
                        $option_data .= '<span class="zen_option_name">' . $rowA['option_value'] . '</span>';
                        $option_data .= '<span class="zen_option_value">' . $row[$check] . '</span>';
                    }
                }

                $selected_option = $this->get_array("
                    SELECT *
                    FROM
                      `ppSD_products_options_qty`
                    WHERE
                      `product_id`='" . $this->mysql_clean($row['product_id']) . "' AND
                      `option1` = '" . $this->mysql_clean($row['option1']) . "'
                ");
                if (! empty($selected_option['sync_id'])) {
                    $sync_id = $selected_option['sync_id'];
                }
            }
            // Pricing
            $pricing = array(
                'subtotal' => $total,
                'unit_price' => $format_price,
                'plain_unit' => $unit_price,
                'qty' => $row['qty'],
                'savings' => $savings,
                'shipping' => $ship_dets['total'],
                'total' => $final_price,
                'tax' => $tax_item['tax'],
                'tax_rate' => $tax_item['tax_rate'],
            );
            $display = array(
                'subtotal' => place_currency($total),
                'unit_price' => $format_price,
                'plain_unit' => $unit_price,
                'qty' => $row['qty'],
                'savings' => place_currency($savings),
                'shipping' => place_currency($ship_dets['total']),
                'total' => place_currency($final_price),
                'tax' => place_currency($tax_item['tax']),
                'tax_rate' => $tax_item['tax_rate'] . '%',
            );
            $product['data']['option_data'] = $option_data;
            $array_out['selected_option'] = $selected_option;
            $array_out['id'] = $row['id'];
            $array_out['sync_id'] = $sync_id;
            $array_out['data'] = $product['data'];
            $array_out['dependencies'] = $product['dependencies'];
            $array_out['content'] = $product['content'];
            $array_out['pricing'] = $pricing;
            $array_out['display'] = $display;
            // Add item to array
            $all_products[] = $array_out;
        }
        return $all_products;
    }


    /**
     * Create terms and conditions
     */
    function add_terms($data)
    {

        global $employee;
        $query = $this->insert("
            INSERT INTO `ppSD_cart_terms` (`name`,`terms`,`created`,`owner`)
            VALUES (
                '" . $this->mysql_clean($data['name']) . "',
                '" . $this->mysql_clean($data['terms']) . "',
                '" . current_date() . "',
                '" . $this->mysql_clean($employee['id']) . "'
            )
        ");
        return $query;
    }


    /**
     * Volume pricing
     */
    function determine_unit_price($product, $cc_qty)
    {

        $volPrice = $this->get_array("
            SELECT `discount`
            FROM `ppSD_products_tiers`
            WHERE `product_id`='" . $this->mysql_clean($product['data']['id']) . "' AND `low`<='" . $this->mysql_clean($cc_qty) . "' AND `high`>='" . $this->mysql_clean($cc_qty) . "'
            LIMIT 1
        ");
        if (!empty($volPrice['discount'])) {
            $subtract = $product['data']['price'] * ($volPrice['discount'] / 100);
            $final_price = $product['data']['price'] - $subtract;
            $put_discount = $volPrice['discount'] + 0;
            $put_discount .= '%';
        } else {
            //if ($product['data']['type'] == '3') {
            //  $final_price = $product['data']['trial_price'];
            //} else {
            $final_price = $product['data']['price'];
            //}
            $put_discount = '';
        }
        return array($final_price, $put_discount);
    }


    function get_tier($id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_products_tiers`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1;
    }


    /**
     * Get a product's information
     */
    function get_product($id, $recache = '1')
    {

        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $final_array = $cache['data'];
        } else {
            $final_array = array();
            // Basic information
            $product = $this->get_array("
                SELECT *
                FROM `ppSD_products`
                WHERE `id`='" . $this->mysql_clean($id) . "'
                LIMIT 1
            ");
            if (empty($product['id'])) {
                $final_array = array(
                    'error' => '1',
                    'error_details' => 'Could not find product'
                );
            } else {
                if ($product['type'] == '3') {
                    $timeframe1 = format_timeframe($product['renew_timeframe']);
                    $timeframe2 = format_timeframe($product['trial_period']);
                    $product['format_trial_timeframe'] = $timeframe2['formatted'];
                    $product['format_timeframe'] = $timeframe1['formatted'];
                } else if ($product['type'] == '2') {
                    $timeframe1 = format_timeframe($product['renew_timeframe']);
                    $product['format_timeframe'] = $timeframe1['formatted'];
                    $product['format_trial_timeframe'] = '';
                } else {
                    $product['format_timeframe'] = '';
                    $product['format_trial_timeframe'] = '';
                }
                if ($product['physical'] != '1') {
                    $product['stock'] = '&#8734;';
                }
                if ($product['physical'] == '1') {
                    $product['show_physical'] = 'Yes (Weighs ' . $product['weight'] . ')';
                } else {
                    $product['show_physical'] = 'No';
                }
                $final_array['data'] = $product;
                // Discounted?
                $up = $this->determine_unit_price($final_array, '1');
                if ($up['0'] != $final_array['data']['price']) {
                    $unit_price = $up['0'];
                    $tier_price = $up['0'];
                    $tier_discount = $up['1'];
                    $final_array['data']['onsale'] = '1';
                } else {
                    $unit_price = $up['0'];
                    $tier_price = '';
                    $tier_discount = '';
                    $final_array['data']['onsale'] = '0';
                }
                $final_array['data']['format_price'] = $this->format_product_price($final_array, $tier_price, $tier_discount);
                $final_array['data']['link'] = PP_URL . '/catalog.php?id=' . $product['id'];
                // <a href="%link%">%name%</a>
                if ($product['hide'] != '0' || ! empty($product['associated_id'])) {
                    $final_array['data']['format_link'] = $product['name'];
                } else {
                    $final_array['data']['format_link'] = '<a href="' . PP_URL . '/catalog.php?id=' . $product['id'] . '">' . $product['name'] . '</a>';
                }
                // 1 = one time, 2 = subscription, 3 = trial
                if ($product['type'] == '1') {
                    $final_array['data']['show_type'] = 'Standard Product';
                } else if ($product['type'] == '2') {
                    $final_array['data']['show_type'] = 'Subscription Product';
                } else {
                    $final_array['data']['show_type'] = 'Trial Product';
                }
                // Category
                if (!empty($product['category']) && $product['hide'] != '1') {
                    $cate = $this->get_array("
                        SELECT *
                        FROM `ppSD_cart_categories`
                        WHERE `id`='" . $product['category'] . "'
                        LIMIT 1
                    ");
                    $final_array['category'] = $cate;
                } else {
                    $final_array['category'] = array(
                        'name' => ' N/A (not currently displaying in the catalog.).',
                    );
                }
                // Access granting
                $content = array();
                $STH = $this->run_query("
                    SELECT *
                    FROM `ppSD_access_granters`
                    WHERE `item_id`='" . $this->mysql_clean($id) . "'
                ");
                while ($row = $STH->fetch()) {
                    $content[] = $row;
                }
                $final_array['content'] = $content;
                // Pacakge
                $find = $this->find_item_package($id);
                $final_array['package'] = $find;


                // Dependencies
                $form_id = '';
                $form_options = '';
                $dependencies = array();
                $STH = $this->run_query("
                    SELECT *
                    FROM `ppSD_product_dependencies`
                    WHERE `product_id`='" . $this->mysql_clean($id) . "'
                ");
                while ($row = $STH->fetch()) {
                    if ($row['type'] == 'form') {
                        $form_id = $row['act_id'];
                        $form_options = $row['options'];
                    }
                }
                $dependencies['form_id'] = $form_id;
                $dependencies['form_options'] = unserialize($form_options);
                $final_array['dependencies'] = $dependencies;


                // ppSD_products_tiers
                $tiers = array();
                $STH = $this->run_query("
                    SELECT *
                    FROM `ppSD_products_tiers`
                    WHERE `product_id`='" . $this->mysql_clean($id) . "'
                ");
                while ($row = $STH->fetch()) {
                    $tiers[] = $row;
                }
                $final_array['tiers'] = $tiers;
                // Options
                $STH = $this->run_query("
                    SELECT *
                    FROM `ppSD_products_options`
                    WHERE `product_id`='" . $this->mysql_clean($id) . "'
                ");
                $all_options = array();
                $this_this = '';
                $price = $unit_price;
                $upaa = 0;
                while ($row = $STH->fetch()) {
                    $upaa++;
                    // Format it
                    $options = explode(',', $row['options']);

                    $this_this .= '<li><label class="zen_prod_opt_label">' . $row['option_value'] . '</label><select id="selectedOption' . $upaa . '" onchange="update_price(this)" name="option[' . $row['option_no'] . ']" class="req" />';
                    foreach ($options as $opt) {
                        $getInner = $this->get_array("SELECT * FROM ppSD_products_options_qty WHERE product_id='" . $id . "' AND option1='" . $opt . "' LIMIT 1");

                        if (! empty($getInner['price_adjust'])) {
                            $useprice = $price + $getInner['price_adjust'];
                        } else {
                            $useprice = $price;
                        }

                        $this_this .= '<option value="' . $opt . '" zen_price="' . currency_symbol($useprice) . '">' . $opt . '</option>';
                    }
                    $this_this .= '</select></li>';
                    // Combine it all
                    $all_options[] = $row;
                }
                $final_array['options'] = $all_options;
                $final_array['data']['fields'] = $this_this;
                // Stock
                $STH = $this->run_query("
                    SELECT *
                    FROM `ppSD_products_options_qty`
                    WHERE `product_id`='" . $this->mysql_clean($id) . "'
                ");
                $stock = array();
                while ($row = $STH->fetch()) {
                    $stock[] = $row;
                }
                // Dependencies
                // Uploads
                $size_tb = $this->get_option('catalog_img_size_tb');
                $size = $this->get_option('catalog_img_size');
                $size_lg = $this->get_option('catalog_img_size_lg');
                $final_array['data']['cover_photo_large'] = '';
                $final_array['data']['cover_photo'] = '';
                $main_thumbnail = '';
                $thumbnails = '';
                $found_cover = '0';
                $uploads = array();
                $STH = $this->run_query("
                    SELECT * FROM `ppSD_uploads`
                    WHERE `item_id`='" . $this->mysql_clean($id) . "'
                ");
                while ($row = $STH->fetch()) {
                    $uploads[] = $row;
                    $url = PP_URL . '/custom/uploads/' . $row['filename'];
                    $path = PP_PATH . '/custom/uploads/' . $row['filename'];
                    if ($row['label'] == 'cover-photo') {
                        $found_cover = '1';
                        $resizing = $this->resize_product_images($size, $path);
                        $resizing1 = $this->resize_product_images($size_lg, $path);
                        // onclick="window.open($url, '_blank', 'location=no,height=800,width=600,scrollbars=yes,status=no');"
                        $final_array['data']['cover_photo'] = '<img src="' . $url . '" width="' . $resizing['0'] . '" height="' . $resizing['1'] . '" border="0" alt="' . addslashes($row['name']) . '" id="zen_cover_photo" />';
                        $final_array['data']['cover_photo_large'] = '<img src="' . $url . '" width="' . $resizing1['0'] . '" height="' . $resizing1['1'] . '" border="0" alt="' . addslashes($row['name']) . '" id="zen_cover_photo" />';
                        $resizingB = $this->resize_product_images($size_tb, $path);
                        $thumbnails .= '<img src="' . $url . '" width="' . $resizingB['0'] . '" height="' . $resizingB['1'] . '" border="0" alt="' . addslashes($row['name']) . '" onclick="return switch_thumb(\'' . $row['filename'] . '\',\'' . $resizing1['0'] . '\',\'' . $resizing1['1'] . '\');" class="zen_product_thumb" />';
                        $main_thumbnail = '<img src="' . $url . '" width="' . $resizingB['0'] . '" height="' . $resizingB['1'] . '" border="0" alt="' . addslashes($row['name']) . '" class="zen_product_mainthumb" />';
                    } else if ($row['label'] == 'thumbnail') {
                        $resizing = $this->resize_product_images($size_tb, $path);
                        $resizing1 = $this->resize_product_images($size_lg, $path);
                        $thumbnails .= '<img src="' . $url . '" width="' . $resizing['0'] . '" height="' . $resizing['1'] . '" border="0" alt="' . addslashes($row['name']) . '" onclick="return switch_thumb(\'' . $row['filename'] . '\',\'' . $resizing1['0'] . '\',\'' . $resizing1['1'] . '\');" class="zen_product_thumb" />';
                    }
                    $uploads['img'] = '<img src="' . $url . '" border="0" alt="' . addslashes($row['name']) . '" title="' . addslashes($row['name']) . '" />';
                }
                if ($found_cover != '1') {
                    $final_array['data']['cover_photo'] = '';
                }
                $final_array['uploads'] = $uploads;
                $final_array['data']['thumbnails'] = $thumbnails;
                $final_array['data']['main_thumbnail'] = $main_thumbnail;
                $final_array['error'] = '0';
                $final_array['error_details'] = '';
            }
            $this->add_cache($id, $final_array);
        }
        return $final_array;
    }


    /**
     * Get product information for all upsell
     * entries. Used to actually display the
     * upsell products.
     *
     * @param $main_product
     * @param string $type
     * @return array
     */
    function get_upsell_products($main_product, $type = 'checkout')
    {

        if ($type == 'popup') {
            $add_where = " AND `type`='popup'";
        } else if ($type == 'checkout') {
            $add_where = " AND `type`='checkout'";
        } else {
            $add_where = '';
        }
        // Upsell
        $upsell = array();
        $STH = $this->run_query("
            SELECT `upsell`,`type`
            FROM `ppSD_product_upsell`
            WHERE `product`='" . $this->mysql_clean($main_product) . "'$add_where
            ORDER BY `order` ASC
        ");
        while ($row = $STH->fetch()) {
            $this_one = $this->get_product($row['upsell']);
            if ($this_one['error'] != '1') {
                $upsell[] = $this_one;
            }
        }
        return $upsell;
    }


    /**
     * Get all upsell entries for a product.
     *
     * @param $product
     * @return array
     */
    function get_upsell($product)
    {

        // Upsell
        $upsell = array();
        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_product_upsell`
            WHERE `product`='" . $this->mysql_clean($product) . "'
            ORDER BY `order` ASC
        ");
        while ($row = $STH->fetch()) {
            //$this_one = $this->get_product($row['upsell']);
            // if ($this_one['error'] != '1') {
            $upsell[] = $row;
            //}
        }
        return $upsell;
    }


    /**
     * Create product tiers
     */
    function create_product_tiers($product_id, $data)
    {

        if (!empty($data)) {
            foreach ($data as $aTier) {
                if (!empty($aTier)) {
                    $q1 = $this->insert("
                    INSERT INTO `ppSD_products_tiers` (`product_id`,`low`,`high`,`discount`)
                    VALUES (
                        '" . $this->mysql_cleans($product_id) . "',
                        '" . $this->mysql_cleans($aTier['low']) . "',
                        '" . $this->mysql_cleans($aTier['high']) . "',
                        '" . $this->mysql_cleans($aTier['discount']) . "'
                    )
                ");
                }
            }
        }
    }


    /**
     * Create product options
     */
    function create_product_options($product_id, $data)
    {

        if (!empty($data)) {
            $current_option = 0;
            $full_csv = '';
            foreach ($data as $anOption) {
                if (!empty($anOption['name'])) {
                    $current_option++;
                    $name = $anOption['name'];
                    // unset($anOption['name']);
                    //$build_csv = $this->csv_options($anOption);
                    //$full_csv .= ',' . $build_csv;
                    $q1 = $this->insert("
                        INSERT INTO `ppSD_products_options` (`product_id`,`option_no`,`option_value`)
                        VALUES (
                            '" . $this->mysql_cleans($product_id) . "',
                            '" . $this->mysql_cleans($current_option) . "',
                            '" . $this->mysql_cleans($name) . "'
                        )
                    ");
                    $build_csv = $this->create_product_option_values($product_id, $anOption['options']);
                    $up = $this->update("
                        UPDATE `ppSD_products_options`
                        SET `options`='" . $this->mysql_clean($build_csv) . "'
                        WHERE `id`='" . $this->mysql_clean($q1) . "'
                        LIMIT 1
                    ");
                }
            }
        }
    }


    function get_product_option($id)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_products_options`
            WHERE `id`='" . $this->mysql_clean($id) . "'
        ");
        return $q1;
    }


    function get_product_option_details($product_id, $option)
    {

        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_products_options_qty`
            WHERE `product_id`='" . $this->mysql_clean($product_id) . "' AND `option1`='" . $this->mysql_clean($option) . "'
        ");
        return $q1;
    }


    function create_product_option_values($product_id, $option_array)
    {

        $build_csv = '';
        foreach ($option_array as $inner_option) {
            $build_csv .= ',' . $inner_option['name'];
            // product_id   option1 option2 option3 option4 option5 qty price_adjust    weight_adjust
            $q1 = $this->insert("
                INSERT INTO `ppSD_products_options_qty` (`product_id`,`option1`,`qty`,`price_adjust`,`weight_adjust`,`sync_id`)
                VALUES (
                '" . $this->mysql_clean($product_id) . "',
                '" . $this->mysql_clean($inner_option['name']) . "',
                '" . $this->mysql_clean($inner_option['stock']) . "',
                '" . $this->mysql_clean($inner_option['price_change']) . "',
                '" . $this->mysql_clean($inner_option['weight_change']) . "',
                '" . $this->mysql_clean($inner_option['sync_id']) . "'
                )
            ");
        }
        return ltrim($build_csv, ',');
        /*
        if (! empty($csv)) {
            $csv = ltrim($csv,',');
            $pull_together = implode(',',$csv);
            $size = sizeof($pull_together);
            $up = 0;
            while ($size > 0) {
                $current = $pull_together[$up];
                foreach ($pull_together as $item) {
                    if ($item != $current) {
                        // product_id   option1 option2 option3 option4 option5 qty price_adjust    weight_adjust
                        $q1 = $this->insert("
                            INSERT INTO `ppSD_products_options_qty` ()
                            VALUES ()
                        ");
                    }
                }
                $size--;
                $up++;
            }
        }
        */
    }


    /**
     * Resize for thumbnails, etc.
     */
    function resize_product_images($size, $path)
    {
        list($width, $height, $type, $attr) = getimagesize($path);
        if ($width >= $height) {
            if ($size >= $width) {
                // Nothing more!
            } else {
                // Catalog Listings
                $ratio = $width / $size;
                $height = ceil($height / $ratio);
                $width = $size;
            }
        }
        else if ($height > $width) {
            if ($size >= $height) {
                // Nothing more!
            } else {
                // Catalog Listings
                $ratio = $height / $size;
                $width = ceil($width / $ratio);
                $height = $size;
            }
        }
        else {
            $width = $size;
            $height = $size;
        }
        return array($width, $height);
    }


    /**
     * Check if there are physical products
     * in an order.
     */
    function find_physical($order_id, $active_order = '0')
    {

        if ($active_order == '1') {
            $table = 'ppSD_cart';
        } else {
            $table = 'ppSD_charge_log';
        }
        $total = 0;
        $found_physical = 0;
        $returned = array();
        $products = array();
        $STH = $this->run_query("
            SELECT * FROM `$table`
            JOIN `ppSD_products`
            ON $table.product_id=ppSD_products.id
            WHERE ppSD_products.physical='1'
        ");
        while ($row = $STH->fetch()) {
            $found_physical++;
            $this_total = $row['qty'] * $row['price'];
            $total += $this_total;
            $row['total_cost'] = $this_total;
            $products[] = $row;
        }
        $returned['total'] = $total;
        $returned['found'] = $found_physical;
        $returned['products'] = $products;
        return $products;
    }


    /**
     * Determine the price of a product.
     * Takes into account trails, volume
     * pricing, and product tiers.
     */
    function get_product_price($product, $qty = '1')
    {

        $pricing_details = array();
        // Base price
        $pricing = $this->get_array("
            SELECT `price`,`type`,`trial_price`
            FROM `ppSD_products`
            WHERE `id`='" . $this->mysql_clean($product) . "'
            LIMIT 1
        ");
        if ($pricing['type'] == '3') {
            $price = $pricing['trial_price'];
            $pricing_details['trial'] = '1';
        } else {
            $price = $pricing['price'];
            $pricing_details['trial'] = '0';
        }
        // Tiers
        $tiers = $this->get_array("
            SELECT `discount` FROM `ppSD_products_tiers`
            WHERE `product_id`='" . $this->mysql_clean($product) . "'
            AND `low`<='" . $this->mysql_clean($qty) . "'
            AND `high`>='" . $this->mysql_clean($qty) . "'
            ");
        if (!empty($tiers['discount'])) {
            $discount = $price * ($tiers['discount'] / 100);
            $price -= $discount;
        } else {
            $discount = '0.00';
        }
        $pricing_details['price'] = $price;
        $pricing_details['tier_discount'] = $discount;
        return $pricing_details;
    }


    /**
     * Get a user's credit cards.
     *
     * @param   $user_id
     *
     * @return
     */
    function getUserCards($user_id)
    {
        $cards = array();

        $go = $this->run_query("
            SELECT *
            FROM `ppSD_cart_billing`
            WHERE `member_id`='" . $this->mysql_clean($user_id) . "'
        ");
        while ($row = $go->fetch()) {
            $cards[] = $this->decode_card($row);
        }

        return array_reverse($cards);
    }


    /**
     * $from_cron => 1 -> sends full card.
     */
    function order_card_info($card_id, $from_cron = '0')
    {

        $billing_data = array();
        $card = $this->get_array("
            SELECT *
            FROM `ppSD_cart_billing`
            WHERE `id`='" . $this->mysql_clean($card_id) . "'
        ");
        if (empty($card['id'])) {
            $billing_data['error'] = '1';
            $billing_data['error_details'] = 'Could not find credit card in the database.';
        } else {
            $gateway = $this->get_gateways('', $card['gateway']);
            if (!empty($gateway['0'])) {
                if ($gateway['0']['api'] != '1') {
                    $billing_data['card_type'] = '';
                    $billing_data['cc_number'] = '';
                    $billing_data['card_exp_yy'] = '';
                    $billing_data['card_exp_mm'] = '';
                    $billing_data['method'] = $gateway['0']['name'];
                }
            }
            // Decode a card
            $card_info = $this->decode_card($card, $from_cron);
            $billing_data = array_merge($billing_data, $card_info);
            // Method
            if ($card['method'] != 'Credit Card') {
                $billing_data['full_method'] = $card['method'];
            }
            // Return
            $billing_data['error'] = '0';
            $billing_data['error_details'] = '';
        }
        return $billing_data;
    }


    /**
     * Decode a credit card
     */
    function decode_card($card, $from_cron = '0')
    {

        $billing_data = array();
        if (! empty($card)) {
            $last_four = '';
            foreach ($card as $aField => $value) {
                if ($aField == 'cc_number' && $aField != '0') {
                    if (empty($value)) {
                        $billing_data['cc_number'] = '';
                        $billing_data['card_type'] = '';
                    } else {
                        $value = decode($value);
                        $last_four = substr($value, -4, 4);
                        $billing_data['last_four'] = $last_four;
                        // $cc_det = get_cc_type($value);
                        // $billing_data['card_type'] = $cc_det['0'];
                        if ($from_cron == '1') {
                            $billing_data['cc_number'] = $value;
                        } else {
                            $billing_data['cc_number'] = 'XXXX' . $last_four;
                        }
                    }
                } else if ($aField == 'card_exp_yy' || $aField == 'card_exp_mm' || $aField == 'first_name' || $aField == 'last_name') {
                    $value = decode($value);
                    $billing_data[$aField] = $value;
                } else {
                    $billing_data[$aField] = $value;
                }
            }
            $billing_data['full_method'] = $billing_data['card_type'] . ' ending in ' . $last_four;
            $theme = $this->get_theme();
            if ($billing_data['card_type'] == 'Visa') {
                $billing_data['img'] = '<img src="' . $theme['url'] . '/imgs/icon-visa.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($billing_data['card_type'] == 'Mastercard') {
                $billing_data['img'] = '<img src="' . $theme['url'] . '/imgs/icon-mastercard.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($billing_data['card_type'] == 'Discover') {
                $billing_data['img'] = '<img src="' . $theme['url'] . '/imgs/icon-discover.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else if ($billing_data['card_type'] == 'Amex') {
                $billing_data['img'] = '<img src="' . $theme['url'] . '/imgs/icon-amex.png" width="32" height="32" border="0" class="zen_cc_icon" />';
            } else {
                $billing_data['img'] = '';
            }
        }
        return $billing_data;
    }


    /**
     * Change an order's ID.
     */
    function update_order_number($old = '', $new_session = '')
    {
        if (empty($old)) {
            $old = $_COOKIE['zen_cart'];
        }
        if (empty($new_session)) {
            $new_session = $this->generate_cart_id();
        }
        $q = $this->update("UPDATE `ppSD_cart_items` SET `cart_session`='$new_session' WHERE `cart_session`='" . $this->mysql_clean($old) . "'");
        $qA = $this->update("UPDATE `ppSD_cart_items_complete` SET `cart_session`='$new_session' WHERE `cart_session`='" . $this->mysql_clean($old) . "'");
        $q1 = $this->update("UPDATE `ppSD_cart_sessions` SET `id`='$new_session' WHERE `id`='" . $this->mysql_clean($old) . "'");
        $q1 = $this->update("UPDATE `ppSD_cart_session_totals` SET `id`='$new_session' WHERE `id`='" . $this->mysql_clean($old) . "'");
        $q3 = $this->update("UPDATE `ppSD_shipping` SET `cart_session`='$new_session' WHERE `cart_session`='" . $this->mysql_clean($old) . "'");
        $q4 = $this->update("UPDATE `ppSD_subscriptions` SET `order_id`='$new_session' WHERE `order_id`='" . $this->mysql_clean($old) . "'");
        $q5 = $this->update("UPDATE `ppSD_invoices` SET `order_id`='$new_session' WHERE `order_id`='" . $this->mysql_clean($old) . "'");
        $q1 = $this->update("UPDATE `ppSD_cart_coupon_codes_used` SET `order_id`='$new_session' WHERE `order_id`='" . $this->mysql_clean($old) . "'");
        $q1 = $this->update("UPDATE `ppSD_cart_tracking` SET `cart_session`='$new_session' WHERE `cart_session`='" . $this->mysql_clean($old) . "'");

        $this->create_cookie("zen_cart", $new_session, "6000000");

        return $new_session;
    }


    /**
     * Completely delete an order from the database.
     *
     * NOT IN USE!
     */
    function clear_cart($cart_id = '')
    {

        if (empty($cart_id)) {
            $cart_id = $_COOKIE['zen_cart'];
        }
        $q1 = $this->delete("DELETE FROM `ppSD_cart_items` WHERE `cart_session`='" . $this->mysql_clean($cart_id) . "'");
        $q2 = $this->delete("DELETE FROM `ppSD_cart_sessions` WHERE `id`='" . $this->mysql_clean($cart_id) . "'");
        $q3 = $this->delete("DELETE FROM `ppSD_shipping` WHERE `order`='" . $this->mysql_clean($cart_id) . "'");
        $q4 = $this->delete("DELETE FROM `ppSD_charge_log` WHERE `order_id`='" . $this->mysql_clean($cart_id) . "'");
        $this->delete_cookie("ppSD_cart");
        return '1';
    }


    function remove_cookies()
    {
        $this->delete_cookie("zen_cart");
        $this->delete_cookie("zen_ret");
        $this->delete_cookie("zen_invoice");
        $this->delete_cookie('zen_forms_later');
    }


    function reset_cart()
    {
        $this->delete_cookie("zen_cart");
        $id = $this->start_session();
        return $id;
    }

    /**
     * Empty products from a cart.
     */
    function empty_cart()
    {

        if (!empty($this->id)) {

            // Start Task
            $indata = array(
                'cart_id' => $this->id,
            );
            $task_id = $this->start_task('cart_empty', 'user', '', '', '', $indata);

            $query2 = $this->delete("
                DELETE FROM `ppSD_cart_items`
                WHERE `cart_session`='" . $this->mysql_clean($this->id) . "'
            ");
            $this->delete_cookie('zen_invoice');

            $task    = $this->end_task($task_id, '1', '', 'cart_empty', '', $indata);


        }
    }


    /**
     * Updates components in a cart.
     */
    function update_cart_item($product_id, $qty, $cart_id = '')
    {

        if (empty($cart_id)) {
            $cart_id = $_COOKIE['ppSD_cart'];
        }
        if ($qty == "0") {

            // Start Task
            $indata = array(
                'product' => $product_id,
                'cart_id' => $cart_id,
            );
            $task_id = $this->start_task('cart_remove', 'user', '', '', '', $indata);

            $query1 = $this->delete("DELETE FROM `ppSD_cart_items` WHERE `cart_session`='" . $this->mysql_clean($cart_id) . "' AND `product_id`='" . $this->mysql_clean($product_id) . "' LIMIT 1");

            $task    = $this->end_task($task_id, '1', '', 'cart_remove', '', $indata);

        } else {

            // Start Task
            $indata = array(
                'product' => $product_id,
                'cart_id' => $cart_id,
                'qty' => $qty,
            );
            $task_id = $this->start_task('cart_update', 'user', '', '', '', $indata);

            $query2 = $this->update("
                UPDATE `ppSD_cart_items`
                SET `qty`='" . $this->mysql_clean($qty) . "'
                WHERE
                    `cart_session`='" . $this->mysql_clean($cart_id) . "` AND
                    `product_id`='" . $this->mysql_clean($product_id) . "'
                LIMIT 1
            ");

            $task    = $this->end_task($task_id, '1', '', 'cart_update', '', $indata);

        }
    }


    /**
     * Remove Cart Item
     */
    function remove_cart_item($product_id, $force_session = '')
    {

        // Route the session
        $session = $this->route_session($force_session);
        // Remove it
        $query1 = $this->delete("
            DELETE FROM `ppSD_cart_items`
            WHERE `cart_session`='" . $this->mysql_clean($session) . "' AND `product_id`='" . $this->mysql_clean($product_id) . "'
            LIMIT 1
        ");
    }


    /**
     * Get cart bubbles
     * Designed for event confirmation,
     * used anywhere!
     */
    function get_cart_bubbles($force_session = '')
    {

        // Route the session
        $session = $this->route_session($force_session);
    }


    /**
     * Add a package of items to the cart.
     * $input = array(PRODUCT_ID => QTY)
     */
    function package_add($items)
    {

        foreach ($items as $id => $qty) {
            $add = $this->add($id, $qty);
        }
    }


    /**
     * Recalcuate order total
     */
    function calculate_order_total($id = '', $active_order = '1')
    {

        if (empty($id)) {
            $id = $this->id;
        }
        $products = $this->get_products_in_cart($id, $active_order);



        // Totals
        $savings = 0;
        $tax = 0;
        $order_subtotal = 0;
        $order_total = 0;
        $weight = 0;
        $total_items = 0;
        $total_products = 0;
        $total_shipping = 0;
        $total_tax = 0;
        $total_physical_items = 0;
        foreach ($products as $aProd) {
            $order_subtotal += $aProd['pricing']['subtotal'];
            $savings += $aProd['pricing']['savings'];
            $weight += $aProd['data']['weight'];
            $total_items += $aProd['pricing']['qty'];
            $total_shipping += $aProd['pricing']['shipping'];
            if ($aProd['data']['physical'] == '1' || $aProd['data']['physical'] == '2') {
                $total_physical_items += $aProd['pricing']['qty'];
            }
            $total_tax += $aProd['pricing']['tax'];
            $order_total += $aProd['pricing']['total'] + $aProd['pricing']['tax'] + $aProd['pricing']['shipping'];
            $total_products++;
        }
        $this->subtotal = $order_subtotal;

        if ($order_total < 0) {
            $order_total = '0.00';
        }

        return array(
            'subtotal' => $order_subtotal,
            'savings' => $savings,
            'weight' => $weight,
            'total_items' => $total_items,
            'shipping' => $total_shipping,
            'total_physical_items' => $total_physical_items,
            'total_tax' => $total_tax,
            'order_total' => $order_total,
            'total_products' => $total_products,
            'products' => $products,
        );
    }


    /**
     * Update a basic component of an order.
     */
    function update_order($id, $data)
    {
        $doupdate = '';
        $debug = '';
        foreach ($data as $name => $value) {
            $doupdate .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_clean($value) . "'";
            $debug .= ",`" . $name . "`='" . $value . "'";
        }
        $doupdate = substr($doupdate, 1);
        if (!empty($doupdate)) {
            $q = $this->update("
                UPDATE `ppSD_cart_sessions`
                SET $doupdate
                WHERE `id`='" . $this->mysql_cleans($id) . "'
                LIMIT 1
            ");
        } else {
            return array('error' => '1', 'error_details' => 'No data submitted.');
        }
    }


    /**
     * Total Items in cart
     */
    function total_items_in_cart($force_session = '')
    {
        $session = $this->route_session($force_session);
        $incart = $this->get_array("
            SELECT SUM(qty)
            FROM `ppSD_cart_items`
            WHERE `cart_session`='" . $this->mysql_clean($session) . "'
        ");
        if (empty($incart['0'])) {
            $incart['0'] = '0';
        }
        return $incart['0'];
    }


    /**
     * Check if a product is currently
     * in the user's cart.
     */
    function find_product_in_cart($product_id, $options = '')
    {
        // Options
        $option_where = $this->format_option_where($options);

        $chekc = $this->get_array("
            SELECT * FROM `ppSD_cart_items`
            WHERE $option_where`cart_session`='" . $this->mysql_clean($this->id) . "'
            AND `product_id`='" . $this->mysql_clean($product_id) . "'
        ");

        if (!empty($chekc['id'])) {
            $chekc['found'] = '1';
            $chekc['error_details'] = '';
            return $chekc;
        } else {
            return array('found' => '0', 'error_details' => 'Product not in cart.', 'qty' => '0');
        }
    }


    /**
     * Return currect product stock.
     */
    function current_stock($product)
    {

        $stock = $this->get_array("
            SELECT `physical`,`stock`,`max_per_cart`
            FROM `ppSD_products`
            WHERE `id`='$product' AND `hide`!='1'
            LIMIT 1
        ");
        return $stock;
    }


    /**
     * Store payment method
     * Takes an array of all information
     * available from the billing information
     * screen and saves it if applicable.
     */
    function add_cardin($data, $save_full_card = '0')
    {
        $task_id = $this->start_task('cc_add', 'user', '', $data['member_id']);
        $gen_id = generate_id('random', '13');
        $salt = generate_id('random', '25');
        $in = "`id`,`salt`";
        $val = "'$gen_id','" . $salt . "'";
        //$val_clean = "'$gen_id','" . $salt . "'";
        // Some functions includes these,
        // so unset them if they exist.
        unset($data['card_id']);
        unset($data['card_type']);
        foreach ($data as $name => $value) {
            if ($name == 'cvv') {
                // Never store this!!
                continue;
            } else {
                if ($name == 'cc_number') {
                    $type = $this->get_card_type($value);
                    if ($save_full_card != '1') {
                        $value = substr($value, -4, 4);
                    }
                    $in .= ",`card_type`";
                    $val .= ",'" . $type['0'] . "'";
                    $value = encode($value);
                } else if ($name == 'card_exp_yy' || $name == 'card_exp_mm' || $name == 'first_name' || $name == 'last_name') {
                    $value = encode($value);
                } else {
                    // nothing more to do.
                }
                // Add to query
                $in .= ",`" . $name . "`";
                $val .= ",'" . $this->mysql_clean($value) . "'";
                //$val_clean .= ",'" . $value . "'";
            }
        }
        $q = $this->insert("
            INSERT INTO `ppSD_cart_billing` ($in)
            VALUES ($val)
        ");
        $indata = array(
            'id' => $gen_id,
            'member_id' => $data['member_id'],
        );
        $task    = $this->end_task($task_id, '0', '', 'cc_add', '', $indata);
        return $gen_id;
    }

    /**
     * Used for $0 cart order with no
     * billing method.
     */
    function store_card_no_method()
    {
        $gen_id = generate_id('random', '13');
        $salt = generate_id('random', '25');
        $in = "`id`,`salt`,`method`";
        $val = "'$gen_id','" . $salt . "','Other'";
        foreach ($this->billing as $name => $value) {
            if ($name == 'cvv') {
                continue;
            }
            if ($name == 'first_name' || $name == 'last_name') {
                $value = encode($value);
            }
            $in .= ",`" . $name . "`";
            $val .= ",'" . $this->mysql_clean($value) . "'";
        }
        $q = $this->insert("
            INSERT INTO `ppSD_cart_billing` ($in)
            VALUES ($val)
        ");
        return $gen_id;
    }


    /**
     * Get a credit card.
     */
    function get_card($id)
    {
        $card = $this->get_array("
            SELECT *
            FROM `ppSD_cart_billing`
            WHERE
                `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        /* AND
                `method`='Credit Card'
         */
        $decode = $this->decode_card($card);
        if (! empty($decode['id'])) {
            $secure_url = $this->getSecureLink();
            //$secure_url = str_replace('http://', 'https://', PP_URL);
            $decode['user_delete_link'] = '(<a href="null.php" onclick="return delete_card(\'' . $decode['id'] . '\',\'' . $decode['salt'] . '\');">' . $this->get_error('G002') . '</a>)';
            // <a href="null.php" onclick="return delete_card('b4ced3d7b0a25','c17d7975598307210712510aa');">Delete</a>
        } else {
            $decode['user_delete_link'] = '';
            $decode['id'] = '';
        }
        return $decode;
    }


    /**
     * Find a credit card.
     * Used for subscriptions
     * to ensure a card is on
     * file.
     */
    function find_card($id)
    {

        $found = $this->get_array("
            SELECT COUNT(*) FROM `ppSD_cart_billing`
            WHERE `id`='" . $this->mysql_clean($id) . "'
        ");
        return $found['0'];
    }

    /**
     * Find a subscription product in your cart.
     * @return bool
     */
    function find_subscription_in_cart()
    {
        foreach ($this->order['components'] as $item) {
            if ($item['data']['type'] == '2' || $item['data']['type'] == '3') {
                return true;
            }
        }
        return false;
    }


    /**
     * Delete a credit card on file.
     */
    function delete_card($id, $salt)
    {
        $error = '1';
        $card = $this->get_card($id);

        $task_id = $this->start_task('cc_delete', 'user', '', $card['member_id']);

        if ($card['salt'] == $salt) {
            $error = '0';
            if (!empty($card['member_id'])) {
                $session = new session;
                $ses = $session->check_session();
                if ($ses['member_id'] != $card['member_id']) {
                    $error = '2';
                }
            }

        }
        if ($error != '0') {
            if ($error == '2') {
                return array('error' => '1', 'error_details' => 'You do not own this card.');
            } else {
                return array('error' => '1', 'error_details' => 'Could not delete credit card.');
            }
            exit;
        } else {
            // Gateway?
            if (!empty($card['gateway_id_1'])) {
                $gateway = new $card['gateway']('', $card);
                $del = $gateway->delete_user();
            }
            $q1 = $this->delete("DELETE FROM `ppSD_cart_billing` WHERE `id`='" . $this->mysql_clean($id) . "' LIMIT 1");

            $indata = array(
                'id' => $id,
                'member_id' => $card['member_id'],
            );
            $task    = $this->end_task($task_id, '0', '', 'cc_delete', '', $indata);

            return array('error' => '0', 'error_details' => '');
        }
    }


    /**
     * Credit cards on file for
     * a user.
     */
    function get_credit_cards($member_id, $cp = '0', $selected_card = '')
    {
        if (empty($member_id))
            return false;

        $ccs = '';
        $STH = $this->run_query("
            SELECT * FROM `ppSD_cart_billing`
            WHERE
                `member_id`='" . $this->mysql_clean($member_id) . "' AND
                `method`='Credit Card' AND 
                `cc_number`!=''
        ");
        while ($row = $STH->fetch()) {
            $changes = $this->decode_card($row);
            if ($cp == '1') {
                if ($selected_card == $row['id']) {
                    $ccs .= '<li><input type="radio" checked="checked" name="card_id" value="' . $row['id'] . '" onclick="return existing_card();" /> ' . $changes['img'] . ' ending in ' . $changes['cc_number'] . ' (' . $changes['card_exp_mm'] . '/' . $changes['card_exp_yy'] . ')</li>';
                } else {
                    $ccs .= '<li><input type="radio" name="card_id" value="' . $row['id'] . '" onclick="return existing_card();" /> ' . $changes['img'] . ' ending in ' . $changes['cc_number'] . ' (' . $changes['card_exp_mm'] . '/' . $changes['card_exp_yy'] . ')</li>';
                }
            } else {
                $template = new template('cart_cc_option', $changes, '0');
                $ccs .= $template;
            }
        }
        return $ccs;
    }


    /**
     * Check for shipping
     * Used for non-APIs
     */
    function check_for_shipping()
    {

        if ($this->order['data']['total_physical_items'] > 0 && empty($this->order['data']['shipping_rule'])) {
            $ship_options = $this->get_flat_shipping();
            $changes = array(
                'ship_options' => $ship_options,
            );
            $template = new template('cart_shipping', $changes, '1');
            echo $template;
            exit;
        }
    }


    function get_flat_shipping($rule_id = '', $type = 'template')
    {
        $up = 0;
        $ship_options = ($type == 'array') ? array() : '';
        $STH = $this->run_query("SELECT * FROM `ppSD_shipping_rules` WHERE `type`='flat' ORDER BY `cost` ASC");
        while ($row = $STH->fetch()) {
            $up++;
            if ($rule_id == $row['id']) {
                $checked = 'checked="checked"';
                $row['put_checked'] = ' checked="checked"';
            } else {
                $checked = '';
                $row['put_checked'] = '';
            }
            $row['format_price'] = place_currency($row['cost']);
            if ($type == 'array') {
                $ship_options[] = $row;
            }
            else if ($type == 'admin') {
                $ship_options .= '<input type="radio" name="shipping[id]" value="' . $row['id'] . '" ' . $checked . ' /> ' . $row['name'] . ' (' . $row['format_price'] . ')<br />';
            } else {
                $temp = new template('cart_shipping_entry', $row, '0');
                $ship_options .= $temp;
            }
        }
        return $ship_options;
    }


    /**
     * Update flat shipping rate.
     */
    function update_shipping($ship_rule)
    {
        $rule = $this->get_shipping_rule($ship_rule);

        $q = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `shipping_rule`='" . $this->mysql_clean($rule['id']) . "',`shipping_name`='" . $this->mysql_clean($rule['name']) . "'
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
    }


    /**
     * Update registration session.
     */
    function update_session_regid($reg_id = '')
    {
        if (empty($reg_id)) {
            $q = $this->update("
                UPDATE `ppSD_cart_sessions`
                SET `reg_session`=''
                WHERE `id`='" . $this->mysql_clean($this->id) . "'
                LIMIT 1
            ");
        } else {
            $q = $this->update("
                UPDATE `ppSD_cart_sessions`
                SET `reg_session`='" . $this->mysql_clean($reg_id) . "'
                WHERE `id`='" . $this->mysql_clean($this->id) . "'
                LIMIT 1
            ");
        }
    }


    /**
     * Agree to terms
     */
    function check_terms()
    {

        if ($this->order['data']['agreed_to_terms'] != '1') {
            //$req = $this->get_option('cart_req_terms');
            //if ($req == '1') {
            $all_terms = '';
            $found_terms = 0;
            $used_terms = array();
            $product_array = $this->get_products_in_cart('', '1', '', '');
            foreach ($product_array as $product) {
                if (!empty($product['data']['terms'])) {
                    $found_terms = 1;
                    $terms = $this->get_terms($product['data']['terms']);
                    if (!in_array($terms['id'], $used_terms)) {
                        $changes = array(
                            'name' => $terms['name'],
                            'terms' => $terms['terms'],
                            'product' => $product['data']
                        );
                        $template = new template('cart_terms_entry', $changes, '0');
                        $used_terms[] = $terms['id'];
                        $all_terms .= $template;
                    }
                }
            }
            if ($found_terms == '1') {
                $changes = array(
                    'all_terms' => $all_terms,
                );
                $template = new template('cart_terms', $changes, '1');
                echo $template;
                exit;
            }
            //}
        }
    }


    function check_upsell()
    {

        if ($this->order['data']['saw_upsell'] != '1') {
            $all_upsell = '';
            $incart = $this->get_products_in_cart();
            foreach ($incart as $anItem) {
                $upsell = $this->get_upsell_products($anItem['data']['id'], 'popup');
                if (!empty($upsell)) {
                    foreach ($upsell as $item) {
                        $in_cart = $this->find_product_in_cart($item['data']['id']);
                        if ($in_cart['found'] != '1') {
                            $upsell_link = $this->upsell_link($item);
                            $changes_up = array(
                                'product' => $item['data'],
                                'link' => $upsell_link,
                            );
                            $tempA = new template('cart_product_entry_upsell', $changes_up, '0');
                            $all_upsell .= $tempA;
                        }
                    }
                    // Update upsell on order
                    $this->update_upsell_status();
                    // Upsell display?
                    if (!empty($all_upsell)) {
                        $changesA = array(
                            'products' => $all_upsell,
                        );
                        $temp = new template('cart_upsell_checkout', $changesA, '1');
                        echo $temp;
                        exit;
                    }
                }
            }
        }
    }


    function upsell_link($item)
    {

        if (!empty($item['options'])) {
            $link = "<a href=\"catalog.php?id=" . $item['data']['id'] . "\">Add to Cart</a>";
        } else {
            $link = "<a href=\"null.php\" onclick=\"return quick_add_to_cart('" . $item['data']['id'] . "')\">Add to Cart</a>";
        }
        return $link;
    }


    function update_upsell_status($force_order = '')
    {

        $force_order = $this->route_session($force_order);
        $q1 = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `saw_upsell`='1'
            WHERE `id`='" . $this->mysql_clean($force_order) . "'
            LIMIT 1
        ");
    }


    function confirm_dependency_submission($status)
    {

        $q = $this->update("
            UPDATE `ppSD_cart_sessions`
            SET `dependencies`='$status'
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
    }


    // $this->order
    function check_forms()
    {

        if (!empty($_COOKIE['zen_forms_later'])) {
            $this->confirm_dependency_submission('2');
        } else {
            if ($this->order['data']['dependencies'] != '1' && $this->order['data']['dependencies'] != '2') {
                if (!empty($this->order['dependency_submitted'])) {
                    $submitted = unserialize($this->order['dependency_submitted']);
                } else {
                    $submitted = array();
                }
                $total_submitted = sizeof($submitted);
                //$req = $this->get_option('cart_req_terms');
                //if ($req == '1') {
                $fields = new field;
                $all_terms = '';
                $found_depend = 0;
                $submitted = 0;
                $used_terms = array();
                $product_array = $this->get_products_in_cart('', '1', '', '');
                foreach ($product_array as $product) {
                    $total_required = '1';
                    if (!empty($product['dependencies']['form_id'])) {
                        $found_depend = 1;
                        // $fields
                        if ($product['dependencies']['form_options']['form_multi'] != '1') {
                            $total_required = $product['pricing']['qty'];
                        }
                        if ($total_submitted == $total_required) {
                            $found_depend = 0;
                            $this->confirm_dependency_submission('1');
                            break;
                        }
                        $salt = md5($this->id . 'ZENYO!' . $product['dependencies']['form_id'] . md5($total_required));
                        $salt_done = md5($this->id . 'ZENDONEYO!' . $product['dependencies']['form_id'] . md5($total_required));
                        // SALT IS SUBMITTED
                        // Based on the Salt received,
                        // we are either still submitting
                        // or we are done submitting.
                        if (!empty($_GET['fds'])) {
                            if ($salt == $_GET['fds']) {
                                if (!empty($_GET['fdsl'])) {
                                    $submitted = $_GET['fdsl'];
                                } else {
                                    $submitted = '1';
                                }
                            } else {
                                $found_depend = 0;
                                $this->confirm_dependency_submission('1');
                                break;
                            }
                        }
                        $next_submit = $submitted + 1;
                        if ($next_submit == $total_required) {
                            $use_salt = $salt_done;
                            $complete_str = 'complete=1&'; // Only controls success code to display on page.
                        } else {
                            $use_salt = $salt;
                            $complete_str = '';
                        }
                        $redirect = PP_URL . '/pp-cart/checkout.php?' . $complete_str . 'fds=' . $use_salt . '&fdsl=' . $submitted;
                        if (!empty($_GET['scode'])) {
                            $scode = $_GET['scode'];
                            $ecode = '';
                        } else {
                            $scode = '';
                            $ecode = 'F039';
                        }
                        $form_idA = str_replace('register-', '', $product['dependencies']['form_id']);

                        $form = new form('', 'register', $form_idA, $_COOKIE['zen_cart'], '1', '0');
                        $form->start_session();
                        $form->set_redirect($redirect);

                        if ($found_depend == '1') {
                            $page = $product['dependencies']['form_id'] . '-1';
                            $field = new field;
                            $genform = $field->generate_form($page, '');
                            $step_ul = $form->generate_step_array($form->formdata, '1');
                            $changes = array(
                                'scode' => $scode,
                                'ecode' => $ecode,
                                'form' => $genform,
                                'data' => $form->formdata,
                                'session' => $form->{'session_id'},
                                'step' => '1',
                                'step_list' => $step_ul,
                                'salt' => md5($form->salt),
                                'product' => $product['data'],
                                'pass_strength' => $this->get_option('required_password_strength'),
                                'captcha' => '',
                            );
                            $template = new template('dependency', $changes, '1');
                            echo $template;
                            exit;
                        }
                    }
                }
                //}
            }
        }
    }


    function get_terms($id)
    {

        $q1 = $this->get_array("
            SELECT * FROM `ppSD_cart_terms`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (strlen($q1['terms']) == strlen(strip_tags($q1['terms']))) {
            $q1['terms'] = '<p>' . nl2br($q1['terms']) . '</p>';
        }
        return $q1;
    }


    /**
     * Add credit card but verify first.
     */
    function add_card_with_verify($cc, $member_id = '', $force_gateway = '')
    {

        $token = array();
        if (!empty($force_gateway)) {
            $gateway = $this->get_gateways('', $force_gateway);
        } else {
            $gateway = $this->get_gateways('1');
        }
        if ($gateway['0']['api'] != '1') {
            echo "0+++Your payment gateway is not an API. You will not be able to create a subscription with a credit card for this user. Please select the \"No Credit Card\" option to continue.";
            exit;
        } else {
            // Good? Add card...
            if ($gateway['0']['local_card_storage'] == '1') {
                $card_id = $this->add_cardin($cc, '1');
            } else {
                // Verify card
                $verify = new $gateway['0']['code']('0', $cc, '', '', '1');
                $token = $verify->build_token();
                if ($token['error'] == '1') {
                    if (! empty($verify->show_error)) {
                        echo "0+++Credit Card error: " . $verify->show_error['msg'] . ' (' . $verify->show_error['resp_code'] . ')';
                        exit;
                    } else {
                        echo "0+++Credit Card error: " . $token['msg'] . ' (' . $token['resp_code'] . ')';
                        exit;
                    }
                } else {
                    $cc['gateway'] = $gateway['0']['code'];
                    $cc['gateway_id_1'] = $token['cust_id'];
                    $cc['member_id'] = $member_id;
                    if (!empty($token['gateway_id_2'])) {
                        $cc['gateway_id_2'] = $token['gateway_id_2'];
                    }
                    $this->billing = $cc; // Added 3/13/2013
                    $cc['id'] = $gateway['0']['code']; // Added 3/13/2013
                    $card_id = $this->store_card($cc, $member_id);
                }
                $gateway_name = $gateway['0']['code'];
            }
        }
        return array($card_id, $gateway_name, $token);
    }


    /**
     * Apply cart settings to account
     *
     * @param string $force_sessions  Controls the order ID. If left blank, it will
     *                                use the cookie to determine this.
     * @param array $gateway        Array of payment gateway details received from the
     *                              payment gateway class.
     *                              $gateway = array('id','resp_code','order_no','fee','msg')
     * @param string $status        The status of the order. 1 = Success.
     * @param bool $skip_thank_you  Used for PayPal IPN mainly and admin CP additions.
     *                              Tells the program not to send thank you receipts.
     * @param datetime $force_date  Controls the date on which this order took place. Almost
     *                              always empty, except for some transactions added from the
     *                              admin dashboard.
     * @param bool $skip_subs_invoices  This is only used for database transfers whereby subs
     *                                  have already been created, such as ppSD2 to Zenbership.
     *                                  In 99.9% of cases, leave this as "0", otherwise subscriptions
     *                                  will not be created, which isn't good.
     */
    function complete_order($force_session = '', $gateway = array(), $status = '1', $skip_thank_you = '0', $force_date = '', $skip_subs_invoices = '0')
    {
        $new_member = false;
        $memData = array();

        /**
         * Get the order
         */
        if (! empty($force_session)) {
            $use_order = $this->get_order($force_session, '1');
        } else {
            $use_order = $this->order;
            $force_session = $use_order['data']['id'];
        }

        if (empty($use_order['data']['id']) || $use_order['data']['status'] == 1) {
            return false;
        }

        /**
         * Save the card
         * Data is stored in $this->billing
         * and $this->shipping before this
         * function was called.
         *   $this->set_shipping($_POST['shipping'])
         *   $this->set_billing($_POST['billing'])
         */
        $member_id = $use_order['data']['member_id'];
        $task_id = $this->start_task('transaction', 'user', '', $member_id);

        // Tracking?
        $connect = new connect;
        $track = $connect->check_tracking();
        /**
         * Registration?
         */
        if (! empty($use_order['data']['reg_session'])) {
            if ($status == '2') {
                $put_status = 'S';
            } else {
                $put_status = '';
            }
            $form = new form($use_order['data']['reg_session']);
            $formdata = $form->get_form($form->{'act_id'});
            $member_id = $form->session_info['final_member_id'];

            $mem_data = $form->complete_reg($formdata, $put_status, '1');
            $member_id = $mem_data['member']['data']['id'];

            $new_member = true;

            $mtype = 'member';
        }

        /**
         * Create a contact
         * Or get logged in member data.
         */
        else {
            // User ID associated with the order?
            if (empty($member_id)) {
                // Logged in?
                $session = new session();
                $ses = $session->check_session();
                if ($ses['error'] == '1') {
                    // Link tracking?
                    if ($track['error'] != '1') {
                        $mtype = $track['user_type'];
                        $member_id = $track['user_id'];
                    } // All else fails: make a contact.
                    else {
                        // Auto-register user?
                        $found_reg = false;
                        foreach ($use_order['components'] as $item) {
                            if ($item['data']['auto_register'] == '1') {
                                $found_reg = true;
                                break;
                            }
                        }

                        if ($found_reg) {
                            $memData = $this->billing;

                            // To force update on first login.
                            $memData['last_updated'] = '2002-05-13 00:00:00';
                            $memData['username'] = $memData['email'];
                            $memData['password'] = strtolower($memData['last_name'] . $force_session);

                            unset($memData['cc_number']);
                            unset($memData['cvv']);
                            unset($memData['card_exp_yy']);
                            unset($memData['card_exp_mm']);

                            $user = new user;
                            $exists = $user->get_id_form_username($memData['email']);
                            if (! empty($exists)) {
                                // Assign to user but DO NOT log the user in
                                // for security reasons?
                                $member_id = $exists;
                            } else {
                                $create = $user->create_member(array('member' => $memData));

                                $member_id = $create['member']['data']['id'];

                                // Auto-log the user in
                                $new_member = true;
                            }
                            $mtype = 'member';
                        } else {
                            $mtype = 'contact';
                            $contact = new contact;
                            $data = $this->billing;
                            $data['account'] = $this->get_option('nonmember_cart_buy_acct');
                            $addit = $contact->create($data);
                            $create = $addit;
                            $member_id = $addit['id'];
                        }
                    }
                } else {
                    $mtype = 'member';
                    $member_id = $ses['member_id'];
                }
            } else {
                $mtype = 'member';
            }
        }


        if (!empty($member_id)) {
            $put = 'sales-' . $member_id;
            $this->put_stats($put);
            $put = 'revenue-' . $member_id;
            $this->put_stats($put, $use_order['pricing']['total']);
        }

        $card_id = '';
        if (!empty($this->billing['stored_card_id'])) {
            $card_id = $this->billing['stored_card_id'];
        } else {
            if (! empty($gateway['id'])) {
                $card_id = $this->store_card($gateway, $member_id);
            }
            else if ($this->order['pricing']['total'] <= 0 && ! $this->find_subscription_in_cart() && $this->order['data']['need_shipping'] != '1') {
                $card_id = $this->store_card_no_method();
            }
        }
        /**
         * Dependency Forms
         * We need to transfer the forms to the
         * correct user if an invoice was
         * requested.
         * User skip dependency forms?
         */
        if (!empty($_COOKIE['zen_forms_later'])) {
            // inform user about forms...
            // check for dependency requirements.
            $dependency_forms = '';
        } else {
            $dependency_forms = $this->transfer_dependencies($use_order['data']['id'], $member_id, $mtype);
        }
        if (empty($force_date)) {
            $force_date = current_date();
        }
        /**
         * Create an invoice
         */
        $invoice_product = '0';
        $invoice_paid = '0';
        $invoice_id = '';
        $invoice_total = '0';
        if ($status == '2') {
            $invoice_id = $this->convert_order_to_invoice($force_session, $use_order, $member_id, $mtype);
            /**
             * History addition
             */
            $history = $this->add_history('invoice_requested', '2', $member_id, '', $invoice_id, '');
        } /**
         * Update the order
         * Or delete it if an invoice
         */
        else {

            $sub_overview = '';

            // Invoice product?
            if (!empty($_COOKIE['zen_invoice'])) {
                $data = explode('|||', $_COOKIE['zen_invoice']);
                $invoice_id = $data['0'];
                $invoice_product = $data['1'];

                $invoice = new invoice();
                $getInvoice = $invoice->get_invoice($invoice_id);
                if (! empty($getInvoice['data']['sub_id'])) {
                    $sub_overview = $getInvoice['data']['sub_id'];
                }
            }

            // Tracking milestone?
            if ($track['error'] != '1') {
                $connect->tracking_activity('order', $use_order['data']['id'], $use_order['pricing']['total']);
            }

            $put = 'sales';
            $this->put_stats($put);

            if ($use_order['data']['need_shipping'] == '1') {
                $ship_in = $this->store_shipping();
            }

            $q1 = $this->update("
                UPDATE `ppSD_cart_sessions`
                SET
                    `status`='" . $this->mysql_clean($status) . "',
                    `payment_gateway`='" . $this->mysql_clean($gateway['id']) . "',
                    `gateway_order_id`='" . $this->mysql_clean($gateway['order_id']) . "',
                    `gateway_resp_code`='" . $this->mysql_clean($gateway['resp_code']) . "',
                    `member_type`='" . $this->mysql_clean($mtype) . "',
                    `member_id`='" . $this->mysql_clean($member_id) . "',
                    `date_completed`='" . $this->mysql_clean($force_date) . "',
                    `card_id`='" . $this->mysql_clean($card_id) . "',
                    `gateway_msg`='" . $this->mysql_clean($gateway['msg']) . "',
                    `invoice_id`='" . $this->mysql_clean($invoice_id) . "'
                WHERE
                    `id`='" . $this->mysql_clean($force_session) . "'
                LIMIT 1
            ");

            // For some reason we need to do this.
            // It isn't ideal, but it prevents
            // duplicates within the table which
            // cause issues on receipt and stats.
            $q2d = $this->delete("
                DELETE FROM `ppSD_cart_session_totals`
                WHERE `id`='" . $this->mysql_clean($force_session) . "'
            ");

            // Now add the totals into the table.
            $q2 = $this->insert("
                INSERT INTO `ppSD_cart_session_totals` (
                    `id`,
                    `shipping`,
                    `tax`,
                    `tax_rate`,
                    `total`,
                    `savings`,
                    `subtotal`,
                    `subtotal_nosave`,
                    `gateway_fees`
                )
                VALUES (
                    '" . $this->mysql_clean($force_session) . "',
                    '" . $this->mysql_clean($use_order['pricing']['shipping']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['tax']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['tax_rate']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['total']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['savings']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['subtotal']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['subtotal_nosave']) . "',
                    '" . $this->mysql_clean($gateway['fee']) . "'
                )
            ");

            $put = 'revenue';
            $this->put_stats($put, $use_order['pricing']['total'], 'add', $force_date);
            if ($use_order['pricing']['savings'] > 0) {
                $put = 'savings';
                $this->put_stats($put, $use_order['pricing']['savings'], 'add', $force_date);
            }
            if ($use_order['pricing']['shipping'] > 0) {
                $put = 'shipping';
                $this->put_stats($put, $use_order['pricing']['shipping'], 'add', $force_date);
            }
            if ($use_order['pricing']['tax'] > 0) {
                $put = 'tax';
                $this->put_stats($put, $use_order['pricing']['tax'], 'add', $force_date);
            }
            if ($gateway['fee'] > 0) {
                $put = 'fees';
                $this->put_stats($put, $gateway['fee'], 'add', $force_date);
            }
            /**
             * Apply product settings
             * Except for invoiced items.
             */

            $show_products = '';
            $products_bought = array();
            foreach ($use_order['components'] as $item) {
                // Check if invoice product
                // was purchased
                if ($invoice_product == $item['data']['id']) {
                    $invoice_paid = '1';
                    $invoice_total = $item['pricing']['plain_unit'];
                }
                $products_bought[] = $item['data']['id'];
                // For success and email pages,
                // build the product entry.
                $show_products .= $this->build_product_block($item, '0');
                // Apply product settings
                if (!empty($member_id)) {
                    $this->apply_product_settings_to_user($item['data']['id'], $member_id);
                }
                // Physical?
                if ($item['data']['physical'] == '1') {
                    $this->update_stock($item['data']['id'], $item['pricing']['qty']);
                }
                // Create subscription?
                if ($item['data']['type'] != '1' && $gateway != 'gw_paypal') {
                    if ($skip_subs_invoices != '1') {
                        $sub_class = new subscription;
                        $sub = $sub_class->create_subscription(
                            $item,
                            $use_order['data']['id'],
                            $member_id, $card_id, '0', '', $gateway, $mtype, $item['data']['price'],
                            '',
                            $item['selected_option']);
                    }
                } else {
                    // Invoice payment
                    if (! empty($sub_overview)) {
                        $sub = array('id' => $sub_overview);
                    } else {
                        $sub = array('id' => '');
                    }
                }

                // Add to sales logs
                $add_sale = $this->add_sale($force_session, $item['data']['id'], $item['pricing'], $sub['id'], 1, $item['selected_option']);
            }
            // Paying an invoice?
            if (!empty($_COOKIE['zen_invoice']) && $invoice_paid == '1') {
                $invoice = new invoice;
                $apply = $invoice->apply_payment($invoice_id, $invoice_total, $force_session);
            }
            if ($skip_thank_you != '1') {
                // E-mail Receipt
                $this->send_receipt($use_order['data']['id'], $member_id, $mtype);
            }
            /**
             * History addition
             */
            $history = $this->add_history('purchase', '2', $member_id, '', $use_order['data']['id'], '');
            // Re-cache
            $redo_cache = $this->get_order($force_session, '0', '1');
        }
        /**
         * Savings code?
         */
        if (!empty($use_order['data']['code'])) {
            $q4 = $this->insert("
                INSERT INTO `ppSD_cart_coupon_codes_used` (`order_id`,`member_id`,`code`,`savings`,`date`)
                VALUES (
                    '" . $this->mysql_clean($force_session) . "',
                    '" . $this->mysql_clean($member_id) . "',
                    '" . $this->mysql_clean($use_order['data']['code']) . "',
                    '" . $this->mysql_clean($use_order['pricing']['savings']) . "',
                    '" . $this->mysql_clean($force_date) . "'
                )
            ");
            $put = 'coupon_usage';
            $this->put_stats($put, '1', 'add', $force_date);
            $put = 'coupon_usage-' . $use_order['data']['code'];
            $this->put_stats($put, '1', 'add', $force_date);
            $put = 'coupon_savings-' . $use_order['data']['code'];
            $this->put_stats($put, $use_order['pricing']['savings'], 'add', $force_date);
        }
        /**
         * Transfer items to "live" sales
         */

        /**
         * Affiliate Commission?
         */
        if (!empty($_COOKIE['zen_affiliate'])) {
            // $aff = new affiliate;
        }
        /**
         * Delete cookie
         */
        $this->delete_cookie('zen_cart');
        $this->delete_cookie('zen_invoice');
        $this->delete_cookie('zen_forms_later');


        $indata = array(
            'id' => $force_session,
            'data' => $use_order['data'],
            'totals' => $use_order['pricing'],
            'card_id' => $card_id,
            'gateway' => $gateway,
            'products_ids' => $products_bought,
            'products' => $use_order['components'],
            'billing' => $this->billing,
            'shipping' => $this->shipping,
        );

        $task = $this->end_task($task_id, '1', '', 'transaction', $products_bought, $indata);

        // Rebuild the order with the updated information.
        $use_order = $this->get_order($force_session, '1');

        if ($new_member) {

            $user = new user();
            $memberData = $user->get_user($member_id);

            if ($memberData['data']['status'] == 'A') {

                // Start the session!
                $task_id = $this->start_task('login', 'user', '', $member_id);

                $session = new session();
                $sesDataBack = $session->create_session($member_id, '0');

                foreach ($memberData['areas'] as $content) {
                    if ($content['type'] == 'folder') {
                        $session->folder_login($sesDataBack, $content['content_id']);
                    }
                }

                $add_login = $user->add_login($member_id, '1', '1', $sesDataBack);

            }

            // End the task
            $indata = array(
                'member_id' => $member_id,
                'login_id'  => $add_login,
            );

            $task   = $this->end_task($task_id, '1', '', 'login', '', $indata);

        }

        /**
         * Show thank you page. Use proper
         * redirect if a return path is specified
         */
        // ----------------------------
        // Registration
        if (!empty($use_order['data']['reg_session'])) {
            $changes = array();
            // Display the correct template
            if ($put_status == 'P') {
                $template = 'reg_activation_code';
            }
            else if ($put_status == 'Y') {
                $template = 'reg_await_activation';
            }
            else if ($put_status == 'S') {
                $last_invoice = $this->get_array("
                    SELECT `id`
                    FROM `ppSD_invoices`
                    WHERE `member_id`='" . $this->mysql_clean($member_id) . "'
                    ORDER BY `date` DESC
                    LIMIT 1
                ");

                $inob = new invoice;
                $invoice = $inob->get_invoice($last_invoice['id']);
                $changes['invoice'] = $invoice['data'];
                $template = 'reg_awaiting_payment';
            } else {
                $template = 'reg_complete';
            }
            // Template
            $wrapper = new template($template, $changes, '1', $member_id);
            echo $wrapper;
            exit;
        } // ----------------------------
        // Standard cart purchase
        else {
            if (!empty($use_order['data']['return_path'])) {
                if (strpos($use_order['data']['return_path'], 'http')) {
                    $full = $use_order['data']['return_path'];
                } else {
                    $full = PP_URL . '/' . trim($use_order['data']['return_path'], '/');
                }
                if ($status == '2') {
                    $full .= '&status=S';
                }
                header('Location: ' . $full);
                exit;
            } else {
                if ($status == '2') {
                    if ($skip_thank_you != '1') {
                        $invoice = new invoice;
                        $indata = $invoice->get_invoice($invoice_id);
                        $full = PP_URL . '/pp-cart/invoice.php?id=' . $invoice_id . '&h=' . $indata['data']['hash'];
                        header('Location: ' . $full);
                        exit;
                    }
                } else {
                    // Would only be skipped for something like PayPal IPN
                    if ($skip_thank_you != '1') {
                        // Format forms
                        $forms = $this->format_forms();
                        // Changes
                        $changes = array(
                            'data' => $use_order['data'],
                            'pricing' => $use_order['pricing'],
                            'cart_components' => $show_products,
                            'billing_form' => $forms['billing'],
                            'shipping_form' => $forms['shipping'],
                            'method_form' => $forms['method'],
                            'newMember' => $memData,
                        );
                        $template = new template('cart_receipt', $changes, '1');
                        echo $template;
                        exit;
                    }
                }
            }
        }
    }


    function transfer_dependencies($cart_id, $member_id, $member_type)
    {
        $note = new notes();

        // Build the forms for the emails.
        $dependency_forms = '';

        $q1AF = $this->run_query("
            SELECT
                *
            FROM
                `ppSD_form_submit`
            WHERE
                `user_id`='" . $this->mysql_clean($cart_id) . "'
        ");

        while ($row = $q1AF->fetch()) {

            $dataA = $this->assemble_eav_data($row['id']);

            $this_form = $this->format_eav_data($dataA, $row['id'], $row['form_name']);

            $dependency_forms .= $this_form;

            // Update it
            $q1 = $this->update("
                UPDATE
                    `ppSD_form_submit`
                SET
                    `user_id`='" . $this->mysql_clean($member_id) . "',
                    `user_type`='" . $this->mysql_clean($member_type) . "'
                WHERE
                    `id`='" . $this->mysql_clean($row['id']) . "'
                LIMIT 1
            ");

            // Add the note...
            $add = $note->add_note(array(
                'user_id' => $cart_id,
                'item_scope' => 'transaction',
                'name' => 'Dependency Form: ' . $row['form_name'],
                'note' => $this_form,
                'label' => '2',
                'public' => '1',
            ));

        }

        return $dependency_forms;
    }


    /**
     * Converts an order to an invoice.
     * @param string $force_session Cart session
     * @param array $use_order From $cart->get_order()
     * @param string $member_id Member ID, if any
     * @return string Invoice ID
     */
    function convert_order_to_invoice($force_session, $use_order = '', $member_id = '', $member_type = '')
    {

        if (empty($use_order)) {
            $use_order = $this->get_order($force_session, '0');
        }
        $invoice = new invoice('2');
        // Main data for the invoice
        $indata = array(
            'order_id' => $force_session,
            'member_id' => $member_id,
            'member_type' => $member_type,
        );
        // Totals
        $totals = array(
            'due' => $use_order['pricing']['total'],
            'subtotal' => $use_order['pricing']['subtotal'],
            'paid' => '0.00',
            'credits' => $use_order['pricing']['savings'],
            'shipping' => $use_order['pricing']['shipping'],
            'tax' => $use_order['pricing']['tax'],
            'tax_rate' => $use_order['pricing']['tax_rate'],
        );
        $invoice_id = $invoice->create_invoice($indata, $totals, $this->billing, $this->shipping, '0');
        foreach ($use_order['components'] as $item) {
            $invoice->add_component_product($invoice_id, $item);
        }
        // Savings codes get applied
        // as credits to invoices.
        if (!empty($use_order['data']['code'])) {
            $invoice->add_component_credit($invoice_id, $use_order['pricing']['savings'], $use_order['data']['code']);
        }
        $invoice->send_invoice($invoice_id);
        // Delete the order
        $q1 = $this->delete("
            DELETE FROM `ppSD_cart_sessions`
            WHERE `id`='" . $this->mysql_clean($force_session) . "'
            LIMIT 1
        ");
        return $invoice_id;
    }


    /**
     * Based on $this->shipping
     */
    function store_shipping($updating = '0', $force_id = '')
    {

        $add1 = '';
        $add2 = '';
        $updateA = '';
        $debug = '';
        // Prep statement
        foreach ($this->shipping as $name => $value) {
            $add1 .= ",`$name`";
            $add2 .= ",'" . $this->mysql_clean($value) . "'";
            $updateA .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_cleans($value) . "'";
            $debug .= ",`$name`='" . $value . "'";
        }
        if (!array_key_exists('name', $this->shipping)) {
            $add1 .= ",`name`";
            $add2 .= ",'" . $this->mysql_cleans($this->order['data']['shipping_name']) . "'";
            $updateA .= ",`name`='" . $this->mysql_cleans($this->order['data']['shipping_name']) . "'";
        }
        // Insert
        if ($updating == '1') {
            $updateA = ltrim($updateA, ',');
            $q1 = $this->update("
                UPDATE `ppSD_shipping`
                SET $updateA
                WHERE `cart_session`='" . $this->mysql_cleans($force_id) . "'
                LIMIT 1
            ");
        } else {
            $q = $this->insert("
                INSERT INTO `ppSD_shipping` (`cart_session`$add1)
                VALUES ('" . $this->id . "'$add2)
            ");
        }
    }


    /**
     * Send email receipt
     */
    function send_receipt($force_order, $force_member_id = '', $force_member_type = 'member', $dependency_forms = '')
    {

        // $order = $this->get_order($order_id,'0');
        if (!empty($force_order)) {
            $order = $this->get_order($force_order, '0', '1');
        } else {
            $order = $this->order;
        }
        if (!empty($order['data']['card_id'])) {
            $billing_data = $this->order_card_info($order['data']['card_id']);
            if ($billing_data['error'] == '1') {
                $data = array();
            } else {
                $data = array(
                    'to' => $billing_data['email']
                );
            }
        } else {
            $data = array();
            $billing_data = array();
        }
        if (empty($order['shipping_info'])) {
            $order['shipping_info'] = array(
                'name' => ''
            );
        }
        $show_products = $this->build_product_blocks($order['components'], '0', $order['data']['state'], $order['data']['country']);
        $changes = array(
            'order' => $order['data'],
            'products' => $show_products,
            'billing' => $billing_data,
            'shipping' => $order['shipping_info'],
            'pricing' => $order['pricing'],
            'dependency_forms' => $dependency_forms,
        );
        if (empty($force_member_id)) {
            $force_member_id = $order['data']['member_id'];
            $force_member_type = $order['data']['member_type'];
        }
        $email = new email('', $force_member_id, $force_member_type, $data, $changes, 'cart_receipt');
    }


    /**
     * Format forms
     */
    function format_forms()
    {

        // Establish method data
        if ($this->method == 'cc') {
            $f1 = new field('billing', '0', '', '', '0', '', '1');
            $method_form = $f1->generate_form('payment_form', $this->billing);
        } else if ($this->method == 'check') {
            $f1 = new field('echeck', '0', '', '', '0', '', '1');
            $method_form = $f1->generate_form('check_form', $this->billing);
        } else if ($this->method == 'invoice') {
            $f1 = new field('invoice', '0', '', '', '0', '', '1');
            $method_form = $f1->generate_form('invoice_form', $this->billing);
        } else {
            $method_form = '';
        }
        // Billing
        $f3 = new field('billing', '0', '', '', '0', '', '1');
        $billing_form = $f3->generate_form('billing_form', $this->billing);
        // Shipping
        if (!empty($this->shipping)) {
            $f2 = new field('shipping', '0', '', '', '0', '', '1');
            $shipping_form = $f2->generate_form('shipping_form', $this->shipping);
        } else {
            $shipping_form = '';
        }
        // Return some stuff
        return array(
            'billing' => $billing_form,
            'shipping' => $shipping_form,
            'method' => $method_form
        );
    }


    /**
     * Store a billing details after
     * a transaction is completed.
     * Set billing $this->set_billing($_POST['billing'])
     * @param array $gateway_reply_data Reply from the gateway call
     */
    function store_card($gateway_reply_data, $member_id = '')
    {

        if (!empty($member_id)) {
            $this->billing['member_id'] = $member_id;
        }
        $add_id = '';
        $gateway_dets = $this->get_gateways('', $gateway_reply_data['id']);
        // Gateway info?
        if (!empty($gateway_reply_data['gateway_id_1'])) {
            $this->billing['gateway_id_1'] = $gateway_reply_data['gateway_id_1'];
        }
        if (!empty($gateway_reply_data['gateway_id_2'])) {
            $this->billing['gateway_id_2'] = $gateway_reply_data['gateway_id_2'];
        }
        $this->billing['gateway'] = $gateway_reply_data['id'];
        if (!empty($member_id)) {
            $this->billing['member_id'] = $member_id;
        }
        // Credit card
        // Determine if this gateway
        // requires full card storage
        // or just the last four.
        if (!empty($this->billing['cc_number'])) {
            if ($gateway_dets['0']['local_card_storage'] == '1') {
                $add_id = $this->add_cardin($this->billing, '1');
            } else {
                $add_id = $this->add_cardin($this->billing, '0');
            }
        } // Other
        // Store what we have but
        // really nothing useful
        // for subscriptions, etc.
        else {
            $add_id = $this->add_cardin($this->billing, '0');
        }
        return $add_id;
    }


    /**
     * Confirm order salt
     */
    function confirm_salt($order_id, $salt)
    {

        $q1 = $this->get_array("
            SELECT COUNT(*) FROM `ppSD_cart_sessions`
            WHERE `id`='" . $this->mysql_clean($order_id) . "' AND `salt`='" . $this->mysql_clean($salt) . "'
        ");
        return $q1['0'];
    }


    /**
     * For display on the website,
     * list of all products currently
     * in a user's cart.
     */
    function build_product_blocks($product_array = '', $allow_edit = '1', $state = '', $country = '', $active_order = '1')
    {
        if (empty($product_array)) {
            $product_array = $this->get_products_in_cart($product_array, $active_order, $state, $country);
        }
        
        $all_together_now = '';

        foreach ($product_array as $an_item) {
            $all_together_now .= $this->build_product_block($an_item, $allow_edit);
        }

        return $all_together_now;
    }


    function build_product_block($an_item, $allow_edit = '1')
    {
        if ($allow_edit == '1') {
            $template_name = 'cart_product_entry_edit';
        } else {
            $template_name = 'cart_product_entry';
        }

        $changes = array(
            'pricing' => $an_item['pricing'],
            'display' => $an_item['display'],
            'in_id' => $an_item['id'],
        );

        $changes = array_merge($changes, $an_item['data']);
        $template = new template($template_name, $changes, '0');

        return $template;
    }


    /**
     * For display on the website,
     * list of all products currently
     * in a user's cart, small panel
     * displays for the checkout page.
     */
    function format_small_panels($product_array, $active_order = '1')
    {

        if (empty($product_array)) {
            $product_array = $this->get_products_in_cart($product_array, $active_order);
        }
        $all_together_now = '';
        foreach ($product_array as $an_item) {
            $changes = array(
                'pricing' => $an_item['pricing'],
                'display' => $an_item['display'],
            );
            $changes = array_merge($changes, $an_item['data']);
            $template = new template('cart_product_entry_small', $changes, '0');
            $all_together_now .= $template;
        }
        return $all_together_now;
    }


    /**
     * Get card type.
     */
    function get_card_type($cardNumber)
    {

        return get_cc_type($cardNumber);
    }


    /**
     * Takes a product's settings and applies
     * them to a user's account.
     * aProduct = product array
     */
    function apply_product_settings_to_user($aProduct, $member_id)
    {
        $reply = array();
        // Username working?
        if (empty($member_id)) {
            return array('error' => '1', 'error_details' => 'No member ID provided.');
        }

        // Make sure we're using a product array
        // and not just a product ID
        if (!is_array($aProduct)) {
            $aProduct = $this->get_product($aProduct);
        }

        if (empty($aProduct['data']['id'])) {
            return array('error' => '1', 'error_details' => 'Product does not exist.');
        }

        $user = new user;
        foreach ($aProduct['content'] as $grants_access_to) {
            if ($grants_access_to['type'] == 'content') {
                $user->add_content_access($grants_access_to['grants_to'], $member_id, $grants_access_to['timeframe']);
            } else {
                $user->add_newsletter_access($grants_access_to['grants_to'], $member_id, $grants_access_to['timeframe']);
            }
        }

        // Member Type
        if (! empty($aProduct['data']['member_type'])) {
            $user->update_member_type($member_id, $aProduct['data']['member_type'], '', '1');
        }

        return $reply;

    }


    /**
     * Add sale to billing logs
     */
    function add_sale($order_id, $product_id, $product_pricing, $subscription_id, $status = '1', $selected_options = [])
    {
        $put = 'product_sale-' . $product_id;
        $this->put_stats($put);
        
        $total = $product_pricing['plain_unit'] * $product_pricing['qty'];
        
        $put = 'product_income-' . $product_id;
        $this->put_stats($put, $total);

        $option1 = (! empty($selected_options['option1'])) ? $selected_options['option1'] : null;
        $option2 = (! empty($selected_options['option2'])) ? $selected_options['option2'] : null;
        $option3 = (! empty($selected_options['option3'])) ? $selected_options['option3'] : null;
        $option4 = (! empty($selected_options['option4'])) ? $selected_options['option4'] : null;
        $option5 = (! empty($selected_options['option5'])) ? $selected_options['option5'] : null;

        $q = $this->insert("
            INSERT INTO `ppSD_cart_items_complete` (
                `cart_session`,
                `unit_price`,
                `qty`,
                `status`,
                `product_id`,
                `subscription_id`,
                `tax`,
                `tax_rate`,
                `savings`,
                `date`,
                `option1`,
                `option2`,
                `option3`,
                `option4`,
                `option5`
            )
            VALUES (
                '" . $this->mysql_clean($order_id) . "',
                '" . $this->mysql_clean($product_pricing['plain_unit']) . "',
                '" . $this->mysql_clean($product_pricing['qty']) . "',
                '" . $this->mysql_clean($status) . "',
                '" . $product_id . "',
                '" . $subscription_id . "',
                '" . $this->mysql_clean($product_pricing['tax']) . "',
                '" . $this->mysql_clean($product_pricing['tax_rate']) . "',
                '" . $this->mysql_clean($product_pricing['savings']) . "',
                '" . current_date() . "',
                '" . $this->mysql_clean($option1) . "',
                '" . $this->mysql_clean($option2) . "',
                '" . $this->mysql_clean($option3) . "',
                '" . $this->mysql_clean($option4) . "',
                '" . $this->mysql_clean($option5) . "'
            )
        ");

        return $q;
    }


    /**
     * Check product cart dependencies
     *
     * NOT ACTUALLY WORKING OR USED IN ZENBERSHIP
     */
    function check_dependencies($product, $qty = '', $simple = '0')
    {
        $user = new user;
        $session = $user->check_session();
        $session_user = $session['username'];
        if (empty($qty)) {
            $qty = '1';
        }
        // Start by presuming there is an error
        $proceed = "0";
        // Now loop rules
        if ($simple != '1') {
            $STH = $this->run_query("SELECT * FROM `ppSD_cart_dependencies` WHERE `product`='" . $this->mysql_clean($product) . "'");
            while ($row = $STH->fetch()) {
                $found_rule = '1';
                // Field required
                if ($row['type'] == "1") {
                    $proceed = "1";
                    $this->create_cookie('ppSD2_force_fields', '1');
                } // Force add product
                else if ($row['type'] == "2") {
                    $proceed = "1";
                    if ($row['add_qty'] == '0') {
                        $row['add_qty'] = $qty;
                    }
                    $add = $this->add($row['add_product'], $row['add_qty']);
                }
            }
        }
        // Requirements
        $last_prod = array();
        $found_one_product = '0';
        $STH = $this->run_query("SELECT * FROM `ppSD_cart_dependencies` WHERE `product`='" . $this->mysql_clean($product) . "' AND `type`='3'");
        while ($row = $STH->fetch()) {
            $last_prod[] = $row['add_product'];
            $found_3_rule = '1';
            $found_rule = '1';
            $check = $this->get_array("
                SELECT COUNT(*)
                FROM `ppSD_cart_items`
                WHERE `cart_session`='" . $this->mysql_clean($_COOKIE['ppSD_cart']) . "' AND `product_id`='" . $row['add_product'] . "'
            ");
            if ($check['0'] > 0) {
                $found_one_product = '1';
            }
            // Logged in?
            // Did this user buy this product
            // in the past, if so he/she qualifies.
            if (!empty($session_user)) {
                $q = $this->get_array("SELECT COUNT(*) FROM `ppSD_charge_log` WHERE `username`='$session_user' AND `product`='" . $row['add_product'] . "'");
                if ($q['0'] > 0) {
                    $found_one_product = '1';
                }
            }
        }
        if ($found_one_product != '1' && $found_3_rule == '1') {
            $show_options = '';
            foreach ($last_prod as $option) {
                $product_name = $this->get_array("SELECT `no_list_in_cart`,`name` FROM `ppSD_products` WHERE `id`='" . $option . "' LIMIT 1");
                if ($product_name['no_list_in_cart'] != '1') {
                    $price = $this->format_product_price($option);
                    if ($simple == '1') {
                        $url = urlencode(PP_URL . "/catalog.php?action=view_product&id=$product");
                        $link_both = PP_URL . "/cart.php?url=$url&action=add&id=" . $option;
                        $show_options .= "<li><b>" . $product_name['name'] . "</b> ($price: <a href=\"$link_both\">Add to cart</a> | <a href=\"" . PP_URL . "/catalog.php?action=view_product&id=$option\" target=\"_blank\">More Information</a>)</li>";
                    } else {
                        $link_both = PP_URL . "/cart.php?action=package&id=" . $option . "," . $product;
                        $show_options .= "<li>Add with \"" . $product_name['name'] . "\" ($price: <a href=\"$link_both\">Add to cart</a> | <a href=\"" . PP_URL . "/catalog.php?action=view_product&id=$option\" target=\"_blank\">More Information</a>)</li>";
                    }
                }
            }
            $proceed = "This product requires that you purchase one of the following:<ul id=\"ppsd2_cart_depend_options\">" . $show_options . "</ul>";
        } else {
            $proceed = "1";
        }
        if ($found_rule != '1') {
            $proceed = '1';
        }
        return $proceed;
    }


    /**
     * Formats a product's price.
     * $50.00/month/year/week
     * $50.00 for 7 days
     * Then $350.00/month, for 10 installments
     * $override_rpcie -> tier volume pricing
     */
    function format_product_price($the_product, $override_price = '', $tier_discount = '', $plain = '0')
    {

        // Product information
        if (!is_array($the_product)) {
            $the_product = $this->get_product($the_product);
        }
        if (!empty($override_price)) {
            $use_price = '<strike>' . place_currency($the_product['data']['price']) . '</strike> ' . place_currency($override_price);
            if (!empty($tier_discount)) {
                $use_price .= '<div class="zen_pricetag"><div class="zen_pricetag-triangle"></div><div class="zen_pricetag-rectangle">-' . $tier_discount . '</div></div>';
            }
            $plain_price = $override_price;
        } else {
            $use_price = place_currency($the_product['data']['price']);
            $plain_price = $the_product['data']['price'];
        }
        $put_price = '';
        // Trial product?
        if ($the_product['data']['type'] == '3') {
            $timeframeA = format_timeframe($the_product['data']['trial_period']);
            $put_price .= place_currency($the_product['data']['trial_price']);
            $plain_price = $the_product['data']['trial_price'];
            // Non-repeating trial
            if ($the_product['data']['trial_repeat'] == '1' || $the_product['data']['trial_repeat'] == '0') {
                $put_price .= ' for ' . $timeframeA['unit'] . ' ' . $timeframeA['unit_word'];
                if ($timeframeA['unit'] > 1) {
                    $put_price .= 's';
                }
                $put_price .= ', then ';
            } // Repeating trial
            else {
                if ($timeframeA['unit'] == 1) {
                    $put_price .= '/' . $timeframeA['unit_word'];
                } else {
                    $put_price .= ' every ' . $timeframeA['unit'] . ' ' . $timeframeA['unit_word'];
                    if ($timeframeA['unit'] > 1) {
                        $put_price .= 's';
                    }
                }

                if (empty($the_product['data']['trial_repeat']) || $the_product['data']['trial_repeat'] == '1') {
                    $the_product['data']['trial_repeat'] = '1';
                    $word = 'installment';
                } else {
                    $word = 'installments';
                }

                $put_price .= ' for ' . $the_product['data']['trial_repeat'] . ' ' . $word . ', then ';
            }

        }
        if ($plain == '1') {
            return $plain_price;
        }
        // Subscription product?
        if ($the_product['data']['type'] == '2' || $the_product['data']['type'] == '3') {
            $put_price .= format_timeframe_full($use_price, $the_product['data']['renew_timeframe'], $the_product['data']['renew_max']);
            /*
            $timeframe = format_timeframe($the_product['data']['renew_timeframe']);
            $put_price .= $use_price;
            
            // Once per month on day
            // So 1st of every month: 777010000000
            if ($timeframe['unit_word'] == 'sp_month') {
                $put_price .= ' on the ' . $timeframe['unit'] . ' of every month';
            }
            // Once per year
            // So January 1st: 888010100000
            else if ($timeframe['unit_word'] == 'sp_year') {
                $put_price .= ' on ' . $timeframe['unit'] . ' every year';
            }
            // Standard recurring
            else {
                if ($timeframe['unit'] == 1) {
                    $put_price .= '/' . $timeframe['unit_word'];
                } else {
                    if ($timeframe['unit'] == '7' && $timeframe['unit_word'] == 'day') {
                        $put_price .= '/week';
                    } else {
                        $put_price .= ' every ' . $timeframe['unit'] . ' ' . $timeframe['unit_word'];
                        if ($timeframe['unit'] > 1) {
                            $put_price .= 's';
                        }
                    }
                }
                if ($the_product['data']['renew_max'] > 0) {
                    $put_price .= ', for ' . $the_product['data']['renew_max'] . ' installment';
                    if ($the_product['data']['renew_max'] > 1) {
                        $put_price .= 's';
                    }
                }
            }
            */
        }
        if ($the_product['data']['type'] != '2' && $the_product['data']['type'] != '3') {
            $put_price = $use_price;
        }
        return $put_price;
    }


    /**
     * Breadcrumbs
     */
    function breadcrumbs($current_category)
    {

        $delimiter = $this->get_option('crumb_divider');
        $crumbs = array();
        $category = $this->get_category($current_category);
        if ($current_category == '1') {
            $category = $this->get_category($current_category);
            $url = PP_URL . '/catalog.php?category=' . $category['id'];
            $full_crumbs = '<a href="' . $url . '">' . $category['name'] . '</a>';
            $crumbs[] = $full_crumbs;
        } else {
            $inbase = 0;
            $max = 0;
            $next_cat = $category['id'];
            while ($inbase == 0) {
                $max++;
                if ($max > 10) {
                    break;
                }
                $category = $this->get_category($next_cat);
                $url = PP_URL . '/catalog.php?category=' . $category['id'];
                $full_crumbs = '<a href="' . $url . '">' . $category['name'] . '</a>';
                $crumbs[] = $full_crumbs;
                if ($category['subcategory'] == '0') {
                    $inbase = 1;
                }
                $next_cat = $category['subcategory'];
            }
        }
        $reverse = array_reverse($crumbs);
        $imp = implode(' ' . $delimiter . ' ', $reverse);
        return $imp;
    }


    function get_subcategories($category)
    {
        // Start by checking session
        $ses = new session();
        $session = $ses->check_session();

        // Continue
        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_cart_categories`
            WHERE `subcategory`='" . $this->mysql_clean($category) . "' AND `hide`!='1'
            ORDER BY `name` ASC
        ");
        $skip = 0;
        $list = array();
        while ($row = $STH->fetch()) {
            if ($row['members_only'] == '1') {
                if ($ses['error'] == '1' && empty($this->order['member_id'])) {
                    $skip = '1';
                }
            }
            if ($skip != '1') {
                $list[] = $row;
            }
        }
        return $list;
    }

    /**
     * Render a list of subcategories
     */
    function render_subcategories($category)
    {
        // Start by checking session
        $ses = new session();
        $session = $ses->check_session();

        // Conitnue
        $cols = $this->get_option('catalog_cate_cols');
        $col_width = floor(100 / $cols);
        $STH = $this->run_query("
            SELECT *
            FROM `ppSD_cart_categories`
            WHERE `subcategory`='" . $this->mysql_clean($category) . "' AND `hide`!='1'
            ORDER BY `name` ASC
        ");

        $list = '';
        $total = 0;
        $add_up = 0;
        while ($row = $STH->fetch()) {

            $skip = 0;
            if ($row['members_only'] == '1') {
                if ($ses['error'] == '1' && empty($this->order['member_id'])) {
                    $skip = '1';
                }
            }
            if ($skip != '1') {
                $url = PP_URL . '/catalog.php?category=' . $row['id'];
                $add_up++;
                if ($cols == 1 || $cols == 0) {
                    $style = "";
                } else {
                    if ($add_up == $cols) {
                        $style = "float:left;width:" . $col_width . "%;";
                    } else {
                        $use_width = $col_width - 1;
                        $style = "float:left;width:" . $use_width . "%;margin-right:1%;";
                    }
                }
                $list .= '<!--start--><li style="' . $style . '"><a href="' . $url . '">' . $row['name'] . '</a></li><!--end-->' . "\n";
                if ($add_up == $cols) {
                    $add_up = 0;
                    $list .= '<li style="clear:both;"></li>' . "\n";
                }
                $total++;
            }

        }

        if (empty($list)) {
            $list .= '<li class="empty">' . $this->get_error('S023') . '</li>' . "\n";
        } else {
            $list .= '<li style="clear:both;"></li>';
        }

        return array($list, $total);
    }


    /**
     * Render a list of subcategories
     */
    function catalog_block($id, $type = 'catalog_entry')
    {

        if (empty($type)) {
            $type = 'catalog_entry';
        }
        if (empty($cols)) {
            $cols = '1';
        }
        // Product
        $skip = 0;
        $product = $this->get_product($id);
        $class = '';
        if ($product['data']['featured'] == '1') {
            $class .= ' zen_featured_product';
        }
        if ($product['data']['onsale'] == '1') {
            $class .= ' zen_featured_sale';
        }
        if ($product['data']['members_only'] == '1') {
            $ses = new session();
            $session = $ses->check_session();
            if ($session['error'] == '1' && empty($this->order['member_id'])) {
                $skip = '1';
            }
        }
        if ($skip != '1') {
            // Render the template
            $changes = array(
                'data' => $product['data'],
                'options' => $product['options'],
                'images' => $product['uploads'],
                'class' => $class,
            );
            $template = new template($type, $changes, '0');
        } else {
            $template = '';
        }
        return $template;
    }


    /**
     * Get a cart category
     */
    function get_category($id)
    {
        $q = $this->get_array("
            SELECT * FROM `ppSD_cart_categories`
            WHERE `id`='" . $this->mysql_clean($id) . "'
        ");
        if (empty($q['id'])) {
            $q = array(
                'error' => '1',
                'error_details' => 'Could not find category.'
            );
        } else {
            $q['error'] = '0';
            $q['error_details'] = '';
        }
        return $q;
    }


    /**
     * Up product stats.
     */
    function up_product_stats($id)
    {

        $put = 'product_views-' . $id;
        $this->put_stats($put);
        /*
        $q = $this->insert("
            INSERT INTO `ppSD_product_views` (`product_id`,`date`,`ip`)
            VALUES ('" . $this->mysql_clean($id) . "','" . current_date() . "','" . $this->mysql_clean(get_ip()) . "')
        ");
        */
    }


    /**
     * This is used to run a transaction, generally from the admin
     * control panel.
     *
     * @param array $products ID=>qty
     * @param array $card
     *                      card_id = 'ID_HERE'
     *                      card_type = 'existing_card'
     *                          OR
     *                      card_type = 'new_card'
     *                      card_id = ''
     *                      cc_number
     *                      card_exp_mm
     *                      card_exp_yy
     *                      cvv
     *                      first_name
     *                      last_name
     *                      address_line_1
     *                      address_line_2
     *                      city
     *                      state
     *                      zip
     *                      country
     *                          OR
     *                      card_type = 'no_card'
     * @param string $savings_code
     * @param string $user_id
     * @param string $force_id Force an ID for this order.
     */
    function run_order($products, $card, $savings_code = '', $user_id = '', $force_id = '')
    {

        // Order ID
        if (!empty($force_id)) {
            $cid = $force_id;
        } else {
            $cid = generate_id($this->get_option('order_id_format'));
        }
        // Begin work of the order
        $this->id = $cid;
        $this->empty_cart();
        $start_session = $this->start_session($user_id, '1', $cid);
        // Add products
        $total_items = 0;
        foreach ($products as $id => $qty) {
            if ($qty > 0) {
                $prod_get = $this->get_product($id);
                $add = $this->add($id, $qty, '', $user_id, $savings_code, $cid);
                $total_items++;
            }
        }
        $return = array();
        if ($total_items <= 0) {
            $return['error'] = '1';
            $return['error_details'] = 'No products submitted.';
        } else {
            $do_order = '0';
            $info = $this->get_order($cid, '1');
            if ($card['card_type'] == 'new_card') {
                $this->set_billing($card);
                $do_order = '1';
                $gateway = $this->get_gateways('1');
                $use_gateway = $gateway['0']['code'];
            } else if ($card['card_type'] == 'existing_card') {
                $card_id = $card['card_id'];
                $card = $this->get_card($card_id);
                $use_gateway = $card['gateway'];
                $do_order = '1';
            } else {
                $do_order = '0';
            }
            if ($do_order == '1') {
                $gateway = $this->get_gateways('1');
                if ($gateway['0']['api'] != '1') {
                    $return['error'] = '1';
                    $return['error_details'] = 'Your payment gateway is not an API. You will not be able to create a subscription with a credit card for this user.';
                } else {
                    $runit = new $use_gateway($info['pricing']['total'], $card, '', '');
                    $charge = $runit->charge($card);
                    if ($charge['error'] == '1') {
                        $return['error'] = '1';
                        $return['error_details'] = 'Credit Card error: ' . $charge['msg'] . ' (' . $charge['resp_code'] . ')';
                    } else {
                        $complete = $this->complete_order($cid, $charge, '1', '1');
                        $return['error'] = '0';
                        $return['error_details'] = 'Order completed.';
                    }
                }
            } else {
                $charge = $this->empty_charge_array();
                $complete = $this->complete_order($cid, $charge, '1', '1');
                $return['error'] = '0';
                $return['error_details'] = 'Order completed, but no transaction was run.';
            }
        }
        return $return;
    }


    /*
     * Generate an empty charge array.
     * The charge array is usually built
     * following a order by the gateway
     * files, but for invoices and admin
     * additions we need to generate an
     * empty one.
     */
    function empty_charge_array($id = '')
    {

        if (empty($id)) {
            $id = $this->id;
        }
        return array(
            'error' => '0',
            'msg' => '',
            'resp_code' => '',
            'id' => '',
            'gateway_id_1' => '',
            'gateway_id_2' => '',
            'zen_order_id' => $id,
            'fee' => '0.00',
            'order_id' => '',
            'cust_id' => '',
        );
    }


    function duplicate($id, $name = '')
    {

        global $employee;
        $new_id = generate_id($this->get_option('product_id_format'));
        $special = array(
            'id' => $new_id,
            'owner' => $employee['id'],
            'hide' => '1',
            'created' => current_date(),
            'name' => $name . ' (copy)',
        );
        $copy = $this->copy_row('ppSD_products', $id, 'id', $special);
        // Event uploads
        $specialB = array(
            'product_id' => $new_id,
        );
        $copy = $this->copy_rows('ppSD_products_options', $id, 'item_id', 'id', $specialB);
        $copy = $this->copy_rows('ppSD_products_options_qty', $id, 'item_id', 'id', $specialB);
        $copy = $this->copy_rows('ppSD_products_tiers', $id, 'item_id', 'id', $specialB);
        // Uploads
        $id_format = 'random';
        $id_length = '30';
        $specialB = array(
            'item_id' => $new_id,
        );
        $copy = $this->copy_rows('ppSD_uploads', $id, 'item_id', 'id', $specialB, '1', $id_format, $id_length);
        return $new_id;
    }
}
