<form action="%secure_url%" id="zen_form" method="post" onsubmit="return verifyForm();">
    <div id="zen_content" class="zen_round">
        <div class="zen_pad">

            <?php
            if (! empty($this->changes['newMember']['username'])) {
                ?>
                <div class="zen_attention zen_margin_bottom">
                    <div class="zen_pad_topl zen_fonts">
                        <div class="zen_col75l">
                            <p><b class="zen_large">Thank you for your order!</b><br />An account has been created for you under username <u>%newMember:username%</u>.</p>
                            <p>Your temporary password is <u>%newMember:password%</u>. Please <a href="/manage/update_account.php">click here</a> to access the member's area and update your password!</p>
                        </div>
                        <div class="zen_col25 zen_right">
                            <input type="button" value="Go To Member's Area" onclick="window.location='/manage/update_account.php'" />
                        </div>
                        <div class="zen_clear"></div>
                    </div>
                </div>
            <?php
            }
            ?>

            <h1 class="zen_notopmargin">Order No. %data:id% Complete!</h1>

            <ul id="zen_event_steps">
                <li>Order Details</li>
                <li>Confirm</li>
                <li class="on">Complete</li>
            </ul>

            <div class="zen_space"></div>

            <table cellspacing="0" cellpadding="0" border="0" class="zen_basic">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit Cost</th>
                    <th width="60">Qty</th>
                    <th class="zen_right" width="100">Total</th>
                </tr>
                </thead>
                <tbody>
                %cart_components%
                <tr>
                    <td colspan="3" class="zen_cart_total">Subtotal</td>
                    <td class="zen_right">%pricing:format_subtotal%</td>
                </tr>
                <tr>
                    <td colspan="3" class="zen_cart_total">Tax</td>
                    <td class="zen_right">%pricing:format_tax%</td>
                </tr>
                <?php
                if ($this->changes['data']['need_shipping'] == '1') {
                    ?>
                    <tr>
                        <td colspan="3" class="zen_cart_total">Shipping</td>
                        <td class="zen_right">%pricing:format_shipping%</td>
                    </tr>
                <?php
                }
                ?>
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
                    <td colspan="3" class="zen_cart_total">Total</td>
                    <td class="zen_right">%pricing:format_total%</td>
                </tr>
                </tbody>
            </table>

            <div class="zen_fonts zen_outline_box">
                <div class="zen_pad">

                    <div class="zen_col50">
                        %billing_form%

                        <div id="zen_form_match">
                            %method_form%
                        </div>

                    </div>
                    <div class="zen_col50">
                        %shipping_form%
                    </div>
                    <div class="zen_clear"></div>

                </div>
            </div>

        </div>

    </div>

    </div>
    </div>
</form>
<script type="text/javascript" src="%pp_url%/pp-js/billing.functions.js"></script>