<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">
        <div class="col20l">

            <h2 class="zen_notopmargin">More Options</h2>

            <div class="zen_gray_box">
                <div class="zen_pad_topl zen_fonts" id="zen_cart_more_options">
                    <p class="zen_medium"><a href="%pp_url%/catalog.php">Continue Shopping</a><br/><a
                            href="%pp_url%/pp-cart/empty.php">Empty Cart</a></p>
                </div>
            </div>


            <h2>Promotional Code</h2>

            <div class="zen_gray_box">
                <div class="zen_pad_topl zen_fonts">
                    <input type="text" id="coupon" value="" style="width:120px;"/> <input type="button" value="&raquo;"
                                                                                          onclick="return applyCoupon();"/>
                </div>
            </div>


        </div>
        <div class="col80r">

            <form action="%pp_url%/pp-cart/update.php" method="post">

                <table cellspacing="0" cellpadding="0" border="0" class="zen_cart">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th width="135">Unit Cost</th>
                        <th width="40">Qty</th>
                        <th class="zen_right" width="85">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    %cart_components%
                    <tr>
                        <td colspan="3" class="zen_cart_subtotal">Subtotal</td>
                        <td class="zen_cart_subtotal"><span id="zen_totals_subtotal">%pricing:format_subtotal%</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="zen_cart_total">Tax
                            <?php
                            if (empty($this->changes['data']['country']) && empty($this->changes['data']['state'])) {
                                ?>
                                <span class="zen_icon">(<a href="null.php" onclick="return set_country_state();">Determine</a>)</span>
                            <?php
                            }
                            ?></td>
                        <td class="zen_cart_total"><span id="zen_totals_tax">%pricing:format_tax%</span></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="zen_cart_total">Shipping
                            <?php
                            if (empty($this->changes['data']['country']) && empty($this->changes['data']['state'])) {
                                ?>
                                <span class="zen_icon">(<a href="null.php" onclick="return set_country_state();">Determine</a>)</span>
                            <?php
                            }
                            ?></td>
                        <td class="zen_cart_total"><span id="zen_totals_shipping">%pricing:format_shipping%</span></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="zen_cart_total">Savings<?php
                            if (!empty($this->changes['code']['id'])) {
                                ?>
                                <p class="zen_code_desc" id="zen_display_code">%code:id%: %code:description% (<a
                                        href="null.php" onclick="return remove_code();">Remove</a>)</p>
                            <?php
                            }
                            ?></td>
                        <td class="zen_cart_total">(<span id="zen_totals_savings">%pricing:format_savings%</span>)</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="zen_cart_grandtotal">Total</td>
                        <td class="zen_cart_grandtotal"><span id="zen_totals_total">%pricing:format_total%</span></td>
                    </tr>
                    </tbody>
                </table>

                <div class="zen_submit">
                    <div class="zen_section_right">
                        <input type="button" onclick="window.location='%pp_url%/pp-cart/checkout.php';"
                               value="Proceed to Checkout"/>
                    </div>
                    <div class="zen_section_left">
                        <input type="submit" value="Update Order"/>
                    </div>
                    <div class="zen_clear"></div>
                </div>
            </form>

        </div>
        <div class="zen_clear"></div>
    </div>

</div>