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
$task = 'invoice-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$invoice  = new invoice($employee['id']);
$cart     = new cart;

// Edit an invoice
if ($type == 'edit') {
    $data       = $invoice->get_invoice($_POST['id']);
    $invoice_id = $_POST['id'];
    $bdata      = array(
        'phone'          => $_POST['billing']['phone'],
        'fax'            => $_POST['billing']['fax'],
        'email'          => $_POST['billing']['email'],
        'contact_name'   => $_POST['billing']['contact_name'],
        'company_name'   => $_POST['billing']['company_name'],
        'website'        => $_POST['billing']['website'],
        'address_line_1' => $_POST['billing']['address_line_1'],
        'address_line_2' => $_POST['billing']['address_line_2'],
        'city'           => $_POST['billing']['city'],
        'state'          => $_POST['billing']['state'],
        'zip'            => $_POST['billing']['zip'],
        'country'        => $_POST['billing']['country'],
        'memo'           => $_POST['billing']['memo'],
    );
    $primary    = array('');
    $ignore     = array('id', 'edit');
    $query_form = $admin->query_from_fields($bdata, $type, $ignore, $primary);
    $q1         = $db->update("
        UPDATE `ppSD_invoice_data`
        SET " . ltrim($query_form['u2'], ',') . "
        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $add_up     = '';
    if ($_POST['ship_yesno'] == '1') {
        $query_form1 = $admin->query_from_fields($_POST['shipping'], 'add', $ignore, $primary);
        $q2          = $db->delete("
            DELETE FROM `ppSD_shipping`
            WHERE `invoice_id`='" . $db->mysql_cleans($_POST['id']) . "'
            LIMIT 1
        ");
        $q2          = $db->insert("
            INSERT INTO `ppSD_shipping` (`invoice_id`" . ltrim($query_form1['if2']) . ")
            VALUES ('" . $db->mysql_cleans($_POST['id']) . "'" . ltrim($query_form1['iv2']) . ")
        ");
        $ship_rule   = $cart->get_shipping_rule($_POST['shipping']['id']);
        $add_up      = "`shipping_rule`='" . $db->mysql_cleans($_POST['shipping']['id']) . "',`shipping_name`='" . $db->mysql_cleans($ship_rule['name']) . "',";

    } else {
        $q1 = $db->delete("
            DELETE FROM `ppSD_shipping`
            WHERE `invoice_id`='" . $db->mysql_clean($_POST['id']) . "'
            LIMIT 1
        ");

    }
    $query_formA = $admin->query_from_fields($_POST['data'], $type, $ignore, $primary);
    $q3          = $db->update("
        UPDATE `ppSD_invoices`
        SET
            " . $add_up . ltrim($query_formA['u2'], ',') . "
        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $a4          = $db->update("
        UPDATE `ppSD_invoice_components`
        SET `hourly`='" . $db->mysql_clean($_POST['data']['hourly']) . "'
        WHERE `invoice_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $code        = '1';

} // Create an invoice
else {

    // Establish the owner of the item.
    if ($_POST['data']['member_type'] == 'member') {
        $user = new user;
        $mem  = $user->get_user($_POST['data']['member_id']);
        if ($mem['error'] == '1') {
            echo "0+++Could not find member.";
            exit;
        } else {
            $member_id = $mem['data']['id'];
        }
        // Company name + URL
        if (! empty($mem['data']['company_name'])) {
            $company_name = $mem['data']['company_name'];
        } else {
            $company_name = $mem['data']['username'];
        }
        if (! empty($mem['data']['url'])) {
            $company_url = $mem['data']['url'];
        } else {
            $company_url = '';
        }
        $member_type = 'member';
        $bdata       = array(
            'email'          => $mem['data']['email'],
            'contact_name'   => $mem['data']['first_name'] . ' ' . $mem['data']['last_name'],
            'address_line_1' => $mem['data']['address_line_1'],
            'address_line_2' => $mem['data']['address_line_2'],
            'city'           => $mem['data']['city'],
            'state'          => $mem['data']['state'],
            'zip'            => $mem['data']['zip'],
            'country'        => $mem['data']['country'],
            'phone'          => $mem['data']['phone'],
            'memo'           => $_POST['billing']['memo'],
            'company_name'   => $company_name,
            'url'            => $company_url,
        );

    }
    else if ($_POST['data']['member_type'] == 'contact') {

        $contact = new contact;
        $mem     = $contact->get_contact($_POST['data']['contact_id']);

        if (empty($mem['data']['id'])) {
            echo "0+++Could not find contact.";
            exit;
        } else {
            $member_id = $mem['data']['id'];
        }

        // Company name + URL
        if (! empty($mem['data']['company_name'])) {
            $company_name = $mem['data']['company_name'];
        } else {
            $company_name = $mem['data']['first_name'] . ' ' . $mem['data']['last_name'];
        }
        if (! empty($mem['data']['url'])) {
            $company_url = $mem['data']['url'];
        } else {
            $company_url = '';
        }

        $member_type = 'contact';
        $bdata       = array(
            'email'          => $mem['data']['email'],
            'contact_name'   => $mem['data']['first_name'] . ' ' . $mem['data']['last_name'],
            'address_line_1' => $mem['data']['address_line_1'],
            'address_line_2' => $mem['data']['address_line_2'],
            'city'           => $mem['data']['city'],
            'state'          => $mem['data']['state'],
            'zip'            => $mem['data']['zip'],
            'country'        => $mem['data']['country'],
            'phone'          => $mem['data']['phone'],
            // 'company_name'   => $mem['data']['company_name'],
            'memo'           => $_POST['billing']['memo'],
            'company_name'   => $company_name,
            'url'            => $company_url,
        );
    }
    else {
        // Need a contact name and email
        if (empty($_POST['billing']['first_name']) || empty($_POST['billing']['last_name']) || empty($_POST['billing']['email'])) {
            echo "0+++Contact name and e-mail is required for this invoice.";
            exit;
        }
        // Create a contact
        $contact     = new contact;
        $data        = array(
            'source'         => '10',
            'account'        => 'NONMEM01',
            'contact_name'     => $_POST['billing']['contact_name'],
            'first_name'     => $_POST['billing']['first_name'],
            'last_name'      => $_POST['billing']['last_name'],
            'email'          => $_POST['billing']['email'],
            'address_line_1' => $_POST['billing']['address_line_1'],
            'address_line_2' => $_POST['billing']['address_line_2'],
            'city'           => $_POST['billing']['city'],
            'state'          => $_POST['billing']['state'],
            'zip'            => $_POST['billing']['zip'],
            'country'        => $_POST['billing']['country'],
            'phone'          => $_POST['billing']['phone'],
            'fax'            => $_POST['billing']['fax'],
            'company_name'   => $_POST['billing']['company_name'],
            'url'            => $_POST['billing']['website'],
        );
        $din         = $contact->create($data);
        $member_id   = $din['id'];
        $member_type = 'contact';
        $bdata       = $_POST['billing'];

        if (empty($_POST['billing']['company_name'])) {
            $bdata['company_name'] = $_POST['billing']['first_name'] . ' ' . $_POST['billing']['last_name'];
        }
    }
    // Create invoice
    $total        = array(
        'paid' => '0.00'
    );
    $use_tax_rate = $_POST['data']['tax_rate'] * 0.01;
    if (empty($_POST['data']['hourly_rate'])) {
        $hourly = $db->get_option('invoice_hourly');

    } else {
        $hourly = $_POST['data']['hourly_rate'];

    }
    $tax      = 0;
    $subtotal = 0;
    $credits  = 0;
    if (!empty($_POST['components'])) {
        foreach ($_POST['components'] as $item) {
            if (empty($item['tax'])) {
                $item['tax'] = '0';
            }
            $cost = '0';
            if ($item['type'] == 'product') {
                $price = $cart->get_product_price($item['id']);
                $cost  = $item['qty'] * $price['price'];
                $subtotal += $cost;
                if (!empty($item['tax']) && !empty($_POST['data']['tax_rate'])) {
                    $this_tax = round($cost * $use_tax_rate, 2);
                    $tax += $this_tax;

                }

            } else if ($item['type'] == 'hourly') {

                if (empty($item['qty'])) { $item['qty'] = 1; }

                if ($item['hourtype'] == 'hours') {
                    $cost = $hourly * $item['qty'];
                } else {
                    if ($db->get_option('invoice_round_up') == '1') {
                        $cost = ceil($hourly / 60) * $item['qty'];
                    } else {
                        $cost = round(($hourly / 60) * $item['qty'], 2);
                    }
                }
                $subtotal += $cost;
                if (!empty($item['tax']) && !empty($_POST['data']['tax_rate'])) {
                    $this_tax = round($cost * $use_tax_rate, 2);
                    $tax += $this_tax;

                }

            } else if ($item['type'] == 'credit') {
                $credits += $item['price'];
                $subtotal -= $item['price'];

            }

        }

    }
    // Shipping?
    if ($_POST['ship_yesno'] == '1') {
        $ship_rule = $cart->get_shipping_rule($_POST['shipping']['id']);
        if (empty($ship_rule['id'])) {
            echo "0+++Please select a shipping option.";
            exit;
        } else {
            $ship_id   = $_POST['shipping']['id'];
            $ship_name = $ship_rule['name'];

            if (! empty($_POST['same_as_billing'])) {
                $shipping  = $bdata;
            } else {
                $shipping  = $_POST['shipping'];
            }

            unset($shipping['id']);
            $total['shipping'] = $ship_rule['cost'];
            $subtotal += $ship_rule['cost'];

        }
    } else {
        $shipping          = array();
        $ship_name         = '';
        $ship_id           = '';
        $total['shipping'] = '0.00';

    }
    $subtotal += $tax;
    $subtotal -= $credits;
    $total['due']      = $subtotal;
    $total['credits']  = $credits;
    $total['tax']      = $tax;
    $total['subtotal'] = $subtotal;
    $total['tax_rate'] = $_POST['data']['tax_rate'];
    // Create invoice
    if (empty($_POST['data']['due_date'])) {
        $time = $db->get_option('invoice_due_date');
        $_POST['data']['due_date'] = add_time_to_expires($time);
    }
    
    $use_data   = array(
        'date_due'      => $_POST['data']['due_date'],
        'member_id'     => $member_id,
        'member_type'   => $member_type,
        'status'        => '9',
        'tax_rate'      => $_POST['data']['tax_rate'],
        'shipping_rule' => $ship_id,
        'shipping_name' => $ship_name,
        'owner'         => $employee['id'],
        'hourly'        => $hourly,
        'auto_inform'   => $_POST['data']['auto_inform'],
        'check_only'    => $_POST['data']['check_only'],
        'quote'    => $_POST['data']['quote'],
    );
    $invoice_id = $invoice->create_invoice($use_data, $total, $bdata, $shipping);
    // Add components
    if (!empty($_POST['components'])) {
        foreach ($_POST['components'] as $item) {
            if (empty($item['tax'])) {
                $item['tax'] = '0';
            }
            if ($item['type'] == 'product') {
                $prod                          = $cart->get_product($item['id']);
                $price                         = $cart->get_product_price($item['id']);
                $prod['pricing']['qty']        = $item['qty'];
                $prod['pricing']['plain_unit'] = $price['price'];
                $comp                          = $invoice->add_component_product($invoice_id, $prod, $item['tax']);

            } else if ($item['type'] == 'hourly') {
                if ($item['hourtype'] == 'hours') {
                    $item['qty'] = $item['qty'] * 60;
                }

                $comp = $invoice->add_component_time($invoice_id, $item['qty'], $_POST['data']['hourly_rate'], $item['name'], $item['desc'], $item['tax'], $employee['id']);

            } else if ($item['type'] == 'credit') {
                $comp = $invoice->add_component_credit($invoice_id, $item['price'], '', $item['name'], $item['desc'], $item['tax']);

            }

        }

    }
    $code = '0';

}
// Recalculate total
$invoice->recalculate_totals($invoice_id);

if ($type != 'edit' && ! empty($_POST['data']['auto_inform'])) {
    $invoice->send_invoice($invoice_id);
}

// Re-cache
$data                  = $invoice->get_invoice($invoice_id, '1');
$use_in_table          = array_merge($data['data'], $data['totals']);
$use_in_table          = array_merge($use_in_table, $data['billing']);
$scope                 = 'invoice';
$table                 = 'ppSD_invoices';
$content               = $use_in_table;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    // Notify Client?
    if ($_POST['data']['auto_inform'] == '1') {
        //$invoice->send_invoice($invoice_id,$code);
        $msg = 'E-Mailed User';

    } else {
        $msg = 'Did not e-mail user.';

    }
    // Reply data
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Invoice (' . $msg . ')';
    $returned['load_slider']    = array(
        'page'    => 'invoice',
        'subpage' => 'view',
        'id'      => $invoice_id,
    );

} else {
    $cell                     = $table_format->render_cell($content, '1');
    $return['update_row']     = $cell;
    $return['show_saved']     = 'Updated Invoice';
    $return['refresh_slider'] = '1';

}
echo "1+++" . json_encode($return);
exit;
$task = $db->end_task($task_id, '1');
echo "1+++" . $_POST['id'] . "+++refresh";
exit;



