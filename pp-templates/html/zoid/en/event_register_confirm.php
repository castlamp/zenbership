<div class="zen_space"></div>

<form action="%pp_url%/event.php" method="get"/>
<input type="hidden" name="complete" value="%completion_code%"/>
<input type="hidden" name="id" value="%id%"/>
<input type="hidden" name="act" value="register"/>

<p class="zen_attention"><b>Confirmation Required!</b><br/>Your registration is not yet complete. Please review and
    confirm your information. Payment, if required, will be handled on the next screen.</p>

<h2>Registration</h2>
%registration_data%
<div class="zen_clear"></div>

<h2>Guests (%selected_guests%)</h2>
%guest_data%
<div class="zen_clear"></div>

<div class="zen_submit zen_right">
    <input type="submit" class="zen_focus" value="Confirm and Continue &raquo;"/>
</div>

</form>