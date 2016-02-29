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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require "../admin/sd-system/config.php";
$final_produce = '';
if (!empty($_GET['id'])) {
    $event          = new event;
    $get_code       = $event->get_qrcode_rsvp($_GET['id']);
    $confirm_device = $event->confirm_device();
    if (!empty($confirm_device)) {
        $check_in = $event->checkin($get_code['id'], $confirm_device);
        echo "<html>";
        echo "<head>";
        echo "<title>" . $check_in['message'] . "</title>";
        echo "<meta name=\"description\" content=\"" . $check_in['message'] . "\">";
        echo "</head>";
        echo "<body>";
        echo $check_in['message'];
        echo "</body>";
        echo "</html>";
        exit;
    } else {
        $final_produce = PP_URL . '/event.php?id=' . $get_code['event_id'];
    }
} else {
    $final_produce = COMPANY_URL;
}
echo $final_produce;
exit;
