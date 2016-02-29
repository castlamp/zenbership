<?php

/**
 * INVOICE REMINDERS.
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
$reminder_pre  = $db->get_option('invoice_reminder_pre');
$reminder_post = $db->get_option('invoice_reminder_post');
if (empty($reminder_pre) && empty($reminder_post)) {

    // Reminders are not active.
} else {
    $invoice = new invoice;
    // Not-overdue reminders
    $pre_date = add_time_to_expires($reminder_pre);
    //$days = 86400 * substr($reminder_pre,4,2);
    //$pre_date = date('Y-m-d H:i:s',strtotime(current_date()) - $days);
    // Overdue reminders
    $post_date = add_time_to_expires($reminder_post);
    $STH       = $db->run_query("
        SELECT *
        FROM `ppSD_invoices`
        WHERE `status`!='1'
    ");
    while ($row = $STH->fetch()) {
        // Remind to pay before due date
        if (current_date() < $row['date_due'] && !empty($reminder_pre)) {
            $days     = 86400 * substr($reminder_pre, 4, 2);
            $pre_date = date('Y-m-d H:i:s', strtotime($row['date_due']) - $days);
            if ($pre_date <= current_date() && $row['last_reminder'] == '0000-00-00 00:00:00') {
                $data = $invoice->send_invoice($row['id'], '3');
                // Update "Last Reminder"
                $q1 = $db->update("
                    UPDATE
                        `ppSD_invoices`
                    SET
                        `last_reminder`='" . current_date() . "'
                    WHERE
                        `id`='" . $db->mysql_clean($row['id']) . "'
                    LIMIT 1
                ");
            }

        }
        // Overdue notices after due date
        if (current_date() >= $row['date_due'] && !empty($reminder_post)) {
            // Get the right date to check
            if ($row['last_reminder'] != '0000-00-00 00:00:00') {
                $use_date = $row['last_reminder'];
            } else {
                $use_date = $row['date_due'];
            }
            // Calculate proper information.
            $days      = 86400 * substr($reminder_post, 4, 2);
            $post_date = date('Y-m-d H:i:s', strtotime($use_date) + $days);
            if (current_date() >= $post_date) {
                // Send reminder
                if ($row['total_reminders'] < $db->get_option('invoice_max_reminders')) {
                    // Send the notice
                    $data = $invoice->send_invoice($row['id'], '4');
                    // Update the database of reminders
                    $q1 = $db->update("
                        UPDATE `ppSD_invoices`
                        SET
                            `last_reminder`='" . current_date() . "',
                            `total_reminders`=(`total_reminders`+1),
                            `status`='3'
                        WHERE `id`='" . $db->mysql_clean($row['id']) . "'
                        LIMIT 1
                    ");
                } // Mark as overdue
                else {
                    $q1 = $db->update("
                        UPDATE `ppSD_invoices`
                        SET `status`='4'
                        WHERE `id`='" . $db->mysql_clean($row['id']) . "'
                        LIMIT 1
                    ");
                }

            }

        }

    }

}