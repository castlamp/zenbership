<form action="%pp_url%/pp-functions/login.php" method="post" id="zen_form" onsubmit="return verifyLogin('zen_form');">

    <div id="zen_content" class="zen_fonts">
        <div class="zen_pad_more">

            <div id="zen_login_left" class="col70l">

                <h2 class="zen_notopmargin">Existing Members</h2>

                <p>Please input your username and password below to continue.</p>

                <div id="zen_login_error"></div>

                <ul id="zen_form">
                    <li>
                        <label class="zen_left zen_medium">Username</label>

                        <div class="zen_field_entry">
                            <input type="text" name="username" class="req" style="width:100%;"/>

                            <div id="error_username" class="error"></div>
                        </div>
                        <div class="zen_clear"></div>
                    </li>
                    <li>
                        <label class="zen_left zen_medium">Password</label>

                        <div class="zen_field_entry">
                            <input type="password" name="password" class="req" style="width:100%;"/>

                            <div id="error_password" class="error"></div>
                        </div>
                        <div class="zen_clear"></div>
                    </li>
                    <li>
                        <label class="zen_left zen_medium">&nbsp;</label>

                        <div class="zen_field_entry zen_medium">
                            <input type="checkbox" name="remember" value="1"/> Remember me for a week <span
                                class="zen_gray">(not recommended for public computers)</span>
                        </div>
                        <div class="zen_clear"></div>
                    </li>
                    <li id="captcha_block" class="notice" style="display:none;">
                        <label class="zen_left zen_medium">&nbsp;</label>

                        <div class="zen_field_entry zen_medium">
                            <img width="200" height="50" id="captchaput" class="imageout" src=""/><input type="text"
                                                                                                         name="captcha"
                                                                                                         value=""
                                                                                                         class="home"
                                                                                                         style="width:200px;"/>
                        </div>
                        <div class="zen_clear"></div>
                    </li>
                </ul>
                <div class="zen_submit">
                    <input type="hidden" name="url" value="%url%"/>
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

                <h2>Need Help?</h2>

                <div class="zen_gray_box">
                    <div class="zen_pad zen_medium">
                        <a href="%pp_url%/lost_password.php">Lost password recovery</a>
                    </div>
                </div>

            </div>
            <div class="zen_clear"></div>

        </div>
    </div>

</form>