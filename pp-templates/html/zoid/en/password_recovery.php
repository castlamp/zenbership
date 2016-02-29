<form action="%pp_url%/pp-functions/lost_password.php" method="post" id="zen_form"
      onsubmit="return verifyForm('zen_form');">

    <div id="zen_content" class="zen_fonts">
        <div class="zen_pad_more">

            <div id="zen_login_left" class="col70l">

                <h2 class="zen_notopmargin">Reset Your Password</h2>

                <p>Please input the following information to reset your password.</p>

                <div id="zen_login_error"></div>

                <ul id="zen_form">
                    <li>
                        <label class="zen_left zen_medium">E-Mail or <br/>Member No.</label>

                        <div class="zen_field_entry">
                            <input type="text" name="email" class="req" style="width:100%;"/>

                            <div id="error_username" class="error"></div>
                        </div>
                        <div class="zen_clear"></div>
                    </li>
                    <li>
                        <label class="zen_left zen_medium">&nbsp;</label>
                        %captcha_block%
                        <div class="zen_clear"></div>
                    </li>
                </ul>


                <div class="zen_submit">
                    <input type="submit" value="Login"/>
                </div>

            </div>
            <div id="zen_login_right" class="col30r">

                <h2 class="zen_notopmargin">Need an Account?</h2>

                <div class="zen_gray_box">
                    <div class="zen_pad zen_medium">
                        <a href="%pp_url%/register.php">Click here</a> to register.
                    </div>
                </div>

                <h2>Have an Account?</h2>

                <div class="zen_gray_box">
                    <div class="zen_pad zen_medium">
                        <a href="%pp_url%/login.php">Log into your account.</a>
                    </div>
                </div>

            </div>
            <div class="zen_clear"></div>

        </div>
    </div>

</form>