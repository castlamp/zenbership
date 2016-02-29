<form action="%type_page%" method="get" id="zen_form">
    <input type="hidden" name="id" value="%form_id%"/>

    <div id="zen_content" class="zen_round zen_box_shadow zen_fonts">
        <div class="zen_pad zen_base_form">

            <h2 class="zen_notopmargin">Please Input Your Registration Code</h2>

            <p class="zen_medium">A code is required to use this form.</p>

            <label class="zen_left zen_medium">Code</label>

            <div class="zen_field_entry zen_medium">
                <input type="text" name="code" maxlength="29" style="width:300px;"/>
            </div>
            <div class="zen_clear"></div>

        </div>
    </div>

    <div id="zen_section_footer" class="zen_fonts zen_small zen_shadow_light zen_gray">
        <div class="zen_section_right" style="text-align:right;">
            <input type="submit" value="Continue"/>
        </div>
        <div class="zen_section_left">
            <span><a href="%pp_url%/register.php?action=reset">Cancel Registration</a></span>
        </div>
        <div class="zen_clear"></div>
    </div>

</form>