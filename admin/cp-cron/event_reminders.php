<?php

/**
 * EVENT REMINDERS AND FOLLOWUPS
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
$date  = explode(' ', current_date());
$check = $date['0'];
$event = new event;
$sms   = new sms;
$q     = $db->run_query("
    SELECT
        ppSD_event_reminders.*
    FROM
        `ppSD_event_reminders`
    JOIN
        `ppSD_events`
    ON
        ppSD_event_reminders.event_id=ppSD_events.id
    WHERE
        ppSD_events.status='1' AND
        ppSD_event_reminders.send_date='" . $db->mysql_clean($check) . "' AND
        ppSD_event_reminders.sent_on='0000-00-00'
");
while ($row = $q->fetch()) {

    // Get RSVPS and the event data.
    $rsvps = $event->get_event_rsvps($row['event_id']);
    $edata = $event->get_event($row['event_id']);

    // Determine template.
    if (empty($row['template_id'])) {
        if ($row['when'] == 'before') {
            $template = 'event_reminder';
        } else {
            $template = 'event_followup';
        }
    } else {
        $template = $row['template_id'];
    }

    // Make changes to custom message...
    $hold_msg = $row['custom_message'];
    foreach ($edata['data'] as $item => $value) {
        $hold_msg = str_replace('%event:' . $item . '%', $value, $hold_msg);
    }

    // Email/SMS RSVPs
    foreach ($rsvps as $user) {
        $fail        = '0';
        $fail_reason = '';

        // Check reminder status
        $check = $event->check_reminder($row['id'], $user['id']);
        if ($check <= 0) {

            // Update custom message
            foreach ($user as $item => $value) {
                if (!is_array($value)) {
                    $hold_msg = str_replace('%' . $item . '%', $value, $hold_msg);
                }
            }

            // SMS or email?
            if ($row['sms'] == '1') {
                if (!empty($user['cell']) && !empty($user['cell_carrier'])) {
                    $fail        = '1';
                    $fail_reason = 'Not enough cell data.';
                } else if (!empty($user['sms_optout'])) {
                    $fail        = '1';
                    $fail_reason = 'SMS Output';
                } else {
                    $prep = $sms->prep_sms($user['id'], 'rsvp', $hold_msg);
                }

            } // Email
            else {
                if (!empty($user['email'])) {
                    $data                      = array();
                    $changes                   = $user;
                    $changes['custom_message'] = $hold_msg;
                    $changes['event']          = $edata['data'];
                    $email                     = new email('', $user['id'], 'rsvp', $data, $changes, $template);
                } else {
                    $fail        = '1';
                    $fail_reason = 'No E-Mail';
                }
            }

            // ppSD_event_reminder_logs
            $add_log = $event->add_reminder_log($row['event_id'], $user['id'], $row['id'], $fail, $fail_reason);

        }

    }

    // Update the notice status
    $q1 = $db->update("
        UPDATE `ppSD_event_reminders`
        SET `sent_on`='" . current_date() . "'
        WHERE `id`='" . $db->mysql_clean($row['id']) . "'
        LIMIT 1
    ");

}