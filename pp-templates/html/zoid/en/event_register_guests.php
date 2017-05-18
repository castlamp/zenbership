<div class="zen_space"></div>

<form action="%pp_url%/pp-functions/form_process.php" method="post"/>
<input type="hidden" name="session" value="%form_session%"/>
<input type="hidden" name="step" value="3"/>

<h1>Are you bringing any guests?</h1>

<p>If you plan on bringing guests, please select a guest ticket for each below. Note that there is a limit of %max_guests% guest(s) per attendee.</p>

<div class="zen_field " id="blockfirst_name">
    <label class="zen_left">Guests</label>
    <div class="zen_field_entry">
        <span id="guestsDisplay">0</span>
        <input type="hidden" id="guests" name="guests" value="%selected_guests%" maxlength="60" style="" />
    </div>
</div>
<div class="zen_clear"></div>

<h2>Guest Pricing</h2>
<?php
if (!empty($this->changes['products_guests'])) {
    ?>
    <ul class="zen_event_product_option">
        %products_guests%
    </ul>
<?php
} else {
    ?>
    <p>Guests are free!</p>
<?php
}
?>

<div class="zen_submit">
    <input type="submit" value="Continue" class="zen_focus" />
</div>
</form>

<script type="text/javascript">
    var totalTickets = 0;
    $(document).ready(function() {
        $('.event_ticket_entry').change(function() {
            totalTickets = 0;
            $(".event_ticket_entry").each(function(index) {
                totalTickets += parseInt($(this).val());
            });
            $('#guests').val(totalTickets);
            $('#guestsDisplay').html(totalTickets);
        });
    });
</script>