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

$c_data = new history($_POST['id'], '', '', '', '', '', 'ppSD_email_scheduled');
$data = new history($c_data->{'final_content'}['email_id'], '', '', '', '', '', 'ppSD_saved_email_content');
if ($c_data->{'final_content'}['user_type'] == 'contact') {
    $contact = new contact;
    $cdata   = $contact->get_contact($c_data->{'final_content'}['user_id']);
    $theuser = "<a href=\"#\" onclick=\"return load_page('contact','view','" . $cdata['data']['id'] . "');\">Contact " . $cdata['data']['last_name'] . ", " . $cdata['data']['first_name'] . "</a>";
} else if ($c_data->{'final_content'}['user_type'] == 'member') {
    $user    = new user;
    $cdata   = $user->get_user($c_data->{'final_content'}['user_id']);
    $theuser = "<a href=\"#\" onclick=\"return load_page('member','view','" . $cdata['data']['id'] . "');\">Member " . $cdata['data']['last_name'] . ", " . $cdata['data']['first_name'] . "</a>";
}
?>

<div id="popupsave">
    <input type="button" value="Re-send" class="save"
           onclick="return resend_email('<?php echo $data->{'final_content'}['id']; ?>');"/> <input type="button"
                                                                                                    value="Delete"
                                                                                                    class="del"
                                                                                                    onclick="return delete_item('ppSD_saved_emails','<?php echo $data->{'final_content'}['id']; ?>','','','2');"/>
</div>
<h1>Viewing Scheduled E-Mail</h1>

<div class="fullForm popupbody">

    <fieldset>
        <div class="pad">


    <div class="field">
        <label class="less">Date</label>

        <div class="field_entry_less">
            <?php echo format_date($data->{'final_content'}['date']); ?>
        </div>
    </div>

    <div class="field">
        <label class="less">User</label>

        <div class="field_entry_less">
            <?php echo $theuser; ?>
        </div>
    </div>

    <div class="field">
        <label class="less">From</label>

        <div class="field_entry_less">
            <?php echo $data->{'final_content'}['from']; ?>
        </div>
    </div>

    <div class="field">
        <label class="less">Subject</label>

        <div class="field_entry_less">
            <?php echo $data->{'final_content'}['subject']; ?>
        </div>
    </div>

</div>
</fieldset>


    <div class="field">
        <iframe width="100%" height="600" frameborder='0'
                src="cp-functions/get_saved_email.php?queue=1&id=<?php echo $data->{'final_content'}['id']; ?>"
                style="border: 1px solid #ccc;"></iframe>
    </div>

</div>