<div id="zen_content" class="zen_round zen_edit_area">
    <div class="zen_pad">

        <h1 class="zen_notopmargin">Order No. %data:id%</h1>

        <p class="zen_small zen_fonts">Status: %data:show_status%. Placed on %data:format_date%.</p>

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
                        <p class="zen_code_desc" id="zen_display_code">%code:id%: %code:description% (<a href="null.php"
                                                                                                         onclick="return remove_code();">Remove</a>)
                        </p>
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

                <div class="zen_col50l">

                    %billing_form%

                    <fieldset class="zen" id="fs14" style="display:block;">
                        <legend class="zen">Payment Information</legend>
                        <div class="zen_field_set_col" style="width:100%;">
                            <div class="zen_pad_topl zen_medium">
                                %billing:img% %billing:full_method%
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_col50">
                    %shipping_form%

                    <fieldset class="zen" id="fs14" style="display:block;">
                        <legend class="zen">Shipping Status</legend>
                        <div class="zen_field_set_col" style="width:100%;">
                            <div class="zen_pad_topl">
                                <div class="zen_field">
                                    <label class="zen_left_preview">Status</label>

                                    <div class="zen_field_entry_preview">%shipping:status%</div>
                                </div>
                                <div class="zen_field">
                                    <label class="zen_left_preview">Tracking</label>

                                    <div class="zen_field_entry_preview">%shipping:tracking%</div>
                                </div>
                                <div class="zen_field">
                                    <label class="zen_left_preview">Tracking Link</label>

                                    <div class="zen_field_entry_preview">%shipping:link%</div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                </div>
                <div class="zen_clear"></div>

            </div>
        </div>

    </div>

</div>

</div>
</div>