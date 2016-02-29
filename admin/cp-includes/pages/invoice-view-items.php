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
$permission = 'invoice-view';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions($error, '', '1');
} else {
    $table = 'ppSD_invoice_components';
    // Ownership
    $invoice = new invoice;
    $data    = $invoice->get_invoice($_POST['id']);
    //pa($data);
    ?>

    <div id="slider_top_table">
        <div class="floatright">&nbsp;</div>
        <div class="floatleft">
            <input type="button" value="New" class="save"
                   onclick="return popup('invoice-item-add','id=new&invoice_id=<?php echo $data['data']['id']; ?>');"/>
        </div>
        <div class="clear"></div>
    </div>

    <form action="" id="slider_checks" method="post">
        <table class="tablesorter listings" id="subslider_table" border="0">
            <thead>
            <tr>
                <th class="first">Item</th>
                <th width="120">Unit Price</th>
                <th width="120">Qty</th>
                <th width="120">Price</th>
                <th width="80">Taxed?</th>
                <th width="24">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($data['components'] as $item) {
                if ($item['type'] == 'product') {
                    $qty         = $item['qty'] . 'x';
                    $price       = place_currency($item['unit_price']);
                    $total_price = place_currency($qty * $item['unit_price']);
                } else if ($item['type'] == 'time') {
                    $qty = $item['minutes'] . ' minutes';
                    if ($data['data']['hourly'] == 0) {
                        $hourly = place_currency($db->get_option('invoice_hourly'));
                    } else {
                        $hourly = place_currency($data['data']['hourly']);
                    }
                    $price       = $hourly . '/hour';
                    $total_price = place_currency($item['unit_price']);
                } else if ($item['type'] == 'credit') {
                    $qty         = $item['qty'] . 'x';
                    $price       = place_currency($item['unit_price']);
                    $total_price = '(' . place_currency($qty * $item['unit_price']) . ')';
                }
                if ($item['tax'] == '1') {
                    $tax = 'Yes';
                } else {
                    $tax = 'No';
                }
                echo "<tr id=\"td-cell-" . $item['id'] . "\">
                <td><a href=\"return_null.php\" onclick=\"return popup('invoice-item-add','invoice_id=" . $data['data']['id'] . "&id=" . $item['id'] . "');\">" . $item['name'] . "</a></td>
                <td>" . $price . "</td>
                <td>" . $qty . "</td>
                <td>" . $total_price . "</td>
                <td>" . $tax . "</td>
                <td><a href=\"return_null.php\" onclick=\"return delete_item('" . $table . "','" . $item['id'] . "');\"><img src=\"" . PP_ADMIN . "/imgs/icon-delete.png\" width=16 height=16 border=0 class=\"option_icon\" alt=\"Delete\" title=\"Delete\" /></a></td>
                </tr>";
            }
            ?>
            </tbody>
        </table>

    </form>

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

}
?>