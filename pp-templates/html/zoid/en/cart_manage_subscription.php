<div id="zen_content" class="zen_round">
    <div class="zen_fonts zen_pad">

        <h2>Subscription ID %data:id%</h2>

        <div class="zen_col50l">
            <fieldset class="zen" id="fs18" style="display:block;">
                <legend class="zen">Subscription Information</legend>
                <div class="zen_field_set_col" style="width:100%;">
                    <div class="zen_pad_topl">
                        <div class="zen_field">
                            <label class="zen_left_preview">Status</label>

                            <div class="zen_field_entry_preview">%data:show_status%%data:alert_info%</div>
                        </div>
                        <div class="zen_field">
                            <label class="zen_left_preview">Next Renewal</label>

                            <div class="zen_field_entry_preview">%data:renews% (%data:format_next_price%)</div>
                        </div>
                        <div class="zen_field">
                            <label class="zen_left_preview">Started</label>

                            <div class="zen_field_entry_preview">%data:started%</div>
                        </div>
                        <div class="zen_field">
                            <label class="zen_left_preview">Current Price</label>

                            <div class="zen_field_entry_preview">%data:format_price%</div>
                        </div>
                        <div class="zen_field">
                            <label class="zen_left_preview">Options</label>

                            <div class="zen_field_entry_preview">%data:user_options%</div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="zen_col50">
            <fieldset class="zen" id="fs16" style="display:block;">
                <legend class="zen">Product Information</legend>
                <div class="zen_field_set_col" style="width:100%;">
                    <div class="zen_pad_topl">
                        <div class="zen_field">
                            <label class="zen_left_preview">Name</label>

                            <div class="zen_field_entry_preview">%product:name%</div>
                        </div>
                        <div class="zen_field">
                            <label class="zen_left_preview">Description</label>

                            <div class="zen_field_entry_preview">%product:tagline%</div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="zen" id="fs14" style="display:block;">
                <legend class="zen">Billing Information</legend>
                <div class="zen_field_set_col" style="width:100%;">
                    <div class="zen_pad_topl">
                        <div class="zen_field">
                            <label class="zen_left_preview">Credit Card</label>

                            <div class="zen_field_entry_preview" id="card_%billing:id%">%billing:img% %billing:full_method% %billing:user_delete_link%</div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="zen_clear"></div>


        <h2>Charge History</h2>

        <table cellspacing="0" cellpadding="0" border="0" class="zen_cart">
            <thead>
            <tr>
                <th>Order Number</th>
                <th>Date</th>
                <th width="135">Status</th>
                <th class="zen_right" width="85">Total</th>
            </tr>
            </thead>
            <tbody>
            %history%
            </tbody>
        </table>

    </div>
</div>
</form>