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
    $user     = new user;

    $found = 0;
    $list  = '';
    $STH   = $this->run_query("
        SELECT ppSD_logins.*,ppSD_members.username
        FROM `ppSD_logins`
        JOIN ppSD_members
        ON ppSD_members.id=ppSD_logins.member_id
        ORDER BY ppSD_logins.date DESC
        LIMIT " . $this->mysql_cleans($options['limit']) . "
    ");
    while ($row = $STH->fetch()) {
        $found      = 1;
        $this_entry = '';
        // Row
        $list .= '<tr>';
        $list .= '<td>' . $this->format_date($row['date']) . '</td>';
        $list .= '<td><a href="returnull.php" onclick="return popup(\'member_view\',\'id=' . $row['member_id'] . '\');">' . $row['username'] . '</a></td>';
        $list .= '<td>' . $row['ip'] . '</td>';
        if ($row['status'] == '1') {
            $list .= '<td>Success</td>';
        } else {
            $list .= '<td>Failed</td>';
        }
        $list .= '</tr>';
    }
    if ($found <= 0) {
        $list .= '<tr><td colspan="3" class="weak">Nothing to display.</td></tr>';
    }
    $final_list = "
        <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" class=\"widget\">
        <thead>
            <tr>
                <td>Date</td>
                <td>Username</td>
                <td>IP</td>
                <td>Status</td>
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
            'title' => 'Logins',
            'key'   => 'logins',
        ),
    );
    $options    = array(
        'title'      => 'Recent Logins',
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
        <div class="widget_full"><a href="index.php?l=logins">Full List &raquo;</a></div>
        <h2><?php echo $data['title']; ?></h2>

        <div class="nontable_section_inner">
            <div class="pad24">
                <?php echo $final_list; ?>
                <?php echo $graph_outA; ?>
            </div>
        </div>
    </div>
</div>