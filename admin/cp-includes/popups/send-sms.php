<?php

/**
 *
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */
if ($_POST['type'] == 'contact') {
    $contact = new contact;
    $cdata   = $contact->get_contact($_POST['id']);
    if (!empty($cdata['data']['id'])) {
        $theuser = "<a href=\"#\" onclick=\"return load_page('contact','view','" . $cdata['data']['id'] . "');\">Contact " . $cdata['data']['last_name'] . ", " . $cdata['data']['first_name'] . "</a>";
    } else {
        $theuser = '';
    }
} else {
    $user  = new user;
    $cdata = $user->get_user($_POST['id']);
    if (!empty($cdata['data']['id'])) {
        $theuser = "<a href=\"#\" onclick=\"return load_page('member','view','" . $cdata['data']['id'] . "');\">Member " . $cdata['data']['last_name'] . ", " . $cdata['data']['first_name'] . "</a>";
    } else {
        $theuser = '';
    }
}

$sms_plugin = $db->get_option('sms_plugin');

$showSMS = false;
if (! empty($sms_plugin)) {
    if (! empty($cdata['data']['cell']) && $cdata['data']['sms_optout'] != '1') {
        $showSMS = true;
    }
} else {
    if (! empty($cdata['data']['cell']) && !empty($cdata['data']['cell_carrier']) && $cdata['data']['cell_carrier'] != 'SMS Unavailable' && $cdata['data']['sms_optout'] != '1') {
        $showSMS = true;
    }
}

if (! $showSMS) {
    $admin->show_popup_error('Cell phone or cell carrier not available, or the user has opted out of SMS services.');
} else {
    ?>

    <script type="text/javascript">
        $.ctrl('S', function () {
            return sendtext('<?php echo $cdata['data']['id']; ?>', '<?php echo $_POST['type']; ?>', 'popupform');
        });
    </script>

    <form action="" method="post" id="popupform"
          onsubmit="return sendtext('<?php echo $cdata['data']['id']; ?>','<?php echo $_POST['type']; ?>','popupform');">

        <div id="popupsave">
            <input type="submit" value="Send" class="save"/>
            <input type="hidden" name="user_id" value="<?php echo $cdata['data']['id']; ?>"/>
        </div>
        <h1>Send SMS</h1>

        <div class="popupbody">

            <p class="highlight">Use this form to send an SMS message to a member or contact.</p>

            <fieldset>
                <div class="pad fullForm">

                    <div class="field">
                        <label class="less">User</label>

                        <div class="field_entry_less">
                            <?php echo $theuser; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="less">Cell</label>

                        <div class="field_entry_less">
                            <?php echo $cdata['data']['cell'] . " (" . $cdata['data']['cell_carrier'] . ")"; ?>
                        </div>
                    </div>

                    <div class="space"></div>
                    <div class="field">
                        <label class="top">Text Message</label>

                        <div class="field_entry_top">
                            <input type="text" name="message" id="sms-message" style="width:100%;" maxlength="160"/>

                            <p class="contact_frequency_dets">Limit 160 characters. Caller tags are available for use. Use any
                                caller tag with the %fIelD_nAmE% syntax. So for example, to include the "First Name", use
                                %first_name%. Any field on file for this user can be used as a caller tag.</p>
                        </div>
                    </div>

                </div>
            </fieldset>

        </div>

    </form>

<?php
}
?>