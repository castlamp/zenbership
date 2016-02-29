<div id="zen_content" class="zen_round">
    <div class="zen_pad zen_fonts">

        <form action="/pp-cart/credit_card.php" method="post" id="zen_form"
              onsubmit="return verifyForm('zen_form');">

            <div class="zen_col75l">
                <h2 class="zen_notopmargin">Credit Card Details</h2>
                %billing_form%
                %method_form%
            </div>
            <div class="zen_col25">

                <div class="zen_gray_box">
                    <div class="zen_pad">
                        <h2 class="zen_notopmargin">Privacy Information</h2>

                        <p class="zen_medium">All credit card information is securely transmitted and stored off-site
                            with our payment provider and can be removed at any time.</p>

                        <?php
                        if (!empty($this->changes['subscription']['id'])) {
                        ?>
                    </div>
                </div>

                <div class="zen_gray_box zen_topmargin">
                    <div class="zen_pad">
                        <h2 class="zen_notopmargin">Subscription</h2>

                        <p class="zen_medium">This card will be automatically assigned to subscription ID
                            %subscription:id%.</p>
                        <input type="hidden" name="sub" value="%subscription:id%"/>
                        <input type="hidden" name="salt" value="%subscription:salt%"/>

                        <?php
                        }
                        ?>
                    </div>
                </div>

            </div>
            <div class="zen_clear"></div>

            <div class="zen_submit">
                <input type="hidden" name="id" value="%card_id%"/>
                <input type="submit" value="Add Card"/>
            </div>

        </form>

    </div>
</div>
</form>