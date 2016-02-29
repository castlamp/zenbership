<div class="zen_pad_topl %class%">
    <?php
    if (!empty($this->changes{'data'}['cover_photo'])) {
        ?>
        <div class="zen_catalog_img"><a href="%data:link%">%data:cover_photo%</a></div>
    <?php
    }
    ?>

    <div class="zen_catalog_dets">
        <h2 class="zen_notopmargin"><a href="%data:link%">%data:name%</a></h2>

        <div><span class="zen_catalog_price">%data:format_price%</span> %data:tagline%</div>
        <p class="zen_medium"><a href="%data:link%">Details &raquo;</a></p>
    </div>
    <div class="zen_clear"></div>
</div>