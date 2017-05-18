%cover_photos%

<div id="zen_content" class="zen_round zen_margin_top">

        <!--
        <div id="zen_event_title" class="">
            <h1 class="zen_nobotmargin">%name%</h1>
        </div>
        -->

        <div id="zen_event_left" class="col30l">
            <div class="zen_event_details">
                <?php
                if ($this->changes['reg_closed'] != '1') {
                    if (empty($_GET['act']) || $_GET['act'] != 'register') {
                ?>
                    <a class="zen_focus" href="%pp_url%/event.php?act=register&id=%id%">Register</a>
                <?php
                    }
                }
                ?>

                <h2 class="zen_topmargin">More Information</h2>
                <div class="zen_gray_box">
                    <ul id="zen_event_links">
                        <li><a href="%pp_url%/event.php?id=%id%">Overview</a></li>
                        <?php
                        if (!empty($this->changes['timeline'])) {
                            ?>
                            <li><a href="%pp_url%/event.php?act=timeline&id=%id%">Timeline</a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>

                <h2 class="zen_topmargin">Details</h2>
                <div class="zen_gray_box  zen_pad_topl">
                    <dl>
                        <dt>Starts</dt>
                        <dd>%starts_formatted%</dd>
                        <dt>Ends</dt>
                        <dd>%ends_formatted%</dd>
                        <dt>Location</dt>
                        <dd>%location_name%</dd>
                        <dt>Capacity</dt>
                        <dd>%stats:show_spaces%</dd>
                    </dl>
                    <div class="zen_clear"></div>
                </div>

                <h2 class="zen_topmargin">Registration Details</h2>
                <div class="zen_gray_box zen_pad_topl">
                    <dl>
                        <?php
                        if ($this->changes['reg_closed'] == '1') {
                            ?>
                            <dt>Status</dt>
                            <dd>Closed</dd>
                        <?php
                        }
                        else if ($this->changes['reg_closed'] == '2') {
                            ?>
                            <dt>Status</dt>
                            <dd>Pre-Registration Stage</dd>
                            <dt>Begins</dt>
                            <dd>%start_registrations_formatted%</dd>
                            <?php
                            if ($this->changes['early_bird'] == '1') {
                                ?>
                                <dt>Early Bird Ends</dt>
                                <dd>%earlybird_formatted%</dd>
                            <?php
                            }
                            ?>
                        <?php
                        }
                        else {
                            ?>
                            <dt>Status</dt>
                            <dd>Open</dd>
                            <dt>Begins</dt>
                            <dd>%start_registrations_formatted%</dd>
                            <dt>Ends</dt>
                            <dd>%close_registrations_formatted%</dd>

                            <?php
                            if ($this->changes['early_bird'] == '1') {
                                ?>
                                <dt>Early Bird Ends</dt>
                                <dd>%earlybird_formatted%</dd>
                            <?php
                            }
                            ?>

                        <?php
                        }
                        ?>
                    </dl>
                    <div class="zen_clear"></div>
                </div>

                <?php
                if (! empty($this->changes['map']) && $this->changes['online'] != '1') {
                    ?>
                    <div id="zen_event_map">
                        %map%
                    </div>
                <?php
                }
                ?>

            </div>
        </div>
        <div id="zen_event_right" class="col70r">
            <div class="zen_pad_more">

            %member_registered%

            %wrapper_content%

            </div>
        </div>
        <div class="zen_clear"></div>

</div>
	