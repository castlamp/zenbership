<div class="zen_holder">
    <div id="zen_content" class="zen_round">

        %invoice:stamp%

        <div class="zen_pad_more">

            <div class="zen_fonts">
                <div class="zen_col50l">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">From</legend>
                        <div class="zen_pad_less">
                            <p class="zen">%company_address%</p>

                            <p class="zen">%company_contact%</p>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_col50">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Details</legend>
                        <div class="zen_pad_less">
                            <dl class="zen">
                                <dt>Invoice No.</dt>
                                <dd>%invoice:id%</dd>
                                <dt>Date Created</dt>
                                <dd>%invoice:format_date%</dd>
                                <dt>Date Due</dt>
                                <dd>%invoice:format_due_date% (%invoice:time_to_due_date%)</dd>
                                <dt>Balance Due</dt>
                                <dd>%pricing:format_due%</dd>
                                <dt>Status</dt>
                                <dd>%invoice:format_status%</dd>
                                <dt>Options</dt>
                                <dd>
                                    <a href="%invoice:payment_link%">Pay this Invoice</a><br/>
                                    <a href="%invoice:print_invoice%">Print Invoice</a>
                                </dd>
                            </dl>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_clear"></div>

                <div class="zen_col50l">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Bill To</legend>
                        <div class="zen_pad_less">
                            <p class="zen">%billing:company_name%<br/>%billing:contact_name%<br/>%format_billing%</p>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_col50">
                    <fieldset class="zen zen_notopmargin zen_nobotmargin">
                        <legend class="zen">Ship To</legend>
                        <div class="zen_pad_less">
                            <p class="zen">%shipping:formatted%</p>
                        </div>
                    </fieldset>
                </div>
                <div class="zen_clear"></div>

                <fieldset class="zen zen_notopmargin zen_nobotmargin">
                    <legend class="zen">Memo</legend>
                    <div class="zen_pad_less">
                        <p class="zen">%billing:memo%</p>
                    </div>
                </fieldset>

            </div>


            <div class="zen_space_more"></div>
            <h1>Invoice Overview</h1>

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