<?php


/**
 * List of sources
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

$permission = 'sources';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();
} else {

    $source = new source();
    $metrics = $source->tracking_report($_GET['id']);
?>

    <div id="topblue" class="fonts small"><div class="holder">
            <div class="floatright" id="tb_right">
                &nbsp;
            </div>
            <div class="floatleft" id="tb_left">
                <b>Viewing Source Report</b>

                <span class="div">|</span>

                <a href="index.php?l=sources">Sources</a>

                <span class="div">|</span>

                <a href="index.php?l=source_tracking">Tracking</a>
            </div>
            <div class="clear"></div>
        </div></div>

    <div id="mainsection">

        <div class="nontable_section" style="margin-bottom: -42px;">
            <div class="pad24notop">
                <h1><img src="imgs/report-source_tracking.png" alt="" class="iconLarge" />Source Report</h1>
            </div>
        </div>

        <div class="nontable_section">
            <div class="pad24" style="position:relative;">

                <div class="col-4">
                    <h2>Users Arriving</h2>
                    <table class="generic">
                        <thead>
                        <tr>
                            <th width="150">Metric</th>
                            <th>Total</th>
                            <th>Percent</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Total</td>
                            <td><?php echo $metrics['total']; ?></td>
                            <td>100%</td>
                        </tr>
                        <tr>
                            <td>Variation A</td>
                            <td><?php echo $metrics['total-A'] ?></td>
                            <td><?php echo $metrics['total-percent-A'] ?>%</td>
                        </tr>
                        <tr>
                            <td>Variation B</td>
                            <td><?php echo $metrics['total-B'] ?></td>
                            <td><?php echo $metrics['total-percent-B'] ?>%</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-75 padding-left">
                    <h2>Conversions</h2>

                    <table class="generic">
                        <thead>
                        <tr>
                            <th width="150">Metric</th>
                            <th>Total</th>
                            <th>Percent</th>
                            <th>Income</th>
                            <th>Income %</th>
                            <th>Income Per Conversion</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Total</td>
                            <td><?php echo $metrics['total-converted']; ?></td>
                            <td><?php echo $metrics['converted-percent']; ?>%</td>
                            <td><?php echo place_currency($metrics['income']); ?></td>
                            <td>100%</td>
                            <td><?php echo place_currency($metrics['income-per']); ?></td>
                        </tr>
                        <tr>
                            <td>Variation A</td>
                            <td><?php echo $metrics['total-converted-A']; ?></td>
                            <td><?php echo $metrics['converted-percent-A']; ?>%</td>
                            <td><?php echo place_currency($metrics['income-A']); ?></td>
                            <td><?php echo $metrics['income-percent-A']; ?>%</td>
                            <td><?php echo place_currency($metrics['income-per-A']); ?></td>
                        </tr>
                        <tr>
                            <td>Variation B</td>
                            <td><?php echo $metrics['total-converted-B']; ?></td>
                            <td><?php echo $metrics['converted-percent-B']; ?>%</td>
                            <td><?php echo place_currency($metrics['income-B']); ?></td>
                            <td><?php echo $metrics['income-percent-B']; ?>%</td>
                            <td><?php echo place_currency($metrics['income-per-B']); ?></td>
                        </tr>
                        </tbody>
                        </table>


                    <!--
                    <div class="col-2">
                        <h2 style="margin-top:24px;">Contacts</h2>
                        <ul class="home_ul">
                            <?php
                            foreach ($metrics['contacts'] as $id => $name) {
                            ?>
                                <li><a href=""><?php echo $name; ?></a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="col-2">
                        <h2 style="margin-top:24px;">Members</h2>
                        <ul class="home_ul">
                            <?php
                            foreach ($metrics['members'] as $id => $name) {
                                ?>
                                <li><a href=""><?php echo $name; ?></a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    -->



                    <div class="col-2 pad-right">

                    <h2 style="margin-top:24px;">Conversions to Contacts</h2>

                    <table class="generic">
                        <thead>
                        <tr>
                            <th width="150">Metric</th>
                            <th>Total</th>
                            <th>Percent</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Contacts</td>
                            <td><?php echo $metrics['total-contacts']; ?></td>
                            <td><?php echo $metrics['total-contacts-percent']; ?>%</td>
                        </tr>
                        <tr>
                            <td>Contacts (Var A)</td>
                            <td><?php echo $metrics['total-contacts-A']; ?></td>
                            <td><?php echo $metrics['total-contacts-percent-A']; ?>%</td>
                        </tr>
                        <tr>
                            <td>Contacts (Var B)</td>
                            <td><?php echo $metrics['total-contacts-B']; ?></td>
                            <td><?php echo $metrics['total-contacts-percent-B']; ?>%</td>
                        </tr>
                        </tbody>
                        </table>

                        <a style="display:block;margin-top:12px;" href="index.php?l=contacts&filters[]=<?php echo $_GET['id']; ?>||source||like||">View List &raquo;</a>

                    </div>
                    <div class="col-2 pad-left">

                    <h2 style="margin-top:24px;">Conversions to Members</h2>

                    <table class="generic">
                        <thead>
                        <tr>
                            <th width="150">Metric</th>
                            <th>Total</th>
                            <th>Percent</th>
                        </tr>
                        </thead>
                        <tbody>
                            <td>Members</td>
                            <td><?php echo $metrics['total-members']; ?></td>
                            <td><?php echo $metrics['total-members-percent']; ?>%</td>
                        </tr>
                        <tr>
                            <td>Members (Var A)</td>
                            <td><?php echo $metrics['total-members-A']; ?></td>
                            <td><?php echo $metrics['total-members-percent-A']; ?>%</td>
                        </tr>
                        <tr>
                            <td>Members (Var B)</td>
                            <td><?php echo $metrics['total-members-B']; ?></td>
                            <td><?php echo $metrics['total-members-percent-B']; ?>%</td>
                        </tr>
                        </tbody>
                    </table>

                    <a style="display:block;margin-top:12px;" href="index.php?l=members&filters[]=<?php echo $_GET['id']; ?>||source||like||">View List &raquo;</a>

                    </div>
                    <div class="clear"></div>



                    <h2 style="margin-top:24px;">Transactions</h2>

                    <table class="generic">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Gateway</th>
                            <th>Gateway ID</th>
                            <th>User ID</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($metrics['transaction_logs'] as $item) {
                            foreach ($item['data'] as $entry) {
                                    ?>

                                    <tr>
                                        <td><a href="null.php"
                                               onclick="return load_page('transaction','view','<?php echo $entry['id']; ?>');"><?php echo $entry['id']; ?></a>
                                        </td>
                                        <td><?php echo format_date($entry['date_completed']); ?></td>
                                        <td><?php echo place_currency($entry['total']); ?></td>
                                        <td><?php echo $entry['payment_gateway']; ?></td>
                                        <td><?php echo $entry['gateway_order_id']; ?></td>
                                        <td><?php
                                            if ($entry['member_type'] == 'member') {
                                                ?>
                                                <a href="returnull.php" onclick="return popup('member_view','id=<?php echo $entry['member_id']; ?>');"><?php echo $entry['member_id']; ?></a>
                                                <?php
                                            } else {
                                                ?>
                                                <a href="returnull.php" onclick="return popup('contact_view','id=<?php echo $entry['member_id']; ?>');"><?php echo $entry['member_id']; ?></a>
                                            <?php
                                            }
                                            ?></td>
                                    </tr>

                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>


                </div>

            </div>
        </div>

    </div>

<?php
}
