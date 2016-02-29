<?php

/**
 * PRIMARY CRON JOB
 *
 * This file is used as a cron-job.
 * Recommended cron timeframe is every 2-4 hours.
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
// The program needs 3 cron jobs:
// 1. emailing.php -> Manages the pending e-mail queue.
//      Recommended: every 15 minutes.
//      */15	*	*	*	*	php /full/server/path/to/members/admin/cp-cron/emailing.php
// 2. index.php -> Manages everything else.
//      Recommended: every 2-4 hours.
//      0 */2 * * * php /full/server/path/to/members/admin/cp-cron/index.php
// 3. backup.php -> Re-builds entire cache and backups database.
//      Recommended: every day.
//      0 0 */1 * * php /full/server/path/to/members/admin/cp-cron/backup.php
error_reporting(0);
require dirname(dirname(__FILE__)) . '/sd-system/config.php';
// ----------------------------
//   Start timer.
$time_start = microtime(true);
// ----------------------------
//   Begin cron functions.
// Backup
// backup.class.php
// Cart
// Clear abandonned sessions.
// Do stats on abandonned sessions.
// Product popularity:
//	-> Views 1 point
//	-> Buys 25 points
// require "stat_rebuild.php";
// Event Reminders
require "event_reminders.php";
// Stats
// Rebuild today's stats to
// account for deletions, etc.
// All stat categories.
// Build browser statistics.
// Campaign Performance:
// - ppSD_link_tracking
//   link_id -> matches `ppSD_tracking_activity`.track_id
//   -> type
//   CREATE STATS FOR EACH:
//   members-clicks -> Resulting from email clicks.
//   members-[CAMPAIGN_ID] -> Resulting from clicks from a campaign.
//   contacts-clicks -> Resulting from email clicks.
//   contacts-[CAMPAIGN_ID] -> Resulting from clicks from a campaign.
//   invoices-clicks -> Resulting from email clicks.
//   invoices-[CAMPAIGN_ID] -> Resulting from clicks from a campaign.
//   order-clicks-revenue -> Resulting from email clicks.
//   order-revenue-[CAMPAIGN_ID] -> Resulting from clicks from a campaign.
//   rsvps-clicks -> Resulting from email clicks.
//   rsvps-[CAMPAIGN_ID] -> Resulting from clicks from a campaign.
//   link_clicks-[CAMPAIGN_ID]
//   emails_read-[CAMPAIGN_ID]
require "stat_rebuild.php";
// Subscriptions
// Subscriptions without a credit card
// on file need to send a reminder email
// with an invoice link.
// -> Create invoice.
// -> Send email.
// Also send pre-formatted reminders
// before a subscription is set to
// expire.
require "subscriptions.php";
// Campaigns:
// Ignore if status != '1'
// optin_type = single or double
//  -> Not sent from cron. Sent from the admin control panel.
// optin_type = criteria
//  -> Send based on members or contacts matching the criteria_id, but not
//     present in the "ppSD_campaign_unsubscribe" table.
require "campaigns.php";
// Invoice reminders.
// 	ppSD_invoices where status != '1'.
//	Options for reminders.
//  -> invoice_reminder_no1
//  -> invoice_reminder_no2
//  -> invoice_reminder_post
require "invoice_reminders.php";
// Delete old files
// Attachments?
// QR Codes?

// ----------------------------
// Custom cron jobs

$dh  = opendir(PP_PATH . '/custom/cron');
while (false !== ($filename = readdir($dh))) {
    $path = PP_PATH . '/custom/cron/' . $filename;
    if ($filename == '.' || $filename == '..' || ! is_dir($path)) {
        continue;
    } else {
        if (file_exists($path . '/index.php')) {
            ob_start();
            include $path . '/index.php';
            $output = ob_get_contents();
            ob_end_clean();
        }
    }
}


// ----------------------------
//   Complete timer.
$time_end = microtime(true);
$time     = $time_end - $time_start;
$db->update_option('cron_last_run', current_date());
$db->update_option('cron_time', $time);
exit;
