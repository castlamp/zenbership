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

// ----------------------------
// List Members

if ($options['list'] == '1') {
    $cart      = new cart;
    $admin     = new admin;
    $user      = new user;
    $contact   = new contact;
    $timeframe = $admin->construct_timeframe($options['increments'], $options['unit']);
    $use_date  = add_time_to_expires($timeframe);
    $found     = 0;
    $list      = '';
    $STH       = $this->run_query("
        SELECT *
        FROM `ppSD_subscriptions`
        WHERE
            `next_renew`<='$use_date' AND
            `status`='1'
        ORDER BY `next_renew` ASC
        LIMIT " . $this->mysql_cleans($options['limit']) . "
    ");
    while ($row = $STH->fetch()) {
        $found = 1;
        // Product
        $product = $cart->get_product($row['product']);
        // Entry
        $this_entry = '<td><a href="returnull.php" onclick="return load_page(\'subscription\',\'view\',\'' . $row['id'] . '\');">' . format_date($row['next_renew']) . '</a></td>';
        if ($row['member_type'] == 'member') {
            $this_entry .= '<td><a href="returnull.php" onclick="return load_page(\'member\',\'view\',\'' . $row['member_id'] . '\');">' . $user->get_username($row['member_id']) . '</a></td>';
        } else if ($row['member_type'] == 'contact') {
            $this_entry .= '<td><a href="returnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $row['member_id'] . '\');">' . $contact->get_name($row['member_id']) . '</a></td>';
        } else {
            $this_entry .= '<td class="weak">N/A</td>';
        }
        $this_entry .= '<td><a href="returnull.php" onclick="return popup(\'product-add\',\'id=' . $row['product'] . '\');">' . $product['data']['name'] . '</td>';
        $this_entry .= '<td>' . place_currency($row['price']) . '</td>';
        // Row
        $list .= '<tr>';
        $list .= $this_entry;
        $list .= '</tr>';
    }
    if ($found <= 0) {
        $list .= '<tr><td colspan="3" class="weak">Nothing to display.</td></tr>';
    }
    $final_list = "
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"widget\">
        <thead>
            <tr>
                <th>Next Renewal</th>
                <th>User</th>
                <th>Product</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            $list
        </tbody>
        </table>
    ";
} else {
    $final_list = '';
}

// ----------------------------
//  Graph Data
if ($options['graph'] == '1') {
    $graph_id   = uniqid();
    $gdata      = array(
        'int'  => $options['increments'],
        'unit' => $options['unit'],
    );
    $graph      = array(
        array(
            'title' => 'Subscription Income',
            'key'   => 'renewal_income',
        ),
        array(
            'title' => 'Success',
            'key'   => 'renewals',
            'type'  => 'line',
        ),
        array(
            'title' => 'Failed',
            'key'   => 'renewals_failed',
            'type'  => 'line',
        ),
    );
    $options    = array(
        'title'      => 'Subscription Income',
        'element'    => $graph_id,
        'increments' => $gdata['int'],
        'type'       => $gdata['unit'],
    );
    $graph_outA = new graph($graph, $options);
    $graph_outA .= '<div id="' . $graph_id . '" class="graph_box_widget" style="height:250px;"></div>';
} else {
    $graph_outA = '';
}

?>

<div class="nontable_section">
    <div class="pad24">
        <div class="widget_full"><a href="index.php?l=subscriptions">Full List &raquo;</a></div>
        <h2><?php echo $data['title']; ?></h2>

        <div class="nontable_section_inner">
            <div class="pad24">
                <?php echo $final_list; ?>
                <?php echo $graph_outA; ?>
            </div>
        </div>
    </div>
</div>