<div class="zen_space"></div>

<form action="%pp_url%/pp-functions/form_process.php" method="post"/>
<input type="hidden" name="session" value="%form_session%"/>
<input type="hidden" name="step" value="3"/>

<h1>Are you bringing any guests?</h1>

<p>If you plan on bringing one or more guests, please tell us how many below, and if applicable, select a guest ticket.
    Note that there is a limit of %max_guests% guest(s) per registrant.</p>

<div class="zen_field " id="blockfirst_name">
    <label class="zen_left">Guests<span class="zen_req_star">*</span></label>

    <div class="zen_field_entry">
        <input type="text" id="guests" name="guests" value="%selected_guests%" maxlength="60" style="" class=" req"/>
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
    <input type="submit" value="Continue"/>
</div>
</form>