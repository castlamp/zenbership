<?php

/**
 * CRITERIA-BASED CAMPAIGNS
 * This file is part of a cron job (index.php)
 * All necessary classes have been pre-loaded.
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
$date = explode(' ', current_date());
$q1   = $db->run_query("
    SELECT
      ppSD_campaign_items.id,
      ppSD_campaign_items.campaign_id,
      ppSD_campaigns.criteria_id,
      ppSD_campaigns.when_type,
      ppSD_campaigns.user_type,
      ppSD_campaigns.type
    FROM
      `ppSD_campaign_items`
    JOIN
      `ppSD_campaigns`
    ON
      ppSD_campaigns.id=ppSD_campaign_items.campaign_id
    WHERE
      ppSD_campaigns.status='1' AND
      ppSD_campaigns.optin_type='criteria'
");
while ($row = $q1->fetch()) {
    // ----------------------------
    //   Load campaign functions,
    //   Load the message,
    //   And prepare for sending.
    $campaign = new campaign($row['campaign_id']);
    $msg_data = $campaign->get_msg($row['id']);

    $connect  = new connect($msg_data['data']['msg_id']); // Load the email
    // ----------------------------
    //   Get criteria and build
    //   the query.
    $criteria   = new criteria($row['criteria_id']);
    $applicable = $db->run_query($criteria->query);

    // ----------------------------
    //   "after_join" campaigns
    if ($row['when_type'] == 'after_join') {
        // Loop possible users.
        while ($user = $applicable->fetch()) {

            if ($row['user_type'] == 'member') {
                $use_date = $user['joined'];
            } else {
                $use_date = $user['created'];
            }

            $dif = explode(' ', add_time_to_expires($msg_data['data']['when_timeframe'], $use_date));

            // Correct date: proceed.
            if ($dif['0'] == $date['0']) {
                $check_log      = $campaign->check_log($user['id'], $row['user_type'], $row['id']);
                $unsubscription = $campaign->find_unsubscription($user['id'], $row['user_type']);
                if ($unsubscription['unsubscribed'] != '1' && $check_log <= 0) {
                    // Queue email for sending...
                    if ($row['type'] == 'email') {
                        $add     = $connect->queue_email($user['id'], $row['user_type'], '0');
                        $add_log = $campaign->add_log($user['id'], $row['user_type'], $row['id']);
                    }
                }
            } else {
                continue;
            }

        }

    } // ----------------------------
    //   "exact_date" campaigns
    else if ($row['when_type'] == 'exact_date') {
        // Only send on correct date
        $bdate = explode(' ', $msg_data['data']['when_date']);
        if ($bdate['0'] == $date['0']) {
            // Loop possible users.
            while ($user = $applicable->fetch()) {
                $check_log      = $campaign->check_log($user['id'], $row['user_type'], $row['id']);
                $unsubscription = $campaign->find_unsubscription($user['id'], $row['user_type']);
                if ($unsubscription['unsubscribed'] != '1' && $check_log <= 0) {
                    // Queue email for sending...
                    if ($row['type'] == 'email') {
                        $add     = $connect->queue_email($user['id'], $row['user_type'], '0');
                        $add_log = $campaign->add_log($user['id'], $row['user_type'], $row['id']);
                    }

                }

            }

        }

    }

}