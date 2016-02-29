<form action="%pp_url%/pp-functions/form_process.php" method="post" id="zen_form">
    <input type="hidden" name="page" value="%step%"/>
    <input type="hidden" name="session" value="%session%"/>
    <input type="hidden" name="zen_complete" value="%captcha_bypass%"/>

    <div id="zen_content" class="zen_round zen_box_shadow zen_fonts">
        <div class="zen_pad">

            %step_list%

            <div class="zen_attention">
                <div class="zen_pad_topl zen_fonts zen_center">
                    Please review your information before completing the process!
                </div>
            </div>

            %form%

        </div>
    </div>

    <div id="zen_section_footer" class="zen_fonts zen_small zen_shadow_light zen_gray">
        <div class="zen_section_right" style="text-align:right;">
            <input type="submit" value="Complete"/>
        </div>
        <div class="zen_section_left">
            <span><a href="%pp_url%/register.php?action=reset">Cancel Registration</a></span>
        </div>
        <div class="zen_clear"></div>
    </div>

</form>