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
// List Outstanding Invoices

if ($options['list'] == '1') {
    $invoice   = new invoice;
    $user      = new user;
    $contact   = new contact;
    $admin     = new admin;
    $timeframe = $admin->construct_timeframe($options['increments'], $options['unit']);
    $use_date  = add_time_to_expires($timeframe);
    $found     = 0;
    $list      = '';
    $STH       = $this->run_query("
        SELECT `id`
        FROM `ppSD_invoices`
        WHERE `status`='3' OR `status`='4'
        ORDER BY `date_due` ASC
        LIMIT " . $this->mysql_cleans($options['limit']) . "
    ");
    while ($row = $STH->fetch()) {
        $found = 1;
        // Invoice
        $this_invoice = $invoice->get_invoice($row['id']);
        // Entry
        $this_entry = '<td><a href="returnull.php" onclick="return load_page(\'invoice\',\'view\',\'' . $this_invoice['data']['id'] . '\');">' . format_date($this_invoice['data']['date_due']) . '</a></td>';
        if ($this_invoice['data']['member_type'] == 'member') {
            $this_entry .= '<td><a href="returnull.php" onclick="return load_page(\'member\',\'view\',\'' . $this_invoice['data']['member_id'] . '\');">' . $user->get_username($this_invoice['data']['member_id']) . '</a></td>';
        } else if ($this_invoice['data']['member_type'] == 'contact') {
            $this_entry .= '<td><a href="returnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $this_invoice['data']['member_id'] . '\');">' . $contact->get_name($this_invoice['data']['member_id']) . '</a></td>';
        } else {
            $this_entry .= '<td class="weak">N/A</td>';
        }
        $this_entry .= '<td>' . place_currency($this_invoice['totals']['due']) . '</td>';
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
                <th>Date Due</th>
                <th>User</th>
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
            'title' => 'Invoice Revenue',
            'key'   => 'invoice_revenue',
        ),
        array(
            'title' => 'Payments',
            'key'   => 'invoice_payments',
        )
    );
    $options    = array(
        'title'      => 'Invoice Revenue and Payments',
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
        <div class="widget_full"><a href="index.php?l=invoices">Full List &raquo;</a></div>
        <h2><?php echo $data['title']; ?></h2>

        <div class="nontable_section_inner">
            <div class="pad24">
                <?php echo $final_list; ?>
                <?php echo $graph_outA; ?>
            </div>
        </div>
    </div>
</div>