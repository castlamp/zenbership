<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">
        <div class="col25l" id="zen_catalog_left">

            <h2 class="zen_notopmargin">Search the Catalog</h2>

            <form action="%pp_url%/catalog.php" id="zen_cart_search" method="get">
                <div id="zen_catalog_search" class="zen_gray_box">
                    <div class="zen_pad_less">
                        <input type="text" name="query" value="%query%" class="zen_search"/>
                    </div>
                </div>
            </form>

            <form action="%pp_url%/catalog.php" id="zen_cart_filters" method="get">
                <h2>Price Range</h2>

                <div id="zen_catalog_search_low" class="zen_gray_box">
                    <div class="zen_pad_less zen_gray zen_small">
                        <?php echo CURRENCY_SYMBOL; ?> <input type="text" name="price_low"
                                                              value="<?php echo $_GET['price_low']; ?>"
                                                              style="width:70px;"/> - <?php echo CURRENCY_SYMBOL; ?>
                        <input type="text" name="price_high" value="<?php echo $_GET['price_high']; ?>"
                               style="width:70px;"/>
                    </div>
                </div>

                <h2>Featured Product</h2>
                <div class="zen_margin_top">
                {-featured_product-}
                </div>

        </div>
        <div class="col75r" id="zen_catalog_right">

            <div class="zen_catalog_description">
                <div class="zen_section_right zen_gray zen_medium">
                    <input type="hidden" name="category" value="%category:id%"/>
                    <select name="display" style="width:125px;" onChange="this.form.submit()">
                        <option value="12"<?php if ($_GET['display'] == '12') {
                            echo " selected=\"selected\"";
                        } ?>>12 per page
                        </option>
                        <option value="24"<?php if ($_GET['display'] == '24') {
                            echo " selected=\"selected\"";
                        } ?>>24 per page
                        </option>
                        <option value="48"<?php if ($_GET['display'] == '48') {
                            echo " selected=\"selected\"";
                        } ?>>48 per page
                        </option>
                        <option value="96"<?php if ($_GET['display'] == '96') {
                            echo " selected=\"selected\"";
                        } ?>>96 per page
                        </option>
                    </select>
                    <select name="organize" style="width:125px;" onChange="this.form.submit()">
                        <option value="cart_ordering"<?php if ($_GET['organize'] == 'cart_ordering') {
                            echo " selected=\"selected\"";
                        } ?>>--
                        </option>
                        <option value="alpha_az"<?php if ($_GET['organize'] == 'alpha_az') {
                            echo " selected=\"selected\"";
                        } ?>>Alphabetical (A-Z)
                        </option>
                        <option value="alpha_za"<?php if ($_GET['organize'] == 'alpha_za') {
                            echo " selected=\"selected\"";
                        } ?>>Alphabetical (Z-A)
                        </option>
                        <option value="price_low"<?php if ($_GET['organize'] == 'price_low') {
                            echo " selected=\"selected\"";
                        } ?>>Price (Low to High)
                        </option>
                        <option value="price_high"<?php if ($_GET['organize'] == 'price_high') {
                            echo " selected=\"selected\"";
                        } ?>>Price (High to Low)
                        </option>
                        <option value="popularity"<?php if ($_GET['organize'] == 'popularity') {
                            echo " selected=\"selected\"";
                        } ?>>Popularity
                        </option>
                    </select><input type="submit" value="Sort"/>
                </div>
                </form>
                <!--<h1 class="zen_notopmargin">%category:name%</h1>-->

                <p class="zen_catalog_crumbs"><span class="zen_catalog_browsing">Browsing &raquo;</span> %breadcrumbs%
                </p>

                <div class="zen_clear"></div>
            </div>

            <?php
            if ($this->changes['total_subcategories'] > 0) {
                ?>
                <ul id="zen_catalog_subcats" class="zen_gray_box">
                    %category_list%
                </ul>
            <?php
            }
            ?>

            %blocks%
            <div class="zen_clear"></div>

            <div class="zen_section_right zen_gray zen_medium zen_pagination">%pagination%</div>
            <div class="zen_clear"></div>

        </div>
        <div class="zen_clear"></div>
    </div>

</div>
	