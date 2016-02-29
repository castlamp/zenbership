<?php

if ($this->changes['type'] == 'newDay') {


	?>
<li class="newDay" style="margin-top:24px;">
	<h1>%day_formatted% (Day %day%)</h1>

    <div class="zen_timeline_desc">
        %description%
    </div>
<!--
    <p class="zen_timeline_date">From %starts_formatted% until %ends_formatted% (Duration: %duration%)</p>

    <div class="zen_timeline_desc">
        %description%
    </div>
-->
</li>
	<?php
}
	?>
<li>
	<span style="margin-right:8px;display:inline-block;width:80px;">%starts_time_formatted%</span> %title%

    <div class="zen_timeline_desc">
        %description%
    </div>
<!--
    <p class="zen_timeline_date">From %starts_formatted% until %ends_formatted% (Duration: %duration%)</p>

    <div class="zen_timeline_desc">
        %description%
    </div>
-->
</li>
