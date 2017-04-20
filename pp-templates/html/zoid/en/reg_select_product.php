<form action="%pp_url%/pp-functions/form_process.php" method="post" id="zen_form">
    <input type="hidden" name="page" value="product"/>
    <input type="hidden" name="session" value="%session%"/>

    <div id="zen_content" class="zen_round zen_box_shadow zen_fonts">
        <div class="zen_pad">

            %step_list%

            <div class="zen_base_form">
                <h2>Select a Product</h2>
                <p>Please select your membership level below.</p>
                <ul>
                    %products%
                </ul>
                <div class="zen_clear"></div>

                <?php
                if (! empty($this->changes['addon_products'])) {
                ?>
                <h2>Optional Addons</h2>
                <p>Would you be interested in any of the following items?</p>
                <ul>
                    %addon_products%
                </ul>
                <div class="zen_clear"></div>
                <?php
                }
                ?>
            </div>

        </div>
    </div>

    <div id="zen_section_footer" class="zen_fonts zen_small zen_shadow_light zen_gray">
        <div class="zen_section_right" style="text-align:right;">
            <input type="submit" value="Continue" class="zen_focus" />
        </div>
        <div class="zen_section_left">
            <span><a href="%pp_url%/register.php?action=reset&sp=%salt%&session=%session%">Cancel
                    Registration</a></span>
        </div>
        <div class="zen_clear"></div>
    </div>

</form>