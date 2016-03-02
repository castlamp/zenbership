<?php

/**
 * STATISTIC REBUILD
 * This file is part of a cron job (index.php)
 * All necessary classes have been pre-loaded.
 *
 * Purpose: Builds statistics based on what
 * happened yesterday.
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
// require "../sd-system/config.php";
// ----------------------------
//   Product Popularity
$day = date('d');
if ($day < 7) {
    $last_month = date('Y-m', strtotime(current_date()) - 1555200);
} else {
    $last_month = date('Y-m', strtotime(current_date()));
}
$view_points = $db->get_option('product_view_points');
$sale_points = $db->get_option('product_sale_points');
$q1          = $db->run_query("
    SELECT `id`,`base_popularity`
    FROM `ppSD_products`
");
while ($row = $q1->fetch()) {
    $key2       = 'product_views-' . $row['id'] . '-' . $last_month;
    $key1       = 'product_sale-' . $row['id'] . '-' . $last_month;
    $views      = new stats($key2, 'get', '');
    $sales      = new stats($key1, 'get', '');
    $popularity = ($views->final * $view_points) + ($sales->final * $sale_points) + $row['base_popularity'];
    $upd        = $db->update("
        UPDATE `ppSD_products`
        SET `popularity`='" . $popularity . "'
        WHERE `id`='" . $db->mysql_clean($row['id']) . "'
        LIMIT 1
    ");
    $db->put_stats('popularity-' . $row['id']);

}
// ----------------------------
//   Campaign Statistics
$yesterday = date('Y-m-d', strtotime(current_date()) - 86400);
$q9        = $db->run_query("
    SELECT `id`
    FROM `ppSD_campaigns`
");
while ($row = $q9->fetch()) {
    $key1     = 'link_clicks-' . $row['id'] . '-' . $yesterday;
    $key2     = 'emails_read-' . $row['id'] . '-' . $yesterday;
    $key3     = 'campaign_unsubscriptions-' . $row['id'] . '-' . $yesterday;
    $key4     = 'campaign_subscriptions-' . $row['id'] . '-' . $yesterday;
    $key5     = 'member-' . $row['id'] . '-' . $yesterday;
    $key6     = 'rsvp-' . $row['id'] . '-' . $yesterday;
    $key7     = 'contact-' . $row['id'] . '-' . $yesterday;
    $key8     = 'invoice-' . $row['id'] . '-' . $yesterday;
    $key9     = 'order-' . $row['id'] . '-' . $yesterday;
    $key10    = 'emails_sent-' . $row['id'] . '-' . $yesterday;
    $clicks   = new stats($key1, 'get', '');
    $read     = new stats($key2, 'get', '');
    $unsub    = new stats($key3, 'get', '');
    $subs     = new stats($key4, 'get', '');
    $members  = new stats($key5, 'get', '');
    $rsvp     = new stats($key6, 'get', '');
    $contacts = new stats($key7, 'get', '');
    $invoices = new stats($key8, 'get', '');
    $orders   = new stats($key9, 'get', '');
    $sent     = new stats($key10, 'get', '');
    //$click_through_percent =
    $effectiveness = ($clicks->final * 0.25) +
        ($read->final * 0.05) +
        ($orders->final * 4) +
        ($members->final * 3) +
        ($rsvp->final * 2) +
        ($invoices->final * 1) +
        ($contacts->final * 1) -
        ($unsub->final * 1);
    $db->put_stats('campaign_effectiveness-' . $row['id'] . '-' . $yesterday, $effectiveness, 'update');
}


/**
 * Anonymous Statistics Collection
 *
 * This is only sent if you opted into the program when your installed it.
 * You can opt-out anytime by updating your "Anonymous Stat Opt-in" option
 * under "Integration > Administration > Options". Stats are sent twice per
 * month on the 1st and 15th when this cron runs.
 */
$stat_opt_in = $db->get_option('stat_opt_in');
if ($stat_opt_in == '1' && (date('j') == 1 || date('j') == 15) && date('G') <= 4) {
    $members = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_members
    ");
    $contacts = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_contacts
    ");
    $rsvp = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_event_rsvps
    ");
    $events = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_events
    ");
    $transactions = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_cart_sessions
        WHERE status='1'
    ");
    $totalSales = $db->get_array("
        SELECT SUM(total) AS total
        FROM ppSD_cart_session_totals
    ");
    $subs = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_subscriptions
    ");
    $invoices = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_invoices
    ");
    $invoiceTotals = $db->get_array("
        SELECT SUM(paid) AS total
        FROM ppSD_invoice_payments
    ");
    $users = $db->get_array("
        SELECT COUNT(*) AS total
        FROM ppSD_staff
    ");

    $currency = $db->get_option('currency');

    $send = array(
        'anon_id' => md5(PP_BASE_PATH . SALT),
        'members' => $members['total'],
        'contacts' => $contacts['total'],
        'rsvp' => $rsvp['total'],
        'events' => $events['total'],
        'transactions' => $transactions['total'],
        'sales' => $totalSales['total'],
        'subscriptions' => $subs['total'],
        'invoices' => $invoices['total'],
        'invoiceSales' => $invoiceTotals['total'],
        'users' => $users['total'],
        'currency' => $currency,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.castlamp.com/clients/custom/plugins/anon_zenbership_stats/index.php');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send));
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result = curl_exec ($ch);
    curl_close ($ch);
}