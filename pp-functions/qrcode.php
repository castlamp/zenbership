<?php

/**
 * Produces a QRCode
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
require "../admin/sd-system/config.php";
$final_produce = '';
$produce       = 0;
if (!empty($_GET['url'])) {
    $final_produce = urldecode($_GET['url']);
    $produce       = '1';
} else {
    if (!empty($_GET['id'])) {
        $event    = new event;
        $get_code = $event->get_qrcode_rsvp($_GET['id']);
        if (!empty($get_code['id'])) {
            $produce       = '1';
            $final_produce = PP_URL . '/event.php?id=' . $get_code['event_id'];
        } else {
            $produce = '0';
        }
        /*
        $admin = new admin;
        $check_employee = $admin->check_employee('qrcode-scan','0','1');
        $event = new event;
           if (! empty($check_employee)) {
               $produce = '1';
            // Get employee details
               // Do the rest
               $get_code = $event->get_qrcode_rsvp($_GET['id']);
               $final_produce = PP_URL . '/admin/cp-functions/qrcode_checkin.php?id=' . $get_code['id'];
           } else {
               $get_code = $event->get_qrcode_rsvp($_GET['id']);
               if (! empty($get_code['id'])) {
                   $produce = '1';
                   $final_produce = PP_URL . '/event.php?id=' . $get_code['event_id'];
               } else {
                   $produce = '0';
               }
           }
           $produce = '1';
           $final_produce = PP_URL . '/pp-functions/qrcode_scan.php?id=' . $_GET['id'];
           */
    }
}

$size = 3;
if (! empty($_GET['size'])) {
    switch ($_GET['size']) {
        case 'large':
            $size = 5;
            break;
        default:
            $size = 3;
    }
}

include "qrcode/qrlib.php";
if ($produce == '1') {
    QRcode::png($final_produce, false, QR_ECLEVEL_L, $size);
    exit;
} else {
    QRcode::png(COMPANY_URL, false, QR_ECLEVEL_L, $size);
    exit;
}
