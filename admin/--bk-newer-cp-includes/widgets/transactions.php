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
    $cart  = new cart;
    $found = 0;
    $list  = '';
    $STH   = $this->run_query("
        SELECT `id`,`date_completed`
        FROM `ppSD_cart_sessions`
        WHERE `status`='1'
        ORDER BY `date_completed` DESC
        LIMIT " . $this->mysql_cleans($options['limit']) . "
    ");
    while ($row = $STH->fetch()) {
        $found = 1;
        // Entry
        $order      = $cart->calculate_order_total($row['id'], '0');
        $this_entry = '<td><a href="returnull.php" onclick="return load_page(\'transaction\',\'view\',\'' . $row['id'] . '\');">' . $row['id'] . '</a></td>';
        $this_entry .= '<td>' . format_date($row['date_completed']) . '</td>';
        $this_entry .= '<td>' . place_currency($order['order_total']) . '</td>';
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
                <th>Order No</th>
                <th>Date</th>
                <th>Total</th>
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
            'title' => 'Sales',
            'key'   => 'revenue',
        ),
        array(
            'title' => 'Transactions',
            'key'   => 'sales',
        ),
    );
    $options    = array(
        'title'      => $data['title'],
        'element'    => $graph_id,
        'increments' => $gdata['int'],
        'type'       => $gdata['unit'],
        'yaxis'      => array(
            array(
                'title'      => '',
                'line_width' => '3',
            ),
            array(
                'title'      => '',
                'line_width' => '3',
                'type'       => 'line',
            ),
        ),
    );
    $graph_outA = new graph($graph, $options);
    $graph_outA .= '<div id="' . $graph_id . '" class="graph_box_widget" style="height:250px;"></div>';
} else {
    $graph_outA = '';
}

?>

<div class="nontable_section">
    <div class="pad24">
        <div class="widget_full"><a href="index.php?l=transactions">Full List &raquo;</a></div>
        <h2><?php echo $data['title']; ?></h2>

        <div class="nontable_section_inner">
            <div class="pad24">
                <?php echo $final_list; ?>
                <?php echo $graph_outA; ?>
            </div>
        </div>
    </div>
</div>