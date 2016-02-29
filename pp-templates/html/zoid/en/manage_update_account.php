<div id="zen_content" class="zen_round">

    <div class="zen_pad_more">

        <div id="zen_catalog_left" class="col25l">
            <h2 class="zen_notopmargin">Navigation</h2>
            {-user_manage_menu-}
        </div>
        <div id="zen_catalog_right" class="col75r">

            <form action="%pp_url%/pp-functions/form_process.php" method="post" id="zen_form"
                  onsubmit="return verifyForm('zen_form');">
                <h1 class="zen_notopmargin">Welcome, %first_name%!</h1>

                <h2 class="zen_notopmargin">Update Account</h2>

                <?php
                if ($this->changes['type'] == 'periodical') {
                    ?>
                    <div class="zen_focus">
                        <div class="zen_pad_topl">
                            <p>In order to ensure that our records are up-to-date, we kindly ask that you take a moment
                                to update your account below.</p>
                        </div>
                    </div>
                    <input type="hidden" name="follow" value="%redirect%"/>
                <?php
                }
                ?>


                <fieldset class="zen" id="fs15" style="display:block;">
                    <legend class="zen">Current Password</legend>
                    <input type="hidden" name="__zen_type" value="update-primary"/>

                    <div class="zen_field_set_col" style="width:100%;">
                        <div class="zen_field_set_col_pad">

                            <div class="zen_field " id="">
                                <label class="zen_left">Current Password<span class="zen_req_star">*</span></label>
                                <div class="zen_field_entry">
                                    <input type="password" id="" name="current_password" value="" style="" class=" req" />
                                    <div id="blockerror5f4dcc3b5aa765d61d8327deb882cf99" class="zen_error_block"></div>
                                </div>
                                <div class="zen_clear"></div>
                            </div>

                            </div>
                        </div>

                    </fieldset>

                %form%

                %custom_forms%

                <div class="zen_submit">
                    <input type="hidden" name="session" value="%form_session%"/>
                    <input type="submit" value="Update Information"/>
                </div>
            </form>

        </div>
        <div class="zen_clear"></div>

    </div>

</div>
	