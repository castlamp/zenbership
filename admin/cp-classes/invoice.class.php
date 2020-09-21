<?php

/**
 * INVOICE MANAGEMENT
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
class invoice extends db
{

    protected $owner;


    function __construct($owner = '')
    {

        if (empty($owner)) {
            $owner = '2';
        }
        $this->owner = $owner;

    }


    /**
     * Create an invoice
     * @param array $data
     *       'member_id'
     *       'member_type'
     *       'shipping_name'
     *       'shipping_rule'
     *       'due_date'
     *       'hourly'
     *       'rsvp_id'
     * @param array $totals
     *       'due'
     *       'shipping'
     *       'tax'
     *       'tax_rate'
     *       'credits'
     *       'subtotal'
     * @param array $billing
     *       'company_name'
     *       'phone'
     *       'fax'
     *       'email'
     *       'contact_name'
     *       'address_line_1'
     *       'address_line_2'
     *       'city'
     *       'state'
     *       'zip'
     *       'country'
     *       'phone'
     *       'memo'
     * @param array $shipping
     *
     * @param bool $send_email 1 = Send it
     */
    function create_invoice($data, $totals, $billing, $shipping = '', $sub_id = '')
    {
        if (!empty($data['date_due'])) {
            $due_date = $data['date_due'] . ' 00:00:00';
        } else {
            $due_date = $this->get_due_date();
        }
        if (!empty($data['hourly'])) {
            $hourly = $data['hourly'];
        } else {
            $hourly = $this->get_option('invoice_hourly');
        }
        if (!empty($data['rsvp_id'])) {
            $rsvp_id = $data['rsvp_id'];
        } else {
            $rsvp_id = '';
        }
        if (!empty($data['status'])) {
            $status = $data['status'];
        } else {
            $status = '9';
        }
        $inid = $this->get_option('invoice_id_format');
        $invoice_id = $this->generate_id($inid);
        
        $task_name = 'invoice_create';
        $task_id = $this->start_task($task_name, 'user', '', $invoice_id);
        
        $invoice_hash = $this->hash_invoice($invoice_id, $due_date);
        // Ownership
        if (!empty($data['owner'])) {
            $owner = $data['owner'];
        } else {
            $owner = '2';
        }
        if (!empty($data['order_id'])) {
            $oid = $data['order_id'];
        } else {
            $oid = '';
        }
        if (!empty($data['shipping_rule'])) {
            $ship_rule = $data['shipping_rule'];
        } else {
            $ship_rule = '';
        }
        if (!empty($data['shipping_name'])) {
            $ship_name = $data['shipping_name'];
        } else {
            $ship_name = '';
        }
        // Create invoice
        $auto_inform = (! empty($data['auto_inform'])) ? $data['auto_inform'] : '';

        $q4 = $this->insert("
            INSERT INTO `ppSD_invoices` (
                `id`,
                `date`,
                `date_due`,
                `member_id`,
                `member_type`,
                `order_id`,
                `status`,
                `hash`,
                `shipping_rule`,
                `shipping_name`,
                `ip`,
                `owner`,
                `tax_rate`,
                `hourly`,
                `rsvp_id`,
                `auto_inform`,
                `sub_id`,
                `quote`,
                `check_only`
            )
            VALUES (
                '" . $this->mysql_clean($invoice_id) . "',
                '" . current_date() . "',
                '" . $due_date . "',
                '" . $this->mysql_clean($data['member_id']) . "',
                '" . $this->mysql_clean($data['member_type']) . "',
                '" . $this->mysql_clean($oid) . "',
                '" . $status . "',
                '" . $this->mysql_clean($invoice_hash) . "',
                '" . $this->mysql_clean($ship_rule) . "',
                '" . $this->mysql_clean($ship_name) . "',
                '" . $this->mysql_clean(get_ip()) . "',
                '" . $this->mysql_clean($owner) . "',
                '" . $this->mysql_clean($totals['tax_rate']) . "',
                '" . $this->mysql_clean($hourly) . "',
                '" . $this->mysql_clean($rsvp_id) . "',
                '" . $this->mysql_clean($auto_inform) . "',
                '" . $this->mysql_clean($sub_id) . "',
                '" . $this->mysql_clean($data['quote']) . "',
                '" . $this->mysql_clean($data['check_only']) . "'
            )
        ");
        $history = $this->add_history('invoice_created', '2', $data['member_id'], '', $invoice_id, '');
        // Billing info
        $this->add_billing($invoice_id, $billing);
        // Calculate Totals
        $this->add_totals($invoice_id, $totals);
        if (!empty($shipping)) {
            $this->add_shipping($invoice_id, $shipping);
        }
        //if ($data['auto_inform'] == '1') {
        //    $this->send_invoice($invoice_id);
        //}
        // Tracking milestone?
        $connect = new connect;
        $track = $connect->check_tracking();
        if ($track['error'] != '1') {
            $connect->tracking_activity('invoice', $invoice_id, $totals['subtotal']);
        }
        $put = 'invoices';
        $this->put_stats($put);
        $put = 'invoices-' . $owner;
        $this->put_stats($put);
        $put = 'invoices_outstanding';
        $this->put_stats($put, $totals['due']);
        $put = 'invoices_outstanding-' . $owner;
        $this->put_stats($put, $totals['due']);

        $indata = array(
        	'invoice_id' => $invoice_id,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        return $invoice_id;

    }



    public function markSeen($id)
    {
        $q1 = $this->run_query("
            UPDATE ppSD_invoices
            SET `seen`=(seen+1), `last_seen_date`='" . current_date() . "'
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");

        return true;
    }

    /**
     * @param string $id Invoice ID
     * @param array $totals Array with total data
     */
    function add_totals($id, $totals)
    {
        $q6 = $this->insert("
            INSERT INTO `ppSD_invoice_totals` (`id`,`paid`,`due`,`shipping`,`tax`,`tax_rate`,`credits`,`subtotal`)
            VALUES (
                '" . $this->mysql_clean($id) . "',
                '" . $this->mysql_clean('0.00') . "',
                '" . $this->mysql_clean($totals['due']) . "',
                '" . $this->mysql_clean($totals['shipping']) . "',
                '" . $this->mysql_clean($totals['tax']) . "',
                '" . $this->mysql_clean($totals['tax_rate']) . "',
                '" . $this->mysql_clean($totals['credits']) . "',
                '" . $this->mysql_clean($totals['subtotal']) . "'
            )
        ");
    }


    /**
     * Apply a payment to an invoice
     * @param string $invoice_id Invoice ID
     * @param array $paid Total paid on the invoice.
     */
    function apply_payment($invoice_id, $paid, $order_id = '', $date = '')
    {

        $task_name = 'invoice_payment';
        $task_id = $this->start_task($task_name, 'user', '', $invoice_id);

        // Get the invoice
        $indata = $this->get_invoice($invoice_id);

        // New totals
        $new_paid = $indata['totals']['paid'] + $paid;
        $new_due = $indata['totals']['due'] - $paid;
        // Update invoice
        $q1 = $this->update("
            UPDATE `ppSD_invoice_totals`
            SET
                `due`='" . $this->mysql_clean($new_due) . "',
                `paid`='" . $this->mysql_clean($new_paid) . "'
            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'
            LIMIT 1
        ");

        if (empty($date)) {
            $date = current_date();
        }

        $q2 = $this->insert("
            INSERT INTO `ppSD_invoice_payments` (
                `order_id`,
                `invoice_id`,
                `date`,
                `paid`,
                `new_balance`
            )
            VALUES (
              '" . $this->mysql_clean($order_id) . "',
              '" . $this->mysql_clean($invoice_id) . "',
              '" . $this->mysql_clean($date) . "',
              '" . $this->mysql_clean($paid) . "',
              '" . $this->mysql_clean($new_due) . "'
            )
        ");

        // -----------------------------
        // Set new status
        if ($new_due <= 0) {
            $status = '1';
        } else {
            if ($indata['data']['status'] == '3') {
                $status = '3';
            } else {
                $status = '2';
            }
        }
        $this->update_status($invoice_id, $status);

        // Recache invoice
        $this->get_invoice($invoice_id, '1');

        // Stats
        /*
        $put = 'revenue';
        $this->put_stats($put, $paid, 'add', $date);
        $put = 'sales';
        $this->put_stats($put, '1', 'add', $date);
        */
        $put = 'invoice_revenue';
        $this->put_stats($put, $paid, 'add', $date);
        $put = 'invoice_payments';
        $this->put_stats($put);
        if (!empty($indata['data']['owner'])) {
            $put = 'invoice_payments-' . $indata['data']['owner'];
            $this->put_stats($put, '1', 'add', $date);
            $put = 'invoice_revenue-' . $indata['data']['owner'];
            $this->put_stats($put, $paid, 'add', $date);

        }
        $history = $this->add_history('invoice_payment', '2', $indata['data']['member_id'], '', $invoice_id, '');
    
        $indata = array(
        	'invoice_id' => $invoice_id,
        	'data' => $indata['data'],
        	'totals' => $indata['totals'],
        	'total' => $paid,
        	'order_id' => $order_id,
        	'new_total_paid' => $new_paid,
        	'new_due' => $new_due,
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $indata);
        
        // Finalized?
        if ($new_due <= 0) {
            $this->close_invoice($invoice_id, $indata['data']['owner']);
            return array('status' => '1', 'id' => $q2);
        } else {
            return array('status' => '0', 'id' => $q2);
        }

    }


    /**
     * @param $id
     * @param string $employee_id
     *
     * @return bool
     */
    public function close_rolling($id, $employee_id = '')
    {
        return $this->close_invoice($id, $employee_id, true);
    }


    /**
     * Finalizes an invoice.
     */
    function close_invoice($invoice_id, $employee_id = '', $skip_rolling_checks = false)
    {
        $task_name = 'invoice_closed';
        $task_id = $this->start_task($task_name, 'user', '', $invoice_id);

        $data = $this->get_invoice($invoice_id);

        // We don't close a rolling invoice until it is manually closed.
        if ($data['rolling_invoice'] == '1') {
            if ($skip_rolling_checks === false) {
                return false;
            }
        }

        // DBing
        $this->update_status($invoice_id, '1');
        if ($data['data']['auto_inform'] == '1') {
            $this->send_invoice($invoice_id, '2');
        }
        $this->apply_credit($invoice_id);

        // Stats
        $put = 'invoices_closed';
        $this->put_stats($put);
        $put = 'invoices_closed-' . $employee_id;
        $this->put_stats($put);

        // Subscription?
        // We don't renew this because the program auto-extends
        // the subscription after issuing the invoice...
        /*
        if (! empty($data['sub_id'])) {
            $sub = new subscription();
            $get = $sub->get_subscription($data['sub_id']);
            $sub->process_renewal($get);
        }
        */

        // Close it
        $indata = $this->get_invoice($invoice_id);
        
        $cusdata = array(
            'invoice' => $data,
        	'invoice_id' => $invoice_id,
        	'data' => $indata['data'],
        	'totals' => $indata['totals'],
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $cusdata);
        
        $history = $this->add_history('invoice_closed', '2', $indata['data']['member_id'], '', $invoice_id, '');
    }


    /**
     * Some bad bad person didn't pay their invoice... time to cancel it.
     *
     * @param $invoice_id
     */
    function mark_dead($invoice_id)
    {
        $task_name = 'invoice_dead';
        $task_id = $this->start_task($task_name, 'user', '', $invoice_id);
        
        $this->update_status($invoice_id, '5');
        // Stats
        $put = 'invoices_dead';
        $this->put_stats($put);
        // Get invoice
        $data = $this->get_invoice($invoice_id);

        // Cancel related subscription?
        if (! empty($data['data']['sub_id'])) {
            $sub = new subscription;
            $sub->cancel_subscription($data['data']['sub_id'], 'Subscription failed and spawned invoice ID ' . $invoice_id . ' which was marked dead on ' . current_date());
        }
        
        $cusdata = array(
        	'invoice_id' => $invoice_id,
        	'data' => $data['data'],
        	'totals' => $data['totals'],
        );
        $task = $this->end_task($task_id, '1', '', $task_name, '', $cusdata);

        $put = 'invoices_dead_revenue';
        $this->put_stats($put, $data['totals']['due']);
    }


    /**
     * Apply credit for stuff like event RSVPs,
     * content access, etc.

     */
    function apply_credit($invoice_id)
    {

        // Get invoice details
        $data = $this->get_invoice($invoice_id);
        // Event Registration
        if (!empty($data['data']['rsvp_id'])) {
            $data = array(
                'status' => '1'
            );
            $event = new event;
            $rsvp = $event->get_rsvp($data['data']['rsvp_id']);
            $up1 = $event->edit_rsvp($data['data']['rsvp_id'], $data);
            $em = $event->email_rsvp($data['data']['rsvp_id']);
            foreach ($rsvp['guest_list'] as $aGuest) {
                $up1 = $event->edit_rsvp($aGuest, $data);
                $em = $event->email_rsvp($aGuest);

            }
            $money_in = $data['totals']['paid'];
            $db->put_stats('event_income', $money_in);
            $db->put_stats('event_income-' . $rsvp['event_id'], $money_in);

        }
        // Contact Access
        // Only if:
        //  1. User is a member.
        //  2. Invoice components are products in the database.
        if (!empty($data['data']['member_id']) && $data['data']['member_type'] == 'member') {
            $cart = new cart;
            foreach ($data['components'] as $anItem) {
                if (!empty($anItem['product_id'])) {
                    $product = $cart->get_product($anItem['id']);
                    $cart->apply_product_settings_to_user($product, $data['data']['member_id']);

                }

            }

        }

    }


    /**
     * Update the status of an invoice.
     * @param string $status
     *      0 = Unpaid
     *      1 = Paid
     *      2 = Partial Payment
     *      3 = Overdue
     *      9 = Empty
     */
    function update_status($invoice_id, $status)
    {
        $q2 = $this->update("
            UPDATE `ppSD_invoices`
            SET `status`='" . $this->mysql_clean($status) . "'
            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'
            LIMIT 1
        ");
    }


    /**
     * Update the status of an invoice.
     * @param array $data Must match data in ppSD_invoices only!
     */
    function update_invoice($invoice_id, $data)
    {

        $query = '';
        foreach ($data as $item => $value) {
            $query .= ",`" . $this->mysql_cleans($item) . "`='" . $this->mysql_cleans($value) . "'";

        }
        $q1 = $this->update("
            UPDATE `ppSD_invoices`
            SET " . ltrim($query, ',') . "
            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'
            LIMIT 1
        ");
    }


    /**
     * @param string $id Invoice ID
     * @param array $billing Array with billing info
     */
    function add_billing($id, $billing)
    {
        $in = '';
        $val = '';
        $permitted = array(
            'company_name',
            'contact_name',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'zip',
            'country',
            'phone',
            'fax',
            'email',
            'website',
            'memo',
        );
        foreach ($billing as $name => $value) {
            if (in_array($name, $permitted)) {
                $in .= ',`' . $this->mysql_cleans($name) . '`';
                $val .= ",'" . $this->mysql_cleans($value) . "'";
            }
        }
        $q6 = $this->insert("
            INSERT INTO `ppSD_invoice_data` (`id`$in)
            VALUES ('" . $this->mysql_cleans($id) . "'$val)
        ");
    }


    /**
     * @param string $id Invoice ID
     * @param array $shipping Array with billing info
     */
    function add_shipping($id, $shipping)
    {

        $in = '';
        $val = '';
        foreach ($shipping as $name => $value) {
            $in .= ',`' . $this->mysql_cleans($name) . '`';
            $val .= ",'" . $this->mysql_cleans($value) . "'";
        }
        $q6 = $this->insert("
            INSERT INTO `ppSD_shipping` (`invoice_id`$in)
            VALUES ('" . $this->mysql_cleans($id) . "'$val)
        ");
    }


    /**
     * Add a time component
     * @param string $invoice_id
     * @param string $minutes
     * @param string $rate Optional override for this invoice's hourly rate.
     */
    function add_component_time($invoice_id, $minutes, $rate = '', $name = '', $desc = '', $taxable = '0', $employee_id = '')
    {

        // Calculate total
        if (empty($rate)) {
            $rate = $this->get_option('invoice_hourly');
        }
        if ($this->get_option('invoice_round_up') == '1') {
            $cost = ceil($minutes / 60) * $rate;
        } else {
            $cost = round(($minutes / 60) * $rate, 2);
        }
        if (empty($name)) {
            $name = $this->get_error('I003');
        }
        // Add element
        $q = $this->insert("
			INSERT INTO `ppSD_invoice_components` (
				`invoice_id`,
				`unit_price`,
				`name`,
				`description`,
				`qty`,
				`type`,
				`hourly`,
				`minutes`,
				`status`,
				`date`,
				`owner`,
				`tax`
			)
			VALUES (
				'" . $this->mysql_clean($invoice_id) . "',
				'" . $this->mysql_clean($cost) . "',
				'" . $this->mysql_clean($name) . "',
				'" . $this->mysql_clean(nl2br($desc)) . "',
				'1',
				'time',
				'" . $this->mysql_clean($rate) . "',
				'" . $this->mysql_clean($minutes) . "',
				'0',
				'" . current_date() . "',
				'" . $this->mysql_clean($this->owner) . "',
				'" . $this->mysql_clean($taxable) . "'
			)
		");
        // Update new status
        $this->update_new($invoice_id);
        // Stats
        $put = 'invoices_minutes_billed';
        $this->put_stats($put, $minutes);
        if (!empty($employee_id)) {
            $put = 'invoices_minutes_billed-' . $employee_id;
            $this->put_stats($put, $minutes);

        }
    }


    /**
     * Removes the "new" status from
     * an invoice.
     */
    function update_new($invoice_id)
    {
        $q1 = $this->update("
            UPDATE `ppSD_invoices`
            SET `status`='0'
            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'
            LIMIT 1
        ");
    }

    /**
     * Adds a credit component to an invoice
     *
     * @param string $invoice_id        Invoice ID
     * @param decimal $credit_amount     Dollar amount of the credit.
     * @param string $savings_code      Optional promotional code.
     * @param string $name              Credit Component name.
     * @param string $desc              Credit Component description.
     * @param bool   $taxable           Whether this is taxable or not. Usually "0".
     */
    function add_component_credit($invoice_id, $credit_amount, $savings_code = '', $name = '', $desc = '', $taxable = '0')
    {

        if (!empty($savings_code)) {
            $desc = $this->get_error('I002');
            $desc = str_replace('%code%', $savings_code, $desc);
        }
        if (empty($name)) {
            $name = $this->get_error('I001');
        }
        $q = $this->insert("
			INSERT INTO `ppSD_invoice_components` (
				`invoice_id`,
				`unit_price`,
				`name`,
				`description`,
				`qty`,
				`type`,
				`date`,
				`owner`,
				`tax`
			)
			VALUES (
				'" . $this->mysql_clean($invoice_id) . "',
				'" . $this->mysql_clean($credit_amount) . "',
				'" . $this->mysql_clean($name) . "',
				'" . $this->mysql_clean($desc) . "',
				'1',
				'credit',
				'" . current_date() . "',
				'" . $this->mysql_clean($this->owner) . "',
				'" . $this->mysql_clean($taxable) . "'
			)
		");
    }


    /**
     * Add a product component
     * @param string $invoice_id
     * @param array $product_array From $cart->get_product
     *      'plain_unit'
     *      'name'
     *      'tagline'
     *      'type'
     *      'qty'
     *      'id'

     */
    function add_component_product($invoice_id, $product_array, $taxable = '0')
    {
        $q = $this->insert("
			INSERT INTO `ppSD_invoice_components` (
				`invoice_id`,
				`unit_price`,
				`name`,
				`description`,
				`type`,
				`qty`,
				`hourly`,
				`minutes`,
				`status`,
				`product_id`,
				`date`,
				`owner`,
				`tax`
			)
			VALUES (
				'" . $this->mysql_clean($invoice_id) . "',
				'" . $this->mysql_clean($product_array['pricing']['plain_unit']) . "',
				'" . $this->mysql_clean($product_array['data']['name']) . "',
				'" . $this->mysql_clean($product_array['data']['tagline']) . "',
				'product',
				'" . $this->mysql_clean($product_array['pricing']['qty']) . "',
				'0',
				'0',
				'0',
				'" . $this->mysql_clean($product_array['data']['id']) . "',
				'" . current_date() . "',
				'" . $this->mysql_clean($this->owner) . "',
				'" . $this->mysql_clean($taxable) . "'
			)
		");
        // Update new status
        $this->update_new($invoice_id);
    }


    /**
     * Create an invoice hash

     */
    function hash_invoice($invoice_id, $cart_id)
    {

        return md5(md5($invoice_id) . $cart_id);

    }


    /**
     * Determine due date on an invoice
     * @param array $data
     * @return string Due date
     */
    function get_due_date($fixed = '')
    {

        if (!empty($fixed)) {
            $due_date = add_time_to_expires($fixed);

        } else {
            $due_date = add_time_to_expires($this->get_option('invoice_due_date'));

        }
        return $due_date;

    }


    public function get_user_invoices($id)
    {
        $subs     = array();

        $STH = $this->run_query("
            SELECT ppSD_invoices.id
            FROM ppSD_invoices
            WHERE ppSD_invoices.member_id='" . $this->mysql_clean($id) . "'
            ORDER BY ppSD_invoices.status ASC, ppSD_invoices.date_due ASC
        ");

        while ($row = $STH->fetch()) {
            $subs[] = $this->get_invoice($row['id'], 0, true);
        }

        return $subs;
    }

    /**
     * Get invoice
     */
    function get_invoice($id, $recache = '0', $skip_meat = false)
    {

        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $return = $cache['data'];

        } else {
            $q1 = $this->get_array("
				SELECT * FROM `ppSD_invoices`
				WHERE `id`='" . $this->mysql_clean($id) . "'
				LIMIT 1
			");
            if (!empty($q1['id'])) {
                $return = array('error' => '0', 'error_details' => '');
                $return['data'] = $q1;
                $return['data']['format_date'] = format_date($q1['date']);
                $return['data']['format_due_date'] = format_date($q1['date_due']);
                $return['data']['time_to_due_date'] = date_difference($q1['date_due']);
                $return['data']['stamp'] = $this->get_stamp($q1['status']);
                if ($return['data']['hourly'] <= 0) {
                    $return['data']['hourly'] = $this->get_option('invoice_hourly');

                }

                if ($return['data']['seen'] > 0) {
                    $return['data']['format_last_seen'] = 'Seen ' . $return['data']['seen'] . ' time(s), last on ' . format_date($return['data']['last_seen_date']);
                } else {
                    $return['data']['format_last_seen'] = 'Unseen';
                }

                // Status
                if ($return['data']['status'] == '0') {
                    $return['data']['format_status'] = $this->get_error('I010');
                    $return['data']['stamp'] = '';
                }
                else if ($return['data']['status'] == '1') {
                    $theme = $this->get_theme();
                    $return['data']['format_status'] = $this->get_error('I011');
                    $return['data']['stamp'] = '<img src="' . PP_URL . '/pp-templates/html/' . $theme['name'] . '/imgs/paid.png" width="415" height="415" border="0" alt="Paid" title="Paid" style="position:absolute;top:25%;left:50%;margin:0 0 0 -207px;" />';
                }
                else if ($return['data']['status'] == '2') {
                    $return['data']['format_status'] = $this->get_error('I012');
                    $return['data']['stamp'] = '';
                }
                else if ($return['data']['status'] == '3') {
                    $return['data']['format_status'] = $this->get_error('I013');
                    $return['data']['stamp'] = '';
                }
                else if ($return['data']['status'] == '4') {
                    $return['data']['format_status'] = $this->get_error('I014');
                    $return['data']['stamp'] = '';
                }
                else if ($return['data']['status'] == '5') {
                    $return['data']['format_status'] = $this->get_error('I015');
                    $return['data']['stamp'] = '';
                }
                else if ($return['data']['status'] == '9') {
                    $return['data']['format_status'] = 'Empty';
                    $return['data']['stamp'] = '';
                }
                if ($return['data']['last_reminder'] == '1920-01-01 00:01:01') {
                    $return['data']['format_last_reminder'] = 'N/A';
                } else {
                    $return['data']['format_last_reminder'] = format_date($return['data']['last_reminder']);
                }

                // Components
                if (! $skip_meat) {
                    $components = array();
                    $STH = $this->run_query("
                    SELECT * FROM `ppSD_invoice_components`
                    WHERE `invoice_id`='" . $this->mysql_clean($id) . "'
                ");
                    while ($row = $STH->fetch()) {
                        if (empty($row['name'])) {
                            $row['name'] = 'Untitled';
                        }
                        $row['format_date'] = format_date($row['date']);
                        $components[] = $row;
                    }
                    $return['components'] = $components;
                    // Billing
                    $billing = $this->get_array("
                    SELECT * FROM `ppSD_invoice_data`
                    WHERE `id`='" . $this->mysql_clean($id) . "'
                    LIMIT 1
                ");
                    $return['billing'] = $billing;
                    // Billing Address
                    $bill_addy = format_address(
                        $return['billing']['address_line_1'],
                        $return['billing']['address_line_2'],
                        $return['billing']['city'],
                        $return['billing']['state'],
                        $return['billing']['zip'],
                        $return['billing']['country']
                    );
                    $return['billing']['formatted'] = $bill_addy;
                    $return['billing'] = $this->fill_array($return['billing'],
                        'contact_name,company_name,phone,fax,website,memo,email,address_line_1,address_line_2,city,state,zip,country');
                    // Shipping, if any
                    $shipping = $this->get_array("
                    SELECT *
                    FROM `ppSD_shipping`
                    WHERE `invoice_id`='" . $this->mysql_clean($id) . "'
                    LIMIT 1
                ");
                    $return['shipping'] = $shipping;
                    // Shipping
                    if (!empty($return['data']['shipping_rule'])) {
                        $ship_addy = format_address(
                            $return['shipping']['address_line_1'],
                            $return['shipping']['address_line_2'],
                            $return['shipping']['city'],
                            $return['shipping']['state'],
                            $return['shipping']['zip'],
                            $return['shipping']['country']
                        );
                    } else {
                        $ship_addy = $this->get_error('I004');
                    }
                    $return['shipping']['formatted'] = $ship_addy;
                    $return['shipping'] = $this->fill_array($return['shipping'],
                        'first_name,last_name,address_line_1,address_line_2,city,state,zip,country');
                    // Components
                    $return['data']['comps'] = $this->render_components($components);
                    // Payments
                    // Payments
                    $q12 = $this->run_query("
                    SELECT *
                    FROM `ppSD_invoice_payments`
                    WHERE `invoice_id`='" . $this->mysql_clean($id) . "'
                    ORDER BY `date` ASC
                ");
                    $payments = '';
                    while ($row = $q12->fetch()) {
                        //$item['hourly'] = place_currency($item['hourly']);
                        $row['format_date'] = format_date($row['date']);
                        $row['format_paid'] = place_currency($row['paid']);
                        $row['format_balance'] = place_currency($row['new_balance']);
                        $template = new template('invoice_payment_entry', $row, '0');
                        $payments .= $template;
                    }
                    $return['data']['payments'] = $payments;
                }
                // Totals
                $tots = $this->get_array("
                    SELECT *
                    FROM `ppSD_invoice_totals`
                    WHERE `id`='" . $this->mysql_clean($id) . "'
                    LIMIT 1
                ");
                $return['totals'] = $tots;
                // paid	due	subtotal	shipping	tax	tax_rate	credits
                $return['totals']['items'] = $tots['subtotal'] - $tots['shipping'] - $tots['tax'];
                $cart = new cart;
                $return['format_totals'] = $cart->format_pricing($return['totals']);
                // Some links
                $link = PP_URL . '/pp-cart/invoice.php?id=' . $q1['id'] . '&h=' . $q1['hash'];
                $return['data']['link'] = $link;

                $secure = $this->getSecureLink();

                $payment_link = $secure . '/pp-cart/invoice_pay.php?id=' . $q1['id'] . '&h=' . $q1['hash'];
                $return['data']['payment_link'] = $payment_link;
                //$print_pdf = PP_URL . '/pp-cart/invoice_pdf.php?id=' . $q1['id'] . '&h=' . $q1['hash'];
                //$return['data']['print_pdf'] = $print_pdf;
                $print_invoice = PP_URL . '/pp-cart/invoice_print.php?id=' . $q1['id'] . '&h=' . $q1['hash'];
                $return['data']['print_invoice'] = $print_invoice;

                /*

                // Current total

                $total = $q1['amount_due'];



                // Charges

                $STH = $this->run_query("

					SELECT * FROM `ppSD_cart_sessions`

					WHERE `invoice_id`='" . $this->mysql_clean($id) . "'

					ORDER BY `date` DESC

				");

                $charges = array();

                while ($row =  $STH->fetch()) {

                    $charges[] = $this->get_order($row['id']);

                    $total -= $row['pricing']['total'];

                }



                $return['history'] = $charges;

                $return['data']['amount_due'] = $total;



                $salt = md5(md5($q1['id'] . $q1['date']) . md5($q1['salt']));

                $link = PP_URL . '/pp-cart/invoice.php?id=' . $q1['id'] . '&s=' . $salt;

                $return['data']['link'] = $link;

                $return['data']['salt'] = $salt;

                */
                // Add cache entry
                $cache = $this->add_cache($q1['id'], $return);

            } else {
                $return = array(
                    'error' => '1',
                    'error_details' => ''
                );

            }

        }
        return $return;

    }


    function find_by_rsvp($id)
    {

        $q1 = $this->get_array("

            SELECT `id`

            FROM `ppSD_invoices`

            WHERE `rsvp_id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            return $this->get_invoice($q1['id']);

        } else {
            return array('error' => '1');

        }

    }


    function find_by_orderid($id)
    {

        $q1 = $this->get_array("

            SELECT `id`

            FROM `ppSD_invoices`

            WHERE `order_id`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            return $this->get_invoice($q1['id']);

        } else {
            return array('error' => '1');

        }

    }


    function get_payment($id)
    {

        $q12 = $this->get_array("
            SELECT *
            FROM `ppSD_invoice_payments`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q12;

    }


    /**
     * Check if an invoice exists on the
     * user-side of things.
     * @param string $id
     */
    function check_invoice($id, $hash)
    {

        $data = $this->get_invoice($id, '1');
        if ($data['error'] == '1') {
            $this->show_error_page('I006');

        } else {
            if ($data['data']['hash'] != $hash) {
                $this->show_error_page('I007');

            } else {
                return $data;

            }

        }

    }


    /**
     * @param string $invoice_id
     */
    function recalculate_totals($invoice_id)
    {

        $data = $this->get_invoice($invoice_id, '1');
        $tax = 0;
        $subtotal = 0;
        $credits = 0;
        $shipping = $data['totals']['shipping'];
        if ($data['data']['hourly'] == 0) {
            $hourly = $this->get_option('invoice_hourly');
        } else {
            $hourly = $data['data']['hourly'];
        }
        foreach ($data['components'] as $item) {
            if ($item['type'] == 'product') {
                $cost = $item['qty'] * $item['unit_price'];
                $subtotal += $cost;
                if (!empty($item['tax']) && !empty($data['data']['tax_rate'])) {
                    $this_tax = round($cost * ($data['data']['tax_rate'] * .01), 2);
                    $tax += $this_tax;
                }
            } else if ($item['type'] == 'time') {
                if ($this->get_option('invoice_round_up') == '1') {
                    $cost = ceil($hourly / 60) * $item['minutes'];
                } else {
                    $cost = round(($hourly / 60) * $item['minutes'], 2);
                }
                $subtotal += $cost;
                if (!empty($item['tax']) && !empty($data['data']['tax_rate'])) {
                    $this_tax = round($cost * ($data['data']['tax_rate'] * .01), 2);
                    $tax += $this_tax;
                }
                // Update total with new hourly
                $q1 = $this->update("
                    UPDATE `ppSD_invoice_components`
                    SET `unit_price`='" . $this->mysql_clean($cost) . "',`hourly`='" . $this->mysql_clean($hourly) . "'
                    WHERE `id`='" . $this->mysql_clean($item['id']) . "'
                    LIMIT 1
                ");
            } else if ($item['type'] == 'credit') {
                $credits += $item['unit_price'];
                // $cost = $item['unit_price'];
                // $subtotal -= $item['unit_price'];
            }
        }
        // Payments
        $q12 = $this->run_query("
            SELECT `paid`
            FROM `ppSD_invoice_payments`
            WHERE `invoice_id`='" . $this->mysql_clean($invoice_id) . "'
        ");
        $payments = 0;
        while ($row = $q12->fetch()) {
            $payments += $row['paid'];
        }
        // Due
        $due = $subtotal + $shipping + $tax - $credits - $payments;
        // paid	due	subtotal	shipping	tax	tax_rate	credits
        $update = $this->update("
            UPDATE `ppSD_invoice_totals`
            SET
              `subtotal`='" . $this->mysql_clean($subtotal) . "',
              `shipping`='" . $this->mysql_clean($shipping) . "',
              `paid`='" . $this->mysql_clean($payments) . "',
              `tax`='" . $this->mysql_clean($tax) . "',
              `credits`='" . $this->mysql_clean($credits) . "',
              `due`='" . $this->mysql_clean($due) . "'
            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'
            LIMIT 1
        ");
        $this->get_invoice($invoice_id, '1');
        if ($due <= 0) {
            // New invoices may have new balance
            // initially. So don't mess with anything.
            if ($data['data']['status'] == '9') {
                return '9';
            } else {
                if ($data['data']['status'] != '1') {
                    $this->close_invoice($invoice_id);
                } else {
                    $this->update_status($invoice_id, '1');
                    $this->get_invoice($invoice_id, '1');
                }
                return '1';
            }
        } else {
            if ($data['data']['status'] == '1') {
                $this->update_status($invoice_id, '0');
                $this->extend_due_date($invoice_id);
                if ($data['data']['auto_inform'] == '1') {
                    $this->send_invoice($invoice_id, '1');
                }
                $this->get_invoice($invoice_id, '1');
                return '0';

            } else if ($data['data']['date_due'] > current_date()) {
                $this->update_status($invoice_id, '0');
                $this->get_invoice($invoice_id, '1');
                return '3';

            } else {
                $this->get_invoice($invoice_id, '1');
                return $data['data']['status'];

            }
        }
    }


    function extend_due_date($invoice_id, $extend = '')
    {

        if (empty($extend)) {
            $extend = $this->get_option('invoice_due_date');

        }
        $new_date = add_time_to_expires($extend);
        $q1 = $this->update("

            UPDATE `ppSD_invoices`

            SET `date_due`='" . $this->mysql_clean($new_date) . "'

            WHERE `id`='" . $this->mysql_clean($invoice_id) . "'

            LIMIT 1

        ");
        return $new_date;

    }


    /**
     * @param string $invoice_id
     * @param string $component_id
     */
    function get_component($component_id)
    {

        $q1 = $this->get_array("

            SELECT * FROM `ppSD_invoice_components`

            WHERE `id`='" . $this->mysql_clean($component_id) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            $q1['error'] = '0';
            return $q1;

        } else {
            return array('error' => '1');

        }

    }


    /**
     * @param string $id Status
     */
    function get_stamp($status)
    {

        if ($status == '1') {
            return '<div id="stamp"><img src="' . PP_URL . '/" border="0" /></div>';

        } else {
            return '';

        }

    }


    /**
     * E-Mail invoice.
     * @param string $id
     * $update = 0 -> invoice
     * $update = 1 -> invoice_updated
     * $update = 2 -> invoice_closed

     */
    function send_invoice($id, $update = '0')
    {
        $invoice = $this->get_invoice($id);
        $changes = array(
            'invoice' => $invoice['data'],
            'billing' => $invoice['billing'],
            'format_billing' => $invoice['billing']['formatted'],
            'shipping' => $invoice['shipping'],
            'format_shipping' => $invoice['shipping']['formatted'],
            'pricing' => $invoice['format_totals'],
            'components' => $invoice['data']['comps'],
            'components_raw' => $invoice['components'],
            'payments' => $invoice['data']['payments'],
        );

        $data = array();
        if (! empty($invoice['billing']['email'])) {
            $data = array(
                'to' => $invoice['billing']['email'],
            );
        }

        if ($update == '1') {
            $template = 'invoice_updated';
        }
        else if ($update == '2') {
            $template = 'invoice_closed';
        }
        else if ($update == '3') {
            $template = 'invoice_due';
        }
        else if ($update == '4') {
            $template = 'invoice_overdue';
        }
        //else if ($update == '5') {
        //    $template = 'invoice_new_but_empty';
        //}
        else {
            $template = 'invoice';
        }
        $email = new email('', $invoice['data']['member_id'], $invoice['data']['member_type'], $data, $changes, $template);

    }


    /**
     * Render Invoice as PDF

     */
    /*

    function pdf($id)

    {

        $invoice = $this->get_invoice($id);

        $changes = array(

            'invoice' => $invoice['data'],

            'billing' => $invoice['billing'],

            'format_billing' => $invoice['billing']['formatted'],

            'shipping' => $invoice['shipping'],

            'format_shipping' => $invoice['shipping']['formatted'],

            'pricing' => $invoice['format_totals'],

            'components' => $invoice['data']['comps'],

        );

        $template = new template('invoice_pdf',$changes,'0');

        // DomPDF

        require PP_PATH . "/custom/dompdf/dompdf_config.inc.php";

        $dompdf = new DOMPDF();

        $dompdf->load_html($template);

        $dompdf->render();

        $dompdf->stream($id . '.pdf');

    }

    */
    /**
     * Render Printable Invoice

     */
    function generate_template($id, $template, $headers = '0')
    {

        // Invoice
        $invoice = $this->get_invoice($id);

        $changes = array(
            'invoice' => $invoice['data'],
            'billing' => $invoice['billing'],
            'format_billing' => $invoice['billing']['formatted'],
            'shipping' => $invoice['shipping'],
            'format_shipping' => $invoice['shipping']['formatted'],
            'pricing' => $invoice['format_totals'],
            'components' => $invoice['data']['comps'],
            'components_raw' => $invoice['components'],
            'payments' => $invoice['data']['payments'],
        );

        $template = new template($template, $changes, $headers);
        return $template;

    }


    /**
     * Render invoice components
     * @param array $components From $invoice->get_invoice();
     */
    function render_components($components)
    {

        $comps = '';
        foreach ($components as $item) {
            if ($item['qty'] <= 0) {
                $item['qty'] = '1';

            }
            if ($item['type'] == 'credit') {
                $item['total'] = place_currency($item['unit_price']);
                $item['unit_price'] = place_currency($item['unit_price']);
                $template_name = 'invoice_credit_entry';

            } else if ($item['type'] == 'time') {
                $item['total'] = place_currency($item['unit_price']);
                $item['unit_price'] = place_currency($item['hourly']) . '/hour';
                $template_name = 'invoice_time_entry';

            } else {
                $item['total'] = place_currency($item['unit_price'] * $item['qty']);
                $item['unit_price'] = place_currency($item['unit_price']);
                $template_name = 'invoice_entry';

            }

            $item['hours'] = round($item['minutes'] / 60, 2);
            $item['hours_rounded'] = ceil($item['minutes'] / 60);
            
            $item['hourly'] = place_currency($item['hourly']);
            $template = new template($template_name, $item, '0');
            $comps .= $template;

        }
        return $comps;

    }

}

