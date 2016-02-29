<form action="%pp_url%/pp-functions/reset_password_process.php" method="post" id="zen_form"
      onsubmit="return verifyForm('zen_form');">

    <div id="zen_content" class="zen_round zen_box_shadow zen_fonts">
        <div class="zen_pad zen_base_form">

            <h2 class="zen_notopmargin">Select a New Password</h2>

            <p>Please select a new password below, and repeat it for confirmation.</p>


            <ul id="zen_form">
                <li>
                    <label class="zen_left zen_medium">Password</label>

                    <div class="zen_field_entry">
                        <input type="password" id="password" name="password" class="req" style="width:100%;"/>

                        <div id="blockerrorpassword" class="zen_error_block"></div>
                    </div>
                    <div class="zen_clear"></div>
                </li>
                <li>
                    <label class="zen_left zen_medium">Repeat Password</label>

                    <div class="zen_field_entry">
                        <input type="password" id="repeat_pwd" name="repeat_pwd" class="req" style="width:100%;"/>

                        <div id="blockerrorrepeat_pwd" class="zen_error_block"></div>
                    </div>
                    <div class="zen_clear"></div>
                </li>
            </ul>

            <div class="zen_submit">
                <input type="hidden" name="s" value="%code%"/>
                <input type="submit" value="Reset Password"/>
            </div>

        </div>
    </div>

</form>