<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">

        <div id="zen_catalog_left" class="col25l zen_edit_area">
            <h2 class="zen_notopmargin">Navigation</h2>
            {-user_manage_menu-}
        </div>
        <div id="zen_catalog_right" class="col75r zen_edit_area">

            <h1 class="zen_notopmargin">Welcome, %first_name%!</h1>

            <h2>Your Content</h2>

            <!--
                <div class="zen_catalog_description">
                    <form action="%pp_url%/manage/billing_history.php" method="get">
                        <div class="zen_section_right zen_gray zen_medium">
                            <select name="display" style="width:125px;" onChange="this.form.submit()">
                                <option value="12"<?php if ($_GET['display'] == '12') { echo " selected=\"selected\""; } ?>>12 per page</option>
                                <option value="24"<?php if ($_GET['display'] == '24') { echo " selected=\"selected\""; } ?>>24 per page</option>
                                <option value="48"<?php if ($_GET['display'] == '48') { echo " selected=\"selected\""; } ?>>48 per page</option>
                                <option value="96"<?php if ($_GET['display'] == '96') { echo " selected=\"selected\""; } ?>>96 per page</option>
                            </select>
                            <select name="organize" style="width:175px;" onChange="this.form.submit()">
                                <option value=""<?php if (empty($_GET['organize'])) { echo " selected=\"selected\""; } ?>>Content Name</option>
                                <option value="expires"<?php if ($_GET['organize'] == 'expires') { echo " selected=\"selected\""; } ?>>Access Expires (Recent last first)</option>
                                <option value="started"<?php if ($_GET['organize'] == 'started') { echo " selected=\"selected\""; } ?>>Access Started (Low to High)</option>
                            </select> <input type="submit" value="Sort" />
                        </div>
                    </form>
                    <div class="zen_clear"></div>
                </div>
                -->

            <table cellspacing="0" cellpadding="0" border="0" class="zen_cart">
                <thead>
                <tr>
                    <th>Name</th>
                    <th width="135">Expires</th>
                </tr>
                </thead>
                <tbody>
                %content%
                </tbody>
            </table>

            <div class="zen_section_right zen_gray zen_medium zen_pagination">%pagination%</div>
            <div class="zen_clear"></div>

        </div>
        <div class="zen_clear"></div>

    </div>

</div>
	