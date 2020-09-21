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
$permission = 'shop';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {


	$date = current_date();

	$exp_date = explode(' ',$date);

	$time = strtotime($date);

	$day7 = date('Y-m-d',$time-604800);

	$day14 = date('Y-m-d',$time-1209600);

	$day30 = date('Y-m-d',$time-2592000);

	$day7f = date('Y-m-d',$time+604800);

	$day14f = date('Y-m-d',$time+1209600);

	$day30f = date('Y-m-d',$time+2592000);


    ?>



    <div id="topblue" class="fonts small">
        <div class="holder">

            <div class="floatright" id="tb_right">

                &nbsp;

            </div>

            <div class="floatleft" id="tb_left">

                <b>Shop</b>

            </div>

            <div class="clear"></div>

        </div>
    </div>



    <div id="mainsection">

        <div class="nontable_section">
            <div class="pad24">

                <h1>Transactions &amp; Sales</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col33">

                            <h2><img src="imgs/icon-sales.png" width="32" height="32" alt="Transactions"
                                     title="Transactions" class="iconlg"/><a href="index.php?l=transactions">Transaction
                                    Logs</a></h2>

                            <p>View all transactions processed by the program.</p>

                            <p class="smaller">Placed: <a
                                    href="index.php?l=transactions&filters[]=<?php echo $exp_date['0']; ?>||date_completed||like||ppSD_cart_sessions">Today</a>
                                &#183; <a
                                    href="index.php?l=transactions&filters[]=<?php echo $day7; ?>||date_completed||gt||ppSD_cart_sessions">7
                                    Day</a> &#183; <a
                                    href="index.php?l=transactions&filters[]=<?php echo $day14; ?>||date_completed||gt||ppSD_cart_sessions">14
                                    Day</a> &#183; <a
                                    href="index.php?l=transactions&filters[]=<?php echo $day30; ?>||date_completed||gt||ppSD_cart_sessions">30
                                    Day</a></p>


                            <p class="smaller"><a
                                    href="index.php?l=transactions&filters[]=1||status||eq||ppSD_cart_sessions">Settled</a>
                                &#183; <a href="index.php?l=transactions&filters[]=2||status||eq||ppSD_cart_sessions">Pending
                                    Payment</a> &#183; <a
                                    href="index.php?l=transactions&filters[]=0||status||eq||ppSD_cart_sessions">Unfinished</a>
                            </p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-subscriptions.png" width="32" height="32" alt="Subscriptions"
                                     title="Subscriptions" class="iconlg"/><a href="index.php?l=subscriptions">Subscriptions</a>
                            </h2>

                            <p>View all subscriptions handled by the program.</p>


                            <p class="smaller">Renews: <a
                                    href="index.php?l=subscriptions&filters[]=<?php echo $exp_date['0']; ?>||date_completed||like||ppSD_subscriptions">Today</a>
                                &#183; <a
                                    href="index.php?l=subscriptions&filters[]=<?php echo $day7f; ?>||next_renew||lt||ppSD_subscriptions">7
                                    Day</a> &#183; <a
                                    href="index.php?l=subscriptions&filters[]=<?php echo $day14f; ?>||next_renew||lt||ppSD_subscriptions">14
                                    Day</a> &#183; <a
                                    href="index.php?l=subscriptions&filters[]=<?php echo $day30f; ?>||next_renew||lt||ppSD_subscriptions">30
                                    Day</a></p>


                            <p class="smaller"><a
                                    href="index.php?l=subscriptions&filters[]=1||status||eq||ppSD_subscriptions">Active</a>
                                &#183; <a href="index.php?l=transactions&filters[]=2||status||eq||ppSD_subscriptions">Canceled</a>
                            </p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-invoices.png" width="32" height="32" alt="Invoices" title="Invoices"
                                     class="iconlg"/><a href="index.php?l=invoices">Invoices</a></h2>

                            <p>View all active and settled invoices.</p>

                            <p class="smaller"><a
                                    href="index.php?l=error_codes&filters[]=I||code||like||ppSD_error_codes">Error
                                    Codes</a></p>

                        </div>

                        <div class="clear"></div>

                    </div>

                    <!--

                    <div class="pad24 line_top">

                        <div class="col33">

                            <h2><img src="imgs/icon-credit_cards.png" width="32" height="32" alt="Credit Cards" title="Credit Cards" class="iconlg" /><a href="index.php?l=credit_cards">Credit Cards</a></h2>

                            <p>List of credit cards on file.</p>

                        </div>

                        <div class="col33">



                        </div>

                        <div class="col33">



                        </div>

                        <div class="clear"></div>

                    </div>

                    -->

                </div>

            </div>
        </div>


        <div class="nontable_section">
            <div class="pad24">

                <h1>Shop Components</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col50">

                            <h2><img src="imgs/icon-products.png" width="32" height="32" alt="Products" title="Products"
                                     class="iconlg"/><a href="index.php?l=products">Products</a></h2>

                            <p>Manage products in the shop.</p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-categories.png" width="32" height="32" alt="Categories"
                                     title="Categories" class="iconlg"/><a href="index.php?l=categories">Categories</a>
                            </h2>

                            <p>Manage classification categories for products.</p>

                        </div>

                        <div class="clear"></div>

                    </div>

                    <div class="pad24 line_top">

                        <div class="col50">

                            <h2><img src="imgs/icon-savings_codes.png" width="32" height="32" alt="Promotional Codes"
                                     title="Promotional Codes" class="iconlg"/><a href="index.php?l=promo_codes">Promotional
                                    Codes</a></h2>

                            <p>Manage promotional coupon codes.</p>

                            <p class="smaller"><a href="index.php?l=promo_code_usage">Usage Logs</a></p>

                        </div>

                        <div class="col50">

                            <h2><img src="imgs/icon-settings.png" width="32" height="32" alt="Shop Settings"
                                     title="Shop Settings" class="iconlg"/>Settings
                            </h2>

                            <p>Manage shop settings.</p>

                            <p class="smaller"><a href="index.php?l=shop_payment_gateways">Payment Gateways</a> | <a
                                    href="index.php?l=shop_terms">Terms</a> | <a href="index.php?l=shop_tax">Tax</a> |
                                <a href="index.php?l=shop_shipping">Shipping Options</a> | <a
                                    href="index.php?l=error_codes&filters[]=S||code||like||ppSD_error_codes">Error
                                    Codes</a></p>

                        </div>

                        <div class="clear"></div>

                    </div>

                </div>

            </div>
        </div>


    </div>



<?php

}
