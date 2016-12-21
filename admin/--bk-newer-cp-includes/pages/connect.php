<!--
WHY THIS PAGE IS REQUIRED!

CLEditor does not play well with the jQuery UI Slide effect.
This is a problem, as we need it to load the... slider.
So the work around is load this static page in the slider,
and then proceed to instantly get the subpage on which
CLEditor is found.

Kind of a hack, but programming is a mess, so what are you
going to do?
-->

<script type="text/javascript">
    $(document).ready(function () {
        get_slider_subpage('email');
    });
</script>

<div id="primary_slider_content">
    <div class="pad24">
        <p style="text-align:center;">
            Criteria has been established. Please hold while we load your email...
            <br/><br/><br/>
            <a href="returnnull.php" onclick="return get_slider_subpage('email');">If you aren't redirected, click here
                to load your email manually.</a>
        </p>
    </div>
</div>