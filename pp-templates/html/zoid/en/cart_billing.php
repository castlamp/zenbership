<form action="%secure_url%" id="zen_form" method="post" onsubmit="return verifyForm();">
    <div id="zen_content" class="zen_round">
        <div class="zen_pad">

            <h1 class="zen_notopmargin">Checkout</h1>
            <ul id="zen_event_steps">
                <li class="on">Order Details</li>
                <li>Confirm</li>
                <li>Complete</li>
            </ul>
            <div class="zen_space"></div>

            <div id="zen_cart_left" class="col30l">

                <h2 class="zen_notopmargin">Overview</h2>

                <div class="zen_shadow_light zen_gray_box">
                    <div class="zen_pad_tiny">
                        <table cellspacing="0" cellpadding="0" border="0" class="zen_basic">
                            <thead>
                            <tr>
                                <th width="30">Qty</th>
                                <th>Product</th>
                                <th width="60">Cost</th>
                            </tr>
                            </thead>
                            <tbody>
                            %cart_components%
                            <?php
                            if ($this->changes['invoice_active'] != '1') {
                                ?>
                                <tr>
                                    <td colspan="2" class="zen_cart_total">Subtotal</td>
                                    <td><span id="zen_totals_subtotal">%pricing:format_subtotal%</span></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="zen_cart_total">Tax</td>
                                    <td><span id="zen_totals_tax">%pricing:format_tax%</span></td>
                                </tr>
                                <?php
                                if ($this->changes['data']['need_shipping'] == '1') {
                                    ?>
                                    <tr>
                                        <td colspan="2" class="zen_cart_total">Shipping</td>
                                        <td><span id="zen_totals_shipping">%pricing:format_shipping%</span></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                <tr>
                                    <td colspan="2" class="zen_cart_total">Savings<?php
                                        if (!empty($this->changes['code']['id'])) {
                                            ?>
                                            <p class="zen_code_desc" id="zen_display_code">%code:id%: %code:description%
                                                (<a href="null.php" onclick="return remove_code();">Remove</a>)</p>
                                        <?php
                                        } // invoice_active
                                        ?>
                                    </td>
                                    <td>(<span id="zen_totals_savings">%pricing:format_savings%</span>)</td>
                                </tr>
                            <?php
                            } // invoice_active
                            ?>
                            <tr>
                                <td colspan="2" class="zen_cart_total">Total</td>
                                <td><span id="zen_totals_total">%pricing:format_total%</span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                if ($this->changes['invoice_active'] != '1') {
                    ?>
                    <h2>Promotional Code</h2>
                    <div class="zen_shadow_light zen_gray_box">
                        <div class="zen_pad_tiny">
                            <input type="text" id="coupon" value="" style="width:192px;"/> <input type="button"
                                                                                                  value="Apply"
                                                                                                  onclick="return applyCoupon();"/>
                        </div>
                    </div>
                <?php
                }
                ?>

                <h2>Payment Method</h2>

                <div class="zen_shadow_light zen_gray_box">
                    <div class="zen_pad">
                        <ul id="zen_payment_methods">
                            %payment_methods%
                            %cards_on_file%
                        </ul>
                    </div>
                </div>

            </div>
            <div id="zen_cart_right" class="col70r">

                %billing_form%

                <?php
                if ($this->changes['data']['need_shipping'] == '1') {
                    ?>
                    <h2>Shipping Option</h2>
                    <div class="zen_shadow_light zen_gray_box">
                        <table cellspacing=0 cellpadding=0 border=0 class="zen_basic">
                            %ship_options%
                        </table>
                    </div>

                    <div class="zen_gray_box zen_topmargin_less">
                        <div class="zen_pad_less zen_medium">
                            <input type="checkbox" id="zen_same_as_billing" value="1"/> My shipping information is the
                            same as my billing information.
                        </div>
                    </div>

                <?php
                }
                ?>

                %shipping_form%

                <div id="zen_form_match">%method_form%</div>

                <div class="zen_submit">
                    <input type="submit" value="Preview Order"/>
                </div>

            </div>
            <div class="zen_clear"></div>
        </div>

    </div>
    </div>
</form>
<script type="text/javascript" src="%pp_url%/pp-js/billing.functions.js"></script>