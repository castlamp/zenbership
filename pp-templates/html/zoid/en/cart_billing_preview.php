<form action="%secure_url%" id="zen_form" method="post" onsubmit="return verifyForm();">
    <div id="zen_content" class="zen_round">
        <div class="zen_pad">


            <h1 class="zen_notopmargin">Review your order</h1>
            <ul id="zen_event_steps">
                <li>Order Details</li>
                <li class="on">Confirm</li>
                <li>Complete</li>
            </ul>

            <div class="zen_space"></div>

            <div class="zen_attention">
                <div class="zen_pad_topl zen_fonts">
                    <div class="zen_col75l">
                        <p><b class="zen_large">Please Review Your Order!</b><br/>
                            If everything is correct, click on "Complete Order" to finalize the order.<br/>
                            Otherwise please go back and make the necessary changes.</p>
                    </div>
                    <div class="zen_col25 zen_right">
                        <input type="hidden" name="method" value="%method%"/>
                        <input type="hidden" name="zen_complete_cart" value="1"/>
                        <input type="submit" value="Complete Order"/>
                    </div>
                    <div class="zen_clear"></div>
                </div>
            </div>

            <div class="zen_space"></div>

            <table cellspacing="0" cellpadding="0" border="0" class="zen_basic">
                <thead>
                <tr>
                    <th>Product</th>
                    <th width="150">Unit Cost</th>
                    <th width="60">Qty</th>
                    <th class="zen_right" width="120">Total</th>
                </tr>
                </thead>
                <tbody>
                %cart_components%
                <?php
                if ($this->changes['invoice_active'] != '1') {
                    ?>
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
                                <p class="zen_code_desc">(%code:id%: %code:description%)</p>
                            <?php
                            }
                            ?></td>
                        <td class="zen_right">(<span id="zen_totals_savings">%pricing:format_savings%</span>)</td>
                    </tr>
                <?php
                } // invoice_active
                ?>
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

            <div class="zen_submit">
                <input type="hidden" name="zen_complete_cart" value="1"/>
                <input type="submit" value="Complete Order"/>
            </div>

        </div>

    </div>

    </div>
    </div>
</form>
<script type="text/javascript" src="%pp_url%/pp-js/billing.functions.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('input[type=submit]').attr('disabled', false);
    $('#fadeOverlay').hide();

    $('#zen_form').submit(function(){
        $('input[type=submit]').attr('disabled', true);
        $('#fadeOverlay').show();
    });
});
</script>

<div id="fadeOverlay" style="z-index:999;position:fixed;top:0;left:0;height:100vh;width:100vw;background-color:rgba(0,0,0,.25);text-align:center;">
    <div style="z-index:1000;position:fixed;top:50%;left:50%;width:16px;height:11px;margin-top:-5px;margin-left:-8px;">
        <img src="%theme_url%/imgs/loading.gif" width="16" height="11" border="0" />
    </div>
</div>