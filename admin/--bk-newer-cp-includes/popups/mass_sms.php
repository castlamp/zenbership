

<script type="text/javascript">
    $.ctrl('S', function () {
        return json_add('sms_schedule', '', '0', 'popupform');
    });
</script>


<form action="" method="post" id="popupform"
      onsubmit="return json_add('sms_schedule','','0','popupform');">

    <div id="popupsave">
        <input type="submit" value="Send" class="save"/>
        <input type="hidden" name="criteria_id" value="<?php echo $_POST['criteria_id']; ?>"/>
    </div>
    <h1>Send SMS</h1>

    <div class="pad24t popupbody">

        <p class="highlight">Only users with a cell phone and provider on file, and who have not opted out of SMS
            messages, will receive this message.</p>

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

</form>
