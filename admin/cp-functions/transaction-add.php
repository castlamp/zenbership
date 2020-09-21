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

// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';
} else {
    $type = 'add';
}

$task = 'transaction-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// Event Query
// $query_form = $admin->query_from_fields($_POST['order'],$type,$ignore,$primary);
$cart = new cart;
if ($type == 'add') {
    // New user?
    if ($_POST['member_type'] == 'new_user') {
        // Create a contact
        $contact     = new contact;
        $data        = array(
            'source'     => '10',
            'account'    => 'NONMEM01',
            'first_name' => $_POST['user']['first_name'],
            'last_name'  => $_POST['user']['last_name'],
            'email'      => $_POST['user']['email'],
        );
        $din         = $contact->create($data);
        $member_id   = $din['id'];
        $member_type = 'contact';

    } else {
        $user   = new user;
        $member = $user->get_user($_POST['order']['member_id']);
        if (!empty($member['data']['id'])) {
            $member_id   = $member['data']['id'];
            $member_type = 'member';

        } else {
            echo "0+++Could not find member.";
            exit;

        }

    }
    // New products?
    if (!empty($_POST['comps'])) {
        $add_product_array = array();
        $prods_in_order    = array();
        foreach ($_POST['comps'] as $item) {
            if (empty($item['qty'])) {
                $item['qty'] = '1';
            }
            if ($item['id'] == 'new') {
                if (empty($item['name'])) {
                    $item['name'] = 'Untitled Product';
                }
                if (!is_numeric($item['price'])) {
                    echo "0+++Product price incorrect. Please enter a number.";
                    exit;
                }
                $data                = array(
                    'name'  => $item['name'],
                    'price' => $item['price'],
                    'qty'   => $item['qty'],
                );
                $add_product_array[] = $data;

            } else {
                $prod             = $cart->get_product($item['id']);
                $price            = $cart->get_product_price($item['id']);
                $order_comps      = array(
                    'product_id'      => $item['id'],
                    'name'            => $item['name'],
                    'price'           => $price,
                    'subscription_id' => '',
                    'qty'             => $item['qty']
                );
                $prods_in_order[] = $order_comps;

            }

        }

    }
    if ($_POST['ship_yesno'] == '1') {
        $shipping = '1';
        $physical = '1';
        $ship     = $cart->get_shipping_rule($_POST['shipping']['id']);
        if (empty($ship['name']) || $ship['type'] != 'flat') {
            echo "0+++Shipping option could not be found or is not a flat-rate shipping option.";
            exit;
        } else {
            $_POST['shipping']['name'] = $ship['name'];
            $cart->set_shipping($_POST['shipping']);
        }
    } else {
        $shipping = '0';
        $physical = '0';
    }

    if ($_POST['tax_exempt'] == '1') {
        $tax_exempt = '1';
    } else {
        $tax_exempt          = '0';
        $force_update_fields = array(
            'state'   => $_POST['order']['state'],
            'country' => $_POST['order']['country'],
        );
    }

    if (!empty($_POST['order']['code'])) {
        $savings_code = $_POST['order']['code'];
    } else {
        $savings_code = '';
    }

    if (!empty($add_product_array)) {
        foreach ($add_product_array as $prodIn) {
            $id               = generate_id($db->get_option('product_id_format'));
            $q1               = $db->insert("
                INSERT INTO `ppSD_products` (
                    `id`,
                    `name`,
                    `type`,
                    `physical`,
                    `tax_exempt`,
                    `price`,
                    `hide`,
                    `owner`,
                    `public`,
                    `created`
                )
                VALUES (
                  '" . $db->mysql_cleans($id) . "',
                  '" . $db->mysql_cleans($prodIn['name']) . "',
                  '1',
                  '" . $physical . "',
                  '" . $tax_exempt . "',
                  '" . $db->mysql_cleans($prodIn['price']) . "',
                  '1',
                  '" . $employee['id'] . "',
                  '1',
                  '" . current_date() . "'
                )
            ");
            $order_comps      = array(
                'product_id'      => $id,
                'name'            => $prodIn['name'],
                'price'           => $prodIn['price'],
                'subscription_id' => '',
                'qty'             => $prodIn['qty']
            );
            $prods_in_order[] = $order_comps;
        }

    }
    // ----------------------------
    // Create the sale in the database
    $cart->id = $_POST['order']['id'];
    $cart->empty_cart();
    $start_session = $cart->start_session($member_id, '1', $_POST['order']['id']);

    foreach ($prods_in_order as $addToOrder) {
        $prod_get = $cart->get_product($addToOrder['product_id']);
        $add      = $cart->add($addToOrder['product_id'], $addToOrder['qty'], '', $member_id, $savings_code, $_POST['order']['id'], '', '1', '1');
    }

    if (!empty($savings_code)) {
        $add_save = $cart->apply_savings_code($savings_code);
        if ($add_save['error'] == '1') {
            echo "0+++" . $add_save['error_details'];
            exit;
        }
    }

    // Update shipping
    if ($shipping == '1') {
        $cart->update_shipping($_POST['shipping']['id']);
    }

    // Total
    if ($tax_exempt != '1') {
        $cart->update_order($_POST['order']['id'], $force_update_fields);
    }

    // $cart->update_order($_POST['order']['id'], array('date_completed' => $_POST['order']['date']));

    $info = $cart->get_order($_POST['order']['id'], '1');

} else {
    if ($_POST['id'] != $_POST['order']['id']) {
        $cart->update_order_number($_POST['id'], $_POST['order']['id']);
    }

    $cart->set_id($_POST['order']['id']);

    if (!empty($_POST['order']['code'])) {
        $add_save = $cart->apply_savings_code($_POST['order']['code']);
        if ($add_save['error'] == '1') {
            echo "0+++" . $add_save['error_details'];
            exit;
        }
    }

    if ($_POST['tax_exempt'] == '1') {
        $_POST['order']['state']   = '';
        $_POST['order']['country'] = '';
    }

    if (!empty($_POST['ship_yesno'])) {
        $cart->set_shipping($_POST['shipping']);
        $cart->store_shipping('1', $_POST['id']);
    }

    $cart->update_order($_POST['id'], $_POST['order']);

}
// ----------------------------
if ($type == 'edit') {
    // Nothing... yet.
    $id = $_POST['order']['id'];

} else {
    if ($_POST['card_type'] == 'none') {
        $charge   = array(
            'id'        => '',
            'order_id'  => '',
            'resp_code' => '',
            'msg'       => '',
            'fee'       => '',
        );
        $complete = $cart->complete_order($_POST['order']['id'], $charge, '1', '1');

    } else if ($_POST['card_type'] == 'new_card') {
        $cart->set_billing($_POST['cc']);
        // Run Charge if add_card_with_verify
        // is successful
        $gatewayA = $cart->get_gateways('1');
        if ($gatewayA['0']['api'] != '1') {
            echo "0+++Your payment gateway is not an API. You will not be able to create a subscription with a credit card for this user. Please select the \"No Credit Card\" option to continue.";
            exit;

        } else {
            $runit  = new $gatewayA['0']['code']($info['pricing']['total'], $_POST['cc'], '', '');
            $charge = $runit->charge();
            if ($charge['error'] == '1') {
                echo "0+++Credit Card error: " . $charge['msg'] . ' (' . $charge['resp_code'] . ')';
                exit;

            } else {
                $complete = $cart->complete_order($_POST['order']['id'], $charge, '1', '1');

            }

        }

    } // Run the charge
    else {
        // Success? Complete the order.
        $card_id = $_POST['card_id'];
        $card    = $cart->get_card($_POST['card_id']);
        $gateway = $cart->get_gateways('1');
        if ($gateway['0']['api'] != '1') {
            echo "0+++Your payment gateway is not an API. You will not be able to create a subscription with a credit card for this user. Please select the \"No Credit Card\" option to continue.";
            exit;
        } else {
            $runit  = new $card['gateway']($info['pricing']['total'], $card, '', '');
            $charge = $runit->charge($card);
            if ($charge['error'] == '1') {
                echo "0+++Credit Card error: " . $charge['msg'] . ' (' . $charge['resp_code'] . ')';
                exit;
            } else {
                $complete = $cart->complete_order($_POST['order']['id'], $charge, '1', '1');
            }
        }

    }

    $id = $_POST['order']['id'];
}

// Re-cache
$data         = $cart->get_order($id, '0', '1');

$task         = $db->end_task($task_id, '1');
$scope        = 'transaction';
$table        = 'ppSD_cart_sessions';
$history      = new history($id, '', '', '', '', '', $table);
$content      = $data['data'];
$table_format = new table($scope, $table);
$return       = array();
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Added Transaction';
    $return['load_slider']      = array(
        'id'      => $id,
        'page'    => 'transaction',
        'subpage' => 'view',
    );

} else {
    $return['close_popup']    = '1';
    $return['refresh_slider'] = '1';
    $cell                     = $table_format->render_cell($content, '1');
    $return['update_row']     = $cell;
    $return['show_saved']     = 'Updated Transaction';

}
echo "1+++" . json_encode($return);
exit;
