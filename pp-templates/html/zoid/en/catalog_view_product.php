<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">
        <?php
        if (!empty($this->changes['data']['cover_photo_large'])) {
        ?>
        <div id="zen_view_product_left" class="col30l">

            <a href="#" onclick="return expandImage();">%data:cover_photo_large%</a>

            <div id="zen_product_tb">
                %data:thumbnails%
            </div>

        </div>
        <div id="zen_view_product_right" class="zen_fonts col70r">
            <?php
            } else {
            ?>
            <div id="zen_view_product_full" class="zen_fonts">
                <?php
                }
                ?>

                <form action="%pp_url%/pp-cart/ajax-functions.php" onsubmit="return add_to_cart();" id="zen_cart_form"
                      method="post">
                    <div id="zen_product_add" class="zen_gray_box">
                        <div class="zen_pad_less">
                            <ul id="zen_product_options">
                                %data:fields%
                                <li>
                                    <label class="zen_prod_opt_label">Qty</label>
                                    <input type="text" name="qty" style="width:70px;" value="1"/>
                                </li>
                            </ul>

                            <center><input type="hidden" name="id" value="%data:id%"/><input type="submit"
                                                                                             value="Add to Cart"/>
                            </center>
                        </div>
                    </div>
                </form>

                <p class="zen_prog_crumbs"><span class="zen_catalog_browsing">Found In &raquo;</span> %breadcrumbs%</p>

                <h1>%data:name%</h1>

                <p class="zen_gray zen_tiny">Product Code: %data:id%</p>

                <div id="zen_prod_view_price">%data:format_price%</div>

                <p class="zen_prog_tag">%data:tagline%</p>

                <div class="zen_prog_desc">
                    %data:description%
                </div>

            </div>
            <div class="zen_clear"></div>
        </div>

    </div>
	