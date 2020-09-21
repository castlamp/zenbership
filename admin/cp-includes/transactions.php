<?php


/**
 * List of transactions
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

$permission = 'transaction';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {
    $filter_array_default = array(
        '-||status||neq||ppSD_cart_sessions'
    );
    $table                = 'ppSD_cart_sessions';
    $order                = 'ppSD_cart_sessions.date_completed';
    $dir                  = 'DESC';
    $display              = '50';
    $page                 = '1';
    $defaults             = array(
        'sort'    => $order,
        'order'   => $dir,
        'page'    => $page,
        'display' => $display,
        'filters' => $filter_array_default,
    );
    $force_filters        = array();
    $gen_table            = $admin->get_table($table, $_GET, $defaults, $force_filters);
    ?>



    <form action="cp-includes/get_table.php" id="table_filters" method="post" onsubmit="return update_table();">

    <input type="hidden" name="order" value="<?php echo $gen_table['order']; ?>"/>

    <input type="hidden" name="dir" value="<?php echo $gen_table['dir']; ?>"/>

    <input type="hidden" name="menu" value="<?php echo $gen_table['menu']; ?>"/>

    <input type="hidden" name="table" value="<?php echo $table; ?>"/>

    <input type="hidden" name="permission" value="<?php echo $permission; ?>"/>

    <input type="hidden" name="filters" id="filter_field" value='<?php if (!empty($_GET['filters'])) {
        echo serialize($_GET['filters']);
    } else {
        $combine = array_merge($filter_array_default, $force_filters);
        if (!empty($combine)) {
            echo serialize($combine);
        }
    } ?>'/>


    <div id="topblue" class="fonts small">
        <div class="holder">

            <div class="floatright" id="tb_right">
                <?php
                include dirname(__FILE__) . '/pagination_display.php';
                ?>
            </div>

            <div class="floatleft" id="tb_left">

                <span><b>Listing Transactions</b></span>

                <span class="div">|</span>

                <a href="null.php" onclick="return show_filters();">Filters<img src="imgs/down-arrow.png"
                                                                                id="filter_arrow" width="10" height="10"
                                                                                alt="Expand" border="0"
                                                                                class="icon-right"/></a>

                <span class="div">|</span>

			<span id="innerLinks">

				<a href="null.php" onclick="return popup('<?php echo $permission; ?>-add','add','1');">Add Transaction</a>
				<a href="index.php?l=billing_report">Billing Report</a>

                <span class="div">|</span>

				<a href="null.php" onclick="return prep_export('transaction');">Export</a>

			</span>

            </div>

            <div class="clear"></div>

        </div>
    </div>


    <div id="filters" class="fonts smaller">
        <div class="pad24">

            <div id="filters_top">

                <div id="filters_right">

                    <input type="submit" value="Apply Filters" class="save"/>

                </div>

                <div id="filters_left">

                    <span><b>Applying Filters</b></span>

                    <!--<span><a href="null.php" onclick="return popup('filters-<?php echo $permission; ?>');"><img src="imgs/icon-settings.png" width="16" height="16" border="0" alt="Settings" title="Settings" class="icon" />Settings</a></span>-->

                </div>

                <div class="clear"></div>

            </div>

            <div class="col50">

                <?php

                $opnm = $permission . '_filters';

                $opt_filters = $db->get_option($opnm);

                if (!empty($employee['options'][$opnm])) {
                    $thefilters = explode(',', $employee['options'][$opnm]);

                } else if (!empty($opt_filters)) {
                    $thefilters = explode(',', $opt_filters);

                } else {
                    // name:table:date:date_range
                    $thefilters = array(
                        'id:ppSD_cart_sessions::',
                        'date_completed:ppSD_cart_sessions:1:1',
                        'ship_date:ppSD_shipping:1:1',
                        'total:ppSD_cart_session_totals::',
                        'shipping:ppSD_cart_session_totals::',
                        'savings:ppSD_cart_session_totals::',
                        'tax:ppSD_cart_session_totals::',
                    );

                }

                foreach ($thefilters as $aFilter) {
                    $exp = explode(':', $aFilter);
                    if (empty($exp['1'])) {
                        $exp['1'] = 'ppSD_cart_session_totals';
                    }

                    ?>

                    <div class="field">

                        <label><?php echo format_db_name($exp['0']); ?></label>

                        <div class="field_entry">

                            <?php

                            if ($exp['2'] == '1') {
                                $date = '1';
                            } else {
                                $date = '0';
                            }

                            if ($exp['3'] == '1') {
                                $dater = '1';
                            } else {
                                $dater = '0';
                            }

                            echo $admin->filter_field($exp['0'], '', $exp['1'], '1', $date, $dater);

                            if ($dater == '1') {
                                ?>

                                <p class="field_desc_show">Create a date range by inputting two dates, or select a
                                    specific date by only inputting the first field. All dates need to be in the
                                    "YYYY-MM-DD" format.</p>

                            <?php

                            }

                            ?>

                        </div>

                    </div>

                <?php

                }

                ?>

            </div>

            <div class="col50">
                <div class="field">
                    <label class="less">Status</label>
                    <div class="field_entry_less">
                        <select name="filter[status]" style="width:150px;">
                            <option value=""></option>
                            <option value="1">Paid</option>
                            <option value="2">Pending Payment</option>
                            <option value="0">Unfinished</option>
                            <option value="3">Partially Refunded</option>
                            <option value="4">Fully Refunded</option>
                            <option value="9">Rejected</option>
                        </select>
                        <input type="hidden" name="filter_tables[card_type]" value="ppSD_cart_sessions" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">Method</label>
                    <div class="field_entry_less">
                        <select name="filter[method]" style="width:150px;">
                            <option value=""></option>
                            <option>Credit Card</option>
                            <option>Check</option>
                            <option>PayPal</option>
                            <option>Invoice</option>
                            <option>Other</option>
                        </select>
                        <input type="hidden" name="filter_tables[method]" value="ppSD_cart_billing" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">Card Type</label>
                    <div class="field_entry_less">
                        <select name="filter[card_type]" style="width:150px;">
                            <option value=""></option>
                            <option>Visa</option>
                            <option>Mastercard</option>
                            <option>Amex</option>
                            <option>Diners</option>
                            <option>Discover</option>
                            <option>JCB</option>
                        </select>
                        <input type="hidden" name="filter_tables[card_type]" value="ppSD_cart_billing" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">Payment Gateway</label>
                    <div class="field_entry_less">
                        <select name="filter[payment_gateway]" style="width:150px;">
                            <option value=""></option>
                            <?php
                            $cart = new cart;
                            $gateways = $cart->get_gateways();
                            foreach ($gateways as $aGateway) {
                                echo "<option value=\"" . $aGateway['code'] . "\">" . $aGateway['name'] . "</option>";
                            }
                            ?>
                        </select>
                        <input type="hidden" name="filter_tables[payment_gateway]" value="ppSD_cart_billing" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">State</label>
                    <div class="field_entry_less">
                        <select name="filter[state]" style="width:150px;">
                            <option value=""></option>
                            <?php
                            $fields = new field;
                            $states = $fields->state_list('', '1', 'select');
                            echo $states;
                            ?>
                        </select>
                        <input type="hidden" name="filter_tables[state]" value="ppSD_cart_billing" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">Country</label>
                    <div class="field_entry_less">
                        <select name="filter[country]" style="width:150px;">
                            <option value=""></option>
                            <?php
                            $countries = $fields->country_list('', '1', 'select');
                            echo $countries;
                            ?>
                        </select>
                        <input type="hidden" name="filter_tables[country]" value="ppSD_cart_billing" />
                    </div>
                </div>

                <div class="field">
                    <label class="less">Shipping</label>
                    <div class="field_entry_less">
                        <a href="index.php?l=transactions&filters[]=1||shipped||eq||ppSD_shipping">Shipped</a><br/>
                        <a href="index.php?l=transactions&filters[]=-||shipping_rule||neq||ppSD_cart_sessions&filters[]=1||shipped||neq||ppSD_shipping&filters[]=-||status||neq||ppSD_cart_sessions">Unshipped</a><br/>
                        <a href="index.php?l=transactions&filters[]=-||shipping_rule||eq||ppSD_cart_sessions&filters[]=-||status||neq||ppSD_cart_sessions">No
                            Shipping Required</a>
                    </div>
                </div>

                <?php
                if (! empty($_GET['filters'])) {
                    foreach ($_GET['filters'] as $item) {
                        $exp = explode('||', $item);
                        if ($exp['1'] == 'shipped') {
                            echo "<input type=\"hidden\" name=\"filter[shipped]\" value=\"" . $exp['0'] . "\" />";
                            echo "<input type=\"hidden\" name=\"filter_tables[shipped]\" value=\"ppSD_shipping\" />";
                        }
                    }
                }
                ?>

            </div>

            <div class="clear"></div>

        </div>
    </div>

    </form>



    <div id="mainsection">

        <form id="table_checkboxes">

            <table class="tablesorter listings" id="active_table" border="0">

                <?php

                echo $gen_table['th'];

                echo $gen_table['td'];

                ?>

            </table>


            <div id="bottom_delete">
                <div id="sum_list"><div class="pad16">
                    <span class="sl_key">Total</span>
                    <span class="sl_value" id="math1"><?php echo $gen_table['math']; ?></span>
                </div></div>
                <div class="pad16"><span class="small gray caps bold" style="margin-right:24px;">With Selected:</span>
                <input type="button" value="Delete" class="del" onclick="return compile_delete('<?php echo $table; ?>','table_checkboxes');"/>
                </div>
            </div>

        </form>

    </div>



<?php

}

?>
