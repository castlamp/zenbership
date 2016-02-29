<div class="zen_holder">
    <div id="zen_content" class="zen_round">

        <div class="zen_pad_more">

            <form action="%pp_url%/pp-cart/invoice_add.php" method="post">

                <h1>How much would you like to pay?</h1>

                <input type="hidden" name="id" value="%invoice:id%"/>
                <input type="hidden" name="hash" value="%invoice:hash%"/>

                <div class="zen_gray_box zen_topmargin_less">
                    <div class="zen_pad_less zen_medium">
                        %payment_field% <input type="submit" value="Continue to Payment"/>
                    </div>
                </div>

            </form>

            <div class="zen_space"></div>

            <h1>Invoice Overview</h1>

            <table cellspacing="0" cellpadding="0" width="100%" border="0" class="zen_cart">
                <thead>
                <th>Item</th>
                <th width="100">Qty / Rate</th>
                <th width="120">Unit Price</th>
                <th width="150" class="zen_right">Total</th>
                </thead>
                %components%
                <tr class="">
                    <td colspan="3" class="zen_cart_subtotal">Subtotal</td>
                    <td class="zen_cart_subtotal">%pricing:format_subtotal%</td>
                </tr>
                <tr class="">
                    <td colspan="3" class="zen_cart_total">Tax (%invoice:tax_rate%%)</td>
                    <td class="zen_cart_total">%pricing:format_tax%</td>
                </tr>
                <tr class="">
                    <td colspan="3" class="zen_cart_total">Shipping</td>
                    <td class="zen_cart_total">%pricing:format_shipping%</td>
                </tr>
                <tr class="">
                    <td colspan="3" class="zen_cart_total">Credits</td>
                    <td class="zen_cart_total">(%pricing:format_credits%)</td>
                </tr>
                <tr class="">
                    <td colspan="3" class="zen_cart_total">Payments</td>
                    <td class="zen_cart_total">(%pricing:format_paid%)</td>
                </tr>
                <tr class="">
                    <td colspan="3" class="zen_cart_grandtotal"><b>Balance Due</b></td>
                    <td class="zen_cart_grandtotal">%pricing:format_due%</td>
                </tr>
            </table>

        </div>
    </div>
</div>