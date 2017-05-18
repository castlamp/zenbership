<div class="zen_holder">
    <div id="zen_content" class="zen_round">

        <div class="zen_pad_more">

            <div class="zen_fonts">
                <div class="col33l">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Overview</legend>
                        <div class="zen_pad_less">
                            <dl class="zen">
                                <dt>Date Prepared</dt>
                                <dd>%invoice:format_date%</dd>
                                <dt>Quoted Amount</dt>
                                <dd>%pricing:format_due%</dd>
                                <dt>Options</dt>
                                <dd>
                                    <!--<a href="%invoice:payment_link%">Accept Quote</a><br/>-->
                                    <a href="%invoice:print_invoice%">Print Quote</a>
                                </dd>
                            </dl>
                        </div>
                    </fieldset>
                </div>
                <div class="col34">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Quoting Party</legend>
                        <div class="zen_pad_less">
                            <p class="zen">%company_address%</p>
                            <p class="zen">%company_contact%</p>
                        </div>
                    </fieldset>
                </div>
                <div class="col33r">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Quoted Party</legend>
                        <div class="zen_pad_less">
                            <p class="zen">%billing:company_name%</p>
                            <p class="zen">%billing:contact_name%</p>
                            <p class="zen">%format_billing%</p>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_clear"></div>

                <fieldset class="zen zen_notopmargin zen_nobotmargin">
                    <legend class="zen">Details</legend>
                    <div class="zen_pad_less">
                        <p class="zen">%billing:memo%</p>
                    </div>
                </fieldset>

            </div>


            <div class="zen_space_more"></div>
            <h1>Quote Components</h1>

            <table cellspacing="0" cellpadding="0" width="100%" border="0" class="zen_cart">
                <thead>
                <th>Item</th>
                <th width="100">Qty / Rate</th>
                <th width="120">Unit Price</th>
                <th width="150" class="zen_right">Total</th>
                </thead>
                %components%
                %payments%
                <tr class="">
                    <td colspan="3" class="zen_cart_subtotal">Tax (%invoice:tax_rate%%)</td>
                    <td class="zen_cart_subtotal">%pricing:format_tax%</td>
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
                    <td colspan="3" class="zen_cart_grandtotal"><b>Quote Total</b></td>
                    <td class="zen_cart_grandtotal">%pricing:format_due%</td>
                </tr>
            </table>

        </div>
    </div>
</div>