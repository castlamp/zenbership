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
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Search contacts
$contacts = 0;
$members  = 0;
$rvsps    = 0;
$accounts = 0;
$rsvps    = 0;
$events   = 0;
$return   = '';
$employee = $admin->check_employee('', '1');
/**
 *    Accounts

 */
$permission = $admin->check_permissions('accounts', $employee);
if ($permission == '1') {
    if ($employee['permissions']['admin'] != '1') {
        $where = " AND (ppSD_accounts.public='1' OR ppSD_accounts.owner='" . $employee['id'] . "')";
    } else {
        $where = '';
    }
    $STH     = $db->run_query("
		SELECT
			ppSD_account_data.company_name,
			ppSD_accounts.id,
			ppSD_accounts.name
		FROM
			ppSD_accounts
		JOIN
			ppSD_account_data
		ON
			ppSD_accounts.id=ppSD_account_data.account_id
		WHERE
			ppSD_account_data.company_name LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_accounts.name LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_accounts.id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'
			$where
		ORDER BY
			ppSD_account_data.company_name ASC
		LIMIT 25
	");
    $accounts = 0;
    $results = '';
    while ($row = $STH->fetch()) {
        $accounts++;
        $results .= "<li class=\"res\" onclick=\"return load_page('account','view','" . $row['id'] . "');\">" . $row['name'] . "</li>";

    }
    $return .= '<ul class="ajax_search">';
    $return .= '<li class="head">Accounts (' . $accounts . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
/**
 *    Contacts

 */
$permission = $admin->check_permissions('contacts', $employee);
if ($permission == '1') {
    if ($employee['permissions']['admin'] != '1') {
        $where = " AND (ppSD_contacts.public='1' OR ppSD_contacts.owner='" . $employee['id'] . "')";

    } else {
        $where = '';

    }
    // Additional searchable fields
    $exp = explode(',', $db->get_option('additional_search_contacts'));
    foreach ($exp as $aField) {
        $trim = trim($aField);
        if ($trim == 'last_name' || $trim == 'email' || $trim == 'id') {

        } else {
            if (!empty($trim)) {
                $where .= " OR ppSD_contact_data." . $db->mysql_cleans($aField) . " LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'";
            }
        }

    }
    // Run the search
    $STH     = $db->run_query("
		SELECT
			ppSD_contact_data.last_name,
			ppSD_contacts.created,
			ppSD_contacts.id
		FROM
			ppSD_contacts
		JOIN
			ppSD_contact_data
		ON
			ppSD_contacts.id=ppSD_contact_data.contact_id
		WHERE
			ppSD_contact_data.last_name LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_contacts.email LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_contacts.id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'
			$where
		ORDER BY
			ppSD_contact_data.last_name ASC
		LIMIT 25
	");
    $contacts = 0;
    $results = '';
    while ($row = $STH->fetch()) {
        $contacts++;
        $results .= "<li class=\"res\" onclick=\"return load_page('contact','view','" . $row['id'] . "');\">" . $row['last_name'] . ", " . $row['first_name'] . " (" . date('Y-m-d', strtotime($row['created'])) . ")</li>";

    }
    $return .= '<ul class="ajax_search">';
    $return .= '<li class="head">Contacts (' . $contacts . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
/**
 *    Members

 */
$permission = $admin->check_permissions('members', $employee);
if ($permission == '1') {
    if ($employee['permissions']['admin'] != '1') {
        $where = " AND (ppSD_members.public='1' OR ppSD_members.owner='" . $employee['id'] . "')";
    } else {
        $where = '';
    }
    // Additional searchable fields
    $exp = explode(',', $db->get_option('additional_search_members'));
    foreach ($exp as $aField) {
        $trim = trim($aField);
        if ($trim == 'last_name' || $trim == 'email' || $trim == 'username' || $trim == 'id') {

        } else {
            if (!empty($trim)) {
                $where .= " OR ppSD_member_data." . $db->mysql_cleans($aField) . " LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'";
            }
        }
    }
    $STH     = $db->run_query("
		SELECT
			ppSD_member_data.first_name,
			ppSD_member_data.last_name,
			ppSD_members.username,
			ppSD_members.id
		FROM
			ppSD_members
		JOIN
			ppSD_member_data
		ON
			ppSD_members.id=ppSD_member_data.member_id
		WHERE
			ppSD_member_data.last_name LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_members.email LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_members.username LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR
			ppSD_members.id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'
			$where
		ORDER BY
			ppSD_members.username ASC
		LIMIT 25
	");

    $members = 0;
    $results = '';
    while ($row = $STH->fetch()) {
        $members++;
        $results .= "<li class=\"res\" onclick=\"return load_page('member','view','" . $row['id'] . "');\">" . $row['username'] . " (" . $row['last_name'] . ", " . $row['first_name'] . ")</li>";

    }
    $return .= '<ul class="ajax_search">';
    $return .= '<li class="head">Members (' . $members . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
/**
 *    Events

 */
$permission = $admin->check_permissions('rsvps', $employee);
if ($permission == '1') {
    if ($employee['permissions']['admin'] != '1') {
        $where = " AND (`public`='1' OR `owner`='" . $employee['id'] . "')";

    } else {
        $where = '';

    }
    $STH     = $db->run_query("
		SELECT
		    `name`,`starts`,`id`
		FROM
			ppSD_events
		WHERE
			`name` LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' AND
			`starts`>='" . current_date() . "'
			$where
		ORDER BY
			`name` ASC
		LIMIT 25
	");
    $events = 0;
    $results = '';
    while ($row = $STH->fetch()) {
        $events++;
        $results .= "<li class=\"res\" onclick=\"return load_page('event','view','" . $row['id'] . "');\">" . $row['name'] . " (" . format_date($row['starts']) . ")</li>";

    }
    $return .= '<ul class="ajax_search last">';
    $return .= '<li class="head">Events (' . $events . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
/**
 *    RSVPs

 */
$permission = $admin->check_permissions('rsvps', $employee);
if ($permission == '1') {
    if ($employee['permissions']['admin'] != '1') {
        $where = " AND (ppSD_events.public='1' OR ppSD_events.owner='" . $employee['id'] . "')";

    } else {
        $where = '';

    }
    $STH     = $db->run_query("

		SELECT

			ppSD_event_rsvp_data.last_name,

			ppSD_event_rsvp_data.first_name,

			ppSD_event_rsvps.id,

			ppSD_events.name

		FROM

			ppSD_event_rsvps

		JOIN

			ppSD_event_rsvp_data

		ON

			ppSD_event_rsvps.id=ppSD_event_rsvp_data.rsvp_id

		JOIN

			ppSD_events

		ON

			ppSD_event_rsvps.event_id=ppSD_events.id

		WHERE

			ppSD_event_rsvp_data.last_name LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR

			ppSD_event_rsvps.email LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR

			ppSD_event_rsvps.id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'

			$where

		ORDER BY

			ppSD_event_rsvp_data.last_name ASC

		LIMIT 25

	");
    $results = '';
    $rsvps = 0;
    while ($row = $STH->fetch()) {
        $rsvps++;
        $results .= "<li class=\"res\" onclick=\"return popup('rsvp_view','id=" . $row['id'] . "');\">" . $row['last_name'] . ", " . $row['first_name'] . " (" . $row['name'] . ")</li>";

    }
    $return .= '<ul class="ajax_search">';
    $return .= '<li class="head">Event Registrants (' . $rsvps . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
/**
 *    Transactions

 */
$permission = $admin->check_permissions('transactions', $employee);
if ($permission == '1') {
    $STH   = $db->run_query("

		SELECT

			ppSD_cart_sessions.id,

			ppSD_cart_sessions.gateway_order_id

		FROM

			ppSD_cart_sessions

		WHERE

			ppSD_cart_sessions.id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%' OR

			ppSD_cart_sessions.gateway_order_id LIKE '%" . $db->mysql_cleans($_POST['q']) . "%'

			$where

		ORDER BY

			ppSD_cart_sessions.id ASC

		LIMIT 25

	");
    $trans = 0;
    $results = '';
    while ($row = $STH->fetch()) {
        $trans++;
        $results .= "<li class=\"res\" onclick=\"return load_page('transaction','view','" . $row['id'] . "');\">" . $row['id'] . "</li>";
    }
    $return .= '<ul class="ajax_search">';
    $return .= '<li class="head">Transactions (' . $trans . ')</li>';
    $return .= $results;
    $return .= '</ul>';

}
$return = str_replace('+++', '&#43;&#43;&#43;', $return);
echo "1+++" . $return;
exit;



