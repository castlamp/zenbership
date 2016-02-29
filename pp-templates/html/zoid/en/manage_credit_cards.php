<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">

        <div id="zen_catalog_left" class="col25l">
            <h2 class="zen_notopmargin">Navigation</h2>
            {-user_manage_menu-}
        </div>
        <div id="zen_catalog_right" class="col75r">

            <div class="zen_section_right">
                <input type="button" onclick="window.location='%pp_url%/pp-cart/add_card.php';" value="Add a Card"/>
            </div>

            <h1 class="zen_notopmargin">Welcome, %first_name%!</h1>

            <h2 class="zen_notopmargin">Manage Credit Cards</h2>

            <table cellspacing="0" cellpadding="0" border="0" class="zen_cart">
                <thead>
                <tr>
                    <th>Card Number</th>
                    <th>Expiration</th>
                    <th class="zen_right" width="85">Options</th>
                </tr>
                </thead>
                <tbody>
                %cards%
                </tbody>
            </table>

            <div class="zen_section_right zen_gray zen_medium zen_pagination">%pagination%</div>
            <div class="zen_clear"></div>

        </div>
        <div class="zen_clear"></div>

    </div>

</div>
	