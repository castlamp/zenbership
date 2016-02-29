<tr class="line">
    <td>
        <?php
        if (!empty($this->changes['main_thumbnail'])) {
            ?>
            <a href="%link%">%main_thumbnail%</a>
        <?php
        }
        ?>
        <span class="zen_large">%format_link%</span><br/>

        <p class="zen_product_options">%option_data%</p>
    </td>
    <td>%display:unit_price%</td>
    <td><input type="text" name="qty[%in_id%]" value="%pricing:qty%" style="width:45px;"/></td>
    <td class="zen_right">%display:subtotal%</td>
</tr>