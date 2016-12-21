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

$permission = 'cart-report';
$check = $admin->check_permissions($permission,$employee);
if ($check != '1') {
	$admin->show_no_permissions();
} else {

    // Date range
    $date = current_date();
    $exp = explode(' ', $date);
    $exp_date = explode('-', $exp['0']);
    if (! empty($_GET['start_date'])) {
        $start = $_GET['start_date'];
    } else {
        $start = $exp_date['0'] . '-' . $exp_date['1'] . '-01';
    }
    if (! empty($_GET['end_date'])) {
        $end = $_GET['end_date'];
    } else {
        $end = $exp_date['0'] . '-' . $exp_date['1'] . '-31';
    }
    
    if ($start > $end) {
        $temp = $start;
        $start = $end;
        $end = $temp;
    }
    
    $days_in_range = ceil((strtotime($end) - strtotime($start)) / 86400);
    
    $cart = new cart;
    $invoice = new invoice;
    
    // Run the reports.
    $total_paid = 0;
    $total_unpaid = 0;
    $total_rejected = 0;
    $total_refunds = 0;
    $total = 0;
    $subtotal = 0;
    $savings = 0;
    $shipping = 0;
    $tax = 0;
    $fees = 0;
    
    $shippable = 0;
    $shipped = 0;
    $unshipped = 0;
    
    $smallest = 0;
    $largest = 0;
    $average = 0;
    $daily_average = 0;
    $units_sold = 0;
    
    $countries = array();
    $countries_totals = array();
    $countries_percent = array();
    $states = array();
    $states_totals = array();
    $states_percent = array();
    
    // -----------------------------
    // TRANSACTIONS
    
    $query = $db->run_query("
        SELECT `id`
        FROM `ppSD_cart_sessions`
        WHERE ( `date_completed`>='" . $db->mysql_clean($start) . "' AND `date_completed`<='" . $db->mysql_clean($end) . "' )
    ");
    while ($row = $query->fetch()) {
    
        $order = $cart->get_order($row['id'], '0');
        $totals = $order['pricing'];
        
        if (array_key_exists($order['data']['country'], $countries)) {
            $countries[$order['data']['country']] += $totals['total'];
            $countries_totals[$order['data']['country']] += 1;
        } else {
            $countries[$order['data']['country']] = $totals['total'];
            $countries_totals[$order['data']['country']] = 1;
        }
        if (array_key_exists($order['data']['state'], $states)) {
            $states[$order['data']['state']] += $totals['total'];
            $states_totals[$order['data']['state']] += 1;
        } else {
            $states[$order['data']['state']] = $totals['total'];
            $states_totals[$order['data']['state']] = 1;
        }
    
        $units_sold += $order['data']['total_items'];
        
        if (! empty($row['shipping_rule'])) {
            $shippable++;
            $shipping = $cart->get_shipping($row['id']);
            if ($shipping['shipped'] == '1') {
                $shipped++;
            } else {
                $unshipped++;
            }
        }
        
        // Smallest?
        if ($totals['total'] <= $smallest || empty($smallest)) {
            $smallest = $totals['total'];
        }
        
        // Largest?
        if ($totals['total'] >= $largest) {
            $largest = $totals['total'];
        }
    
        // Approved
        if ($order['data']['status'] == '1') {
            $total_paid++;
            $total += $totals['total'];
            $subtotal += $totals['subtotal'];
            $shipping += $totals['shipping'];
            $tax += $totals['tax'];
            $fees += $totals['gateway_fees'];
            
        }
        // Refunded
        else if ($order['data']['status'] == '3' || $order['data']['status'] == '4') {
            $total_refunds++;
            
        }
        // Rejected
        else if ($order['data']['status'] == '9') {
            $total_rejected++;
            
        }
    
    }
    
    if ($total_paid > 0) {
        $average = $total / $total_paid;
        $daily_average = $total / $days_in_range;
    }
    
    $total_no_tax = $total - $tax;
    
    foreach ($countries_totals as $name => $dollars) {
        $percent = $dollars / $total_paid;
        $countries_percent[$name] = number_format($percent * 100, 2);
    }
    foreach ($states_totals as $name => $dollars) {
        $percent = $dollars / $total_paid;
        $states_percent[$name] = number_format($percent * 100, 2);
    }
    
    
    // -----------------------------
    // SUBSCRIPTIONS
    
    $failed = 0;
    $renewed = 0;
    $renewing = 0;
    $canceled = 0;
    $created = 0;
    $created_value = 0;
    $renewed_value = 0;
    $renewing_value = 0;
    $canceled_value = 0;

    $query_count = $db->get_array("
        SELECT
            COUNT(*) AS total,
            SUM(price) AS price
        FROM `ppSD_subscriptions`
        WHERE `date`>='" . $db->mysql_clean($start) . "' AND `date`<='" . $db->mysql_clean($end) . "'
    ");
    $created = $query_count['total'];
    $created_value = $query_count['price'];

    $queryB = $db->run_query("
        SELECT
            `id`,
            SUM(unit_price * qty) AS money
        FROM
            `ppSD_cart_items_complete`
        WHERE
            `date`>='" . $db->mysql_clean($start) . "' AND
            `date`<='" . $db->mysql_clean($end) . "' AND
            `subscription_id`!=''
    ");
    while ($rowB = $queryB->fetch()) {
        if (! empty($row['id'])) {
            $renewed++;
            $renewed_value += $row['money'];
        }
    }

    $queryB = $db->run_query("
        SELECT `next_renew`,`price`,`status`
        FROM `ppSD_subscriptions`
        WHERE ( `next_renew`>='" . $db->mysql_clean($start) . "' AND `next_renew`<='" . $db->mysql_clean($end) . "' ) AND
        `status`='1'
    ");
    while ($rowB = $queryB->fetch()) {
            $renewing++;
            $renewing_value += $rowB['price'];
    }

    $queryU = $db->run_query("
        SELECT `cancel_date`,`price`,`retry`
        FROM `ppSD_subscriptions`
        WHERE ( `cancel_date`>='" . $db->mysql_clean($start) . "' AND `cancel_date`<='" . $db->mysql_clean($end) . "' ) AND
            `status`='1'
    ");
    while ($rowU = $queryU->fetch()) {
    
        $canceled++;
        $canceled_value += $rowU['price'];
        
        if ($rowU['retry'] > 0) {
            $failed += $rowU['retry'];
        }
    
    }
    
    
    
    // -----------------------------
    // INVOICES
    
    $invoices_all = 0;
    $unpaid_invoices = 0;
    $paid_invoices = 0;
    $partially_paid_invoices = 0;
    $dead_invoices = 0;
    $overdue_invoices = 0;
    $in_paid = 0;
    $in_due = 0;
    $in_paid_dead = 0;
    $in_due_dead = 0;
    $in_paid_overdue = 0;
    $in_due_overdue = 0;
    $partial_paid_total = 0;
    $partial_paid_due = 0;
    
    $queryA = $db->run_query("
        SELECT *
        FROM `ppSD_invoices`
        WHERE ( `date`>='" . $db->mysql_clean($start) . "' AND `date`<='" . $db->mysql_clean($end) . "' )
    ");
    while ($rowA = $queryA->fetch()) {
        
        $invoices_all++;
        
        $data = $invoice->get_invoice($rowA['id']);
        
        // pa($data['totals']);
        
        $in_due += $data['totals']['due'];
        $in_paid += $data['totals']['paid'];
        
        // Unpaid
        if ($rowA['status'] == '0') {
            
            if ($rowA['date_due'] <= $date) {
                $overdue_invoices++;
                $in_paid_overdue = $data['totals']['paid'];
                $in_due_overdue = $data['totals']['due'];
            } else {
                $unpaid_invoices++;
            }
            
        }
        // Paid
        else if ($rowA['status'] == '1') {
            
            $paid_invoices++;
            
        }
        // Partial
        else if ($rowA['status'] == '2') {
        
            $partially_paid_invoices++;
            $partial_paid_total = $data['totals']['paid'];
            $partial_paid_due = $data['totals']['due'];
            
        }
        // Overdue
        else if ($rowA['status'] == '3') {
        
            $overdue_invoices++;
            $in_paid_overdue = $data['totals']['paid'];
            $in_due_overdue = $data['totals']['due'];
        
        }
        // Dead
        else if ($rowA['status'] == '4') {
        
            $dead_invoices++;
            $in_paid_dead = $data['totals']['paid'];
            $in_due_dead = $data['totals']['due'];
            
        }
        
    }
    
    $in_settled = $in_paid - $partial_paid_total;

    $sub_based = round(($created_value + $renewed_value) / $total,2) * 100;

?>

<form action="index.php" method="get">
<input type="hidden" name="l" value="<?php echo $_GET['l']; ?>" />
<div id="topblue" class="fonts small"><div class="holder">
	<div class="floatright" id="tb_right">
		<?php
        $datepickstart = $admin->datepicker('start_date', $start, '0', '100');
        $datepickend = $admin->datepicker('end_date', $end, '0', '100');
        echo $datepickstart; ?> to <?php echo $datepickend; ?> <input type="submit" value="Go" class="blue " />
	</div>
	<div class="floatleft" id="tb_left">
		<b>Shopping Cart Report</b>
	</div>
	<div class="clear"></div>
</div></div>
</form>
	
<div id="mainsection">
			
    <div class="nontable_section" style="margin-bottom: -42px;">
        <div class="pad24notop">
            <h1>Shop Report: <?php
                echo $start;
            ?> to <?php echo $end; ?></h1>
        </div>
    </div>
			
    <div class="col50l">
    			
        <div class="nontable_section">
            <div class="pad24">
                
                <h2 class="">Totals</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <span class="tcol0">Number</span>
                                <span class="tcol1">Total</span>
                            </dd>
                            <dt>Total</dt>
                            <dd>
                                <span class="tcol0 bold"><?php echo $total_paid; ?></span>
                                <span class="tcol1 bold"><?php echo place_currency($total); ?></span>
                            </dd>
                            <dt>Subtotal</dt>
                            <dd>
                                <span class="tcol0">-</span>
                                <span class="tcol1"><?php echo place_currency($subtotal); ?></span>
                            </dd>
                            <dt>Shipping</dt>
                            <dd>
                                <span class="tcol0"><?php echo $shippable; ?></span>
                                <span class="tcol1"><?php echo place_currency($shipping); ?></span>
                            </dd>
                            <dt>Tax</dt>
                            <dd>
                                <span class="tcol0">-</span>
                                <span class="tcol1"><?php echo place_currency($tax); ?></span>
                            </dd>
                            <dt>Savings</dt>
                            <dd>
                                <span class="tcol0">-</span>
                                <span class="tcol1">(<?php echo place_currency($savings); ?>)</span>
                            </dd>
                            <dt>Fees</dt>
                            <dd>
                                <span class="tcol0">-</span>
                                <span class="tcol1">(<?php echo place_currency($fees); ?>)</span>
                            </dd>
                            <dt>Units Sold</dt>
                            <dd><?php echo $units_sold; ?></dd>
                        </dl>
                        <div class="clear"></div>
                        
                    </div>
                </div>
                
                <h2 class="margintopmore">Averages</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>Smallest</dt>
                            <dd><?php echo place_currency($smallest); ?></dd>
                            <dt>Largest</dt>
                            <dd><?php echo place_currency($largest); ?></dd>
                            <dt>Average</dt>
                            <dd><?php echo place_currency($average); ?></dd>
                            <dt>Daily Average</dt>
                            <dd><?php echo place_currency($daily_average); ?>/day</dd>
                        </dl>
                        
                        <div class="clear"></div>
                    </div>
                </div>
                
                
                <h2 class="margintopmore">By Country</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <span class="tcol0">Number</span>
                                <span class="tcol1">Total</span>
                                <span class="tcol2">Percent</span>
                            </dd>
                            <?php
                            foreach ($countries as $name => $dollars) {
                                echo "<dt>" . $name . "</dt>";
                                echo "<dd>
                                    <span class=\"tcol0\">" . $countries_totals[$name] . "</span>
                                    <span class=\"tcol1\">" . place_currency($dollars) . "</span>
                                    <span class=\"tcol2\">" . $countries_percent[$name] . "%</span>
                                </dd>";
                            }
                            ?>
                        </dl>
                        
                        <div class="clear"></div>
                    </div>
                </div>
                
                <h2 class="margintopmore">By State/Province</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <span class="tcol0">Number</span>
                                <span class="tcol1">Total</span>
                                <span class="tcol2">Percent</span>
                            </dd>
                            <?php
                            foreach ($states as $name => $dollars) {
                                echo "<dt>" . $name . "</dt>";
                                echo "<dd>
                                    <span class=\"tcol0\">" . $states_totals[$name] . "</span>
                                    <span class=\"tcol1\">" . place_currency($dollars) . "</span>
                                    <span class=\"tcol2\">" . $states_percent[$name] . "%</span>
                                </dd>";
                            }
                            ?>
                        </dl>
                        
                        <div class="clear"></div>
                    </div>
                </div>
                
                
                
            </div>
        </div>

    </div>
    <div class="col50r">
        
        <div class="nontable_section">
            <div class="pad24">
            
                <h2>Invoices</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <span class="tcol0">Number</span>
                                <span class="tcol1">Paid</span>
                                <span class="tcol2">Unpaid</span>
                            </dd>
                            <dt>Total</dt>
                            <dd>
                                <span class="tcol0 bold"><?php echo $invoices_all; ?></span>
                                <span class="tcol1 bold"><?php echo place_currency($in_paid); ?></span>
                                <span class="tcol2 bold"><?php echo place_currency($in_due); ?></span>
                            </dd>
                            <dt>Settled</dt>
                            <dd>
                                <span class="tcol0"><?php echo $paid_invoices; ?></span>
                                <span class="tcol1"><?php echo place_currency($in_settled); ?></span>
                                <span class="tcol2 bold">-</span>
                            </dd>
                            <dt>Unsettled</dt>
                            <dd>
                                <span class="tcol0"><?php echo $unpaid_invoices; ?></span>
                                <span class="tcol1">-</span>
                                <span class="tcol2"><?php echo place_currency($in_due); ?></span>
                            </dd>
                            <dt>Partially Paid</dt>
                            <dd>
                                <span class="tcol0"><?php echo $partially_paid_invoices; ?></span>
                                <span class="tcol1"><?php echo place_currency($partial_paid_total); ?></span>
                                <span class="tcol2"><?php echo place_currency($partial_paid_due); ?></span>
                            </dd>
                            <dt>Overdue</dt>
                            <dd>
                                <span class="tcol0"><?php echo $overdue_invoices; ?></span>
                                <span class="tcol1"><?php echo place_currency($in_paid_overdue); ?></span>
                                <span class="tcol2"><?php echo place_currency($in_due_overdue); ?></span>
                            </dd>
                            <dt>Dead</dt>
                            <dd>
                                <span class="tcol0"><?php echo $dead_invoices; ?></span>
                                <span class="tcol1"><?php echo place_currency($in_paid_dead); ?></span>
                                <span class="tcol2"><?php echo place_currency($in_due_dead); ?></span>
                            </dd>
                        </dl>
                        <div class="clear"></div>
                        
                    </div>
                </div>
                
                <h2 class="margintopmore">Subscriptions</h2>
                <div class="nontable_section_inner">
                    <div class="pad24">
                    
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <span class="tcol0">Number</span>
                                <span class="tcol1">Total</span>
                            </dd>
                            <dt>Renewed</dt>
                            <dd>
                                <span class="tcol0"><?php echo $renewed; ?></span>
                                <span class="tcol1"><?php echo place_currency($renewed_value); ?></span>
                            </dd>
                            <dt>Renewing</dt>
                            <dd>
                                <span class="tcol0"><?php echo $renewing; ?></span>
                                <span class="tcol1"><?php echo place_currency($renewing_value); ?></span>
                            </dd>
                            <dt>Failed</dt>
                            <dd>
                                <span class="tcol0"><?php echo $failed; ?></span>
                                <span class="tcol1">-</span>
                            </dd>
                            <dt>Created</dt>
                            <dd>
                                <span class="tcol0"><?php echo $created; ?></span>
                                <span class="tcol1"><?php echo place_currency($created_value); ?></span>
                            </dd>
                            <dt>Canceled</dt>
                            <dd>
                                <span class="tcol0"><?php echo $canceled; ?></span>
                                <span class="tcol1"><?php echo place_currency($canceled_value); ?></span>
                            </dd>
                            <dt>Percent</dt>
                            <dd>
                                <span class="tcol0"><?php echo $sub_based; ?>%</span>
                                <span class="tcol1"></span>
                             </dd>
                        </dl>
                        <div class="clear"></div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    
    </div>
    <div class="clear"></div>
    
</div>

<?php
}
?>