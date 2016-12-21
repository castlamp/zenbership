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
// Check permissions, ownership,
// and if it exists.
$permission = 'transaction-view';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions($error, '', '1');
} else {

    $notes = new notes;
    $pinned_notes = $notes->get_pinned_notes($_POST['id']);

    // Ownership
    $cart    = new cart;
    $product = $cart->get_product($_POST['id'], '0');
    // GRAPHING
    $gdata = $admin->get_graph_array($_POST);
    // Graph 1
    $graph      = array(
        array(
            'title' => 'Views',
            'key'   => 'product_views-' . $_POST['id'],
        ),
        array(
            'title' => 'Units Sold',
            'key'   => 'product_sale-' . $_POST['id'],
        ),
    );
    $options    = array(
        'title'      => 'Product Views vs Sales',
        'element'    => 'stats_graphA',
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
    // Graph 2
    $graph      = array(
        array(
            'title' => 'Product Income',
            'key'   => 'product_income-' . $_POST['id'],
            'money' => '1',
        ),
    );
    $options    = array(
        'element'    => 'stats_graphB',
        'title'      => 'Product Income',
        'type'       => $gdata['unit'],
        'increments' => $gdata['int'],
    );
    $graph_outB = new graph($graph, $options);
    echo $graph_outA;
    echo $graph_outB;
    ?>


    <div class="col50l">
        <div class="pad24_fs_l">

            <form action="" id="graph_form" onsubmit="return regen_graph();" method="get">
                <div class="graph_area">
                    <?php
                    echo $admin->graph_form($gdata);
                    ?>
                    <div id="stats_graphA" class="graph_box" style="height:250px;"></div>
                    <div id="stats_graphB" class="graph_box" style="height:250px;"></div>
                    <div class="clear"></div>
                </div>
            </form>

        </div>
    </div>
    <div class="col50r">
        <div class="pad24_fs_r">

            <?php

            if (!empty($pinned_notes)) {

                echo '<div style="margin-bottom:24px;">';

                foreach ($pinned_notes as $item) {
                    echo $admin->format_note($item);
                }

                echo '</div>';

            }

            ?>


            <fieldset>
                <legend>Product Overview</legend>
                <div class="pad24">

                    <dl>
                        <dt>ID</dt>
                        <dd><?php echo $product['data']['id']; ?></dd>
                        <dt>Name</dt>
                        <dd><?php echo $product['data']['name']; ?></dd>
                        <dt>Tagline</dt>
                        <dd><?php echo $product['data']['tagline']; ?></dd>
                        <dt>Category</dt>
                        <dd><?php echo $product['category']['name']; ?></dd>
                    </dl>
                    <div class="clear"></div>

                </div>
            </fieldset>

            <fieldset>
                <legend>Additional Information</legend>
                <div class="pad24">

                    <dl>
                        <dt>Type</dt>
                        <dd><?php echo $product['data']['show_type']; ?></dd>
                        <dt>Price</dt>
                        <dd><?php echo $product['data']['format_price']; ?></dd>
                        <?php

                        if ($product['data']['type'] == '2' || $product['data']['type'] == '3') {
                            ?>
                            <dt>Timeframe</dt>
                            <dd><?php echo $product['data']['format_timeframe']; ?></dd>
                            <?php

                            if (!empty($product['data']['threshold_date']) && substr($product['data']['renew_timeframe'], 0, 3) == '888') {
                                ?>
                                <dt>Threshold Date</dt>
                                <dd><?php echo substr($product['data']['threshold_date'], 0, 2) . '/' . substr($product['data']['threshold_date'], 2, 2); ?></dd>
                                <?php

                            }
                        }
                        ?>
                        <dt>Physical</dt>
                        <dd><?php echo $product['data']['show_physical']; ?></dd>
                    </dl>
                    <div class="clear"></div>

                </div>
            </fieldset>

        </div>
    </div>
    <div class="clear"></div>

<?php
}