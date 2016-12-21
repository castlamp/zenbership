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

$permission = 'cart-estimated-income';
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

    $sub = new subscription();

    $using_range = (strtotime($end) - strtotime($start)) / 86400;

    $year_ago = date('Y-m-d H:i:s', strtotime(current_date()) - 31557600 );
    $queryB = $db->get_array("
        SELECT
          COUNT(*)
        FROM
            `ppSD_subscriptions`
        WHERE
          status != '1' AND
          `cancel_date` >= '" . $year_ago . "-01-01' AND `cancel_date` <= '" . current_date() . "-'
    ");
    $queryV = $db->get_array("
        SELECT
          COUNT(*)
        FROM
            `ppSD_subscriptions`
        WHERE
          status = '1'
    ");

    $canceled = $queryB['0'];
    $total_active = $queryV['0'];
    $drop_rate = $canceled / $total_active;

    $estimated_sub_income = 0;
    $estimated_charges = 0;

    $queryB = $db->run_query("
        SELECT
            ppSD_subscriptions.next_renew,
            ppSD_subscriptions.price,
            ppSD_products.renew_timeframe
        FROM
            `ppSD_subscriptions`
        JOIN
            `ppSD_products`
        ON
            ppSD_subscriptions.product=ppSD_products.id
        WHERE
          ppSD_subscriptions.next_renew <= '" . $db->mysql_clean($end) . "' AND
          ppSD_subscriptions.status = '1'
    ");
    while ($rowB = $queryB->fetch()) {

        $remain = $sub->check_remaining($rowB['next_renew'], $rowB['renew_timeframe'], $rowB['price'], $end);
        $estimated_charges += $remain['charges'];
        $estimated_sub_income += $remain['total'];

    }

    $plusminus = $estimated_sub_income * $drop_rate;
    $plusminus = ($plusminus / 365) * $using_range;

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

            <h2 class="margintopmore">Estimated Future Subscription Income</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Total</dt>
                        <dd><?php echo place_currency($estimated_sub_income); ?></dd>
                        <dt>+/-</dt>
                        <dd><?php echo place_currency($plusminus); ?></dd>
                        <dt>Charges</dt>
                        <dd><?php echo $estimated_charges; ?></dd>
                    </dl>

                    <div class="clear"></div>
                </div>
            </div>

        </div>
    </div>

    </div>
    <div class="col50r">


    </div>
    <div class="clear"></div>
    
</div>

<?php
}
?>