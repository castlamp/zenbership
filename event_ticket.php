<?php

/**
 * Renders an event ticket.
 *
 */

require "admin/sd-system/config.php";

// Check a user's session
$session = new session;
$ses     = $session->check_session();

// Accessible?
$event     = new event();
$getTicket = $event->getRsvpFromSalt($_GET['id'], $_GET['s']);

if ($getTicket['found']) {

    $getEvent = $event->get_event($getTicket['tickets']['0']['event_id']);

    $tickets = '';

    foreach ($getTicket['tickets'] as $aTicket) {
        $salted = md5($aTicket['id'] . SALT);
        $check_in_url = PP_URL . '/pp-functions/qrcode_scan.php?id=' . $aTicket['id'] . '&s=' . $aTicket['s'] . '&s1=' . $salted;

        $code = PP_URL . '/pp-functions/qrcode.php?size=large&url=' . urlencode($check_in_url);

        $changes = array(
            'qr_code_url' => $code,
            'ticket' => $aTicket,
            'event' => $getEvent['data'],
            'media' => $getEvent['uploads'],
            'check_in_url' => $check_in_url,
        );
        $thisTicket = new template('event_ticket_entry', $changes, '0');

        $tickets .= $thisTicket;
    }

    // Event ticket render
    $wrapper = new template('event_ticket', $changes, '0');
    $wrapper = str_replace('%tickets%', $tickets, $wrapper);
    echo $wrapper;
    exit;
} else {
    echo 'Incorrect ticket information';
    exit;
}
