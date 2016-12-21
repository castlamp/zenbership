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

if (!empty($_POST['data'])) {
    $data = unserialize($_POST['data']);
} else {
    $data = array();
}

?>

<form action="" method="post" id="popupform" onsubmit="return apply_filters('subscriptions');">

    <div id="popupsave">
        <input type="submit" value="Save" class="save"/>
        <input type="hidden" value="filter[campaign_id]" class="<?php echo $_POST['campaign_id']; ?>" />
    </div>
    <h1>Apply Filters</h1>

    <div id="pop_inner" class="pad24t popupbody">

        <fieldset>
            <legend>Basic Details</legend>
            <div class="pad24t">

                <?php
                $admin = new admin;
                // name:table:date:date_range
                $thefilters = array(
                    'date:ppSD_campaign_subscriptions:1:1',
                    'optin_date:ppSD_campaign_subscriptions:1:1',
                );
                foreach ($thefilters as $aFilter) {
                    $exp = explode(':', $aFilter);
                    if (empty($exp['1'])) {
                        $exp['1'] = 'ppSD_event_rsvps';
                    }
                    if (!empty($data[$exp['0']])) {
                        $value = $data[$exp['0']];
                    } else {
                        $value = '';
                    }

                    ?>
                    <div class="field">
                        <label><?php echo format_db_name($exp['0']); ?></label>

                        <div class="field_entry">
                            <?php

                            if ($exp['2'] == '1') {
                                $date = '1';
                            } else {
                                $date = '0';
                            }
                            if ($exp['3'] == '1') {
                                $dater = '1';
                            } else {
                                $dater = '0';
                            }
                            echo $admin->filter_field($exp['0'], $value, $exp['1'], '1', $date, $dater);
                            if ($dater == '1') {
                                ?>
                                <p class="field_desc_show">Create a date range by inputting two dates, or select a
                                    specific date by only inputting the first field. All dates need to be in the
                                    "YYYY-MM-DD" format.</p>
                            <?php

                            }
                            ?>
                        </div>
                    </div>
                <?php

                }
                ?>

                <div class="field">
                    <label>Subscribed By</label>
                    <div class="field_entry">
                        <input type="radio" name="filter[subscribed_by]" value="" checked="checked" /> --<br/>
                        <input type="radio" name="filter[subscribed_by]" value="condition" /> Form Condition<br/>
                        <input type="radio" name="filter[subscribed_by]" value="criteria" /> Criteria<br/>
                    </div>
                </div>
                <input type="hidden" name="filter_tables[subscribed_by]" value="ppSD_campaign_subscriptions"/>

                <div class="field">
                    <label>Status</label>
                    <div class="field_entry">
                        <input type="radio" name="filter[active]" value="" checked="checked" /> --<br/>
                        <input type="radio" name="filter[active]" value="1" /> Active<br/>
                        <input type="radio" name="filter[active]" value="-" /> Pending<br/>
                    </div>
                </div>
                <input type="hidden" name="filter_tables[active]" value="ppSD_campaign_subscriptions"/>

                <div class="field">
                    <label>User Type</label>
                    <div class="field_entry">
                        <input type="radio" name="filter[user_type]" value="" checked="checked" /> --<br/>
                        <input type="radio" name="filter[user_type]" value="member" /> Member<br/>
                        <input type="radio" name="filter[user_type]" value="contact" /> Contact<br/>
                    </div>
                </div>
                <input type="hidden" name="filter_tables[user_type]" value="ppSD_campaign_subscriptions"/>

            </div>
        </fieldset>

    </div>

</form>