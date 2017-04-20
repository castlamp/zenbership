<form action="%pp_url%/pp-functions/form_process.php" method="post" id="zen_form"/>
<input type="hidden" name="session" value="%form_session%"/>
<input type="hidden" name="step" value="1"/>

<?php
if ($this->changes['early_bird'] == '1') {
    ?>
    <h2>Early Bird Pricing</h2>
    <ul class="zen_event_product_option">
        %products_early_bird%
    </ul>
<?php
}
?>

<h2 class="zen_topmargin">Tickets</h2>
<?php
if (!empty($this->changes['products_tickets'])) {
    ?>
    <ul class="zen_event_product_option">
        %products_tickets%
    </ul>
<?php
} else {
    ?>
    <ul class="zen_event_product_option">
        <li>This event is free!</li>
    </ul>
<?php
}
?>

<?php
if (!empty($this->changes['products_others'])) {
    ?>
    <h2 class="zen_topmargin">Additional Options</h2>
    <ul class="zen_event_product_option">
        %products_others%
    </ul>
<?php
}
?>


<div class="zen_submit">
    <input type="submit" value="Continue" class="zen_focus"/>
</div>
</form>