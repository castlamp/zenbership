<div id="zen_content" class="round box_shadow fonts">
    <div id="zen_calendar_holderA" class="zen_padtop">
        <div id="zen_calendar_top">
            <h1>%title%</h1>

            <p>
                <span><a href="%prev_link%">&laquo; %prev_month%</a></span>
                <span class="zen_divide">&#183;</span>
                <span><b>%title%</b></span>
                <span class="zen_divide">&#183;</span>
                <span><a href="%next_link%">%next_month% &raquo;</a></span>
            </p>
        </div>

        <div id="zen_calendar_holder">
            %calendar%
        </div>

        <div id="zen_calendar_bottom">
            <div class="zen_section_left">
                <a href="<?php echo PP_URL; ?>/calendar.php?export=%calendar_id%&year=%year%&month=%month%">Export
                    Month</a>
                <span class="zen_divide">&#183;</span>
                <a href="<?php echo PP_URL; ?>/calendar.php?export=%calendar_id%">Export Calendar</a>
            </div>
            <div class="zen_section_right">
                %label_legend%
            </div>
            <div class="zen_clear"></div>
        </div>
    </div>
</div>
		