<?php

/**
 * SUBSCRIPTION RENEWALS
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
$sub = new subscription;

$subscription_retries = $db->get_option('subscription_retries');

if (empty($subscription_retries) || ! is_numeric($subscription_retries)) {
    $subscription_retries = 3;
}

$q1S = $db->run_query("
    SELECT `id`,`retry`
    FROM `ppSD_subscriptions`
    WHERE
      `status`='1' AND
      `next_renew`<='" . current_date() . "'
");
while ($row = $q1S->fetch()) {
    if ($row['retry'] >= $subscription_retries) {
        $er           = $db->get_error('S040');
        $er = str_replace('%retry%', $row['retry'], $er);
        $sub->cancel_subscription($row['id'], $er);
    } else {
        $sub->renew_subscription($row['id'], '1');
    }
}

// Notify upcoming subscriptions
$sub->notifyUpcoming();